<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'coach_id',
    'name',
    'plan_type',
    'methodology',
    'description',
    'content_json',
    'ai_generated',
    'is_public',
])]
class PlanTemplate extends Model
{
    protected $table = 'plan_templates';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'content_json' => 'array',
            'ai_generated' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'coach_id');
    }
}
