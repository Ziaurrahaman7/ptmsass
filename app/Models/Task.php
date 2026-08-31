<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'parent_task_id', 'project_id', 'section_id', 'company_id', 'created_by', 'assigned_to',
        'title', 'description', 'status', 'priority', 'start_date', 'due_date', 'list_group', 'position', 'custom_values', 'is_milestone',
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
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
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
