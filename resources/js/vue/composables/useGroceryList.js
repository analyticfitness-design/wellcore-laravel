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
      'aguacate', 'avocado', 'nuez', 'nueces', 'almendra', 'mani',
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
    .replace(/[\u0300-\u036F]/g, '')
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

// Extrae todos los alimentos de una comida (soporta alimentos directos y opciones A/B/C)
function extractFoodsFromMeal(meal) {
  const items = [];
  const mealLabel = meal.nombre || meal.name || '';

  // Alimentos directos (sin opciones múltiples)
  const directFoods = meal.alimentos || meal.foods || meal.ingredientes || [];
  for (const food of directFoods) {
    const parsed = parseFood(food);
    if (parsed) items.push({ ...parsed, meal: mealLabel });
  }

  // Opciones — formato canónico v2: opcion_a, opcion_b, opcion_c (top-level del meal)
  for (const suffix of ['a', 'b', 'c']) {
    const optFoods = meal[`opcion_${suffix}`];
    if (!Array.isArray(optFoods) || optFoods.length === 0) continue;
    for (const food of optFoods) {
      const parsed = parseFood(food);
      if (parsed) {
        items.push({
          name: parsed.name + ` (Opción ${suffix.toUpperCase()})`,
          qty: parsed.qty,
          meal: mealLabel,
        });
      }
    }
  }

  // Opciones — formato legacy: meal.opciones = { a: [...], b: [...] }
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
  const allItems = computed(() => {
    const plan = nutritionPlanRef.value;
    if (!plan) return [];
    const meals = plan.comidas || plan.comidas_sugeridas || [];
    return meals.flatMap(extractFoodsFromMeal);
  });

  const byCategory = computed(() => {
    const groups = new Map();
    for (const cat of [...CATEGORIES, OTROS]) {
      groups.set(cat.key, { ...cat, items: [] });
    }
    for (const item of allItems.value) {
      const cat = classify(item.name);
      groups.get(cat.key).items.push(item);
    }
    return [...groups.values()].filter((g) => g.items.length > 0);
  });

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
