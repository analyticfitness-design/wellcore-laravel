<template>
  <div
    v-if="totalKcal > 0"
    class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-6"
  >
    <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-[1fr_auto] sm:gap-6">
      <!-- Lado izquierdo: kcal hero (matching m-day-hero del HTML target v2) -->
      <div class="min-w-0">
        <p class="mb-1 text-[10px] uppercase tracking-[0.2em] text-wc-text-secondary sm:text-xs sm:tracking-widest">
          Calorías del día
        </p>
        <div class="flex flex-wrap items-baseline gap-x-2">
          <span class="font-display text-[56px] font-bold leading-none tabular-nums text-wc-text sm:text-[96px]">
            {{ totalKcal.toLocaleString('es-CO') }}
          </span>
          <span class="font-sans text-xs font-normal text-wc-text-tertiary sm:text-sm">kcal · objetivo</span>
        </div>
      </div>

      <!-- Lado derecho: objetivo con tag inline + descripción -->
      <div
        v-if="objetivoText"
        class="relative rounded-xl border border-wc-accent/30 bg-wc-accent/5 px-4 py-3 sm:max-w-xs"
      >
        <span class="inline-block rounded-full bg-wc-accent/20 px-2.5 py-0.5 mr-1.5 font-display text-[10px] uppercase tracking-[0.18em] text-wc-accent">
          {{ objetivoTag }}
        </span>
        <span class="text-sm text-wc-text-secondary leading-relaxed">
          {{ objetivoBody }}
        </span>
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

// Si el objetivo viene en formato corto (ej. "Volumen limpio") lo usa como tag completo.
// Si es largo (>40 chars), extrae primera frase como tag y resto como body descriptivo.
const objetivoTag = computed(() => {
  const t = objetivoText.value;
  if (!t) return '';
  if (t.length <= 40) return t;
  const firstSentence = t.split(/[.\-—]\s+/)[0] || t;
  return firstSentence.length <= 40 ? firstSentence : firstSentence.slice(0, 40);
});

const objetivoBody = computed(() => {
  const t = objetivoText.value;
  if (!t || t.length <= 40) return '';
  const tag = objetivoTag.value;
  return t.startsWith(tag) ? t.slice(tag.length).replace(/^[.\-—\s]+/, '').trim() : t;
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
