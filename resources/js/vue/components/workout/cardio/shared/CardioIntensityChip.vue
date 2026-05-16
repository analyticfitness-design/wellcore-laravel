<script setup>
/**
 * CardioIntensityChip.vue
 *
 * Chip compacto que muestra zona FC + RPE + descripción del coach.
 * Diseño: el cliente NO ve "Z2" como dato principal — ve la descripción
 * en lenguaje cliente y el dato técnico queda como subtexto.
 */
import { computed } from 'vue';

const props = defineProps({
    intensity: { type: Object, default: () => ({}) },
});

const zoneColor = computed(() => {
    const z = parseInt(props.intensity?.zona_fc || 0);
    if (z <= 2) return 'cardio-intensity--low';
    if (z === 3) return 'cardio-intensity--moderate';
    if (z === 4) return 'cardio-intensity--high';
    return 'cardio-intensity--max';
});

const hasContent = computed(() => {
    const i = props.intensity || {};
    return !!(i.descripcion_cliente || i.rpe || i.zona_fc);
});
</script>

<template>
  <div v-if="hasContent" class="cardio-intensity" :class="zoneColor">
    <div v-if="intensity.descripcion_cliente" class="cardio-intensity__desc">
      {{ intensity.descripcion_cliente }}
    </div>
    <div class="cardio-intensity__meta">
      <span v-if="intensity.zona_fc" class="cardio-intensity__pill">Z{{ intensity.zona_fc }}</span>
      <span v-if="intensity.rpe" class="cardio-intensity__pill">RPE {{ intensity.rpe }}</span>
      <span v-if="intensity.porcentaje_fcmax" class="cardio-intensity__pill cardio-intensity__pill--muted">
        {{ intensity.porcentaje_fcmax }}% FCmax
      </span>
    </div>
  </div>
</template>

<style scoped>
.cardio-intensity {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 10px 12px;
  border-radius: 8px;
  border-left: 3px solid;
  background: rgba(255, 255, 255, 0.03);
  font-size: 13px;
}
.cardio-intensity__desc {
  color: rgb(229, 231, 235);
  line-height: 1.4;
}
.cardio-intensity__meta {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}
.cardio-intensity__pill {
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.08);
  color: rgb(229, 231, 235);
  font-weight: 600;
  letter-spacing: 0.02em;
}
.cardio-intensity__pill--muted {
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: rgb(156, 163, 175);
  font-weight: 500;
}
.cardio-intensity--low      { border-color: rgb(34, 197, 94); }
.cardio-intensity--moderate { border-color: rgb(59, 130, 246); }
.cardio-intensity--high     { border-color: rgb(245, 158, 11); }
.cardio-intensity--max      { border-color: rgb(220, 38, 38); }
</style>
