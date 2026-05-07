<script setup>
import { computed } from 'vue';
import DeltaBadge from './DeltaBadge.vue';

const props = defineProps({
  currentWeight: { type: [Number, String], default: null },
  weightChange: { type: Number, default: null },
  streak: { type: Number, default: 0 },
  lastDate: { type: String, default: null },
});

const formattedDate = computed(() => {
  if (!props.lastDate) return null;
  const d = new Date(props.lastDate + 'T00:00:00');
  return d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
});
</script>

<template>
  <header class="mhero">
    <!-- Breadcrumb -->
    <nav class="mhero-crumb" aria-label="breadcrumb">
      <span class="mhero-crumb-item">Dashboard</span>
      <span class="mhero-crumb-sep" aria-hidden="true">/</span>
      <span class="mhero-crumb-item mhero-crumb-item--active" aria-current="page">Métricas</span>
    </nav>

    <!-- Title row -->
    <div class="mhero-row">
      <h1 class="mhero-title">Métricas</h1>
      <div class="mhero-badges">
        <span v-if="streak > 0" class="mhero-streak" :title="`${streak} semanas consecutivas`">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M13.5 2c0 0-9 7.5-9 13.5a9 9 0 0 0 18 0C22.5 9.5 13.5 2 13.5 2Z"/>
          </svg>
          {{ streak }}sem
        </span>
        <DeltaBadge v-if="weightChange !== null" :delta="weightChange" unit="kg" />
      </div>
    </div>

    <!-- Sub row -->
    <div class="mhero-meta">
      <p class="mhero-sub">Registra peso, medidas y composición corporal</p>
      <p v-if="formattedDate" class="mhero-last">
        Último registro: <time :datetime="lastDate">{{ formattedDate }}</time>
      </p>
    </div>
  </header>
</template>

<style scoped>
.mhero { padding: 0 0 24px; }
.mhero-crumb {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 12px;
}
.mhero-crumb-item {
  font-family: var(--font-mono);
  font-size: 11px;
  letter-spacing: .06em;
  color: var(--color-wc-text-tertiary);
  text-transform: uppercase;
}
.mhero-crumb-item--active { color: var(--color-wc-text-secondary); }
.mhero-crumb-sep { color: var(--color-wc-text-tertiary); font-size: 10px; }
.mhero-row {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}
.mhero-title {
  font-family: var(--font-display);
  font-size: clamp(28px, 5vw, 40px);
  font-weight: 400;
  letter-spacing: .04em;
  text-transform: uppercase;
  color: var(--color-wc-text);
  margin: 0;
  line-height: 1;
}
.mhero-badges { display: flex; align-items: center; gap: 8px; }
.mhero-streak {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 999px;
  background: rgba(245,158,11,.12);
  border: 1px solid rgba(245,158,11,.20);
  color: #F59E0B;
  font-family: var(--font-mono);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: .04em;
  font-variant-numeric: tabular-nums;
}
.mhero-meta {
  display: flex;
  align-items: baseline;
  gap: 16px;
  margin-top: 8px;
  flex-wrap: wrap;
}
.mhero-sub {
  font-size: 13.5px;
  color: var(--color-wc-text-tertiary);
  margin: 0;
}
.mhero-last {
  font-family: var(--font-mono);
  font-size: 11px;
  color: var(--color-wc-text-tertiary);
  letter-spacing: .04em;
  margin: 0;
  opacity: .7;
}
</style>
