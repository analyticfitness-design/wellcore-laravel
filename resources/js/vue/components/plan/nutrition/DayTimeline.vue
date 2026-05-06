<template>
  <div
    ref="scrollerRef"
    class="dt-scroller flex items-start gap-1 overflow-x-auto pb-2 snap-x snap-proximity"
    :aria-label="ariaLabel"
    role="list"
  >
    <template v-for="(meal, idx) in meals" :key="idx">
      <div
        ref="nodeRefs"
        role="listitem"
        class="snap-center shrink-0"
      >
        <TimelineNode
          :time="meal.hora || meal.time || ''"
          :label="resolveLabel(meal)"
          :state="resolveMealState(idx)"
          :interactive="interactive"
          :compact="meals.length > 5"
          @click="onNodeClick(idx)"
        />
      </div>
      <span
        v-if="idx < meals.length - 1"
        aria-hidden="true"
        class="self-start mt-[22px] h-px min-w-[12px] flex-1"
        :class="resolveLineClass(idx)"
      ></span>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import TimelineNode from './TimelineNode.vue';

const props = defineProps({
  meals: { type: Array, required: true },
  currentMealIndex: { type: Number, default: -1 },
  swappedMealIndices: { type: Array, default: () => [] },
  // Indices de comidas marcadas como hechas (useMealCheckins). Cuando el cliente
  // marca DESAYUNO con el boton MealBody, el dot del timeline cambia a 'done' verde.
  checkedMealIndices: { type: Array, default: () => [] },
  interactive: { type: Boolean, default: false },
});

const emit = defineEmits(['select-meal']);

const scrollerRef = ref(null);
const nodeRefs = ref([]);

const ariaLabel = computed(
  () => `Cronograma del día con ${props.meals.length} comida${props.meals.length === 1 ? '' : 's'}`
);

function resolveLabel(meal) {
  const raw = String(meal?.nombre || meal?.name || '').trim();
  if (!raw) return '';
  const firstWord = raw.split(/[\s\-·]+/).filter(Boolean)[0] || raw;
  return firstWord.length > 10 ? firstWord.slice(0, 9) + '…' : firstWord;
}

// Prioridad de estado:
//   1. swapped → REEMPLAZADA (rojo wc-accent + ring)
//   2. done    → MARCADA por el cliente (verde emerald + ring)
//   3. current → comida actual segun hora (rojo pulsing)
//   4. pending → futura (dot vacio outline)
function resolveMealState(idx) {
  if (props.swappedMealIndices.includes(idx)) return 'swapped';
  if (props.checkedMealIndices.includes(idx)) return 'done';
  if (idx === props.currentMealIndex) return 'current';
  return 'pending';
}

// Linea conectora — verde si esta o las previas estan marcadas; tertiary
// si paso por hora pero sin marcar; sino sutil border.
function resolveLineClass(idx) {
  if (props.checkedMealIndices.includes(idx)) return 'bg-emerald-400/40';
  const cur = props.currentMealIndex;
  if (cur >= 0 && idx < cur) return 'bg-wc-text-tertiary/40';
  return 'bg-wc-border';
}

function onNodeClick(idx) {
  if (!props.interactive) return;
  emit('select-meal', idx);
}

function scrollToCurrent() {
  const idx = props.currentMealIndex;
  if (idx < 0 || idx >= (nodeRefs.value?.length || 0)) return;
  const node = nodeRefs.value[idx];
  if (node && typeof node.scrollIntoView === 'function') {
    node.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
  }
}

watch(
  () => props.currentMealIndex,
  () => {
    nextTick(scrollToCurrent);
  }
);

onMounted(() => {
  nextTick(scrollToCurrent);
});
</script>

<style scoped>
.dt-scroller {
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.dt-scroller::-webkit-scrollbar {
  display: none;
}
</style>
