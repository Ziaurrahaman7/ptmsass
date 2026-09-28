<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['company_id', 'name', 'slug', 'is_system', 'portal_type'];

    protected $casts = [
        'is_system' => 'boolean',
        'company_id' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role');
    }

    public function isAdminPortalRole(): bool
    {
        return $this->portal_type === 'admin';
    }

    public function isEmployeePortalRole(): bool
    {
        return $this->portal_type !== 'admin';
    }
}
