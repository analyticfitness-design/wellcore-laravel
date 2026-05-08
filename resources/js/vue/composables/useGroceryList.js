import { computed, ref } from 'vue';

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

// === Helpers para acumulado semanal ===

// "200g" → {value:200, unit:'g'} | "2" → {value:2, unit:''} | "al gusto" → null
function parseNumericQty(qty) {
  if (!qty) return null;
  const m = String(qty).match(/^(\d+(?:[.,]\d+)?)\s*(g|kg|ml|l|oz|lb)?(?:\s|$)/i);
  if (!m) return null;
  return { value: parseFloat(m[1].replace(',', '.')), unit: (m[2] || '').toLowerCase() };
}

// Normaliza a unidades base (g, ml) para poder sumar sin errores de unidad
function toBaseUnit(parsed) {
  if (!parsed) return null;
  if (parsed.unit === 'kg') return { value: parsed.value * 1000, unit: 'g' };
  if (parsed.unit === 'l')  return { value: parsed.value * 1000, unit: 'ml' };
  return { ...parsed };
}

// Formatea desde unidad base a string legible: 1400g → "1.4kg"
function formatQty(value, unit) {
  const v = value;
  if (unit === 'g'  && v >= 1000) return `${parseFloat((v / 1000).toFixed(2))}kg`;
  if (unit === 'ml' && v >= 1000) return `${parseFloat((v / 1000).toFixed(2))}L`;
  if (!unit) return `${parseFloat(v.toFixed(1))}`;
  return `${parseFloat(v.toFixed(1))}${unit}`;
}

// Aplica ×7 a un string de cantidad: "200g"→"1.4kg", "2"→"14", "al gusto"→"al gusto"
function times7qty(qty) {
  if (!qty) return '';
  const parsed = parseNumericQty(qty);
  if (!parsed) return qty;
  const base = toBaseUnit(parsed);
  return formatQty(base.value * 7, base.unit);
}

// Aplica ×7 al número inicial de un nombre: "4 huevos"→"28 huevos"
function times7name(name) {
  if (!name) return '';
  return name.replace(
    /^(\d+(?:[.,]\d+)?)(?: *(g|kg|ml|l|oz|lb))?(?=[ \t]|$)/i,
    (_, num, unit = '') => {
      const n = parseFloat(num.replace(',', '.'));
      const x = n * 7;
      if (unit.toLowerCase() === 'g'  && x >= 1000) return `${parseFloat((x / 1000).toFixed(2))}kg`;
      if (unit.toLowerCase() === 'ml' && x >= 1000) return `${parseFloat((x / 1000).toFixed(2))}L`;
      return `${parseFloat(x.toFixed(1))}${unit}`;
    },
  );
}

// Deduplica items por nombre (sumando cantidades numéricas compatibles) y luego aplica ×7.
// Esto evita que el mismo alimento (ej. pechuga en 3 comidas) genere 3 filas cada una ×7.
function aggregateForWeek(items) {
  const map = new Map();
  for (const item of items) {
    const key = normalize(item.name);
    const base = toBaseUnit(parseNumericQty(item.qty));

    if (!map.has(key)) {
      map.set(key, {
        name: item.name,
        qty: item.qty,
        baseValue: base ? base.value : null,
        baseUnit:  base ? base.unit  : null,
      });
    } else {
      const ex = map.get(key);
      if (ex.baseValue !== null && base !== null && ex.baseUnit === base.unit) {
        ex.baseValue += base.value;
        ex.qty = formatQty(ex.baseValue, ex.baseUnit);
      }
      // Unidades distintas o no-numéricas: mantener primera entrada sin duplicar
    }
  }

  return [...map.values()].map((entry) => ({
    name: entry.qty ? entry.name : times7name(entry.name),
    qty:  times7qty(entry.qty),
  }));
}

// Extrae alimentos de una comida. Si selectedOption ('a','b','c') está definida,
// solo incluye esa opción. Sin selectedOption incluye todas con etiqueta.
function extractFoodsFromMeal(meal, selectedOption) {
  const items = [];
  const mealLabel = meal.nombre || meal.name || '';

  // Alimentos directos (siempre incluidos)
  const directFoods = meal.alimentos || meal.foods || meal.ingredientes || [];
  for (const food of directFoods) {
    const parsed = parseFood(food);
    if (parsed) items.push({ ...parsed, meal: mealLabel });
  }

  // Construir mapa de opciones (formato canónico v2 + legacy)
  const optionMap = {};
  for (const s of ['a', 'b', 'c']) {
    const optFoods = meal[`opcion_${s}`];
    if (Array.isArray(optFoods) && optFoods.length > 0) optionMap[s] = optFoods;
  }
  const opciones = meal.opciones || meal.options || {};
  for (const [k, optFoods] of Object.entries(opciones)) {
    if (Array.isArray(optFoods) && optFoods.length > 0) {
      optionMap[k] = optionMap[k] ? [...optionMap[k], ...optFoods] : optFoods;
    }
  }

  if (Object.keys(optionMap).length === 0) return items;

  const keysToShow = selectedOption ? [selectedOption] : Object.keys(optionMap).sort();
  for (const key of keysToShow) {
    for (const food of (optionMap[key] || [])) {
      const parsed = parseFood(food);
      if (parsed) {
        items.push({
          name: selectedOption ? parsed.name : `${parsed.name} (Opción ${key.toUpperCase()})`,
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
 * @param {import('vue').Ref<string|null>} activeOptionRef — 'a'|'b'|'c'|null
 * @returns {{ byCategory: ComputedRef, byMeal: ComputedRef, availableOptions: ComputedRef }}
 */
export function useGroceryList(nutritionPlanRef, activeOptionRef = ref(null)) {
  const allItems = computed(() => {
    const plan = nutritionPlanRef.value;
    if (!plan) return [];
    const opt = activeOptionRef.value;
    const meals = plan.comidas || plan.comidas_sugeridas || [];
    return meals.flatMap((meal) => extractFoodsFromMeal(meal, opt));
  });

  // Opciones disponibles en el plan (union de todas las comidas)
  const availableOptions = computed(() => {
    const plan = nutritionPlanRef.value;
    if (!plan) return [];
    const meals = plan.comidas || plan.comidas_sugeridas || [];
    const opts = new Set();
    for (const meal of meals) {
      for (const s of ['a', 'b', 'c']) {
        if (Array.isArray(meal[`opcion_${s}`]) && meal[`opcion_${s}`].length > 0) opts.add(s);
      }
      const opciones = meal.opciones || meal.options || {};
      for (const [k, v] of Object.entries(opciones)) {
        if (Array.isArray(v) && v.length > 0) opts.add(k);
      }
    }
    return [...opts].sort();
  });

  const byCategory = computed(() => {
    const groups = new Map();
    for (const cat of [...CATEGORIES, OTROS]) {
      groups.set(cat.key, { ...cat, items: [] });
    }
    // Agrega por nombre y aplica ×7 antes de mostrar — evita cantidades irracionales
    for (const item of aggregateForWeek(allItems.value)) {
      const cat = classify(item.name);
      groups.get(cat.key).items.push(item);
    }
    return [...groups.values()].filter((g) => g.items.length > 0);
  });

  const byMeal = computed(() => {
    const plan = nutritionPlanRef.value;
    if (!plan) return [];
    const opt = activeOptionRef.value;
    const meals = plan.comidas || plan.comidas_sugeridas || [];
    return meals
      .map((meal) => ({
        label: meal.nombre || meal.name || 'Comida',
        hora: meal.hora || meal.time || '',
        items: extractFoodsFromMeal(meal, opt),
      }))
      .filter((m) => m.items.length > 0);
  });

  return { byCategory, byMeal, availableOptions };
}
