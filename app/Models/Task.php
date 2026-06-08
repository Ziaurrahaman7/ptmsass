<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'project_id', 'company_id', 'created_by', 'assigned_to',
        'title', 'description', 'status', 'priority', 'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
        'project_id' => 'integer',
        'company_id' => 'integer',
        'created_by' => 'integer',
        'assigned_to' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
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

    public function priorityColor(): string
    {
        return match($this->priority) {
            'urgent' => 'red',
            'high'   => 'orange',
            'medium' => 'yellow',
            default  => 'gray',
        };
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
