<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Models\Concerns\AutoCreatedAt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'log_date',
    'habit_type',
    'value',
])]
class HabitLog extends Model
{
    use AutoCreatedAt;

    protected $table = 'habit_logs';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'value' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
