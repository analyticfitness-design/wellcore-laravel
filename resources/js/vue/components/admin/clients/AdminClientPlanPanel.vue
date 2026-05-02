<script setup>
import { computed } from 'vue';
import { useAdminClientDetailStore } from '../../../stores/adminClientDetail';

const props = defineProps({
    client: { type: Object, default: null },
});

const store = useAdminClientDetailStore();

const planDetails = computed(() => props.client?.planDetails || {});
const assignedPlans = computed(() => store.plans || []);
</script>

<template>
  <div class="plan-panel">
    <article class="card card--hero">
      <header class="card-head">
        <span class="card-eyebrow">PLAN ACTUAL</span>
        <span v-if="planDetails.startDate" class="line-mono">
          INICIO {{ planDetails.startDate }}
        </span>
      </header>

      <div v-if="planDetails.name" class="hero-body">
        <span class="plan-name">{{ planDetails.name }}</span>
        <div class="hero-stats">
          <div class="hero-stat">
            <span class="line-label">SEMANA</span>
            <span class="hero-value">{{ planDetails.currentWeek ?? '—' }}</span>
          </div>
          <div class="hero-stat">
            <span class="line-label">DURACION</span>
            <span class="hero-value">{{ planDetails.totalWeeks ?? '∞' }}</span>
          </div>
        </div>
      </div>

      <div v-else class="card-empty">
        <p class="empty-msg">"Sin plan asignado todavía."</p>
      </div>
    </article>

    <article class="card">
      <header class="card-head">
        <span class="card-eyebrow">PLANES ASIGNADOS · {{ assignedPlans.length }}</span>
      </header>

      <div v-if="assignedPlans.length" class="plans-list">
        <div
          v-for="plan in assignedPlans"
          :key="plan.id"
          class="plan-row"
          :class="{ 'plan-row--inactive': !plan.active }"
        >
          <div class="plan-row-main">
            <span class="plan-type-pill" :class="plan.active ? 'pill--success' : 'pill--neutral'">
              {{ (plan.plan_type || 'plan').toUpperCase() }}
            </span>
            <span class="plan-row-meta">v{{ plan.version }}</span>
          </div>
          <div class="plan-row-meta-line">
            <span class="line-mono">{{ plan.created_at || '—' }}</span>
            <span v-if="!plan.active" class="line-mono line-mono--dim">INACTIVO</span>
          </div>
        </div>
      </div>

      <div v-else class="card-empty">
        <p class="empty-msg">"Aún no se ha asignado un plan estructurado al cliente."</p>
      </div>
    </article>
  </div>
</template>

<style scoped>
.plan-panel { display: flex; flex-direction: column; gap: 12px; }

.card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.65);
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.card--hero { border-color: rgba(220, 38, 38, 0.2); }

.card-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.card-eyebrow {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.line-mono {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.4px;
    color: var(--c-text-3);
    text-transform: uppercase;
}
.line-mono--dim { opacity: 0.5; }
.line-label {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
    display: block;
    margin-bottom: 2px;
}

.hero-body {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.plan-name {
    font-family: var(--font-display);
    font-size: clamp(34px, 5vw, 52px);
    letter-spacing: 0.04em;
    color: var(--c-text);
    line-height: 1;
}
.hero-stats { display: flex; gap: 16px; }
.hero-stat { display: flex; flex-direction: column; align-items: flex-start; }
.hero-value {
    font-family: var(--font-display);
    font-feature-settings: 'tnum' 1;
    font-size: 28px;
    font-weight: 700;
    color: var(--c-text);
    line-height: 1;
}

.plans-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    border-top: 1px solid var(--c-border);
    padding-top: 8px;
}
.plan-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    gap: 12px;
}
.plan-row:last-child { border-bottom: none; }
.plan-row--inactive { opacity: 0.55; }
.plan-row-main { display: flex; align-items: center; gap: 8px; }
.plan-row-meta {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.2px;
    color: var(--c-text-3);
}
.plan-row-meta-line { display: flex; align-items: center; gap: 8px; }

.plan-type-pill,
.pill--success,
.pill--neutral {
    display: inline-block;
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: var(--r-pill, 999px);
    line-height: 1.4;
}
.pill--success { background: rgba(16,185,129,0.1); color: #34D399; }
.pill--neutral { background: rgba(255, 255, 255, 0.04); color: var(--c-text-3); }

.card-empty { padding: 8px 0; }
.empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: var(--c-text-3);
    margin: 0;
}
</style>
