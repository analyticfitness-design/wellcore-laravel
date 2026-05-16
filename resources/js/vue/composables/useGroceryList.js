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

// Detecta si lo que va entre paréntesis parece cantidad (no texto descriptivo)
const QTY_HINT_RE = /^[\d½¼¾⅓⅔]/;
const QTY_UNIT_RE = /\b(?:g|kg|ml|l|oz|lb|unidades?|und|u|scoops?|cucharadas?|cda|cdas|cdita|cditas|rebanadas?|tabletas?|capsulas?|cápsulas?)\b/i;

function looksLikeQuantity(s) {
  const t = (s || '').trim();
  return QTY_HINT_RE.test(t) || QTY_UNIT_RE.test(t);
}

// Extrae qty del primer "(...)" del string si parece cantidad.
// "Pechuga (180g) con limón" → { name:"Pechuga con limón", qty:"180g" }
// "Aguacate (½ unidad mediana, 50g)" → { name:"Aguacate", qty:"½ unidad mediana, 50g" }
// "Carne con cilantro (no frita)" → null (no parece qty)
function extractParenQty(str) {
  const m = str.match(/^(.+?)\s*\(([^)]+)\)(.*)$/);
  if (!m) return null;
  if (!looksLikeQuantity(m[2])) return null;
  const before = m[1].trim();
  const after = m[3].trim();
  return { name: after ? `${before} ${after}`.replace(/\s+/g, ' ').trim() : before, qty: m[2].trim() };
}

// Extrae qty del prefijo del string: "3 huevos" → { name:"huevos", qty:"3" }
function extractLeadingQty(str) {
  const m = str.match(/^(\d+(?:[.,]\d+)?\s*(?:g|kg|ml|l|oz|lb|unidades?|und|u|scoops?|cucharadas?|cda|cdas|cdita|cditas|rebanadas?|tabletas?|capsulas?|cápsulas?)?)\s+(.+)$/i);
  if (!m) return null;
  return { name: m[2].trim(), qty: m[1].trim() };
}

// Parsea un único segmento (sin '+') a {name, qty}
function parseFoodSegment(str) {
  const trimmed = (str || '').trim();
  if (!trimmed) return null;
  return extractParenQty(trimmed) || extractLeadingQty(trimmed) || { name: trimmed, qty: '' };
}

// Extrae el nombre y cantidad de un alimento. Retorna ARRAY porque un string
// compuesto con " + " (ej "Huevos (2 und) + claras (3 und)") es múltiples ítems.
function parseFood(food) {
  if (!food) return [];
  if (typeof food === 'string') {
    const trimmed = food.trim();
    if (!trimmed) return [];
    // Split por " + " solo si ambos lados parecen items completos
    const parts = trimmed.split(/\s+\+\s+/);
    const out = [];
    for (const part of parts) {
      const parsed = parseFoodSegment(part);
      if (parsed) out.push(parsed);
    }
    return out;
  }
  if (typeof food === 'object') {
    const name = (food.nombre || food.alimento || food.name || '').trim();
    if (!name) return [];
    const qty = (food.cantidad || food.porcion || food.quantity || food.amount || '').toString().trim();
    return [{ name, qty }];
  }
  return [];
}

// === Helpers para acumulado semanal ===

// Reemplaza fracciones unicode (½ ¼ ¾ ⅓ ⅔) y tipo "1/2" por decimales
function expandFractions(s) {
  const map = { '½':'0.5', '¼':'0.25', '¾':'0.75', '⅓':'0.333', '⅔':'0.667' };
  let out = String(s);
  for (const [f, v] of Object.entries(map)) out = out.replace(new RegExp(f, 'g'), v);
  return out.replace(/(\d+)\s*\/\s*(\d+)/g, (_, a, b) => (parseFloat(a) / parseFloat(b)).toString());
}

// Normaliza unidades aceptadas a forma canónica singular
function normalizeUnit(u) {
  const x = (u || '').toLowerCase().trim();
  if (!x) return '';
  if (/^(unidades?|und|u)$/.test(x)) return 'unidad';
  if (/^scoops?$/.test(x)) return 'scoop';
  if (/^(cucharadas?|cdas?)$/.test(x)) return 'cda';
  if (/^cditas?$/.test(x)) return 'cdita';
  if (/^rebanadas?$/.test(x)) return 'rebanada';
  if (/^tabletas?$/.test(x)) return 'tableta';
  if (/^(c[aá]psulas?)$/.test(x)) return 'capsula';
  return x; // g, kg, ml, l, oz, lb
}

// "200g" → {value:200, unit:'g'} | "½ unidad" → {value:0.5, unit:'unidad'} | "al gusto" → null
function parseNumericQty(qty) {
  if (!qty) return null;
  const s = expandFractions(String(qty).trim());
  const m = s.match(/(\d+(?:\.\d+)?)\s*(g|kg|ml|l|oz|lb|unidades?|und|u|scoops?|cucharadas?|cdas?|cditas?|rebanadas?|tabletas?|c[aá]psulas?)?/i);
  if (!m) return null;
  return { value: parseFloat(m[1]), unit: normalizeUnit(m[2]) };
}

// Normaliza a unidades base (g, ml) para poder sumar sin errores de unidad
function toBaseUnit(parsed) {
  if (!parsed) return null;
  if (parsed.unit === 'kg') return { value: parsed.value * 1000, unit: 'g' };
  if (parsed.unit === 'l')  return { value: parsed.value * 1000, unit: 'ml' };
  return { ...parsed };
}

// Pluraliza unidad descriptiva según valor
function pluralizeUnit(unit, value) {
  const plural = value !== 1;
  switch (unit) {
    case 'unidad':    return plural ? 'unidades' : 'unidad';
    case 'scoop':     return plural ? 'scoops' : 'scoop';
    case 'cda':       return plural ? 'cdas' : 'cda';
    case 'cdita':     return plural ? 'cditas' : 'cdita';
    case 'rebanada':  return plural ? 'rebanadas' : 'rebanada';
    case 'tableta':   return plural ? 'tabletas' : 'tableta';
    case 'capsula':   return plural ? 'cápsulas' : 'cápsula';
    default:          return unit; // g, kg, ml, l, oz, lb
  }
}

// Formatea desde unidad base a string legible: 1400g → "1.4kg", 14 unidad → "14 unidades"
function formatQty(value, unit) {
  if (unit === 'g'  && value >= 1000) return `${parseFloat((value / 1000).toFixed(2))}kg`;
  if (unit === 'ml' && value >= 1000) return `${parseFloat((value / 1000).toFixed(2))}L`;
  const num = parseFloat(value.toFixed(1));
  const u = pluralizeUnit(unit, num);
  if (!u) return `${num}`;
  // Para unidades descriptivas (unidades, scoops, etc.) va con espacio
  if (['unidades','unidad','scoops','scoop','cdas','cda','cditas','cdita','rebanadas','rebanada','tabletas','tableta','cápsulas','cápsula'].includes(u)) {
    return `${num} ${u}`;
  }
  // Para g/kg/ml/L/oz/lb va pegado
  return `${num}${u}`;
}

// Aplica ×7 a un string de cantidad: "200g"→"1.4kg", "2 unidades"→"14 unidades", "al gusto"→"al gusto"
function times7qty(qty) {
  if (!qty) return '';
  const parsed = parseNumericQty(qty);
  if (!parsed) return qty;
  const base = toBaseUnit(parsed);
  return formatQty(base.value * 7, base.unit);
}

// Aplica ×7 al primer número del nombre: "4 huevos"→"28 huevos"
function times7name(name) {
  if (!name) return '';
  const expanded = expandFractions(name);
  return expanded.replace(
    /(\d+(?:\.\d+)?)\s*(g|kg|ml|l|oz|lb|unidades?|und|u|scoops?|cucharadas?|cdas?|cditas?|rebanadas?|tabletas?|c[aá]psulas?)?/i,
    (match, num, unit) => {
      const n = parseFloat(num);
      const u = normalizeUnit(unit);
      const x = n * 7;
      if (u === 'g'  && x >= 1000) return `${parseFloat((x / 1000).toFixed(2))}kg`;
      if (u === 'ml' && x >= 1000) return `${parseFloat((x / 1000).toFixed(2))}L`;
      const formatted = parseFloat(x.toFixed(1));
      const pluralized = pluralizeUnit(u, formatted);
      if (!pluralized) return `${formatted}`;
      if (['unidades','unidad','scoops','scoop','cdas','cda','cditas','cdita','rebanadas','rebanada','tabletas','tableta','cápsulas','cápsula'].includes(pluralized)) {
        return `${formatted} ${pluralized}`;
      }
      return `${formatted}${pluralized}`;
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
    for (const parsed of parseFood(food)) {
      items.push({ ...parsed, meal: mealLabel });
    }
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
      for (const parsed of parseFood(food)) {
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
