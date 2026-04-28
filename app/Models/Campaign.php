<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'platform',
    'status',
    'budget_cop',
    'spent_cop',
    'impressions',
    'clicks',
    'leads',
    'sales',
    'start_date',
    'end_date',
    'daily_stats',
    'notes',
    'created_by',
])]
class Campaign extends Model
{
    protected $table = 'campaigns';

    protected function casts(): array
    {
        return [
            'start_date'  => 'date',
            'end_date'    => 'date',
            'daily_stats' => 'array',
            'budget_cop'  => 'integer',
            'spent_cop'   => 'integer',
            'impressions' => 'integer',
            'clicks'      => 'integer',
            'leads'       => 'integer',
            'sales'       => 'integer',
        ];
    }

    public function getCtrAttribute(): float
    {
        return $this->impressions > 0
            ? round(($this->clicks / $this->impressions) * 100, 2)
            : 0.0;
    }

    public function getCrAttribute(): float
    {
        return $this->clicks > 0
            ? round(($this->sales / $this->clicks) * 100, 2)
            : 0.0;
    }

    public function getRoasAttribute(): float
    {
        return $this->spent_cop > 0
            ? round($this->revenue_cop / $this->spent_cop, 2)
            : 0.0;
    }

    public function getCplAttribute(): float
    {
        return $this->leads > 0
            ? round($this->spent_cop / $this->leads)
            : 0.0;
    }
}
