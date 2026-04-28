<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserType;
use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AIGenerationHistory;
use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\ClientProfile;
use App\Models\PlanTemplate;
use App\Models\WellcoreNotification;
use App\Services\AIService;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin AI Generator — streaming endpoint + history + approval flow for the v2 UI.
 * Reuses PlanTemplate/AssignedPlan when admin approves a draft.
 * The AI brand (Anthropic/Claude) is never surfaced to the UI; system prompt
 * forbids the model from naming its provider.
 */
class AdminAIGeneratorController extends Controller
{
    use AuthenticatesVueRequests;

    /**
     * Resolve the authenticated Admin (admin/superadmin/jefe) or abort.
     * Mirrors AdminController::resolveAdminOrFail to keep this controller standalone.
     */
    protected function resolveAdmin(Request $request): Admin
    {
        $auth = $this->resolveAuthUser($request);
        if (! $auth) {
            abort(401, 'Token invalido o expirado.');
        }
        if ($auth['userType'] !== UserType::Admin) {
            abort(403, 'Acceso solo para administradores.');
        }
        $admin = $auth['user'];
        $role = $admin->role?->value ?? $admin->role ?? '';
        if (! in_array($role, ['admin', 'superadmin', 'jefe'])) {
            abort(403, 'No tienes permisos de administrador.');
        }
        return $admin;
    }

    /**
     * POST /api/v/admin/ai-generator/stream
     * Streams plan text via SSE. Persists a history row that the client
     * resumes via GET /history once the stream finishes.
     */
    public function stream(Request $request): StreamedResponse
    {
        $admin = $this->resolveAdmin($request);

        $validated = $request->validate([
            'plan_type'            => 'required|in:entrenamiento,nutricion,habitos,combinado',
            'methodology'          => 'nullable|string|max:100',
            'duration_weeks'       => 'required|integer|min:1|max:52',
            'frequency'            => 'nullable|integer|min:1|max:7',
            'experience_level'     => 'nullable|in:principiante,intermedio,avanzado',
            'training_goal'        => 'nullable|string|max:100',
            'injuries'             => 'nullable|string|max:500',
            'preferences'          => 'nullable|string|max:500',
            'calorie_target'       => 'nullable|integer|min:800|max:10000',
            'meals_per_day'        => 'nullable|integer|min:1|max:10',
            'dietary_restrictions' => 'nullable|string|max:500',
            'habit_focus_areas'    => 'nullable|array',
            'target_client_id'     => 'nullable|integer',
        ]);

        [$systemPrompt, $userPrompt] = $this->buildPrompt($validated);

        // Persist a draft history row up-front so the front-end can reference it.
        $history = AIGenerationHistory::create([
            'admin_id'         => $admin->id,
            'target_client_id' => $validated['target_client_id'] ?? null,
            'plan_type'        => $validated['plan_type'],
            'methodology'      => $validated['methodology'] ?? null,
            'duration_weeks'   => $validated['duration_weeks'],
            'brief_json'       => $validated,
            'output_text'      => '',
            'status'           => 'streaming',
        ]);

        $startedAt = microtime(true);

        $response = new StreamedResponse(function () use (
            $systemPrompt, $userPrompt, $history, $startedAt
        ) {
            // Disable PHP output buffering so flush() actually emits to the wire.
            while (ob_get_level() > 0) {
                @ob_end_flush();
            }
            @ini_set('zlib.output_compression', '0');

            $accumulated = '';
            $aiService = app(AIService::class);

            // Send a comment line first to flush headers through nginx proxy.
            echo ": stream-open\n\n";
            @ob_flush();
            @flush();

            $emit = function (array $payload) {
                echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
                @ob_flush();
                @flush();
            };

            try {
                $result = $aiService->streamText(
                    $systemPrompt,
                    $userPrompt,
                    8192,
                    function (string $delta) use (&$accumulated, $emit) {
                        $accumulated .= $delta;
                        $emit(['chunk' => $delta]);
                        // If the user disconnects (closes tab / aborts fetch),
                        // PHP sets connection_status() to a non-zero value.
                        if (connection_aborted() || connection_status() !== CONNECTION_NORMAL) {
                            return false;
                        }
                        return true;
                    }
                );
            } catch (\Throwable $e) {
                Log::error('AI generator stream exception', ['err' => $e->getMessage()]);
                $emit(['error' => 'Error en el sistema asistido']);
                $history->update([
                    'status'       => 'aborted',
                    'output_text'  => $accumulated,
                    'duration_ms'  => (int) ((microtime(true) - $startedAt) * 1000),
                    'output_chars' => strlen($accumulated),
                ]);
                return;
            }

            $durationMs = (int) ((microtime(true) - $startedAt) * 1000);

            if (! $result['ok']) {
                $errorMsg = $result['error'] === 'aborted'
                    ? 'aborted'
                    : 'Error generando: ' . $result['error'];
                $history->update([
                    'status'       => $result['error'] === 'aborted' ? 'aborted' : 'aborted',
                    'output_text'  => $accumulated,
                    'duration_ms'  => $durationMs,
                    'output_chars' => strlen($accumulated),
                ]);
                $emit(['error' => $errorMsg]);
                return;
            }

            $history->update([
                'status'       => 'completed',
                'output_text'  => $accumulated,
                'duration_ms'  => $durationMs,
                'output_chars' => strlen($accumulated),
            ]);

            $emit([
                'done'        => true,
                'history_id'  => $history->id,
                'duration_ms' => $durationMs,
                'chars'       => strlen($accumulated),
            ]);
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache, no-transform');
        $response->headers->set('Connection', 'keep-alive');
        // Critical for nginx — without it nginx buffers the entire response.
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    /**
     * GET /api/v/admin/ai-generator/history
     * Returns the latest 20 generations of the calling admin.
     */
    public function history(Request $request): JsonResponse
    {
        $admin = $this->resolveAdmin($request);

        $rows = AIGenerationHistory::where('admin_id', $admin->id)
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'plan_type', 'methodology', 'target_client_id', 'duration_weeks', 'status', 'output_chars', 'duration_ms', 'created_at']);

        $clientNames = [];
        $clientIds = $rows->pluck('target_client_id')->filter()->unique()->values();
        if ($clientIds->isNotEmpty()) {
            $clientNames = Client::whereIn('id', $clientIds)
                ->pluck('name', 'id')
                ->all();
        }

        return response()->json([
            'rows' => $rows->map(function ($r) use ($clientNames) {
                return [
                    'id'             => $r->id,
                    'plan_type'      => $r->plan_type,
                    'methodology'    => $r->methodology,
                    'duration_weeks' => $r->duration_weeks,
                    'target_client'  => $r->target_client_id
                        ? ['id' => $r->target_client_id, 'name' => $clientNames[$r->target_client_id] ?? null]
                        : null,
                    'status'         => $r->status,
                    'chars'          => $r->output_chars,
                    'duration_ms'    => $r->duration_ms,
                    'created_at'     => $r->created_at?->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * GET /api/v/admin/ai-generator/history/{id}
     * Returns the full output_text + brief_json of one history entry (the admin's own).
     */
    public function historyDetail(Request $request, int $id): JsonResponse
    {
        $admin = $this->resolveAdmin($request);
        $row = AIGenerationHistory::where('admin_id', $admin->id)->find($id);
        abort_if(! $row, 404, 'Generación no encontrada');

        return response()->json([
            'id'          => $row->id,
            'brief'       => $row->brief_json,
            'output_text' => $row->output_text,
            'status'      => $row->status,
            'plan_type'   => $row->plan_type,
        ]);
    }

    /**
     * POST /api/v/admin/ai-generator/history/{id}/approve
     * Persist the streamed output as a PlanTemplate (and optionally as AssignedPlan).
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $admin = $this->resolveAdmin($request);

        $validated = $request->validate([
            'template_name'    => 'required|string|max:160',
            'is_public'        => 'nullable|boolean',
            'save_mode'        => 'nullable|in:template_only,template_and_assign',
            'target_client_id' => 'nullable|integer',
            'edited_text'      => 'nullable|string',
        ]);

        $row = AIGenerationHistory::where('admin_id', $admin->id)->find($id);
        abort_if(! $row, 404, 'Generación no encontrada');
        abort_if($row->status === 'streaming', 409, 'La generación aún está en curso');

        $finalText = $validated['edited_text'] ?? $row->output_text;
        if (! is_string($finalText) || trim($finalText) === '') {
            return response()->json(['error' => 'El plan está vacío'], 422);
        }

        // PlanTemplate.content_json is a json column; we store the raw markdown
        // under a known key so existing flows can read it back.
        $template = PlanTemplate::create([
            'coach_id'     => $admin->id,
            'name'         => $validated['template_name'],
            'plan_type'    => $row->plan_type,
            'methodology'  => $row->methodology,
            'content_json' => [
                'markdown'    => $finalText,
                'source'      => 'ai_streaming',
                'history_id'  => $row->id,
                'generated_at'=> $row->created_at?->toIso8601String(),
            ],
            'ai_generated' => true,
            'is_public'    => (bool) ($validated['is_public'] ?? false),
        ]);

        $assignedId = null;
        $clientId = $validated['target_client_id'] ?? $row->target_client_id;

        if (($validated['save_mode'] ?? '') === 'template_and_assign' && $clientId) {
            $prevExpiry = AssignedPlan::where('client_id', $clientId)
                ->where('plan_type', $row->plan_type)
                ->where('active', true)
                ->whereNotNull('expires_at')
                ->max('expires_at');

            AssignedPlan::where('client_id', $clientId)
                ->where('plan_type', $row->plan_type)
                ->where('active', true)
                ->update(['active' => false]);

            $assigned = AssignedPlan::create([
                'client_id'   => $clientId,
                'plan_type'   => $row->plan_type,
                'content'     => ['markdown' => $finalText, 'source' => 'ai_streaming'],
                'version'     => 1,
                'active'      => true,
                'assigned_by' => $admin->id,
                'expires_at'  => $prevExpiry ?? null,
            ]);
            $assignedId = $assigned->id;

            WellcoreNotification::create([
                'user_type' => 'client',
                'user_id'   => $clientId,
                'type'      => 'new_plan',
                'title'     => 'Nuevo plan asignado',
                'body'      => "Tu coach asignó un nuevo plan de {$row->plan_type}",
                'link'      => '/client/plan',
            ]);
            try {
                PushNotificationService::notifyNewPlan($clientId, $row->plan_type);
            } catch (\Throwable) {
            }
        }

        $row->update([
            'status'           => 'approved',
            'template_id'      => $template->id,
            'assigned_plan_id' => $assignedId,
        ]);

        return response()->json([
            'ok'                => true,
            'template_id'       => $template->id,
            'assigned_plan_id'  => $assignedId,
        ]);
    }

    /**
     * POST /api/v/admin/ai-generator/history/{id}/discard
     */
    public function discard(Request $request, int $id): JsonResponse
    {
        $admin = $this->resolveAdmin($request);
        $row = AIGenerationHistory::where('admin_id', $admin->id)->find($id);
        abort_if(! $row, 404, 'Generación no encontrada');

        $row->update(['status' => 'discarded']);

        return response()->json(['ok' => true]);
    }

    /**
     * GET /api/v/admin/ai-generator/templates
     * Returns AI-generated public templates (the "winning prompts" panel).
     * Uses plan_templates rows with ai_generated=1.
     */
    public function templates(Request $request): JsonResponse
    {
        $admin = $this->resolveAdmin($request);

        $rows = PlanTemplate::query()
            ->where('ai_generated', true)
            ->where(function ($q) use ($admin) {
                $q->where('is_public', true)
                  ->orWhere('coach_id', $admin->id);
            })
            ->orderByDesc('id')
            ->limit(40)
            ->get(['id', 'name', 'plan_type', 'methodology', 'is_public', 'created_at']);

        return response()->json([
            'rows' => $rows->map(fn ($r) => [
                'id'          => $r->id,
                'name'        => $r->name,
                'plan_type'   => $r->plan_type,
                'methodology' => $r->methodology,
                'is_public'   => (bool) $r->is_public,
                'created_at'  => $r->created_at?->toIso8601String(),
            ]),
        ]);
    }

    /**
     * GET /api/v/admin/ai-generator/clients/search?q=...
     * Lightweight client search (top 12) for the brief form autocomplete.
     */
    public function clientSearch(Request $request): JsonResponse
    {
        $this->resolveAdmin($request);
        $q = trim((string) $request->query('q', ''));

        $query = Client::query()
            ->select('id', 'name', 'email')
            ->orderByDesc('id');

        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                  ->orWhere('email', 'like', $like);
            });
        }

        $rows = $query->limit(12)->get();

        return response()->json([
            'rows' => $rows->map(fn ($c) => [
                'id'    => $c->id,
                'name'  => $c->name,
                'email' => $c->email,
            ]),
        ]);
    }

    /**
     * Build the system + user prompt pair from validated brief data.
     * Returns [system, user].
     */
    protected function buildPrompt(array $v): array
    {
        $system = <<<SYS
Eres un coach senior de fitness y nutricion basado en evidencia, con voz directa, filosofica, sin caer en motivacion superficial. Tu tarea es generar planes profesionales en Markdown legible para que un administrador los revise antes de asignarlos a un cliente.

REGLAS DE FORMATO (no negociables):
- Devuelve SOLO Markdown con encabezados `##` para cada seccion principal (Entrenamiento, Nutricion, Habitos, Suplementacion segun aplique).
- Cada subseccion con `###`. Tablas con sintaxis Markdown estandar para series/repeticiones/macros.
- Usa bullets para listas; nunca emojis.
- Lenguaje: espanol latino neutro (Colombia/Mexico). Sin "vos/tenes/queres" ni "vale/tio/guay".
- Tono: filosofico-disciplinado, sin exclamaciones, sin urgencia artificial.
- Ningun campo debe contener referencia a la marca/proveedor del modelo de IA (no nombres de Anthropic, Claude, OpenAI, GPT). Si el usuario te pregunta quien eres, responde "Sistema asistido WellCore".
- Cierra con una seccion `## Notas del coach` con 2-3 lineas de contexto/recomendacion clinica.
SYS;

        $lines = [];
        $lines[] = "Genera un plan profesional de tipo: {$v['plan_type']}.";
        if (! empty($v['methodology'])) {
            $lines[] = "Metodologia: {$v['methodology']}.";
        }
        $lines[] = "Duracion: {$v['duration_weeks']} semanas.";
        if (! empty($v['frequency'])) {
            $lines[] = "Frecuencia semanal: {$v['frequency']} dias.";
        }
        if (! empty($v['experience_level'])) {
            $lines[] = "Nivel: {$v['experience_level']}.";
        }
        if (! empty($v['training_goal'])) {
            $lines[] = "Objetivo principal: {$v['training_goal']}.";
        }
        if (! empty($v['injuries'])) {
            $lines[] = "Lesiones / restricciones: {$v['injuries']}.";
        }
        if (! empty($v['preferences'])) {
            $lines[] = "Preferencias: {$v['preferences']}.";
        }
        if (! empty($v['calorie_target'])) {
            $lines[] = "Calorias objetivo: {$v['calorie_target']} kcal.";
        }
        if (! empty($v['meals_per_day'])) {
            $lines[] = "Comidas por dia: {$v['meals_per_day']}.";
        }
        if (! empty($v['dietary_restrictions'])) {
            $lines[] = "Restricciones dieteticas: {$v['dietary_restrictions']}.";
        }
        if (! empty($v['habit_focus_areas'])) {
            $lines[] = 'Habitos a trabajar: ' . implode(', ', (array) $v['habit_focus_areas']) . '.';
        }

        if (! empty($v['target_client_id'])) {
            $client = Client::find($v['target_client_id']);
            if ($client) {
                $lines[] = "Cliente: {$client->name}.";
                $profile = ClientProfile::where('client_id', $client->id)->first();
                if ($profile) {
                    if ($profile->age)    $lines[] = "Edad: {$profile->age}";
                    if ($profile->weight) $lines[] = "Peso: {$profile->weight} kg";
                    if ($profile->height) $lines[] = "Altura: {$profile->height} cm";
                    if ($profile->gender) $lines[] = "Genero: {$profile->gender}";
                }
            }
        }

        return [$system, implode("\n", $lines)];
    }
}
