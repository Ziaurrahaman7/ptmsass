<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    protected $fillable = [
        'company_id', 'project_id', 'name', 'type', 'options', 'position',
    ];

    protected $casts = [
        'options'    => 'array',
        'company_id' => 'integer',
        'project_id' => 'integer',
        'position'   => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
