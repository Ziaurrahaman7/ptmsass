<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'company_id', 'is_active',
        'weekly_capacity_hours', 'mfa_enabled',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
        'company_id'        => 'integer',
        'weekly_capacity_hours' => 'integer',
        'mfa_enabled' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function clientProjects()
    {
        return $this->belongsToMany(Project::class, 'project_clients')
            ->withPivot('access_mode')
            ->withTimestamps();
    }

    public function workspaceRoles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role', 'job_title', 'field_values')
            ->withTimestamps();
    }

    public function hasPermission(string $key): bool
    {
        return app(\App\Services\PermissionService::class)->allows($this, $key);
    }

    public function projectLevel(Project $project): ?string
    {
        return app(\App\Services\PermissionService::class)->projectLevel($this, $project);
    }

    public function clientMode(Project $project): ?string
    {
        return app(\App\Services\PermissionService::class)->clientMode($this, $project);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isCompanyAdmin(): bool
    {
        return $this->role === 'company_admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            if ($user->company_id && \Illuminate\Support\Facades\Schema::hasTable('roles')) {
                app(\App\Services\RoleProvisioner::class)->assignDefault($user);
            }
        });
    }
}
