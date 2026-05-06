<template>
  <div class="relative">
    <!-- línea horizontal sutil detrás de los dots (alineada con centro del dot) -->
    <div
      class="pointer-events-none absolute left-0 right-0 top-[22px] h-px bg-wc-border"
      aria-hidden="true"
    ></div>

    <div
      ref="scrollerRef"
      class="dt-scroller relative flex items-start gap-1 sm:gap-2 overflow-x-auto snap-x snap-mandatory pb-2"
      :aria-label="ariaLabel"
      role="list"
    >
      <div
        v-for="(meal, idx) in meals"
        :key="idx"
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
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import TimelineNode from './TimelineNode.vue';

const props = defineProps({
  meals: { type: Array, required: true },
  currentMealIndex: { type: Number, default: -1 },
  swappedMealIndices: { type: Array, default: () => [] },
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
  // Primera palabra significativa para evitar overflow horizontal del timeline.
  // "Almuerzo post-entreno" → "Almuerzo" · "Pre-entreno" → "Pre" · "Merienda nocturna" → "Merienda".
  const firstWord = raw.split(/[\s\-·]+/).filter(Boolean)[0] || raw;
  return firstWord.length > 10 ? firstWord.slice(0, 10) : firstWord;
}

function resolveMealState(idx) {
  if (props.swappedMealIndices.includes(idx)) return 'swapped';
  if (idx === props.currentMealIndex) return 'current';
  if (props.currentMealIndex >= 0 && idx < props.currentMealIndex) return 'done';
  return 'pending';
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
