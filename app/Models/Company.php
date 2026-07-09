<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model
{
    protected $fillable = [
        'name', 'slug', 'email', 'phone', 'logo', 'status', 'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn ($company) => $company->slug ??= Str::slug($company->name));
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

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
