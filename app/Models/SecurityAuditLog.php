<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityAuditLog extends Model
{
    protected $fillable = ['company_id', 'user_id', 'action', 'ip', 'meta'];

    protected $casts = [
        'meta' => 'array',
    ];
}
