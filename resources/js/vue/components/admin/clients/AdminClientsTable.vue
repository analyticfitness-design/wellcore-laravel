<script setup>
import { useRouter } from 'vue-router';
import { useAdminClientListStore } from '../../../stores/adminClientList';

const router = useRouter();
const store = useAdminClientListStore();

const emit = defineEmits(['deactivate', 'delete', 'impersonate']);

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
    try {
        const d = new Date(iso);
        if (Number.isNaN(d.getTime())) return '—';
        const days = Math.floor((Date.now() - d.getTime()) / 86400000);
        if (days < 0) return 'HOY';
        if (days === 0) return 'HOY';
        if (days === 1) return 'AYER';
        if (days < 7) return `HACE ${days}D`;
        if (days < 30) return `HACE ${Math.floor(days / 7)}SEM`;
        if (days < 365) return `HACE ${Math.floor(days / 30)}M`;
        return `HACE ${Math.floor(days / 365)}A`;
    } catch {
        return '—';
    }
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

function formatTooltip(iso) {
    if (!iso) return '';
    try {
        const d = new Date(iso);
        return d.toLocaleString('es-CO', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    } catch {
        return '';
    }
}

function applySort(field) {
    store.setSort(field);
}

function sortGlyph(field) {
    if (store.filters.sortBy !== field) return '';
    return store.filters.sortDir === 'asc' ? '↑' : '↓';
}

function openDetail(client) {
    if (!client?.id) return;
    router.push(`/admin/clients/${client.id}`);
}

function actionClick(action, client, ev) {
    ev?.stopPropagation();
    emit(action, client);
}
</script>

<template>
  <div class="clients-table">
    <div class="table-head">
      <span class="th col-client" @click="applySort('name')">
        CLIENTE <span class="sort-glyph">{{ sortGlyph('name') }}</span>
      </span>
      <span class="th col-code" @click="applySort('client_code')">
        CODIGO <span class="sort-glyph">{{ sortGlyph('client_code') }}</span>
      </span>
      <span class="th col-plan" @click="applySort('plan')">
        PLAN <span class="sort-glyph">{{ sortGlyph('plan') }}</span>
      </span>
      <span class="th col-status" @click="applySort('status')">
        ESTADO <span class="sort-glyph">{{ sortGlyph('status') }}</span>
      </span>
      <span class="th col-joined" @click="applySort('fecha_inicio')">
        INSCRIPCION <span class="sort-glyph">{{ sortGlyph('fecha_inicio') }}</span>
      </span>
      <span class="th col-login">ULTIMO LOGIN</span>
      <span class="th col-actions">ACCIONES</span>
    </div>

    <div
      v-for="client in store.clients"
      :key="client.id"
      class="row"
      role="button"
      tabindex="0"
      @click="openDetail(client)"
      @keydown.enter.prevent="openDetail(client)"
      @keydown.space.prevent="openDetail(client)"
    >
      <div class="cell col-client">
        <span class="avatar" aria-hidden="true">{{ avatarInitial(client.name) }}</span>
        <div class="client-info">
          <span class="client-name">{{ client.name || 'Sin nombre' }}</span>
          <span class="client-mail">{{ client.email || '—' }}</span>
        </div>
      </div>

      <div class="cell col-code">
        <span class="code-mono">{{ client.client_code || '—' }}</span>
      </div>

      <div class="cell col-plan">
        <span class="plan-pill">{{ planLabel(client.plan) }}</span>
      </div>

      <div class="cell col-status">
        <span class="pill" :class="`pill--${statusVariant(client.status)}`">
          {{ statusLabel(client.status) }}
        </span>
      </div>

      <div class="cell col-joined">
        <span class="date-mono" :title="formatTooltip(client.fecha_inicio || client.created_at)">
          {{ formatRelative(client.fecha_inicio || client.created_at) }}
        </span>
      </div>

      <div class="cell col-login">
        <span
          class="login-mono"
          :class="`login-${loginUrgency(client.last_login_at)}`"
          :title="formatTooltip(client.last_login_at)"
        >
          {{ formatRelative(client.last_login_at) }}
        </span>
      </div>

      <div class="cell col-actions" @click.stop>
        <button
          class="action-btn"
          type="button"
          :title="'Ver detalle de ' + (client.name || 'cliente')"
          :aria-label="'Ver detalle de ' + (client.name || 'cliente')"
          @click="openDetail(client)"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <path d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
        <button
          v-if="store.isSuperadmin"
          class="action-btn action-btn--accent"
          type="button"
          :title="'Ver portal como ' + (client.name || 'cliente')"
          :aria-label="'Impersonificar a ' + (client.name || 'cliente')"
          @click="actionClick('impersonate', client, $event)"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <path d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
        <button
          v-if="client.status !== 'inactivo' && client.status?.value !== 'inactivo'"
          class="action-btn action-btn--amber"
          type="button"
          title="Marcar como inactivo"
          aria-label="Marcar como inactivo"
          @click="actionClick('deactivate', client, $event)"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <path d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
        <button
          v-if="store.isSuperadmin"
          class="action-btn action-btn--danger"
          type="button"
          title="Eliminar cliente (Superadmin)"
          aria-label="Eliminar cliente"
          @click="actionClick('delete', client, $event)"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <path d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.clients-table {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 18px;
    overflow: hidden;
}

.table-head,
.row {
    display: grid;
    grid-template-columns:
        minmax(220px, 2fr)
        minmax(80px, 0.7fr)
        minmax(90px, 0.8fr)
        minmax(110px, 1fr)
        minmax(110px, 0.9fr)
        minmax(110px, 0.9fr)
        minmax(110px, 0.9fr);
    gap: 12px;
    align-items: center;
}

.table-head {
    padding-bottom: 10px;
    margin-bottom: 6px;
    border-bottom: 1px solid var(--c-border);
}

.th {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
    user-select: none;
}
.col-client,
.col-code,
.col-plan,
.col-status,
.col-joined { cursor: pointer; }
.sort-glyph {
    color: #F87171;
    font-family: var(--font-display);
    margin-left: 2px;
}
.col-actions { text-align: right; }

.row {
    padding: 11px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease);
}
.row:last-child { border-bottom: none; }
.row:hover { background: rgba(255, 255, 255, 0.02); }
.row:focus-visible {
    outline: 1px solid var(--c-accent);
    outline-offset: -1px;
    background: rgba(220, 38, 38, 0.04);
}

.cell { min-width: 0; }

/* ── Cliente (avatar + name + email) ───────────────────────────────── */
.col-client {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}
.avatar {
    width: 32px;
    height: 32px;
    flex-shrink: 0;
    border-radius: 50%;
    background: rgba(220, 38, 38, 0.12);
    border: 1px solid rgba(220, 38, 38, 0.25);
    color: #F87171;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-size: 14px;
    letter-spacing: 0.04em;
}
.client-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
    gap: 1px;
}
.client-name {
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 600;
    color: var(--c-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.client-mail {
    font-size: 11px;
    color: var(--c-text-3);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ── Code ──────────────────────────────────────────────────────────── */
.code-mono {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 1.0px;
    color: var(--c-text-2);
    text-transform: uppercase;
}

/* ── Plan pill ─────────────────────────────────────────────────────── */
.plan-pill {
    display: inline-block;
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: var(--r-pill, 999px);
    line-height: 1.4;
    background: rgba(96, 165, 250, 0.08);
    color: #60A5FA;
    border: 1px solid rgba(96, 165, 250, 0.18);
}

/* ── Status pill ───────────────────────────────────────────────────── */
.pill {
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
.pill--amber   { background: rgba(245,158,11,0.1); color: #FCD34D; }
.pill--danger  { background: var(--c-accent-dim); color: #F87171; }
.pill--info    { background: rgba(59,130,246,0.1); color: #60A5FA; }

/* ── Date / login ──────────────────────────────────────────────────── */
.date-mono,
.login-mono {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-2);
}
.login-warn   { color: #FCD34D; }
.login-urgent { color: #F87171; }
.login-never  { color: var(--c-text-3); opacity: 0.55; }

/* ── Actions ───────────────────────────────────────────────────────── */
.col-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
}
.action-btn {
    width: 30px;
    height: 30px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.4);
    color: var(--c-text-2);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.action-btn:hover {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(255,255,255,0.12);
    color: var(--c-text);
}
.action-btn--amber:hover  { color: #FCD34D; border-color: rgba(245, 158, 11, 0.4); }
.action-btn--danger:hover { color: #F87171; border-color: rgba(220, 38, 38, 0.4); }
.action-btn--accent:hover { color: var(--c-accent); border-color: rgba(220, 38, 38, 0.4); background: rgba(220, 38, 38, 0.06); }
</style>
