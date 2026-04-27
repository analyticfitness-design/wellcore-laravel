<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\ClientPulso;
use App\Models\ClientPulsoReaction;
use App\Models\ClientPulsoView;
use App\Services\ImagePipelineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PulsoController extends Controller
{
    use AuthenticatesVueRequests;

    /**
     * GET /api/v/client/pulsos
     * Lista los pulsos activos de la comunidad, 1 por cliente (el más reciente).
     */
    public function index(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $pulsos = Cache::remember('community:pulsos', 60, function () {
            return ClientPulso::where('expires_at', '>', now())
                ->with('client:id,name')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
                ->groupBy('client_id')
                ->map(fn ($group) => $group->first())
                ->values()
                ->map(fn (ClientPulso $p) => [
                    'id'          => $p->id,
                    'client_id'   => $p->client_id,
                    'client_name' => $p->client?->name ?? 'Miembro',
                    'initials'    => mb_strtoupper(mb_substr(trim($p->client?->name ?? 'M'), 0, 2)),
                    'pulso_type'  => $p->pulso_type,
                    'ring_color'  => ClientPulso::ringColorForType($p->pulso_type),
                    'media_type'  => $p->media_type,
                    'has_media'   => $p->media_url !== null,
                    'expires_at'  => $p->expires_at->toIso8601String(),
                    'caption'     => $p->caption,
                ])
                ->all();
        });

        $viewedIds = ClientPulsoView::where('viewer_id', $clientId)
            ->pluck('pulso_id')
            ->toArray();

        $pulsos = array_map(function ($p) use ($viewedIds, $clientId) {
            $p['has_new'] = ! in_array($p['id'], $viewedIds, true) && $p['client_id'] !== $clientId;
            return $p;
        }, $pulsos);

        return response()->json(['pulsos' => $pulsos]);
    }

    /**
     * GET /api/v/client/pulsos/{id}
     * Devuelve el pulso completo y registra el view.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $pulso = ClientPulso::where('id', $id)
            ->where('expires_at', '>', now())
            ->with('client:id,name')
            ->first();

        if (! $pulso) {
            return response()->json(['message' => 'Este pulso ya expiró o no existe.'], 404);
        }

        if ($pulso->client_id !== $clientId) {
            $isNew = ! ClientPulsoView::where('pulso_id', $id)
                ->where('viewer_id', $clientId)
                ->exists();

            if ($isNew) {
                ClientPulsoView::create([
                    'pulso_id'  => $id,
                    'viewer_id' => $clientId,
                    'viewed_at' => now(),
                ]);
                ClientPulso::where('id', $id)->increment('views_count');
                Cache::forget('community:pulsos');
            }
        }

        $reactionCounts = ClientPulsoReaction::where('pulso_id', $id)
            ->selectRaw('reaction_type, COUNT(*) as total')
            ->groupBy('reaction_type')
            ->pluck('total', 'reaction_type');

        $myReactions = ClientPulsoReaction::where('pulso_id', $id)
            ->where('client_id', $clientId)
            ->pluck('reaction_type')
            ->toArray();

        $mediaFullUrl = null;
        if ($pulso->media_url) {
            $mediaFullUrl = Storage::disk('private')->exists($pulso->media_url)
                ? url('/api/v/client/pulsos/'.$id.'/media')
                : asset('storage/'.$pulso->media_url);
        }

        $data = [
            'id'                => $pulso->id,
            'client_id'         => $pulso->client_id,
            'client_name'       => $pulso->client?->name ?? 'Miembro',
            'initials'          => mb_strtoupper(mb_substr(trim($pulso->client?->name ?? 'M'), 0, 2)),
            'pulso_type'        => $pulso->pulso_type,
            'ring_color'        => ClientPulso::ringColorForType($pulso->pulso_type),
            'media_type'        => $pulso->media_type,
            'media_url'         => $mediaFullUrl,
            'caption'           => $pulso->caption,
            'stats_overlay'     => $pulso->stats_overlay,
            'expires_at'        => $pulso->expires_at->toIso8601String(),
            'views_count'       => $pulso->views_count,
            'reaction_counts'   => $reactionCounts,
            'my_reactions'      => $myReactions,
            'is_mine'           => $pulso->client_id === $clientId,
            'is_auto_generated' => $pulso->is_auto_generated,
        ];

        if ($pulso->client_id === $clientId) {
            $data['viewers'] = ClientPulsoView::where('pulso_id', $id)
                ->with('viewer:id,name')
                ->orderByDesc('viewed_at')
                ->limit(20)
                ->get()
                ->map(fn ($v) => [
                    'name'      => $v->viewer?->name ?? 'Miembro',
                    'initials'  => mb_strtoupper(mb_substr(trim($v->viewer?->name ?? 'M'), 0, 2)),
                    'viewed_at' => $v->viewed_at?->toIso8601String(),
                ])
                ->toArray();
        }

        return response()->json(['pulso' => $data]);
    }

    /**
     * POST /api/v/client/pulsos
     * Crea un pulso con o sin media.
     */
    public function store(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'pulso_type'                  => ['required', Rule::in(['entrenamiento', 'pr', 'nutricion', 'recuperacion', 'logro', 'libre'])],
            'caption'                     => 'nullable|string|max:200',
            'workout_session_id'          => 'nullable|integer',
            'stats_overlay'               => 'nullable|array',
            'stats_overlay.volume_kg'     => 'nullable|numeric',
            'stats_overlay.series'        => 'nullable|integer',
            'stats_overlay.ejercicios'    => 'nullable|integer',
            'stats_overlay.duracion_min'  => 'nullable|integer',
            'stats_overlay.day_name'      => 'nullable|string|max:100',
            'media'                       => 'nullable|file|mimes:jpeg,jpg,png,webp,mp4,mov|max:30720',
        ]);

        $pulsoType = $request->input('pulso_type');
        $mediaUrl  = null;
        $mediaType = 'stat_card';

        if ($request->hasFile('media')) {
            $file      = $request->file('media');
            $extension = strtolower($file->getClientOriginalExtension());
            $isVideo   = in_array($extension, ['mp4', 'mov'], true);

            if ($isVideo) {
                $mediaUrl  = $file->store("pulsos/{$clientId}", 'private');
                $mediaType = 'video';
            } else {
                try {
                    $result    = app(ImagePipelineService::class)->processUpload(
                        file: $file,
                        disk: 'private',
                        directory: "pulsos/{$clientId}",
                        maxWidth: 1080,
                        quality: 85,
                    );
                    $mediaUrl  = $result['path_webp'];
                    $mediaType = 'photo';
                } catch (\Throwable $e) {
                    Log::error('pulso image upload failed', ['client_id' => $clientId, 'error' => $e->getMessage()]);
                    return response()->json(['message' => 'No pudimos procesar la imagen. Intenta de nuevo.'], 422);
                }
            }
        }

        $pulso = ClientPulso::create([
            'client_id'          => $clientId,
            'pulso_type'         => $pulsoType,
            'media_url'          => $mediaUrl,
            'media_type'         => $mediaType,
            'caption'            => $request->input('caption'),
            'workout_session_id' => $request->input('workout_session_id'),
            'stats_overlay'      => $request->input('stats_overlay'),
            'expires_at'         => ClientPulso::expiryForType($pulsoType),
            'is_auto_generated'  => $request->boolean('is_auto_generated', false),
        ]);

        Cache::forget('community:pulsos');

        return response()->json([
            'id'         => $pulso->id,
            'expires_at' => $pulso->expires_at->toIso8601String(),
        ], 201);
    }

    /**
     * GET /api/v/client/pulsos/{id}/media
     * Sirve el archivo media del pulso (accesible a cualquier miembro autenticado).
     */
    public function media(Request $request, int $id)
    {
        $this->resolveClientOrFail($request);

        $pulso = ClientPulso::where('id', $id)
            ->where('expires_at', '>', now())
            ->first();

        if (! $pulso || ! $pulso->media_url) {
            return response()->json(['message' => 'Media no disponible.'], 404);
        }

        if (Storage::disk('private')->exists($pulso->media_url)) {
            return Storage::disk('private')->response($pulso->media_url);
        }

        return response()->json(['message' => 'Archivo no encontrado.'], 404);
    }

    /**
     * POST /api/v/client/pulsos/{id}/react
     * Toggle reacción en un pulso.
     */
    public function react(Request $request, int $id): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'reaction_type' => ['required', Rule::in(['fire', 'muscle', 'trophy', 'energy'])],
        ]);

        $pulso = ClientPulso::where('id', $id)
            ->where('expires_at', '>', now())
            ->first();

        if (! $pulso) {
            return response()->json(['message' => 'Este pulso ya expiró.'], 404);
        }

        $existing = ClientPulsoReaction::where('pulso_id', $id)
            ->where('client_id', $clientId)
            ->where('reaction_type', $request->input('reaction_type'))
            ->first();

        if ($existing) {
            $existing->delete();
            $toggled = false;
        } else {
            ClientPulsoReaction::create([
                'pulso_id'      => $id,
                'client_id'     => $clientId,
                'reaction_type' => $request->input('reaction_type'),
            ]);
            $toggled = true;
        }

        $counts = ClientPulsoReaction::where('pulso_id', $id)
            ->selectRaw('reaction_type, COUNT(*) as total')
            ->groupBy('reaction_type')
            ->pluck('total', 'reaction_type');

        return response()->json([
            'toggled'         => $toggled,
            'reaction_type'   => $request->input('reaction_type'),
            'reaction_counts' => $counts,
        ]);
    }

    /**
     * DELETE /api/v/client/pulsos/{id}
     * Elimina un pulso propio y su media.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $pulso = ClientPulso::where('id', $id)
            ->where('client_id', $client->id)
            ->first();

        if (! $pulso) {
            return response()->json(['message' => 'Pulso no encontrado.'], 404);
        }

        if ($pulso->media_url) {
            Storage::disk('private')->delete($pulso->media_url);
        }

        $pulso->delete();
        Cache::forget('community:pulsos');

        return response()->json(['deleted' => true]);
    }
}
