<script setup>
import { computed, ref, watch } from 'vue';
import { useAdminClientDetailStore } from '../../../stores/adminClientDetail';

const store = useAdminClientDetailStore();

const props = defineProps({
    client: { type: Object, default: null },
});

const emit = defineEmits(['edit', 'assign-coach']);

const localStatus = ref('');
const localPlan = ref('');

// Sincronizamos el select local con la data cargada
watch(() => props.client, (c) => {
    const status = typeof c?.status === 'string' ? c.status : (c?.status?.value || '');
    const plan = typeof c?.plan === 'string' ? c.plan : (c?.plan?.value || '');
    if (status) localStatus.value = status;
    if (plan) localPlan.value = plan;
}, { immediate: true });

const statusOptions = computed(() => store.statusOptions || []);
const planOptions = computed(() => store.planOptions || []);

const membership = computed(() => store.membership || null);
const hasMonthlyPlan = computed(() => !!membership.value?.plan_type);
const isLocked = computed(() => membership.value?.is_locked === true);
const isInGrace = computed(() => membership.value?.is_in_grace === true);
const daysUntil = computed(() => membership.value?.days_until_expiry);
const expiresFormatted = computed(() => membership.value?.expires_at_formatted);

const stateLabel = computed(() => {
    if (!hasMonthlyPlan.value) return { text: 'SIN MENSUAL', cls: 'membership-pill--neutral' };
    if (isLocked.value) return { text: 'VENCIDO', cls: 'membership-pill--danger' };
    if (isInGrace.value) return { text: 'POR VENCER', cls: 'membership-pill--warn' };
    return { text: 'AL DIA', cls: 'membership-pill--success' };
});

async function handleStatusChange() {
    if (!localStatus.value) return;
    await store.changeStatus(localStatus.value);
}
async function handlePlanChange() {
    if (!localPlan.value) return;
    await store.changePlan(localPlan.value);
}
</script>

<template>
  <aside class="quick-actions">
    <section class="card">
      <header class="card-head">
        <span class="card-eyebrow">CAMBIO RAPIDO</span>
      </header>

      <div class="form-row">
        <label class="row-label" for="qa-status">ESTADO</label>
        <div class="select-wrap">
          <select id="qa-status" v-model="localStatus" class="select" :disabled="store.savingStatus">
            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
              {{ opt.label.toUpperCase() }}
            </option>
          </select>
          <button
            type="button"
            class="btn btn--accent"
            :disabled="store.savingStatus"
            @click="handleStatusChange"
          >
            {{ store.savingStatus ? '...' : 'GUARDAR' }}
          </button>
        </div>
      </div>

      <div class="form-row">
        <label class="row-label" for="qa-plan">PLAN</label>
        <div class="select-wrap">
          <select id="qa-plan" v-model="localPlan" class="select" :disabled="store.savingPlan">
            <option v-for="opt in planOptions" :key="opt.value" :value="opt.value">
              {{ opt.label.toUpperCase() }}
            </option>
          </select>
          <button
            type="button"
            class="btn btn--accent"
            :disabled="store.savingPlan"
            @click="handlePlanChange"
          >
            {{ store.savingPlan ? '...' : 'GUARDAR' }}
          </button>
        </div>
      </div>
    </section>

    <section class="card card--membership">
      <header class="card-head">
        <span class="card-eyebrow">MEMBRESIA</span>
        <span class="membership-pill" :class="stateLabel.cls">{{ stateLabel.text }}</span>
      </header>
      <div class="membership-grid">
        <div class="membership-cell">
          <span class="row-label">FECHA DE CORTE</span>
          <span class="membership-value">{{ expiresFormatted || '—' }}</span>
        </div>
        <div class="membership-cell">
          <span class="row-label">DIAS RESTANTES</span>
          <span
            class="membership-value"
            :class="{ 'membership-value--danger': isLocked, 'membership-value--warn': isInGrace }"
          >{{ daysUntil !== null && daysUntil !== undefined ? daysUntil : '—' }}</span>
        </div>
      </div>
      <button
        type="button"
        class="btn btn--accent btn--extend"
        @click="store.openExtendModal()"
      >
        EXTENDER MEMBRESIA
      </button>
    </section>

    <section class="card">
      <header class="card-head">
        <span class="card-eyebrow">ACCIONES</span>
      </header>

      <div class="action-list">
        <button type="button" class="action" @click="emit('edit')">
          <span class="action-glyph" aria-hidden="true">✎</span>
          <span class="action-text">
            <span class="action-title">EDITAR PERFIL</span>
            <span class="action-sub">Email, teléfono, ciudad</span>
          </span>
        </button>

        <button type="button" class="action" @click="emit('assign-coach')">
          <span class="action-glyph" aria-hidden="true">↻</span>
          <span class="action-text">
            <span class="action-title">ASIGNAR COACH</span>
            <span class="action-sub">{{ store.coaches.length }} disponibles · ordenados por carga</span>
          </span>
        </button>

        <button
          type="button"
          class="action action--locked"
          disabled
          title="Backend pendiente: POST /:id/suspend con razón obligatoria"
        >
          <span class="action-glyph" aria-hidden="true">⛔</span>
          <span class="action-text">
            <span class="action-title">SUSPENDER CON RAZON</span>
            <span class="action-sub action-sub--warn">Próximamente · requiere backend</span>
          </span>
        </button>

        <button
          type="button"
          class="action action--locked"
          disabled
          title="Backend pendiente: POST /:id/cancel-plan con alternativa"
        >
          <span class="action-glyph" aria-hidden="true">⊘</span>
          <span class="action-text">
            <span class="action-title">ANULAR PLAN</span>
            <span class="action-sub action-sub--warn">Próximamente · requiere backend</span>
          </span>
        </button>
      </div>
    </section>

    <Transition name="msg">
      <div v-if="store.actionMessage" class="action-msg" role="status">
        <span>{{ store.actionMessage }}</span>
        <button type="button" class="msg-close" @click="store.clearMessage()" aria-label="Cerrar">×</button>
      </div>
    </Transition>
  </aside>
</template>

<style scoped>
.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.65);
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.card-head { display: flex; align-items: center; justify-content: space-between; }
.card-eyebrow {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
}

.form-row { display: flex; flex-direction: column; gap: 4px; }
.row-label {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
}

.select-wrap { display: flex; gap: 6px; }
.select {
    flex: 1;
    height: 36px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: #18181b;
    color: #fafafa;
    font-family: var(--font-sans);
    font-size: 12px;
    padding: 0 8px;
    color-scheme: dark;
}
.select option {
    background: #18181b;
    color: #fafafa;
}
.select:focus {
    outline: none;
    border-color: var(--c-accent);
}
.btn {
    padding: 0 12px;
    height: 36px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: transparent;
    color: var(--c-text-2);
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
    flex-shrink: 0;
}
.btn:hover:not(:disabled) { background: rgba(255, 255, 255, 0.04); color: var(--c-text); }
.btn--accent:hover:not(:disabled) {
    color: #F87171;
    border-color: rgba(220, 38, 38, 0.4);
}
.btn:disabled { opacity: 0.5; cursor: not-allowed; }

.action-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.action {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255, 255, 255, 0.02);
    color: var(--c-text-2);
    cursor: pointer;
    text-align: left;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.action:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(255,255,255,0.12);
    color: var(--c-text);
}
.action--locked {
    cursor: not-allowed;
    opacity: 0.55;
}
.action-glyph {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.04);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
    color: var(--c-text-2);
}
.action-text { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
.action-title {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
}
.action-sub {
    font-family: var(--font-sans);
    font-size: 11px;
    color: var(--c-text-3);
}
.action-sub--warn { color: #FCD34D; }

.card--membership { border-color: rgba(220, 38, 38, 0.35); }
.membership-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    padding: 8px 0;
    border-top: 1px solid var(--c-border);
    border-bottom: 1px solid var(--c-border);
}
.membership-cell { display: flex; flex-direction: column; gap: 2px; }
.membership-value {
    font-family: var(--font-display);
    font-feature-settings: 'tnum' 1;
    font-size: 16px;
    font-weight: 600;
    color: var(--c-text);
    line-height: 1.1;
}
.membership-value--danger { color: #F87171; }
.membership-value--warn { color: #FBBF24; }
.membership-pill {
    display: inline-block;
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: var(--r-pill, 999px);
    line-height: 1.4;
}
.membership-pill--success { background: rgba(16,185,129,0.1); color: #34D399; }
.membership-pill--warn { background: rgba(251,191,36,0.12); color: #FBBF24; }
.membership-pill--danger { background: rgba(220,38,38,0.15); color: #F87171; }
.membership-pill--neutral { background: rgba(255,255,255,0.04); color: var(--c-text-3); }

.btn--extend {
    width: 100%;
    background: #DC2626;
    border-color: #DC2626;
    color: white;
    height: 40px;
}
.btn--extend:hover:not(:disabled) {
    background: #B91C1C;
    border-color: #B91C1C;
    color: white;
}

.action-msg {
    border-radius: var(--r-sm, 12px);
    border: 1px solid rgba(16, 185, 129, 0.3);
    background: rgba(16,185,129,0.1);
    color: #34D399;
    padding: 10px 12px;
    font-family: var(--font-sans);
    font-size: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
}
.msg-close {
    background: transparent;
    border: none;
    color: inherit;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
    opacity: 0.7;
}
.msg-close:hover { opacity: 1; }

.msg-enter-active, .msg-leave-active { transition: opacity 0.18s var(--ease-out, ease), transform 0.18s var(--ease-out, ease); }
.msg-enter-from, .msg-leave-to { opacity: 0; transform: translateY(-4px); }
</style>
