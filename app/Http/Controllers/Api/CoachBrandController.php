<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserType;
use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\CoachProfile;
use App\Services\ImagePipelineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Coach Branding API (P4 — Mi Marca).
 *
 * Manages the coach's personal brand shown to assigned clients:
 *   logo (WebP + fallback), primary color, nombre comercial, tagline.
 *
 * Endpoints are scoped to the authenticated coach — no cross-coach access.
 */
class CoachBrandController extends Controller
{
    use AuthenticatesVueRequests;

    private const LOGO_DISK = 'public';

    private const LOGO_DIRECTORY = 'coach-logos';

    private const LOGO_MAX_WIDTH = 1000;

    private const LOGO_QUALITY = 85;

    public function __construct(
        private readonly ImagePipelineService $imagePipeline,
    ) {}

    /**
     * GET /api/v/coach/brand
     *
     * Return the branding payload for the authenticated coach.
     * Auto-provisions a CoachProfile row the first time it's requested.
     */
    public function show(Request $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);
        $profile = $this->getOrCreateProfile($coach);

        return response()->json($this->brandPayload($profile));
    }

    /**
     * PUT /api/v/coach/brand
     *
     * Update text-based branding fields. Logo is handled by separate endpoints.
     */
    public function update(Request $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $validated = $request->validate([
            'primary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'nombre_comercial' => ['nullable', 'string', 'max:150'],
            'tagline' => ['nullable', 'string', 'max:250'],
        ]);

        $profile = $this->getOrCreateProfile($coach);

        $updates = [];
        if (array_key_exists('primary_color', $validated)) {
            $updates['color_primary'] = $validated['primary_color'];
        }
        if (array_key_exists('nombre_comercial', $validated)) {
            $updates['nombre_comercial'] = $validated['nombre_comercial'];
        }
        if (array_key_exists('tagline', $validated)) {
            $updates['tagline'] = $validated['tagline'];
        }

        if ($updates !== []) {
            $profile->update($updates);
            $profile->refresh();
        }

        return response()->json([
            'updated' => true,
            'brand' => $this->brandPayload($profile),
        ]);
    }

    /**
     * POST /api/v/coach/brand/logo
     *
     * Upload a new coach logo. Replaces the previous one (deleting artifacts).
     * Returns old URLs alongside new ones so the client can transition UI state.
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $request->validate([
            'logo' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $profile = $this->getOrCreateProfile($coach);

        $oldUrls = [
            'logo_url' => $profile->logo_url,
            'logo_url_webp' => $profile->logo_url_webp,
        ];
        $oldPathWebp = $profile->logo_path_webp;
        $oldPathFallback = $profile->logo_path_fallback;

        try {
            $result = $this->imagePipeline->processUpload(
                file: $request->file('logo'),
                disk: self::LOGO_DISK,
                directory: self::LOGO_DIRECTORY,
                maxWidth: self::LOGO_MAX_WIDTH,
                quality: self::LOGO_QUALITY,
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $profile->update([
            'logo_url' => $result['url_fallback'],
            'logo_url_webp' => $result['url_webp'],
            'logo_path_webp' => $result['path_webp'],
            'logo_path_fallback' => $result['path_fallback'],
        ]);
        $profile->refresh();

        // Clean up old artifacts only after DB commit, so we never delete the
        // live image if the update fails.
        if ($oldPathWebp || $oldPathFallback) {
            $this->imagePipeline->delete(
                pathWebp: $oldPathWebp ?? '',
                pathFallback: $oldPathFallback ?? '',
                disk: self::LOGO_DISK,
            );
        }

        return response()->json([
            'uploaded' => true,
            'old' => $oldUrls,
            'new' => [
                'logo_url' => $profile->logo_url,
                'logo_url_webp' => $profile->logo_url_webp,
            ],
            'brand' => $this->brandPayload($profile),
        ], 201);
    }

    /**
     * DELETE /api/v/coach/brand/logo
     *
     * Remove the coach logo (both WebP + fallback artifacts) and clear columns.
     */
    public function deleteLogo(Request $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);
        $profile = $this->getOrCreateProfile($coach);

        if (! $profile->logo_path_webp && ! $profile->logo_path_fallback && ! $profile->logo_url) {
            return response()->json(['deleted' => false, 'reason' => 'no-logo'], 200);
        }

        $this->imagePipeline->delete(
            pathWebp: $profile->logo_path_webp ?? '',
            pathFallback: $profile->logo_path_fallback ?? '',
            disk: self::LOGO_DISK,
        );

        $profile->update([
            'logo_url' => null,
            'logo_url_webp' => null,
            'logo_path_webp' => null,
            'logo_path_fallback' => null,
        ]);

        return response()->json(['deleted' => true]);
    }

    // ─── Helpers ────────────────────────────────────────────────────────

    /**
     * Resolve the authenticated coach/admin or abort.
     */
    private function resolveCoachOrFail(Request $request): Admin
    {
        $auth = $this->resolveAuthUser($request);

        if (! $auth) {
            abort(401, 'Token invalido o expirado.');
        }

        if ($auth['userType'] !== UserType::Admin) {
            abort(403, 'Acceso solo para coaches.');
        }

        $admin = $auth['user'];
        $role = $admin->role?->value ?? $admin->role ?? '';

        if (! in_array($role, ['coach', 'admin', 'superadmin', 'jefe'], true)) {
            abort(403, 'No tienes permisos de coach.');
        }

        return $admin;
    }

    private function getOrCreateProfile(Admin $coach): CoachProfile
    {
        $profile = CoachProfile::where('admin_id', $coach->id)->first();

        if ($profile) {
            return $profile;
        }

        return CoachProfile::create([
            'admin_id' => $coach->id,
            'slug' => Str::slug($coach->name ?: 'coach').'-'.Str::random(4),
            'color_primary' => '#DC2626',
            'public_visible' => true,
        ]);
    }

    /**
     * Serialize brand payload shown to coach UI.
     */
    private function brandPayload(CoachProfile $profile): array
    {
        return [
            'logo_url' => $profile->logo_url,
            'logo_url_webp' => $profile->logo_url_webp,
            'primary_color' => $profile->color_primary ?? '#DC2626',
            'nombre_comercial' => $profile->nombre_comercial,
            'tagline' => $profile->tagline,
        ];
    }
}
