<script setup>
defineProps({
  planName: { type: String, default: 'Mi Plan' },
  currentWeek: { type: Number, default: 1 },
  totalWeeks: { type: Number, default: 12 },
  adherence: { type: Number, default: 0 },
});
</script>

<template>
  <section class="hero grain section" :style="{ animationDelay: '40ms' }">
    <div class="hero-content">
      <div class="hero-greeting tight">
        <span class="name">{{ planName }}</span>
      </div>
      <div class="hero-row">
        <span class="chip chip-accent">Semana {{ currentWeek }} / {{ totalWeeks }}</span>
        <span v-if="adherence > 0" class="chip" style="background:rgba(16,185,129,.12); color:var(--wc-green); box-shadow:inset 0 0 0 1px rgba(16,185,129,.25);">
          {{ adherence }}% adherencia
        </span>
      </div>
      <p class="hero-sub">Tu programa personalizado de entrenamiento y nutrición.</p>
    </div>
  </section>
</template>
