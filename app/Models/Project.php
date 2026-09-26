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

    public function linkedTasks()
    {
        return $this->belongsToMany(Task::class, 'task_project')->withTimestamps();
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
        return $this->belongsToMany(User::class, 'project_clients')
            ->withPivot('access_mode')
            ->withTimestamps();
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

    /**
     * Asana-style remove from project: drop membership, unassign open work, keep assignee on completed tasks.
     *
     * @return array{open_unassigned: int, completed_kept: int}
     */
    public function revokeMemberAccess(User $user): array
    {
        $this->members()->detach($user->id);

        $tasks = Task::query()
            ->where('company_id', $this->company_id)
            ->where(function ($q) {
                $q->where('project_id', $this->id)
                    ->orWhereHas('projects', fn ($p) => $p->where('projects.id', $this->id));
            })
            ->get();

        $openUnassigned = 0;
        $completedKept = 0;

        foreach ($tasks as $task) {
            $involved = (int) $task->assigned_to === (int) $user->id
                || $task->assignees()->where('users.id', $user->id)->exists();

            if (! $involved) {
                continue;
            }

            if ($task->status === 'done') {
                $completedKept++;

                continue;
            }

            if ((int) $task->assigned_to === (int) $user->id) {
                $task->update(['assigned_to' => null]);
            }
            $task->assignees()->detach($user->id);
            $task->followers()->detach($user->id);
            $openUnassigned++;
        }

        return [
            'open_unassigned' => $openUnassigned,
            'completed_kept' => $completedKept,
        ];
    }
}
