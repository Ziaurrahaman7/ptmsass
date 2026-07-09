<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = ['company_id', 'name', 'description'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('job_title', 'field_values')
            ->withTimestamps();
    }

    public function fields()
    {
        return $this->hasMany(TeamField::class)->orderBy('position');
    }

    public function messages()
    {
        return $this->hasMany(TeamMessage::class);
    }

    public function docs()
    {
        return $this->hasMany(TeamDoc::class)->latest();
    }

    public function notes()
    {
        return $this->hasMany(TeamNote::class)->orderBy('id');
    }
}
