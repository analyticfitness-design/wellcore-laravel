<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * AuditLog — immutable audit trail for critical actions.
 *
 * NOTE: legacy columns (user_type, user_id, model_type, model_id, old_values,
 * new_values, ip_address, user_agent TEXT, updated_at) may coexist with the
 * new columns (actor_*, target_*, diff, ip, user_agent VARCHAR(500)). Both
 * are listed in $fillable so AuditService (legacy) and the Auditable trait
 * (new) can both write safely. No record is ever updated.
 */
class AuditLog extends Model
{
    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $fillable = [
        // New schema (P2.3)
        'actor_type',
        'actor_id',
        'actor_name',
        'action',
        'target_type',
        'target_id',
        'target_label',
        'diff',
        'ip',
        'user_agent',
        'created_at',

        // Legacy schema (kept for compatibility with AuditService)
        'user_type',
        'user_id',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'diff' => 'array',
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
