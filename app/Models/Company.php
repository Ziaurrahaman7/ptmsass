<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'slug', 'email', 'phone', 'logo', 'status', 'trial_ends_at',
        'mfa_required', 'trusted_domains',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'mfa_required' => 'boolean',
        'trusted_domains' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn ($company) => $company->slug ??= Str::slug($company->name));
        static::created(function ($company) {
            if (\Illuminate\Support\Facades\Schema::hasTable('roles')) {
                app(\App\Services\RoleProvisioner::class)->forCompany((int) $company->id);
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function admin()
    {
        return $this->hasOne(User::class)->where('role', 'company_admin');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
