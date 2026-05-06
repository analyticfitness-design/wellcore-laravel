<template>
  <div
    class="rounded-full bg-wc-bg-secondary overflow-hidden w-full"
    :style="{ height: height + 'px' }"
    role="img"
    :aria-label="ariaLabel"
  >
    <div class="flex h-full">
      <div
        class="bg-red-400 transition-[width] duration-700 ease-out"
        :style="{ width: protPct + '%' }"
      ></div>
      <div
        class="bg-blue-400 transition-[width] duration-700 ease-out"
        :style="{ width: carbPct + '%' }"
      ></div>
      <div
        class="bg-amber-400 transition-[width] duration-700 ease-out"
        :style="{ width: fatPct + '%' }"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  proteinG: { type: Number, required: true },
  carbsG: { type: Number, required: true },
  fatG: { type: Number, required: true },
  totalKcal: { type: Number, default: 0 },
  height: { type: Number, default: 6 },
});

const pKcal = computed(() => Math.max(0, props.proteinG) * 4);
const cKcal = computed(() => Math.max(0, props.carbsG) * 4);
const fKcal = computed(() => Math.max(0, props.fatG) * 9);

const totalRef = computed(() => {
  if (props.totalKcal && props.totalKcal > 0) return props.totalKcal;
  return pKcal.value + cKcal.value + fKcal.value;
});

const protPct = computed(() => {
  const t = totalRef.value;
  if (!t || t <= 0) return 0;
  return Math.round((pKcal.value / t) * 100);
});

const carbPct = computed(() => {
  const t = totalRef.value;
  if (!t || t <= 0) return 0;
  return Math.round((cKcal.value / t) * 100);
});

// Último segmento toma el resto para sumar 100% exacto.
const fatPct = computed(() => {
  const t = totalRef.value;
  if (!t || t <= 0) return 0;
  const rest = 100 - protPct.value - carbPct.value;
  return Math.max(0, rest);
});

const ariaLabel = computed(
  () =>
    `Distribución de macros: ${protPct.value}% proteína, ${carbPct.value}% carbohidratos, ${fatPct.value}% grasas`
);
</script>
