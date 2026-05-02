<script setup>
import { computed, watch, onUnmounted } from 'vue';
import { useAdminClientRequestsStore } from '../../../stores/adminClientRequests';

const store = useAdminClientRequestsStore();

const open = computed(() => store.drawerOpen);
const req  = computed(() => store.drawerRequest);

const ACTION_META = {
    delete:     { label: 'Eliminar cliente',   warning: 'Al aprobar, el cliente será marcado para eliminación. Sus datos históricos se conservarán.' },
    deactivate: { label: 'Desactivar cliente', warning: 'Al aprobar, el cliente perderá acceso a la plataforma de inmediato.' },
    edit:       { label: 'Editar datos',       warning: 'Al aprobar, confirmas que el coach puede realizar los cambios descritos.' },
};

const STATUS_META = {
    pendiente: { label: 'Pendiente', cls: 'status--amber' },
    aprobado:  { label: 'Aprobado',  cls: 'status--green' },
    rechazado: { label: 'Rechazado', cls: 'status--red'   },
};

const actionMeta  = computed(() => ACTION_META[req.value?.action] || { label: req.value?.action || '—', warning: '' });
const statusMeta  = computed(() => STATUS_META[req.value?.status] || { label: req.value?.status || '—', cls: '' });
const isPending   = computed(() => req.value?.status === 'pendiente');

const refreshHint = computed(() => {
    const s = store.secondsSinceRefresh;
    if (s === null) return '';
    if (s < 10) return 'Actualizado ahora';
    if (s < 60) return `Hace ${s}s`;
    return `Hace ${Math.floor(s / 60)} min`;
});

function fmtDate(iso) {
    if (!iso) return '—';
    try {
        return new Date(iso).toLocaleString('es-MX', { dateStyle: 'medium', timeStyle: 'short' });
    } catch { return '—'; }
}

watch(open, (val) => {
    document.body.style.overflow = val ? 'hidden' : '';
});

onUnmounted(() => {
    document.body.style.overflow = '';
});
</script>

<template>
  <Teleport to="body">
    <Transition name="drawer-fade">
      <div
        v-if="open"
        class="drawer-backdrop"
        aria-hidden="true"
        @click="store.closeDetail()"
      ></div>
    </Transition>

    <Transition name="drawer-slide">
      <aside
        v-if="open"
        class="drawer-panel"
        role="dialog"
        aria-label="Detalle de solicitud"
      >
        <!-- Head -->
        <header class="drawer-head">
          <div class="head-left">
            <span class="type-icon" :class="`type-icon--${req?.action}`" aria-hidden="true">
              <!-- delete -->
              <svg v-if="req?.action === 'delete'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16" />
              </svg>
              <!-- deactivate -->
              <svg v-else-if="req?.action === 'deactivate'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
              </svg>
              <!-- edit / default -->
              <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
              </svg>
            </span>
            <div class="head-text">
              <span class="eyebrow">SOLICITUD DE COACH</span>
              <h2 class="title">{{ actionMeta.label.toUpperCase() }}</h2>
            </div>
          </div>
          <button
            type="button"
            class="head-close"
            aria-label="Cerrar detalle"
            @click="store.closeDetail()"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </header>

        <!-- Body -->
        <div class="drawer-body">
          <div v-if="store.drawerLoading" class="skeleton-block" aria-label="Cargando..."></div>

          <template v-else-if="req">
            <!-- Status + fecha -->
            <div class="status-row">
              <span class="status-pill" :class="statusMeta.cls">{{ statusMeta.label }}</span>
              <span class="detail-date">{{ fmtDate(req.created_at) }}</span>
            </div>

            <!-- Coach + Cliente grid -->
            <div class="info-grid">
              <div class="info-cell">
                <span class="cell-label">COACH</span>
                <span class="cell-val">{{ req.coach_name || '—' }}</span>
              </div>
              <div class="info-cell">
                <span class="cell-label">CLIENTE AFECTADO</span>
                <span class="cell-val">{{ req.client_name || '—' }}</span>
              </div>
            </div>

            <!-- Razón completa -->
            <div class="block">
              <span class="block-label">RAZÓN DEL COACH</span>
              <p class="block-text">{{ req.reason || 'Sin descripción.' }}</p>
            </div>

            <!-- Notas del admin (post-resolución) -->
            <div v-if="req.admin_notas" class="block">
              <span class="block-label">NOTAS DEL ADMIN</span>
              <p class="block-text">{{ req.admin_notas }}</p>
            </div>

            <!-- Resolución -->
            <div v-if="req.resolved_at" class="block">
              <span class="block-label">RESUELTO</span>
              <p class="block-text mono">{{ fmtDate(req.resolved_at) }}</p>
            </div>

            <!-- Warning de acción -->
            <div v-if="isPending && actionMeta.warning" class="action-warning">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
              </svg>
              {{ actionMeta.warning }}
            </div>

            <!-- Acciones (solo pendiente) -->
            <div v-if="isPending" class="drawer-actions">
              <button
                type="button"
                class="action-reject"
                @click="store.openReject(req)"
              >Rechazar</button>
              <button
                type="button"
                class="action-approve"
                @click="store.openApprove(req)"
              >Aprobar solicitud</button>
            </div>

            <p v-if="refreshHint" class="poll-hint">{{ refreshHint }}</p>
          </template>
        </div>
      </aside>
    </Transition>
  </Teleport>
</template>

<style scoped>
.drawer-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(6px);
    z-index: 80;
}
.drawer-panel {
    position: fixed;
    top: 0; right: 0; bottom: 0;
    width: 100%;
    max-width: 480px;
    background: var(--c-surface);
    border-left: 1px solid var(--c-border);
    z-index: 90;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
@media (min-width: 768px) { .drawer-panel { max-width: 520px; } }

/* Head */
.drawer-head {
    padding: 18px 18px 14px;
    border-bottom: 1px solid var(--c-border);
    display: flex;
    align-items: flex-start;
    gap: 12px;
    flex-shrink: 0;
}
.head-left {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}
.type-icon {
    width: 44px; height: 44px;
    flex-shrink: 0;
    border-radius: var(--r-sm, 12px);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid;
}
.type-icon--delete     { background: var(--c-accent-dim);   color: #F87171;   border-color: rgba(220,38,38,0.25); }
.type-icon--deactivate { background: rgba(245,158,11,0.1); color: #FCD34D; border-color: rgba(245,158,11,0.25); }
.type-icon--edit       { background: rgba(59,130,246,0.1);  color: #60A5FA;  border-color: rgba(59,130,246,0.25); }

.head-text {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
}
.eyebrow {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.8px;
    color: var(--c-text-3);
}
.title {
    font-family: var(--font-display);
    font-size: 20px;
    letter-spacing: 0.04em;
    color: var(--c-text);
    margin: 0;
    line-height: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.head-close {
    width: var(--tap-comfort, 48px); height: var(--tap-comfort, 48px);
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: transparent;
    color: var(--c-text-2);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background 0.15s var(--ease-out), color 0.15s var(--ease-out);
}
.head-close:hover {
    background: rgba(255,255,255,0.04);
    color: var(--c-text);
}

/* Body */
.drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.skeleton-block {
    height: 200px;
    border-radius: var(--r-sm, 12px);
    background: var(--c-surface-2);
    border: 1px solid var(--c-border);
    animation: page-pulse 1.5s ease-in-out infinite;
}
@keyframes page-pulse {
    0%, 100% { opacity: 0.6; }
    50%       { opacity: 0.9; }
}

.status-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.status-pill {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    padding: 4px 10px;
    border-radius: var(--r-pill, 999px);
}
.status--amber { background: rgba(245,158,11,0.1); color: #FCD34D; }
.status--green { background: rgba(16,185,129,0.1); color: #34D399; }
.status--red   { background: var(--c-accent-dim);   color: #F87171;   }
.detail-date {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.0px;
    color: var(--c-text-3);
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.info-cell {
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255,255,255,0.02);
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.cell-label {
    font-family: var(--font-display);
    font-size: 7px;
    letter-spacing: 1.4px;
    color: var(--c-text-3);
}
.cell-val {
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 500;
    color: var(--c-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.block {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.block-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    color: var(--c-text-3);
}
.block-text {
    font-family: var(--font-sans);
    font-size: 13px;
    line-height: 1.6;
    color: var(--c-text-2);
    white-space: pre-wrap;
    margin: 0;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255,255,255,0.015);
    padding: 12px 14px;
}
.block-text.mono {
    font-family: var(--font-display);
    font-size: 11px;
    letter-spacing: 1.0px;
}

.action-warning {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid rgba(245,158,11,0.25);
    background: rgba(245,158,11,0.1);
    color: #FCD34D;
    font-family: var(--font-sans);
    font-size: 12px;
    line-height: 1.5;
    padding: 12px 14px;
}
.action-warning svg { flex-shrink: 0; margin-top: 1px; }

.drawer-actions {
    display: flex;
    gap: 10px;
    padding-top: 4px;
}
.action-reject {
    flex: 1;
    min-height: var(--tap-comfort, 48px);
    border-radius: var(--r-sm, 12px);
    border: 1px solid rgba(220,38,38,0.3);
    background: var(--c-accent-dim);
    color: #F87171;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    padding: 0 12px;
    cursor: pointer;
    transition: background 0.15s var(--ease-out);
}
.action-reject:hover { background: rgba(220,38,38,0.18); }

.action-approve {
    flex: 2;
    min-height: var(--tap-comfort, 48px);
    border-radius: var(--r-sm, 12px);
    background: rgba(16,185,129,0.1);
    border: 1px solid rgba(16,185,129,0.3);
    color: #34D399;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    padding: 0 12px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.15s var(--ease-out);
}
.action-approve:hover { background: rgba(16,185,129,0.18); }

.poll-hint {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    color: var(--c-text-3);
    text-align: right;
    opacity: 0.5;
    margin: 0;
}

/* Transitions */
.drawer-fade-enter-active,
.drawer-fade-leave-active { transition: opacity 0.2s var(--ease-out, ease); }
.drawer-fade-enter-from,
.drawer-fade-leave-to { opacity: 0; }

.drawer-slide-enter-active,
.drawer-slide-leave-active { transition: transform 0.28s var(--ease-out, ease); }
.drawer-slide-enter-from,
.drawer-slide-leave-to { transform: translateX(100%); }

@media (prefers-reduced-motion: reduce) {
    .drawer-fade-enter-active, .drawer-fade-leave-active,
    .drawer-slide-enter-active, .drawer-slide-leave-active { transition: none !important; }
    .skeleton-block { animation: none !important; }
    .head-close, .action-reject, .action-approve { transition: none !important; }
}
</style>
