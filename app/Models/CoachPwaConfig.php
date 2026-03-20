<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'coach_id',
    'app_name',
    'icon_url',
    'color',
    'subdomain',
])]
class CoachPwaConfig extends Model
{
    protected $table = 'coach_pwa_config';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'coach_id');
    }
}
