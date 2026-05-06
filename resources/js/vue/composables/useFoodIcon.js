// Module-level cache shared across all useFoodIcon() consumers.
// Key: lowercased input string. Value: emoji string (or '' for no match).
const cache = new Map();

// Keyword → emoji map. Order matters: first match wins.
// Mirrors PlanViewer.vue foodIcon() (line 297) — DO NOT modify behavior.
const FOOD_MAP = [
  [['pollo', 'pechuga', 'chicken', 'pavo'], '\u{1F357}'],
  [['carne', 'res', 'steak', 'lomo', 'cerdo'], '\u{1F969}'],
  [['salmón', 'salmon', 'atún', 'atun', 'tilapia', 'pescado', 'corvina', 'trucha'], '\u{1F41F}'],
  [['huevo', 'clara', 'claras'], '\u{1F95A}'],
  [['yogur', 'yogurt', 'leche'], '\u{1F95B}'],
  [['queso', 'requesón', 'requeson'], '\u{1F9C0}'],
  [['avena', 'granola', 'oatmeal'], '\u{1F963}'],
  [['arroz', 'rice', 'quinoa'], '\u{1F35A}'],
  [['pasta'], '\u{1F35D}'],
  [['pan', 'tostada'], '\u{1F35E}'],
  [['arepa', 'tortilla'], '\u{1FAD3}'],
  [['papa'], '\u{1F954}'],
  [['batata', 'camote'], '\u{1F360}'],
  [['banana', 'banano', 'plátano', 'platano'], '\u{1F34C}'],
  [['manzana'], '\u{1F34E}'],
  [['fresa', 'fresas'], '\u{1F353}'],
  [['fruta', 'frutas'], '\u{1F347}'],
  [['brócoli', 'brocoli'], '\u{1F966}'],
  [['espinaca', 'lechuga'], '\u{1F96C}'],
  [['ensalada', 'vegetal', 'vegetales'], '\u{1F957}'],
  [['tomate'], '\u{1F345}'],
  [['aguacate', 'avocado'], '\u{1F951}'],
  [['nuez', 'nueces', 'almendra', 'maní', 'mani'], '\u{1F95C}'],
  [['aceite', 'oliva'], '\u{1FAD2}'],
  [['proteína', 'proteina', 'whey'], '\u{1F9EA}'],
  [['agua'], '\u{1F4A7}'],
  [['café', 'cafe'], '☕'],
  [['miel'], '\u{1F36F}'],
  [['frijol', 'lenteja'], '\u{1FAD8}'],
  [['jugo', 'mango', 'maracuy'], '\u{1F9C3}'],
];

/**
 * Normalize a food entry that may be a string or an object shaped like
 * `{ nombre, alimento, name, cantidad, porcion, quantity, amount }`.
 * Mirrors PlanViewer.vue formatNutrAlimento() (line 306) — DO NOT modify behavior.
 *
 * @param {string|object} alimento
 * @returns {string} Display text (e.g. "4 huevos — 200g") or '' if unresolvable.
 */
function formatFoodName(alimento) {
  if (typeof alimento === 'string') return alimento;
  if (alimento && typeof alimento === 'object') {
    const name = alimento.nombre || alimento.alimento || alimento.name || '';
    const qty = alimento.cantidad || alimento.porcion || alimento.quantity || alimento.amount || '';
    if (name && qty) return `${name} — ${qty}`;
    return name || qty || '';
  }
  if (alimento === null || alimento === undefined) return '';
  return String(alimento);
}

/**
 * Resolve a food entry (string or object) to a representative emoji.
 * Iterates FOOD_MAP keywords in order and returns the first emoji whose
 * keyword appears (case-insensitive substring) in the input. Object inputs
 * are normalized via formatFoodName() to extract the searchable name.
 * Results are memoized in a module-level Map keyed by the lowercased text.
 *
 * @param {string|object} input - Food description string or object.
 * @returns {string} Emoji character, or empty string if no keyword matches.
 */
function foodIcon(input) {
  const text = typeof input === 'string' ? input : formatFoodName(input);
  const lower = text.toLowerCase();
  if (!lower) return '';
  const cached = cache.get(lower);
  if (cached !== undefined) return cached;
  for (const [keywords, emoji] of FOOD_MAP) {
    if (keywords.some((k) => lower.includes(k))) {
      cache.set(lower, emoji);
      return emoji;
    }
  }
  cache.set(lower, '');
  return '';
}

/**
 * Clear the module-level emoji cache. Primarily useful for tests.
 * @returns {void}
 */
function clearCache() {
  cache.clear();
}

export function useFoodIcon() {
  return { foodIcon, formatFoodName, clearCache };
}
