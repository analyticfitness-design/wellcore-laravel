<template>
  <div
    @click="$emit('toggle')"
    role="button"
    tabindex="0"
    @keydown.enter="$emit('toggle')"
    @keydown.space.prevent="$emit('toggle')"
    class="grid w-full cursor-pointer grid-cols-[auto_1fr_auto] items-center gap-3 px-4 py-3.5 text-left transition hover:bg-wc-bg-tertiary/40"
    :class="{ 'border-b border-wc-border': expanded }"
  >
    <!-- Col 1: index stack vertical (NN + HH:MM) -->
    <div class="flex w-[44px] shrink-0 flex-col items-center gap-0.5">
      <span class="font-data text-[10px] uppercase tracking-wider text-wc-text-tertiary tabular-nums">
        {{ formattedIndex }}
      </span>
      <span
        class="font-data text-[11px] font-semibold tabular-nums"
        :class="isCurrent ? 'text-wc-accent' : 'text-wc-text'"
      >
        {{ meal.hora || meal.time || '--:--' }}
      </span>
    </div>

    <!-- Col 2: name + subtitle + macros inline -->
    <div class="min-w-0">
      <p class="truncate font-display text-sm font-medium uppercase tracking-wide text-wc-text leading-tight">
        {{ (meal.nombre || meal.name || ('Comida ' + (mealIdx + 1))) }}
      </p>
      <p
        v-if="subtitle"
        class="mt-0.5 truncate text-[11px] text-wc-text-tertiary"
      >
        {{ subtitle }}
      </p>
      <p
        v-if="proteinG > 0 || carbsG > 0 || fatG > 0"
        class="mt-1 flex items-center gap-2.5 font-data text-[11px] tabular-nums"
      >
        <span v-if="proteinG > 0" class="inline-flex items-center gap-1">
          <span class="h-1 w-1 rounded-full bg-red-400"></span>
          <span class="text-wc-text-secondary">{{ proteinG }}<span class="ml-0.5 text-[9px] text-wc-text-tertiary">g</span></span>
          <span class="text-wc-text-tertiary">P</span>
        </span>
        <span v-if="carbsG > 0" class="inline-flex items-center gap-1">
          <span class="h-1 w-1 rounded-full bg-blue-400"></span>
          <span class="text-wc-text-secondary">{{ carbsG }}<span class="ml-0.5 text-[9px] text-wc-text-tertiary">g</span></span>
          <span class="text-wc-text-tertiary">C</span>
        </span>
        <span v-if="fatG > 0" class="inline-flex items-center gap-1">
          <span class="h-1 w-1 rounded-full bg-amber-400"></span>
          <span class="text-wc-text-secondary">{{ fatG }}<span class="ml-0.5 text-[9px] text-wc-text-tertiary">g</span></span>
          <span class="text-wc-text-tertiary">G</span>
        </span>
      </p>
    </div>

    <!-- Col 3: kcal big + chevron -->
    <div class="flex shrink-0 items-center gap-2.5">
      <div v-if="kcalValue" class="flex flex-col items-end leading-none">
        <span class="font-display text-xl font-medium tabular-nums text-wc-text">{{ kcalValue }}</span>
        <span class="mt-0.5 font-data text-[9px] uppercase tracking-[0.1em] text-wc-text-tertiary">kcal</span>
      </div>
      <ChevronDown
        :size="16"
        :stroke-width="2.5"
        class="text-wc-text-tertiary transition-transform duration-200"
        :class="{ 'rotate-180': expanded }"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { ChevronDown } from 'lucide-vue-next';

const props = defineProps({
  meal: { type: Object, required: true },
  mealIdx: { type: Number, required: true },
  expanded: { type: Boolean, default: false },
  swapPanelOpen: { type: Boolean, default: false },
  swapped: { type: Boolean, default: false },
  isCurrent: { type: Boolean, default: false },
});

defineEmits(['toggle', 'open-swap']);

const proteinG = computed(() => props.meal.macros?.proteina ?? props.meal.macros?.proteina_g ?? 0);
const carbsG = computed(() => props.meal.macros?.carbohidratos ?? props.meal.macros?.carbohidratos_g ?? 0);
const fatG = computed(() => props.meal.macros?.grasas ?? props.meal.macros?.grasas_g ?? 0);
const kcalValue = computed(() => props.meal.kcal ?? props.meal.calorias ?? props.meal.calories ?? null);

const formattedIndex = computed(() => {
  const n = props.meal.numero ?? (props.mealIdx + 1);
  return String(n).padStart(2, '0');
});

const subtitle = computed(() => {
  return props.meal.subtitulo
    || props.meal.subtitle
    || props.meal.descripcion
    || props.meal.description
    || '';
});
</script>
