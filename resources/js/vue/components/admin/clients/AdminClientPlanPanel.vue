<script setup>
import { computed } from 'vue';
import { useAdminClientDetailStore } from '../../../stores/adminClientDetail';

const props = defineProps({
    client: { type: Object, default: null },
});

const store = useAdminClientDetailStore();

const planDetails = computed(() => props.client?.planDetails || {});
const assignedPlans = computed(() => store.plans || []);
const membership = computed(() => store.membership || null);
const recentExtensions = computed(() => store.recentExtensions || []);

const hasMonthlyPlan = computed(() => !!membership.value?.plan_type);
const isLocked = computed(() => membership.value?.is_locked === true);
const isInGrace = computed(() => membership.value?.is_in_grace === true);
const daysUntil = computed(() => membership.value?.days_until_expiry);
const expiresFormatted = computed(() => membership.value?.expires_at_formatted);

const stateLabel = computed(() => {
    if (!hasMonthlyPlan.value) return { text: 'SIN PLAN MENSUAL', cls: 'pill--neutral' };
    if (isLocked.value) return { text: 'VENCIDO', cls: 'pill--danger' };
    if (isInGrace.value) return { text: 'POR VENCER', cls: 'pill--warn' };
    return { text: 'AL DÍA', cls: 'pill--success' };
});
</script>

<template>
  <div class="plan-panel">
    <article class="card card--membership">
      <header class="card-head">
        <span class="card-eyebrow">MEMBRESÍA</span>
        <span class="plan-type-pill" :class="stateLabel.cls">{{ stateLabel.text }}</span>
      </header>

      <div class="membership-grid">
        <div class="m-stat">
          <span class="line-label">FECHA DE CORTE</span>
          <span class="m-value">{{ expiresFormatted || '—' }}</span>
        </div>
        <div class="m-stat">
          <span class="line-label">DÍAS RESTANTES</span>
          <span class="m-value" :class="{ 'm-value--danger': isLocked, 'm-value--warn': isInGrace }">
            {{ daysUntil !== null && daysUntil !== undefined ? daysUntil : '—' }}
          </span>
        </div>
      </div>

      <button
        type="button"
        class="extend-btn"
        @click="store.openExtendModal()"
      >
        Extender membresía
      </button>

      <p v-if="!hasMonthlyPlan" class="info-msg">
        Este cliente no tiene plan mensual (rise/presencial/trial siguen flujos distintos).
      </p>

      <div v-if="recentExtensions.length" class="history">
        <div class="history-head">
          <span class="card-eyebrow">HISTORIAL · ÚLTIMAS {{ recentExtensions.length }}</span>
        </div>
        <ul class="history-list">
          <li v-for="ext in recentExtensions" :key="ext.id" class="history-row">
            <div class="history-row-main">
              <span class="line-mono">{{ ext.created_at }}</span>
              <span class="plan-type-pill" :class="ext.actor_role === 'coach' ? 'pill--warn' : 'pill--neutral'">
                {{ ext.actor_role?.toUpperCase() || 'OPERADOR' }}
              </span>
              <span class="actor-name">{{ ext.actor_name }}</span>
            </div>
            <div class="history-row-meta">
              <span class="line-mono line-mono--dim">{{ ext.previous_expires_at || '—' }}</span>
              <span class="arrow">→</span>
              <span class="line-mono">{{ ext.new_expires_at }}</span>
            </div>
            <p v-if="ext.notes" class="history-notes">"{{ ext.notes }}"</p>
          </li>
        </ul>
      </div>
    </article>

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
            <span v-if="plan.expires_at" class="line-mono">EXP {{ plan.expires_at }}</span>
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
.card--membership { border-color: rgba(220, 38, 38, 0.35); }

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

.membership-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    padding: 10px 0;
    border-top: 1px solid var(--c-border);
    border-bottom: 1px solid var(--c-border);
}
.m-stat { display: flex; flex-direction: column; gap: 2px; }
.m-value {
    font-family: var(--font-display);
    font-feature-settings: 'tnum' 1;
    font-size: 18px;
    font-weight: 600;
    color: var(--c-text);
    line-height: 1;
}
.m-value--danger { color: #F87171; }
.m-value--warn { color: #FBBF24; }

.extend-btn {
    background: #DC2626;
    color: white;
    border: none;
    border-radius: var(--r-sm, 8px);
    padding: 10px 14px;
    font-family: var(--font-display);
    font-size: 11px;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s;
    align-self: flex-start;
}
.extend-btn:hover { background: #B91C1C; }

.info-msg {
    margin: 0;
    font-size: 12px;
    color: var(--c-text-3);
    font-style: italic;
}

.history {
    border-top: 1px solid var(--c-border);
    padding-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.history-head { display: flex; justify-content: space-between; }
.history-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.history-row {
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding: 6px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}
.history-row:last-child { border-bottom: none; }
.history-row-main { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.history-row-meta { display: flex; align-items: center; gap: 6px; font-size: 10px; }
.actor-name { font-size: 11px; color: var(--c-text); }
.arrow { color: var(--c-text-3); font-size: 11px; }
.history-notes {
    margin: 2px 0 0 0;
    font-size: 11px;
    color: var(--c-text-3);
    font-style: italic;
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
.plan-row-meta-line { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.plan-type-pill,
.pill--success,
.pill--neutral,
.pill--danger,
.pill--warn {
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
.pill--danger { background: rgba(220, 38, 38, 0.15); color: #F87171; }
.pill--warn { background: rgba(251, 191, 36, 0.12); color: #FBBF24; }

.card-empty { padding: 8px 0; }
.empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: var(--c-text-3);
    margin: 0;
}
</style>
