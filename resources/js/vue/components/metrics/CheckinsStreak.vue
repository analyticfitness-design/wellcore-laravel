<script setup>
import { computed } from 'vue';

const props = defineProps({
  weeklyCheckins: { type: Array, default: () => [] },
});

// Build a 12-cell grid; fill from weeklyCheckins data
const cells = computed(() => {
  const maxWeeks = 12;
  const filled = [...props.weeklyCheckins].slice(-maxWeeks);
  // Pad with empty cells to always show 12
  const result = [];
  for (let i = 0; i < maxWeeks; i++) {
    const data = filled[i] || null;
    if (!data) { result.push({ status: 'empty', cnt: 0 }); continue; }
    const cnt = data.cnt || 0;
    result.push({
      status: cnt >= 3 ? 'full' : cnt > 0 ? 'partial' : 'miss',
      cnt,
    });
  }
  return result;
});

const attendancePct = computed(() => {
  if (!props.weeklyCheckins.length) return 0;
  const total = props.weeklyCheckins.length;
  const present = props.weeklyCheckins.filter(w => (w.cnt || 0) > 0).length;
  return Math.round((present / total) * 100);
});
</script>

<template>
  <div class="checkins-streak">
    <div class="streak-hd">
      <p class="streak-label">Check-ins Semanales</p>
      <p class="streak-sub">Últimas 12 semanas</p>
    </div>

    <div v-if="weeklyCheckins.length" class="streak-grid" role="list" aria-label="Historial de check-ins">
      <div
        v-for="(cell, i) in cells"
        :key="i"
        class="streak-cell"
        :class="{
          'streak-cell--full': cell.status === 'full',
          'streak-cell--partial': cell.status === 'partial',
          'streak-cell--miss': cell.status === 'miss',
        }"
        role="listitem"
        :title="`Semana ${i + 1}: ${cell.cnt} check-in(s)`"
      ></div>
    </div>

    <p v-else class="streak-empty">Sin check-ins recientes</p>

    <div v-if="weeklyCheckins.length" class="streak-meta">
      <span>12 semanas</span>
      <span class="tnum">{{ attendancePct }}% asistencia</span>
    </div>
  </div>
</template>

<style scoped>
.checkins-streak { padding: 16px 20px; }
.streak-hd { margin-bottom: 14px; }
.streak-label {
  font: 600 12px/1 var(--font-display);
  text-transform: uppercase;
  letter-spacing: .06em;
  color: var(--color-wc-text);
  margin: 0;
}
.streak-sub {
  font-size: 11px;
  color: var(--color-wc-text-tertiary);
  margin-top: 2px;
}
.streak-grid {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  gap: 6px;
  margin-top: 14px;
}
.streak-cell {
  aspect-ratio: 1;
  border-radius: 6px;
  background: var(--color-wc-bg);
  border: 1px solid var(--color-wc-border);
}
.streak-cell--full { background: var(--color-wc-success, #10B981); border-color: transparent; }
.streak-cell--partial { background: rgba(16,185,129,.45); border-color: transparent; }
.streak-cell--miss { background: var(--color-wc-bg); border-color: var(--color-wc-border); }
.streak-meta {
  display: flex;
  justify-content: space-between;
  margin-top: 14px;
  font-family: var(--font-mono);
  font-size: 11px;
  color: var(--color-wc-text-tertiary);
  letter-spacing: .04em;
}
.tnum { font-variant-numeric: tabular-nums; }
.streak-empty {
  font-size: 13px;
  color: var(--color-wc-text-tertiary);
  text-align: center;
  padding: 24px 0;
  margin: 0;
}
</style>
