<?php

namespace App\Traits;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * P2.3 — Auditable trait. Use inside controllers to persist an immutable
 * audit_logs row for state-changing actions. Swallows any Throwable so
 * business logic is NEVER blocked by an audit failure.
 */
trait Auditable
{
    protected function audit(string $action, ?Model $target = null, array $diff = [], ?string $targetLabel = null): void
    {
        try {
            $request = app(Request::class);
            $actorType = null;
            $actorId = null;
            $actorName = null;

            // Try to resolve the current admin/client via the AuthenticatesVueRequests concern.
            if (method_exists($this, 'resolveAuthUser')) {
                $auth = $this->resolveAuthUser($request);
                if ($auth) {
                    $user = $auth['user'] ?? null;
                    $actorType = ($auth['userType'] ?? null) && method_exists($auth['userType'], 'value')
                        ? strtolower($auth['userType']->value)
                        : strtolower((string) ($auth['userType'] ?? ''));
                    $actorId = $user?->id;
                    $actorName = $user?->name ?? $user?->username ?? null;
                }
            }

            // Fallback to Laravel auth user.
            if (! $actorId) {
                $fallback = auth()->user();
                if ($fallback instanceof Admin) {
                    $actorType = 'admin';
                } elseif ($fallback instanceof Client) {
                    $actorType = 'client';
                }
                $actorId = $fallback?->id;
                $actorName = $fallback?->name ?? $fallback?->username ?? null;
            }

            AuditLog::create([
                'actor_type' => $actorType,
                'actor_id' => $actorId,
                'actor_name' => $actorName ? mb_substr($actorName, 0, 150) : null,
                'action' => mb_substr($action, 0, 50),
                'target_type' => $target ? class_basename($target) : null,
                'target_id' => $target?->getKey(),
                'target_label' => $targetLabel
                    ? mb_substr($targetLabel, 0, 255)
                    : ($target ? mb_substr((string) ($target->name ?? $target->title ?? $target->getKey()), 0, 255) : null),
                'diff' => empty($diff) ? null : $diff,
                'ip' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('audit() failed', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
