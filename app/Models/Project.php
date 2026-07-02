<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'company_id', 'created_by', 'name', 'description', 'status', 'start_date', 'due_date', 'month_goals',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'due_date'    => 'date',
        'month_goals' => 'array',
        'company_id'  => 'integer',
        'created_by'  => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class)->orderBy('position')->orderBy('id');
    }

    public function customFields()
    {
        return $this->hasMany(CustomField::class)->orderBy('position')->orderBy('id');
    }

    public function progressPercentage(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) return 0;
        $done = $this->tasks()->where('status', 'done')->count();
        return (int) round(($done / $total) * 100);
    }
}
