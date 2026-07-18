<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dashboard extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'title', 'chart_style', 'x_axis',
        'report_on', 'project_filter', 'status_filter', 'priority_filter', 'date_range',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function widgets()
    {
        return $this->hasMany(DashboardWidget::class)->orderBy('position');
    }
}
