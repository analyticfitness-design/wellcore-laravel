<template>
  <li v-if="parsed.name" class="flex items-start gap-2.5">
    <span v-if="icon" class="shrink-0 text-base leading-none mt-0.5">{{ icon }}</span>
    <span v-else class="mt-2 h-1 w-1 shrink-0 rounded-full bg-wc-accent"></span>
    <span class="min-w-0 flex-1 text-sm leading-relaxed text-wc-text-secondary">
      <span v-if="parsed.qty" class="mr-1.5 inline-block rounded-md bg-wc-bg-tertiary/60 px-1.5 py-0.5 text-[11px] font-data font-semibold text-wc-text tabular-nums tracking-wide">
        {{ parsed.qty }}
      </span>
      <span class="text-wc-text-secondary">{{ parsed.name }}</span>
      <em v-if="parsed.detail" class="not-italic ml-1 text-xs text-wc-text-tertiary">{{ parsed.detail }}</em>
    </span>
  </li>
</template>

<script setup>
import { computed } from 'vue';
import { useFoodIcon } from '@/composables/useFoodIcon';

const props = defineProps({
  food: {
    type: [String, Object],
    required: true,
  },
  icon: {
    type: String,
    default: '',
  },
});

const { formatFoodName } = useFoodIcon();

// Parser heuristico — replica la estructura del HTML target v2 (qty | name | em sub-detail)
// usando datos reales del backend (que vienen como string libre).
//
// Ejemplos:
//   "4 huevos enteros revueltos con cebolla, tomate y cilantro"
//     → qty="4 und", name="Huevos enteros revueltos", detail="con cebolla, tomate y cilantro"
//   "1 taza de frijoles rojos o negros"
//     → qty="1 taza", name="Frijoles rojos o negros", detail=""
//   "1/2 aguacate"
//     → qty="½ und", name="Aguacate", detail=""
const parsed = computed(() => {
  const raw = formatFoodName(props.food);
  const trimmed = (raw || '').trim();
  if (!trimmed) return { qty: '', name: '', detail: '' };

  // Si viene como object con keys nombre/cantidad explicit, respetar
  if (props.food && typeof props.food === 'object') {
    const qty = (props.food.cantidad || props.food.porcion || props.food.quantity || props.food.amount || '').toString().trim();
    const fullName = (props.food.nombre || props.food.alimento || props.food.name || trimmed).toString().trim();
    const split = splitDetail(fullName);
    return { qty, name: capitalize(split.name), detail: split.detail };
  }

  // String libre — extraer qty leading
  const qtyMatch = trimmed.match(/^([\d¼½¾]+(?:\.\d+)?(?:\/\d+)?\s*(?:und|unidad(?:es)?|taza[s]?|cucharada[s]?|cucharadita[s]?|ml|g|kg|oz|porcion(?:es)?|vaso[s]?|piezas?|gramos?)?)\s+(?:de\s+)?/i);

  let qty = '';
  let rest = trimmed;
  if (qtyMatch) {
    qty = normalizeQty(qtyMatch[1]);
    rest = trimmed.slice(qtyMatch[0].length).trim();
  } else {
    // Si no hay match formal pero arranca con número, captura solo el número
    const numMatch = trimmed.match(/^([\d¼½¾]+(?:\.\d+)?(?:\/\d+)?)\s+/);
    if (numMatch) {
      qty = numMatch[1].replace('1/2', '½').replace('1/4', '¼').replace('3/4', '¾') + ' und';
      rest = trimmed.slice(numMatch[0].length).trim();
    }
  }

  const split = splitDetail(rest);
  return { qty, name: capitalize(split.name), detail: split.detail };
});

function splitDetail(str) {
  if (!str) return { name: '', detail: '' };
  // Patrones para extraer sub-detail:
  //  1. "X con Y" → name=X, detail="con Y"
  //  2. "X (o Y)" → name=X, detail="o Y"
  //  3. "X — Y" o "X - Y" → name=X, detail=Y
  //  4. "X, ingrediente1, ingrediente2" → name="X", detail="ingrediente1, ingrediente2"
  const conMatch = str.match(/^(.+?)\s+con\s+(.+)$/i);
  if (conMatch) return { name: conMatch[1].trim(), detail: 'con ' + conMatch[2].trim() };

  const parenMatch = str.match(/^(.+?)\s*\(([^)]+)\)\s*$/);
  if (parenMatch) return { name: parenMatch[1].trim(), detail: parenMatch[2].trim() };

  const dashMatch = str.match(/^(.+?)\s+[—–-]\s+(.+)$/);
  if (dashMatch) return { name: dashMatch[1].trim(), detail: dashMatch[2].trim() };

  // Sin patron → todo es name
  return { name: str.trim(), detail: '' };
}

function normalizeQty(raw) {
  return raw
    .replace(/1\/2/g, '½')
    .replace(/1\/4/g, '¼')
    .replace(/3\/4/g, '¾')
    .replace(/\s+/g, ' ')
    .trim();
}

function capitalize(str) {
  if (!str) return '';
  const first = str.charAt(0);
  if (first === first.toLowerCase() && first !== first.toUpperCase()) {
    return first.toUpperCase() + str.slice(1);
  }
  return str;
}
</script>
