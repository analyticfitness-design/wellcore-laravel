<?php

namespace App\Livewire\Coach;

use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\ClientProfile;
use App\Models\PlanTemplate;
use App\Services\AIService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Planes'])]
class PlansManager extends Component
{
    // ── Tab state ──
    public string $activeTab = 'my_templates';

    // ── My Templates tab ──
    public string $templateSearch = '';
    public string $templateTypeFilter = '';
    public bool $showTemplateModal = false;
    public bool $editingTemplate = false;
    public ?int $editTemplateId = null;
    public string $tplName = '';
    public string $tplPlanType = 'entrenamiento';
    public string $tplMethodology = '';
    public string $tplDescription = '';
    public string $tplContentJson = '';
    public bool $tplIsPublic = false;
    public bool $showPreviewModal = false;
    public ?array $previewContent = null;
    public string $previewTitle = '';
    public bool $showDeleteConfirm = false;
    public ?int $deleteTemplateId = null;

    // ── Assigned tab ──
    public string $assignedClientFilter = '';
    public string $assignedTypeFilter = '';
    public string $assignedActiveFilter = 'active'; // active | inactive | all
    public bool $showAssignModal = false;
    public ?int $assignClientId = null;
    public ?int $assignTemplateId = null;
    public bool $showAssignedContentModal = false;
    public ?array $assignedContentPreview = null;
    public string $assignedContentTitle = '';

    // ── Generate tab ──
    public int $genStep = 1; // Step A=1, Step B=2
    public string $planType = '';
    public string $methodology = '';
    public int $durationWeeks = 8;
    public int $frequency = 4;
    public ?int $targetClientId = null;
    public string $clientSearch = '';

    // Training params
    public string $experienceLevel = 'intermedio';
    public string $trainingGoal = 'hipertrofia';
    public string $injuries = '';

    // Nutrition params
    public int $calorieTarget = 2200;
    public int $proteinPct = 30;
    public int $carbsPct = 45;
    public int $fatPct = 25;
    public int $mealsPerDay = 4;
    public string $dietaryRestrictions = '';

    // Habits params
    public array $habitFocusAreas = [];

    // Generation state
    public bool $isGenerating = false;
    public bool $planGenerated = false;
    public ?array $generatedPlan = null;
    public string $generatedPlanJson = '';
    public bool $showRawJson = false;
    public string $generationError = '';

    // Save state
    public string $templateName = '';
    public bool $saveAsPublic = false;
    public string $saveMode = 'template_only'; // template_only | template_and_assign
    public bool $saved = false;
    public ?int $savedTemplateId = null;
    public ?int $savedAssignedId = null;

    // ── Methodology definitions ──
    public function getMethodologiesProperty(): array
    {
        return [
            'training' => [
                'progressive_overload' => [
                    'name' => 'Progressive Overload',
                    'desc' => 'Incremento gradual de carga, volumen o intensidad para estimular adaptacion continua.',
                    'icon' => 'arrow-trending-up',
                ],
                'dup' => [
                    'name' => 'DUP (Periodizacion Ondulante)',
                    'desc' => 'Varia intensidad y volumen diariamente para maximizar adaptaciones neuromusculares.',
                    'icon' => 'chart-bar',
                ],
                'block_periodization' => [
                    'name' => 'Periodizacion por Bloques',
                    'desc' => 'Mesociclos enfocados: acumulacion, transmutacion, realizacion.',
                    'icon' => 'squares-2x2',
                ],
                'ppl' => [
                    'name' => 'PPL (Push/Pull/Legs)',
                    'desc' => 'Division en empuje, jalon y piernas — ideal para 3-6 dias por semana.',
                    'icon' => 'arrows-pointing-out',
                ],
                'upper_lower' => [
                    'name' => 'Upper/Lower Split',
                    'desc' => 'Alterna tren superior e inferior — frecuencia y recuperacion.',
                    'icon' => 'arrows-up-down',
                ],
                'full_body' => [
                    'name' => 'Full Body',
                    'desc' => 'Todo el cuerpo cada sesion — alta frecuencia muscular, ideal para 3 dias.',
                    'icon' => 'user',
                ],
                'hiit' => [
                    'name' => 'HIIT',
                    'desc' => 'Intervalos de alta intensidad con periodos de recuperacion.',
                    'icon' => 'clock',
                ],
                'hypertrophy_focused' => [
                    'name' => 'Hipertrofia Enfocada',
                    'desc' => 'Volumen moderado-alto con tecnicas de intensidad: drop sets, super series.',
                    'icon' => 'beaker',
                ],
            ],
            'nutrition' => [
                'iifym' => [
                    'name' => 'Flexible Dieting (IIFYM)',
                    'desc' => 'Si cabe en tus macros, lo puedes comer — flexibilidad con precision nutricional.',
                    'icon' => 'calculator',
                ],
                'keto' => [
                    'name' => 'Keto',
                    'desc' => 'Muy baja en carbohidratos, alta en grasas — cetosis como fuente de energia.',
                    'icon' => 'fire',
                ],
                'reverse_diet' => [
                    'name' => 'Reverse Diet',
                    'desc' => 'Incremento gradual de calorias post-deficit para restaurar el metabolismo.',
                    'icon' => 'arrow-trending-up',
                ],
                'mediterranean' => [
                    'name' => 'Mediterranea',
                    'desc' => 'Alimentos enteros, aceite de oliva, pescado — salud y longevidad.',
                    'icon' => 'globe-americas',
                ],
            ],
        ];
    }

    public function getHabitAreasProperty(): array
    {
        return [
            'sleep' => ['name' => 'Sueno', 'desc' => 'Optimizar calidad y duracion del sueno (7-9h)', 'icon' => 'moon'],
            'hydration' => ['name' => 'Hidratacion', 'desc' => 'Consumo adecuado de agua segun peso corporal', 'icon' => 'beaker'],
            'stress' => ['name' => 'Manejo del Estres', 'desc' => 'Respiracion, meditacion, journaling', 'icon' => 'heart'],
            'mobility' => ['name' => 'Movilidad', 'desc' => 'Rutinas de estiramiento y movilidad articular', 'icon' => 'arrows-pointing-out'],
            'nutrition_habits' => ['name' => 'Habitos Alimenticios', 'desc' => 'Comer consciente, prep de comidas', 'icon' => 'clock'],
            'recovery' => ['name' => 'Recuperacion', 'desc' => 'Foam rolling, descanso activo', 'icon' => 'sparkles'],
        ];
    }

    // ══════════════════════════════════════
    //  TAB SWITCHING
    // ══════════════════════════════════════

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ══════════════════════════════════════
    //  MY TEMPLATES TAB
    // ══════════════════════════════════════

    public function openCreateTemplate(): void
    {
        $this->resetTemplateForm();
        $this->editingTemplate = false;
        $this->editTemplateId = null;
        $this->showTemplateModal = true;
    }

    public function openEditTemplate(int $id): void
    {
        $tpl = PlanTemplate::where('coach_id', auth('wellcore')->id())->find($id);
        if (!$tpl) return;

        $this->editingTemplate = true;
        $this->editTemplateId = $id;
        $this->tplName = $tpl->name;
        $this->tplPlanType = $tpl->plan_type;
        $this->tplMethodology = $tpl->methodology ?? '';
        $this->tplDescription = $tpl->description ?? '';
        $this->tplContentJson = is_array($tpl->content_json) ? json_encode($tpl->content_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : ($tpl->content_json ?? '');
        $this->tplIsPublic = (bool) $tpl->is_public;
        $this->showTemplateModal = true;
    }

    public function saveTemplate(): void
    {
        $this->validate([
            'tplName' => 'required|string|max:160',
            'tplPlanType' => 'required|in:entrenamiento,nutricion,habitos,suplementacion,ciclo',
        ], [
            'tplName.required' => 'El nombre es obligatorio.',
        ]);

        $contentArray = null;
        if (!empty($this->tplContentJson)) {
            $contentArray = json_decode($this->tplContentJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError('tplContentJson', 'JSON invalido: ' . json_last_error_msg());
                return;
            }
        }

        $data = [
            'coach_id' => auth('wellcore')->id(),
            'name' => $this->tplName,
            'plan_type' => $this->tplPlanType,
            'methodology' => $this->tplMethodology,
            'description' => $this->tplDescription,
            'content_json' => $contentArray,
            'is_public' => $this->tplIsPublic,
        ];

        if ($this->editingTemplate && $this->editTemplateId) {
            $tpl = PlanTemplate::where('coach_id', auth('wellcore')->id())->find($this->editTemplateId);
            if ($tpl) {
                $tpl->update($data);
            }
        } else {
            $data['ai_generated'] = false;
            PlanTemplate::create($data);
        }

        $this->showTemplateModal = false;
        $this->resetTemplateForm();
    }

    public function duplicateTemplate(int $id): void
    {
        $tpl = PlanTemplate::where('coach_id', auth('wellcore')->id())->find($id);
        if (!$tpl) return;

        PlanTemplate::create([
            'coach_id' => auth('wellcore')->id(),
            'name' => $tpl->name . ' (copia)',
            'plan_type' => $tpl->plan_type,
            'methodology' => $tpl->methodology,
            'description' => $tpl->description,
            'content_json' => $tpl->content_json,
            'ai_generated' => $tpl->ai_generated,
            'is_public' => false,
        ]);
    }

    public function togglePublic(int $id): void
    {
        $tpl = PlanTemplate::where('coach_id', auth('wellcore')->id())->find($id);
        if ($tpl) {
            $tpl->update(['is_public' => !$tpl->is_public]);
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteTemplateId = $id;
        $this->showDeleteConfirm = true;
    }

    public function deleteTemplate(): void
    {
        if ($this->deleteTemplateId) {
            PlanTemplate::where('coach_id', auth('wellcore')->id())
                ->where('id', $this->deleteTemplateId)
                ->delete();
        }
        $this->showDeleteConfirm = false;
        $this->deleteTemplateId = null;
    }

    public function previewTemplate(int $id): void
    {
        $tpl = PlanTemplate::find($id);
        if (!$tpl) return;

        $this->previewTitle = $tpl->name;
        $this->previewContent = is_array($tpl->content_json) ? $tpl->content_json : json_decode($tpl->content_json ?? '{}', true);
        $this->showPreviewModal = true;
    }

    protected function resetTemplateForm(): void
    {
        $this->tplName = '';
        $this->tplPlanType = 'entrenamiento';
        $this->tplMethodology = '';
        $this->tplDescription = '';
        $this->tplContentJson = '';
        $this->tplIsPublic = false;
        $this->editingTemplate = false;
        $this->editTemplateId = null;
    }

    // ══════════════════════════════════════
    //  ASSIGNED TAB
    // ══════════════════════════════════════

    public function toggleAssignedActive(int $id): void
    {
        $plan = AssignedPlan::where('assigned_by', auth('wellcore')->id())->find($id);
        if ($plan) {
            $plan->update(['active' => !$plan->active]);
        }
    }

    public function viewAssignedContent(int $id): void
    {
        $plan = AssignedPlan::with('client')->where('assigned_by', auth('wellcore')->id())->find($id);
        if (!$plan) return;

        $this->assignedContentTitle = ($plan->client?->name ?? 'Cliente') . ' — ' . ucfirst($plan->plan_type) . ' v' . $plan->version;
        $this->assignedContentPreview = is_array($plan->content) ? $plan->content : json_decode($plan->content ?? '{}', true);
        $this->showAssignedContentModal = true;
    }

    public function openAssignModal(): void
    {
        $this->assignClientId = null;
        $this->assignTemplateId = null;
        $this->showAssignModal = true;
    }

    public function assignPlan(): void
    {
        $this->validate([
            'assignClientId' => 'required|exists:clients,id',
            'assignTemplateId' => 'required|exists:plan_templates,id',
        ], [
            'assignClientId.required' => 'Selecciona un cliente.',
            'assignTemplateId.required' => 'Selecciona un template.',
        ]);

        $template = PlanTemplate::find($this->assignTemplateId);
        if (!$template) return;

        $coachId = auth('wellcore')->id();

        // Deactivate previous plans of same type
        AssignedPlan::where('client_id', $this->assignClientId)
            ->where('plan_type', $template->plan_type)
            ->where('active', true)
            ->update(['active' => false]);

        $latestVersion = AssignedPlan::where('client_id', $this->assignClientId)
            ->where('plan_type', $template->plan_type)
            ->max('version') ?? 0;

        AssignedPlan::create([
            'client_id' => $this->assignClientId,
            'plan_type' => $template->plan_type,
            'content' => $template->content_json,
            'version' => $latestVersion + 1,
            'assigned_by' => $coachId,
            'valid_from' => now()->toDateString(),
            'active' => true,
        ]);

        $this->showAssignModal = false;
        $this->assignClientId = null;
        $this->assignTemplateId = null;
    }

    // ══════════════════════════════════════
    //  GENERATE TAB
    // ══════════════════════════════════════

    public function selectPlanType(string $type): void
    {
        $this->planType = $type;
        $this->methodology = '';
        $this->habitFocusAreas = [];
    }

    public function selectMethodology(string $key): void
    {
        $this->methodology = $key;
    }

    public function toggleHabitArea(string $area): void
    {
        if (in_array($area, $this->habitFocusAreas)) {
            $this->habitFocusAreas = array_values(array_diff($this->habitFocusAreas, [$area]));
        } else {
            $this->habitFocusAreas[] = $area;
        }
    }

    public function goToGenerate(): void
    {
        if (empty($this->planType)) return;
        if ($this->planType === 'entrenamiento' && empty($this->methodology)) return;
        if ($this->planType === 'nutricion' && empty($this->methodology)) return;
        if ($this->planType === 'habitos' && empty($this->habitFocusAreas)) return;

        $this->genStep = 2;

        if (empty($this->templateName)) {
            $this->templateName = $this->buildDefaultTemplateName();
        }
    }

    public function backToConfig(): void
    {
        $this->genStep = 1;
    }

    public function generatePlan(): void
    {
        $this->isGenerating = true;
        $this->generationError = '';
        $this->planGenerated = false;
        $this->generatedPlan = null;

        try {
            $aiService = app(AIService::class);
            $prompt = $this->buildPrompt();
            $systemPrompt = $this->buildSystemPrompt();

            $response = $aiService->generateText($systemPrompt, $prompt, 8192);

            if ($response) {
                $jsonMatch = [];
                if (preg_match('/\{[\s\S]*\}/m', $response, $jsonMatch)) {
                    $decoded = json_decode($jsonMatch[0], true);
                    if ($decoded && json_last_error() === JSON_ERROR_NONE) {
                        $this->generatedPlan = $decoded;
                        $this->generatedPlanJson = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        $this->planGenerated = true;
                        $this->isGenerating = false;
                        return;
                    }
                }
            }

            // Fallback: template-based plan
            Log::info('Coach PlansManager: falling back to template-based generation');
            $this->generatedPlan = $this->generateTemplatePlan();
            $this->generatedPlanJson = json_encode($this->generatedPlan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $this->planGenerated = true;
        } catch (\Exception $e) {
            Log::error('Coach PlansManager generation error', ['message' => $e->getMessage()]);
            $this->generatedPlan = $this->generateTemplatePlan();
            $this->generatedPlanJson = json_encode($this->generatedPlan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $this->planGenerated = true;
        }

        $this->isGenerating = false;
    }

    public function toggleRawJson(): void
    {
        $this->showRawJson = !$this->showRawJson;
    }

    public function updateGeneratedJson(): void
    {
        $decoded = json_decode($this->generatedPlanJson, true);
        if ($decoded && json_last_error() === JSON_ERROR_NONE) {
            $this->generatedPlan = $decoded;
            $this->generationError = '';
        } else {
            $this->generationError = 'JSON invalido: ' . json_last_error_msg();
        }
    }

    public function saveGeneratedPlan(): void
    {
        $this->validate([
            'templateName' => 'required|string|max:160',
        ], [
            'templateName.required' => 'El nombre de la plantilla es obligatorio.',
        ]);

        $coachId = auth('wellcore')->id();
        $methodologyLabel = $this->getMethodologyLabel();

        $template = PlanTemplate::create([
            'coach_id' => $coachId,
            'name' => $this->templateName,
            'plan_type' => $this->planType,
            'methodology' => $methodologyLabel,
            'description' => $this->buildDescription(),
            'content_json' => $this->generatedPlan,
            'ai_generated' => true,
            'is_public' => $this->saveAsPublic,
        ]);

        $this->savedTemplateId = $template->id;

        if ($this->saveMode === 'template_and_assign' && $this->targetClientId) {
            AssignedPlan::where('client_id', $this->targetClientId)
                ->where('plan_type', $this->planType)
                ->where('active', true)
                ->update(['active' => false]);

            $latestVersion = AssignedPlan::where('client_id', $this->targetClientId)
                ->where('plan_type', $this->planType)
                ->max('version') ?? 0;

            $assigned = AssignedPlan::create([
                'client_id' => $this->targetClientId,
                'plan_type' => $this->planType,
                'content' => $this->generatedPlan,
                'version' => $latestVersion + 1,
                'assigned_by' => $coachId,
                'valid_from' => now()->toDateString(),
                'active' => true,
            ]);

            $this->savedAssignedId = $assigned->id;
        }

        $this->saved = true;
    }

    public function startNewGeneration(): void
    {
        $this->genStep = 1;
        $this->planType = '';
        $this->methodology = '';
        $this->durationWeeks = 8;
        $this->frequency = 4;
        $this->targetClientId = null;
        $this->clientSearch = '';
        $this->experienceLevel = 'intermedio';
        $this->trainingGoal = 'hipertrofia';
        $this->injuries = '';
        $this->calorieTarget = 2200;
        $this->proteinPct = 30;
        $this->carbsPct = 45;
        $this->fatPct = 25;
        $this->mealsPerDay = 4;
        $this->dietaryRestrictions = '';
        $this->habitFocusAreas = [];
        $this->isGenerating = false;
        $this->planGenerated = false;
        $this->generatedPlan = null;
        $this->generatedPlanJson = '';
        $this->showRawJson = false;
        $this->generationError = '';
        $this->templateName = '';
        $this->saveAsPublic = false;
        $this->saveMode = 'template_only';
        $this->saved = false;
        $this->savedTemplateId = null;
        $this->savedAssignedId = null;
    }

    // ══════════════════════════════════════
    //  PROMPT / AI BUILDERS
    // ══════════════════════════════════════

    protected function buildSystemPrompt(): string
    {
        $methodologyLabel = $this->getMethodologyLabel();

        if ($this->planType === 'entrenamiento') {
            return "Eres un coach de fitness certificado con 15 anos de experiencia especializado en la metodologia '{$methodologyLabel}'.
Genera un plan de entrenamiento COMPLETO y DETALLADO en formato JSON puro (sin markdown, sin explicaciones extra).
El JSON debe tener esta estructura:
{
  \"plan_type\": \"entrenamiento\",
  \"methodology\": \"{$methodologyLabel}\",
  \"duration_weeks\": {$this->durationWeeks},
  \"frequency\": {$this->frequency},
  \"goal\": \"{$this->trainingGoal}\",
  \"level\": \"{$this->experienceLevel}\",
  \"weeks\": [
    {
      \"week\": 1,
      \"focus\": \"descripcion del enfoque de la semana\",
      \"sessions\": [
        {
          \"day\": 1,
          \"name\": \"Nombre de la sesion\",
          \"muscle_groups\": [\"pecho\", \"triceps\"],
          \"warmup\": \"descripcion calentamiento\",
          \"exercises\": [
            {\"name\": \"ejercicio\", \"sets\": 4, \"reps\": \"8-12\", \"rest\": \"90s\", \"rpe\": 8, \"notes\": \"nota\"}
          ],
          \"cooldown\": \"vuelta a la calma\"
        }
      ]
    }
  ],
  \"progression_notes\": \"notas de progresion\",
  \"deload_protocol\": \"protocolo de descarga\"
}
Incluye al menos las primeras 2 semanas detalladas. Responde SOLO con JSON valido.";
        }

        if ($this->planType === 'nutricion') {
            return "Eres un nutricionista deportivo certificado especializado en el enfoque '{$methodologyLabel}'.
Genera un plan nutricional COMPLETO en formato JSON puro (sin markdown, sin explicaciones extra).
El JSON debe tener esta estructura:
{
  \"plan_type\": \"nutricion\",
  \"approach\": \"{$methodologyLabel}\",
  \"duration_weeks\": {$this->durationWeeks},
  \"calories\": {$this->calorieTarget},
  \"macros\": {\"protein_g\": 0, \"protein_pct\": {$this->proteinPct}, \"carbs_g\": 0, \"carbs_pct\": {$this->carbsPct}, \"fat_g\": 0, \"fat_pct\": {$this->fatPct}},
  \"meals_per_day\": {$this->mealsPerDay},
  \"meal_plan\": [{\"meal_number\": 1, \"name\": \"Desayuno\", \"time\": \"7:00\", \"calories\": 500, \"foods\": [{\"name\": \"alimento\", \"quantity\": \"100g\", \"protein\": 20, \"carbs\": 30, \"fat\": 10}]}],
  \"weekly_adjustments\": \"ajustes semanales\",
  \"supplements\": [\"creatina 5g\"],
  \"hydration\": \"recomendaciones\",
  \"restrictions_notes\": \"notas\"
}
Responde SOLO con JSON valido.";
        }

        $areas = implode(', ', array_map(fn($a) => $this->habitAreas[$a]['name'] ?? $a, $this->habitFocusAreas));
        return "Eres un coach de habitos y bienestar especializado en: {$areas}.
Genera un plan de habitos COMPLETO en formato JSON puro.
El JSON debe tener esta estructura:
{
  \"plan_type\": \"habitos\",
  \"focus_areas\": [\"{$areas}\"],
  \"duration_weeks\": {$this->durationWeeks},
  \"habits\": [{\"area\": \"nombre\", \"habit\": \"descripcion\", \"frequency\": \"diario\", \"metric\": \"como medir\", \"target\": \"objetivo\", \"weeks_progression\": [{\"week\": 1, \"goal\": \"meta\"}]}],
  \"daily_routine\": {\"morning\": [], \"afternoon\": [], \"evening\": []},
  \"tracking_method\": \"seguimiento\",
  \"accountability\": \"adherencia\"
}
Responde SOLO con JSON valido.";
    }

    protected function buildPrompt(): string
    {
        $client = null;
        if ($this->targetClientId) {
            $clientModel = Client::with('profile')->find($this->targetClientId);
            if ($clientModel) {
                $age = $clientModel->birth_date ? Carbon::parse($clientModel->birth_date)->age : null;
                $client = [
                    'name' => $clientModel->name,
                    'age' => $age,
                    'peso' => $clientModel->profile?->peso,
                    'altura' => $clientModel->profile?->altura,
                    'objetivo' => $clientModel->profile?->objetivo ?? '-',
                    'nivel' => $clientModel->profile?->nivel ?? '-',
                    'lugar_entreno' => $clientModel->profile?->lugar_entreno ?? '-',
                    'dias_disponibles' => $clientModel->profile?->dias_disponibles ?? [],
                    'plan' => $clientModel->plan?->value ?? '-',
                    'restricciones' => $clientModel->profile?->restricciones ?? '',
                ];
            }
        }

        $lines = [];

        if ($client) {
            $lines[] = "DATOS DEL CLIENTE:";
            $lines[] = "- Nombre: {$client['name']}";
            $lines[] = "- Edad: " . ($client['age'] ?? 'No especificada');
            $lines[] = "- Peso: " . ($client['peso'] ? "{$client['peso']} kg" : 'No especificado');
            $lines[] = "- Altura: " . ($client['altura'] ? "{$client['altura']} cm" : 'No especificada');
            $lines[] = "- Objetivo: {$client['objetivo']}";
            $lines[] = "- Nivel: {$client['nivel']}";
            $lines[] = "- Lugar de entreno: {$client['lugar_entreno']}";
            $lines[] = "- Dias disponibles: " . (is_array($client['dias_disponibles']) ? count($client['dias_disponibles']) . " dias" : $client['dias_disponibles']);
            $lines[] = "";
        } else {
            $lines[] = "PLAN GENERICO (sin cliente especifico):";
            $lines[] = "";
        }

        $lines[] = "CONFIGURACION DEL PLAN:";
        $lines[] = "- Tipo: {$this->planType}";
        $lines[] = "- Duracion: {$this->durationWeeks} semanas";
        $lines[] = "- Frecuencia: {$this->frequency} dias/semana";

        if ($this->planType === 'entrenamiento') {
            $lines[] = "- Metodologia: " . $this->getMethodologyLabel();
            $lines[] = "- Meta: {$this->trainingGoal}";
            $lines[] = "- Nivel: {$this->experienceLevel}";
            if ($this->injuries) $lines[] = "- Lesiones: {$this->injuries}";
        } elseif ($this->planType === 'nutricion') {
            $lines[] = "- Enfoque: " . $this->getMethodologyLabel();
            $lines[] = "- Calorias: {$this->calorieTarget} kcal";
            $lines[] = "- Macros: P {$this->proteinPct}%, C {$this->carbsPct}%, G {$this->fatPct}%";
            $lines[] = "- Comidas/dia: {$this->mealsPerDay}";
            if ($this->dietaryRestrictions) $lines[] = "- Restricciones: {$this->dietaryRestrictions}";
            if ($client && $client['restricciones']) $lines[] = "- Restricciones perfil: {$client['restricciones']}";
        } else {
            $areas = implode(', ', array_map(fn($a) => $this->habitAreas[$a]['name'] ?? $a, $this->habitFocusAreas));
            $lines[] = "- Areas: {$areas}";
        }

        $lines[] = "";
        $lines[] = "Genera el plan completo en formato JSON. Solo JSON, sin texto adicional.";

        return implode("\n", $lines);
    }

    // ── Template fallback generators (reuse admin logic) ──

    protected function generateTemplatePlan(): array
    {
        return match ($this->planType) {
            'entrenamiento' => $this->generateTrainingTemplate(),
            'nutricion' => $this->generateNutritionTemplate(),
            'habitos' => $this->generateHabitsTemplate(),
            default => ['plan_type' => $this->planType, 'note' => 'Plan tipo no soportado para generacion automatica'],
        };
    }

    protected function generateTrainingTemplate(): array
    {
        $methodologyLabel = $this->getMethodologyLabel();
        $exercises = [
            ['name' => 'Press Banca', 'sets' => 4, 'reps' => '8-12', 'rest' => '90s', 'rpe' => 8, 'notes' => 'Controlar fase excentrica'],
            ['name' => 'Sentadilla', 'sets' => 4, 'reps' => '8-12', 'rest' => '120s', 'rpe' => 8, 'notes' => 'Profundidad completa'],
            ['name' => 'Remo con Barra', 'sets' => 4, 'reps' => '8-12', 'rest' => '90s', 'rpe' => 7, 'notes' => 'Escapulas retraidas'],
            ['name' => 'Press Militar', 'sets' => 3, 'reps' => '10-12', 'rest' => '90s', 'rpe' => 7, 'notes' => 'Sin impulso'],
            ['name' => 'Peso Muerto Rumano', 'sets' => 3, 'reps' => '10-12', 'rest' => '90s', 'rpe' => 7, 'notes' => 'Estirar isquiotibiales'],
            ['name' => 'Curl Biceps', 'sets' => 3, 'reps' => '12-15', 'rest' => '60s', 'rpe' => 7, 'notes' => 'Supinacion completa'],
            ['name' => 'Extension Triceps', 'sets' => 3, 'reps' => '12-15', 'rest' => '60s', 'rpe' => 7, 'notes' => 'Codo fijo'],
            ['name' => 'Elevaciones Laterales', 'sets' => 3, 'reps' => '15-20', 'rest' => '45s', 'rpe' => 8, 'notes' => 'Control total'],
            ['name' => 'Hip Thrust', 'sets' => 4, 'reps' => '10-12', 'rest' => '90s', 'rpe' => 8, 'notes' => 'Pausa arriba 2s'],
            ['name' => 'Dominadas', 'sets' => 3, 'reps' => '6-10', 'rest' => '120s', 'rpe' => 8, 'notes' => 'Rango completo'],
            ['name' => 'Zancadas', 'sets' => 3, 'reps' => '10/lado', 'rest' => '60s', 'rpe' => 7, 'notes' => 'Paso largo'],
            ['name' => 'Face Pull', 'sets' => 3, 'reps' => '15-20', 'rest' => '45s', 'rpe' => 6, 'notes' => 'Rotacion externa'],
        ];

        $sessionTemplates = [];
        if ($this->frequency <= 3) {
            $sessionTemplates = [
                ['name' => 'Full Body A', 'muscle_groups' => ['pecho', 'espalda', 'piernas'], 'warmup' => '5 min cardio + movilidad', 'exercises' => array_slice($exercises, 0, 5), 'cooldown' => 'Estiramiento 5 min'],
                ['name' => 'Full Body B', 'muscle_groups' => ['piernas', 'espalda', 'brazos'], 'warmup' => '5 min cardio + movilidad', 'exercises' => array_slice($exercises, 3, 5), 'cooldown' => 'Estiramiento 5 min'],
                ['name' => 'Full Body C', 'muscle_groups' => ['piernas', 'hombros', 'core'], 'warmup' => '5 min cardio + movilidad', 'exercises' => array_slice($exercises, 6, 5), 'cooldown' => 'Estiramiento 5 min'],
            ];
        } elseif ($this->frequency <= 4) {
            $sessionTemplates = [
                ['name' => 'Tren Superior A', 'muscle_groups' => ['pecho', 'espalda', 'hombros'], 'warmup' => '5 min cardio + movilidad hombros', 'exercises' => array_values(array_filter([$exercises[0], $exercises[2], $exercises[3], $exercises[5], $exercises[6]])), 'cooldown' => 'Estiramiento tren superior'],
                ['name' => 'Tren Inferior A', 'muscle_groups' => ['cuadriceps', 'isquiotibiales', 'gluteos'], 'warmup' => '5 min cardio + movilidad caderas', 'exercises' => array_values(array_filter([$exercises[1], $exercises[4], $exercises[8], $exercises[10]])), 'cooldown' => 'Estiramiento tren inferior'],
                ['name' => 'Tren Superior B', 'muscle_groups' => ['pecho', 'espalda', 'brazos'], 'warmup' => '5 min cardio + movilidad', 'exercises' => array_values(array_filter([$exercises[0], $exercises[9], $exercises[7], $exercises[11], $exercises[5]])), 'cooldown' => 'Estiramiento tren superior'],
                ['name' => 'Tren Inferior B', 'muscle_groups' => ['cuadriceps', 'isquiotibiales', 'gluteos'], 'warmup' => '5 min cardio + movilidad caderas', 'exercises' => array_values(array_filter([$exercises[1], $exercises[8], $exercises[10], $exercises[4]])), 'cooldown' => 'Estiramiento tren inferior'],
            ];
        } else {
            $sessionTemplates = [
                ['name' => 'Push (Empuje)', 'muscle_groups' => ['pecho', 'hombros', 'triceps'], 'warmup' => '5 min cardio + activacion', 'exercises' => array_values(array_filter([$exercises[0], $exercises[3], $exercises[7], $exercises[6]])), 'cooldown' => 'Estiramiento pectoral y deltoides'],
                ['name' => 'Pull (Jalon)', 'muscle_groups' => ['espalda', 'biceps'], 'warmup' => '5 min cardio + movilidad escapular', 'exercises' => array_values(array_filter([$exercises[2], $exercises[9], $exercises[11], $exercises[5]])), 'cooldown' => 'Estiramiento dorsal y biceps'],
                ['name' => 'Legs (Piernas)', 'muscle_groups' => ['cuadriceps', 'isquiotibiales', 'gluteos'], 'warmup' => '5 min cardio + movilidad caderas', 'exercises' => array_values(array_filter([$exercises[1], $exercises[4], $exercises[8], $exercises[10]])), 'cooldown' => 'Estiramiento tren inferior'],
            ];
        }

        $weeks = [];
        for ($w = 1; $w <= min($this->durationWeeks, 4); $w++) {
            $sessions = [];
            for ($d = 1; $d <= $this->frequency; $d++) {
                $idx = ($d - 1) % count($sessionTemplates);
                $s = $sessionTemplates[$idx];
                $s['day'] = $d;
                $sessions[] = $s;
            }
            $weeks[] = [
                'week' => $w,
                'focus' => $w <= 2 ? 'Adaptacion y tecnica' : ($w <= 3 ? 'Incremento progresivo' : 'Intensificacion'),
                'sessions' => $sessions,
            ];
        }

        return [
            'plan_type' => 'entrenamiento',
            'methodology' => $methodologyLabel,
            'duration_weeks' => $this->durationWeeks,
            'frequency' => $this->frequency,
            'goal' => $this->trainingGoal,
            'level' => $this->experienceLevel,
            'weeks' => $weeks,
            'progression_notes' => 'Aumentar carga 2.5-5% cada semana si se completan todas las reps con buena tecnica.',
            'deload_protocol' => 'Cada 4 semanas reducir volumen al 60% y carga al 70%.',
            'generated_by' => 'template',
        ];
    }

    protected function generateNutritionTemplate(): array
    {
        $methodologyLabel = $this->getMethodologyLabel();
        $protGrams = round($this->calorieTarget * ($this->proteinPct / 100) / 4);
        $carbsGrams = round($this->calorieTarget * ($this->carbsPct / 100) / 4);
        $fatGrams = round($this->calorieTarget * ($this->fatPct / 100) / 9);
        $calPerMeal = round($this->calorieTarget / $this->mealsPerDay);

        $mealNames = ['Desayuno', 'Media Manana', 'Almuerzo', 'Merienda', 'Cena', 'Pre-sueno'];
        $mealTimes = ['7:00', '10:00', '13:00', '16:00', '19:30', '21:30'];
        $mealFoods = [
            [['name' => 'Avena', 'quantity' => '80g', 'protein' => 10, 'carbs' => 54, 'fat' => 6], ['name' => 'Claras de huevo', 'quantity' => '200ml', 'protein' => 22, 'carbs' => 2, 'fat' => 0], ['name' => 'Banano', 'quantity' => '1 unidad', 'protein' => 1, 'carbs' => 27, 'fat' => 0]],
            [['name' => 'Yogurt griego', 'quantity' => '200g', 'protein' => 18, 'carbs' => 8, 'fat' => 10], ['name' => 'Frutos rojos', 'quantity' => '100g', 'protein' => 1, 'carbs' => 12, 'fat' => 0], ['name' => 'Almendras', 'quantity' => '20g', 'protein' => 4, 'carbs' => 2, 'fat' => 10]],
            [['name' => 'Pechuga de pollo', 'quantity' => '200g', 'protein' => 46, 'carbs' => 0, 'fat' => 6], ['name' => 'Arroz integral', 'quantity' => '150g', 'protein' => 4, 'carbs' => 45, 'fat' => 1], ['name' => 'Vegetales mixtos', 'quantity' => '200g', 'protein' => 4, 'carbs' => 12, 'fat' => 0]],
            [['name' => 'Whey Protein', 'quantity' => '1 scoop', 'protein' => 25, 'carbs' => 3, 'fat' => 2], ['name' => 'Manzana', 'quantity' => '1 unidad', 'protein' => 0, 'carbs' => 25, 'fat' => 0]],
            [['name' => 'Salmon', 'quantity' => '180g', 'protein' => 36, 'carbs' => 0, 'fat' => 18], ['name' => 'Batata', 'quantity' => '200g', 'protein' => 3, 'carbs' => 40, 'fat' => 0], ['name' => 'Ensalada verde', 'quantity' => '150g', 'protein' => 2, 'carbs' => 5, 'fat' => 0]],
            [['name' => 'Caseina', 'quantity' => '1 scoop', 'protein' => 24, 'carbs' => 3, 'fat' => 1], ['name' => 'Nueces', 'quantity' => '15g', 'protein' => 2, 'carbs' => 1, 'fat' => 10]],
        ];

        $meals = [];
        for ($i = 0; $i < $this->mealsPerDay && $i < 6; $i++) {
            $meals[] = ['meal_number' => $i + 1, 'name' => $mealNames[$i] ?? "Comida " . ($i + 1), 'time' => $mealTimes[$i] ?? '12:00', 'calories' => $calPerMeal, 'foods' => $mealFoods[$i] ?? $mealFoods[0]];
        }

        return [
            'plan_type' => 'nutricion',
            'approach' => $methodologyLabel,
            'duration_weeks' => $this->durationWeeks,
            'calories' => $this->calorieTarget,
            'macros' => ['protein_g' => $protGrams, 'protein_pct' => $this->proteinPct, 'carbs_g' => $carbsGrams, 'carbs_pct' => $this->carbsPct, 'fat_g' => $fatGrams, 'fat_pct' => $this->fatPct],
            'meals_per_day' => $this->mealsPerDay,
            'meal_plan' => $meals,
            'weekly_adjustments' => 'Revisar peso y medidas semanalmente. Ajustar +/- 100 kcal segun progreso.',
            'supplements' => ['Creatina 5g/dia', 'Vitamina D3 4000 IU', 'Omega-3 2g/dia'],
            'hydration' => 'Minimo 35ml por kg de peso corporal.',
            'restrictions_notes' => $this->dietaryRestrictions ?: 'Sin restricciones reportadas',
            'generated_by' => 'template',
        ];
    }

    protected function generateHabitsTemplate(): array
    {
        $allHabitTemplates = [
            'sleep' => [
                ['habit' => 'Dormir 7-8 horas cada noche', 'frequency' => 'diario', 'metric' => 'Horas de sueno', 'target' => '7.5 horas promedio'],
                ['habit' => 'Apagar pantallas 1h antes de dormir', 'frequency' => 'diario', 'metric' => 'Si/No', 'target' => '6 de 7 dias'],
            ],
            'hydration' => [
                ['habit' => 'Tomar minimo 2.5L de agua al dia', 'frequency' => 'diario', 'metric' => 'Litros', 'target' => '2.5L minimo'],
                ['habit' => 'Vaso de agua al despertar', 'frequency' => 'diario', 'metric' => 'Si/No', 'target' => '7 de 7 dias'],
            ],
            'stress' => [
                ['habit' => 'Meditacion o respiracion 10 min', 'frequency' => 'diario', 'metric' => 'Minutos', 'target' => '10 min/dia'],
                ['habit' => 'Journaling antes de dormir', 'frequency' => 'diario', 'metric' => 'Si/No', 'target' => '5 de 7 dias'],
            ],
            'mobility' => [
                ['habit' => 'Rutina de movilidad 15 min', 'frequency' => 'diario', 'metric' => 'Minutos', 'target' => '15 min/dia'],
                ['habit' => 'Foam rolling post-entrenamiento', 'frequency' => '3x/semana', 'metric' => 'Sesiones', 'target' => '3/semana'],
            ],
            'nutrition_habits' => [
                ['habit' => 'Meal prep dominical', 'frequency' => 'semanal', 'metric' => 'Si/No', 'target' => '4 de 4 semanas'],
                ['habit' => 'Comer sin pantallas', 'frequency' => 'diario', 'metric' => 'Comidas', 'target' => '2 de 3 comidas'],
            ],
            'recovery' => [
                ['habit' => 'Dia de descanso activo semanal', 'frequency' => 'semanal', 'metric' => 'Si/No', 'target' => '1 dia/semana'],
                ['habit' => 'Estiramientos antes de dormir', 'frequency' => 'diario', 'metric' => 'Minutos', 'target' => '10 min/dia'],
            ],
        ];

        $habits = [];
        foreach ($this->habitFocusAreas as $area) {
            $areaName = $this->habitAreas[$area]['name'] ?? $area;
            $templates = $allHabitTemplates[$area] ?? [];
            foreach ($templates as $t) {
                $progression = [];
                for ($w = 1; $w <= min($this->durationWeeks, 4); $w++) {
                    $progression[] = ['week' => $w, 'goal' => "Semana {$w}: " . match ($w) { 1 => 'Establecer habito', 2 => 'Consolidar', 3 => 'Aumentar exigencia', default => 'Automatizar' }];
                }
                $habits[] = array_merge($t, ['area' => $areaName, 'weeks_progression' => $progression]);
            }
        }

        return [
            'plan_type' => 'habitos',
            'focus_areas' => array_map(fn($a) => $this->habitAreas[$a]['name'] ?? $a, $this->habitFocusAreas),
            'duration_weeks' => $this->durationWeeks,
            'habits' => $habits,
            'daily_routine' => [
                'morning' => ['Vaso de agua al despertar', 'Respiracion 4-7-8 (5 min)', '3 cosas por agradecer'],
                'afternoon' => ['Caminar 10 min post-almuerzo', 'Hidratacion check'],
                'evening' => ['Apagar pantallas 21:00', 'Estiramientos 10 min'],
            ],
            'tracking_method' => 'Registro diario en WellCore. Check-in semanal con coach.',
            'accountability' => 'Compartir progreso en comunidad. Check-in con coach cada lunes.',
            'generated_by' => 'template',
        ];
    }

    // ── Helpers ──

    protected function getMethodologyLabel(): string
    {
        if ($this->planType === 'entrenamiento') {
            return $this->methodologies['training'][$this->methodology]['name'] ?? $this->methodology;
        }
        if ($this->planType === 'nutricion') {
            return $this->methodologies['nutrition'][$this->methodology]['name'] ?? $this->methodology;
        }
        return implode(', ', array_map(fn($a) => $this->habitAreas[$a]['name'] ?? $a, $this->habitFocusAreas));
    }

    protected function buildDefaultTemplateName(): string
    {
        $type = match ($this->planType) {
            'entrenamiento' => 'Entrenamiento',
            'nutricion' => 'Nutricion',
            'habitos' => 'Habitos',
            default => ucfirst($this->planType),
        };
        $method = $this->getMethodologyLabel();
        $clientName = '';
        if ($this->targetClientId) {
            $c = Client::find($this->targetClientId);
            $clientName = $c ? " — {$c->name}" : '';
        }
        return "{$type} — {$method}{$clientName}";
    }

    protected function buildDescription(): string
    {
        $method = $this->getMethodologyLabel();
        $clientName = '';
        if ($this->targetClientId) {
            $c = Client::find($this->targetClientId);
            $clientName = $c ? " para {$c->name}" : '';
        }
        return "Plan generado por IA{$clientName}. Metodologia: {$method}. Duracion: {$this->durationWeeks} semanas, {$this->frequency} dias/semana.";
    }

    // ══════════════════════════════════════
    //  RENDER
    // ══════════════════════════════════════

    public function render()
    {
        $coachId = auth('wellcore')->id();

        // My templates
        $templatesQuery = PlanTemplate::where('coach_id', $coachId);
        if ($this->templateTypeFilter) {
            $templatesQuery->where('plan_type', $this->templateTypeFilter);
        }
        if ($this->templateSearch) {
            $templatesQuery->where(function ($q) {
                $q->where('name', 'like', "%{$this->templateSearch}%")
                  ->orWhere('methodology', 'like', "%{$this->templateSearch}%");
            });
        }
        $templates = $templatesQuery->orderByDesc('updated_at')->get();

        // Template stats
        $allCoachTemplates = PlanTemplate::where('coach_id', $coachId)->get();
        $templateStats = [
            'total' => $allCoachTemplates->count(),
            'entrenamiento' => $allCoachTemplates->where('plan_type', 'entrenamiento')->count(),
            'nutricion' => $allCoachTemplates->where('plan_type', 'nutricion')->count(),
            'habitos' => $allCoachTemplates->where('plan_type', 'habitos')->count(),
            'ai_generated' => $allCoachTemplates->where('ai_generated', true)->count(),
        ];

        // Assigned plans
        $assignedQuery = AssignedPlan::with('client')
            ->where('assigned_by', $coachId);
        if ($this->assignedActiveFilter === 'active') {
            $assignedQuery->where('active', true);
        } elseif ($this->assignedActiveFilter === 'inactive') {
            $assignedQuery->where('active', false);
        }
        if ($this->assignedTypeFilter) {
            $assignedQuery->where('plan_type', $this->assignedTypeFilter);
        }
        if ($this->assignedClientFilter) {
            $assignedQuery->whereHas('client', function ($q) {
                $q->where('name', 'like', "%{$this->assignedClientFilter}%");
            });
        }
        $assignedPlans = $assignedQuery->orderByDesc('created_at')->get();

        // Clients list (for assign modal & generate tab)
        $clients = Client::where('status', 'activo')->orderBy('name')->get(['id', 'name', 'email', 'plan']);

        // Client search for generate tab
        $searchClients = [];
        if (strlen($this->clientSearch) >= 2) {
            $searchClients = Client::where(function ($q) {
                    $q->where('name', 'like', "%{$this->clientSearch}%")
                      ->orWhere('email', 'like', "%{$this->clientSearch}%");
                })
                ->where('status', 'activo')
                ->orderBy('name')
                ->limit(15)
                ->get(['id', 'name', 'email', 'plan']);
        }

        // Templates for assign modal
        $assignableTemplates = PlanTemplate::where(function ($q) use ($coachId) {
            $q->where('coach_id', $coachId)->orWhere('is_public', true);
        })->orderBy('name')->get(['id', 'name', 'plan_type', 'methodology']);

        return view('livewire.coach.plans-manager', [
            'templates' => $templates,
            'templateStats' => $templateStats,
            'assignedPlans' => $assignedPlans,
            'clients' => $clients,
            'searchClients' => $searchClients,
            'assignableTemplates' => $assignableTemplates,
        ]);
    }
}
