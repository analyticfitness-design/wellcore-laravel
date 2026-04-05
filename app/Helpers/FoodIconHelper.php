<?php

namespace App\Helpers;

class FoodIconHelper
{
    /**
     * Food keyword → emoji icon mapping.
     * Order matters: more specific keywords first to avoid partial matches.
     */
    private static array $map = [
        // Proteínas animales
        'pollo'         => '🍗',
        'pechuga'       => '🍗',
        'chicken'       => '🍗',
        'pavo'          => '🍗',
        'carne'         => '🥩',
        'res'           => '🥩',
        'steak'         => '🥩',
        'lomo'          => '🥩',
        'cerdo'         => '🥩',
        'cordero'       => '🥩',
        'salmón'        => '🐟',
        'salmon'        => '🐟',
        'atún'          => '🐟',
        'atun'          => '🐟',
        'tilapia'       => '🐟',
        'corvina'       => '🐟',
        'trucha'        => '🐟',
        'pescado'       => '🐟',
        'camarón'       => '🦐',
        'camaron'       => '🦐',
        'mariscos'      => '🦐',

        // Huevos y lácteos
        'huevo'         => '🥚',
        'clara'         => '🥚',
        'claras'        => '🥚',
        'yogur'         => '🥛',
        'yogurt'        => '🥛',
        'leche'         => '🥛',
        'requesón'      => '🧀',
        'requeson'      => '🧀',
        'queso'         => '🧀',

        // Cereales y carbohidratos
        'avena'         => '🥣',
        'oatmeal'       => '🥣',
        'granola'       => '🥣',
        'arroz'         => '🍚',
        'rice'          => '🍚',
        'quinoa'        => '🍚',
        'pasta'         => '🍝',
        'pan'           => '🍞',
        'tostada'       => '🍞',
        'arepa'         => '🫓',
        'tortilla'      => '🫓',
        'papa'          => '🥔',
        'batata'        => '🍠',
        'camote'        => '🍠',
        'lenteja'       => '🫘',
        'frijol'        => '🫘',

        // Frutas
        'banana'        => '🍌',
        'banano'        => '🍌',
        'plátano'       => '🍌',
        'platano'       => '🍌',
        'manzana'       => '🍎',
        'fresa'         => '🍓',
        'fresas'        => '🍓',
        'arándano'      => '🫐',
        'arandano'      => '🫐',
        'fruta'         => '🍇',
        'frutas'        => '🍇',
        'dátil'         => '🫘',
        'datil'         => '🫘',
        'naranja'       => '🍊',
        'jugo'          => '🧃',

        // Vegetales
        'brócoli'       => '🥦',
        'brocoli'       => '🥦',
        'espinaca'      => '🥬',
        'lechuga'       => '🥬',
        'ensalada'      => '🥗',
        'vegetal'       => '🥗',
        'vegetales'     => '🥗',
        'tomate'        => '🍅',
        'zanahoria'     => '🥕',
        'espárrago'     => '🌿',
        'esparrago'     => '🌿',

        // Grasas saludables
        'aguacate'      => '🥑',
        'avocado'       => '🥑',
        'nuez'          => '🥜',
        'nueces'        => '🥜',
        'almendra'      => '🥜',
        'maní'          => '🥜',
        'mani'          => '🥜',
        'mantequilla de maní' => '🥜',
        'aceite'        => '🫒',
        'oliva'         => '🫒',
        'semilla'       => '🌻',
        'chía'          => '🌻',
        'chia'          => '🌻',

        // Suplementos y bebidas
        'proteína'      => '🧪',
        'proteina'      => '🧪',
        'whey'          => '🧪',
        'creatina'      => '💊',
        'suplemento'    => '💊',
        'café'          => '☕',
        'cafe'          => '☕',
        'agua'          => '💧',
        'miel'          => '🍯',
        'chocolate'     => '🍫',
        'mermelada'     => '🍯',

        // Genéricos
        'snack'         => '🍿',
        'torta de arroz' => '🍘',
    ];

    /**
     * Get the emoji icon for a food name.
     */
    public static function icon(string $foodName): string
    {
        $lower = mb_strtolower($foodName);

        foreach (self::$map as $keyword => $emoji) {
            if (str_contains($lower, $keyword)) {
                return $emoji;
            }
        }

        return '•'; // default bullet
    }

    /**
     * Get icon with fallback CSS class for the bullet dot.
     * Returns array ['icon' => emoji, 'isEmoji' => bool]
     */
    public static function resolve(string $foodName): array
    {
        $icon = self::icon($foodName);
        return [
            'icon' => $icon,
            'isEmoji' => $icon !== '•',
        ];
    }
}
