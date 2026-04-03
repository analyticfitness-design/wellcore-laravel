<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EjercicioFitcron extends Model
{
    protected $table = 'ejercicios_fitcron';

    protected $fillable = [
        'slug',
        'nombre',
        'tipo',
        'grupo_muscular',
        'musculos_involucrados',
        'equipamiento',
        'dificultad',
        'gif_url',
        'gif_filename',
        'gif_path',
        'gif_path_sin_fondo',
        'sin_fondo_listo',
        'descargado',
        'video_url',
    ];

    protected $casts = [
        'dificultad' => 'integer',
        'descargado' => 'boolean',
        'sin_fondo_listo' => 'boolean',
    ];

    protected $appends = [];

    public function videos(): HasMany
    {
        return $this->hasMany(EjercicioVideo::class, 'fitcron_slug', 'slug');
    }

    public function getGifLocalUrlAttribute(): ?string
    {
        if (! $this->gif_filename) {
            return null;
        }

        return '/ejercicios/'.$this->gif_filename;
    }

    public function scopePorGrupo(Builder $query, string $grupo): Builder
    {
        return $query->where('grupo_muscular', $grupo);
    }

    public function scopeConGif(Builder $query): Builder
    {
        return $query->where('descargado', true)->whereNotNull('gif_filename');
    }

    public static function gruposMusculares(): array
    {
        return self::query()
            ->whereNotNull('grupo_muscular')
            ->distinct()
            ->orderBy('grupo_muscular')
            ->pluck('grupo_muscular')
            ->all();
    }
}
