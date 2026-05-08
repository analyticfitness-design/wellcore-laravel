# Lista de Mercado — GroceryDrawer Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Agregar un drawer "Lista de Mercado" al Tab de Nutrición que genera automáticamente una lista de compras agrupada por categoría (vista semanal) o por comida (vista por comida), con una Guía de Compra general al final.

**Architecture:** Pure frontend — el `nutritionPlan` ya llega completo al cliente como prop de `NutritionTab.vue`. Un composable `useGroceryList` extrae todos los alimentos, los clasifica por categoría usando keyword matching, y retorna dos vistas: `byCategory` (lista completa agrupada) y `byMeal` (filtrada por comida). Un nuevo componente `GroceryDrawer.vue` renderiza el bottom-sheet con toggle y guía. Sin nuevo endpoint ni lógica de backend.

**Tech Stack:** Vue 3 Composition API, lucide-vue-next (ShoppingCart, X, ChevronDown), Tailwind CSS 4, tokens WellCore (wc-bg-secondary, wc-border, wc-accent)

---

## Mapa de archivos

| Archivo | Acción | Responsabilidad |
|---------|--------|-----------------|
| `resources/js/vue/composables/useGroceryList.js` | **Crear** | Extrae alimentos del nutritionPlan, agrupa por categoría y por comida |
| `resources/js/vue/components/plan/nutrition/GroceryDrawer.vue` | **Crear** | Bottom-sheet drawer con toggle, lista, y guía de compra |
| `resources/js/vue/components/plan/nutrition/NutritionTab.vue` | **Modificar** | Agrega botón "Lista de mercado" y monta GroceryDrawer |

---

## Task 1: Composable `useGroceryList.js`

**Files:**
- Create: `resources/js/vue/composables/useGroceryList.js`

- [ ] **Step 1.1: Crear el archivo del composable**

Crear `resources/js/vue/composables/useGroceryList.js` con este contenido completo:

```js
import { computed } from 'vue';

// Categorías de alimentos — keywords en minúsculas sin tildes (para matching robusto).
// El orden determina prioridad: un alimento matchea la primera categoría que encaje.
const CATEGORIES = [
  {
    key: 'proteinas',
    label: 'Proteínas',
    emoji: '🥩',
    keywords: [
      'pollo', 'pechuga', 'pavo', 'carne', 'res', 'steak', 'lomo', 'cerdo',
      'salmon', 'atun', 'tilapia', 'pescado', 'corvina', 'trucha',
      'huevo', 'clara', 'yogur', 'yogurt', 'queso cottage', 'proteina', 'whey',
    ],
  },
  {
    key: 'carbohidratos',
    label: 'Carbohidratos',
    emoji: '🍚',
    keywords: [
      'arroz', 'avena', 'quinoa', 'pasta', 'pan', 'tostada', 'arepa', 'tortilla',
      'papa', 'batata', 'camote', 'banano', 'platano', 'banana', 'granola',
      'frijol', 'lenteja', 'maiz', 'yuca',
    ],
  },
  {
    key: 'grasas',
    label: 'Grasas saludables',
    emoji: '🥑',
    keywords: [
      'aguacate', 'avocado', 'nuez', 'nueces', 'almendra', 'mani', 'mani',
      'aceite', 'oliva', 'chia', 'linaza', 'coco', 'mantequilla',
    ],
  },
  {
    key: 'verduras',
    label: 'Verduras y frutas',
    emoji: '🥦',
    keywords: [
      'brocoli', 'espinaca', 'lechuga', 'tomate', 'pepino', 'zanahoria',
      'ensalada', 'vegetal', 'verdura', 'manzana', 'fresa', 'fruta',
      'mango', 'naranja', 'maracuya', 'jugo',
    ],
  },
];

const OTROS = { key: 'otros', label: 'Otros', emoji: '🛒' };

// Normaliza string: minúsculas, sin tildes, sin caracteres especiales
function normalize(str) {
  return (str || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .trim();
}

// Clasifica un nombre de alimento en una categoría
function classify(name) {
  const n = normalize(name);
  for (const cat of CATEGORIES) {
    if (cat.keywords.some((kw) => n.includes(kw))) return cat;
  }
  return OTROS;
}

// Extrae el nombre y cantidad de un alimento (string u objeto)
function parseFood(food) {
  if (!food) return null;
  if (typeof food === 'string') {
    const trimmed = food.trim();
    if (!trimmed) return null;
    return { name: trimmed, qty: '' };
  }
  if (typeof food === 'object') {
    const name = (food.nombre || food.alimento || food.name || '').trim();
    if (!name) return null;
    const qty = (food.cantidad || food.porcion || food.quantity || food.amount || '').toString().trim();
    return { name, qty };
  }
  return null;
}

// Extrae todos los alimentos de una comida (soporta alimentos directos y opciones A/B)
function extractFoodsFromMeal(meal) {
  const items = [];
  const mealLabel = meal.nombre || meal.name || '';

  // Alimentos directos
  const directFoods = meal.alimentos || meal.foods || meal.ingredientes || [];
  for (const food of directFoods) {
    const parsed = parseFood(food);
    if (parsed) items.push({ ...parsed, meal: mealLabel });
  }

  // Opciones A/B
  const opciones = meal.opciones || meal.options || {};
  for (const [optKey, optFoods] of Object.entries(opciones)) {
    if (!Array.isArray(optFoods)) continue;
    for (const food of optFoods) {
      const parsed = parseFood(food);
      if (parsed) {
        items.push({
          name: parsed.name + ` (Opción ${optKey.toUpperCase()})`,
          qty: parsed.qty,
          meal: mealLabel,
        });
      }
    }
  }

  return items;
}

/**
 * useGroceryList — extrae y agrupa alimentos del nutritionPlan.
 *
 * @param {import('vue').Ref|import('vue').ComputedRef} nutritionPlanRef
 * @returns {{ byCategory: ComputedRef, byMeal: ComputedRef }}
 */
export function useGroceryList(nutritionPlanRef) {
  // Todos los alimentos planos con su comida de origen
  const allItems = computed(() => {
    const plan = nutritionPlanRef.value;
    if (!plan) return [];
    const meals = plan.comidas || plan.comidas_sugeridas || [];
    return meals.flatMap(extractFoodsFromMeal);
  });

  // Vista semanal: agrupado por categoría
  const byCategory = computed(() => {
    const groups = new Map();

    // Inicializar orden de categorías
    for (const cat of [...CATEGORIES, OTROS]) {
      groups.set(cat.key, { ...cat, items: [] });
    }

    for (const item of allItems.value) {
      const cat = classify(item.name);
      groups.get(cat.key).items.push(item);
    }

    // Retornar solo categorías con ítems
    return [...groups.values()].filter((g) => g.items.length > 0);
  });

  // Vista por comida: una entrada por comida con sus alimentos
  const byMeal = computed(() => {
    const plan = nutritionPlanRef.value;
    if (!plan) return [];
    const meals = plan.comidas || plan.comidas_sugeridas || [];
    return meals
      .map((meal) => ({
        label: meal.nombre || meal.name || 'Comida',
        hora: meal.hora || meal.time || '',
        items: extractFoodsFromMeal(meal),
      }))
      .filter((m) => m.items.length > 0);
  });

  return { byCategory, byMeal };
}
```

- [ ] **Step 1.2: Commit del composable**

```bash
git add resources/js/vue/composables/useGroceryList.js
git commit -m "feat(nutrition): add useGroceryList composable — extrae y agrupa alimentos del plan"
```

---

## Task 2: Componente `GroceryDrawer.vue`

**Files:**
- Create: `resources/js/vue/components/plan/nutrition/GroceryDrawer.vue`

- [ ] **Step 2.1: Crear el componente completo**

Crear `resources/js/vue/components/plan/nutrition/GroceryDrawer.vue`:

```vue
<template>
  <!-- Overlay backdrop -->
  <Teleport to="body">
    <Transition name="grocery-fade">
      <div
        v-if="open"
        class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm"
        @click="$emit('close')"
      />
    </Transition>

    <!-- Drawer bottom-sheet -->
    <Transition name="grocery-slide">
      <div
        v-if="open"
        class="fixed bottom-0 left-0 right-0 z-50 flex max-h-[90dvh] flex-col rounded-t-2xl border-t border-wc-border bg-wc-bg-secondary shadow-2xl"
      >
        <!-- Drag handle -->
        <div class="flex justify-center pt-3 pb-1 shrink-0">
          <div class="h-1 w-10 rounded-full bg-wc-border"></div>
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-3 shrink-0 border-b border-wc-border">
          <div class="flex items-center gap-2.5">
            <ShoppingCart :size="18" class="text-wc-accent shrink-0" />
            <h2 class="font-display text-sm tracking-widest uppercase text-wc-text">
              Lista de mercado
            </h2>
          </div>
          <button
            type="button"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-tertiary hover:text-wc-text hover:bg-wc-bg-tertiary transition-colors"
            @click="$emit('close')"
          >
            <X :size="18" />
          </button>
        </div>

        <!-- Toggle semanal / por comida -->
        <div class="px-5 pt-4 pb-3 shrink-0">
          <div class="flex rounded-lg border border-wc-border bg-wc-bg-tertiary p-1 gap-1">
            <button
              type="button"
              class="flex-1 rounded-md py-2 text-xs font-semibold transition-colors"
              :class="view === 'category'
                ? 'bg-wc-accent text-white shadow-sm'
                : 'text-wc-text-secondary hover:text-wc-text'"
              @click="view = 'category'"
            >
              Todo el plan
            </button>
            <button
              type="button"
              class="flex-1 rounded-md py-2 text-xs font-semibold transition-colors"
              :class="view === 'meal'
                ? 'bg-wc-accent text-white shadow-sm'
                : 'text-wc-text-secondary hover:text-wc-text'"
              @click="view = 'meal'"
            >
              Por comida
            </button>
          </div>
        </div>

        <!-- Contenido scrollable -->
        <div class="overflow-y-auto flex-1 px-5 pb-6">

          <!-- Empty state -->
          <div
            v-if="isEmpty"
            class="py-12 text-center"
          >
            <p class="text-sm text-wc-text-tertiary">Tu coach está preparando el plan de nutrición.</p>
          </div>

          <!-- Vista: Todo el plan (por categoría) -->
          <template v-else-if="view === 'category'">
            <div
              v-for="group in byCategory"
              :key="group.key"
              class="mb-5"
            >
              <div class="mb-2 flex items-center gap-2">
                <span class="text-base leading-none">{{ group.emoji }}</span>
                <p class="font-display text-[10px] tracking-[0.18em] uppercase text-wc-text-secondary">
                  {{ group.label }}
                </p>
              </div>
              <ul class="rounded-xl border border-wc-border bg-wc-bg-tertiary divide-y divide-wc-border/50">
                <li
                  v-for="(item, idx) in group.items"
                  :key="idx"
                  class="grid grid-cols-[auto_1fr] gap-x-3 px-4 py-2.5"
                >
                  <span class="font-data text-[11px] text-wc-text-tertiary tabular-nums whitespace-nowrap text-right min-w-[56px] pt-0.5">
                    {{ item.qty || '·' }}
                  </span>
                  <div class="min-w-0">
                    <p class="text-sm text-wc-text leading-snug">{{ item.name }}</p>
                    <p v-if="item.meal" class="text-[10px] text-wc-text-tertiary mt-0.5">{{ item.meal }}</p>
                  </div>
                </li>
              </ul>
            </div>
          </template>

          <!-- Vista: Por comida -->
          <template v-else>
            <!-- Selector de comida -->
            <div class="flex gap-2 overflow-x-auto pb-3 -mx-5 px-5 no-scrollbar">
              <button
                v-for="(meal, idx) in byMeal"
                :key="idx"
                type="button"
                class="shrink-0 rounded-full border px-4 py-2 text-xs font-semibold transition-colors whitespace-nowrap"
                :class="selectedMealIdx === idx
                  ? 'border-wc-accent bg-wc-accent/10 text-wc-accent'
                  : 'border-wc-border text-wc-text-secondary hover:text-wc-text'"
                @click="selectedMealIdx = idx"
              >
                {{ meal.label }}
              </button>
            </div>

            <!-- Alimentos de la comida seleccionada -->
            <div v-if="selectedMeal">
              <div class="mb-3 flex items-baseline gap-2">
                <p class="font-display text-sm tracking-wide text-wc-text uppercase">{{ selectedMeal.label }}</p>
                <p v-if="selectedMeal.hora" class="font-data text-[11px] text-wc-text-tertiary">{{ selectedMeal.hora }}</p>
              </div>
              <ul class="rounded-xl border border-wc-border bg-wc-bg-tertiary divide-y divide-wc-border/50">
                <li
                  v-for="(item, idx) in selectedMeal.items"
                  :key="idx"
                  class="grid grid-cols-[auto_1fr] gap-x-3 px-4 py-2.5"
                >
                  <span class="font-data text-[11px] text-wc-text-tertiary tabular-nums whitespace-nowrap text-right min-w-[56px] pt-0.5">
                    {{ item.qty || '·' }}
                  </span>
                  <p class="text-sm text-wc-text leading-snug">{{ item.name }}</p>
                </li>
              </ul>
            </div>
          </template>

          <!-- Guía de compra — siempre visible al final -->
          <div class="mt-6 rounded-xl border border-wc-border/60 bg-wc-bg-tertiary/60 p-4">
            <p class="font-display text-[10px] tracking-[0.2em] uppercase text-wc-text-secondary mb-3">
              📋 Guía de compra
            </p>
            <ul class="space-y-2.5">
              <li
                v-for="(tip, i) in SHOPPING_TIPS"
                :key="i"
                class="flex items-start gap-2.5"
              >
                <span class="mt-0.5 h-1.5 w-1.5 shrink-0 rounded-full bg-wc-accent/60"></span>
                <p class="text-xs leading-relaxed text-wc-text-secondary">{{ tip }}</p>
              </li>
            </ul>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { ShoppingCart, X } from 'lucide-vue-next';
import { useGroceryList } from '@/composables/useGroceryList';

const props = defineProps({
  open: { type: Boolean, required: true },
  nutritionPlan: { type: Object, default: null },
});

defineEmits(['close']);

const view = ref('category');
const selectedMealIdx = ref(0);

const nutritionPlanRef = computed(() => props.nutritionPlan);
const { byCategory, byMeal } = useGroceryList(nutritionPlanRef);

const isEmpty = computed(() => byCategory.value.length === 0 && byMeal.value.length === 0);

const selectedMeal = computed(() => byMeal.value[selectedMealIdx.value] ?? null);

// Reset al abrir
watch(() => props.open, (val) => {
  if (val) {
    view.value = 'category';
    selectedMealIdx.value = 0;
  }
});

const SHOPPING_TIPS = [
  'Compra proteínas frescas el mismo día que las consumas o máximo 2 días antes y guárdalas refrigeradas.',
  'Para carnes: elige cortes de color rojizo brillante, sin olor fuerte y sin líquido oscuro en el empaque.',
  'Para vegetales: busca que estén firmes, con color vivo y sin manchas oscuras ni humedad excesiva.',
  'Lee las etiquetas: el primer ingrediente es el más abundante. Evita azúcar en los primeros 3 ingredientes de productos empacados.',
  'Prefiere productos con lista de ingredientes corta: entre menos procesado, mejor calidad nutricional.',
  'Organiza tu carrito siguiendo el orden de secciones de esta lista para hacer el mercado más eficiente.',
];
</script>

<style scoped>
.grocery-fade-enter-active,
.grocery-fade-leave-active {
  transition: opacity 0.25s ease;
}
.grocery-fade-enter-from,
.grocery-fade-leave-to {
  opacity: 0;
}

.grocery-slide-enter-active,
.grocery-slide-leave-active {
  transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
}
.grocery-slide-enter-from,
.grocery-slide-leave-to {
  transform: translateY(100%);
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
```

- [ ] **Step 2.2: Commit del componente**

```bash
git add resources/js/vue/components/plan/nutrition/GroceryDrawer.vue
git commit -m "feat(nutrition): add GroceryDrawer component — bottom-sheet con lista de mercado por categoría y por comida"
```

---

## Task 3: Integrar en `NutritionTab.vue`

**Files:**
- Modify: `resources/js/vue/components/plan/nutrition/NutritionTab.vue`

- [ ] **Step 3.1: Agregar import de GroceryDrawer y ref de apertura**

En `NutritionTab.vue`, en el bloque `<script setup>`, agregar:

1. El import del componente (al final de los imports de componentes):
```js
import GroceryDrawer from './GroceryDrawer.vue';
```

2. Modificar el import existente de lucide-vue-next (línea 133) para agregar `ShoppingCart`:
```js
// Antes:
import { Check } from 'lucide-vue-next';
// Después:
import { Check, ShoppingCart } from 'lucide-vue-next';
```

2. La ref para controlar el drawer (junto a las otras refs locales):
```js
const groceryOpen = ref(false);
```

- [ ] **Step 3.2: Agregar botón "Lista de mercado" en el template**

En `NutritionTab.vue`, localizar el bloque del header "Plan del día" (la sección con la clase `flex flex-wrap items-baseline justify-between`). Agregar el botón justo dentro del div del header, después del `<p>` que muestra el conteo de comidas:

```html
<!-- Botón Lista de mercado -->
<button
  v-if="hasMeals"
  type="button"
  class="flex items-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-semibold text-wc-text-secondary hover:text-wc-text hover:border-wc-accent/40 transition-colors"
  @click="groceryOpen = true"
>
  <ShoppingCart :size="14" class="shrink-0" />
  Lista de mercado
</button>
```

- [ ] **Step 3.3: Montar GroceryDrawer en el template**

Al final del template de `NutritionTab.vue`, justo antes del `</div>` de cierre del componente raíz, agregar:

```html
<!-- Lista de Mercado drawer -->
<GroceryDrawer
  :open="groceryOpen"
  :nutrition-plan="nutritionPlan"
  @close="groceryOpen = false"
/>
```

- [ ] **Step 3.4: Verificar que el template compila sin errores**

```bash
npm run build 2>&1 | tail -20
```

Resultado esperado: sin errores de compilación.

- [ ] **Step 3.5: Commit de la integración**

```bash
git add resources/js/vue/components/plan/nutrition/NutritionTab.vue
git commit -m "feat(nutrition): integrar GroceryDrawer en NutritionTab — botón Lista de mercado"
```

---

## Task 4: Build local y verificación

- [ ] **Step 4.1: Build de assets**

```bash
npm run build
```

Resultado esperado: sin errores. `public/build/` actualizado.

- [ ] **Step 4.2: Commit del build**

```bash
git add public/build/
git commit -m "build: assets — grocery list drawer"
```

- [ ] **Step 4.3: Push y deploy**

```bash
git push origin main
```

Luego en EasyPanel — ejecutar `gitpull-load` script en la consola de servicio.

- [ ] **Step 4.4: Verificar en prod con Chrome DevTools MCP**

1. Navegar a `https://wellcorefitness.com` → login → ir a `/planes` → Tab Nutrición
2. Verificar que aparece el botón "Lista de mercado" con ícono de carrito
3. Hacer click → verificar que el drawer sube con animación correcta
4. Verificar toggle "Todo el plan" / "Por comida"
5. Verificar que la Guía de compra aparece al final siempre
6. Verificar que el backdrop cierra el drawer al hacer click

---

## Checklist de QA manual

- [ ] Botón visible solo cuando `hasMeals = true`
- [ ] Drawer abre y cierra con animación slide-up
- [ ] Backdrop cierra al hacer click
- [ ] Toggle "Todo el plan" agrupa alimentos por categoría (🥩🍚🥑🥦🛒)
- [ ] Toggle "Por comida" muestra chips scrollables por comida
- [ ] Seleccionar chip cambia alimentos mostrados
- [ ] Comidas con opciones A/B aparecen con label "(Opción A)" / "(Opción B)"
- [ ] "Guía de compra" visible en ambas vistas, al final
- [ ] Si nutritionPlan es null, botón no aparece
- [ ] Responsive: funciona en mobile (375px) y desktop
- [ ] No hay errores en consola
