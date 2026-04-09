<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CoachApplication as CoachApplicationModel;
use App\Models\Inscription;
use App\Models\Referral;
use App\Models\RiseProgram;
use App\Models\WellcoreNotification;
use App\Services\TrialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
}
