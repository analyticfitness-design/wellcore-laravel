<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanType;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientProfile;
use App\Models\CoachApplication as CoachApplicationModel;
use App\Models\Inscription;
use App\Models\Invitation;
use App\Models\Referral;
use App\Models\RiseProgram;
use App\Models\WellcoreNotification;
use App\Services\TrialService;
use App\Services\WellCoinsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PublicFormController extends Controller
{
    // -------------------------------------------------------------------------
    // 1. Inscription (7-step wizard — all data submitted at once)
    // -------------------------------------------------------------------------
    public function inscriptionSubmit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'whatsapp' => 'required|string|max:50',
            'edad' => 'required|integer|min:14|max:80',
            'peso' => 'required|numeric|min:30|max:250',
            'estatura' => 'required|numeric|min:100|max:230',
            'genero' => 'required|string|max:50',
            'objetivo' => 'required|string|max:500',
            'plan' => 'required|in:esencial,metodo,elite',
            'experiencia' => 'required|string|max:100',
            'dias_disponibles' => 'required|string|max:50',
            'equipamiento' => 'nullable|string|max:255',
            'lesion' => 'nullable|string|max:500',
            'password' => 'required|string|min:8|confirmed',
            'terminos' => 'accepted',
            // Optional fields
            'apellido' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:100',
            'pais' => 'nullable|string|max:100',
            'como_conocio' => 'nullable|string|max:255',
            'referral' => 'nullable|string|max:255',
            'utm_source' => 'nullable|string|max:255',
            'utm_medium' => 'nullable|string|max:255',
            'utm_campaign' => 'nullable|string|max:255',
            // Vue InscriptionForm extras (preserved in extras JSON)
            'coaching_previo' => 'nullable|in:si,no',
            'rutina_actual' => 'nullable|string|max:1000',
            'tipo_entrenamiento' => 'nullable|in:pesas,funcional,hibrido,calistenia,sin_preferencia',
            'horario' => 'nullable|in:manana,mediodia,tarde,noche',
            'restricciones_ejercicio' => 'nullable|string|max:1000',
            'condiciones_medicas' => 'nullable|string|max:2000',
            'medicamentos' => 'nullable|string|max:1000',
            'dieta_actual' => 'nullable|in:sin_dieta,equilibrada,alta_proteina,vegetariana,vegana,keto,otra',
            'alergias' => 'nullable|string|max:500',
            'experiencia_macros' => 'nullable|in:ninguna,basica,intermedia,avanzada',
            'horario_trabajo' => 'nullable|in:oficina,remoto,turnos,estudiante,independiente',
            'comer_fuera' => 'nullable|in:nunca,1-2,3-4,diario',
            'notas' => 'nullable|string|max:2000',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo valido.',
            'whatsapp.required' => 'El numero de WhatsApp es obligatorio.',
            'edad.required' => 'La edad es obligatoria.',
            'edad.min' => 'La edad minima es 14 anos.',
            'edad.max' => 'La edad maxima es 80 anos.',
            'peso.required' => 'El peso es obligatorio.',
            'peso.min' => 'El peso minimo es 30 kg.',
            'peso.max' => 'El peso maximo es 250 kg.',
            'estatura.required' => 'La estatura es obligatoria.',
            'estatura.min' => 'La estatura minima es 100 cm.',
            'estatura.max' => 'La estatura maxima es 230 cm.',
            'genero.required' => 'El genero es obligatorio.',
            'objetivo.required' => 'El objetivo es obligatorio.',
            'plan.required' => 'Selecciona un plan.',
            'plan.in' => 'El plan seleccionado no es valido.',
            'experiencia.required' => 'La experiencia es obligatoria.',
            'dias_disponibles.required' => 'Los dias disponibles son obligatorios.',
            'password.required' => 'La contrasena es obligatoria.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
            'terminos.accepted' => 'Debes aceptar los terminos y condiciones.',
        ]);

        try {
            // Build extras JSON with password_hash, UTM data, and extra fields
            $extras = [
                'password_hash' => bcrypt($validated['password']),
                'peso' => $validated['peso'],
                'estatura' => $validated['estatura'],
                'genero' => $validated['genero'],
                'equipamiento' => $validated['equipamiento'] ?? null,
            ];

            // Append UTM tracking data if present
            if ($request->filled('utm_source')) {
                $extras['utm_source'] = $validated['utm_source'];
            }
            if ($request->filled('utm_medium')) {
                $extras['utm_medium'] = $validated['utm_medium'];
            }
            if ($request->filled('utm_campaign')) {
                $extras['utm_campaign'] = $validated['utm_campaign'];
            }

            // Persist Vue InscriptionForm extras (como_conocio is stored on dedicated column, not duplicated)
            foreach ([
                'coaching_previo', 'rutina_actual', 'tipo_entrenamiento',
                'horario', 'restricciones_ejercicio', 'condiciones_medicas', 'medicamentos',
                'dieta_actual', 'alergias', 'experiencia_macros',
                'horario_trabajo', 'comer_fuera', 'notas',
            ] as $extraKey) {
                if (! empty($validated[$extraKey])) {
                    $extras[$extraKey] = $validated[$extraKey];
                }
            }

            // Store objetivo with extras JSON separated by |||
            $objetivoWithExtras = $validated['objetivo'].'|||'.json_encode($extras, JSON_UNESCAPED_UNICODE);

            $inscription = Inscription::create([
                'id' => Str::ulid()->toBase32(),
                'nombre' => $validated['nombre'],
                'apellido' => $validated['apellido'] ?? null,
                'email' => $validated['email'],
                'whatsapp' => $validated['whatsapp'],
                'edad' => $validated['edad'],
                'objetivo' => $objetivoWithExtras,
                'plan' => $validated['plan'],
                'experiencia' => $validated['experiencia'],
                'dias_disponibles' => $validated['dias_disponibles'],
                'lesion' => $validated['lesion'] ?? null,
                'ciudad' => $validated['ciudad'] ?? null,
                'pais' => $validated['pais'] ?? null,
                'como_conocio' => $validated['como_conocio'] ?? $validated['referral'] ?? null,
                'status' => 'pending_contact',
                'ip_hash' => hash('sha256', $request->ip()),
            ]);

            // Link referral if a referral code was provided
            if (! empty($validated['referral'])) {
                $referrer = Client::where('referral_code', $validated['referral'])->first();
                if ($referrer) {
                    $inscription->update(['como_conocio' => 'referral:'.$referrer->referral_code]);

                    Referral::create([
                        'referrer_id' => $referrer->id,
                        'referred_email' => $validated['email'],
                        'status' => 'pending',
                        'reward_granted' => false,
                    ]);
                }
            }

            // Notify admin of new inscription
            try {
                WellcoreNotification::create([
                    'user_type' => UserType::Admin,
                    'user_id' => 1,
                    'type' => 'new_inscription',
                    'title' => 'Nueva inscripcion',
                    'body' => "{$validated['nombre']} se inscribio al plan {$validated['plan']}.",
                    'link' => '/admin/inscriptions',
                ]);
            } catch (\Throwable $e) {
                Log::warning('Admin notification for new inscription failed', ['error' => $e->getMessage()]);
            }

            // Fire Meta CAPI Lead event (non-blocking)
            try {
                $this->fireMetaLeadEvent($request, $validated);
            } catch (\Throwable $e) {
                Log::warning('Meta CAPI Lead event failed', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'message' => 'Inscripcion recibida exitosamente.',
                'id' => $inscription->id,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('PublicFormController::inscriptionSubmit failed', [
                'error' => $e->getMessage(),
                'email' => $validated['email'] ?? null,
            ]);

            return response()->json([
                'message' => 'Error al procesar tu inscripcion. Intenta de nuevo.',
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // 2. Coach Application
    // -------------------------------------------------------------------------
    public function coachApply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'whatsapp' => 'required|string|max:50',
            'city' => 'required|string|max:100',
            'bio' => 'required|string|min:50|max:2000',
            'experience' => 'required|in:1-2,3-5,5-10,10+',
            'plan' => 'required|in:training,nutrition,both',
            'current_clients' => 'required|in:0,1-5,6-15,16+',
            'specializations' => 'required|array|min:1',
            'referral' => 'nullable|string|max:255',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo valido.',
            'whatsapp.required' => 'El numero de WhatsApp es obligatorio.',
            'city.required' => 'La ciudad es obligatoria.',
            'bio.required' => 'La biografia es obligatoria.',
            'bio.min' => 'La biografia debe tener al menos 50 caracteres.',
            'bio.max' => 'La biografia no puede superar los 2000 caracteres.',
            'experience.required' => 'Selecciona tu experiencia.',
            'experience.in' => 'Selecciona una opcion valida de experiencia.',
            'plan.required' => 'Selecciona el tipo de coaching.',
            'plan.in' => 'Selecciona una opcion valida de coaching.',
            'current_clients.required' => 'Selecciona cuantos clientes manejas.',
            'current_clients.in' => 'Selecciona una opcion valida.',
            'specializations.required' => 'Selecciona al menos una especializacion.',
            'specializations.min' => 'Selecciona al menos una especializacion.',
        ]);

        try {
            $application = CoachApplicationModel::create([
                'id' => Str::ulid(),
                'name' => $validated['name'],
                'email' => $validated['email'],
                'whatsapp' => $validated['whatsapp'],
                'city' => $validated['city'],
                'bio' => $validated['bio'],
                'experience' => $validated['experience'],
                'plan' => $validated['plan'],
                'current_clients' => $validated['current_clients'],
                'specializations' => $validated['specializations'],
                'referral' => $validated['referral'] ?? null,
                'ip_hash' => hash('sha256', $request->ip()),
                'status' => 'pending',
            ]);

            return response()->json([
                'message' => 'Aplicacion recibida exitosamente. Nos comunicaremos contigo pronto.',
                'id' => $application->id,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('PublicFormController::coachApply failed', [
                'error' => $e->getMessage(),
                'email' => $validated['email'] ?? null,
            ]);

            return response()->json([
                'message' => 'Error al enviar tu aplicacion. Intenta de nuevo.',
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // 3. RISE Enrollment (3-step form)
    // -------------------------------------------------------------------------
    public function riseEnroll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // Step 1: Personal data
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'whatsapp' => 'required|string|max:50',
            'edad' => 'required|integer|min:16|max:80',
            'peso' => 'required|numeric|min:30|max:300',
            'estatura' => 'required|numeric|min:100|max:250',
            'genero' => 'required|in:male,female,other',
            'ciudad' => 'required|string|max:100',
            // Step 2: Goals & level
            'objetivo' => 'required|string|max:500',
            'experiencia' => 'required|in:principiante,intermedio,avanzado',
            'ubicacion_entrenamiento' => 'required|in:gym,home,hybrid',
            'dias_disponibles' => 'required|in:3,4,5,6',
            'lesion' => 'required|in:si,no',
            'motivacion' => 'required|string|max:500',
            // Optional
            'detalle_lesion' => 'nullable|string|max:500',
            'pais' => 'nullable|string|max:100',
        ], [
            'nombre.required' => 'Tu nombre es obligatorio.',
            'apellido.required' => 'Tu apellido es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo valido.',
            'whatsapp.required' => 'El numero de WhatsApp es obligatorio.',
            'edad.required' => 'Tu edad es obligatoria.',
            'edad.min' => 'Debes tener al menos 16 anos.',
            'edad.max' => 'La edad maxima es 80 anos.',
            'peso.required' => 'Tu peso es obligatorio.',
            'peso.min' => 'El peso minimo es 30 kg.',
            'peso.max' => 'El peso maximo es 300 kg.',
            'estatura.required' => 'Tu estatura es obligatoria.',
            'estatura.min' => 'La estatura minima es 100 cm.',
            'estatura.max' => 'La estatura maxima es 250 cm.',
            'genero.required' => 'Selecciona tu genero.',
            'genero.in' => 'Selecciona una opcion valida.',
            'ciudad.required' => 'Tu ciudad es obligatoria.',
            'objetivo.required' => 'Tu objetivo es obligatorio.',
            'experiencia.required' => 'Selecciona tu nivel de experiencia.',
            'experiencia.in' => 'Selecciona una opcion valida.',
            'ubicacion_entrenamiento.required' => 'Selecciona donde entrenas.',
            'ubicacion_entrenamiento.in' => 'Selecciona una opcion valida.',
            'dias_disponibles.required' => 'Selecciona tus dias disponibles.',
            'dias_disponibles.in' => 'Selecciona una opcion valida.',
            'lesion.required' => 'Indica si tienes alguna lesion.',
            'lesion.in' => 'Selecciona si o no.',
            'motivacion.required' => 'Cuentanos tu motivacion.',
        ]);

        try {
            $client = Client::where('email', $validated['email'])->first();

            if ($client) {
                // Existing client: create RISE program with start_date = next Monday + 7 days
                $startDate = now()->addDays(7)->startOfWeek();

                RiseProgram::create([
                    'client_id' => $client->id,
                    'enrollment_date' => now(),
                    'start_date' => $startDate,
                    'end_date' => $startDate->copy()->addWeeks(12),
                    'experience_level' => $validated['experiencia'],
                    'training_location' => $validated['ubicacion_entrenamiento'],
                    'gender' => $validated['genero'],
                    'status' => 'active',
                ]);

                return response()->json([
                    'message' => 'Inscripcion RISE completada. Tu programa inicia pronto.',
                    'programCreated' => true,
                ], 201);
            }

            // New user: record enrollment data via Inscription for admin to process later
            Inscription::create([
                'id' => Str::ulid()->toBase32(),
                'nombre' => $validated['nombre'],
                'apellido' => $validated['apellido'],
                'email' => $validated['email'],
                'whatsapp' => $validated['whatsapp'],
                'edad' => $validated['edad'],
                'ciudad' => $validated['ciudad'],
                'pais' => $validated['pais'] ?? 'Colombia',
                'objetivo' => $validated['objetivo'],
                'experiencia' => $validated['experiencia'],
                'dias_disponibles' => $validated['dias_disponibles'],
                'lesion' => $validated['lesion'],
                'detalle_lesion' => $validated['detalle_lesion'] ?? null,
                'plan' => 'rise',
                'como_conocio' => 'rise_enrollment',
                'status' => 'pending_contact',
                'ip_hash' => hash('sha256', $request->ip()),
            ]);

            return response()->json([
                'message' => 'Inscripcion recibida. Un asesor se comunicara contigo pronto.',
                'programCreated' => false,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('PublicFormController::riseEnroll failed', [
                'error' => $e->getMessage(),
                'email' => $validated['email'] ?? null,
            ]);

            return response()->json([
                'message' => 'Error al procesar tu inscripcion. Intenta de nuevo.',
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // 4. Presencial Form
    // -------------------------------------------------------------------------
    public function presencialSubmit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'whatsapp' => 'required|string|max:50',
            'edad' => 'required|integer|min:16|max:80',
            'objetivo' => 'required|string|max:500',
            'experiencia' => 'required|in:principiante,intermedio,avanzado',
            'horario' => 'required|string|max:100',
            'dias_disponibles' => 'required|in:3,4,5',
            'lesion' => 'required|in:si,no',
            'detalle_lesion' => 'nullable|string|max:500',
        ], [
            'nombre.required' => 'Tu nombre es obligatorio.',
            'apellido.required' => 'Tu apellido es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo valido.',
            'whatsapp.required' => 'El numero de WhatsApp es obligatorio.',
            'edad.required' => 'Tu edad es obligatoria.',
            'edad.min' => 'Debes tener al menos 16 anos.',
            'edad.max' => 'La edad maxima es 80 anos.',
            'objetivo.required' => 'Tu objetivo es obligatorio.',
            'experiencia.required' => 'Selecciona tu nivel de experiencia.',
            'experiencia.in' => 'Selecciona una opcion valida.',
            'horario.required' => 'El horario es obligatorio.',
            'dias_disponibles.required' => 'Selecciona tus dias disponibles.',
            'dias_disponibles.in' => 'Selecciona 3, 4 o 5 dias.',
            'lesion.required' => 'Indica si tienes alguna lesion.',
            'lesion.in' => 'Selecciona si o no.',
        ]);

        try {
            $inscription = Inscription::create([
                'id' => Str::ulid()->toBase32(),
                'plan' => 'esencial',
                'nombre' => $validated['nombre'],
                'apellido' => $validated['apellido'],
                'email' => $validated['email'],
                'whatsapp' => $validated['whatsapp'],
                'edad' => $validated['edad'],
                'ciudad' => 'Bogota',
                'pais' => 'Colombia',
                'objetivo' => $validated['objetivo'],
                'experiencia' => $validated['experiencia'],
                'horario' => $validated['horario'],
                'dias_disponibles' => $validated['dias_disponibles'],
                'lesion' => $validated['lesion'],
                'detalle_lesion' => $validated['detalle_lesion'] ?? null,
                'como_conocio' => 'presencial',
                'status' => 'pending_contact',
                'ip_hash' => hash('sha256', $request->ip()),
            ]);

            return response()->json([
                'message' => 'Inscripcion presencial recibida. Te contactaremos pronto.',
                'id' => $inscription->id,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('PublicFormController::presencialSubmit failed', [
                'error' => $e->getMessage(),
                'email' => $validated['email'] ?? null,
            ]);

            return response()->json([
                'message' => 'Error al enviar tu solicitud. Intenta de nuevo.',
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // 5. Trial Signup (7-day free trial)
    // -------------------------------------------------------------------------
    public function trialSignup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:clients,email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
            'apellido' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo valido.',
            'email.unique' => 'Este correo ya tiene una cuenta registrada.',
            'password.required' => 'La contrasena es obligatoria.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
        ]);

        try {
            $client = DB::transaction(function () use ($validated) {
                $code = 'WC-'.strtoupper(Str::random(6));

                return Client::create([
                    'client_code' => $code,
                    'name' => trim($validated['nombre'].' '.($validated['apellido'] ?? '')),
                    'email' => $validated['email'],
                    'password_hash' => Hash::make($validated['password']),
                    'plan' => 'metodo',
                    'status' => 'trial',
                    'fecha_inicio' => now()->toDateString(),
                ]);
            });

            $result = TrialService::startTrial($client->id);

            if (! $result['success']) {
                return response()->json(['message' => $result['error']], 422);
            }

            return response()->json([
                'message' => 'Tu trial de 7 días está activo',
                'ends_at' => $result['ends_at'],
            ], 201);
        } catch (\Throwable $e) {
            Log::error('PublicFormController::trialSignup failed', [
                'error' => $e->getMessage(),
                'email' => $validated['email'] ?? null,
            ]);

            return response()->json([
                'message' => 'Error al crear tu cuenta de prueba. Intenta de nuevo.',
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // 6. Resolve Invitation (Vue SPA — /unirse/{code} prefill)
    // -------------------------------------------------------------------------
    public function resolveInvitation(string $code): JsonResponse
    {
        $invitation = Invitation::where('code', strtoupper($code))->first();

        if (! $invitation) {
            return response()->json([
                'valid' => false,
                'status' => 'invalid',
                'message' => 'Codigo de invitacion no encontrado.',
            ]);
        }

        $rawStatus = $invitation->getRawOriginal('status') ?? 'pending';

        if ($rawStatus !== 'pending') {
            $status = $rawStatus === 'expired' ? 'expired' : ($rawStatus === 'used' ? 'used' : 'invalid');

            return response()->json([
                'valid' => false,
                'status' => $status,
                'message' => $this->invitationStatusMessage($status),
            ]);
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            return response()->json([
                'valid' => false,
                'status' => 'expired',
                'message' => $this->invitationStatusMessage('expired'),
            ]);
        }

        $plan = $invitation->plan instanceof PlanType
            ? $invitation->plan->value
            : (string) $invitation->plan;

        return response()->json([
            'valid' => true,
            'status' => 'pending',
            'plan' => $plan,
            'plan_label' => $this->planLabel($plan),
            'email_hint' => $invitation->email_hint,
        ]);
    }

    // -------------------------------------------------------------------------
    // 7. Invitation Intake (Vue SPA — create real client account)
    // -------------------------------------------------------------------------
    public function invitationIntake(Request $request): JsonResponse
    {
        // Normalize input aliases before validation (estatura→altura, lesion→tiene_lesiones, etc.)
        $payload = $this->normalizeIntakePayload($request->all());

        // 1. Validate invitation FIRST (anti-tampering: plan always comes from DB, never payload)
        $code = strtoupper((string) ($payload['invitation_code'] ?? ''));
        $invitation = $code !== ''
            ? Invitation::where('code', $code)->first()
            : null;

        $invalidCode = ! $invitation
            || ($invitation->getRawOriginal('status') ?? 'pending') !== 'pending'
            || ($invitation->expires_at && $invitation->expires_at->isPast());

        if ($invalidCode) {
            return response()->json([
                'errors' => ['invitation_code' => ['Codigo invalido o expirado.']],
            ], 422);
        }

        $planType = $invitation->plan instanceof PlanType
            ? $invitation->plan->value
            : (string) $invitation->plan;

        // 2. Validate payload according to plan-specific rules
        try {
            $validated = validator($payload, $this->buildIntakeRules($planType), $this->intakeMessages())
                ->validate();
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        // 3. Race-condition guard for email uniqueness
        if (Client::where('email', $validated['email'])->exists()) {
            return response()->json([
                'errors' => ['email' => ['Este email ya tiene una cuenta registrada.']],
            ], 409);
        }

        try {
            $client = DB::transaction(function () use ($validated, $invitation, $planType) {
                do {
                    $clientCode = 'WC-'.strtoupper(Str::random(6));
                } while (Client::where('client_code', $clientCode)->exists());

                $client = Client::create([
                    'client_code' => $clientCode,
                    'name' => trim($validated['nombre'].' '.$validated['apellido']),
                    'email' => $validated['email'],
                    'password_hash' => Hash::make($validated['password']),
                    'plan' => $planType, // ALWAYS from invitation, never from payload
                    'status' => 'activo',
                    'fecha_inicio' => now()->toDateString(),
                    'city' => $validated['ciudad'],
                    'onboarding_completed' => 0,
                ]);

                ClientProfile::create([
                    'client_id' => $client->id,
                    'edad' => $validated['edad'],
                    'peso' => $validated['peso'],
                    'altura' => $validated['altura'],
                    'genero' => $this->normalizeGender($validated['genero']),
                    'objetivo' => $validated['objetivo_principal'],
                    'ciudad' => $validated['ciudad'],
                    'whatsapp' => $validated['whatsapp'],
                    'nivel' => $validated['nivel_experiencia'],
                    'lugar_entreno' => $validated['lugar_entreno'],
                    'dias_disponibles' => $validated['dias_disponibles'],
                    'restricciones' => $validated['detalle_lesiones'] ?? null,
                    'macros' => $this->buildMacrosPayload($validated, $planType),
                ]);

                Invitation::where('code', $invitation->code)->update([
                    'status' => 'used',
                    'used_by' => $client->id,
                    'used_at' => now(),
                ]);

                $this->convertReferralIfPending($client, $validated['email']);

                return $client;
            });

            return response()->json([
                'success' => true,
                'client_id' => $client->id,
                'message' => 'Cuenta creada exitosamente.',
                'redirect_url' => '/login',
            ], 201);
        } catch (\Throwable $e) {
            Log::error('PublicFormController::invitationIntake failed', [
                'error' => $e->getMessage(),
                'class' => get_class($e),
                'email' => $validated['email'] ?? null,
                'plan' => $planType,
                'code' => $invitation->code,
            ]);

            if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'unique')) {
                return response()->json([
                    'errors' => ['email' => ['Este email ya esta registrado.']],
                ], 409);
            }

            return response()->json([
                'message' => 'Error al crear tu cuenta. Intenta de nuevo.',
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Fire a Meta Conversions API (CAPI) Lead event for tracking.
     * Non-blocking — failures are logged but do not affect the response.
     */
    private function fireMetaLeadEvent(Request $request, array $data): void
    {
        $pixelId = config('services.meta.pixel_id');
        $accessToken = config('services.meta.access_token');

        if (! $pixelId || ! $accessToken) {
            return;
        }

        $eventData = [
            'data' => [[
                'event_name' => 'Lead',
                'event_time' => time(),
                'action_source' => 'website',
                'event_source_url' => $request->header('Referer', config('app.url')),
                'user_data' => [
                    'em' => [hash('sha256', strtolower(trim($data['email'])))],
                    'ph' => [hash('sha256', preg_replace('/\D/', '', $data['whatsapp']))],
                    'fn' => [hash('sha256', strtolower(trim($data['nombre'])))],
                    'client_ip_address' => $request->ip(),
                    'client_user_agent' => $request->userAgent(),
                ],
                'custom_data' => [
                    'plan' => $data['plan'],
                    'currency' => 'COP',
                ],
            ]],
        ];

        if ($request->filled('utm_source')) {
            $eventData['data'][0]['custom_data']['utm_source'] = $data['utm_source'];
        }
        if ($request->filled('utm_medium')) {
            $eventData['data'][0]['custom_data']['utm_medium'] = $data['utm_medium'];
        }
        if ($request->filled('utm_campaign')) {
            $eventData['data'][0]['custom_data']['utm_campaign'] = $data['utm_campaign'];
        }

        Http::timeout(5)
            ->connectTimeout(3)
            ->post("https://graph.facebook.com/v18.0/{$pixelId}/events?access_token={$accessToken}", $eventData);
    }

    private function invitationStatusMessage(string $status): string
    {
        return match ($status) {
            'expired' => 'Esta invitacion ha expirado.',
            'used' => 'Esta invitacion ya fue utilizada.',
            default => 'Codigo de invitacion invalido.',
        };
    }

    private function planLabel(string $plan): string
    {
        return match ($plan) {
            'esencial' => 'Esencial',
            'metodo' => 'El Metodo',
            'elite' => 'Elite',
            'presencial' => 'Presencial',
            'rise' => 'Rise',
            'trial' => 'Trial',
            default => ucfirst($plan),
        };
    }

    /**
     * Map Vue payload aliases to canonical keys used by the validator and DB.
     * Accepts variants like: estatura→altura, lesion→tiene_lesiones, terminos→acepta_terminos.
     */
    private function normalizeIntakePayload(array $input): array
    {
        $aliases = [
            'estatura' => 'altura',
            'objetivo' => 'objetivo_principal',
            'experiencia' => 'nivel_experiencia',
            'lesion' => 'tiene_lesiones',
            'detalle_lesion' => 'detalle_lesiones',
            'terminos' => 'acepta_terminos',
            'horario' => 'horario_preferido',
        ];

        foreach ($aliases as $from => $to) {
            if (array_key_exists($from, $input) && ! array_key_exists($to, $input)) {
                $input[$to] = $input[$from];
            }
        }

        if (isset($input['equipamiento']) && empty($input['lugar_entreno'])) {
            $input['lugar_entreno'] = match ($input['equipamiento']) {
                'gimnasio_completo', 'gimnasio_basico' => 'gym',
                'casa_equipamiento' => 'casa_con_equipo',
                'casa_sin_equipamiento' => 'casa_sin_equipo',
                default => $input['equipamiento'],
            };
        }

        if (isset($input['dias_disponibles']) && is_string($input['dias_disponibles']) && ctype_digit($input['dias_disponibles'])) {
            $count = (int) $input['dias_disponibles'];
            $catalog = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
            $input['dias_disponibles'] = array_slice($catalog, 0, max(0, min($count, 7)));
        }

        if (isset($input['duracion_sesion'])) {
            $input['duracion_sesion'] = match ((string) $input['duracion_sesion']) {
                '30-45' => '45',
                '45-60' => '60',
                '60-90' => '75',
                '90+' => '90',
                default => (string) $input['duracion_sesion'],
            };
        }

        return $input;
    }

    private function buildIntakeRules(string $planType): array
    {
        $rules = [
            'invitation_code' => 'required|string|size:12',
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'whatsapp' => 'required|string|max:50',
            'edad' => 'required|integer|min:16|max:80',
            'peso' => 'required|numeric|min:30|max:300',
            'altura' => 'required|numeric|min:100|max:250',
            'genero' => 'required|in:hombre,mujer,otro,masculino,femenino',
            'ciudad' => 'required|string|max:100',
            'pais' => 'nullable|string|max:100',
            'objetivo_principal' => 'required|string|max:255',
            'nivel_experiencia' => 'required|in:principiante,intermedio,avanzado',
            'lugar_entreno' => 'required|in:gym,casa_con_equipo,casa_sin_equipo,aire_libre,mixto',
            'dias_disponibles' => 'required|array|min:2',
            'duracion_sesion' => ['required', Rule::in(['30', '45', '60', '75', '90', 30, 45, 60, 75, 90])],
            'tiene_lesiones' => 'required|in:si,no',
            'detalle_lesiones' => 'required_if:tiene_lesiones,si|nullable|string|max:500',
            'password' => 'required|string|min:8|confirmed',
            'acepta_terminos' => 'accepted',
            // Vue InscriptionForm extras (always optional, preserved in macros JSON)
            'coaching_previo' => 'nullable|in:si,no',
            'rutina_actual' => 'nullable|string|max:1000',
            'tipo_entrenamiento' => 'nullable|in:pesas,funcional,hibrido,calistenia,sin_preferencia',
            'horario' => 'nullable|in:manana,mediodia,tarde,noche',
            'restricciones_ejercicio' => 'nullable|string|max:1000',
            'condiciones_medicas' => 'nullable|string|max:2000',
            'medicamentos' => 'nullable|string|max:1000',
            'dieta_actual' => 'nullable|in:sin_dieta,equilibrada,alta_proteina,vegetariana,vegana,keto,otra',
            'alergias' => 'nullable|string|max:500',
            'experiencia_macros' => 'nullable|in:ninguna,basica,intermedia,avanzada',
            'horario_trabajo' => 'nullable|in:oficina,remoto,turnos,estudiante,independiente',
            'comer_fuera' => 'nullable|in:nunca,1-2,3-4,diario',
            'como_conocio' => 'nullable|string|max:255',
            'notas' => 'nullable|string|max:2000',
        ];

        if (in_array($planType, ['metodo', 'elite'], true)) {
            $rules += [
                'trabajo_tipo' => 'required|in:sedentario,moderado,activo',
                'horas_sueno' => 'required|in:5_menos,6_7,8_mas',
                'nivel_estres' => 'required|in:bajo,moderado,alto,muy_alto',
                'comidas_por_dia' => 'required|in:2,3,4,5_mas',
                'intolerancias' => 'nullable|array',
                'otras_intolerancias' => 'nullable|string|max:500',
                'alimentos_evitar' => 'nullable|string|max:500',
                'suplementos_actuales' => 'nullable|string|max:500',
            ];
        }

        if ($planType === 'elite') {
            $rules += [
                'objetivo_composicion' => 'required|string|max:500',
                'historial_medico' => 'required|string|max:1000',
                'ciclo_hormonal' => 'required|in:si,no',
                'bloodwork_disponible' => 'required|in:si,no',
            ];
        }

        if ($planType === 'rise') {
            $rules['compromiso_30dias'] = 'accepted';
        }

        if ($planType === 'presencial') {
            $rules['horario_preferido'] = 'required|in:manana,tarde,noche';
        }

        return $rules;
    }

    private function intakeMessages(): array
    {
        return [
            'invitation_code.required' => 'El codigo de invitacion es obligatorio.',
            'invitation_code.size' => 'El codigo de invitacion debe tener 12 caracteres.',
            'email.unique' => 'Este email ya tiene una cuenta. Intenta iniciar sesion.',
            'edad.min' => 'Debes tener al menos 16 anos.',
            'edad.max' => 'La edad maxima es 80 anos.',
            'peso.min' => 'El peso minimo es 30 kg.',
            'peso.max' => 'El peso maximo es 300 kg.',
            'altura.min' => 'La altura minima es 100 cm.',
            'altura.max' => 'La altura maxima es 250 cm.',
            'dias_disponibles.min' => 'Selecciona al menos 2 dias disponibles.',
            'detalle_lesiones.required_if' => 'Describe tus lesiones o restricciones.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
            'acepta_terminos.accepted' => 'Debes aceptar los terminos para continuar.',
            'compromiso_30dias.accepted' => 'Debes aceptar el compromiso de 30 dias.',
            'horario_preferido.required' => 'Selecciona tu horario preferido.',
        ];
    }

    private function normalizeGender(string $genero): string
    {
        return match ($genero) {
            'masculino' => 'hombre',
            'femenino' => 'mujer',
            default => $genero,
        };
    }

    private function buildMacrosPayload(array $validated, string $planType): array
    {
        $macros = [
            'pais' => $validated['pais'] ?? 'Colombia',
            'duracion_sesion' => (string) $validated['duracion_sesion'],
            'tiene_lesiones' => $validated['tiene_lesiones'],
        ];

        if (in_array($planType, ['metodo', 'elite'], true)) {
            $macros += [
                'trabajo_tipo' => $validated['trabajo_tipo'] ?? null,
                'horas_sueno' => $validated['horas_sueno'] ?? null,
                'nivel_estres' => $validated['nivel_estres'] ?? null,
                'intolerancias' => $validated['intolerancias'] ?? [],
                'otras_intolerancias' => $validated['otras_intolerancias'] ?? '',
                'alimentos_evitar' => $validated['alimentos_evitar'] ?? '',
                'comidas_por_dia' => $validated['comidas_por_dia'] ?? null,
                'suplementos_actuales' => $validated['suplementos_actuales'] ?? '',
            ];
        }

        if ($planType === 'elite') {
            $macros += [
                'objetivo_composicion' => $validated['objetivo_composicion'] ?? '',
                'historial_medico' => $validated['historial_medico'] ?? '',
                'ciclo_hormonal' => $validated['ciclo_hormonal'] ?? 'no',
                'bloodwork_disponible' => $validated['bloodwork_disponible'] ?? 'no',
            ];
        }

        if ($planType === 'rise') {
            $macros['compromiso_30dias'] = ! empty($validated['compromiso_30dias']);
        }

        if ($planType === 'presencial') {
            $macros['horario_preferido'] = $validated['horario_preferido'] ?? null;
        }

        // Vue InscriptionForm extras: always preserved across all plans, filtered to drop blanks.
        // Renames: horario → horario_entreno_preferido (avoid clash with presencial horario_preferido),
        //          alergias → alergias_texto (avoid clash with intolerancias array on metodo/elite).
        $extras = array_filter([
            'coaching_previo' => $validated['coaching_previo'] ?? null,
            'rutina_actual' => $validated['rutina_actual'] ?? null,
            'tipo_entrenamiento' => $validated['tipo_entrenamiento'] ?? null,
            'horario_entreno_preferido' => $validated['horario'] ?? null,
            'restricciones_ejercicio' => $validated['restricciones_ejercicio'] ?? null,
            'condiciones_medicas' => $validated['condiciones_medicas'] ?? null,
            'medicamentos' => $validated['medicamentos'] ?? null,
            'dieta_actual' => $validated['dieta_actual'] ?? null,
            'alergias_texto' => $validated['alergias'] ?? null,
            'experiencia_macros' => $validated['experiencia_macros'] ?? null,
            'horario_trabajo' => $validated['horario_trabajo'] ?? null,
            'comer_fuera' => $validated['comer_fuera'] ?? null,
            'como_conocio' => $validated['como_conocio'] ?? null,
            'notas' => $validated['notas'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        return array_merge($macros, $extras);
    }

    private function convertReferralIfPending(Client $client, string $email): void
    {
        $referral = Referral::where('referred_email', $email)
            ->where('status', 'pending')
            ->first();

        if (! $referral) {
            return;
        }

        $referral->update([
            'referred_id' => $client->id,
            'status' => 'converted',
            'reward_granted' => true,
            'converted_at' => now(),
        ]);

        $client->update(['referred_by' => $referral->referrer_id]);

        WellCoinsService::earn($referral->referrer_id, 'referral_signup');
    }
}
