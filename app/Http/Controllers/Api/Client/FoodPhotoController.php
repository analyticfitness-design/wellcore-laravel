<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFoodPhotoRequest;
use App\Http\Resources\FoodPhotoResource;
use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\FoodPhoto;
use App\Services\FoodPhotoService;
use App\Services\NutritionPlanParser;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FoodPhotoController extends Controller
{
    public function __construct(private FoodPhotoService $service) {}

    public function index(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user('wellcore');
        $today = Carbon::now('America/Bogota')->toDateString();

        $plan = AssignedPlan::where('client_id', $client->id)
            ->where('plan_type', 'nutricion')
            ->where('active', true)
            ->latest()
            ->first();

        $meals = $plan && $plan->content
            ? NutritionPlanParser::extractMeals(is_array($plan->content) ? $plan->content : [])
            : [];

        $todayPhotos = FoodPhoto::where('photo_date', $today)
            ->where('client_id', $client->id)
            ->get()
            ->keyBy('meal_index');

        $mealsWithPhotos = collect($meals)->values()->map(function ($meal, $i) use ($todayPhotos) {
            return [
                'index'     => $i,
                'nombre'    => $meal['nombre'],
                'calorias'  => $meal['calorias'],
                'alimentos' => $meal['alimentos'],
                'macros'    => $meal['macros'],
                'notas'     => $meal['notas'],
                'photo'     => $todayPhotos->get($i)
                    ? new FoodPhotoResource($todayPhotos->get($i))
                    : null,
            ];
        })->all();

        $xpToday = $todayPhotos->where('xp_awarded', true)->count() * 15;
        $bonusEarned = DB::table('habit_logs')
            ->where('client_id', $client->id)
            ->where('habit_type', 'food_day_bonus')
            ->where('log_date', $today)
            ->exists();

        if ($bonusEarned) {
            $xpToday += 30;
        }

        return response()->json([
            'has_nutrition_plan' => $plan !== null,
            'meals'              => $mealsWithPhotos,
            'xp_today'           => $xpToday,
            'bonus_earned_today' => $bonusEarned,
            'streak_days'        => $this->computeStreak($client->id),
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user('wellcore');
        $days = collect(range(0, 6))->map(function ($offset) use ($client) {
            $date = Carbon::now('America/Bogota')->subDays($offset)->toDateString();
            $count = FoodPhoto::where('photo_date', $date)
                ->where('client_id', $client->id)
                ->count();

            return [
                'date'     => $date,
                'uploaded' => $count,
            ];
        });

        return response()->json(['week_history' => $days->values()->all()]);
    }

    public function store(StoreFoodPhotoRequest $request): JsonResource
    {
        /** @var Client $client */
        $client = $request->user('wellcore');

        $photo = $this->service->store(
            $client,
            $request->file('photo'),
            $request->input('meal_name'),
            (int) $request->input('meal_index'),
            $request->photoDate(),
            $request->input('client_note')
        );

        Cache::forget("food_streak:{$client->id}");

        return (new FoodPhotoResource($photo))
            ->response()
            ->setStatusCode(201);
    }

    public function updateNote(Request $request, int $id): JsonResponse
    {
        $request->validate(['client_note' => 'nullable|string|max:1000']);

        /** @var Client $client */
        $client = $request->user('wellcore');

        $photo = FoodPhoto::where('client_id', $client->id)->find($id);
        if (! $photo) {
            return response()->json(['message' => 'No encontrada'], 404);
        }

        $note = trim((string) $request->input('client_note', ''));
        $photo->update(['client_note' => $note === '' ? null : $note]);

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user('wellcore');

        $photo = FoodPhoto::where('client_id', $client->id)->find($id);
        if (! $photo) {
            return response()->json(['message' => 'No encontrada'], 404);
        }

        try {
            $this->service->delete($photo);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        Cache::forget("food_streak:{$client->id}");

        return response()->json(null, 204);
    }

    private function computeStreak(int $clientId): int
    {
        return Cache::remember("food_streak:{$clientId}", 300, function () use ($clientId) {
            $rows = DB::table('food_photos')
                ->select('photo_date', DB::raw('COUNT(*) as cnt'))
                ->where('client_id', $clientId)
                ->groupBy('photo_date')
                ->orderByDesc('photo_date')
                ->limit(60)
                ->get();

            if ($rows->isEmpty()) {
                return 0;
            }

            $streak = 0;
            $cursor = Carbon::now('America/Bogota');
            $byDate = $rows->pluck('cnt', 'photo_date');

            while ($byDate->get($cursor->toDateString(), 0) > 0) {
                $streak++;
                $cursor = $cursor->subDay();
                if ($streak > 365) {
                    break;
                }
            }

            return $streak;
        });
    }
}
