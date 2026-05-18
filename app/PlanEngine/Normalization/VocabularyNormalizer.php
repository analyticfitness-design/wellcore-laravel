<?php

declare(strict_types=1);

namespace App\PlanEngine\Normalization;

/**
 * Normaliza vocabulario externo (UI, ticket JSON, inscripción) a las llaves canónicas
 * del motor v2 que usan `decision_rules.when_json` y `methodologies.target_*`.
 *
 * Bug histórico (Karen Vanessa #8): el JSON del ticket exponía `objetivo_general: "perder_grasa"`
 * pero la BD usa `perdida_grasa`. El DecisionEngine matcheaba 0/19 reglas y rechazaba el plan.
 *
 * Toda fuente que provea valores de goal/level/gender/place/equipment debe pasar primero
 * por estos normalizadores ANTES de construir ClientProfile o IntakeDto.
 *
 * Si una clave no tiene mapping conocido, devuelve el valor original tal cual — el DTO
 * downstream rechazará el enum y la falla saldrá con mensaje claro.
 */
final class VocabularyNormalizer
{
    /** Aliases del campo `goal` → valor canónico del motor. */
    private const GOAL_ALIASES = [
        // Pérdida de grasa
        'perder_grasa'         => 'perdida_grasa',
        'perdida_de_grasa'     => 'perdida_grasa',
        'perder_peso'          => 'perdida_grasa',
        'bajar_grasa'          => 'perdida_grasa',
        'definicion'           => 'perdida_grasa',
        'definir'              => 'perdida_grasa',
        'cutting'              => 'perdida_grasa',
        'corte'                => 'perdida_grasa',
        'disminuir_porcentaje_de_grasa' => 'perdida_grasa',

        // Hipertrofia
        'ganar_masa'           => 'hipertrofia',
        'ganar_musculo'        => 'hipertrofia',
        'aumentar_masa'        => 'hipertrofia',
        'volumen'              => 'hipertrofia',
        'bulking'              => 'hipertrofia',
        'masa_muscular'        => 'hipertrofia',

        // Recomposición
        'recomp'               => 'recomposicion',
        'recomposicion_corporal' => 'recomposicion',

        // Mantenimiento
        'mantener'             => 'mantenimiento',
        'mantener_peso'        => 'mantenimiento',
        'estancarse'           => 'mantenimiento',

        // Performance
        'rendimiento'          => 'performance',
        'fuerza'               => 'performance',
    ];

    /** Aliases del campo `gender`. Devuelve 'M' o 'F' canónico. */
    private const GENDER_ALIASES = [
        // F
        'femenino'  => 'F',
        'female'    => 'F',
        'mujer'     => 'F',
        'f'         => 'F',
        // M
        'masculino' => 'M',
        'male'      => 'M',
        'hombre'    => 'M',
        'm'         => 'M',
    ];

    /** Aliases del campo `level`. */
    private const LEVEL_ALIASES = [
        'principiante'   => 'principiante',
        'beginner'       => 'principiante',
        'inicial'        => 'principiante',
        'novato'         => 'principiante',

        'intermedio'     => 'intermedio',
        'intermediate'   => 'intermedio',
        'medio'          => 'intermedio',

        'avanzado'       => 'avanzado',
        'advanced'       => 'avanzado',
        'experto'        => 'avanzado',
        'elite'          => 'avanzado',
    ];

    /** Aliases del campo `place`. */
    private const PLACE_ALIASES = [
        'gym'           => 'gym',
        'gimnasio'      => 'gym',
        'comercial'     => 'gym',

        'casa'          => 'casa',
        'home'          => 'casa',
        'domicilio'     => 'casa',

        'hibrido'       => 'hibrido',
        'mixto'         => 'hibrido',
        'hybrid'        => 'hibrido',
    ];

    public static function goal(?string $raw): ?string
    {
        return self::lookup($raw, self::GOAL_ALIASES);
    }

    public static function gender(?string $raw): ?string
    {
        return self::lookup($raw, self::GENDER_ALIASES);
    }

    public static function level(?string $raw): ?string
    {
        return self::lookup($raw, self::LEVEL_ALIASES);
    }

    public static function place(?string $raw): ?string
    {
        return self::lookup($raw, self::PLACE_ALIASES);
    }

    /**
     * Lookup case-insensitive y tolerante a espacios/acentos.
     * Si no hay mapping, devuelve el valor normalizado (lower + sin acentos + underscored)
     * para que el enum downstream falle con un mensaje predecible.
     */
    private static function lookup(?string $raw, array $aliases): ?string
    {
        if ($raw === null) {
            return null;
        }

        $normalized = self::canonicalKey($raw);

        if ($normalized === '') {
            return null;
        }

        return $aliases[$normalized] ?? $normalized;
    }

    /**
     * Reduce un string a su clave canónica: lower + sin tildes + espacios/guiones → underscore.
     */
    private static function canonicalKey(string $raw): string
    {
        $s = mb_strtolower(trim($raw), 'UTF-8');
        $s = strtr($s, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'ü' => 'u',
        ]);
        $s = (string) preg_replace('/[\s\-]+/u', '_', $s);
        $s = (string) preg_replace('/[^a-z0-9_]/u', '', $s);
        return $s;
    }
}
