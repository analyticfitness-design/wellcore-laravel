# WellCore PULSOS — Sistema de Momentos Efímeros de Comunidad

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implementar "PULSOS" — sistema de contenido efímero 24-48h único de WellCore donde los miembros comparten su energía de entrenamiento con foto opcional, stat overlay automático desde WorkoutSession, y anillo de avatar con color según tipo.

**Architecture:** 3 nuevas tablas aditivas (`client_pulsos`, `client_pulso_views`, `client_pulso_reactions`), `PulsoController` separado del sobrecargado `SocialController`, 4 componentes Vue nuevos (`PulsoRing`, `PulsoViewer`, `PulsoUploader`, `PulsoStatCard`) integrados en `CommunityFeed.vue`. El hook en `TrainingController::finishWorkout` agrega `pulso_offer` al response para disparar el uploader automáticamente post-workout.

**Tech Stack:** Laravel 13 + PHP 8.4, MySQL (migraciones aditivas), Vue 3.5 + TypeScript + Tailwind CSS 4, Vite 8, disco `private` de Storage para media.

---

## Mapa de Archivos

### Crear
- `database/migrations/2026_04_27_000001_create_client_pulsos_tables.php`
- `app/Models/ClientPulso.php`
- `app/Models/ClientPulsoView.php`
- `app/Models/ClientPulsoReaction.php`
- `app/Http/Controllers/Api/PulsoController.php`
- `resources/js/vue/components/Community/PulsoRing.vue`
- `resources/js/vue/components/Community/PulsoViewer.vue`
- `resources/js/vue/components/Community/PulsoUploader.vue`
- `resources/js/vue/components/Community/PulsoStatCard.vue`

### Modificar
- `app/Http/Controllers/Api/TrainingController.php` — agregar `pulso_offer` al response de `finishWorkout`
- `app/Http/Controllers/Api/SocialController.php` — reemplazar bloque `community:stories` con datos de `client_pulsos`
- `routes/api.php` — registrar rutas de pulsos
- `resources/js/vue/pages/Client/CommunityFeed.vue` — integrar PulsoRing, PulsoViewer, PulsoUploader

---

## Concepto Visual PULSOS

```
[Anillo animado rojo]  ← entrenamiento (streak <7 días)
[Anillo animado dorado] ← streak ≥7 días o PR
[Anillo animado verde]  ← nutrición
[Anillo animado azul]   ← recuperación
[Anillo gris]           ← libre / sin pulso activo

Tipos de Pulso:
🔥 entrenamiento — ring rojo,   expira 24h
🏆 pr           — ring dorado,  expira 48h
🥗 nutricion    — ring verde,   expira 24h
😴 recuperacion — ring azul,    expira 24h
🏅 logro        — ring morado,  expira 24h
📸 libre        — ring gris,    expira 24h

Reacciones exclusivas:
fire=🔥  muscle=💪  trophy=🏆  energy=⚡
```

---

## Task 1: Migraciones DB (3 tablas aditivas)

**Files:**
- Create: `database/migrations/2026_04_27_000001_create_client_pulsos_tables.php`

- [ ] **Crear el archivo de migración**

```php
<?php
// database/migrations/2026_04_27_000001_create_client_pulsos_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_pulsos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->enum('pulso_type', ['entrenamiento', 'pr', 'nutricion', 'recuperacion', 'logro', 'libre'])
                  ->default('libre');
            $table->string('media_url', 500)->nullable();
            $table->enum('media_type', ['photo', 'video', 'stat_card'])->default('stat_card');
            $table->string('caption', 200)->nullable();
            $table->unsignedBigInteger('workout_session_id')->nullable();
            $table->json('stats_overlay')->nullable();
            // stats_overlay shape: {"volume_kg":6042.0,"series":36,"ejercicios":5,"duracion_min":45,"day_name":"Pecho + Hombros"}
            $table->timestamp('expires_at');
            $table->boolean('is_auto_generated')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            $table->index(['client_id', 'expires_at'], 'idx_pulso_client_expires');
            $table->index('expires_at', 'idx_pulso_expires');
        });

        Schema::create('client_pulso_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pulso_id');
            $table->unsignedBigInteger('viewer_id');
            $table->timestamp('viewed_at')->useCurrent();

            $table->unique(['pulso_id', 'viewer_id'], 'uq_pulso_viewer');
            $table->index('pulso_id', 'idx_pview_pulso');
        });

        Schema::create('client_pulso_reactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pulso_id');
            $table->unsignedBigInteger('client_id');
            $table->enum('reaction_type', ['fire', 'muscle', 'trophy', 'energy']);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['pulso_id', 'client_id', 'reaction_type'], 'uq_pulso_client_reaction');
            $table->index('pulso_id', 'idx_preaction_pulso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_pulso_reactions');
        Schema::dropIfExists('client_pulso_views');
        Schema::dropIfExists('client_pulsos');
    }
};
```

- [ ] **Ejecutar migración**

```bash
cd C:\Users\GODSF\Herd\wellcore-laravel
php artisan migrate --path=database/migrations/2026_04_27_000001_create_client_pulsos_tables.php
```

Esperado: `Migrating: 2026_04_27_000001... Migrated`

- [ ] **Commit**

```bash
git add database/migrations/2026_04_27_000001_create_client_pulsos_tables.php
git commit -m "feat(pulsos): migraciones aditivas client_pulsos, views y reactions"
```

---

## Task 2: Modelos Eloquent

**Files:**
- Create: `app/Models/ClientPulso.php`
- Create: `app/Models/ClientPulsoView.php`
- Create: `app/Models/ClientPulsoReaction.php`

- [ ] **Crear `app/Models/ClientPulso.php`**

```php
<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPulso extends Model
{
    protected $table = 'client_pulsos';

    protected $fillable = [
        'client_id',
        'pulso_type',
        'media_url',
        'media_type',
        'caption',
        'workout_session_id',
        'stats_overlay',
        'expires_at',
        'is_auto_generated',
        'views_count',
    ];

    protected $casts = [
        'stats_overlay'      => 'array',
        'expires_at'         => 'datetime',
        'is_auto_generated'  => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(ClientPulsoView::class, 'pulso_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ClientPulsoReaction::class, 'pulso_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /** Duración de expiración según tipo de pulso */
    public static function expiryForType(string $type): Carbon
    {
        return match ($type) {
            'pr'    => now()->addHours(48),
            default => now()->addHours(24),
        };
    }

    /** Color del anillo según tipo */
    public static function ringColorForType(string $type): string
    {
        return match ($type) {
            'entrenamiento' => 'red',
            'pr'            => 'gold',
            'nutricion'     => 'green',
            'recuperacion'  => 'blue',
            'logro'         => 'purple',
            default         => 'gray',
        };
    }
}
```

- [ ] **Crear `app/Models/ClientPulsoView.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPulsoView extends Model
{
    protected $table = 'client_pulso_views';

    public $timestamps = false;

    protected $fillable = ['pulso_id', 'viewer_id', 'viewed_at'];

    protected $casts = ['viewed_at' => 'datetime'];

    public function pulso(): BelongsTo
    {
        return $this->belongsTo(ClientPulso::class, 'pulso_id');
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'viewer_id');
    }
}
```

- [ ] **Crear `app/Models/ClientPulsoReaction.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPulsoReaction extends Model
{
    protected $table = 'client_pulso_reactions';

    public $timestamps = false;

    protected $fillable = ['pulso_id', 'client_id', 'reaction_type'];

    public function pulso(): BelongsTo
    {
        return $this->belongsTo(ClientPulso::class, 'pulso_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
```

- [ ] **Commit**

```bash
git add app/Models/ClientPulso.php app/Models/ClientPulsoView.php app/Models/ClientPulsoReaction.php
git commit -m "feat(pulsos): modelos Eloquent ClientPulso, View y Reaction"
```

---

## Task 3: PulsoController — API REST

**Files:**
- Create: `app/Http/Controllers/Api/PulsoController.php`

- [ ] **Crear el controller completo**

```php
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

    // ─── Lista de pulsos activos de la comunidad ───────────────────────

    /**
     * GET /api/v/client/pulsos
     *
     * Devuelve los pulsos activos (no expirados) de los miembros de la comunidad.
     * Agrupa por cliente para el Stories Row: máx 1 pulso por cliente (el más reciente).
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
                    'id'         => $p->id,
                    'client_id'  => $p->client_id,
                    'client_name' => $p->client?->name ?? 'Miembro',
                    'initials'   => mb_strtoupper(mb_substr(trim($p->client?->name ?? 'M'), 0, 2)),
                    'pulso_type' => $p->pulso_type,
                    'ring_color' => ClientPulso::ringColorForType($p->pulso_type),
                    'media_type' => $p->media_type,
                    'has_media'  => $p->media_url !== null,
                    'expires_at' => $p->expires_at->toIso8601String(),
                    'caption'    => $p->caption,
                ])
                ->all();
        });

        // Marcar cuáles ya vio el cliente actual
        $viewedIds = ClientPulsoView::where('viewer_id', $clientId)
            ->pluck('pulso_id')
            ->toArray();

        $pulsos = array_map(function ($p) use ($viewedIds, $clientId) {
            $p['has_new'] = ! in_array($p['id'], $viewedIds, true) && $p['client_id'] !== $clientId;
            return $p;
        }, $pulsos);

        return response()->json(['pulsos' => $pulsos]);
    }

    // ─── Ver un pulso individual (registra view) ───────────────────────

    /**
     * GET /api/v/client/pulsos/{id}
     *
     * Devuelve el pulso completo con stats_overlay, reacciones y viewers (si es el owner).
     * Registra el view automáticamente.
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

        // Registrar view (upsert para evitar duplicados)
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

        // Conteos de reacciones
        $reactionCounts = ClientPulsoReaction::where('pulso_id', $id)
            ->selectRaw('reaction_type, COUNT(*) as total')
            ->groupBy('reaction_type')
            ->pluck('total', 'reaction_type');

        $myReactions = ClientPulsoReaction::where('pulso_id', $id)
            ->where('client_id', $clientId)
            ->pluck('reaction_type')
            ->toArray();

        // Media URL — si tiene archivo en disco privado, proxy autenticado
        $mediaFullUrl = null;
        if ($pulso->media_url) {
            $mediaFullUrl = Storage::disk('private')->exists($pulso->media_url)
                ? url('/api/v/client/pulsos/'.$id.'/media')
                : asset('storage/'.$pulso->media_url);
        }

        $data = [
            'id'               => $pulso->id,
            'client_id'        => $pulso->client_id,
            'client_name'      => $pulso->client?->name ?? 'Miembro',
            'initials'         => mb_strtoupper(mb_substr(trim($pulso->client?->name ?? 'M'), 0, 2)),
            'pulso_type'       => $pulso->pulso_type,
            'ring_color'       => ClientPulso::ringColorForType($pulso->pulso_type),
            'media_type'       => $pulso->media_type,
            'media_url'        => $mediaFullUrl,
            'caption'          => $pulso->caption,
            'stats_overlay'    => $pulso->stats_overlay,
            'expires_at'       => $pulso->expires_at->toIso8601String(),
            'views_count'      => $pulso->views_count,
            'reaction_counts'  => $reactionCounts,
            'my_reactions'     => $myReactions,
            'is_mine'          => $pulso->client_id === $clientId,
            'is_auto_generated' => $pulso->is_auto_generated,
        ];

        // Viewers — solo para el owner
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

    // ─── Crear pulso ───────────────────────────────────────────────────

    /**
     * POST /api/v/client/pulsos
     *
     * Crea un pulso con o sin media. Si es stat_card, genera la tarjeta desde stats_overlay.
     * Si tiene archivo, lo procesa y almacena en disco privado.
     */
    public function store(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'pulso_type'         => ['required', Rule::in(['entrenamiento', 'pr', 'nutricion', 'recuperacion', 'logro', 'libre'])],
            'caption'            => 'nullable|string|max:200',
            'workout_session_id' => 'nullable|integer',
            'stats_overlay'      => 'nullable|array',
            'stats_overlay.volume_kg'    => 'nullable|numeric',
            'stats_overlay.series'       => 'nullable|integer',
            'stats_overlay.ejercicios'   => 'nullable|integer',
            'stats_overlay.duracion_min' => 'nullable|integer',
            'stats_overlay.day_name'     => 'nullable|string|max:100',
            'media'              => 'nullable|file|mimes:jpeg,jpg,png,webp,mp4,mov|max:30720',
        ]);

        $pulsoType = $request->input('pulso_type');
        $mediaUrl = null;
        $mediaType = 'stat_card';

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $extension = strtolower($file->getClientOriginalExtension());
            $isVideo = in_array($extension, ['mp4', 'mov'], true);

            if ($isVideo) {
                $mediaUrl  = $file->store("pulsos/{$clientId}", 'private');
                $mediaType = 'video';
            } else {
                try {
                    $result = app(ImagePipelineService::class)->processUpload(
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

    // ─── Servir media privada ──────────────────────────────────────────

    /**
     * GET /api/v/client/pulsos/{id}/media
     *
     * Sirve el archivo media del pulso al dueño o a cualquier miembro de la comunidad
     * (el pulso ya es semipúblico dentro de la plataforma).
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

    // ─── Toggle reacción ───────────────────────────────────────────────

    /**
     * POST /api/v/client/pulsos/{id}/react
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
            'toggled'          => $toggled,
            'reaction_type'    => $request->input('reaction_type'),
            'reaction_counts'  => $counts,
        ]);
    }

    // ─── Eliminar pulso propio ─────────────────────────────────────────

    /**
     * DELETE /api/v/client/pulsos/{id}
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
```

- [ ] **Commit**

```bash
git add app/Http/Controllers/Api/PulsoController.php
git commit -m "feat(pulsos): PulsoController con CRUD, media serving y react"
```

---

## Task 4: Registrar Rutas de Pulsos + Hook en finishWorkout

**Files:**
- Modify: `routes/api.php`
- Modify: `app/Http/Controllers/Api/TrainingController.php`

- [ ] **Agregar rutas en `routes/api.php`**

Buscar el bloque de rutas de client community (alrededor de la línea con `/v/client/community`) y agregar DESPUÉS:

```php
// Pulsos WellCore
Route::get('/v/client/pulsos', [PulsoController::class, 'index']);
Route::post('/v/client/pulsos', [PulsoController::class, 'store']);
Route::get('/v/client/pulsos/{id}', [PulsoController::class, 'show']);
Route::get('/v/client/pulsos/{id}/media', [PulsoController::class, 'media']);
Route::post('/v/client/pulsos/{id}/react', [PulsoController::class, 'react']);
Route::delete('/v/client/pulsos/{id}', [PulsoController::class, 'destroy']);
```

Y agregar el import al top del archivo junto a los demás controllers:

```php
use App\Http\Controllers\Api\PulsoController;
```

- [ ] **Modificar `TrainingController::finishWorkout`**

Al final del método `finishWorkout` en `app/Http/Controllers/Api/TrainingController.php`, ANTES del `return response()->json(...)` final, agregar la construcción del `pulso_offer`:

```php
// Construir oferta de auto-pulso con los datos de la sesión completada
$pulsoOffer = [
    'session_id'  => $session->id,
    'pulso_type'  => 'entrenamiento',
    'stats'       => [
        'volume_kg'    => round((float) $session->total_volume_kg, 1),
        'series'       => (int) $session->total_sets,
        'ejercicios'   => $session->logs()->where('completed', true)->distinct('exercise_name')->count('exercise_name'),
        'duracion_min' => (int) round($durationSec / 60),
        'day_name'     => $session->day_name ?? '',
    ],
];
```

Y en el array del `return response()->json([...])` agregar:
```php
'pulso_offer' => $pulsoOffer,
```

- [ ] **Verificar que las rutas se registraron**

```bash
php artisan route:list | grep pulsos
```

Esperado: 6 rutas listadas para `/api/v/client/pulsos`

- [ ] **Commit**

```bash
git add routes/api.php app/Http/Controllers/Api/TrainingController.php
git commit -m "feat(pulsos): rutas API y hook pulso_offer en finishWorkout"
```

---

## Task 5: Componente PulsoStatCard.vue

Tarjeta visual generada cuando el pulso no tiene foto — gradiente rojo/negro WellCore con stats overlay.

**Files:**
- Create: `resources/js/vue/components/Community/PulsoStatCard.vue`

- [ ] **Crear el componente**

```bash
mkdir -p resources/js/vue/components/Community
```

```vue
<!-- resources/js/vue/components/Community/PulsoStatCard.vue -->
<script setup lang="ts">
interface StatsOverlay {
  volume_kg?: number;
  series?: number;
  ejercicios?: number;
  duracion_min?: number;
  day_name?: string;
}

interface Props {
  pulsoType: string;
  caption?: string;
  stats?: StatsOverlay | null;
  clientName?: string;
  compact?: boolean; // true = vista miniatura en el ring
}

const props = withDefaults(defineProps<Props>(), {
  compact: false,
});

const typeConfig: Record<string, { emoji: string; label: string; gradient: string }> = {
  entrenamiento: { emoji: '🔥', label: 'ENTRENAMIENTO', gradient: 'from-red-950 via-zinc-900 to-black' },
  pr:            { emoji: '🏆', label: 'NUEVO PR',       gradient: 'from-yellow-900 via-zinc-900 to-black' },
  nutricion:     { emoji: '🥗', label: 'NUTRICIÓN',      gradient: 'from-green-950 via-zinc-900 to-black' },
  recuperacion:  { emoji: '😴', label: 'RECUPERACIÓN',   gradient: 'from-blue-950 via-zinc-900 to-black' },
  logro:         { emoji: '🏅', label: 'LOGRO',          gradient: 'from-purple-950 via-zinc-900 to-black' },
  libre:         { emoji: '📸', label: 'MOMENTO',        gradient: 'from-zinc-800 via-zinc-900 to-black' },
};

const config = typeConfig[props.pulsoType] ?? typeConfig.libre;
</script>

<template>
  <div
    :class="[
      'relative flex flex-col items-center justify-center overflow-hidden bg-gradient-to-br text-white',
      config.gradient,
      compact ? 'h-full w-full rounded-2xl' : 'min-h-[340px] w-full rounded-2xl p-6',
    ]"
  >
    <!-- Ruido de fondo sutil -->
    <div class="absolute inset-0 opacity-5"
         style="background-image:url('data:image/svg+xml,%3Csvg viewBox=\'0 0 200 200\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cfilter id=\'n\'%3E%3CfeTurbulence type=\'fractalNoise\' baseFrequency=\'0.9\' numOctaves=\'4\'/%3E%3C/filter%3E%3Crect width=\'100%25\' height=\'100%25\' filter=\'url(%23n)\'/%3E%3C/svg%3E')"></div>

    <!-- Compact: solo emoji + tipo -->
    <template v-if="compact">
      <span class="text-2xl">{{ config.emoji }}</span>
      <span class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/80">{{ config.label }}</span>
    </template>

    <!-- Full view: stats + caption -->
    <template v-else>
      <p class="mb-1 text-xs font-bold uppercase tracking-[0.3em] text-white/50">{{ config.label }}</p>
      <span class="mb-4 text-5xl">{{ config.emoji }}</span>

      <!-- Stats grid -->
      <div v-if="stats && Object.values(stats).some(Boolean)" class="mb-4 grid w-full grid-cols-2 gap-2">
        <div v-if="stats.volume_kg" class="flex flex-col items-center rounded-xl bg-white/10 px-3 py-2 backdrop-blur-sm">
          <span class="text-xl font-black text-wc-accent">{{ stats.volume_kg.toLocaleString('es') }}</span>
          <span class="text-[10px] font-semibold uppercase tracking-widest text-white/60">KG VOLUMEN</span>
        </div>
        <div v-if="stats.series" class="flex flex-col items-center rounded-xl bg-white/10 px-3 py-2 backdrop-blur-sm">
          <span class="text-xl font-black text-wc-accent">{{ stats.series }}</span>
          <span class="text-[10px] font-semibold uppercase tracking-widest text-white/60">SERIES</span>
        </div>
        <div v-if="stats.ejercicios" class="flex flex-col items-center rounded-xl bg-white/10 px-3 py-2 backdrop-blur-sm">
          <span class="text-xl font-black text-wc-accent">{{ stats.ejercicios }}</span>
          <span class="text-[10px] font-semibold uppercase tracking-widest text-white/60">EJERCICIOS</span>
        </div>
        <div v-if="stats.duracion_min" class="flex flex-col items-center rounded-xl bg-white/10 px-3 py-2 backdrop-blur-sm">
          <span class="text-xl font-black text-wc-accent">{{ stats.duracion_min }}</span>
          <span class="text-[10px] font-semibold uppercase tracking-widest text-white/60">MINUTOS</span>
        </div>
      </div>

      <p v-if="stats?.day_name" class="mb-2 text-center text-sm font-semibold text-white/80">
        {{ stats.day_name }}
      </p>
      <p v-if="caption" class="text-center text-sm italic text-white/70">"{{ caption }}"</p>
      <p v-if="clientName" class="mt-4 text-xs text-white/40">— {{ clientName }}</p>
    </template>
  </div>
</template>
```

- [ ] **Commit**

```bash
git add resources/js/vue/components/Community/PulsoStatCard.vue
git commit -m "feat(pulsos): componente PulsoStatCard con stats overlay WellCore"
```

---

## Task 6: Componente PulsoRing.vue

Avatar con anillo animado de color por tipo. Si el miembro no tiene pulso activo, anillo gris sin animación.

**Files:**
- Create: `resources/js/vue/components/Community/PulsoRing.vue`

- [ ] **Crear el componente**

```vue
<!-- resources/js/vue/components/Community/PulsoRing.vue -->
<script setup lang="ts">
interface Props {
  name: string;
  initials: string;
  ringColor: 'red' | 'gold' | 'green' | 'blue' | 'purple' | 'gray';
  hasNew: boolean;
  pulsoId?: number | null;
  size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  pulsoId: null,
});

const emit = defineEmits<{ click: [] }>();

const ringClasses: Record<string, string> = {
  red:    'bg-gradient-to-tr from-wc-accent via-orange-500 to-yellow-400',
  gold:   'bg-gradient-to-tr from-yellow-400 via-amber-500 to-yellow-300',
  green:  'bg-gradient-to-tr from-green-500 via-emerald-400 to-teal-400',
  blue:   'bg-gradient-to-tr from-blue-500 via-cyan-400 to-sky-400',
  purple: 'bg-gradient-to-tr from-purple-600 via-violet-500 to-purple-400',
  gray:   'bg-zinc-700',
};

const sizeClasses = {
  sm: { wrap: 'w-12 h-12', ring: 'p-[2px]', avatar: 'w-10 h-10 text-xs' },
  md: { wrap: 'w-16 h-16', ring: 'p-[2.5px]', avatar: 'w-[52px] h-[52px] text-sm' },
  lg: { wrap: 'w-20 h-20', ring: 'p-[3px]', avatar: 'w-[68px] h-[68px] text-base' },
};
</script>

<template>
  <button
    :class="['flex flex-col items-center gap-1.5 focus:outline-none', !hasNew && 'opacity-70']"
    @click="emit('click')"
  >
    <!-- Ring wrapper -->
    <div :class="[sizeClasses[size].wrap, 'relative rounded-full']">
      <!-- Animated ring -->
      <div
        :class="[
          'absolute inset-0 rounded-full',
          ringClasses[ringColor] ?? ringClasses.gray,
          hasNew ? 'animate-spin-slow' : '',
        ]"
        style="animation-duration: 3s;"
      ></div>
      <!-- Avatar inner -->
      <div
        :class="[
          sizeClasses[size].ring,
          'absolute inset-0 rounded-full',
        ]"
      >
        <div
          :class="[
            sizeClasses[size].avatar,
            'flex items-center justify-center rounded-full bg-wc-bg-secondary font-bold uppercase text-wc-text',
          ]"
        >
          {{ initials }}
        </div>
      </div>
    </div>
    <!-- Name -->
    <span class="max-w-[56px] truncate text-center text-[10px] text-wc-text-secondary">
      {{ name.split(' ')[0] }}
    </span>
  </button>
</template>
```

Luego agregar la animación `animate-spin-slow` en `resources/css/app.css` dentro del bloque `@theme` o al final del archivo:

```css
/* Pulso ring animation */
@keyframes spin-slow {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
.animate-spin-slow {
  animation: spin-slow 3s linear infinite;
}
```

- [ ] **Commit**

```bash
git add resources/js/vue/components/Community/PulsoRing.vue resources/css/app.css
git commit -m "feat(pulsos): PulsoRing con anillo animado por tipo + CSS"
```

---

## Task 7: Componente PulsoViewer.vue

Modal fullscreen que muestra el pulso (foto, video o stat card), stats overlay, reacciones y quién vio (si es owner).

**Files:**
- Create: `resources/js/vue/components/Community/PulsoViewer.vue`

- [ ] **Crear el componente**

```vue
<!-- resources/js/vue/components/Community/PulsoViewer.vue -->
<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useApi } from '../../../composables/useApi';
import { useToast } from '../../../composables/useToast';
import PulsoStatCard from './PulsoStatCard.vue';

interface PulsoDetail {
  id: number;
  client_id: number;
  client_name: string;
  initials: string;
  pulso_type: string;
  ring_color: string;
  media_type: string;
  media_url: string | null;
  caption: string | null;
  stats_overlay: Record<string, any> | null;
  expires_at: string;
  views_count: number;
  reaction_counts: Record<string, number>;
  my_reactions: string[];
  is_mine: boolean;
  viewers?: Array<{ name: string; initials: string; viewed_at: string }>;
}

const props = defineProps<{ pulsoId: number }>();
const emit = defineEmits<{ close: []; deleted: [] }>();

const { get, post, del } = useApi();
const { showToast } = useToast();

const pulso = ref<PulsoDetail | null>(null);
const loading = ref(true);
const showViewers = ref(false);
const deleting = ref(false);

const reactionEmojis: Record<string, string> = {
  fire:   '🔥',
  muscle: '💪',
  trophy: '🏆',
  energy: '⚡',
};

// Barra de progreso de expiración
const expiryProgress = ref(100);
let expiryTimer: ReturnType<typeof setInterval>;

function calcExpiryProgress(expiresAt: string) {
  const expiresMs = new Date(expiresAt).getTime();
  const now = Date.now();
  const totalMs = 24 * 60 * 60 * 1000; // 24h base
  const remainingMs = expiresMs - now;
  expiryProgress.value = Math.max(0, Math.min(100, (remainingMs / totalMs) * 100));
}

onMounted(async () => {
  const res = await get<{ pulso: PulsoDetail }>(`/client/pulsos/${props.pulsoId}`);
  if (res?.pulso) {
    pulso.value = res.pulso;
    calcExpiryProgress(res.pulso.expires_at);
    expiryTimer = setInterval(() => {
      if (pulso.value) calcExpiryProgress(pulso.value.expires_at);
    }, 30_000);
  }
  loading.value = false;
});

onBeforeUnmount(() => clearInterval(expiryTimer));

async function toggleReaction(type: string) {
  if (!pulso.value) return;
  const res = await post<{ toggled: boolean; reaction_type: string; reaction_counts: Record<string, number> }>(
    `/client/pulsos/${pulso.value.id}/react`,
    { reaction_type: type },
  );
  if (res) {
    pulso.value.reaction_counts = res.reaction_counts;
    if (res.toggled) {
      pulso.value.my_reactions = [...pulso.value.my_reactions, type];
    } else {
      pulso.value.my_reactions = pulso.value.my_reactions.filter(r => r !== type);
    }
  }
}

async function deletePulso() {
  if (!pulso.value) return;
  deleting.value = true;
  const ok = await del(`/client/pulsos/${pulso.value.id}`);
  if (ok) {
    showToast('Pulso eliminado', 'success');
    emit('deleted');
  } else {
    showToast('No se pudo eliminar', 'error');
  }
  deleting.value = false;
}

function formatTimeLeft(expiresAt: string): string {
  const ms = new Date(expiresAt).getTime() - Date.now();
  if (ms <= 0) return 'Expirado';
  const h = Math.floor(ms / 3_600_000);
  const m = Math.floor((ms % 3_600_000) / 60_000);
  return h > 0 ? `${h}h ${m}m` : `${m}m`;
}
</script>

<template>
  <!-- Backdrop -->
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
    @click.self="emit('close')"
  >
    <!-- Loader -->
    <div v-if="loading" class="flex items-center justify-center">
      <div class="h-10 w-10 animate-spin rounded-full border-2 border-wc-accent border-t-transparent"></div>
    </div>

    <!-- Pulso Card -->
    <div v-else-if="pulso" class="relative flex w-full max-w-sm flex-col overflow-hidden rounded-3xl bg-zinc-900">

      <!-- Barra de expiración superior -->
      <div class="h-1 w-full bg-zinc-800">
        <div
          class="h-full bg-wc-accent transition-all duration-[30000ms]"
          :style="{ width: expiryProgress + '%' }"
        ></div>
      </div>

      <!-- Header -->
      <div class="flex items-center justify-between px-4 py-3">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-full bg-wc-bg-secondary text-xs font-bold text-wc-text">
            {{ pulso.initials }}
          </div>
          <div>
            <p class="text-sm font-semibold text-wc-text">{{ pulso.client_name }}</p>
            <p class="text-[10px] text-wc-text-secondary">Expira en {{ formatTimeLeft(pulso.expires_at) }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button v-if="pulso.is_mine" @click="deletePulso" :disabled="deleting"
            class="rounded-lg p-1.5 text-zinc-500 hover:bg-zinc-800 hover:text-red-400">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </button>
          <button @click="emit('close')" class="rounded-lg p-1.5 text-zinc-500 hover:bg-zinc-800">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Contenido principal -->
      <div class="relative aspect-square w-full overflow-hidden bg-zinc-950">
        <!-- Video -->
        <video v-if="pulso.media_type === 'video' && pulso.media_url"
          :src="pulso.media_url" autoplay loop muted playsinline
          class="h-full w-full object-cover"/>
        <!-- Foto -->
        <img v-else-if="pulso.media_type === 'photo' && pulso.media_url"
          :src="pulso.media_url" class="h-full w-full object-cover" :alt="pulso.caption ?? 'Pulso'"/>
        <!-- Stat Card -->
        <PulsoStatCard
          v-else
          :pulso-type="pulso.pulso_type"
          :caption="pulso.caption ?? undefined"
          :stats="pulso.stats_overlay"
          :client-name="pulso.client_name"
        />
      </div>

      <!-- Caption (si tiene media Y caption) -->
      <p v-if="pulso.caption && pulso.media_type !== 'stat_card'"
        class="px-4 py-2 text-sm text-wc-text-secondary">
        {{ pulso.caption }}
      </p>

      <!-- Reacciones -->
      <div class="flex items-center justify-around border-t border-zinc-800 px-4 py-3">
        <button
          v-for="(emoji, type) in reactionEmojis" :key="type"
          @click="toggleReaction(type)"
          :class="[
            'flex flex-col items-center gap-0.5 rounded-xl px-3 py-1.5 transition-colors',
            pulso.my_reactions.includes(type) ? 'bg-wc-accent/20 text-wc-accent' : 'text-zinc-400 hover:bg-zinc-800',
          ]"
        >
          <span class="text-lg">{{ emoji }}</span>
          <span class="text-[10px] font-semibold">{{ pulso.reaction_counts[type] ?? 0 }}</span>
        </button>
      </div>

      <!-- Viewers (solo owner) -->
      <div v-if="pulso.is_mine" class="border-t border-zinc-800 px-4 py-3">
        <button @click="showViewers = !showViewers"
          class="flex w-full items-center justify-between text-sm text-zinc-400 hover:text-zinc-200">
          <span>👁 {{ pulso.views_count }} vieron tu Pulso</span>
          <svg :class="['h-4 w-4 transition-transform', showViewers ? 'rotate-180' : '']"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>
        <div v-if="showViewers && pulso.viewers?.length" class="mt-2 flex flex-wrap gap-2">
          <div v-for="v in pulso.viewers" :key="v.name + v.viewed_at"
            class="flex items-center gap-1.5 rounded-full bg-zinc-800 px-2 py-1">
            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-zinc-700 text-[9px] font-bold text-white">
              {{ v.initials }}
            </span>
            <span class="text-[11px] text-zinc-300">{{ v.name.split(' ')[0] }}</span>
          </div>
        </div>
        <p v-else-if="showViewers" class="mt-2 text-xs text-zinc-600">Nadie lo ha visto todavía.</p>
      </div>
    </div>
  </div>
</template>
```

- [ ] **Commit**

```bash
git add resources/js/vue/components/Community/PulsoViewer.vue
git commit -m "feat(pulsos): PulsoViewer modal con media, stats overlay, reacciones y viewers"
```

---

## Task 8: Componente PulsoUploader.vue

Modal para crear un Pulso: seleccionar tipo, subir foto/video opcional, caption, preview de stat_card.

**Files:**
- Create: `resources/js/vue/components/Community/PulsoUploader.vue`

- [ ] **Crear el componente**

```vue
<!-- resources/js/vue/components/Community/PulsoUploader.vue -->
<script setup lang="ts">
import { ref, computed } from 'vue';
import { useApi } from '../../../composables/useApi';
import { useToast } from '../../../composables/useToast';
import PulsoStatCard from './PulsoStatCard.vue';

interface StatsOverlay {
  volume_kg?: number;
  series?: number;
  ejercicios?: number;
  duracion_min?: number;
  day_name?: string;
}

interface Props {
  // Si viene desde finishWorkout, traerá datos pre-rellenados
  prefillType?: string;
  prefillStats?: StatsOverlay;
  prefillSessionId?: number;
}

const props = withDefaults(defineProps<Props>(), {
  prefillType: 'libre',
});

const emit = defineEmits<{ close: []; created: [id: number] }>();

const { postFormData } = useApi();
const { showToast } = useToast();

const pulsoType = ref(props.prefillType);
const caption = ref('');
const mediaFile = ref<File | null>(null);
const mediaPreviewUrl = ref<string | null>(null);
const uploading = ref(false);

const typeOptions = [
  { value: 'entrenamiento', label: '🔥 Entrenamiento' },
  { value: 'pr',            label: '🏆 Nuevo PR' },
  { value: 'nutricion',     label: '🥗 Nutrición' },
  { value: 'recuperacion',  label: '😴 Recuperación' },
  { value: 'logro',         label: '🏅 Logro' },
  { value: 'libre',         label: '📸 Libre' },
];

function handleFileSelect(e: Event) {
  const input = e.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;

  const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'video/mp4', 'video/quicktime'];
  if (!validTypes.includes(file.type)) {
    showToast('Solo fotos (JPG/PNG/WebP) o videos (MP4/MOV)', 'error');
    return;
  }
  if (file.size > 30 * 1024 * 1024) {
    showToast('Máximo 30 MB', 'error');
    return;
  }

  mediaFile.value = file;
  mediaPreviewUrl.value = URL.createObjectURL(file);
}

function removeMedia() {
  if (mediaPreviewUrl.value) URL.revokeObjectURL(mediaPreviewUrl.value);
  mediaFile.value = null;
  mediaPreviewUrl.value = null;
}

const isVideo = computed(() => {
  if (!mediaFile.value) return false;
  return ['video/mp4', 'video/quicktime'].includes(mediaFile.value.type);
});

async function submit() {
  uploading.value = true;

  const fd = new FormData();
  fd.append('pulso_type', pulsoType.value);
  if (caption.value.trim()) fd.append('caption', caption.value.trim());
  if (mediaFile.value) fd.append('media', mediaFile.value);
  if (props.prefillSessionId) {
    fd.append('workout_session_id', String(props.prefillSessionId));
    fd.append('is_auto_generated', '1');
  }
  if (props.prefillStats) {
    Object.entries(props.prefillStats).forEach(([k, v]) => {
      if (v !== undefined) fd.append(`stats_overlay[${k}]`, String(v));
    });
  }

  const res = await postFormData<{ id: number; expires_at: string }>('/client/pulsos', fd);
  uploading.value = false;

  if (res?.id) {
    showToast('¡Pulso publicado! 🔥', 'success');
    emit('created', res.id);
  } else {
    showToast('No se pudo publicar el Pulso', 'error');
  }
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/80 sm:items-center"
    @click.self="emit('close')">
    <div class="w-full max-w-sm overflow-hidden rounded-t-3xl bg-zinc-900 sm:rounded-3xl">

      <!-- Handle bar -->
      <div class="flex justify-center pt-3 sm:hidden">
        <div class="h-1 w-10 rounded-full bg-zinc-700"></div>
      </div>

      <!-- Header -->
      <div class="flex items-center justify-between px-5 py-4">
        <h3 class="font-display text-lg uppercase tracking-wider text-wc-text">Nuevo Pulso</h3>
        <button @click="emit('close')" class="text-zinc-500 hover:text-zinc-300">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <div class="space-y-4 px-5 pb-6">
        <!-- Tipo de Pulso -->
        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-widest text-zinc-500">Tipo</label>
          <div class="grid grid-cols-3 gap-2">
            <button
              v-for="opt in typeOptions" :key="opt.value"
              @click="pulsoType = opt.value"
              :class="[
                'rounded-xl py-2 text-center text-xs font-semibold transition-colors',
                pulsoType === opt.value
                  ? 'bg-wc-accent text-white'
                  : 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700',
              ]"
            >
              {{ opt.label }}
            </button>
          </div>
        </div>

        <!-- Preview -->
        <div class="overflow-hidden rounded-2xl">
          <!-- Media preview -->
          <video v-if="mediaPreviewUrl && isVideo"
            :src="mediaPreviewUrl" autoplay loop muted playsinline
            class="aspect-square w-full object-cover"/>
          <img v-else-if="mediaPreviewUrl"
            :src="mediaPreviewUrl" class="aspect-square w-full object-cover" alt="preview"/>
          <!-- Stat card preview (cuando no hay media) -->
          <div v-else class="aspect-square w-full">
            <PulsoStatCard
              :pulso-type="pulsoType"
              :caption="caption || undefined"
              :stats="prefillStats ?? null"
            />
          </div>
        </div>

        <!-- Caption -->
        <textarea
          v-model="caption"
          placeholder="Añade un mensaje... (opcional)"
          rows="2"
          maxlength="200"
          class="w-full resize-none rounded-xl bg-zinc-800 px-4 py-3 text-sm text-wc-text placeholder-zinc-600 focus:outline-none focus:ring-1 focus:ring-wc-accent"
        ></textarea>

        <!-- Media upload / remove -->
        <div class="flex items-center gap-3">
          <label class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-zinc-700 py-3 text-sm text-zinc-500 hover:border-zinc-500 hover:text-zinc-300 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            {{ mediaFile ? 'Cambiar' : 'Foto / Video' }}
            <input type="file" accept="image/jpeg,image/jpg,image/png,image/webp,video/mp4,video/quicktime"
              class="hidden" @change="handleFileSelect"/>
          </label>
          <button v-if="mediaFile" @click="removeMedia"
            class="rounded-xl bg-zinc-800 px-3 py-3 text-sm text-zinc-400 hover:bg-zinc-700">
            Quitar
          </button>
        </div>

        <!-- Submit -->
        <button
          @click="submit"
          :disabled="uploading"
          class="w-full rounded-xl bg-wc-accent py-4 font-display text-sm uppercase tracking-widest text-white transition-opacity disabled:opacity-60"
        >
          {{ uploading ? 'Publicando...' : '⚡ Publicar Pulso' }}
        </button>
      </div>
    </div>
  </div>
</template>
```

- [ ] **Verificar que `useApi` expone `postFormData`**

```bash
grep -n "postFormData" resources/js/vue/composables/useApi.ts
```

Si NO existe, agregar en `useApi.ts` (buscar el bloque de funciones exportadas):

```typescript
async function postFormData<T = any>(endpoint: string, body: FormData): Promise<T | null> {
  try {
    const res = await fetch(`/api/v${endpoint}`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
      body,
    });
    if (!res.ok) return null;
    return await res.json() as T;
  } catch {
    return null;
  }
}
// Y exportarlo en el return del composable
```

- [ ] **Commit**

```bash
git add resources/js/vue/components/Community/PulsoUploader.vue resources/js/vue/composables/useApi.ts
git commit -m "feat(pulsos): PulsoUploader con preview, tipos y upload multipart"
```

---

## Task 9: Integrar en CommunityFeed.vue

Reemplazar el Stories Row antiguo (basado en `storiesMembers` de SocialController) con el nuevo sistema de Pulsos.

**Files:**
- Modify: `resources/js/vue/pages/Client/CommunityFeed.vue`
- Modify: `app/Http/Controllers/Api/SocialController.php`

### Parte A: Actualizar SocialController (community:stories → community:pulsos)

- [ ] **En `SocialController::communityIndex`, reemplazar el bloque `$storiesMembers`**

Buscar líneas 75-93 de `SocialController.php` (bloque `community:stories`) y reemplazar con:

```php
// Pulsos activos — tomar 1 por cliente (el más reciente)
$storiesMembers = \App\Models\ClientPulso::where('expires_at', '>', now())
    ->with('client:id,name')
    ->orderByDesc('created_at')
    ->limit(50)
    ->get()
    ->groupBy('client_id')
    ->map(fn ($group) => $group->first())
    ->values()
    ->map(fn (\App\Models\ClientPulso $p) => [
        'id'         => $p->id,
        'client_id'  => $p->client_id,
        'name'       => $p->client?->name ?? 'Miembro',
        'initials'   => mb_strtoupper(mb_substr(trim($p->client?->name ?? 'M'), 0, 2)),
        'has_new'    => true, // todos los pulsos son "nuevos" en el row
        'ring_color' => \App\Models\ClientPulso::ringColorForType($p->pulso_type),
        'pulso_id'   => $p->id,
        'color'      => $this->colorForName($p->client?->name ?? ''),
    ])
    ->all();
```

### Parte B: Agregar imports y estado en CommunityFeed.vue

- [ ] **Agregar imports al `<script setup>` de CommunityFeed.vue (después de los imports existentes):**

```typescript
import PulsoRing from '../../components/Community/PulsoRing.vue';
import PulsoViewer from '../../components/Community/PulsoViewer.vue';
import PulsoUploader from '../../components/Community/PulsoUploader.vue';
```

- [ ] **Agregar estado de Pulsos después del estado existente de `storiesMembers`:**

```typescript
// ── Pulsos ────────────────────────────────────────────────────────────
const activePulsoId = ref<number | null>(null);
const showPulsoUploader = ref(false);
const pulsoUploaderPrefill = ref<{
  type: string;
  stats?: Record<string, any>;
  sessionId?: number;
} | null>(null);

function openPulso(pulsoId: number) {
  activePulsoId.value = pulsoId;
}

function closePulsoViewer() {
  activePulsoId.value = null;
}

function openPulsoUploader(prefill?: typeof pulsoUploaderPrefill.value) {
  pulsoUploaderPrefill.value = prefill ?? null;
  showPulsoUploader.value = true;
}

function onPulsoCreated() {
  showPulsoUploader.value = false;
  pulsoUploaderPrefill.value = null;
  // Refrescar los datos de comunidad
  loadFeed();
}

function onPulsoDeleted() {
  activePulsoId.value = null;
  loadFeed();
}
```

### Parte C: Actualizar el template del Stories Row

- [ ] **Reemplazar en el `<template>` de CommunityFeed.vue el bloque del Stories Row:**

Buscar la sección `v-if="hasActiveStories"` con el scroll horizontal de avatares y reemplazar con:

```html
<!-- ── PULSOS ROW ──────────────────────────────────────────── -->
<div v-if="hasActiveStories" class="overflow-x-auto pb-1">
  <div class="flex gap-4 px-1">
    <!-- Botón "Crear Pulso" siempre visible -->
    <button
      @click="openPulsoUploader()"
      class="flex flex-col items-center gap-1.5 focus:outline-none"
    >
      <div class="relative flex h-16 w-16 items-center justify-center rounded-full border-2 border-dashed border-zinc-700 bg-zinc-800/60 hover:border-wc-accent/60 transition-colors">
        <svg class="h-6 w-6 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
      </div>
      <span class="text-[10px] text-zinc-600">Tu Pulso</span>
    </button>

    <!-- Pulsos de miembros -->
    <PulsoRing
      v-for="member in storiesMembers"
      :key="member.pulso_id ?? member.id"
      :name="member.name ?? member.client_name"
      :initials="member.initials"
      :ring-color="member.ring_color ?? 'red'"
      :has-new="member.has_new"
      :pulso-id="member.pulso_id ?? null"
      size="md"
      @click="member.pulso_id ? openPulso(member.pulso_id) : null"
    />
  </div>
</div>

<!-- Botón "Publicar Pulso" cuando no hay pulsos activos (junto al tour o solo) -->
<div v-if="!hasActiveStories" class="flex justify-center py-2">
  <button @click="openPulsoUploader()"
    class="flex items-center gap-2 rounded-xl border border-dashed border-zinc-700 px-4 py-2 text-sm text-zinc-500 hover:border-wc-accent/60 hover:text-zinc-300 transition-colors">
    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Sé el primero en publicar un Pulso
  </button>
</div>
```

### Parte D: Agregar modales al final del template

- [ ] **Antes del cierre `</template>`, agregar los modales:**

```html
<!-- PulsoViewer modal -->
<PulsoViewer
  v-if="activePulsoId"
  :pulso-id="activePulsoId"
  @close="closePulsoViewer"
  @deleted="onPulsoDeleted"
/>

<!-- PulsoUploader modal -->
<PulsoUploader
  v-if="showPulsoUploader"
  :prefill-type="pulsoUploaderPrefill?.type ?? 'libre'"
  :prefill-stats="pulsoUploaderPrefill?.stats"
  :prefill-session-id="pulsoUploaderPrefill?.sessionId"
  @close="showPulsoUploader = false"
  @created="onPulsoCreated"
/>
```

### Parte E: Hook en WorkoutSummary (auto-pulso post-workout)

- [ ] **Verificar qué devuelve `/client/workout-summary/{sessionId}`**

```bash
grep -n "workout-summary\|workoutSummary\|pulso_offer" resources/js/vue/pages/Client/WorkoutSummary.vue | head -20
```

- [ ] **En `WorkoutSummary.vue`, después de recibir los datos del summary, si llega `pulso_offer` del `finishWorkout`, disparar el uploader:**

Buscar la función que procesa la respuesta de `finishWorkout` y agregar:

```typescript
// Si el backend devolvió pulso_offer, ofrecer crear auto-pulso
if (data.pulso_offer) {
  // Mostrar oferta tras 1.5s (deja que el usuario vea el summary primero)
  setTimeout(() => {
    showAutoPulsoOffer.value = data.pulso_offer;
  }, 1500);
}
```

Y en el template del WorkoutSummary agregar el banner de oferta:

```html
<!-- Auto-pulso offer -->
<div v-if="showAutoPulsoOffer"
  class="rounded-2xl border border-wc-accent/30 bg-wc-bg-secondary p-4">
  <p class="mb-1 text-xs font-bold uppercase tracking-widest text-wc-accent">¡Comparte tu Pulso! 🔥</p>
  <p class="mb-3 text-sm text-wc-text-secondary">Publicá tu sesión en la comunidad con tus stats de hoy.</p>
  <div class="flex gap-2">
    <button @click="router.push({ path: '/client/community', query: { openPulso: '1', sessionId: showAutoPulsoOffer.session_id } })"
      class="flex-1 rounded-xl bg-wc-accent py-2 text-sm font-semibold text-white">
      Publicar Pulso
    </button>
    <button @click="showAutoPulsoOffer = null"
      class="rounded-xl bg-zinc-800 px-4 py-2 text-sm text-zinc-400">
      Ahora no
    </button>
  </div>
</div>
```

Y en CommunityFeed.vue, al montar el componente, verificar si viene con `?openPulso=1`:

```typescript
onMounted(async () => {
  // Verificar si viene desde WorkoutSummary para abrir el uploader
  const route = useRoute();
  if (route.query.openPulso === '1') {
    const sessionId = route.query.sessionId ? parseInt(String(route.query.sessionId)) : undefined;
    // Buscar los stats del workout si hay sessionId
    openPulsoUploader({
      type: 'entrenamiento',
      sessionId,
    });
  }
  // ... resto del onMounted existente
});
```

- [ ] **Commit**

```bash
git add resources/js/vue/pages/Client/CommunityFeed.vue \
        resources/js/vue/pages/Client/WorkoutSummary.vue \
        app/Http/Controllers/Api/SocialController.php
git commit -m "feat(pulsos): integrar PulsoRing/Viewer/Uploader en CommunityFeed + auto-pulso post-workout"
```

---

## Task 10: Build y Deploy

- [ ] **Build local**

```bash
cd C:\Users\GODSF\Herd\wellcore-laravel
npm run build
```

Verificar que compila sin errores TypeScript.

- [ ] **Verificar manifest generado**

```bash
grep -i "CommunityFeed\|PulsoRing\|PulsoViewer" public/build/manifest.json
```

- [ ] **Commit del build + push**

```bash
git add public/build/
git commit -m "build: assets Pulsos (PulsoRing, PulsoViewer, PulsoUploader, PulsoStatCard)"
git push origin main
```

- [ ] **Deploy en EasyPanel via silvia-gitpull-load**

Abrir EasyPanel panel y ejecutar el script `silvia-gitpull-load` en la consola del contenedor.

- [ ] **Verificar migración en producción**

```bash
# En la consola del container EasyPanel
php artisan migrate --path=database/migrations/2026_04_27_000001_create_client_pulsos_tables.php
```

- [ ] **Invalidar cache de comunidad**

```bash
php artisan tinker --execute="Cache::forget('community:stories'); Cache::forget('community:pulsos');"
```

- [ ] **Test E2E en producción con Chrome DevTools**

1. Navegar a `https://www.wellcorefitness.com/client/community`
2. Verificar que el botón "Tu Pulso" (+) aparece en el row
3. Click en "+" → PulsoUploader abre
4. Seleccionar tipo "🔥 Entrenamiento", caption "Test", sin media
5. Click "⚡ Publicar Pulso"
6. Verificar que el ring aparece en el Stories Row con anillo rojo animado
7. Click en el ring → PulsoViewer abre con stat card
8. Verificar barra de expiración en la parte superior del viewer
9. Reaccionar con 🔥 → contador incrementa
10. Click "👁 X vieron tu Pulso" → expande lista de viewers

---

## Self-Review

### Spec coverage

| Requisito | Tarea |
|-----------|-------|
| 3 tablas aditivas | Task 1 |
| Modelos Eloquent | Task 2 |
| CRUD API + media serving | Task 3 |
| Hook finishWorkout → pulso_offer | Task 4 |
| Stat card visual sin foto | Task 5 |
| Ring animado con color por tipo | Task 6 |
| Viewer con media + stats + reacciones + viewers | Task 7 |
| Uploader con tipos + preview | Task 8 |
| Integración en CommunityFeed | Task 9 |
| Auto-pulso desde WorkoutSummary | Task 9E |
| Build + deploy + migración prod | Task 10 |

### Consistencia de tipos

- `ClientPulso::ringColorForType()` devuelve `'red'|'gold'|'green'|'blue'|'purple'|'gray'` — mismo set que `ringClasses` en `PulsoRing.vue` ✓
- `StatsOverlay` interface idéntica en `PulsoStatCard.vue` y `PulsoUploader.vue` ✓
- `postFormData` agregado a `useApi.ts` antes de usarse en `PulsoUploader.vue` ✓
- `pulso_offer.stats` keys coinciden con `StatsOverlay` interface ✓
