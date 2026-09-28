<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Services\PermissionService;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isCompanyAdmin()) {
            return app(PermissionService::class)->allows($user, 'project.view');
        }

        return $user->isEmployee();
    }

    public function view(User $user, Project $project): bool
    {
        $perms = app(PermissionService::class);
        if (! $perms->sameCompany($user, $project)) {
            return false;
        }
        if ($user->isCompanyAdmin() && $perms->allows($user, 'project.view')) {
            return true;
        }
        if ($user->isClient()) {
            return (bool) $perms->clientMode($user, $project);
        }
        // Project list/page: members (and clients) only — like Asana after "remove from project".
        // Assignees work from My Tasks / task detail, not the full project board.
        return (bool) $perms->projectLevel($user, $project);
    }

    public function create(User $user): bool
    {
        return app(PermissionService::class)->allows($user, 'project.create');
    }

    public function update(User $user, Project $project): bool
    {
        $perms = app(PermissionService::class);
        if (! $perms->sameCompany($user, $project)) {
            return false;
        }

        return ($user->isCompanyAdmin() && $perms->allows($user, 'project.edit'))
            || $perms->allows($user, 'project.edit')
            || $perms->projectAtLeast($user, $project, 'admin');
    }

    public function delete(User $user, Project $project): bool
    {
        $perms = app(PermissionService::class);
        if (! $perms->sameCompany($user, $project)) {
            return false;
        }

        return ($user->isCompanyAdmin() && $perms->allows($user, 'project.delete'))
            || $perms->allows($user, 'project.delete')
            || $perms->projectAtLeast($user, $project, 'owner');
    }

    public function manageMembers(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }

    public function createTasks(User $user, Project $project): bool
    {
        $perms = app(PermissionService::class);
        if (! $perms->sameCompany($user, $project)) {
            return false;
        }

        return ($user->isCompanyAdmin() && $perms->allows($user, 'project.edit'))
            || $perms->projectAtLeast($user, $project, 'editor')
            || $perms->clientAtLeast($user, $project, 'contribute');
    }
}
