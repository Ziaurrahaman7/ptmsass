<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectStatusUpdate extends Model
{
    protected $fillable = [
        'project_id', 'company_id', 'user_id', 'status', 'message',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getColorAttribute(): string
    {
        return match ($this->status) {
            'on_track'  => '#4ade80',
            'at_risk'   => '#fbbf24',
            'off_track' => '#f87171',
            'on_hold'   => '#94a3b8',
            default     => '#6b7385',
        };
    }

    public function getLabelAttribute(): string
    {
        return match ($this->status) {
            'on_track'  => 'On track',
            'at_risk'   => 'At risk',
            'off_track' => 'Off track',
            'on_hold'   => 'On hold',
            default     => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }
}
