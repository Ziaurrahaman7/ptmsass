<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $fillable = [
        'company_id', 'owner_id', 'team_id', 'parent_goal_id',
        'title', 'description', 'scope', 'status', 'progress_mode',
        'manual_progress', 'start_date', 'due_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date'   => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function parent()
    {
        return $this->belongsTo(Goal::class, 'parent_goal_id');
    }

    public function children()
    {
        return $this->hasMany(Goal::class, 'parent_goal_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'goal_project')->withTimestamps();
    }

    public function getProgressAttribute(): int
    {
        if ($this->progress_mode === 'projects') {
            $projects = $this->relationLoaded('projects') ? $this->projects : $this->projects()->get();
            if ($projects->isEmpty()) return 0;
            return (int) round($projects->avg(fn ($p) => $p->progressPercentage()));
        }

        return (int) ($this->manual_progress ?? 0);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'on_track'  => '#4ade80',
            'at_risk'   => '#fbbf24',
            'off_track' => '#f87171',
            'done'      => '#7c3aed',
            default     => '#6b7385',
        };
    }
}
