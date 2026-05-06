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
    <!-- Col 1: index stack vertical con accent color por tipo de meal -->
    <div
      class="flex w-[52px] shrink-0 flex-col items-center gap-0.5 rounded-lg border px-1.5 py-1.5 transition-colors"
      :class="indexBadgeClass"
    >
      <span class="font-data text-[10px] uppercase tracking-wider tabular-nums" :class="indexNumClass">
        {{ formattedIndex }}
      </span>
      <span
        class="font-data text-[11px] font-semibold tabular-nums"
        :class="isCurrent ? 'text-wc-accent' : indexTimeClass"
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
        class="mt-1.5 flex flex-wrap items-center gap-1.5 font-data text-[10px] tabular-nums"
      >
        <span v-if="proteinG > 0" class="inline-flex items-center gap-1 rounded-full bg-red-500/10 border border-red-500/20 px-2 py-0.5 text-red-400 font-semibold">
          <span class="h-1 w-1 rounded-full bg-red-400"></span>
          P {{ proteinG }}<span class="ml-0.5 opacity-70">g</span>
        </span>
        <span v-if="carbsG > 0" class="inline-flex items-center gap-1 rounded-full bg-blue-500/10 border border-blue-500/20 px-2 py-0.5 text-blue-400 font-semibold">
          <span class="h-1 w-1 rounded-full bg-blue-400"></span>
          C {{ carbsG }}<span class="ml-0.5 opacity-70">g</span>
        </span>
        <span v-if="fatG > 0" class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 text-amber-400 font-semibold">
          <span class="h-1 w-1 rounded-full bg-amber-400"></span>
          G {{ fatG }}<span class="ml-0.5 opacity-70">g</span>
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

// Paleta CONTROLADA — solo 3 acentos + neutral fallback. Sin pink/indigo (muy
// femeninos). Reglas:
//   - Desayuno → amber (warm energy)
//   - Pre-entreno / post-entreno → emerald (semantic action OK)
//   - Almuerzo / Cena → wc-accent (rojo brand WellCore)
//   - Snack / Merienda / fallback → slate neutral
const mealColorScheme = computed(() => {
  const n = (props.meal.nombre || props.meal.name || '').toLowerCase();
  if (n.includes('desayuno')) {
    return { bg: 'bg-amber-500/10', border: 'border-amber-500/30', num: 'text-amber-400', time: 'text-amber-400/80' };
  }
  if (n.includes('pre-entreno') || n.includes('pre entreno') || n.includes('post-entreno') || n.includes('post entreno')) {
    return { bg: 'bg-emerald-500/10', border: 'border-emerald-500/30', num: 'text-emerald-400', time: 'text-emerald-400/80' };
  }
  if (n.includes('almuerzo') || n.includes('cena')) {
    return { bg: 'bg-wc-accent/10', border: 'border-wc-accent/30', num: 'text-wc-accent', time: 'text-wc-accent/80' };
  }
  // Snacks, merienda, otros — neutral
  return { bg: 'bg-wc-bg-tertiary', border: 'border-wc-border-strong', num: 'text-wc-text-tertiary', time: 'text-wc-text-secondary' };
});

const indexBadgeClass = computed(() => `${mealColorScheme.value.bg} ${mealColorScheme.value.border}`);
const indexNumClass = computed(() => mealColorScheme.value.num);
const indexTimeClass = computed(() => mealColorScheme.value.time);
</script>
