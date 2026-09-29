<?php

namespace App\Support;

class AdminRoutePermissions
{
    /** @return array<string, string> pattern => permission key */
    public static function patterns(): array
    {
        return [
            'company.dashboard' => 'dashboard.view',
            'company.search' => 'dashboard.view',
            'company.my-tasks.*' => 'my_tasks.view',
            'company.notifications.*' => 'inbox.view',
            'company.members.*' => 'member.manage',
            'company.roles.*' => 'settings.manage',
            'company.templates.*' => 'template.manage',
            'company.forms.*' => 'form.manage',
            'company.rules.*' => 'rule.manage',
            'company.time.*' => 'time.track',
            'company.timesheets.*' => 'time.review',
            'company.reports.*' => 'report.view',
            'company.integrations.*' => 'integration.manage',
            'company.security.*' => 'settings.manage',
            'company.priorities.*' => 'priority.manage',
            'company.insights.*' => 'insight.view',
            'company.portfolios.*' => 'portfolio.manage',
            'company.goals.*' => 'goal.manage',
            'company.teams.*' => 'team.manage',
            'company.team.*' => 'team.manage',
            'company.projects.index' => 'project.view',
            'company.projects.create' => 'project.create',
            'company.projects.store' => 'project.create',
            'company.projects.show' => 'project.view',
            'company.projects.edit' => 'project.edit',
            'company.projects.update' => 'project.edit',
            'company.projects.destroy' => 'project.delete',
            'company.projects.export' => 'project.view',
            'company.projects.export.download' => 'project.view',
            'company.projects.import' => 'project.edit',
            'company.projects.goal' => 'project.edit',
            'company.projects.color-icon' => 'project.edit',
            'company.projects.favorite' => 'project.view',
            'company.projects.duplicate' => 'project.create',
            'company.projects.save-as-template' => 'template.manage',
            'company.projects.status-updates.store' => 'project.edit',
            'company.projects.members.*' => 'project.edit',
            'company.projects.clients.*' => 'project.edit',
            'company.projects.resources.*' => 'project.edit',
            'company.projects.messages.store' => 'project.view',
            'company.projects.milestones.*' => 'project.edit',
            'company.tasks.*' => 'project.view',
            'company.custom_fields.*' => 'project.edit',
            'company.sections.*' => 'project.edit',
        ];
    }

    public static function forRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        foreach (self::patterns() as $pattern => $permission) {
            if (self::matches($pattern, $routeName)) {
                return $permission;
            }
        }

        return null;
    }

    protected static function matches(string $pattern, string $routeName): bool
    {
        if ($pattern === $routeName) {
            return true;
        }
        if (str_ends_with($pattern, '.*')) {
            $prefix = substr($pattern, 0, -1);

            return str_starts_with($routeName, $prefix);
        }

        return false;
    }
}
