<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'log_date',
    'completed',
    'year_num',
    'week_num',
])]
class TrainingLog extends Model
{
    protected $table = 'training_logs';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'completed' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
