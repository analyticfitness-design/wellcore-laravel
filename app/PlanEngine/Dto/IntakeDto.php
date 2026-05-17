<?php

declare(strict_types=1);

namespace App\PlanEngine\Dto;

/**
 * IntakeDto — DTO inmutable que captura el intake del cliente para el motor v2.
 *
 * Es el output de Stage 1 (INTAKE) y se pasa a las stages siguientes
 * (SELECT, COMPOSE, VALIDATE, PERSIST, VERIFY) sin mutación.
 *
 * Ver docs/wellcore-engine-v2/04-stages-architecture.md §2 (convenciones de tipos)
 * y §3 (Stage 1 INTAKE).
 *
 * Reglas:
 * - readonly class — cero mutación. Si una stage necesita transformar, devuelve DTO nuevo.
 * - Tipos primitivos donde se puede (no objetos intermedios complejos).
 * - Validación de invariantes en el constructor (throws InvalidArgumentException).
 */
final readonly class IntakeDto
{
    public function __construct(
        /** ID del cliente en wellcore_fitness.clients */
        public int $clientId,

        /** Vertical del plan a generar */
        public string $vertical,

        /** Nombre del cliente (para personalizar notas_coach) */
        public string $clientName,

        /** "M" | "F" — usado en cálculo de macros Mifflin-St Jeor */
        public string $gender,

        /** Edad en años */
        public int $age,

        /** Peso en kg (puede ser null si vertical no requiere — ej. solo hábitos) */
        public ?float $weightKg,

        /** Estatura en cm (idem) */
        public ?float $heightCm,

        /** Objetivo: hipertrofia | perdida_grasa | recomposicion | mantenimiento | performance */
        public string $goal,

        /** Nivel: principiante | intermedio | avanzado */
        public string $level,

        /** Días disponibles a la semana (3-7) */
        public int $daysAvailable,

        /** Lugar: gym | casa | hibrido */
        public string $place,

        /** Lista de equipamiento disponible. ["bodyweight"] si es solo peso corporal. */
        public array $equipment,

        /** Lesiones / contraindicaciones (texto libre del coach, validado por lint anti-prompt-injection) */
        public ?string $injuries,

        /** Restricciones dietarias (vegano, sin gluten, etc.) */
        public ?string $dietaryRestrictions,

        /** admin_id del coach asignado. Default Anderson Ardila = 7 */
        public ?int $coachId,

        /** Fecha de inicio del plan en formato ISO (YYYY-MM-DD) */
        public string $validFrom,

        /** Duración del plan en semanas. Default según plan tier (esencial=4, metodo=12, elite=12, etc.) */
        public ?int $durationWeeks,
    ) {
        $this->assertEnum($vertical, ['entrenamiento', 'nutricion', 'suplementacion', 'habitos', 'ciclo'], 'vertical');
        $this->assertEnum($gender, ['M', 'F'], 'gender');
        $this->assertEnum($goal, ['hipertrofia', 'perdida_grasa', 'recomposicion', 'mantenimiento', 'performance'], 'goal');
        $this->assertEnum($level, ['principiante', 'intermedio', 'avanzado'], 'level');
        $this->assertEnum($place, ['gym', 'casa', 'hibrido'], 'place');

        if ($daysAvailable < 3 || $daysAvailable > 7) {
            throw new \InvalidArgumentException("daysAvailable debe estar entre 3 y 7, recibido: $daysAvailable");
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $validFrom)) {
            throw new \InvalidArgumentException("validFrom debe ser ISO date YYYY-MM-DD, recibido: $validFrom");
        }
    }

    private function assertEnum(string $value, array $allowed, string $fieldName): void
    {
        if (! in_array($value, $allowed, true)) {
            $allowedStr = implode(', ', $allowed);
            throw new \InvalidArgumentException("$fieldName inválido: '$value'. Permitidos: $allowedStr");
        }
    }

    /**
     * Snapshot serializable para guardar en plan_engine_runs.intake_dto_json
     * (reproducibilidad — el mismo input puede regenerar el mismo plan).
     */
    public function toJson(): string
    {
        return json_encode([
            'client_id' => $this->clientId,
            'vertical' => $this->vertical,
            'client_name' => $this->clientName,
            'gender' => $this->gender,
            'age' => $this->age,
            'weight_kg' => $this->weightKg,
            'height_cm' => $this->heightCm,
            'goal' => $this->goal,
            'level' => $this->level,
            'days_available' => $this->daysAvailable,
            'place' => $this->place,
            'equipment' => $this->equipment,
            'injuries' => $this->injuries,
            'dietary_restrictions' => $this->dietaryRestrictions,
            'coach_id' => $this->coachId,
            'valid_from' => $this->validFrom,
            'duration_weeks' => $this->durationWeeks,
        ], JSON_UNESCAPED_UNICODE);
    }
}
