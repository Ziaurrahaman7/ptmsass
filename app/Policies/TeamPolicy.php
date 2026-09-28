<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use App\Services\PermissionService;

class TeamPolicy
{
    public function view(User $user, Team $team): bool
    {
        $perms = app(PermissionService::class);
        if (! $perms->sameCompany($user, $team)) {
            return false;
        }

        return $perms->allows($user, 'team.manage')
            || $perms->teamRole($user, $team) !== null;
    }

    public function update(User $user, Team $team): bool
    {
        $perms = app(PermissionService::class);
        if (! $perms->sameCompany($user, $team)) {
            return false;
        }

        return $perms->allows($user, 'team.manage')
            || $perms->teamRole($user, $team) === 'admin';
    }

    public function delete(User $user, Team $team): bool
    {
        $perms = app(PermissionService::class);

        return $perms->sameCompany($user, $team)
            && $perms->allows($user, 'team.manage');
    }
}
