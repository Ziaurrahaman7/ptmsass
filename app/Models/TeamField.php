<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamField extends Model
{
    protected $fillable = [
        'company_id', 'team_id', 'name', 'type', 'options', 'position',
    ];

    protected $casts = [
        'options'    => 'array',
        'company_id' => 'integer',
        'team_id'    => 'integer',
        'position'   => 'integer',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
