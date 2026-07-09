<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMessage extends Model
{
    protected $fillable = [
        'company_id', 'team_id', 'user_id', 'body',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'team_id'    => 'integer',
        'user_id'    => 'integer',
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
