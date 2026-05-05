<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\MentionResolverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MentionSearchController extends Controller
{
    public function __construct(private MentionResolverService $service) {}

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:3|max:50',
            'scope' => 'nullable|string|in:coach-team,all',
        ]);

        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $query = (string) $request->query('q');
        $scope = (string) $request->query('scope', 'coach-team');

        $scopeCoachId = null;
        if ($user instanceof Admin) {
            $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
            if ($role === 'coach') {
                $scopeCoachId = $user->id;
            }
        } else {
            $scopeCoachId = $user->coach_id ?? null;
        }

        $results = Cache::remember(
            'wc:mention-search:v1:'.md5("{$scope}:{$scopeCoachId}:{$query}"),
            ttl: 300,
            callback: fn () => $this->service->searchMentionTargets($query, $scopeCoachId),
        );

        return response()->json(['results' => $results]);
    }
}
