<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Coach;

use App\Enums\Marketing\DropStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Coach\Marketing\CoachDropResource;
use App\Http\Resources\Coach\Marketing\CoachDropSummaryResource;
use App\Models\CoachContentDrop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

final class StrategyController extends Controller
{
    public function current(Request $request): CoachDropResource|JsonResponse
    {
        $coach = Auth::user();
        $monday = now()->startOfWeek();
        $year = (int) $monday->isoFormat('GGGG');
        $week = (int) $monday->isoFormat('W');

        $drop = Cache::remember(
            "coach_drop_v3:{$coach->id}:{$year}:{$week}",
            60,
            fn () => CoachContentDrop::with('pieceStates')
                ->where('coach_id', $coach->id)
                ->where('iso_year', $year)
                ->where('iso_week', $week)
                ->whereIn('status', [
                    DropStatus::Ready->value,
                    DropStatus::InProgress->value,
                    DropStatus::Completed->value,
                ])
                ->first()
        );

        if (! $drop) {
            return response()->json(['data' => null], 200);
        }

        Gate::authorize('view', $drop);

        return new CoachDropResource($drop);
    }

    public function history(Request $request): AnonymousResourceCollection
    {
        $coach = Auth::user();
        $perPage = min((int) $request->query('per_page', 20), 50);

        $drops = CoachContentDrop::with('pieceStates')
            ->where('coach_id', $coach->id)
            ->whereIn('status', [
                DropStatus::Ready->value,
                DropStatus::InProgress->value,
                DropStatus::Completed->value,
                DropStatus::Archived->value,
            ])
            ->orderByDesc('iso_year')
            ->orderByDesc('iso_week')
            ->paginate($perPage);

        return CoachDropSummaryResource::collection($drops);
    }

    public function show(Request $request, CoachContentDrop $drop): CoachDropResource
    {
        Gate::authorize('view', $drop);
        $drop->load('pieceStates');

        return new CoachDropResource($drop);
    }
}
