<template>
  <li v-if="parsed.name" class="grid grid-cols-[auto_1fr] gap-x-4 border-b border-dashed border-wc-border/40 py-2.5 last:border-b-0">
    <span class="font-data text-[11px] uppercase tracking-wider text-wc-text-tertiary tabular-nums whitespace-nowrap text-right min-w-[60px] pt-0.5">
      {{ parsed.qty || '·' }}
    </span>
    <span class="min-w-0 text-sm leading-snug text-wc-text">
      <span v-if="icon && showIcon" class="mr-1.5 text-base leading-none">{{ icon }}</span>
      <span>{{ parsed.name }}</span>
      <em v-if="parsed.detail" class="not-italic mt-0.5 block text-[11px] text-wc-text-tertiary">{{ parsed.detail }}</em>
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
  // El HTML target NO usa emojis. Mantenemos el prop por compatibilidad pero
  // sin renderizado por default — solo si parent fuerza explicitamente
  // showIcon=true (caso SwappedRecipe ingredients).
  icon: {
    type: String,
    default: '',
  },
  showIcon: {
    type: Boolean,
    default: false,
  },
});

const { formatFoodName } = useFoodIcon();

// Parser heuristico — replica la estructura del HTML target v2 (qty | name | em sub-detail)
const parsed = computed(() => {
  const raw = formatFoodName(props.food);
  const trimmed = (raw || '').trim();
  if (!trimmed) return { qty: '', name: '', detail: '' };

  if (props.food && typeof props.food === 'object') {
    const qty = (props.food.cantidad || props.food.porcion || props.food.quantity || props.food.amount || '').toString().trim();
    const fullName = (props.food.nombre || props.food.alimento || props.food.name || trimmed).toString().trim();
    const split = splitDetail(fullName);
    return { qty, name: capitalize(split.name), detail: split.detail };
  }

  const qtyMatch = trimmed.match(/^([\d¼½¾]+(?:\.\d+)?(?:\/\d+)?\s*(?:und|unidad(?:es)?|taza[s]?|cucharada[s]?|cucharadita[s]?|ml|g|kg|oz|porcion(?:es)?|vaso[s]?|piezas?|gramos?)?)\s+(?:de\s+)?/i);

  let qty = '';
  let rest = trimmed;
  if (qtyMatch) {
    qty = normalizeQty(qtyMatch[1]);
    rest = trimmed.slice(qtyMatch[0].length).trim();
  } else {
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
  const conMatch = str.match(/^(.+?)\s+con\s+(.+)$/i);
  if (conMatch) return { name: conMatch[1].trim(), detail: 'con ' + conMatch[2].trim() };

  const parenMatch = str.match(/^(.+?)\s*\(([^)]+)\)\s*$/);
  if (parenMatch) return { name: parenMatch[1].trim(), detail: parenMatch[2].trim() };

  const dashMatch = str.match(/^(.+?)\s+[—–-]\s+(.+)$/);
  if (dashMatch) return { name: dashMatch[1].trim(), detail: dashMatch[2].trim() };

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
