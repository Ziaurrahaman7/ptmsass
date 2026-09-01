<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesWorkspaces;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use CreatesWorkspaces;
    use RefreshDatabase;

    public function test_company_a_cannot_read_company_b_task_on_own_slug(): void
    {
        $a = $this->workspace('acme');
        $b = $this->workspace('globex');

        $this->actingAs($a['admin'])
            ->get(route('company.tasks.show', [$a['company']->slug, $b['task']]))
            ->assertForbidden();
    }

    public function test_company_a_cannot_open_company_b_admin_url(): void
    {
        $a = $this->workspace('acme');
        $b = $this->workspace('globex');

        $this->actingAs($a['admin'])
            ->get(route('company.tasks.show', [$b['company']->slug, $b['task']]))
            ->assertForbidden();
    }

    public function test_company_a_employee_cannot_read_company_b_task(): void
    {
        $a = $this->workspace('acme');
        $b = $this->workspace('globex');

        $this->actingAs($a['employee'])
            ->get(route('employee.tasks.show', [$a['company']->slug, $b['task']]))
            ->assertForbidden();
    }

    public function test_company_admin_can_read_own_company_task(): void
    {
        $a = $this->workspace('acme');

        $this->actingAs($a['admin'])
            ->get(route('company.tasks.show', [$a['company']->slug, $a['task']]))
            ->assertOk();
    }
}
