<?php

namespace App\Livewire\Admin;

use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\ClientProfile;
use App\Models\PlanTemplate;
use App\Services\AIService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin', ['title' => 'AI Plan Generator'])]
class AIPlanGenerator extends Component
{
    // ── Wizard state ──
    public int $currentStep = 1;

    // ── Step 1: Client selection ──
    public string $clientSearch = '';
    public ?int $selectedClientId = null;
    public ?array $selectedClientData = null;

    // ── Step 2: Plan configuration ──
    public string $planType = '';
    public string $methodology = '';
    public int $durationWeeks = 8;
    public int $frequency = 4;

    // Training params
    public string $experienceLevel = 'intermedio';
    public string $trainingGoal = 'hipertrofia';
    public array $equipmentAvailable = [];
    public string $injuries = '';

    // Nutrition params
    public int $calorieTarget = 2200;
    public int $proteinPct = 30;
    public int $carbsPct = 45;
    public int $fatPct = 25;
    public string $dietaryRestrictions = '';
    public int $mealsPerDay = 4;

    // Habits params
    public array $habitFocusAreas = [];

    // ── Step 3: AI generation ──
    public bool $isGenerating = false;
    public bool $planGenerated = false;
    public ?array $generatedPlan = null;
    public string $generatedPlanJson = '';
    public bool $showRawJson = false;
    public string $generationError = '';

    // ── Step 4: Save & assign ──
    public string $templateName = '';
    public bool $isPublic = false;
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
                    'desc' => 'Incremento gradual de carga, volumen o intensidad en cada sesion para estimular adaptacion continua.',
                    'icon' => 'arrow-trending-up',
                ],
                'dup' => [
                    'name' => 'DUP (Periodizacion Ondulante Diaria)',
                    'desc' => 'Varia intensidad y volumen diariamente para maximizar adaptaciones neuromusculares.',
                    'icon' => 'chart-bar',
                ],
                'block_periodization' => [
                    'name' => 'Periodizacion por Bloques',
                    'desc' => 'Divide el entrenamiento en mesociclos enfocados: acumulacion, transmutacion, realizacion.',
                    'icon' => 'squares-2x2',
                ],
                'linear_periodization' => [
                    'name' => 'Periodizacion Lineal',
                    'desc' => 'Progresion lineal de volumen alto/intensidad baja hacia volumen bajo/intensidad alta.',
                    'icon' => 'arrow-long-right',
                ],
                'conjugate' => [
                    'name' => 'Conjugate (Westside)',
                    'desc' => 'Combina esfuerzo maximo y esfuerzo dinamico con rotacion de ejercicios accesorios.',
                    'icon' => 'arrows-right-left',
                ],
                'gvt' => [
                    'name' => 'German Volume Training',
                    'desc' => '10 series de 10 repeticiones por ejercicio — hipertrofia extrema por volumen acumulado.',
                    'icon' => 'fire',
                ],
                'wendler_531' => [
                    'name' => '5/3/1 Wendler',
                    'desc' => 'Ciclos de 4 semanas basados en porcentajes del 1RM para fuerza progresiva sostenible.',
                    'icon' => 'bolt',
                ],
                'starting_strength' => [
                    'name' => 'Starting Strength',
                    'desc' => 'Programa basico de fuerza con movimientos compuestos: sentadilla, press, peso muerto.',
                    'icon' => 'trophy',
                ],
                'ppl' => [
                    'name' => 'PPL (Push/Pull/Legs)',
                    'desc' => 'Division en empuje, jalon y piernas — ideal para 3-6 dias por semana.',
                    'icon' => 'arrows-pointing-out',
                ],
                'upper_lower' => [
                    'name' => 'Upper/Lower Split',
                    'desc' => 'Alterna tren superior e inferior — equilibrio entre frecuencia y recuperacion.',
                    'icon' => 'arrows-up-down',
                ],
                'full_body' => [
                    'name' => 'Full Body',
                    'desc' => 'Entrena todo el cuerpo cada sesion — alta frecuencia muscular, ideal para 3 dias.',
                    'icon' => 'user',
                ],
                'hiit' => [
                    'name' => 'HIIT',
                    'desc' => 'Intervalos de alta intensidad con periodos de recuperacion — quema calorica elevada.',
                    'icon' => 'clock',
                ],
                'crossfit_style' => [
                    'name' => 'CrossFit-style',
                    'desc' => 'WODs variados combinando fuerza, cardio y gimnasia — alto rendimiento funcional.',
                    'icon' => 'sparkles',
                ],
                'calisthenics' => [
                    'name' => 'Calistenia',
                    'desc' => 'Entrenamiento con peso corporal — progresiones de habilidad y fuerza relativa.',
                    'icon' => 'hand-raised',
                ],
                'powerlifting' => [
                    'name' => 'Powerlifting',
                    'desc' => 'Enfoque en sentadilla, press banca y peso muerto — maximizar el 1RM.',
                    'icon' => 'scale',
                ],
                'hypertrophy_focused' => [
                    'name' => 'Hipertrofia Enfocada',
                    'desc' => 'Volumen moderado-alto con tecnicas de intensidad: drop sets, super series, TUT.',
                    'icon' => 'beaker',
                ],
                'strength_endurance' => [
                    'name' => 'Fuerza-Resistencia',
                    'desc' => 'Combina cargas moderadas con volumen alto — ideal para deportes de resistencia.',
                    'icon' => 'heart',
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
                    'desc' => 'Muy baja en carbohidratos, alta en grasas — cetosis como fuente principal de energia.',
                    'icon' => 'fire',
                ],
                'reverse_diet' => [
                    'name' => 'Reverse Diet',
                    'desc' => 'Incremento gradual de calorias post-deficit para restaurar el metabolismo.',
                    'icon' => 'arrow-trending-up',
                ],
                'mediterranean' => [
                    'name' => 'Mediterranea',
                    'desc' => 'Basada en alimentos enteros, aceite de oliva, pescado — salud cardiovascular y longevidad.',
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
            'stress' => ['name' => 'Manejo del Estres', 'desc' => 'Tecnicas de respiracion, meditacion, journaling', 'icon' => 'heart'],
            'mobility' => ['name' => 'Movilidad', 'desc' => 'Rutinas de estiramiento y movilidad articular', 'icon' => 'arrows-pointing-out'],
            'nutrition_habits' => ['name' => 'Habitos Alimenticios', 'desc' => 'Comer consciente, prep de comidas, horarios', 'icon' => 'clock'],
            'recovery' => ['name' => 'Recuperacion', 'desc' => 'Foam rolling, banos de contraste, descanso activo', 'icon' => 'sparkles'],
        ];
    }

    // ── Navigation ──

    public function nextStep(): void
    {
        if ($this->currentStep === 1 && !$this->selectedClientId) {
            return;
        }
        if ($this->currentStep === 2) {
            if (empty($this->planType)) return;
            if ($this->planType === 'entrenamiento' && empty($this->methodology)) return;
            if ($this->planType === 'nutricion' && empty($this->methodology)) return;
            if ($this->planType === 'habitos' && empty($this->habitFocusAreas)) return;
        }
        if ($this->currentStep === 3 && !$this->planGenerated) return;

        if ($this->currentStep < 4) {
            $this->currentStep++;
        }

        if ($this->currentStep === 4 && empty($this->templateName)) {
            $this->templateName = $this->buildDefaultTemplateName();
        }
    }

    public function prevStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    // ── Step 1: Client ──

    public function selectClient(int $id): void
    {
        $client = Client::with('profile')->find($id);
        if (!$client) return;

        $this->selectedClientId = $client->id;

        $age = $client->birth_date ? Carbon::parse($client->birth_date)->age : null;

        $this->selectedClientData = [
            'id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
            'plan' => $client->plan?->value ?? '-',
            'status' => $client->status?->value ?? '-',
            'age' => $age,
            'city' => $client->city ?? $client->profile?->ciudad ?? '-',
            'peso' => $client->profile?->peso ?? null,
            'altura' => $client->profile?->altura ?? null,
            'objetivo' => $client->profile?->objetivo ?? '-',
            'nivel' => $client->profile?->nivel ?? '-',
            'lugar_entreno' => $client->profile?->lugar_entreno ?? '-',
            'dias_disponibles' => $client->profile?->dias_disponibles ?? [],
            'restricciones' => $client->profile?->restricciones ?? '',
            'fecha_inicio' => $client->fecha_inicio?->format('d/m/Y') ?? '-',
        ];

        // Pre-fill frequency from client profile
        if (!empty($this->selectedClientData['dias_disponibles'])) {
            $this->frequency = count($this->selectedClientData['dias_disponibles']);
        }
    }

    public function clearClient(): void
    {
        $this->selectedClientId = null;
        $this->selectedClientData = null;
    }

    // ── Step 2: Config ──

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

    public function toggleEquipment(string $item): void
    {
        if (in_array($item, $this->equipmentAvailable)) {
            $this->equipmentAvailable = array_values(array_diff($this->equipmentAvailable, [$item]));
        } else {
            $this->equipmentAvailable[] = $item;
        }
    }

    public function toggleHabitArea(string $area): void
    {
        if (in_array($area, $this->habitFocusAreas)) {
            $this->habitFocusAreas = array_values(array_diff($this->habitFocusAreas, [$area]));
        } else {
            $this->habitFocusAreas[] = $area;
        }
    }

    // ── Step 3: Generate ──

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
                // Try to extract JSON
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

            // Fallback: generate template-based plan
            Log::info('AI Plan Generator: falling back to template-based generation');
            $this->generatedPlan = $this->generateTemplatePlan();
            $this->generatedPlanJson = json_encode($this->generatedPlan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $this->planGenerated = true;

        } catch (\Exception $e) {
            Log::error('AI Plan Generator error', ['message' => $e->getMessage()]);
            // Fallback on exception too
            $this->generatedPlan = $this->generateTemplatePlan();
            $this->generatedPlanJson = json_encode($this->generatedPlan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $this->planGenerated = true;
        }

        $this->isGenerating = false;
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

    public function toggleRawJson(): void
    {
        $this->showRawJson = !$this->showRawJson;
    }

    // ── Step 4: Save ──

    public function savePlan(): void
    {
        $this->validate([
            'templateName' => 'required|string|max:160',
        ], [
            'templateName.required' => 'El nombre de la plantilla es obligatorio.',
        ]);

        $adminId = auth('wellcore')->id();

        $methodologyLabel = $this->getMethodologyLabel();

        $template = PlanTemplate::create([
            'coach_id' => $adminId,
            'name' => $this->templateName,
            'plan_type' => $this->planType,
            'methodology' => $methodologyLabel,
            'description' => $this->buildDescription(),
            'content_json' => $this->generatedPlan,
            'ai_generated' => true,
            'is_public' => $this->isPublic,
        ]);

        $this->savedTemplateId = $template->id;

        if ($this->saveMode === 'template_and_assign' && $this->selectedClientId) {
            // Deactivate previous plans of same type
            AssignedPlan::where('client_id', $this->selectedClientId)
                ->where('plan_type', $this->planType)
                ->where('active', true)
                ->update(['active' => false]);

            // Find latest version
            $latestVersion = AssignedPlan::where('client_id', $this->selectedClientId)
                ->where('plan_type', $this->planType)
                ->max('version') ?? 0;

            $assigned = AssignedPlan::create([
                'client_id' => $this->selectedClientId,
                'plan_type' => $this->planType,
                'content' => $this->generatedPlan,
                'version' => $latestVersion + 1,
                'assigned_by' => $adminId,
                'valid_from' => now()->toDateString(),
                'active' => true,
            ]);

            $this->savedAssignedId = $assigned->id;
        }

        $this->saved = true;
    }

    public function startNew(): void
    {
        $this->reset();
        $this->currentStep = 1;
    }

    // ── Prompt builders ──

    protected function buildSystemPrompt(): string
    {
        $methodologyLabel = $this->getMethodologyLabel();

        if ($this->planType === 'entrenamiento') {
            return "Eres un coach de fitness certificado con 15 anos de experiencia especializado en la metodologia '{$methodologyLabel}'.
Genera un plan de entrenamiento COMPLETO y DETALLADO en formato JSON puro (sin markdown, sin explicaciones extra).
El JSON debe tener exactamente esta estructura:
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
            {
              \"name\": \"nombre del ejercicio\",
              \"sets\": 4,
              \"reps\": \"8-12\",
              \"rest\": \"90s\",
              \"rpe\": 8,
              \"notes\": \"nota tecnica\"
            }
          ],
          \"cooldown\": \"descripcion vuelta a la calma\"
        }
      ]
    }
  ],
  \"progression_notes\": \"notas sobre como progresar\",
  \"deload_protocol\": \"protocolo de descarga\"
}
Incluye al menos las primeras 2 semanas detalladas. Responde SOLO con JSON valido.";
        }

        if ($this->planType === 'nutricion') {
            return "Eres un nutricionista deportivo certificado especializado en el enfoque '{$methodologyLabel}'.
Genera un plan nutricional COMPLETO en formato JSON puro (sin markdown, sin explicaciones extra).
El JSON debe tener exactamente esta estructura:
{
  \"plan_type\": \"nutricion\",
  \"approach\": \"{$methodologyLabel}\",
  \"duration_weeks\": {$this->durationWeeks},
  \"calories\": {$this->calorieTarget},
  \"macros\": {
    \"protein_g\": 0,
    \"protein_pct\": {$this->proteinPct},
    \"carbs_g\": 0,
    \"carbs_pct\": {$this->carbsPct},
    \"fat_g\": 0,
    \"fat_pct\": {$this->fatPct}
  },
  \"meals_per_day\": {$this->mealsPerDay},
  \"meal_plan\": [
    {
      \"meal_number\": 1,
      \"name\": \"Desayuno\",
      \"time\": \"7:00\",
      \"calories\": 500,
      \"foods\": [
        {\"name\": \"alimento\", \"quantity\": \"100g\", \"protein\": 20, \"carbs\": 30, \"fat\": 10}
      ]
    }
  ],
  \"weekly_adjustments\": \"como ajustar semanalmente\",
  \"supplements\": [\"creatina 5g\", \"vitamina D3\"],
  \"hydration\": \"recomendaciones de hidratacion\",
  \"restrictions_notes\": \"notas sobre restricciones\"
}
Responde SOLO con JSON valido.";
        }

        // Habits
        $areas = implode(', ', array_map(fn($a) => $this->habitAreas[$a]['name'] ?? $a, $this->habitFocusAreas));
        return "Eres un coach de habitos y bienestar especializado en las areas: {$areas}.
Genera un plan de habitos COMPLETO en formato JSON puro (sin markdown, sin explicaciones extra).
El JSON debe tener exactamente esta estructura:
{
  \"plan_type\": \"habitos\",
  \"focus_areas\": [\"{$areas}\"],
  \"duration_weeks\": {$this->durationWeeks},
  \"habits\": [
    {
      \"area\": \"nombre del area\",
      \"habit\": \"descripcion del habito\",
      \"frequency\": \"diario/semanal\",
      \"metric\": \"como medir\",
      \"target\": \"objetivo cuantificable\",
      \"weeks_progression\": [
        {\"week\": 1, \"goal\": \"meta semana 1\"},
        {\"week\": 2, \"goal\": \"meta semana 2\"}
      ]
    }
  ],
  \"daily_routine\": {
    \"morning\": [\"actividad 1\", \"actividad 2\"],
    \"afternoon\": [\"actividad 1\"],
    \"evening\": [\"actividad 1\", \"actividad 2\"]
  },
  \"tracking_method\": \"como hacer seguimiento\",
  \"accountability\": \"estrategias de adherencia\"
}
Responde SOLO con JSON valido.";
    }

    protected function buildPrompt(): string
    {
        $client = $this->selectedClientData;
        $lines = [
            "DATOS DEL CLIENTE:",
            "- Nombre: {$client['name']}",
            "- Edad: " . ($client['age'] ?? 'No especificada'),
            "- Peso: " . ($client['peso'] ? "{$client['peso']} kg" : 'No especificado'),
            "- Altura: " . ($client['altura'] ? "{$client['altura']} cm" : 'No especificada'),
            "- Objetivo: {$client['objetivo']}",
            "- Nivel: {$client['nivel']}",
            "- Lugar de entreno: {$client['lugar_entreno']}",
            "- Dias disponibles: " . (is_array($client['dias_disponibles']) ? count($client['dias_disponibles']) . " dias" : $client['dias_disponibles']),
            "- Plan WellCore: {$client['plan']}",
            "",
            "CONFIGURACION DEL PLAN:",
            "- Tipo: {$this->planType}",
            "- Duracion: {$this->durationWeeks} semanas",
            "- Frecuencia: {$this->frequency} dias/semana",
        ];

        if ($this->planType === 'entrenamiento') {
            $methodologyLabel = $this->getMethodologyLabel();
            $equipment = !empty($this->equipmentAvailable) ? implode(', ', $this->equipmentAvailable) : 'Gym completo';
            $lines[] = "- Metodologia: {$methodologyLabel}";
            $lines[] = "- Meta: {$this->trainingGoal}";
            $lines[] = "- Nivel experiencia: {$this->experienceLevel}";
            $lines[] = "- Equipamiento: {$equipment}";
            if ($this->injuries) {
                $lines[] = "- Lesiones/limitaciones: {$this->injuries}";
            }
        } elseif ($this->planType === 'nutricion') {
            $methodologyLabel = $this->getMethodologyLabel();
            $lines[] = "- Enfoque: {$methodologyLabel}";
            $lines[] = "- Calorias objetivo: {$this->calorieTarget} kcal";
            $lines[] = "- Macros: Proteina {$this->proteinPct}%, Carbohidratos {$this->carbsPct}%, Grasas {$this->fatPct}%";
            $lines[] = "- Comidas al dia: {$this->mealsPerDay}";
            if ($this->dietaryRestrictions) {
                $lines[] = "- Restricciones: {$this->dietaryRestrictions}";
            }
            if ($client['restricciones']) {
                $lines[] = "- Restricciones del perfil: {$client['restricciones']}";
            }
        } else {
            $areas = implode(', ', array_map(fn($a) => $this->habitAreas[$a]['name'] ?? $a, $this->habitFocusAreas));
            $lines[] = "- Areas de enfoque: {$areas}";
        }

        $lines[] = "";
        $lines[] = "Genera el plan completo en formato JSON. Solo JSON, sin texto adicional.";

        return implode("\n", $lines);
    }

    // ── Template-based fallback generator ──

    protected function generateTemplatePlan(): array
    {
        return match ($this->planType) {
            'entrenamiento' => $this->generateTrainingTemplate(),
            'nutricion' => $this->generateNutritionTemplate(),
            'habitos' => $this->generateHabitsTemplate(),
            default => ['plan_type' => $this->planType, 'error' => 'Tipo no soportado'],
        };
    }

    protected function generateTrainingTemplate(): array
    {
        $methodologyLabel = $this->getMethodologyLabel();

        $exercisesByGoal = [
            'hipertrofia' => [
                ['name' => 'Press Banca', 'sets' => 4, 'reps' => '8-12', 'rest' => '90s', 'rpe' => 8, 'notes' => 'Controlar la fase excentrica'],
                ['name' => 'Sentadilla', 'sets' => 4, 'reps' => '8-12', 'rest' => '120s', 'rpe' => 8, 'notes' => 'Profundidad completa'],
                ['name' => 'Remo con Barra', 'sets' => 4, 'reps' => '8-12', 'rest' => '90s', 'rpe' => 7, 'notes' => 'Escepulas retraidas'],
                ['name' => 'Press Militar', 'sets' => 3, 'reps' => '10-12', 'rest' => '90s', 'rpe' => 7, 'notes' => 'Sin impulso'],
                ['name' => 'Peso Muerto Rumano', 'sets' => 3, 'reps' => '10-12', 'rest' => '90s', 'rpe' => 7, 'notes' => 'Estirar isquiotibiales'],
                ['name' => 'Curl Biceps', 'sets' => 3, 'reps' => '12-15', 'rest' => '60s', 'rpe' => 7, 'notes' => 'Supinacion completa'],
                ['name' => 'Extension Triceps', 'sets' => 3, 'reps' => '12-15', 'rest' => '60s', 'rpe' => 7, 'notes' => 'Codo fijo'],
                ['name' => 'Elevaciones Laterales', 'sets' => 3, 'reps' => '15-20', 'rest' => '45s', 'rpe' => 8, 'notes' => 'Control total'],
                ['name' => 'Hip Thrust', 'sets' => 4, 'reps' => '10-12', 'rest' => '90s', 'rpe' => 8, 'notes' => 'Pausa arriba 2s'],
                ['name' => 'Dominadas', 'sets' => 3, 'reps' => '6-10', 'rest' => '120s', 'rpe' => 8, 'notes' => 'Rango completo'],
                ['name' => 'Zancadas', 'sets' => 3, 'reps' => '10/lado', 'rest' => '60s', 'rpe' => 7, 'notes' => 'Paso largo'],
                ['name' => 'Face Pull', 'sets' => 3, 'reps' => '15-20', 'rest' => '45s', 'rpe' => 6, 'notes' => 'Rotacion externa al final'],
            ],
            'fuerza' => [
                ['name' => 'Sentadilla Barra', 'sets' => 5, 'reps' => '5', 'rest' => '180s', 'rpe' => 8, 'notes' => 'Tecnica estricta'],
                ['name' => 'Press Banca', 'sets' => 5, 'reps' => '5', 'rest' => '180s', 'rpe' => 8, 'notes' => 'Arco, retraccion escapular'],
                ['name' => 'Peso Muerto', 'sets' => 5, 'reps' => '3-5', 'rest' => '180s', 'rpe' => 9, 'notes' => 'Reset cada rep'],
                ['name' => 'Press Militar Estricto', 'sets' => 4, 'reps' => '5-6', 'rest' => '150s', 'rpe' => 8, 'notes' => 'Gluteos apretados'],
                ['name' => 'Remo Pendlay', 'sets' => 4, 'reps' => '5', 'rest' => '120s', 'rpe' => 7, 'notes' => 'Explosivo concentrico'],
                ['name' => 'Dominadas Lastradas', 'sets' => 4, 'reps' => '5-6', 'rest' => '150s', 'rpe' => 8, 'notes' => 'Agregar peso progresivamente'],
            ],
        ];

        $goalKey = in_array($this->trainingGoal, ['fuerza', 'strength']) ? 'fuerza' : 'hipertrofia';
        $exercises = $exercisesByGoal[$goalKey];

        $sessionTemplates = $this->buildSessionTemplates($exercises);
        $weeks = [];
        $weeksToGenerate = min($this->durationWeeks, 4);

        for ($w = 1; $w <= $weeksToGenerate; $w++) {
            $sessions = [];
            for ($d = 1; $d <= $this->frequency; $d++) {
                $sessionIdx = ($d - 1) % count($sessionTemplates);
                $session = $sessionTemplates[$sessionIdx];
                $session['day'] = $d;
                $sessions[] = $session;
            }
            $weeks[] = [
                'week' => $w,
                'focus' => $w <= 2 ? 'Adaptacion y tecnica' : ($w <= 3 ? 'Incremento progresivo de carga' : 'Intensificacion'),
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
            'progression_notes' => 'Aumentar carga 2.5-5% cada semana si se completan todas las repeticiones con buena tecnica. Registrar pesos en cada sesion.',
            'deload_protocol' => 'Cada 4 semanas reducir volumen al 60% y carga al 70% para recuperacion.',
            'generated_by' => 'template',
        ];
    }

    protected function buildSessionTemplates(array $exercises): array
    {
        if ($this->frequency <= 3) {
            // Full body style
            return [
                [
                    'name' => 'Full Body A',
                    'muscle_groups' => ['pecho', 'espalda', 'piernas', 'hombros'],
                    'warmup' => '5 min cardio ligero + movilidad articular + series de aproximacion',
                    'exercises' => array_slice($exercises, 0, 5),
                    'cooldown' => '5 min estiramiento estatico + foam rolling',
                ],
                [
                    'name' => 'Full Body B',
                    'muscle_groups' => ['piernas', 'espalda', 'pecho', 'brazos'],
                    'warmup' => '5 min cardio ligero + movilidad articular + series de aproximacion',
                    'exercises' => array_slice($exercises, 3, 5),
                    'cooldown' => '5 min estiramiento estatico + foam rolling',
                ],
                [
                    'name' => 'Full Body C',
                    'muscle_groups' => ['piernas', 'hombros', 'espalda', 'core'],
                    'warmup' => '5 min cardio ligero + movilidad articular + series de aproximacion',
                    'exercises' => array_merge(array_slice($exercises, 6, 4), [array_slice($exercises, 0, 1)[0] ?? []]),
                    'cooldown' => '5 min estiramiento estatico + foam rolling',
                ],
            ];
        }

        if ($this->frequency <= 4) {
            // Upper/Lower split
            return [
                [
                    'name' => 'Tren Superior — Fuerza',
                    'muscle_groups' => ['pecho', 'espalda', 'hombros', 'brazos'],
                    'warmup' => '5 min cardio + movilidad hombros + series de aproximacion',
                    'exercises' => array_values(array_filter([$exercises[0] ?? null, $exercises[2] ?? null, $exercises[3] ?? null, $exercises[5] ?? null, $exercises[6] ?? null])),
                    'cooldown' => '5 min estiramiento estatico tren superior',
                ],
                [
                    'name' => 'Tren Inferior — Fuerza',
                    'muscle_groups' => ['cuadriceps', 'isquiotibiales', 'gluteos', 'pantorrillas'],
                    'warmup' => '5 min cardio + movilidad caderas + series de aproximacion',
                    'exercises' => array_values(array_filter([$exercises[1] ?? null, $exercises[4] ?? null, $exercises[8] ?? null, $exercises[10] ?? null])),
                    'cooldown' => '5 min estiramiento estatico tren inferior',
                ],
                [
                    'name' => 'Tren Superior — Hipertrofia',
                    'muscle_groups' => ['pecho', 'espalda', 'hombros', 'brazos'],
                    'warmup' => '5 min cardio + movilidad hombros + series de aproximacion',
                    'exercises' => array_values(array_filter([$exercises[0] ?? null, $exercises[9] ?? null, $exercises[7] ?? null, $exercises[11] ?? null, $exercises[5] ?? null])),
                    'cooldown' => '5 min estiramiento estatico tren superior',
                ],
                [
                    'name' => 'Tren Inferior — Hipertrofia',
                    'muscle_groups' => ['cuadriceps', 'isquiotibiales', 'gluteos', 'pantorrillas'],
                    'warmup' => '5 min cardio + movilidad caderas + series de aproximacion',
                    'exercises' => array_values(array_filter([$exercises[1] ?? null, $exercises[8] ?? null, $exercises[10] ?? null, $exercises[4] ?? null])),
                    'cooldown' => '5 min estiramiento estatico tren inferior',
                ],
            ];
        }

        // PPL for 5-6 days
        return [
            [
                'name' => 'Push (Empuje)',
                'muscle_groups' => ['pecho', 'hombros', 'triceps'],
                'warmup' => '5 min cardio + movilidad hombros + activacion pectoral',
                'exercises' => array_values(array_filter([$exercises[0] ?? null, $exercises[3] ?? null, $exercises[7] ?? null, $exercises[6] ?? null])),
                'cooldown' => '5 min estiramiento pectoral y deltoides',
            ],
            [
                'name' => 'Pull (Jalon)',
                'muscle_groups' => ['espalda', 'biceps', 'antebrazos'],
                'warmup' => '5 min cardio + movilidad escapular + activacion dorsal',
                'exercises' => array_values(array_filter([$exercises[2] ?? null, $exercises[9] ?? null, $exercises[11] ?? null, $exercises[5] ?? null])),
                'cooldown' => '5 min estiramiento dorsal y biceps',
            ],
            [
                'name' => 'Legs (Piernas)',
                'muscle_groups' => ['cuadriceps', 'isquiotibiales', 'gluteos', 'pantorrillas'],
                'warmup' => '5 min cardio + movilidad caderas + activacion glutea',
                'exercises' => array_values(array_filter([$exercises[1] ?? null, $exercises[4] ?? null, $exercises[8] ?? null, $exercises[10] ?? null])),
                'cooldown' => '5 min estiramiento completo tren inferior',
            ],
        ];
    }

    protected function generateNutritionTemplate(): array
    {
        $methodologyLabel = $this->getMethodologyLabel();
        $protGrams = round($this->calorieTarget * ($this->proteinPct / 100) / 4);
        $carbsGrams = round($this->calorieTarget * ($this->carbsPct / 100) / 4);
        $fatGrams = round($this->calorieTarget * ($this->fatPct / 100) / 9);
        $calPerMeal = round($this->calorieTarget / $this->mealsPerDay);

        $meals = [];
        $mealNames = ['Desayuno', 'Media Manana', 'Almuerzo', 'Merienda', 'Cena', 'Pre-sueno'];
        $mealTimes = ['7:00', '10:00', '13:00', '16:00', '19:30', '21:30'];

        $mealFoods = [
            [
                ['name' => 'Avena', 'quantity' => '80g', 'protein' => 10, 'carbs' => 54, 'fat' => 6],
                ['name' => 'Claras de huevo', 'quantity' => '200ml', 'protein' => 22, 'carbs' => 2, 'fat' => 0],
                ['name' => 'Banano', 'quantity' => '1 unidad', 'protein' => 1, 'carbs' => 27, 'fat' => 0],
            ],
            [
                ['name' => 'Yogurt griego', 'quantity' => '200g', 'protein' => 18, 'carbs' => 8, 'fat' => 10],
                ['name' => 'Frutos rojos', 'quantity' => '100g', 'protein' => 1, 'carbs' => 12, 'fat' => 0],
                ['name' => 'Almendras', 'quantity' => '20g', 'protein' => 4, 'carbs' => 2, 'fat' => 10],
            ],
            [
                ['name' => 'Pechuga de pollo', 'quantity' => '200g', 'protein' => 46, 'carbs' => 0, 'fat' => 6],
                ['name' => 'Arroz integral', 'quantity' => '150g cocido', 'protein' => 4, 'carbs' => 45, 'fat' => 1],
                ['name' => 'Vegetales mixtos', 'quantity' => '200g', 'protein' => 4, 'carbs' => 12, 'fat' => 0],
                ['name' => 'Aceite de oliva', 'quantity' => '10ml', 'protein' => 0, 'carbs' => 0, 'fat' => 10],
            ],
            [
                ['name' => 'Whey Protein', 'quantity' => '1 scoop', 'protein' => 25, 'carbs' => 3, 'fat' => 2],
                ['name' => 'Manzana', 'quantity' => '1 unidad', 'protein' => 0, 'carbs' => 25, 'fat' => 0],
                ['name' => 'Mantequilla de mani', 'quantity' => '15g', 'protein' => 4, 'carbs' => 2, 'fat' => 8],
            ],
            [
                ['name' => 'Salmon', 'quantity' => '180g', 'protein' => 36, 'carbs' => 0, 'fat' => 18],
                ['name' => 'Batata', 'quantity' => '200g', 'protein' => 3, 'carbs' => 40, 'fat' => 0],
                ['name' => 'Ensalada verde', 'quantity' => '150g', 'protein' => 2, 'carbs' => 5, 'fat' => 0],
                ['name' => 'Aguacate', 'quantity' => '50g', 'protein' => 1, 'carbs' => 4, 'fat' => 8],
            ],
            [
                ['name' => 'Caseina', 'quantity' => '1 scoop', 'protein' => 24, 'carbs' => 3, 'fat' => 1],
                ['name' => 'Nueces', 'quantity' => '15g', 'protein' => 2, 'carbs' => 1, 'fat' => 10],
            ],
        ];

        for ($i = 0; $i < $this->mealsPerDay && $i < 6; $i++) {
            $meals[] = [
                'meal_number' => $i + 1,
                'name' => $mealNames[$i] ?? "Comida " . ($i + 1),
                'time' => $mealTimes[$i] ?? '12:00',
                'calories' => $calPerMeal,
                'foods' => $mealFoods[$i] ?? $mealFoods[0],
            ];
        }

        return [
            'plan_type' => 'nutricion',
            'approach' => $methodologyLabel,
            'duration_weeks' => $this->durationWeeks,
            'calories' => $this->calorieTarget,
            'macros' => [
                'protein_g' => $protGrams,
                'protein_pct' => $this->proteinPct,
                'carbs_g' => $carbsGrams,
                'carbs_pct' => $this->carbsPct,
                'fat_g' => $fatGrams,
                'fat_pct' => $this->fatPct,
            ],
            'meals_per_day' => $this->mealsPerDay,
            'meal_plan' => $meals,
            'weekly_adjustments' => 'Revisar peso y medidas semanalmente. Ajustar calorias +/- 100 kcal segun progreso.',
            'supplements' => ['Creatina monohidrato 5g/dia', 'Vitamina D3 4000 IU', 'Omega-3 2g/dia', 'Magnesio 400mg antes de dormir'],
            'hydration' => 'Minimo 35ml por kg de peso corporal. Agregar 500ml por hora de entrenamiento.',
            'restrictions_notes' => $this->dietaryRestrictions ?: 'Sin restricciones reportadas',
            'generated_by' => 'template',
        ];
    }

    protected function generateHabitsTemplate(): array
    {
        $habits = [];
        $allHabitTemplates = [
            'sleep' => [
                ['habit' => 'Dormir 7-8 horas cada noche', 'frequency' => 'diario', 'metric' => 'Horas de sueno', 'target' => '7.5 horas promedio semanal'],
                ['habit' => 'Apagar pantallas 1 hora antes de dormir', 'frequency' => 'diario', 'metric' => 'Si/No', 'target' => '6 de 7 dias'],
                ['habit' => 'Mantener horario fijo de sueno', 'frequency' => 'diario', 'metric' => 'Variacion horaria', 'target' => 'Menos de 30 min variacion'],
            ],
            'hydration' => [
                ['habit' => 'Tomar minimo 2.5L de agua al dia', 'frequency' => 'diario', 'metric' => 'Litros consumidos', 'target' => '2.5L minimo'],
                ['habit' => 'Vaso de agua al despertar', 'frequency' => 'diario', 'metric' => 'Si/No', 'target' => '7 de 7 dias'],
            ],
            'stress' => [
                ['habit' => 'Meditacion o respiracion 10 minutos', 'frequency' => 'diario', 'metric' => 'Minutos', 'target' => '10 min/dia'],
                ['habit' => 'Journaling antes de dormir', 'frequency' => 'diario', 'metric' => 'Si/No', 'target' => '5 de 7 dias'],
            ],
            'mobility' => [
                ['habit' => 'Rutina de movilidad 15 minutos', 'frequency' => 'diario', 'metric' => 'Minutos', 'target' => '15 min/dia'],
                ['habit' => 'Foam rolling post-entrenamiento', 'frequency' => '3x/semana', 'metric' => 'Sesiones', 'target' => '3 sesiones/semana'],
            ],
            'nutrition_habits' => [
                ['habit' => 'Meal prep dominical', 'frequency' => 'semanal', 'metric' => 'Si/No', 'target' => '4 de 4 semanas'],
                ['habit' => 'Comer sin pantallas', 'frequency' => 'diario', 'metric' => 'Comidas conscientes', 'target' => '2 de 3 comidas principales'],
            ],
            'recovery' => [
                ['habit' => 'Dia de descanso activo semanal', 'frequency' => 'semanal', 'metric' => 'Si/No', 'target' => '1 dia/semana'],
                ['habit' => 'Estiramientos antes de dormir', 'frequency' => 'diario', 'metric' => 'Minutos', 'target' => '10 min/dia'],
            ],
        ];

        foreach ($this->habitFocusAreas as $area) {
            $areaName = $this->habitAreas[$area]['name'] ?? $area;
            $templates = $allHabitTemplates[$area] ?? [];
            foreach ($templates as $template) {
                $progressionWeeks = [];
                for ($w = 1; $w <= min($this->durationWeeks, 4); $w++) {
                    $progressionWeeks[] = ['week' => $w, 'goal' => "Semana {$w}: " . ($w === 1 ? 'Establecer el habito basico' : ($w === 2 ? 'Consolidar consistencia' : ($w === 3 ? 'Aumentar exigencia' : 'Automatizar el habito')))];
                }
                $habits[] = array_merge($template, [
                    'area' => $areaName,
                    'weeks_progression' => $progressionWeeks,
                ]);
            }
        }

        return [
            'plan_type' => 'habitos',
            'focus_areas' => array_map(fn($a) => $this->habitAreas[$a]['name'] ?? $a, $this->habitFocusAreas),
            'duration_weeks' => $this->durationWeeks,
            'habits' => $habits,
            'daily_routine' => [
                'morning' => ['Vaso de agua al despertar', 'Respiracion 4-7-8 (5 min)', 'Journaling: 3 cosas por agradecer'],
                'afternoon' => ['Caminar 10 min post-almuerzo', 'Hidratacion check'],
                'evening' => ['Apagar pantallas a las 21:00', 'Estiramientos 10 min', 'Preparar ropa de entrenamiento'],
            ],
            'tracking_method' => 'Registro diario en la app WellCore. Check-in semanal con el coach.',
            'accountability' => 'Compartir progreso en el grupo de comunidad WellCore. Check-in con el coach cada lunes.',
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
        $clientName = $this->selectedClientData['name'] ?? 'Cliente';
        $type = match ($this->planType) {
            'entrenamiento' => 'Entrenamiento',
            'nutricion' => 'Nutricion',
            'habitos' => 'Habitos',
            default => $this->planType,
        };
        $method = $this->getMethodologyLabel();

        return "{$type} — {$method} — {$clientName}";
    }

    protected function buildDescription(): string
    {
        $clientName = $this->selectedClientData['name'] ?? 'Cliente';
        $method = $this->getMethodologyLabel();

        return "Plan generado por IA para {$clientName}. Metodologia: {$method}. Duracion: {$this->durationWeeks} semanas, {$this->frequency} dias/semana.";
    }

    public function render()
    {
        $clients = [];
        if (strlen($this->clientSearch) >= 2) {
            $clients = Client::with('profile')
                ->where(function ($q) {
                    $q->where('name', 'like', "%{$this->clientSearch}%")
                      ->orWhere('email', 'like', "%{$this->clientSearch}%");
                })
                ->where('status', 'activo')
                ->orderBy('name')
                ->limit(20)
                ->get();
        }

        return view('livewire.admin.ai-plan-generator', [
            'clients' => $clients,
        ]);
    }
}
