<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Support\Facades\Schema;

class PermissionService
{
    protected static array $provisioned = [];

    public function allows(User $user, string $key): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        if (! $user->company_id) {
            return false;
        }
        if ($user->hasUnrestrictedAdminPortalAccess()) {
            return true;
        }
        $this->ensure($user);

        $roles = $user->workspaceRoles()->where('roles.company_id', $user->company_id);
        // Custom role assigned → only custom roles count (ignore stacked system "Employee" pack).
        if ((clone $roles)->where('roles.is_system', false)->exists()) {
            $roles->where('roles.is_system', false);
        } elseif ($user->isCompanyAdmin()) {
            $roles->where('roles.slug', 'company-admin');
        }

        return $roles->whereHas('permissions', fn ($q) => $q->where('key', $key))->exists();
    }

    public function projectLevel(User $user, Project $project): ?string
    {
        if ((int) $user->company_id !== (int) $project->company_id) {
            return null;
        }
        if ($user->isCompanyAdmin() && $this->allows($user, 'project.view')) {
            return 'owner';
        }

        $row = $project->members()->where('users.id', $user->id)->first();

        return $row?->pivot?->role;
    }

    public function projectAtLeast(User $user, Project $project, string $need): bool
    {
        $level = $this->projectLevel($user, $project);
        if (! $level) {
            return false;
        }
        $ranks = PermissionCatalog::projectRanks();

        return ($ranks[$level] ?? 0) >= ($ranks[$need] ?? 99);
    }

    public function clientMode(User $user, Project $project): ?string
    {
        if (! $user->isClient() || (int) $user->company_id !== (int) $project->company_id) {
            return null;
        }
        $row = $project->clients()->where('users.id', $user->id)->first();

        return $row?->pivot?->access_mode;
    }

    public function clientAtLeast(User $user, Project $project, string $need): bool
    {
        $mode = $this->clientMode($user, $project);
        if (! $mode) {
            return false;
        }

        return PermissionCatalog::clientRank($mode) >= PermissionCatalog::clientRank($need);
    }

    public function teamRole(User $user, Team $team): ?string
    {
        if ((int) $user->company_id !== (int) $team->company_id) {
            return null;
        }
        if ($this->allows($user, 'team.manage')) {
            return 'admin';
        }
        $row = $team->members()->where('users.id', $user->id)->first();

        return $row?->pivot?->role;
    }

    public function isAssignee(User $user, Task $task): bool
    {
        return (int) $task->assigned_to === (int) $user->id
            || $task->assignees()->where('users.id', $user->id)->exists()
            || ($task->project_id === null && (int) $task->created_by === (int) $user->id);
    }

    public function sameCompany(User $user, $model): bool
    {
        return (int) $user->company_id === (int) $model->company_id;
    }

    protected function ensure(User $user): void
    {
        if (! Schema::hasTable('roles') || ! $user->company_id) {
            return;
        }
        $companyId = (int) $user->company_id;
        if (! isset(self::$provisioned[$companyId])) {
            self::$provisioned[$companyId] = \App\Models\Role::query()->where('company_id', $companyId)->exists();
        }
        if (! self::$provisioned[$companyId]) {
            app(RoleProvisioner::class)->forCompany($companyId);
            self::$provisioned[$companyId] = true;
        }
    }
}
