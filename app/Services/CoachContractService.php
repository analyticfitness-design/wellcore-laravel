<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\CoachContractAcceptance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class CoachContractService
{
    public function getCurrentVersion(): string
    {
        return (string) config('wellcore.coach_contract.version', '1.0');
    }

    public function isGateEnabled(): bool
    {
        $value = config('wellcore.coach_contract.enabled');

        if ($value === null) {
            throw new \RuntimeException('Coach contract gate config missing — cannot determine gate state');
        }

        return (bool) $value;
    }

    public function getContractHtml(string $version): string
    {
        $path = resource_path('views/legal/coach-contract-v'.$version.'.blade.php');

        if (! file_exists($path)) {
            throw new \RuntimeException("Contract HTML for version {$version} not found at {$path}.");
        }

        return View::file($path)->render();
    }

    public function getCurrentContentHash(): string
    {
        return hash('sha256', $this->getContractHtml($this->getCurrentVersion()));
    }

    public function hasAcceptedCurrentVersion(int $coachId): bool
    {
        return CoachContractAcceptance::query()
            ->where('coach_id', $coachId)
            ->where('contract_version', $this->getCurrentVersion())
            ->where('status', 'accepted')
            ->exists();
    }

    public function recordAcceptance(int $coachId, Request $request, bool $scrollCompleted): CoachContractAcceptance
    {
        return DB::transaction(function () use ($coachId, $request, $scrollCompleted): CoachContractAcceptance {
            $existing = CoachContractAcceptance::query()
                ->where('coach_id', $coachId)
                ->where('contract_version', $this->getCurrentVersion())
                ->lockForUpdate()
                ->first();

            $data = [
                'status' => 'accepted',
                'accepted_at' => now(),
                'declined_at' => null,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 4000),
                'content_hash' => $this->getCurrentContentHash(),
                'scroll_completed' => $scrollCompleted,
            ];

            if ($existing) {
                $existing->fill($data)->save();

                return $existing->fresh();
            }

            return CoachContractAcceptance::create(array_merge([
                'coach_id' => $coachId,
                'contract_version' => $this->getCurrentVersion(),
            ], $data));
        });
    }

    public function recordDecline(int $coachId, Request $request): CoachContractAcceptance
    {
        return DB::transaction(function () use ($coachId, $request) {
            $row = CoachContractAcceptance::query()
                ->where('coach_id', $coachId)
                ->where('contract_version', $this->getCurrentVersion())
                ->lockForUpdate()
                ->first();

            $attributes = [
                'status' => 'declined',
                'accepted_at' => null,
                'declined_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 4000),
                'content_hash' => $this->getCurrentContentHash(),
                'scroll_completed' => false,
            ];

            if ($row) {
                $row->fill($attributes)->save();
            } else {
                $row = CoachContractAcceptance::create(array_merge([
                    'coach_id' => $coachId,
                    'contract_version' => $this->getCurrentVersion(),
                ], $attributes));
            }

            Admin::query()->where('id', $coachId)->update([
                'active' => false,
                'inactive_reason' => 'contract_declined',
            ]);

            DB::table('auth_tokens')
                ->where('user_id', $coachId)
                ->where('user_type', 'admin')
                ->delete();

            return $row;
        });
    }
}
