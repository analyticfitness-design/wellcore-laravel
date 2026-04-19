<?php

namespace App\Services;

use App\Enums\PlanType;
use App\Models\Admin;
use App\Models\AssignedPlan;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\ClientProfile;
use App\Models\Inscription;
use App\Models\Metric;
use App\Models\PlanTicket;
use Illuminate\Support\Carbon;

final class PlanTicketExportService
{
    public const SECTION_INSTRUCTIONS = [
        'entrenamiento' => 'Genera JSON con: titulo, metodologia, duracion_semanas, frecuencia_dias, split, objetivo, deload_protocol, semanas[{semana, nombre_bloque, dias[{dia, nombre, ejercicios[{nombre, series, repeticiones, descanso, rir, gif_url, notas, variacion?}]}]}]. Cada ejercicio DEBE usar nombre + gif_url del catalogo CATALOGO_GIF_265.md. RIR de 1-3 segun plan tier. Seguir metodologia en TESIS_ENTRENAMIENTO_WELLCORE.md.',
        'nutricion' => 'Genera JSON con: titulo, metodologia, objetivo_calorico (calculado desde TMB Mifflin-St Jeor x factor actividad), macros {proteina_g, carbohidratos_g, grasas_g}, comidas_sugeridas[{nombre, hora, calorias, macros, opciones:["Opcion N: ing1 (Xg) + ing2 (Yg) + ing3 (Zg)", ...]}], hidratacion, tips_nutricionales, notas_coach. SUMA de calorias de comidas = objetivo_calorico. 3 opciones por comida con ±5% variacion de macros. Formato de opciones OBLIGATORIO con " + " separando ingredientes. Seguir TESIS_NUTRICION_WELLCORE.md.',
        'habitos' => 'Genera JSON con: titulo, areas_foco[], habitos[{habito, frecuencia, metrica, objetivo, categoria}]. Cada habito debe ser mensurable y especifico segun el objetivo del cliente.',
        'suplementacion' => 'Genera JSON con: titulo, objetivo, suplementos[{nombre, dosis, momento, frecuencia, notas}]. Solo recomendar suplementos respaldados por evidencia relevantes al objetivo del cliente.',
        'ciclo' => 'Solo Elite. Genera JSON con: name, duracion, descripcion_protocolo, warning (supervision medica), metricas, compounds[], phases[], pct[], labs[], efectos_secundarios[], monitoreo_diario[], emergencia[], notas_coach. Seguir METODOLOGIAS_ELITE_COMPLETAS.md.',
    ];

    private const PLAN_TIER_EXPECTATIONS = [
        'esencial' => [
            'duracion_semanas' => 4,
            'frecuencia_dias' => 4,
            'metodologia_base' => 'Hipertrofia Base + Flexible Dieting',
            'incluye' => ['entrenamiento', 'nutricion', 'habitos', 'suplementacion'],
            'ciclo_incluido' => false,
        ],
        'metodo' => [
            'duracion_semanas' => 4,
            'frecuencia_dias' => '4-5',
            'metodologia_base' => 'Hipertrofia Progresiva + Periodizacion Nutricional Lite',
            'incluye' => ['entrenamiento', 'nutricion', 'habitos', 'suplementacion'],
            'ciclo_incluido' => false,
        ],
        'elite' => [
            'duracion_semanas' => 4,
            'frecuencia_dias' => '5-6',
            'metodologia_base' => 'Periodizacion por Bloques + Periodizacion Nutricional Avanzada',
            'incluye' => ['entrenamiento', 'nutricion', 'habitos', 'suplementacion', 'ciclo'],
            'ciclo_incluido' => true,
        ],
        'rise' => [
            'duracion_semanas' => 8,
            'frecuencia_dias' => 3,
            'metodologia_base' => 'RISE — programa femenino especializado',
            'incluye' => ['entrenamiento', 'nutricion', 'habitos'],
            'ciclo_incluido' => false,
        ],
        'presencial' => [
            'duracion_semanas' => 4,
            'frecuencia_dias' => '3-5',
            'metodologia_base' => 'Presencial — adaptado por coach',
            'incluye' => ['entrenamiento', 'nutricion', 'habitos', 'suplementacion'],
            'ciclo_incluido' => false,
        ],
        'trial' => [
            'duracion_semanas' => 1,
            'frecuencia_dias' => 3,
            'metodologia_base' => 'Trial — plan introductorio',
            'incluye' => ['entrenamiento', 'nutricion'],
            'ciclo_incluido' => false,
        ],
    ];

    public function buildFullExport(PlanTicket $ticket): array
    {
        $planType = $ticket->plan_type?->value;
        $client = $this->loadClient($ticket->client_id);
        $profile = $client?->profile ?? ($ticket->client_id ? ClientProfile::where('client_id', $ticket->client_id)->first() : null);
        $inscription = $this->loadInscription($client);
        $profileSnapshot = $this->buildProfileSnapshot($client, $profile, $inscription);
        $coach = Admin::find($ticket->coach_id);
        $tierExpectations = $this->tierExpectations($planType);

        return [
            'version' => '1.0',
            'generated_at' => now()->toIso8601String(),
            'ticket' => [
                'id' => $ticket->id,
                'category' => $ticket->category,
                'status' => $ticket->status?->value,
                'submitted_at' => $ticket->submitted_at?->toIso8601String(),
                'deadline_at' => $ticket->deadline_at?->toIso8601String(),
                'reviewed_at' => $ticket->reviewed_at?->toIso8601String(),
                'completed_at' => $ticket->completed_at?->toIso8601String(),
                'parent_ticket_id' => $ticket->parent_ticket_id,
            ],
            'client' => $this->buildClientBlock($ticket, $client),
            'coach' => $this->buildCoachBlock($ticket, $coach),
            'profile_snapshot' => $profileSnapshot,
            'previous_plans_summary' => $this->buildPreviousPlans($ticket->client_id),
            'recent_checkins_summary' => $this->buildRecentCheckins($ticket->client_id),
            'attachments' => $this->buildAttachments($ticket),
            'coach_comments' => $this->buildComments($ticket),
            'plan_tier_expectations' => $tierExpectations,
            'instructions' => $this->buildInstructions($planType, $tierExpectations),
            'coach_brief' => [
                'datos_generales' => $ticket->datos_generales ?? (object) [],
                'plan_entrenamiento' => $ticket->plan_entrenamiento ?? (object) [],
                'plan_nutricional' => $ticket->plan_nutricional ?? (object) [],
                'plan_habitos' => $ticket->plan_habitos ?? (object) [],
                'plan_suplementacion' => $ticket->plan_suplementacion ?? (object) [],
                'plan_ciclo' => $ticket->plan_type === PlanType::Elite ? ($ticket->plan_ciclo ?? (object) []) : null,
            ],
            'notas_coach' => $ticket->notas_coach,
        ];
    }

    public function buildSectionExport(PlanTicket $ticket, string $section): array
    {
        $map = [
            'entrenamiento' => $ticket->plan_entrenamiento,
            'nutricion' => $ticket->plan_nutricional,
            'habitos' => $ticket->plan_habitos,
            'suplementacion' => $ticket->plan_suplementacion,
            'ciclo' => $ticket->plan_ciclo,
        ];

        $planType = $ticket->plan_type?->value;
        $client = $this->loadClient($ticket->client_id);
        $profile = ClientProfile::where('client_id', $ticket->client_id)->first();
        $inscription = $this->loadInscription($client);
        $tierExpectations = $this->tierExpectations($planType);
        $clientName = $client?->name ?? $ticket->client_name;
        $sectionLabel = ucfirst($section);

        return [
            'version' => '1.0',
            'generated_at' => now()->toIso8601String(),
            'client_id' => $ticket->client_id,
            'plan_type' => $planType,
            'section' => $section,
            'instructions' => self::SECTION_INSTRUCTIONS[$section] ?? '',
            'brief' => $map[$section] ?? (object) [],
            'profile_snapshot' => $this->buildProfileSnapshot($client, $profile, $inscription),
            'plan_tier_expectations' => $tierExpectations,
            'upload_payload_template' => [
                'name' => "Plan {$sectionLabel} — {$clientName}",
                'plan_type' => $section,
                'methodology' => 'TBD',
                'save_template' => true,
                'content_json' => (object) [],
            ],
            'apis' => $this->buildApiEndpoints(),
        ];
    }

    private function loadClient(?int $clientId): ?Client
    {
        if (! $clientId) {
            return null;
        }

        return Client::with('profile')->find($clientId);
    }

    private function loadInscription(?Client $client): ?Inscription
    {
        if (! $client?->email) {
            return null;
        }

        return Inscription::where('email', $client->email)->latest('created_at')->first();
    }

    private function buildClientBlock(PlanTicket $ticket, ?Client $client): array
    {
        $birthDate = $client?->birth_date;
        $age = $this->computeAge($birthDate);

        return [
            'id' => $ticket->client_id,
            'name' => $client?->name ?? $ticket->client_name,
            'email' => $client?->email,
            'plan_contratado' => $client?->plan?->value ?? $ticket->plan_type?->value,
            'fecha_inicio_servicio' => $client?->fecha_inicio?->toDateString(),
            'city' => $client?->city,
            'birth_date' => $birthDate?->toDateString(),
            'age_years' => $age,
        ];
    }

    private function buildCoachBlock(PlanTicket $ticket, ?Admin $coach): array
    {
        return [
            'id' => $ticket->coach_id,
            'name' => $coach?->name ?? $ticket->coach_name,
            'username' => $coach?->username ?? null,
        ];
    }

    private function buildProfileSnapshot(?Client $client, ?ClientProfile $profile, ?Inscription $inscription): array
    {
        $metrics = $client
            ? Metric::where('client_id', $client->id)
                ->orderByDesc('log_date')
                ->limit(6)
                ->get()
            : collect();

        $pesoHistorico = $metrics->map(fn (Metric $m) => [
            'date' => $m->log_date?->toDateString(),
            'value' => $m->peso !== null ? (float) $m->peso : null,
        ])->values()->all();

        $pesoActual = $metrics->first()?->peso !== null
            ? (float) $metrics->first()->peso
            : ($profile?->peso !== null ? (float) $profile->peso : null);

        $ageFromBirth = $this->computeAge($client?->birth_date);

        return [
            'edad' => $ageFromBirth ?? ($profile?->edad !== null ? (int) $profile->edad : ($inscription?->edad !== null ? (int) $inscription->edad : null)),
            'peso_actual_kg' => $pesoActual,
            'peso_historico_kg' => $pesoHistorico,
            'estatura_cm' => $profile?->altura !== null ? (float) $profile->altura : null,
            'genero' => $profile?->genero,
            'nivel_actividad' => $profile?->nivel,
            'lugar_entrenamiento' => $profile?->lugar_entreno,
            'dias_disponibles' => $profile?->dias_disponibles ?? ($inscription?->dias_disponibles ? [$inscription->dias_disponibles] : []),
            'objetivo_general' => $profile?->objetivo ?? $inscription?->objetivo,
            'restricciones_medicas' => $profile?->restricciones ?? ($inscription?->lesion ? trim(($inscription->lesion ?? '').' '.($inscription->detalle_lesion ?? '')) : null),
            'experiencia_previa' => $inscription?->experiencia,
            'macros_asignados' => $profile?->macros,
            'intake_data' => $profile?->intake_data,
        ];
    }

    private function buildPreviousPlans(?int $clientId): array
    {
        if (! $clientId) {
            return [];
        }

        return AssignedPlan::where('client_id', $clientId)
            ->orderByDesc('active')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function (AssignedPlan $plan) {
                $content = is_array($plan->content) ? $plan->content : [];

                return [
                    'plan_type' => $plan->plan_type,
                    'name' => $content['titulo'] ?? $content['name'] ?? null,
                    'assigned_at' => $plan->created_at?->toIso8601String(),
                    'valid_from' => $plan->valid_from?->toDateString(),
                    'duration_weeks' => $content['duracion_semanas'] ?? null,
                    'metodologia' => $content['metodologia'] ?? null,
                    'active' => (bool) $plan->active,
                    'version' => $plan->version,
                ];
            })
            ->values()
            ->all();
    }

    private function buildRecentCheckins(?int $clientId): array
    {
        if (! $clientId) {
            return [];
        }

        return Checkin::where('client_id', $clientId)
            ->orderByDesc('checkin_date')
            ->limit(3)
            ->get()
            ->map(fn (Checkin $c) => [
                'date' => $c->checkin_date?->toDateString(),
                'week_label' => $c->week_label,
                'bienestar' => $c->bienestar,
                'dias_entrenados' => $c->dias_entrenados,
                'nutricion' => $c->nutricion,
                'rpe' => $c->rpe,
                'comentario' => $c->comentario,
            ])
            ->values()
            ->all();
    }

    private function buildAttachments(PlanTicket $ticket): array
    {
        return $ticket->attachments()->get()->map(fn ($att) => [
            'id' => $att->id,
            'original_name' => $att->original_name,
            'category' => $att->category,
            'mime' => $att->mime,
            'size_bytes' => $att->size_bytes,
            'url' => $att->url,
            'uploaded_by' => $att->uploaded_by_name,
            'uploaded_at' => $att->created_at?->toIso8601String(),
        ])->all();
    }

    private function buildComments(PlanTicket $ticket): array
    {
        return $ticket->comments()->get()->map(fn ($c) => [
            'author' => $c->author_name,
            'author_type' => $c->author_type,
            'body' => $c->body,
            'created_at' => $c->created_at?->toIso8601String(),
        ])->all();
    }

    private function tierExpectations(?string $planType): ?array
    {
        if (! $planType) {
            return null;
        }

        return self::PLAN_TIER_EXPECTATIONS[$planType] ?? null;
    }

    private function buildInstructions(?string $planType, ?array $tierExpectations): array
    {
        $includesCiclo = (bool) ($tierExpectations['ciclo_incluido'] ?? false);

        $global = "Eres Claude Code generando los planes de WellCore Fitness para un cliente con plan '{$planType}'. "
            .'Lee este JSON completo antes de producir cualquier plan. Debes generar los archivos JSON correspondientes a cada seccion incluida en plan_tier_expectations.incluye, '
            .'subir cada uno via el endpoint indicado, y finalmente marcar el ticket como completado con los IDs generados.';

        return [
            'global' => $global,
            'workflow' => [
                '1. Leer este JSON completo (profile_snapshot, previous_plans_summary, recent_checkins_summary, attachments, coach_brief, notas_coach).',
                '2. Consultar referencias: CATALOGO_GIF_265.md, TESIS_ENTRENAMIENTO_WELLCORE.md, TESIS_NUTRICION_WELLCORE.md, METODOLOGIAS_ELITE_COMPLETAS.md y las guias plan-*.md.',
                '3. Generar un JSON por cada seccion listada en plan_tier_expectations.incluye ('.($includesCiclo ? 'incluyendo ciclo' : 'sin ciclo').').',
                '4. Subir cada plan via POST /api/v/admin/clients/{client_id}/plans con payload {name, plan_type, methodology, save_template: true, content_json: {...}}.',
                '5. Marcar el ticket como completado: POST /api/v/admin/plan-tickets/{ticket_id}/status body {status: "completado", generated_plan_ids: [ids creados]}.',
            ],
            'upload_endpoint' => 'POST /api/v/admin/clients/{client_id}/plans',
            'required_headers' => [
                'Authorization' => 'Bearer <admin_token>',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'payload_shape_per_plan' => [
                'name' => "string — ej 'Plan Entrenamiento — {Nombre Cliente}'",
                'plan_type' => 'entrenamiento | nutricion | habitos | suplementacion | ciclo',
                'methodology' => "string — ej 'Hipertrofia Progresiva | 4 dias/sem'",
                'save_template' => true,
                'content_json' => 'el JSON del plan segun el schema correspondiente',
            ],
            'references' => [
                'exercise_catalog' => 'CATALOGO_GIF_265.md — usar SOLO ejercicios de ese catalogo con su gif_url',
                'training_methodology' => 'TESIS_ENTRENAMIENTO_WELLCORE.md — periodizacion y progresion',
                'nutrition_methodology' => 'TESIS_NUTRICION_WELLCORE.md — calculo de macros, flexible dieting',
                'elite_protocols' => 'METODOLOGIAS_ELITE_COMPLETAS.md — protocolos hormonales, ciclo, bloodwork',
                'plan_guides' => ['plan-esencial.md', 'plan-metodo.md', 'plan-elite.md'],
            ],
            'apis' => $this->buildApiEndpoints(),
            'entrenamiento' => self::SECTION_INSTRUCTIONS['entrenamiento'],
            'nutricion' => self::SECTION_INSTRUCTIONS['nutricion'],
            'habitos' => self::SECTION_INSTRUCTIONS['habitos'],
            'suplementacion' => self::SECTION_INSTRUCTIONS['suplementacion'],
            'ciclo' => self::SECTION_INSTRUCTIONS['ciclo'],
        ];
    }

    private function buildApiEndpoints(): array
    {
        return [
            'upload_plan' => 'POST /api/v/admin/clients/{client_id}/plans',
            'mark_ticket_completado' => 'POST /api/v/admin/plan-tickets/{ticket_id}/status body {status: "completado", generated_plan_ids: [int,...]}',
            'mark_ticket_en_revision' => 'POST /api/v/admin/plan-tickets/{ticket_id}/status body {status: "en_revision"}',
            'reject_ticket' => 'POST /api/v/admin/plan-tickets/{ticket_id}/status body {status: "rechazado", rejection_code, admin_notas}',
        ];
    }

    private function computeAge(?Carbon $birthDate): ?int
    {
        if (! $birthDate) {
            return null;
        }

        return $birthDate->diffInYears(now());
    }
}
