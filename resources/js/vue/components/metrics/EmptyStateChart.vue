<script setup>
defineProps({
  title: { type: String, default: 'Sin datos aún' },
  message: { type: String, default: 'Registra tu primer dato para ver el gráfico.' },
  ctaText: { type: String, default: 'Registra tu primer dato →' },
  height: { type: String, default: '260px' },
});

const emit = defineEmits(['cta-click']);
</script>

<template>
  <div class="empty-chart" :style="{ height }">
    <!-- Ghost SVG line — no Chart.js para evitar flicker -->
    <svg class="ghost-svg" viewBox="0 0 400 120" preserveAspectRatio="none" aria-hidden="true">
      <defs>
        <linearGradient id="ghostGrad" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stop-color="rgba(255,255,255,0.04)"/>
          <stop offset="100%" stop-color="rgba(255,255,255,0)"/>
        </linearGradient>
      </defs>
      <!-- Ghost area fill -->
      <path d="M0,80 C60,60 120,90 180,55 C240,20 300,70 400,40 L400,120 L0,120 Z" fill="url(#ghostGrad)"/>
      <!-- Ghost line -->
      <path d="M0,80 C60,60 120,90 180,55 C240,20 300,70 400,40" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="2"/>
      <!-- Ghost dots -->
      <circle cx="0"   cy="80" r="3" fill="rgba(255,255,255,0.06)"/>
      <circle cx="100" cy="75" r="3" fill="rgba(255,255,255,0.06)"/>
      <circle cx="200" cy="50" r="3" fill="rgba(255,255,255,0.06)"/>
      <circle cx="300" cy="65" r="3" fill="rgba(255,255,255,0.06)"/>
      <circle cx="400" cy="40" r="3" fill="rgba(255,255,255,0.06)"/>
    </svg>

    <!-- Overlay -->
    <div class="empty-overlay">
      <h3 class="empty-title">{{ title }}</h3>
      <p class="empty-msg">{{ message }}</p>
      <button class="empty-cta" @click="emit('cta-click')">{{ ctaText }}</button>
    </div>
  </div>
</template>

<style scoped>
.empty-chart {
  position: relative;
  width: 100%;
  border-radius: 12px;
  overflow: hidden;
  background: linear-gradient(180deg, rgba(255,255,255,.015), rgba(255,255,255,0));
}
.ghost-svg {
  width: 100%;
  height: 100%;
  display: block;
}
.empty-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 14px;
  text-align: center;
  padding: 24px;
  background: radial-gradient(ellipse at center, rgba(9,9,11,.78) 0%, rgba(9,9,11,.30) 60%, transparent 100%);
}
.empty-title {
  font-family: var(--font-display);
  font-size: 16px;
  font-weight: 500;
  letter-spacing: .14em;
  text-transform: uppercase;
  color: var(--color-wc-text);
  margin: 0;
}
.empty-msg {
  font-size: 13px;
  color: var(--color-wc-text-secondary);
  max-width: 36ch;
  margin: 0;
}
.empty-cta {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 18px;
  min-height: 44px;
  border-radius: 10px;
  background: var(--color-wc-accent);
  color: #fff;
  font-family: var(--font-sans);
  font-size: 14px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: background .12s ease;
}
.empty-cta:hover { background: var(--color-wc-accent-hover); }
</style>
