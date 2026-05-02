<script setup>
import { computed } from 'vue';

const props = defineProps({
  resolutionBuckets: {
    type: Object,
    default: () => ({ buckets: [], stats: {}, total: 0 }),
  },
});

const buckets = computed(() => props.resolutionBuckets?.buckets || []);
const stats   = computed(() => props.resolutionBuckets?.stats || {});
const total   = computed(() => props.resolutionBuckets?.total || 0);

const maxCount = computed(() => Math.max(1, ...buckets.value.map(b => b.count || 0)));
const isEmpty  = computed(() => total.value === 0);

function fmtH(v) {
  if (v === null || v === undefined) return '—';
  const n = Number(v);
  if (!Number.isFinite(n)) return '—';
  return n >= 24 ? `${(n / 24).toFixed(1)}d` : `${n.toFixed(1)}h`;
}
</script>

<template>
  <article class="resolution-card">
    <header class="res-head">
      <h2 class="res-title">TIEMPO DE RESOLUCION</h2>
      <span class="res-sub">desde creacion a cierre</span>
    </header>

    <!-- Stats row -->
    <div v-if="!isEmpty" class="stats-row">
      <div class="stat-item">
        <span class="stat-label">MEDIA</span>
        <span class="stat-value">{{ fmtH(stats.mean_hours) }}</span>
      </div>
      <div class="stat-item">
        <span class="stat-label">MEDIANA</span>
        <span class="stat-value">{{ fmtH(stats.median_hours) }}</span>
      </div>
      <div class="stat-item">
        <span class="stat-label">P90</span>
        <span class="stat-value stat-value--amber">{{ fmtH(stats.p90_hours) }}</span>
      </div>
      <div class="stat-item">
        <span class="stat-label">TOTAL</span>
        <span class="stat-value">{{ total }}</span>
      </div>
    </div>

    <!-- Histogram bars -->
    <div v-if="isEmpty" class="res-empty">
      <div class="res-empty-num">—</div>
      <p class="res-empty-msg">
        "Sin datos de resolucion en el periodo seleccionado."
      </p>
    </div>

    <div v-else class="histogram">
      <div
        v-for="bucket in buckets"
        :key="bucket.bucket"
        class="bucket-row"
      >
        <span class="bucket-label">{{ bucket.bucket }}</span>
        <div class="bucket-bar-track">
          <div
            class="bucket-bar"
            :style="{ width: `${(bucket.count / maxCount) * 100}%` }"
            :class="{ 'bucket-bar--empty': bucket.count === 0 }"
          ></div>
        </div>
        <span class="bucket-count">{{ bucket.count }}</span>
        <span class="bucket-pct">{{ bucket.pct }}%</span>
      </div>
    </div>
  </article>
</template>

<style scoped>
.resolution-card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 18px;
}
.res-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 14px;
    gap: 8px;
}
.res-title {
    font-family: var(--font-display);
    font-size: 13px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text);
    margin: 0;
}
.res-sub {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-3);
}

/* Stats row */
.stats-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    padding-bottom: 14px;
    margin-bottom: 14px;
    border-bottom: 1px solid var(--c-border);
}
.stat-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.stat-label {
    font-family: var(--font-display);
    font-size: 7px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.stat-value {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 700;
    color: var(--c-text);
    font-variant-numeric: tabular-nums;
}
.stat-value--amber { color: #FCD34D; }

/* Empty */
.res-empty {
    padding: 18px 8px 14px;
    text-align: center;
}
.res-empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--c-surface-2);
    letter-spacing: 0.8px;
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}
.res-empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: var(--c-text-3);
    line-height: 1.55;
    margin: 0;
    text-wrap: balance;
}

/* Histogram */
.histogram {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.bucket-row {
    display: grid;
    grid-template-columns: 44px 1fr 36px 36px;
    align-items: center;
    gap: 8px;
}
.bucket-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.0px;
    color: var(--c-text-3);
    text-align: right;
}
.bucket-bar-track {
    height: 14px;
    background: rgba(255,255,255,0.04);
    border-radius: 4px;
    overflow: hidden;
}
.bucket-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--c-accent), rgba(220,38,38,0.5));
    border-radius: 4px;
    transition: width 0.6s var(--ease-out, ease);
    min-width: 2px;
}
.bucket-bar--empty {
    background: rgba(255,255,255,0.06);
    min-width: 0;
    width: 0 !important;
}
.bucket-count {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 700;
    color: var(--c-text);
    text-align: right;
    font-variant-numeric: tabular-nums;
}
.bucket-pct {
    font-family: var(--font-display);
    font-size: 8px;
    color: var(--c-text-3);
    text-align: right;
}

@media (prefers-reduced-motion: reduce) {
    .bucket-bar { transition: none !important; }
}
</style>
