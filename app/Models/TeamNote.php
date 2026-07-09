<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamNote extends Model
{
    protected $fillable = [
        'company_id', 'team_id', 'user_id', 'title', 'content',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
