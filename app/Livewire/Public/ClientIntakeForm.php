<?php

namespace App\Livewire\Public;

use App\Enums\PlanType;
use App\Models\Client;
use App\Models\ClientProfile;
use App\Models\Invitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public', ['title' => 'Completa tu registro — WellCore'])]
class ClientIntakeForm extends Component
{
    // ---------------------------------------------------------------------------
    // Wizard state
    // ---------------------------------------------------------------------------
    public int $step       = 1;
    public int $totalSteps = 4;

    public bool $invalidCode  = false;
    public bool $codeExpired  = false;
    public bool $submitted    = false;

    // ---------------------------------------------------------------------------
    // Invitation data (read-only after mount)
    // ---------------------------------------------------------------------------
    public ?string $invitationCode = null;
    public ?string $planType       = null;
    public ?string $planNote       = null;

    // ---------------------------------------------------------------------------
    // Step 2 — Datos Personales
    // ---------------------------------------------------------------------------
    public string $nombre   = '';
    public string $apellido = '';
    public string $email    = '';
    public string $whatsapp = '';
    public ?int   $edad     = null;
    public ?float $peso     = null;
    public ?float $altura   = null;
    public string $genero   = '';
    public string $ciudad   = '';
    public string $pais     = 'Colombia';

    // ---------------------------------------------------------------------------
    // Step 3 — Perfil Fitness
    // ---------------------------------------------------------------------------
    public string $objetivo_principal  = '';
    public string $nivel_experiencia   = '';
    public string $lugar_entreno       = '';
    public array  $dias_disponibles    = [];
    public string $duracion_sesion     = '60';
    public string $tiene_lesiones      = 'no';
    public string $detalle_lesiones    = '';

    // ---------------------------------------------------------------------------
    // Step 4 — Nutrición & Estilo de Vida (Método + Elite)
    // ---------------------------------------------------------------------------
    public string $trabajo_tipo          = '';
    public string $horas_sueno           = '';
    public string $nivel_estres          = '';
    public array  $intolerancias         = [];
    public string $otras_intolerancias   = '';
    public string $alimentos_evitar      = '';
    public string $comidas_por_dia       = '4';
    public string $suplementos_actuales  = '';

    // ---------------------------------------------------------------------------
    // Step 5 — Info Avanzada (Elite only)
    // ---------------------------------------------------------------------------
    public string $objetivo_composicion  = '';
    public string $historial_medico      = '';
    public string $ciclo_hormonal        = 'no';
    public string $bloodwork_disponible  = 'no';

    // ---------------------------------------------------------------------------
    // Plan-specific fields
    // ---------------------------------------------------------------------------
    public bool   $compromiso_30dias  = false;   // Rise
    public string $horario_preferido  = '';       // Presencial

    // ---------------------------------------------------------------------------
    // Step final — Contraseña
    // ---------------------------------------------------------------------------
    public string $password              = '';
    public string $password_confirmation = '';
    public bool   $acepta_terminos       = false;

    // ---------------------------------------------------------------------------
    // Mount
    // ---------------------------------------------------------------------------
    public bool $alreadyLoggedIn = false;

    public function mount(string $code): void
    {
        // If user is already authenticated, flag it instead of crashing
        if (auth('wellcore')->check()) {
            $this->alreadyLoggedIn = true;
        }

        $invitation = Invitation::where('code', strtoupper($code))->first();

        if (! $invitation) {
            $this->invalidCode = true;
            return;
        }

        $rawStatus = $invitation->getRawOriginal('status') ?? 'pending';

        if ($rawStatus !== 'pending') {
            if ($rawStatus === 'expired') {
                $this->codeExpired = true;
            } else {
                $this->invalidCode = true;
            }
            return;
        }

        // Check date-based expiry
        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            $this->codeExpired = true;
            return;
        }

        $this->invitationCode = $invitation->code;
        $this->planType       = $invitation->plan instanceof PlanType
            ? $invitation->plan->value
            : $invitation->plan;
        $this->planNote = $invitation->note;
        $this->email    = $invitation->email_hint ?? '';

        $this->totalSteps = $this->resolveStepCount($this->planType);
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------
    private function resolveStepCount(string $plan): int
    {
        return match ($plan) {
            'metodo'     => 5,
            'elite'      => 6,
            default      => 4,   // esencial, rise, presencial, trial
        };
    }

    public function isStepForNutricion(): bool
    {
        return in_array($this->planType, ['metodo', 'elite'], true);
    }

    public function isStepForAdvanced(): bool
    {
        return $this->planType === 'elite';
    }

    /**
     * Map the logical step number to the internal step semantic.
     * Step labels differ by plan; this maps step → meaning.
     */
    private function stepLabel(int $step): string
    {
        // Step 1 is always Bienvenida, step 2 always datos, step 3 always fitness.
        // For metodo/elite step 4 = nutricion; for elite step 5 = avanzado.
        // Password step is always the last step.
        if ($step === 1) return 'bienvenida';
        if ($step === 2) return 'datos';
        if ($step === 3) return 'fitness';

        if ($this->planType === 'elite') {
            if ($step === 4) return 'nutricion';
            if ($step === 5) return 'avanzado';
            if ($step === 6) return 'password';
        }

        if ($this->planType === 'metodo') {
            if ($step === 4) return 'nutricion';
            if ($step === 5) return 'password';
        }

        // 4-step plans
        if ($step === 4) return 'password';

        return 'password';
    }

    // ---------------------------------------------------------------------------
    // Navigation
    // ---------------------------------------------------------------------------
    public function nextStep(): void
    {
        if ($this->invalidCode || $this->codeExpired) {
            return;
        }

        $label = $this->stepLabel($this->step);

        match ($label) {
            'datos'     => $this->validateDatos(),
            'fitness'   => $this->validateFitness(),
            'nutricion' => $this->validateNutricion(),
            default     => null,
        };

        // validateX() will halt via $this->validate() throwing on failure
        $this->step = min($this->step + 1, $this->totalSteps);
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    // ---------------------------------------------------------------------------
    // Per-step validation
    // ---------------------------------------------------------------------------
    private function validateDatos(): void
    {
        $this->validate([
            'nombre'   => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email'    => 'required|email|unique:clients,email',
            'whatsapp' => 'required|string|max:50',
            'edad'     => 'required|integer|min:16|max:80',
            'peso'     => 'required|numeric|min:30|max:300',
            'altura'   => 'required|numeric|min:100|max:250',
            'genero'   => 'required|in:hombre,mujer,otro',
            'ciudad'   => 'required|string|max:100',
        ], [
            'nombre.required'   => 'Tu nombre es requerido.',
            'apellido.required' => 'Tu apellido es requerido.',
            'email.required'    => 'El email es requerido.',
            'email.email'       => 'Ingresa un email valido.',
            'email.unique'      => 'Este email ya tiene una cuenta. Intenta iniciar sesion.',
            'whatsapp.required' => 'Tu numero de WhatsApp es requerido.',
            'edad.required'     => 'Tu edad es requerida.',
            'edad.min'          => 'Debes tener al menos 16 anos.',
            'edad.max'          => 'La edad maxima es 80 anos.',
            'peso.required'     => 'Tu peso es requerido.',
            'peso.min'          => 'El peso minimo es 30 kg.',
            'peso.max'          => 'El peso maximo es 300 kg.',
            'altura.required'   => 'Tu altura es requerida.',
            'altura.min'        => 'La altura minima es 100 cm.',
            'altura.max'        => 'La altura maxima es 250 cm.',
            'genero.required'   => 'Selecciona tu genero.',
            'genero.in'         => 'Selecciona una opcion valida.',
            'ciudad.required'   => 'Tu ciudad es requerida.',
        ]);
    }

    private function validateFitness(): void
    {
        $rules = [
            'objetivo_principal' => 'required|in:perder_grasa,ganar_musculo,recomposicion,rendimiento,salud_general,tonificar',
            'nivel_experiencia'  => 'required|in:principiante,intermedio,avanzado',
            'lugar_entreno'      => 'required|in:gym,casa_con_equipo,casa_sin_equipo,aire_libre,mixto',
            'dias_disponibles'   => 'required|array|min:2',
            'duracion_sesion'    => 'required|in:45,60,75,90',
            'tiene_lesiones'     => 'required|in:si,no',
        ];

        if ($this->tiene_lesiones === 'si') {
            $rules['detalle_lesiones'] = 'required|string|max:500';
        }

        $this->validate($rules, [
            'objetivo_principal.required' => 'Selecciona tu objetivo principal.',
            'objetivo_principal.in'       => 'Selecciona una opcion valida.',
            'nivel_experiencia.required'  => 'Selecciona tu nivel de experiencia.',
            'nivel_experiencia.in'        => 'Selecciona una opcion valida.',
            'lugar_entreno.required'      => 'Selecciona donde entrenas.',
            'lugar_entreno.in'            => 'Selecciona una opcion valida.',
            'dias_disponibles.required'   => 'Selecciona al menos 2 dias disponibles.',
            'dias_disponibles.min'        => 'Selecciona al menos 2 dias disponibles.',
            'duracion_sesion.required'    => 'Selecciona la duracion de tus sesiones.',
            'tiene_lesiones.required'     => 'Indica si tienes lesiones o restricciones.',
            'detalle_lesiones.required'   => 'Describe tus lesiones o restricciones.',
        ]);
    }

    private function validateNutricion(): void
    {
        $this->validate([
            'trabajo_tipo'   => 'required|in:sedentario,moderado,activo',
            'horas_sueno'    => 'required|in:5_menos,6_7,8_mas',
            'nivel_estres'   => 'required|in:bajo,moderado,alto,muy_alto',
            'comidas_por_dia' => 'required|in:2,3,4,5_mas',
        ], [
            'trabajo_tipo.required'    => 'Indica tu nivel de actividad laboral.',
            'trabajo_tipo.in'          => 'Selecciona una opcion valida.',
            'horas_sueno.required'     => 'Indica tus horas de sueno.',
            'horas_sueno.in'           => 'Selecciona una opcion valida.',
            'nivel_estres.required'    => 'Indica tu nivel de estres.',
            'nivel_estres.in'          => 'Selecciona una opcion valida.',
            'comidas_por_dia.required' => 'Indica cuantas comidas haces al dia.',
            'comidas_por_dia.in'       => 'Selecciona una opcion valida.',
        ]);
    }

    private function validatePassword(): void
    {
        $rules = [
            'password'       => 'required|min:8|confirmed',
            'acepta_terminos' => 'accepted',
        ];

        $messages = [
            'password.required'       => 'Crea una contrasena.',
            'password.min'            => 'La contrasena debe tener al menos 8 caracteres.',
            'password.confirmed'      => 'Las contrasenas no coinciden.',
            'acepta_terminos.accepted' => 'Debes aceptar los terminos para continuar.',
        ];

        if ($this->planType === 'rise') {
            $rules['compromiso_30dias'] = 'accepted';
            $messages['compromiso_30dias.accepted'] = 'Debes aceptar el compromiso de 30 dias.';
        }

        if ($this->planType === 'presencial') {
            $rules['horario_preferido'] = 'required|in:manana,tarde,noche';
            $messages['horario_preferido.required'] = 'Selecciona tu horario preferido.';
        }

        $this->validate($rules, $messages);
    }

    // ---------------------------------------------------------------------------
    // Submit
    // ---------------------------------------------------------------------------
    public function submit(): void
    {
        $this->validatePassword();

        // Re-check email uniqueness right before creating
        if (Client::where('email', $this->email)->exists()) {
            $this->addError('email', 'Este email ya tiene una cuenta registrada. Intenta iniciar sesion en wellcorefitness.com/login');
            $this->step = 2;
            return;
        }

        try {
            $client = DB::transaction(function () {
                // Generate unique client code
                do {
                    $code = 'WC-' . strtoupper(Str::random(6));
                } while (Client::where('client_code', $code)->exists());

                $client = Client::create([
                    'client_code'  => $code,
                    'name'         => trim($this->nombre . ' ' . $this->apellido),
                    'email'        => $this->email,
                    'password_hash' => Hash::make($this->password),
                    'plan'         => $this->planType,
                    'status'       => 'active',
                    'fecha_inicio' => now()->toDateString(),
                    'city'         => $this->ciudad,
                    'onboarding_completed' => 0,
                ]);

                // Build macros data structure for extra plan info
                $macros = null;
                if ($this->isStepForNutricion()) {
                    $macros = [
                        'trabajo_tipo'         => $this->trabajo_tipo,
                        'horas_sueno'          => $this->horas_sueno,
                        'nivel_estres'         => $this->nivel_estres,
                        'intolerancias'        => $this->intolerancias,
                        'otras_intolerancias'  => $this->otras_intolerancias,
                        'alimentos_evitar'     => $this->alimentos_evitar,
                        'comidas_por_dia'      => $this->comidas_por_dia,
                        'suplementos_actuales' => $this->suplementos_actuales,
                    ];
                }

                if ($this->isStepForAdvanced()) {
                    $macros = array_merge($macros ?? [], [
                        'objetivo_composicion' => $this->objetivo_composicion,
                        'historial_medico'     => $this->historial_medico,
                        'ciclo_hormonal'       => $this->ciclo_hormonal,
                        'bloodwork_disponible' => $this->bloodwork_disponible,
                    ]);
                }

                ClientProfile::create([
                    'client_id'        => $client->id,
                    'edad'             => $this->edad,
                    'peso'             => $this->peso,
                    'altura'           => $this->altura,
                    'genero'           => $this->genero,
                    'objetivo'         => $this->objetivo_principal,
                    'ciudad'           => $this->ciudad,
                    'whatsapp'         => $this->whatsapp,
                    'nivel'            => $this->nivel_experiencia,
                    'lugar_entreno'    => $this->lugar_entreno,
                    'dias_disponibles' => $this->dias_disponibles,
                    'restricciones'    => $this->detalle_lesiones ?: null,
                    'macros'           => $macros,
                ]);

                Invitation::where('code', $this->invitationCode)->update([
                    'status'  => 'used',
                    'used_by' => $client->id,
                    'used_at' => now(),
                ]);

                return $client;
            });

            auth('wellcore')->login($client);
            $this->submitted = true;
            $this->redirect(route('client.dashboard'), navigate: true);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ClientIntakeForm::submit FAILED', [
                'error'   => $e->getMessage(),
                'email'   => $this->email,
                'plan'    => $this->planType,
                'code'    => $this->invitationCode,
                'class'   => get_class($e),
            ]);

            if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'unique')) {
                $this->addError('email', 'Este email ya esta registrado. Inicia sesion en wellcorefitness.com/login');
                $this->step = 2;
            } else {
                $this->addError('email', 'Error al crear tu cuenta. Por favor intenta de nuevo o contactanos por WhatsApp.');
                $this->step = $this->totalSteps;
            }
        }
    }

    // ---------------------------------------------------------------------------
    // Render
    // ---------------------------------------------------------------------------
    public function render()
    {
        return view('livewire.public.client-intake-form');
    }
}
