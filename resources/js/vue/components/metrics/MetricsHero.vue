<script setup>
import { computed } from 'vue';

const props = defineProps({
  currentWeight: { type: [Number, String], default: null },
  weightChange: { type: Number, default: null },
});

const changeColor = computed(() => {
  if (props.weightChange === null) return 'var(--wc-text-2)';
  return props.weightChange > 0
    ? 'var(--wc-amber)'
    : props.weightChange < 0
      ? 'var(--wc-green)'
      : 'var(--wc-text)';
});
</script>

<template>
  <section class="hero grain section" :style="{ animationDelay: '40ms' }">
    <div class="hero-content">
      <div class="hero-greeting tight">
        <span class="name">Métricas</span>
      </div>
      <div class="hero-row">
        <span v-if="currentWeight" class="chip chip-accent tnum">
          {{ Number(currentWeight).toFixed(1) }} kg
        </span>
        <span
          v-if="weightChange !== null"
          class="chip"
          :style="{
            color: changeColor,
            background: 'rgba(255,255,255,.04)',
            boxShadow: 'inset 0 0 0 1px rgba(255,255,255,.06)',
          }"
        >
          {{ weightChange > 0 ? '+' : '' }}{{ Number(weightChange).toFixed(1) }} kg este mes
        </span>
      </div>
      <p class="hero-sub">Registra tu peso, medidas corporales y composición.</p>
    </div>
  </section>
</template>
