<template>
  <div
    v-if="totalKcal > 0"
    class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 sm:p-6 overflow-hidden"
  >
    <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-4 sm:gap-6 items-start">
      <!-- Lado izquierdo: kcal hero -->
      <div class="min-w-0">
        <p class="text-xs uppercase tracking-widest text-wc-text-secondary mb-1">
          Kcal diarias
        </p>
        <p
          class="font-display text-[64px] sm:text-[96px] font-bold leading-none text-wc-text tabular-nums"
        >
          {{ totalKcal.toLocaleString() }}
        </p>
        <p class="mt-1 text-base text-wc-text-tertiary">kcal por día</p>
      </div>

      <!-- Lado derecho: pull-quote del coach -->
      <div
        v-if="objetivoText"
        class="relative rounded-xl border border-wc-accent/30 bg-wc-accent/5 px-4 py-3 sm:max-w-xs"
      >
        <span
          aria-hidden="true"
          class="absolute -top-2 left-2 font-display text-2xl text-wc-accent/30 leading-none select-none"
        >&laquo;</span>
        <p class="text-sm text-wc-accent leading-relaxed">
          {{ objetivoText }}
        </p>
        <span
          aria-hidden="true"
          class="absolute -bottom-3 right-2 font-display text-2xl text-wc-accent/30 leading-none select-none"
        >&raquo;</span>
      </div>
    </div>

    <!-- Stacked bar de macros (6px) -->
    <div v-if="hasMacros" class="mt-5">
      <MacrosBar
        :total-kcal="totalKcal"
        :protein-g="macroP"
        :carbs-g="macroC"
        :fat-g="macroF"
        :height="6"
      />
    </div>

    <!-- Leyenda con 3 macros y porcentajes -->
    <div v-if="hasMacros" class="mt-4">
      <MacroLegend
        :total-kcal="totalKcal"
        :protein-g="macroP"
        :carbs-g="macroC"
        :fat-g="macroF"
        layout="grid"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import MacrosBar from './MacrosBar.vue';
import MacroLegend from './MacroLegend.vue';

const props = defineProps({
  nutritionPlan: {
    type: Object,
    required: true,
  },
});

// kcal totales — soporta múltiples nombres de keys del backend.
const totalKcal = computed(() => {
  const p = props.nutritionPlan;
  if (!p) return 0;
  return (
    Number(
      p.objetivo_cal ??
        p.objetivo_calorico ??
        p.calorias_diarias ??
        p.calorias ??
        0,
    ) || 0
  );
});

// Texto objetivo del coach (pull-quote).
const objetivoText = computed(() => {
  const v = props.nutritionPlan?.objetivo;
  return typeof v === 'string' && v.trim().length > 0 ? v.trim() : '';
});

// Macros — replicado de PlanViewer.vue para soportar es/en + anidado en `macros`.
const macroP = computed(() => {
  const p = props.nutritionPlan;
  if (!p) return 0;
  return (
    Number(
      p.macros?.proteina_g ??
        p.macros?.proteinas_g ??
        p.macros?.protein_g ??
        p.macros?.proteina ??
        p.proteina_g ??
        p.proteinas_g ??
        p.protein_g ??
        0,
    ) || 0
  );
});

const macroC = computed(() => {
  const p = props.nutritionPlan;
  if (!p) return 0;
  return (
    Number(
      p.macros?.carbohidratos_g ??
        p.macros?.carbs_g ??
        p.macros?.carbohidratos ??
        p.carbohidratos_g ??
        p.carbs_g ??
        p.carbohidratos ??
        0,
    ) || 0
  );
});

const macroF = computed(() => {
  const p = props.nutritionPlan;
  if (!p) return 0;
  return (
    Number(
      p.macros?.grasas_g ??
        p.macros?.fat_g ??
        p.macros?.grasas ??
        p.grasas_g ??
        p.fat_g ??
        p.grasas ??
        0,
    ) || 0
  );
});

const hasMacros = computed(
  () => macroP.value > 0 || macroC.value > 0 || macroF.value > 0,
);
</script>
