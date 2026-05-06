<template>
  <div class="empty-state" data-testid="plan-empty-state">
    <div class="empty-state__icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
      </svg>
    </div>
    <h3>Tu coach está armando tu plan</h3>
    <p>{{ resolvedCoach }} lo va a publicar en cuanto termine la planificación. Te avisamos por notificación cuando esté listo.</p>
    <span class="empty-state__pulse">En preparación</span>
  </div>
</template>

<script setup>
// PlanEmptyState — sin plan asignado todavía.
// CSS lines 845-873 del HTML V2.1.
import { computed } from 'vue';

const props = defineProps({
  coachName: { type: String, default: '' },
});

const resolvedCoach = computed(() => {
  const n = (props.coachName || '').trim();
  return n.length ? n : 'Tu coach';
});
</script>

<style scoped>
.empty-state {
  text-align: center;
  padding: 48px 32px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
  border-radius: 16px;
  border: 1px solid var(--wc-border);
  background: var(--wc-bg-secondary);
}
.empty-state__icon {
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 16px;
  background: var(--wc-bg-tertiary);
  border: 1px solid var(--wc-border);
}
.empty-state__icon svg {
  width: 26px;
  height: 26px;
  color: var(--wc-text-secondary);
}
.empty-state h3 {
  font-family: var(--font-display, 'Oswald', Impact, sans-serif);
  font-size: 20px;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--wc-text);
  margin: 0;
}
.empty-state p {
  font-size: 14px;
  line-height: 1.55;
  color: var(--wc-text-secondary);
  max-width: 340px;
  margin: 0;
}
.empty-state__pulse {
  margin-top: 8px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 10px;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: #EF4444;
}
.empty-state__pulse::before {
  content: '';
  width: 6px;
  height: 6px;
  border-radius: 999px;
  background: #EF4444;
  box-shadow: 0 0 6px #EF4444;
  animation: pulse-dot 1.6s ease-out infinite;
}
@keyframes pulse-dot {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.30; }
}
</style>
