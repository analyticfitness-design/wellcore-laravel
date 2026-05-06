<template>
  <div class="objetivo-banner" data-testid="plan-objetivo-banner">
    <div class="objetivo-banner__icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
      </svg>
    </div>
    <div class="objetivo-banner__main">
      <div class="objetivo-banner__lab">Objetivo del bloque</div>
      <p class="objetivo-banner__body">{{ objetivo }}</p>
    </div>
  </div>
</template>

<script setup>
// PlanObjetivoBanner — bloque del objetivo de entrenamiento.
// CSS lines 723-745 del HTML V2.1.
defineProps({
  objetivo: { type: String, required: true },
});
</script>

<style scoped>
.objetivo-banner {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px 18px;
  border-radius: 16px;
  border: 1px solid rgba(220, 38, 38, 0.25);
  background: linear-gradient(135deg, rgba(220, 38, 38, 0.08), transparent 60%), var(--wc-bg-secondary);
}
.objetivo-banner__icon {
  width: 36px;
  height: 36px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  background: rgba(220, 38, 38, 0.15);
  color: #EF4444;
}
.objetivo-banner__icon svg { width: 18px; height: 18px; }
.objetivo-banner__main {
  flex: 1;
  min-width: 0;
}
.objetivo-banner__lab {
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 10px;
  letter-spacing: 0.20em;
  text-transform: uppercase;
  color: #EF4444;
  margin-bottom: 4px;
}
.objetivo-banner__body {
  font-size: 14px;
  line-height: 1.55;
  color: var(--wc-text-secondary);
  margin: 0;
}
</style>
