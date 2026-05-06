<template>
  <div
    @click="$emit('toggle')"
    role="button"
    tabindex="0"
    @keydown.enter="$emit('toggle')"
    @keydown.space.prevent="$emit('toggle')"
    class="flex w-full cursor-pointer items-center gap-3 p-4 text-left transition hover:bg-wc-bg-tertiary"
  >
    <!-- Colored number badge -->
    <div
      class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
      :class="badgeColorClass"
    >
      <span class="font-data text-sm font-bold">{{ meal.numero ?? (mealIdx + 1) }}</span>
    </div>

    <!-- Name + hora -->
    <div class="min-w-0 flex-1">
      <p class="truncate font-display text-sm tracking-wide text-wc-text">
        {{ (meal.nombre || meal.name || ('Comida ' + (mealIdx + 1))).toUpperCase() }}
      </p>
      <p v-if="meal.hora || meal.time" class="text-sm text-wc-text-tertiary">
        {{ meal.hora || meal.time }}
      </p>
    </div>

    <!-- Macro chips desktop only -->
    <div class="hidden items-center gap-1.5 sm:flex">
      <span
        v-if="proteinG > 0"
        class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
        style="background:rgba(220,38,38,0.12); color:#F87171;"
      >P {{ proteinG }}g</span>
      <span
        v-if="carbsG > 0"
        class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
        style="background:rgba(59,130,246,0.12); color:#60A5FA;"
      >C {{ carbsG }}g</span>
      <span
        v-if="fatG > 0"
        class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
        style="background:rgba(245,158,11,0.12); color:#FBBF24;"
      >G {{ fatG }}g</span>
    </div>

    <!-- Swap CTA -->
    <button
      type="button"
      @click.stop="$emit('open-swap')"
      :title="swapped ? 'Cambiar por otra receta' : 'Cambiar por receta'"
      :class="{ 'text-wc-accent bg-wc-accent/10': swapPanelOpen }"
      class="wc-swap-ghost group/swap ml-1 sm:ml-2 inline-flex shrink-0 items-center gap-1.5 rounded-full border border-wc-border bg-wc-bg-secondary/50 px-2 py-1.5 sm:px-2.5 text-wc-text-secondary transition-all duration-300 ease-out hover:bg-wc-bg-secondary hover:text-wc-accent hover:border-wc-accent/30 active:scale-95 min-h-[36px]"
      aria-label="Cambiar receta"
    >
      <Replace :size="14" :stroke-width="2.5" class="shrink-0 transition-transform duration-300 group-hover/swap:rotate-180" />
      <span class="hidden font-display text-[10px] tracking-[0.2em] sm:inline">CAMBIAR</span>
    </button>

    <!-- kcal + chevron -->
    <div class="ml-2 flex shrink-0 items-center gap-3">
      <span v-if="kcalValue" class="font-data text-sm font-bold tabular-nums text-wc-text">
        {{ kcalValue }}<span class="text-xs font-normal text-wc-text-tertiary"> kcal</span>
      </span>
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
import { Replace, ChevronDown } from 'lucide-vue-next';

const props = defineProps({
  meal: { type: Object, required: true },
  mealIdx: { type: Number, required: true },
  expanded: { type: Boolean, default: false },
  swapPanelOpen: { type: Boolean, default: false },
  swapped: { type: Boolean, default: false },
});

defineEmits(['toggle', 'open-swap']);

const proteinG = computed(() => props.meal.macros?.proteina ?? props.meal.macros?.proteina_g ?? 0);
const carbsG = computed(() => props.meal.macros?.carbohidratos ?? props.meal.macros?.carbohidratos_g ?? 0);
const fatG = computed(() => props.meal.macros?.grasas ?? props.meal.macros?.grasas_g ?? 0);
const kcalValue = computed(() => props.meal.kcal ?? props.meal.calorias ?? props.meal.calories ?? null);

const badgeColorClass = computed(() => {
  const n = (props.meal.nombre || props.meal.name || '').toLowerCase();
  if (n.includes('desayuno')) return 'bg-amber-500/10 text-amber-400';
  if (n.includes('pre-entreno') || n.includes('pre entreno')) return 'bg-green-500/10 text-green-400';
  if (n.includes('almuerzo') || n.includes('post-entreno') || n.includes('post entreno')) return 'bg-blue-500/10 text-blue-400';
  if (n.includes('cena')) return 'bg-indigo-500/10 text-indigo-400';
  if (n.includes('snack') || n.includes('merienda') || n.includes('media mañana') || n.includes('media manana')) return 'bg-pink-500/10 text-pink-400';
  return 'bg-wc-accent/10 text-wc-accent';
});
</script>
