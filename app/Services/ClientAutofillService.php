<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientProfile;
use App\Models\Inscription;
use App\Models\Metric;
use Illuminate\Support\Carbon;

/**
 * Pre-fills plan ticket form data using known sources in WellCore DB.
 *
 * Sources used (verified columns):
 *  - clients (Client model): id, name, email, plan, birth_date, city
 *  - client_profiles (ClientProfile model): edad, peso, altura, objetivo,
 *    nivel, lugar_entreno, dias_disponibles, restricciones, macros
 *  - inscriptions (Inscription model): edad, objetivo, experiencia, lesion,
 *    detalle_lesion, dias_disponibles, horario (matched by email)
 *  - metrics (Metric model): peso (latest log_date)
 *
 * Any source that returns null is silently skipped.
 */
final class ClientAutofillService
{
    public function forClient(int $clientId): array
    {
        $client = Client::find($clientId);

        if (! $client) {
            return [
                'datos_generales' => [],
                'plan_entrenamiento' => [],
                'plan_nutricional' => [],
            ];
        }

        $profile = ClientProfile::where('client_id', $clientId)->first();
        $inscription = $client->email
            ? Inscription::where('email', $client->email)->orderByDesc('created_at')->first()
            : null;
        $latestMetric = Metric::where('client_id', $clientId)->orderByDesc('log_date')->first();

        return [
            'datos_generales' => $this->buildDatosGenerales($client, $profile, $inscription, $latestMetric),
            'plan_entrenamiento' => $this->buildPlanEntrenamiento($profile, $inscription),
            'plan_nutricional' => $this->buildPlanNutricional($profile, $inscription),
        ];
    }

    private function buildDatosGenerales(
        Client $client,
        ?ClientProfile $profile,
        ?Inscription $inscription,
        ?Metric $metric,
    ): array {
        $edad = $profile?->edad
            ?? $inscription?->edad
            ?? $this->computeAgeFromBirthDate($client->birth_date);

        $pesoInicial = $profile?->peso;
        $pesoActual = $metric?->peso ?? $profile?->peso;

        return array_filter([
            'nombre' => $client->name,
            'email' => $client->email,
            'edad' => $edad,
            'ciudad' => $profile?->ciudad ?? $inscription?->ciudad ?? $client->city,
            'plan' => $client->plan?->value,
            'peso_inicial' => $pesoInicial,
            'peso_actual' => $pesoActual,
            'estatura' => $profile?->altura,
        ], fn ($v) => $v !== null && $v !== '');
    }

    private function buildPlanEntrenamiento(?ClientProfile $profile, ?Inscription $inscription): array
    {
        $lesion = null;

        if ($inscription) {
            $lesion = $inscription->lesion
                ? trim(($inscription->lesion ?? '').' '.($inscription->detalle_lesion ?? ''))
                : null;
        }

        return array_filter([
            'nivel_actividad' => $profile?->nivel ?? $inscription?->experiencia,
            'lugar_entreno' => $profile?->lugar_entreno,
            'dias_disponibles' => $profile?->dias_disponibles ?? $inscription?->dias_disponibles,
            'horario' => $inscription?->horario,
            'lesiones' => $lesion,
            'restricciones' => $profile?->restricciones,
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    private function buildPlanNutricional(?ClientProfile $profile, ?Inscription $inscription): array
    {
        return array_filter([
            'objetivo' => $profile?->objetivo ?? $inscription?->objetivo,
            'macros' => $profile?->macros,
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    private function computeAgeFromBirthDate(mixed $birthDate): ?int
    {
        if (! $birthDate) {
            return null;
        }

        try {
            return Carbon::parse($birthDate)->age;
        } catch (\Throwable) {
            return null;
        }
    }
}
