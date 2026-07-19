<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'title', 'description',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }
}
