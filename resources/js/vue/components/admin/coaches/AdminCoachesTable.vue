<script setup>
import { useAdminCoachListStore } from '../../../stores/adminCoachList';
import { useAdminCoachDetailStore } from '../../../stores/adminCoachDetail';

const store = useAdminCoachListStore();
const detail = useAdminCoachDetailStore();

const emit = defineEmits(['edit', 'reset', 'suspend', 'impersonate']);

function avatarInitial(name) {
    return (name || '?').trim().charAt(0).toUpperCase() || '?';
}

function statusVariant(active) {
    return active ? 'success' : 'neutral';
}

function statusLabel(active) {
    return active ? 'ACTIVO' : 'INACTIVO';
}

function formatLastLogin(iso) {
    if (!iso) return 'NUNCA';
    try {
        const d = new Date(iso);
        const days = Math.floor((Date.now() - d.getTime()) / 86400000);
        if (days <= 0) return 'HOY';
        if (days === 1) return 'AYER';
        if (days < 7) return `HACE ${days}D`;
        if (days < 30) return `HACE ${Math.floor(days / 7)}SEM`;
        return `HACE ${Math.floor(days / 30)}M`;
    } catch {
        return '—';
    }
}

function applySort(field) {
    store.setSort(field);
}

function sortGlyph(field) {
    if (store.filters.sortBy !== field) return '';
    return store.filters.sortDir === 'asc' ? '↑' : '↓';
}

function openDetail(coach) {
    detail.openCoach(coach);
}

// Stop propagation para no abrir el detail al usar acciones del kebab
function actionClick(action, coach, ev) {
    ev?.stopPropagation();
    emit(action, coach);
}
</script>

<template>
  <div class="coaches-table">
    <div class="table-head">
      <span class="th col-coach" @click="applySort('name')">
        COACH <span class="sort-glyph">{{ sortGlyph('name') }}</span>
      </span>
      <span class="th col-contact">CONTACTO</span>
      <span class="th col-clients" @click="applySort('clients')">
        CLIENTES <span class="sort-glyph">{{ sortGlyph('clients') }}</span>
      </span>
      <span class="th col-tickets">TICKETS MES</span>
      <span class="th col-retention">RETENCION</span>
      <span class="th col-login" @click="applySort('last_login')">
        ULTIMO LOGIN <span class="sort-glyph">{{ sortGlyph('last_login') }}</span>
      </span>
      <span class="th col-status">ESTADO</span>
      <span class="th col-actions">ACCIONES</span>
    </div>

    <div
      v-for="coach in store.sortedCoaches"
      :key="coach.id"
      class="row"
      role="button"
      tabindex="0"
      @click="openDetail(coach)"
      @keydown.enter.prevent="openDetail(coach)"
      @keydown.space.prevent="openDetail(coach)"
    >
      <div class="cell col-coach">
        <span class="avatar" aria-hidden="true">{{ avatarInitial(coach.name) }}</span>
        <div class="coach-info">
          <span class="coach-name">{{ coach.name || 'Sin nombre' }}</span>
          <span class="coach-handle">@{{ coach.username || '—' }}</span>
          <span class="coach-id">#{{ coach.id }}</span>
        </div>
      </div>

      <div class="cell col-contact">
        <span class="contact-mail">{{ coach.email || '—' }}</span>
        <span class="contact-wa">{{ coach.whatsapp || '—' }}</span>
      </div>

      <div class="cell col-clients">
        <span class="num-data">{{ coach.client_count ?? 0 }}</span>
      </div>

      <div class="cell col-tickets">
        <span class="num-soft">—</span>
      </div>

      <div class="cell col-retention">
        <span class="num-soft">—</span>
      </div>

      <div class="cell col-login">
        <span class="login-mono">{{ formatLastLogin(coach.last_login_at) }}</span>
      </div>

      <div class="cell col-status">
        <span class="pill" :class="`pill--${statusVariant(coach.active)}`">{{ statusLabel(coach.active) }}</span>
      </div>

      <div class="cell col-actions" @click.stop>
        <button class="action-btn" type="button" title="Editar" @click="actionClick('edit', coach, $event)">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
        <button class="action-btn action-btn--amber" type="button" title="Resetear contrasena" @click="actionClick('reset', coach, $event)">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <path d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
        <button class="action-btn action-btn--accent" type="button" title="Impersonar" @click="actionClick('impersonate', coach, $event)">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <path d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
        <button v-if="coach.active" class="action-btn action-btn--danger" type="button" title="Desactivar" @click="actionClick('suspend', coach, $event)">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <path d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.coaches-table {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 18px;
}

.table-head,
.row {
    display: grid;
    grid-template-columns:
        minmax(190px, 1.7fr)
        minmax(160px, 1.3fr)
        minmax(80px, 0.6fr)
        minmax(90px, 0.7fr)
        minmax(90px, 0.7fr)
        minmax(110px, 0.9fr)
        minmax(90px, 0.7fr)
        minmax(150px, 1fr);
    gap: 12px;
    align-items: center;
}

.table-head {
    padding-bottom: 10px;
    margin-bottom: 6px;
    border-bottom: 1px solid var(--color-wc-border);
}

.th {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    user-select: none;
}
.col-coach,
.col-clients,
.col-login {
    cursor: pointer;
}
.sort-glyph {
    color: var(--color-wc-red-text, #F87171);
    font-family: var(--font-data, sans-serif);
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
    outline: 1px solid var(--color-wc-accent, #DC2626);
    outline-offset: -1px;
    background: rgba(220, 38, 38, 0.04);
}

.cell { min-width: 0; }

/* ── Coach (avatar + name + handle) ────────────────────────────────── */
.col-coach {
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
    color: var(--color-wc-red-text, #F87171);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-size: 14px;
    letter-spacing: 0.04em;
}
.coach-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
    gap: 1px;
}
.coach-name {
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 600;
    color: var(--color-wc-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.coach-handle {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.14em;
    color: var(--color-wc-text-tertiary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.coach-id {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.16em;
    color: rgba(220, 38, 38, 0.5);
    white-space: nowrap;
}

/* ── Contact ───────────────────────────────────────────────────────── */
.col-contact { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
.contact-mail {
    font-size: 11px;
    color: var(--color-wc-text-secondary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.contact-wa {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.12em;
    color: var(--color-wc-text-tertiary);
}

/* ── Numbers ───────────────────────────────────────────────────────── */
.num-data {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-feature-settings: 'tnum' 1;
    font-size: 16px;
    font-weight: 700;
    color: var(--color-wc-text);
}
.num-soft {
    font-family: var(--font-mono, monospace);
    font-size: 14px;
    color: var(--color-wc-text-tertiary);
    opacity: 0.55;
}

/* ── Last login ────────────────────────────────────────────────────── */
.login-mono {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-secondary);
}

/* ── Status pill ───────────────────────────────────────────────────── */
.pill {
    display: inline-block;
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: 4px;
    line-height: 1.4;
}
.pill--success { background: var(--color-wc-green-soft, rgba(16, 185, 129, 0.1)); color: var(--color-wc-green-text, #34D399); }
.pill--neutral { background: rgba(255, 255, 255, 0.04); color: var(--color-wc-text-tertiary); }

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
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.4);
    color: var(--color-wc-text-secondary);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.action-btn:hover {
    background: rgba(255, 255, 255, 0.04);
    border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.12));
    color: var(--color-wc-text);
}
.action-btn--amber:hover  { color: var(--color-wc-amber-text, #FCD34D); border-color: rgba(245, 158, 11, 0.4); }
.action-btn--accent:hover { color: var(--color-wc-red-text, #F87171); border-color: rgba(220, 38, 38, 0.4); }
.action-btn--danger:hover { color: var(--color-wc-red-text, #F87171); border-color: rgba(220, 38, 38, 0.4); }
</style>
