<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardPref extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'type', 'title_override', 'color', 'icon', 'is_favorite', 'is_hidden',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
        'is_hidden'   => 'boolean',
    ];
}
