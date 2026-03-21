<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplementLog extends Model
{
    protected $table = 'supplement_logs';

    protected $fillable = [
        'client_id',
        'log_date',
        'supplement_name',
        'timing',
        'taken',
    ];

    protected $casts = [
        'log_date' => 'date',
        'taken' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
