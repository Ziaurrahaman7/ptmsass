<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id', 'created_by', 'name', 'description', 'color', 'icon', 'is_favorite', 'is_template',
        'status', 'start_date', 'due_date', 'month_goals',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'due_date'    => 'date',
        'month_goals' => 'array',
        'company_id'  => 'integer',
        'created_by'  => 'integer',
        'is_favorite' => 'boolean',
        'is_template' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class)->orderBy('position')->orderBy('id');
    }

    public function customFields()
    {
        return $this->hasMany(CustomField::class)->orderBy('position')->orderBy('id');
    }

    public function portfolios()
    {
        return $this->belongsToMany(Portfolio::class);
    }

    public function goals()
    {
        return $this->belongsToMany(Goal::class, 'goal_project')->withTimestamps();
    }

    public function statusUpdates()
    {
        return $this->hasMany(ProjectStatusUpdate::class)->latest();
    }

    public function latestStatusUpdate()
    {
        return $this->hasOne(ProjectStatusUpdate::class)->latestOfMany();
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_user')->withPivot('role')->withTimestamps();
    }

    public function clients()
    {
        return $this->belongsToMany(User::class, 'project_clients')->withTimestamps();
    }

    public function resources()
    {
        return $this->hasMany(ProjectResource::class)->latest();
    }

    public function messages()
    {
        return $this->hasMany(ProjectMessage::class);
    }

    public function milestones()
    {
        return $this->hasMany(Task::class)->where('is_milestone', true)->orderBy('due_date');
    }

    public function progressPercentage(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) return 0;
        $done = $this->tasks()->where('status', 'done')->count();
        return (int) round(($done / $total) * 100);
    }
}
