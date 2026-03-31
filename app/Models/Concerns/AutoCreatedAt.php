<?php

namespace App\Models\Concerns;

trait AutoCreatedAt
{
    public static function bootAutoCreatedAt(): void
    {
        static::creating(function ($model) {
            if (! $model->created_at) {
                $model->created_at = now();
            }
        });
    }
}
