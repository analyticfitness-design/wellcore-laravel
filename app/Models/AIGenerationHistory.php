<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIGenerationHistory extends Model
{
    protected $table = 'ai_generation_history';

    protected $fillable = [
        'admin_id',
        'target_client_id',
        'plan_type',
        'methodology',
        'duration_weeks',
        'brief_json',
        'output_text',
        'status',
        'template_id',
        'assigned_plan_id',
        'output_chars',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'brief_json' => 'array',
            'duration_weeks' => 'integer',
            'output_chars' => 'integer',
            'duration_ms' => 'integer',
        ];
    }
}
