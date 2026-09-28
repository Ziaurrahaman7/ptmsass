<?php

namespace App\Support;

class PermissionCatalog
{
    /**
     * Roles UI: permissions that work on the employee portal (/{slug}/...).
     *
     * @return array<string, array{label: string, hint: string}>
     */
    public static function employeePortalPermissions(): array
    {
        return [
            'project.create' => [
                'label' => 'Create projects',
                'hint' => 'Employee sidebar → Projects (+) and /projects/create',
            ],
            'member.invite' => [
                'label' => 'Invite members',
                'hint' => 'Employee sidebar → Invite members',
            ],
            'time.track' => [
                'label' => 'Track time',
                'hint' => 'Employee sidebar → Time',
            ],
            'form.manage' => [
                'label' => 'Manage intake forms',
                'hint' => 'Employee sidebar → Intake forms + public /f/… links',
            ],
        ];
    }

    /**
     * Roles UI: permissions for the company admin portal (/{slug}/admin/...).
     * Employee login cannot open these pages even when ticked.
     *
     * @return array<string, array{label: string, hint: string}>
     */
    public static function adminPortalPermissions(): array
    {
        return [
            'project.edit' => [
                'label' => 'Edit projects',
                'hint' => 'Admin → Projects → edit project details',
            ],
            'project.delete' => [
                'label' => 'Delete projects',
                'hint' => 'Admin → Projects list → Delete',
            ],
            'team.manage' => [
                'label' => 'Manage all teams',
                'hint' => 'Admin → Teams (any team, not only team admin)',
            ],
            'settings.manage' => [
                'label' => 'Manage workspace settings and roles',
                'hint' => 'Admin → Roles & security settings',
            ],
            'template.manage' => [
                'label' => 'Manage task templates',
                'hint' => 'Admin → Settings → Templates',
            ],
            'rule.manage' => [
                'label' => 'Manage automation rules',
                'hint' => 'Admin → Settings → Rules',
            ],
            'time.review' => [
                'label' => 'Review timesheets',
                'hint' => 'Admin → Settings → Timesheets',
            ],
            'report.view' => [
                'label' => 'View cross-project reports',
                'hint' => 'Admin → Settings → Reports',
            ],
        ];
    }

    /** Workspace-wide capabilities (Roles UI + global delegation). */
    public static function workspace(): array
    {
        $labels = [];
        foreach (self::employeePortalPermissions() as $key => $meta) {
            $labels[$key] = $meta['label'];
        }
        foreach (self::adminPortalPermissions() as $key => $meta) {
            $labels[$key] = $meta['label'];
        }

        return $labels;
    }

    public static function employeePortalKeys(): array
    {
        return array_keys(self::employeePortalPermissions());
    }

    public static function adminPortalKeys(): array
    {
        return array_keys(self::adminPortalPermissions());
    }

    /** @return array<int, string> */
    public static function keysForPortalType(string $portalType): array
    {
        return $portalType === 'admin'
            ? self::adminPortalKeys()
            : self::employeePortalKeys();
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
