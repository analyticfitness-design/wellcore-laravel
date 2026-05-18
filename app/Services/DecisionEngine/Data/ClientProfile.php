<?php

declare(strict_types=1);

namespace App\Services\DecisionEngine\Data;

/**
 * Snapshot inmutable del intake de un cliente para Stage 2 SELECT del motor v2.
 *
 * Los campos opcionales pueden estar null si el intake no los recolectó (ej.
 * suplementación no necesita days). El WhenMatcher solo evalúa keys presentes
 * en el `when_json` de una rule — keys ausentes en el profile = no-match.
 */
final readonly class ClientProfile
{
    public function __construct(
        public string $vertical,
        public ?string $goal = null,
        public ?string $level = null,
        public ?int $days = null,
        public ?string $gender = null,
        public ?string $equipment = null,
        public ?int $age = null,
        public ?float $weightKg = null,
        public ?float $heightCm = null,
        public ?string $tier = null,
        /** @var string[] */
        public array $injuries = [],
        /** @var string[] */
        public array $preferences = [],
    ) {
    }

    /**
     * Convierte a array asociativo en snake_case para matching contra when_json.
     */
    public function toArray(): array
    {
        return array_filter([
            'vertical' => $this->vertical,
            'goal' => $this->goal,
            'level' => $this->level,
            'days' => $this->days,
            'gender' => $this->gender,
            'equipment' => $this->equipment,
            'age' => $this->age,
            'weight_kg' => $this->weightKg,
            'height_cm' => $this->heightCm,
            'tier' => $this->tier,
            'injuries' => $this->injuries === [] ? null : $this->injuries,
            'preferences' => $this->preferences === [] ? null : $this->preferences,
        ], fn ($v) => $v !== null);
    }

    /**
     * Constructor desde array (útil para cargar desde JSON/CLI).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            vertical: (string) ($data['vertical'] ?? ''),
            goal: $data['goal'] ?? null,
            level: $data['level'] ?? null,
            days: isset($data['days']) ? (int) $data['days'] : null,
            gender: $data['gender'] ?? null,
            equipment: $data['equipment'] ?? null,
            age: isset($data['age']) ? (int) $data['age'] : null,
            weightKg: isset($data['weight_kg']) ? (float) $data['weight_kg'] : null,
            heightCm: isset($data['height_cm']) ? (float) $data['height_cm'] : null,
            tier: $data['tier'] ?? null,
            injuries: is_array($data['injuries'] ?? null) ? $data['injuries'] : [],
            preferences: is_array($data['preferences'] ?? null) ? $data['preferences'] : [],
        );
    }
}
