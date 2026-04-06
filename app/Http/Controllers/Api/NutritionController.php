<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\AssignedPlan;
use App\Models\MealSwap;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NutritionController extends Controller
{
    use AuthenticatesVueRequests;

    /**
     * GET /api/v/client/nutrition/macros-today
     */
    public function macrosToday(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $today = Carbon::today()->toDateString();

        $plan = AssignedPlan::where('client_id', $client->id)
            ->where('plan_type', 'nutricion')
            ->where('active', true)
            ->latest('id')
            ->first();

        $content = $this->extractContent($plan);

        $goals = [
            'calories' => (int) ($content['objetivo_calorico'] ?? 0),
            'protein'  => (int) ($content['macros']['proteina_g'] ?? 0),
            'carbs'    => (int) ($content['macros']['carbohidratos_g'] ?? 0),
            'fat'      => (int) ($content['macros']['grasas_g'] ?? 0),
        ];

        $planMeals = $this->extractPlanMeals($content);

        $swaps = MealSwap::where('client_id', $client->id)
            ->whereDate('swap_date', $today)
            ->get();

        $swapsByMeal = $swaps->keyBy('original_meal_name');

        $consumedFromPlan = [
            'calories' => array_sum(array_column($planMeals, 'calories')),
            'protein'  => array_sum(array_column($planMeals, 'protein')),
            'carbs'    => array_sum(array_column($planMeals, 'carbs')),
            'fat'      => array_sum(array_column($planMeals, 'fat')),
        ];

        $meals = [];
        foreach ($planMeals as $meal) {
            $swap = $swapsByMeal->get($meal['name']);

            if ($swap) {
                $meals[] = [
                    'name'     => $meal['name'],
                    'calories' => (int) $swap->calories,
                    'protein'  => (int) $swap->protein_g,
                    'carbs'    => (int) $swap->carbs_g,
                    'fat'      => (int) $swap->fat_g,
                    'swapped'  => true,
                    'swap_id'  => $swap->id,
                    'recipe_name' => $swap->recipe_name,
                ];
                continue;
            }

            $meals[] = [
                'name'     => $meal['name'],
                'calories' => (int) $meal['calories'],
                'protein'  => (int) $meal['protein'],
                'carbs'    => (int) $meal['carbs'],
                'fat'      => (int) $meal['fat'],
                'swapped'  => false,
            ];
        }

        $totalDiff = [
            'calories' => (int) $swaps->sum('calories_diff'),
            'protein'  => (int) $swaps->sum('protein_diff'),
            'carbs'    => (int) $swaps->sum('carbs_diff'),
            'fat'      => (int) $swaps->sum('fat_diff'),
        ];

        $currentTotal = [
            'calories' => $consumedFromPlan['calories'] + $totalDiff['calories'],
            'protein'  => $consumedFromPlan['protein']  + $totalDiff['protein'],
            'carbs'    => $consumedFromPlan['carbs']    + $totalDiff['carbs'],
            'fat'      => $consumedFromPlan['fat']      + $totalDiff['fat'],
        ];

        $remaining = [
            'calories' => $goals['calories'] - $currentTotal['calories'],
            'protein'  => $goals['protein']  - $currentTotal['protein'],
            'carbs'    => $goals['carbs']    - $currentTotal['carbs'],
            'fat'      => $goals['fat']      - $currentTotal['fat'],
        ];

        return response()->json([
            'goals'             => $goals,
            'consumed_from_plan' => $consumedFromPlan,
            'swaps_today'       => $swaps->map(fn ($s) => [
                'id'                 => $s->id,
                'recipe_id'          => $s->recipe_id,
                'recipe_name'        => $s->recipe_name,
                'original_meal_name' => $s->original_meal_name,
                'calories_diff'      => (int) $s->calories_diff,
                'protein_diff'       => (int) $s->protein_diff,
                'carbs_diff'         => (int) $s->carbs_diff,
                'fat_diff'           => (int) $s->fat_diff,
            ])->values(),
            'current_total' => $currentTotal,
            'remaining'     => $remaining,
            'meals'         => $meals,
        ]);
    }

    /**
     * POST /api/v/client/nutrition/swap
     */
    public function createSwap(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $data = $request->validate([
            'recipe_id'              => ['required', 'integer'],
            'recipe_name'            => ['required', 'string', 'max:255'],
            'original_meal_name'     => ['required', 'string', 'max:255'],
            'recipe_macros'          => ['required', 'array'],
            'recipe_macros.calories' => ['required', 'numeric'],
            'recipe_macros.protein'  => ['required', 'numeric'],
            'recipe_macros.carbs'    => ['required', 'numeric'],
            'recipe_macros.fat'      => ['required', 'numeric'],
            'original_macros'          => ['required', 'array'],
            'original_macros.calories' => ['required', 'numeric'],
            'original_macros.protein'  => ['required', 'numeric'],
            'original_macros.carbs'    => ['required', 'numeric'],
            'original_macros.fat'      => ['required', 'numeric'],
        ]);

        $today = Carbon::today()->toDateString();

        $swap = DB::transaction(function () use ($client, $data, $today) {
            MealSwap::where('client_id', $client->id)
                ->whereDate('swap_date', $today)
                ->where('original_meal_name', $data['original_meal_name'])
                ->delete();

            return MealSwap::create([
                'client_id'          => $client->id,
                'recipe_id'          => $data['recipe_id'],
                'recipe_name'        => $data['recipe_name'],
                'original_meal_name' => $data['original_meal_name'],
                'swap_date'          => $today,
                'calories'           => (int) $data['recipe_macros']['calories'],
                'protein_g'          => (int) $data['recipe_macros']['protein'],
                'carbs_g'            => (int) $data['recipe_macros']['carbs'],
                'fat_g'              => (int) $data['recipe_macros']['fat'],
                'calories_diff'      => (int) ($data['recipe_macros']['calories'] - $data['original_macros']['calories']),
                'protein_diff'       => (int) ($data['recipe_macros']['protein']  - $data['original_macros']['protein']),
                'carbs_diff'         => (int) ($data['recipe_macros']['carbs']    - $data['original_macros']['carbs']),
                'fat_diff'           => (int) ($data['recipe_macros']['fat']      - $data['original_macros']['fat']),
            ]);
        });

        return response()->json(['swap' => $swap], 201);
    }

    /**
     * DELETE /api/v/client/nutrition/swap/{id}
     */
    public function deleteSwap(Request $request, int $id): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $swap = MealSwap::where('id', $id)
            ->where('client_id', $client->id)
            ->first();

        if (! $swap) {
            return response()->json(['message' => 'Swap no encontrado.'], 404);
        }

        $swap->delete();

        return response()->json(['message' => 'Swap eliminado.']);
    }

    /**
     * Normalize plan content (may be array or JSON string).
     */
    private function extractContent(?AssignedPlan $plan): array
    {
        if (! $plan) {
            return [];
        }

        $content = $plan->content;

        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($content) ? $content : [];
    }

    /**
     * Extract meals from plan content with computed macros.
     *
     * @return array<int, array{name:string,calories:int,protein:int,carbs:int,fat:int}>
     */
    private function extractPlanMeals(array $content): array
    {
        $rawMeals = $content['comidas'] ?? $content['comidas_sugeridas'] ?? [];

        if (! is_array($rawMeals)) {
            return [];
        }

        $meals = [];

        foreach ($rawMeals as $key => $meal) {
            if (! is_array($meal)) {
                continue;
            }

            $name = $meal['nombre']
                ?? $meal['tipo']
                ?? $meal['name']
                ?? (is_string($key) ? $key : 'Comida');

            $calories = (int) ($meal['calorias'] ?? $meal['calories'] ?? 0);
            $protein  = (int) ($meal['proteina_g'] ?? $meal['proteinas'] ?? $meal['protein'] ?? 0);
            $carbs    = (int) ($meal['carbohidratos_g'] ?? $meal['carbohidratos'] ?? $meal['carbs'] ?? 0);
            $fat      = (int) ($meal['grasas_g'] ?? $meal['grasas'] ?? $meal['fat'] ?? 0);

            $alimentos = $meal['alimentos'] ?? $meal['ingredientes'] ?? [];

            if (is_array($alimentos) && ! empty($alimentos)) {
                $sumCal = 0;
                $sumP = 0;
                $sumC = 0;
                $sumF = 0;
                $hasAny = false;

                foreach ($alimentos as $alimento) {
                    if (! is_array($alimento)) {
                        continue;
                    }
                    $hasAny = true;
                    $sumCal += (int) ($alimento['calorias'] ?? $alimento['calories'] ?? 0);
                    $sumP   += (int) ($alimento['proteina_g'] ?? $alimento['proteinas'] ?? $alimento['protein'] ?? 0);
                    $sumC   += (int) ($alimento['carbohidratos_g'] ?? $alimento['carbohidratos'] ?? $alimento['carbs'] ?? 0);
                    $sumF   += (int) ($alimento['grasas_g'] ?? $alimento['grasas'] ?? $alimento['fat'] ?? 0);
                }

                if ($hasAny && $sumCal > 0) {
                    $calories = $sumCal;
                    $protein  = $sumP;
                    $carbs    = $sumC;
                    $fat      = $sumF;
                }
            }

            $meals[] = [
                'name'     => (string) $name,
                'calories' => $calories,
                'protein'  => $protein,
                'carbs'    => $carbs,
                'fat'      => $fat,
            ];
        }

        return $meals;
    }
}
