<?php

namespace App\Http\Controllers\Api\Coach;

use App\Http\Controllers\Controller;
use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\FoodPhoto;
use App\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FoodPhotoReviewController extends Controller
{
    /**
     * GET /api/v/coach/food-photos
     * Lista pendientes/revisadas con filtro opcional por cliente.
     */
    public function index(Request $request): JsonResponse
    {
        $coachId = auth('wellcore')->id();
        $showReviewed = $request->boolean('reviewed');
        $selectedClientId = $request->integer('client_id') ?: null;

        $clientIds = $this->getCoachClientIds($coachId);

        $photos = FoodPhoto::whereIn('client_id', $clientIds)
            ->where('coach_seen', $showReviewed)
            ->when($selectedClientId, fn ($q) => $q->where('client_id', $selectedClientId))
            ->orderByDesc('created_at')
            ->limit(40)
            ->get();

        $clientsById = Client::whereIn('id', $photos->pluck('client_id')->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        $allClients = Client::whereIn('id', $clientIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->values();

        $pendingCount = Cache::remember(
            "coach_food_pending:{$coachId}",
            60,
            fn () => FoodPhoto::whereIn('client_id', $clientIds)
                ->where('coach_seen', false)
                ->count()
        );

        return response()->json([
            'photos' => $photos->map(fn ($p) => [
                'id'             => $p->id,
                'client_id'      => $p->client_id,
                'client_name'    => $clientsById->get($p->client_id)?->name ?? 'Cliente',
                'meal_name'      => $p->meal_name,
                'photo_date'     => Carbon::parse($p->photo_date)->format('d M'),
                'photo_url'      => $p->photo_url,
                'coach_seen'     => $p->coach_seen,
                'coach_reaction' => $p->coach_reaction,
                'coach_note'     => $p->coach_note,
                'created_at'     => $p->created_at?->toIso8601String(),
                'created_diff'   => $p->created_at?->diffForHumans(),
            ]),
            'all_clients'   => $allClients,
            'pending_count' => $pendingCount,
        ]);
    }

    /**
     * POST /api/v/coach/food-photos/{id}/react
     * Body: { reaction: 'bien'|'mejorar' }
     */
    public function react(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reaction' => 'required|in:bien,mejorar',
        ]);

        $coachId = auth('wellcore')->id();
        $photo = FoodPhoto::find($id);

        if (! $photo) {
            return response()->json(['message' => 'No encontrada'], 404);
        }

        if (! $this->getCoachClientIds($coachId)->contains($photo->client_id)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $reaction = $request->input('reaction');

        $photo->update([
            'coach_seen'     => true,
            'coach_seen_at'  => Carbon::now(),
            'coach_reaction' => $reaction,
        ]);

        Cache::forget("coach_food_pending:{$coachId}");

        try {
            $coach = \App\Models\Admin::find($coachId);
            PushNotificationService::notifyClientFoodPhotoReacted(
                $photo->client_id,
                $coach?->name ?? 'Tu coach',
                $reaction,
                $photo->meal_name
            );
        } catch (\Throwable $e) {
            Log::warning('FoodPhotoReview::react notify failed', ['error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * PATCH /api/v/coach/food-photos/{id}/note
     * Body: { note: string }
     */
    public function saveNote(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        $coachId = auth('wellcore')->id();
        $photo = FoodPhoto::find($id);

        if (! $photo) {
            return response()->json(['message' => 'No encontrada'], 404);
        }

        if (! $this->getCoachClientIds($coachId)->contains($photo->client_id)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $note = trim((string) $request->input('note', ''));
        $photo->update(['coach_note' => $note === '' ? null : $note]);

        return response()->json(['ok' => true]);
    }

    /**
     * POST /api/v/coach/food-photos/{id}/seen
     */
    public function markSeen(Request $request, int $id): JsonResponse
    {
        $coachId = auth('wellcore')->id();
        $photo = FoodPhoto::find($id);

        if (! $photo) {
            return response()->json(['message' => 'No encontrada'], 404);
        }

        if (! $this->getCoachClientIds($coachId)->contains($photo->client_id)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $photo->update([
            'coach_seen'    => true,
            'coach_seen_at' => Carbon::now(),
        ]);

        Cache::forget("coach_food_pending:{$coachId}");

        return response()->json(['ok' => true]);
    }

    private function getCoachClientIds(int $coachId): \Illuminate\Support\Collection
    {
        return AssignedPlan::where('assigned_by', $coachId)
            ->pluck('client_id')
            ->unique()
            ->values();
    }
}
