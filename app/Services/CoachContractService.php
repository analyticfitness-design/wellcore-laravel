<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\CoachContractAcceptance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoachContractService
{
    public function getCurrentVersion(): string
    {
        return (string) config('wellcore.coach_contract.version', '1.0');
    }

    public function isGateEnabled(): bool
    {
        return (bool) config('wellcore.coach_contract.enabled', false);
    }

    public function getContractHtml(string $version): string
    {
        $path = resource_path('views/legal/coach-contract-v' . $version . '.blade.php');

        if (! file_exists($path)) {
            throw new \RuntimeException("Contract HTML for version {$version} not found at {$path}.");
        }

        return \Illuminate\Support\Facades\View::file($path)->render();
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
        return CoachContractAcceptance::query()->updateOrCreate(
            [
                'coach_id'         => $coachId,
                'contract_version' => $this->getCurrentVersion(),
            ],
            [
                'status'           => 'accepted',
                'accepted_at'      => now(),
                'declined_at'      => null,
                'ip_address'       => $request->ip() ?? '0.0.0.0',
                'user_agent'       => substr((string) $request->userAgent(), 0, 4000),
                'content_hash'     => $this->getCurrentContentHash(),
                'scroll_completed' => $scrollCompleted,
            ]
        );
    }

    public function recordDecline(int $coachId, Request $request): CoachContractAcceptance
    {
        $row = CoachContractAcceptance::query()->updateOrCreate(
            [
                'coach_id'         => $coachId,
                'contract_version' => $this->getCurrentVersion(),
            ],
            [
                'status'           => 'declined',
                'accepted_at'      => null,
                'declined_at'      => now(),
                'ip_address'       => $request->ip() ?? '0.0.0.0',
                'user_agent'       => substr((string) $request->userAgent(), 0, 4000),
                'content_hash'     => $this->getCurrentContentHash(),
                'scroll_completed' => false,
            ]
        );

        // Deactivate coach (coaches are stored in admins table with role='coach')
        // 'active' is the boolean column in admins table
        Admin::query()->where('id', $coachId)->update([
            'active' => false,
        ]);

        // Revoke all auth tokens for this coach (user_type='admin' in auth_tokens enum)
        DB::table('auth_tokens')
            ->where('user_id', $coachId)
            ->where('user_type', 'admin')
            ->delete();

        return $row;
    }
}
