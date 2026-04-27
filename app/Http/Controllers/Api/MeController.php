<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'autoshare_workout' => 'sometimes|boolean',
            'autoshare_pr' => 'sometimes|boolean',
            'autoshare_medal' => 'sometimes|boolean',
            'autoshare_weight' => 'sometimes|boolean',
            'autoshare_streak' => 'sometimes|boolean',
        ]);

        auth('wellcore')->user()->update($validated);

        return response()->json(['ok' => true]);
    }
}
