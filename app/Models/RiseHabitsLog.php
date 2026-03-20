<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'log_date',
    'habits_json',
    'completed',
])]
class RiseHabitsLog extends Model
{
    protected $table = 'rise_habits_log';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'habits_json' => 'array',
            'completed' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
