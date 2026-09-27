<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\Services\PermissionService;
use App\Support\PermissionCatalog;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        $perms = app(PermissionService::class);
        if (! $perms->sameCompany($user, $task)) {
            return false;
        }
        if ($user->isCompanyAdmin()) {
            return true;
        }
        if ($perms->isAssignee($user, $task)) {
            return true;
        }
        if ($task->project && $this->seeProject($user, $task)) {
            return true;
        }

        return $task->projects()->where(function ($q) use ($user) {
            $q->whereHas('members', fn ($m) => $m->where('users.id', $user->id))
                ->orWhereHas('clients', fn ($c) => $c->where('users.id', $user->id));
        })->exists();
    }

    public function create(User $user): bool
    {
        return $user->isCompanyAdmin() || $user->isEmployee();
    }

    public function update(User $user, Task $task): bool
    {
        $perms = app(PermissionService::class);
        if (! $this->view($user, $task)) {
            return false;
        }
        if ($user->isCompanyAdmin()) {
            return true;
        }
        if ($this->projectMemberBelow($user, $task, 'editor')) {
            return false;
        }
        if ($task->project && $perms->projectAtLeast($user, $task->project, 'editor')) {
            return true;
        }
        if ($task->project && $perms->clientAtLeast($user, $task->project, 'contribute')) {
            return true;
        }
        if ($perms->isAssignee($user, $task)) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Task $task): bool
    {
        $perms = app(PermissionService::class);
        if (! $perms->sameCompany($user, $task)) {
            return false;
        }
        if ($user->isCompanyAdmin()) {
            return true;
        }
        if ($task->project_id === null && (int) $task->created_by === (int) $user->id) {
            return true;
        }

        return $task->project && $perms->projectAtLeast($user, $task->project, 'admin');
    }

    public function comment(User $user, Task $task): bool
    {
        $perms = app(PermissionService::class);
        if (! $this->view($user, $task)) {
            return false;
        }
        if ($user->isCompanyAdmin()) {
            return true;
        }
        if ($this->projectMemberBelow($user, $task, 'commenter')) {
            return false;
        }
        if ($task->project && $perms->clientAtLeast($user, $task->project, 'collaborate')) {
            return true;
        }
        if ($task->project && $perms->projectAtLeast($user, $task->project, 'commenter')) {
            return true;
        }
        if ($perms->isAssignee($user, $task)) {
            return true;
        }

        return false;
    }

    public function attach(User $user, Task $task): bool
    {
        return $this->comment($user, $task);
    }

    public function approve(User $user, Task $task): bool
    {
        $perms = app(PermissionService::class);
        if (! $this->view($user, $task)) {
            return false;
        }
        if ($user->isCompanyAdmin() || $perms->allows($user, 'approval.manage')) {
            return true;
        }

        return $task->project && $perms->clientAtLeast($user, $task->project, 'approve');
    }

    protected function seeProject(User $user, Task $task): bool
    {
        return app(ProjectPolicy::class)->view($user, $task->project);
    }

    /** Project members below this level cannot perform the action (viewer = read-only). */
    protected function projectMemberBelow(User $user, Task $task, string $needLevel): bool
    {
        if (! $task->project_id) {
            return false;
        }
        $level = app(PermissionService::class)->projectLevel($user, $task->project);
        if (! $level) {
            return false;
        }
        $ranks = PermissionCatalog::projectRanks();

        return ($ranks[$level] ?? 0) < ($ranks[$needLevel] ?? 99);
    }
}
