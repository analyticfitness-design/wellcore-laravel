<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'test_name',
    'value',
    'unit',
    'reference_range',
    'test_date',
])]
class BloodworkResult extends Model
{
    protected $table = 'bloodwork_results';

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'test_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
