<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EjercicioVideo extends Model
{
    protected $table = 'ejercicio_videos';

    protected $fillable = [
        'fitcron_slug',
        'nombre_display',
        'youtube_url',
        'keywords',
        'active',
    ];

    protected $casts = [
        'keywords' => 'array',
        'active'   => 'boolean',
    ];

    public function ejercicio(): BelongsTo
    {
        return $this->belongsTo(EjercicioFitcron::class, 'fitcron_slug', 'slug');
    }
}
