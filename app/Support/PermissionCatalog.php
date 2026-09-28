<?php

namespace App\Support;

class PermissionCatalog
{
    /** Workspace-wide capabilities (Roles UI + global delegation). */
    public static function workspace(): array
    {
        return [
            'project.create' => 'Create projects',
            'project.edit' => 'Edit projects',
            'project.delete' => 'Delete projects',
            'team.manage' => 'Manage all teams',
            'member.invite' => 'Invite members',
            'settings.manage' => 'Manage workspace settings and roles',
            'form.manage' => 'Manage intake forms',
            'rule.manage' => 'Manage automation rules',
            'template.manage' => 'Manage task templates',
            'time.track' => 'Track time',
            'time.review' => 'Review timesheets',
            'report.view' => 'View cross-project reports',
        ];
    }

    /** @deprecated Hidden from Roles UI; kept for policy/DB cleanup only. */
    public static function legacyHiddenKeys(): array
    {
        return ['task.assign', 'task.edit', 'task.comment', 'approval.manage', 'capacity.view'];
    }

    public static function all(): array
    {
        return self::workspace();
    }

    public static function companyAdminKeys(): array
    {
        return array_keys(self::workspace());
    }

    public static function employeeKeys(): array
    {
        return ['time.track'];
    }

    public static function projectRanks(): array
    {
        return [
            'viewer' => 1,
            'commenter' => 2,
            'editor' => 3,
            'admin' => 4,
            'owner' => 5,
        ];
    }

    public static function projectLevels(): array
    {
        return array_keys(self::projectRanks());
    }

    public static function clientModes(): array
    {
        return ['view', 'collaborate', 'contribute', 'approve'];
    }

    public static function clientRank(string $mode): int
    {
        return match ($mode) {
            'view' => 1,
            'collaborate' => 2,
            'contribute' => 3,
            'approve' => 4,
            default => 0,
        };
    }

    public static function projectRoleLabel(?string $role): string
    {
        return match ($role) {
            'owner' => 'Owner',
            'admin' => 'Admin',
            'editor', 'member' => 'Editor',
            'commenter' => 'Commenter',
            'viewer' => 'Viewer',
            default => $role ? ucfirst($role) : 'Guest',
        };
    }

    public static function projectAccessSummary(?string $role, bool $assignedTasksOnly = false): string
    {
        if ($assignedTasksOnly) {
            return 'You only see tasks assigned to you on this project.';
        }

        return match ($role) {
            'viewer' => 'You can view all tasks in this project (read-only).',
            'commenter' => 'You can view tasks and add comments & attachments.',
            'editor', 'member' => 'You can view, edit, and create tasks in this project.',
            'admin' => 'You can manage tasks and project settings.',
            'owner' => 'You have full control of this project.',
            default => 'You can open this project because a task is assigned to you.',
        };
    }
}
