<?php

namespace Tests\Concerns;

use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

trait CreatesWorkspaces
{
    protected function workspace(string $slug): array
    {
        $company = Company::factory()->create([
            'name' => ucfirst($slug).' Inc',
            'slug' => $slug,
            'email' => $slug.'@example.test',
        ]);

        $admin = User::factory()->companyAdmin()->create([
            'company_id' => $company->id,
            'name' => $slug.' admin',
            'email' => $slug.'.admin@example.test',
        ]);

        $employee = User::factory()->employee()->create([
            'company_id' => $company->id,
            'name' => $slug.' employee',
            'email' => $slug.'.employee@example.test',
        ]);

        $project = Project::factory()->create([
            'company_id' => $company->id,
            'created_by' => $admin->id,
            'name' => $slug.' project',
        ]);

        $task = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'assigned_to' => $admin->id,
            'title' => $slug.' secret task',
        ]);

        return compact('company', 'admin', 'employee', 'project', 'task');
    }
}
