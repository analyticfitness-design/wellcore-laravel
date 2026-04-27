<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Coach;

use App\Enums\Marketing\PieceState;
use App\Enums\Marketing\PieceType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\MarkPiecePublishedRequest;
use App\Http\Resources\Coach\Marketing\PieceStateResource;
use App\Models\CoachContentDrop;
use App\Models\CoachContentPieceState;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

final class PieceStateController extends Controller
{
    public function publish(MarkPiecePublishedRequest $request, CoachContentDrop $drop, string $pieceKey): JsonResponse
    {
        Gate::authorize('markPiecePublished', $drop);

        $state = CoachContentPieceState::updateOrCreate(
            [
                'drop_id' => $drop->id,
                'piece_type' => $this->detectType($pieceKey),
                'piece_key' => $pieceKey,
            ],
            [
                'coach_id' => $drop->coach_id,
                'state' => PieceState::Published,
                'published_url' => $request->validated('url'),
                'notes' => $request->validated('notes'),
                'state_changed_at' => now(),
            ],
        );

        $this->forgetDropCache($drop);

        return (new PieceStateResource($state))->response()->setStatusCode(200);
    }

    public function skip(CoachContentDrop $drop, string $pieceKey): JsonResponse
    {
        Gate::authorize('markPiecePublished', $drop);

        $state = CoachContentPieceState::updateOrCreate(
            [
                'drop_id' => $drop->id,
                'piece_type' => $this->detectType($pieceKey),
                'piece_key' => $pieceKey,
            ],
            [
                'coach_id' => $drop->coach_id,
                'state' => PieceState::Skipped,
                'state_changed_at' => now(),
            ],
        );

        $this->forgetDropCache($drop);

        return (new PieceStateResource($state))->response()->setStatusCode(200);
    }

    public function inProgress(CoachContentDrop $drop, string $pieceKey): JsonResponse
    {
        Gate::authorize('markPiecePublished', $drop);

        $state = CoachContentPieceState::updateOrCreate(
            [
                'drop_id' => $drop->id,
                'piece_type' => $this->detectType($pieceKey),
                'piece_key' => $pieceKey,
            ],
            [
                'coach_id' => $drop->coach_id,
                'state' => PieceState::InProgress,
                'state_changed_at' => now(),
            ],
        );

        $this->forgetDropCache($drop);

        return (new PieceStateResource($state))->response()->setStatusCode(200);
    }

    private function forgetDropCache(CoachContentDrop $drop): void
    {
        Cache::forget("coach_drop_v3:{$drop->coach_id}:{$drop->iso_year}:{$drop->iso_week}");
    }

    private function detectType(string $key): PieceType
    {
        return match (true) {
            str_starts_with($key, 'reel_') => PieceType::Reel,
            str_starts_with($key, 'story_') => PieceType::Story,
            str_starts_with($key, 'phase_') => PieceType::ChecklistPhase,
            default => throw new \InvalidArgumentException("Unknown piece key prefix: {$key}"),
        };
    }
}
