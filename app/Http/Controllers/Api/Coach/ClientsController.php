<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Coach;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientsController extends Controller
{
    public function count(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($this->isCoach($coach), 403);

        $statuses = (array) $request->query('status', ['activo']);
        $plans = $request->query('plan');

        $query = Client::query()
            ->where('coach_id', $coach->id)
            ->whereIn('status', $statuses);

        if (! empty($plans)) {
            $plansArr = is_array($plans) ? $plans : explode(',', (string) $plans);
            $query->whereIn('plan', $plansArr);
        }

        return response()->json([
            'count' => (int) $query->count(),
        ]);
    }

    private function isCoach(mixed $user): bool
    {
        if (! $user instanceof Admin) {
            return false;
        }

        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

        return $role === 'coach';
    }
}
