<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public static function log(
        string $action,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): AuditLog {
        $user = auth()->user();

        return AuditLog::create([
            'user_type' => $user ? class_basename($user) : null,
            'user_id'   => $user?->id,
            'action'    => $action,
            'model_type' => $model ? class_basename($model) : null,
            'model_id'  => $model?->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function logAction(string $action, ?string $description = null): AuditLog
    {
        return self::log($action, null, null, $description ? ['description' => $description] : null);
    }
}
