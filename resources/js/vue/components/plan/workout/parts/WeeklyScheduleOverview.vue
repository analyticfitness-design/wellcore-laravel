<template>
  <div v-if="rows.length" class="weekly-schedule" data-testid="weekly-schedule">
    <div
      v-for="(row, idx) in rows"
      :key="idx"
      class="ws-row"
    >
      <div class="ws-day">{{ row.letter }}</div>
      <div class="ws-info">
        <p>{{ row.label }}</p>
        <p>{{ row.group }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
// WeeklyScheduleOverview — split semanal (L M X J V S).
// CSS lines 747-772 del HTML V2.1.
import { computed } from 'vue';

const props = defineProps({
  days: {
    type: Array,
    default: () => [],
  },
});

// Defensive: backend may give weekly_schedule with day_letter/day_label/muscle_groups
// or the parent may already map to {label, letter, group}.
const rows = computed(() => {
  if (!Array.isArray(props.days)) return [];
  return props.days
    .map((d) => {
      if (!d) return null;
      const letter = d.letter || d.day_letter || d.dia || '';
      const label = d.label || d.day_label || d.dayLabel || d.weekday || '';
      const group = d.group || d.muscle_groups || d.muscleGroups || d.grupos || d.foco || '';
      if (!letter && !label) return null;
      return {
        letter: String(letter).toUpperCase().slice(0, 1),
        label: String(label),
        group: String(group),
      };
    })
    .filter(Boolean);
});
</script>

<style scoped>
.weekly-schedule {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}
@media (max-width: 600px) {
  .weekly-schedule { grid-template-columns: 1fr; }
}
.ws-row {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 12px;
  border: 1px solid var(--wc-border);
  background: var(--wc-bg-secondary);
}
.ws-day {
  width: 36px;
  height: 36px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  background: rgba(220, 38, 38, 0.08);
  font-family: var(--font-display, 'Oswald', Impact, sans-serif);
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 0.08em;
  color: #EF4444;
}
.ws-info {
  flex: 1;
  min-width: 0;
}
.ws-info p {
  margin: 0;
}
.ws-info p:first-child {
  font-family: var(--font-display, 'Oswald', Impact, sans-serif);
  font-size: 12px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--wc-text);
  line-height: 1.2;
}
.ws-info p:last-child {
  margin-top: 3px;
  font-size: 13px;
  line-height: 1.4;
  color: var(--wc-text-tertiary);
}
</style>
