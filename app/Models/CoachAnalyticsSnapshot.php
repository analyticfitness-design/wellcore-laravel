<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'coach_id',
    'snapshot_date',
    'active_clients',
    'churn_risk_count',
    'checkins_week',
    'revenue_month',
    'avg_engagement',
])]
class CoachAnalyticsSnapshot extends Model
{
    protected $table = 'coach_analytics_snapshots';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'revenue_month' => 'decimal:2',
            'avg_engagement' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }
}
