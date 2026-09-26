<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskTemplate extends Model
{
    protected $fillable = [
        'company_id', 'created_by', 'name', 'description', 'priority',
        'due_in_days', 'subtasks', 'custom_values',
    ];

    protected $casts = [
        'subtasks' => 'array',
        'custom_values' => 'array',
        'due_in_days' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
