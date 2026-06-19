<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'company_id', 'project_id', 'name', 'position',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'project_id' => 'integer',
        'position'   => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
