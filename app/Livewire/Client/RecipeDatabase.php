<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Recetas — WellCore'])]
class RecipeDatabase extends Component
{
    public string $search = '';
    public string $categoryFilter = '';
    public string $goalFilter = '';
    public ?string $selectedRecipeId = null;

    /**
     * Static high-quality fitness recipe dataset.
     * No DB model for recipes — data lives here and is filtered client-side via Alpine.js.
     * The full dataset and filtering logic is in the Alpine component in the blade view.
     */
    private function getRecipes(): array
    {
        return [
            [
                'id'          => 'rec-001',
                'title'       => 'Bowl de Proteina con Arroz y Pollo',
                'category'    => 'almuerzo',
                'goal'        => ['volumen', 'mantenimiento'],
                'calories'    => 520,
                'protein'     => 45,
                'carbs'       => 55,
                'fat'         => 10,
                'prep_min'    => 20,
                'ingredients' => [
                    '150g pechuga de pollo',
                    '100g arroz integral cocido',
                    '1 taza espinaca',
                    '½ aguacate',
                    'Salsa de soya baja en sodio',
                ],
                'steps' => [
                    'Cocina el pollo a la plancha con especias',
                    'Mezcla el arroz con la espinaca',
                    'Corta el aguacate en rodajas',
                    'Arma el bowl y agrega la salsa',
                ],
                'emoji' => '🍗',
            ],
            [
                'id'          => 'rec-002',
                'title'       => 'Pancakes de Avena con Claras',
                'category'    => 'desayuno',
                'goal'        => ['volumen', 'mantenimiento'],
                'calories'    => 380,
                'protein'     => 28,
                'carbs'       => 45,
                'fat'         => 8,
                'prep_min'    => 15,
                'ingredients' => [
                    '6 claras de huevo',
                    '80g avena en hojuelas',
                    '1 banana madura',
                    '1 cdta extracto de vainilla',
                    'Canela al gusto',
                ],
                'steps' => [
                    'Licua todos los ingredientes hasta obtener mezcla uniforme',
                    'Calienta sarten antiadherente a fuego medio',
                    'Vierte porciones de la mezcla',
                    'Cocina 2-3 min por lado hasta dorar',
                ],
                'emoji' => '🥞',
            ],
            [
                'id'          => 'rec-003',
                'title'       => 'Ensalada de Atun con Aguacate',
                'category'    => 'almuerzo',
                'goal'        => ['definicion'],
                'calories'    => 320,
                'protein'     => 35,
                'carbs'       => 12,
                'fat'         => 16,
                'prep_min'    => 10,
                'ingredients' => [
                    '1 lata atun en agua',
                    '½ aguacate',
                    '1 taza lechuga romana',
                    'Cherry tomatoes',
                    'Limon y aceite de oliva',
                ],
                'steps' => [
                    'Escurre el atun',
                    'Corta el aguacate en cubos',
                    'Mezcla todos los ingredientes',
                    'Alina con limon y aceite',
                ],
                'emoji' => '🥗',
            ],
            [
                'id'          => 'rec-004',
                'title'       => 'Batido Proteico de Chocolate',
                'category'    => 'snack',
                'goal'        => ['volumen', 'mantenimiento', 'definicion'],
                'calories'    => 280,
                'protein'     => 30,
                'carbs'       => 25,
                'fat'         => 5,
                'prep_min'    => 5,
                'ingredients' => [
                    '30g proteina en polvo chocolate',
                    '250ml leche de almendras',
                    '1 banana congelada',
                    '1 cdta cacao puro',
                    'Hielo al gusto',
                ],
                'steps' => [
                    'Anade todos los ingredientes a la licuadora',
                    'Licua hasta obtener consistencia suave',
                    'Sirve inmediatamente',
                ],
                'emoji' => '🥤',
            ],
            [
                'id'          => 'rec-005',
                'title'       => 'Salmon al Horno con Vegetales',
                'category'    => 'cena',
                'goal'        => ['definicion', 'mantenimiento'],
                'calories'    => 420,
                'protein'     => 42,
                'carbs'       => 15,
                'fat'         => 22,
                'prep_min'    => 30,
                'ingredients' => [
                    '180g filete de salmon',
                    '1 taza brocoli',
                    '1 zanahoria',
                    'Esparragos',
                    'Aceite de oliva, ajo, limon',
                ],
                'steps' => [
                    'Precalienta horno a 200°C',
                    'Sazona el salmon con ajo, limon y hierbas',
                    'Arregla los vegetales alrededor',
                    'Hornea 20-25 minutos',
                ],
                'emoji' => '🍣',
            ],
            [
                'id'          => 'rec-006',
                'title'       => 'Huevos Revueltos con Verduras',
                'category'    => 'desayuno',
                'goal'        => ['definicion', 'mantenimiento', 'volumen'],
                'calories'    => 290,
                'protein'     => 24,
                'carbs'       => 8,
                'fat'         => 18,
                'prep_min'    => 10,
                'ingredients' => [
                    '3 huevos enteros',
                    '2 claras extra',
                    'Pimiento rojo',
                    'Espinaca',
                    'Cebolla',
                    'Sal y pimienta',
                ],
                'steps' => [
                    'Saltea los vegetales 3 min',
                    'Bate los huevos con las claras',
                    'Agrega los huevos a la sarten',
                    'Cocina a fuego bajo revolviendo suavemente',
                ],
                'emoji' => '🍳',
            ],
        ];
    }

    public function selectRecipe(string $id): void
    {
        $this->selectedRecipeId = $this->selectedRecipeId === $id ? null : $id;
    }

    public function render()
    {
        $recipes = collect($this->getRecipes());

        if ($this->search) {
            $recipes = $recipes->filter(
                fn ($r) => str_contains(strtolower($r['title']), strtolower($this->search))
            );
        }

        if ($this->categoryFilter) {
            $recipes = $recipes->filter(fn ($r) => $r['category'] === $this->categoryFilter);
        }

        if ($this->goalFilter) {
            $recipes = $recipes->filter(fn ($r) => in_array($this->goalFilter, $r['goal']));
        }

        $selected = $this->selectedRecipeId
            ? $recipes->firstWhere('id', $this->selectedRecipeId)
            : null;

        return view('livewire.client.recipe-database', [
            'recipes'  => $recipes->values(),
            'selected' => $selected,
        ]);
    }
}
