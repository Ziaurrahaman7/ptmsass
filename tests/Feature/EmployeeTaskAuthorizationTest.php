<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesWorkspaces;
use Tests\TestCase;

class EmployeeTaskAuthorizationTest extends TestCase
{
    use CreatesWorkspaces;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_employee_cannot_change_status_of_unassigned_task(): void
    {
        $ws = $this->workspace('acme');

        $this->actingAs($ws['employee'])
            ->patch(route('employee.tasks.status', [$ws['company']->slug, $ws['task']]), [
                'status' => 'in_progress',
            ])
            ->assertForbidden();

        $this->assertSame('todo', $ws['task']->fresh()->status);
    }

    public function test_employee_cannot_inline_update_unassigned_task(): void
    {
        $ws = $this->workspace('acme');

        $this->actingAs($ws['employee'])
            ->patch(route('employee.tasks.inline', [$ws['company']->slug, $ws['task']]), [
                'title' => 'Hijacked title',
            ])
            ->assertForbidden();

        $this->assertSame($ws['task']->title, $ws['task']->fresh()->title);
    }

    public function test_employee_cannot_comment_on_unassigned_task(): void
    {
        $ws = $this->workspace('acme');

        $this->actingAs($ws['employee'])
            ->post(route('employee.tasks.comments.store', [$ws['company']->slug, $ws['task']]), [
                'comment' => 'Should not be saved',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('task_comments', [
            'task_id' => $ws['task']->id,
            'user_id' => $ws['employee']->id,
        ]);
    }

    public function test_employee_can_change_status_of_assigned_task(): void
    {
        $ws = $this->workspace('acme');
        $ws['task']->update(['assigned_to' => $ws['employee']->id]);

        $this->actingAs($ws['employee'])
            ->patch(route('employee.tasks.status', [$ws['company']->slug, $ws['task']]), [
                'status' => 'in_progress',
            ])
            ->assertRedirect();

        $this->assertSame('in_progress', $ws['task']->fresh()->status);
    }
}
