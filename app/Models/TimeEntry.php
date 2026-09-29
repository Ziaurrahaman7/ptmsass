<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'task_id', 'project_id', 'minutes', 'worked_on',
        'billable', 'status', 'note', 'started_at', 'stopped_at',
    ];

    protected $casts = [
        'worked_on' => 'date',
        'billable' => 'boolean',
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'minutes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeFinished($query)
    {
        return $query->where('status', '!=', 'running');
    }

    public static function runningForUser(int $userId): ?self
    {
        return static::query()
            ->where('user_id', $userId)
            ->where('status', 'running')
            ->latest('id')
            ->first();
    }

    public static function loggedMinutesForTask(int $taskId): int
    {
        return (int) static::query()
            ->where('task_id', $taskId)
            ->finished()
            ->sum('minutes');
    }
}
