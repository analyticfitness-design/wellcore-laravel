<script setup>
import { useRouter } from 'vue-router';

const props = defineProps({
    client: { type: Object, required: true },
});

const router = useRouter();
const emit = defineEmits(['deactivate', 'delete']);

const STATUS_VARIANT = {
    activo: 'success',
    pendiente: 'amber',
    inactivo: 'neutral',
    suspendido: 'danger',
    congelado: 'info',
};
const STATUS_LABEL = {
    activo: 'ACTIVO',
    pendiente: 'PENDIENTE',
    inactivo: 'INACTIVO',
    suspendido: 'SUSPENDIDO',
    congelado: 'CONGELADO',
};
const PLAN_LABEL = {
    metodo: 'METODO',
    elite: 'ELITE',
    esencial: 'ESENCIAL',
    rise: 'RISE',
    presencial: 'PRESENCIAL',
    trial: 'TRIAL',
};

function avatarInitial(name) {
    return (name || '?').trim().charAt(0).toUpperCase() || '?';
}
function statusVariant(s) {
    const k = typeof s === 'string' ? s : (s?.value || '');
    return STATUS_VARIANT[k] || 'neutral';
}
function statusLabel(s) {
    const k = typeof s === 'string' ? s : (s?.value || '');
    return STATUS_LABEL[k] || (k ? k.toUpperCase() : '—');
}
function planLabel(p) {
    const k = typeof p === 'string' ? p : (p?.value || '');
    return PLAN_LABEL[k] || (k ? k.toUpperCase() : 'SIN PLAN');
}

function formatRelative(iso) {
    if (!iso) return '—';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '—';
    const days = Math.floor((Date.now() - d.getTime()) / 86400000);
    if (days <= 0) return 'HOY';
    if (days === 1) return 'AYER';
    if (days < 7) return `HACE ${days}D`;
    if (days < 30) return `HACE ${Math.floor(days / 7)}SEM`;
    if (days < 365) return `HACE ${Math.floor(days / 30)}M`;
    return `HACE ${Math.floor(days / 365)}A`;
}

function loginUrgency(iso) {
    if (!iso) return 'never';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return 'never';
    const days = Math.floor((Date.now() - d.getTime()) / 86400000);
    if (days >= 14) return 'urgent';
    if (days >= 7) return 'warn';
    return '';
}

function openDetail() {
    if (!props.client?.id) return;
    router.push(`/admin/clients/${props.client.id}`);
}
</script>

<template>
  <article
    class="client-card"
    role="button"
    tabindex="0"
    @click="openDetail"
    @keydown.enter.prevent="openDetail"
    @keydown.space.prevent="openDetail"
  >
    <header class="card-head">
      <span class="avatar" aria-hidden="true">{{ avatarInitial(client.name) }}</span>
      <div class="head-info">
        <h3 class="card-name">{{ client.name || 'Sin nombre' }}</h3>
        <span class="card-mail">{{ client.email || '—' }}</span>
      </div>
      <span class="status-pill" :class="`pill--${statusVariant(client.status)}`">
        {{ statusLabel(client.status) }}
      </span>
    </header>

    <div class="card-stats">
      <div class="stat">
        <span class="stat-label">PLAN</span>
        <span class="stat-pill">{{ planLabel(client.plan) }}</span>
      </div>
      <div class="stat">
        <span class="stat-label">CODIGO</span>
        <span class="stat-mono">{{ client.client_code || '—' }}</span>
      </div>
      <div class="stat">
        <span class="stat-label">INSCRIPCION</span>
        <span class="stat-mono">{{ formatRelative(client.fecha_inicio || client.created_at) }}</span>
      </div>
      <div class="stat">
        <span class="stat-label">ULTIMO LOGIN</span>
        <span class="stat-mono" :class="`login-${loginUrgency(client.last_login_at)}`">
          {{ formatRelative(client.last_login_at) }}
        </span>
      </div>
    </div>

    <div class="card-cta">
      <span class="cta-text">VER DETALLE</span>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
        <path d="M8.25 4.5l7.5 7.5-7.5 7.5" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
    </div>
  </article>
</template>

<style scoped>
.client-card {
    display: block;
    width: 100%;
    text-align: left;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 14px;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.client-card:hover {
    background: rgba(17, 17, 17, 0.9);
    border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.12));
}
.client-card:focus-visible {
    outline: 1px solid var(--color-wc-accent, #DC2626);
    outline-offset: -1px;
}

.card-head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}
.avatar {
    width: 38px;
    height: 38px;
    flex-shrink: 0;
    border-radius: 50%;
    background: rgba(220, 38, 38, 0.12);
    border: 1px solid rgba(220, 38, 38, 0.25);
    color: var(--color-wc-red-text, #F87171);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-size: 16px;
    letter-spacing: 0.04em;
}
.head-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 1px;
}
.card-name {
    font-family: var(--font-sans);
    font-size: 14px;
    font-weight: 700;
    color: var(--color-wc-text);
    margin: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.card-mail {
    font-size: 11px;
    color: var(--color-wc-text-tertiary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.status-pill,
.pill--success,
.pill--neutral,
.pill--amber,
.pill--danger,
.pill--info {
    display: inline-block;
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: 4px;
    line-height: 1.4;
    flex-shrink: 0;
}
.pill--success { background: var(--color-wc-green-soft, rgba(16, 185, 129, 0.1)); color: var(--color-wc-green-text, #34D399); }
.pill--neutral { background: rgba(255, 255, 255, 0.04); color: var(--color-wc-text-tertiary); }
.pill--amber   { background: var(--color-wc-amber-soft, rgba(245, 158, 11, 0.1)); color: var(--color-wc-amber-text, #FCD34D); }
.pill--danger  { background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.1)); color: var(--color-wc-red-text, #F87171); }
.pill--info    { background: var(--color-wc-blue-soft, rgba(59, 130, 246, 0.1)); color: var(--color-wc-blue-text, #60A5FA); }

.card-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-bottom: 12px;
}
.stat {
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    padding: 8px 10px;
    background: rgba(255, 255, 255, 0.02);
    min-width: 0;
}
.stat-label {
    display: block;
    font-family: var(--font-mono, monospace);
    font-size: 7px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    margin-bottom: 4px;
}
.stat-pill {
    display: inline-block;
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    padding: 2px 6px;
    border-radius: 4px;
    line-height: 1.4;
    background: rgba(96, 165, 250, 0.08);
    color: var(--color-wc-blue-text, #60A5FA);
    border: 1px solid rgba(96, 165, 250, 0.18);
}
.stat-mono {
    font-family: var(--font-mono, monospace);
    font-size: 11px;
    letter-spacing: 0.12em;
    color: var(--color-wc-text-secondary);
    text-transform: uppercase;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
}
.login-warn   { color: var(--color-wc-amber-text, #FCD34D); }
.login-urgent { color: var(--color-wc-red-text, #F87171); }
.login-never  { color: var(--color-wc-text-tertiary); opacity: 0.55; }

.card-cta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 10px;
    border-top: 1px solid var(--color-wc-border);
    color: var(--color-wc-text-secondary);
}
.cta-text {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
}
</style>
