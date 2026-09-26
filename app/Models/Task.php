<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'parent_task_id', 'project_id', 'section_id', 'company_id', 'created_by', 'assigned_to',
        'title', 'description', 'status', 'priority', 'start_date', 'due_date', 'list_group', 'position', 'custom_values', 'is_milestone',
        'recurrence', 'recurrence_until', 'recurrence_remaining', 'estimated_minutes', 'task_template_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'custom_values' => 'array',
        'parent_task_id' => 'integer',
        'project_id' => 'integer',
        'section_id' => 'integer',
        'company_id' => 'integer',
        'created_by' => 'integer',
        'assigned_to' => 'integer',
        'is_milestone' => 'boolean',
        'recurrence_until' => 'date',
        'recurrence_remaining' => 'integer',
        'estimated_minutes' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'task_project')->withTimestamps();
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_assignees');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'task_followers')->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function activities()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
    
    public function parentTask()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }
    
    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function blockedByLinks()
    {
        return $this->hasMany(TaskDependency::class, 'task_id');
    }

    public function blockingLinks()
    {
        return $this->hasMany(TaskDependency::class, 'depends_on_task_id');
    }

    public function blockedBy()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id')
            ->withPivot('id', 'type')
            ->withTimestamps();
    }

    public function blocking()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_task_id', 'task_id')
            ->withPivot('id', 'type')
            ->withTimestamps();
    }

    public function approvals()
    {
        return $this->hasMany(TaskApproval::class);
    }

    protected static function booted(): void
    {
        static::created(function (Task $task) {
            if ($task->project_id && \Illuminate\Support\Facades\Schema::hasTable('task_project')) {
                $task->projects()->syncWithoutDetaching([$task->project_id]);
            }
        });
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'done'        => 'green',
            'in_progress' => 'blue',
            'in_review'   => 'purple',
            default       => 'gray',
        };
    }
}
