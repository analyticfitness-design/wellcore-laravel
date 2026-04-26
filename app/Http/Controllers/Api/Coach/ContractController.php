<?php

namespace App\Http\Controllers\Api\Coach;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Services\CoachContractService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function __construct(private readonly CoachContractService $service) {}

    public function status(Request $request): JsonResponse
    {
        $user = $request->user('wellcore');

        if (! $user || ! $this->service->isGateEnabled() || $user->role !== UserRole::Coach) {
            return response()->json([
                'requires_acceptance' => false,
                'version' => $this->service->getCurrentVersion(),
                'html' => null,
            ]);
        }

        $version = $this->service->getCurrentVersion();
        $needs = ! $this->service->hasAcceptedCurrentVersion($user->id);

        return response()->json([
            'requires_acceptance' => $needs,
            'version' => $version,
            'html' => $needs ? $this->service->getContractHtml($version) : null,
        ]);
    }

    public function accept(Request $request): JsonResponse
    {
        $user = $request->user('wellcore');
        if (! $user) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        if ($user->role !== UserRole::Coach) {
            return response()->json(['error' => 'not_a_coach'], 403);
        }

        if (! $this->service->isGateEnabled()) {
            return response()->json(['error' => 'gate_disabled'], 404);
        }

        $data = $request->validate([
            'version' => ['required', 'string'],
            'scroll_completed' => ['required', 'boolean'],
        ]);

        if ($data['version'] !== $this->service->getCurrentVersion()) {
            return response()->json(['error' => 'version_mismatch'], 422);
        }

        if (! $data['scroll_completed']) {
            return response()->json(['error' => 'scroll_not_completed'], 422);
        }

        $this->service->recordAcceptance($user->id, $request, true);

        return response()->json(['ok' => true]);
    }

    public function decline(Request $request): JsonResponse
    {
        $user = $request->user('wellcore');
        if (! $user) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        if ($user->role !== UserRole::Coach) {
            return response()->json(['error' => 'not_a_coach'], 403);
        }

        if (! $this->service->isGateEnabled()) {
            return response()->json(['error' => 'gate_disabled'], 404);
        }

        if ($this->service->hasAcceptedCurrentVersion($user->id)) {
            return response()->json(['error' => 'already_accepted'], 409);
        }

        $this->service->recordDecline($user->id, $request);

        return response()->json(['ok' => true, 'logged_out' => true]);
    }
}
