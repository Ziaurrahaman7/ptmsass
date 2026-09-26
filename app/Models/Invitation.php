<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    protected $fillable = [
        'company_id', 'invited_by', 'name', 'email', 'role', 'workspace_role_id',
        'token', 'expires_at', 'accepted_at',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function workspaceRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'workspace_role_id');
    }

    public function scopePending(Builder $q): Builder
    {
        return $q->whereNull('accepted_at');
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->accepted_at === null && $this->expires_at->isPast();
    }

    public function acceptUrl(): string
    {
        return route('invite.show', $this->token);
    }

    public function roleLabel(): string
    {
        if ($this->role === 'client') {
            return 'Client';
        }
        if ($this->workspaceRole) {
            return $this->workspaceRole->name;
        }

        return 'Employee';
    }
}
