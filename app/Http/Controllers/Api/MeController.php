<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class MeController extends Controller
{
    private const SUPPORTED_LOCALES = ['es', 'en'];
    private const SUPPORTED_UNITS = ['metric', 'imperial'];

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

    /**
     * PATCH /api/v/me/locale — guarda locale del usuario autenticado.
     * Honra locale_locked: si está en true, devuelve 403 (solo admin puede cambiarlo desde el panel).
     */
    public function updateLocale(Request $request): JsonResponse
    {
        $user = auth('wellcore')->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (! empty($user->locale_locked)) {
            return response()->json([
                'message' => 'Tu idioma fue configurado por el equipo de coach. / Your language was set by your coach team.',
                'locale_locked' => true,
                'current_locale' => $user->locale ?? 'es',
            ], 403);
        }

        $validated = $request->validate([
            'locale' => 'required|string|in:' . implode(',', self::SUPPORTED_LOCALES),
            'unit_system' => 'sometimes|string|in:' . implode(',', self::SUPPORTED_UNITS),
        ]);

        $update = ['locale' => $validated['locale']];

        if (isset($validated['unit_system']) && array_key_exists('unit_system', $user->getAttributes())) {
            $update['unit_system'] = $validated['unit_system'];
        }

        $user->update($update);

        $cookie = Cookie::create(
            name: 'wc_locale',
            value: $validated['locale'],
            expire: time() + (60 * 60 * 24 * 365),
            path: '/',
            secure: $request->isSecure(),
            httpOnly: false,
            sameSite: Cookie::SAMESITE_LAX,
        );

        return response()
            ->json([
                'ok' => true,
                'locale' => $validated['locale'],
                'unit_system' => $user->unit_system ?? null,
            ])
            ->withCookie($cookie);
    }
}
