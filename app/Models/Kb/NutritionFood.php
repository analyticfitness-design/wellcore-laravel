<?php

declare(strict_types=1);

namespace App\Models\Kb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * wellcore_kb.nutrition_foods — catálogo curado de alimentos crudos.
 *
 * Macros por 100g crudo en columnas escalares (protein_g, carbs_g, fat_g, kcal).
 * Usado por FoodSelector (Sprint 7) para componer comidas del plan nutricional.
 */
class NutritionFood extends Model
{
    protected $connection = 'kb';
    protected $table = 'nutrition_foods';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'name_alternatives' => 'array',
            'unit_options' => 'array',
            'portion_typical' => 'array',
            'availability_country' => 'array',
            'alternatives_protein_equivalent' => 'array',
            'scientific_sources' => 'array',
            'tags' => 'array',
            'raw_data' => 'array',
            'protein_g' => 'float',
            'carbs_g' => 'float',
            'fat_g' => 'float',
            'fiber_g' => 'float',
            'kcal' => 'float',
            'is_vegetarian' => 'boolean',
            'is_vegan' => 'boolean',
            'is_gluten_free' => 'boolean',
            'is_lactose_free' => 'boolean',
            'is_keto_friendly' => 'boolean',
            'is_paleo_friendly' => 'boolean',
            'needs_daniel_validation' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('active', true);
    }

    public function scopeOfCategory(Builder $q, string $category): Builder
    {
        return $q->where('category', $category);
    }

    /**
     * Filtra por restricciones dietarias (vegetariano, vegano, etc.).
     * @param string[] $restrictions
     */
    public function scopeRespectingRestrictions(Builder $q, array $restrictions): Builder
    {
        foreach ($restrictions as $r) {
            if (! is_string($r)) {
                continue; // ignora claves estructuradas como 'excluded_foods'
            }
            match ($r) {
                'vegano' => $q->where('is_vegan', true),
                'vegetariano' => $q->where('is_vegetarian', true),
                'sin_gluten' => $q->where('is_gluten_free', true),
                'sin_lactosa' => $q->where('is_lactose_free', true),
                'keto' => $q->where('is_keto_friendly', true),
                'paleo' => $q->where('is_paleo_friendly', true),
                default => null,
            };
        }
        return $q;
    }

    /**
     * Excluye alimentos por slug O substring de nombre (case-insensitive).
     * Tolerante a plurales/acentos/typos: 'huevos' → busca 'huev', 'arándanos' → 'arandano'.
     *
     * @param string[] $excluded
     */
    public function scopeExcludingFoods(Builder $q, array $excluded): Builder
    {
        if ($excluded === []) {
            return $q;
        }
        return $q->where(function (Builder $sub) use ($excluded) {
            foreach ($excluded as $needle) {
                foreach (self::keywordVariants((string) $needle) as $variant) {
                    $sub->where('slug', 'not like', "%$variant%")
                        ->where('name', 'not like', "%$variant%");
                }
            }
        });
    }

    /**
     * Filtra por keyword del nombre (case-insensitive). Útil para preferencia de proteína:
     * ej. 'huevos' encuentra "Huevo entero" + "Claras de huevo".
     */
    public function scopeMatchingKeyword(Builder $q, string $keyword): Builder
    {
        $variants = self::keywordVariants($keyword);
        if ($variants === []) {
            return $q;
        }
        return $q->where(function (Builder $sub) use ($variants) {
            foreach ($variants as $v) {
                $sub->orWhere('slug', 'like', "%$v%")
                    ->orWhere('name', 'like', "%$v%");
            }
        });
    }

    /**
     * Genera variantes léxicas de una keyword para tolerar plurales/acentos/typos:
     *   - original normalizado (lowercase + trim)
     *   - sin tildes
     *   - sin 's' final (plural)
     *   - raíz de 5 chars (para typos cortos)
     *
     * @return string[]
     */
    private static function keywordVariants(string $kw): array
    {
        $kw = mb_strtolower(trim($kw));
        if ($kw === '') {
            return [];
        }
        $variants = [$kw];

        // Sin tildes
        $noAccent = strtr($kw, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
        ]);
        if ($noAccent !== $kw) {
            $variants[] = $noAccent;
        }

        // Sin 's' final (plurales)
        foreach ($variants as $v) {
            if (mb_strlen($v) > 3 && str_ends_with($v, 's')) {
                $variants[] = mb_substr($v, 0, -1);
            }
        }

        // Raíces cortas para tolerar typos en medio de palabra:
        // 'zuccini' (typo) → 'zucc' matches 'zucchini' (catálogo)
        // 'huevos' → 'huev' matches 'Huevo entero' + 'Claras de huevo' (vía nombre 'huevo')
        if (mb_strlen($noAccent) >= 6) {
            $variants[] = mb_substr($noAccent, 0, 4);
        }
        if (mb_strlen($noAccent) >= 8) {
            $variants[] = mb_substr($noAccent, 0, 5);
        }

        return array_values(array_unique($variants));
    }
}
