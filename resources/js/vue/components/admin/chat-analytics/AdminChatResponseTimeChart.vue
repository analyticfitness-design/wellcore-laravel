<script setup>
import { computed } from 'vue';

const props = defineProps({
    buckets: { type: Array, default: () => [] },
    stats:   { type: Object, default: () => ({ mean: 0, median: 0, p90: 0 }) },
    maxCount: { type: Number, default: 1 },
});

function formatMinutes(min) {
    if (!min || min === 0) return '—';
    if (min < 60) return `${min} min`;
    return `${(min / 60).toFixed(1)} h`;
}

const hasData = computed(() => props.buckets.some(b => b.count > 0));

const BUCKET_COLORS = {
    '<5min':    'var(--color-wc-green-text, #34D399)',
    '5-15min':  'var(--color-wc-green-text, #34D399)',
    '15-60min': 'var(--color-wc-blue-text,  #60A5FA)',
    '1-3h':     'var(--color-wc-blue-text,  #60A5FA)',
    '3-12h':    'var(--color-wc-amber-text, #FCD34D)',
    '+12h':     'var(--color-wc-red-text,   #F87171)',
};
</script>

<template>
  <div class="rt-card">
    <header class="rt-head">
      <div>
        <h2 class="rt-title">DISTRIBUCIÓN TIEMPO RESPUESTA</h2>
        <span class="rt-eyebrow">COACHES → CLIENTES</span>
      </div>
      <div class="rt-stats">
        <div class="rt-stat">
          <span class="stat-label">MEDIA</span>
          <span class="stat-val">{{ formatMinutes(stats.mean) }}</span>
        </div>
        <div class="rt-stat">
          <span class="stat-label">MEDIANA</span>
          <span class="stat-val">{{ formatMinutes(stats.median) }}</span>
        </div>
        <div class="rt-stat">
          <span class="stat-label">P90</span>
          <span class="stat-val">{{ formatMinutes(stats.p90) }}</span>
        </div>
      </div>
    </header>

    <div v-if="!hasData" class="rt-empty">
      <div class="empty-num">—</div>
      <p class="empty-msg">"Sin respuestas registradas en este período."</p>
    </div>

    <div v-else class="rt-bars">
      <div
        v-for="b in buckets"
        :key="b.bucket"
        class="bar-row"
      >
        <span class="bar-label">{{ b.bucket }}</span>
        <div class="bar-track" role="presentation">
          <div
            class="bar-fill"
            :style="{
              width: `${(b.count / (maxCount || 1)) * 100}%`,
              background: BUCKET_COLORS[b.bucket] || 'var(--color-wc-blue-text)',
            }"
          ></div>
        </div>
        <span class="bar-count">{{ b.count }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.rt-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17,17,17,0.7);
    padding: 18px;
}
.rt-head {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 10px; flex-wrap: wrap; margin-bottom: 16px;
}
.rt-title {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--color-wc-text); margin: 0 0 2px;
}
.rt-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 7px; letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.rt-stats { display: flex; gap: 14px; flex-wrap: wrap; }
.rt-stat { display: flex; flex-direction: column; gap: 1px; }
.stat-label {
    font-family: var(--font-mono, monospace);
    font-size: 7px; letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.stat-val {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 13px; font-weight: 600;
    color: var(--color-wc-text);
    font-feature-settings: 'tnum' 1;
}
.rt-empty { padding: 24px 8px; text-align: center; }
.empty-num {
    font-family: var(--font-display); font-size: 56px;
    color: var(--color-wc-bg-tertiary); letter-spacing: 0.1em;
    line-height: 1; margin-bottom: 12px; user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic; font-size: 12px;
    color: var(--color-wc-text-tertiary); margin: 0;
}
.rt-bars { display: flex; flex-direction: column; gap: 8px; }
.bar-row {
    display: grid;
    grid-template-columns: 66px 1fr 38px;
    align-items: center;
    gap: 10px;
}
.bar-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.12em; text-transform: uppercase;
    color: var(--color-wc-text-secondary);
    text-align: right;
}
.bar-track {
    height: 10px; border-radius: 5px;
    background: rgba(255,255,255,0.05);
    overflow: hidden;
}
.bar-fill {
    height: 100%; border-radius: 5px;
    transition: width 0.5s var(--ease-out, ease);
    min-width: 2px;
}
.bar-count {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 11px; font-weight: 600;
    color: var(--color-wc-text-secondary);
    font-feature-settings: 'tnum' 1;
    text-align: right;
}
@media (prefers-reduced-motion: reduce) {
    .bar-fill { transition: none !important; }
}
</style>
