<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardWidget extends Model
{
    protected $fillable = [
        'dashboard_id', 'title', 'chart_style', 'x_axis',
        'project_filter', 'status_filter', 'priority_filter', 'date_range', 'position',
    ];

    public function dashboard()
    {
        return $this->belongsTo(Dashboard::class);
    }
}
