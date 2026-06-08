<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'company_id', 'created_by', 'name', 'description', 'status', 'start_date', 'due_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date'   => 'date',
        'company_id' => 'integer',
        'created_by' => 'integer',
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

    public function progressPercentage(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) return 0;
        $done = $this->tasks()->where('status', 'done')->count();
        return (int) round(($done / $total) * 100);
    }
}
