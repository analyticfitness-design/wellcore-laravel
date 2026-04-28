<script setup>
import { computed } from 'vue';
import { useAdminClientRequestsStore } from '../../../stores/adminClientRequests';

const props = defineProps({
    request: { type: Object, required: true },
});

const store = useAdminClientRequestsStore();

const ACTION_META = {
    delete:     { label: 'Eliminar cliente',    color: 'red'   },
    deactivate: { label: 'Desactivar cliente',  color: 'amber' },
    edit:       { label: 'Editar datos',        color: 'blue'  },
};

const STATUS_META = {
    pendiente: { label: 'Pendiente', color: 'amber' },
    aprobado:  { label: 'Aprobado',  color: 'green' },
    rechazado: { label: 'Rechazado', color: 'red'   },
};

const actionMeta  = computed(() => ACTION_META[props.request.action]  || { label: props.request.action || '—', color: 'blue' });
const statusMeta  = computed(() => STATUS_META[props.request.status]  || { label: props.request.status || '—', color: 'blue' });
const isPending   = computed(() => props.request.status === 'pendiente');

function truncate(str, n = 120) {
    if (!str) return '';
    return str.length > n ? str.slice(0, n) + '…' : str;
}

function fmtDate(iso) {
    if (!iso) return '';
    try {
        return new Date(iso).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' });
    } catch { return ''; }
}
</script>

<template>
  <article
    class="request-card"
    :class="{ 'request-card--pending': isPending }"
    @click="store.openDetail(request)"
    tabindex="0"
    role="button"
    :aria-label="`Ver detalle: ${actionMeta.label} - ${request.client_name}`"
    @keydown.enter.prevent="store.openDetail(request)"
  >
    <div class="card-badges">
      <span class="badge" :class="`badge--${actionMeta.color}`">
        {{ actionMeta.label.toUpperCase() }}
      </span>
      <span class="status-badge" :class="`status--${statusMeta.color}`">
        {{ statusMeta.label }}
      </span>
      <span class="card-date">{{ fmtDate(request.created_at) }}</span>
    </div>

    <div class="card-meta">
      <div class="meta-pair">
        <span class="meta-label">COACH</span>
        <span class="meta-val">{{ request.coach_name || '—' }}</span>
      </div>
      <span class="meta-sep" aria-hidden="true">·</span>
      <div class="meta-pair">
        <span class="meta-label">CLIENTE</span>
        <span class="meta-val">{{ request.client_name || '—' }}</span>
      </div>
    </div>

    <p v-if="request.reason" class="card-reason">{{ truncate(request.reason) }}</p>

    <div class="card-actions" @click.stop>
      <button
        type="button"
        class="btn-detail"
        @click="store.openDetail(request)"
      >
        Ver detalle
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
        </svg>
      </button>

      <template v-if="isPending">
        <button
          type="button"
          class="btn-approve"
          @click="store.openApprove(request)"
          aria-label="Aprobar solicitud"
        >Aprobar</button>
        <button
          type="button"
          class="btn-reject"
          @click="store.openReject(request)"
          aria-label="Rechazar solicitud"
        >Rechazar</button>
      </template>
    </div>
  </article>
</template>

<style scoped>
.request-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    cursor: pointer;
    transition: border-color 0.15s var(--ease-out), background 0.15s var(--ease-out);
    position: relative;
}
.request-card:hover,
.request-card:focus-visible {
    border-color: var(--color-wc-border-2);
    background: rgba(24, 24, 24, 0.85);
    outline: none;
}
.request-card--pending {
    border-left: 2px solid var(--color-wc-amber-text, #FCD34D);
}

/* Badges */
.card-badges {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
}
.badge {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.18em;
    padding: 3px 8px;
    border-radius: 4px;
    border: 1px solid;
}
.badge--red   { background: var(--color-wc-red-soft);   color: var(--color-wc-red-text);   border-color: rgba(220,38,38,0.28); }
.badge--amber { background: var(--color-wc-amber-soft); color: var(--color-wc-amber-text); border-color: rgba(245,158,11,0.28); }
.badge--blue  { background: var(--color-wc-blue-soft);  color: var(--color-wc-blue-text);  border-color: rgba(59,130,246,0.28); }

.status-badge {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.16em;
    padding: 3px 8px;
    border-radius: 20px;
}
.status--amber { background: var(--color-wc-amber-soft); color: var(--color-wc-amber-text); }
.status--green { background: var(--color-wc-green-soft); color: var(--color-wc-green-text); }
.status--red   { background: var(--color-wc-red-soft);   color: var(--color-wc-red-text);   }

.card-date {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.12em;
    color: var(--color-wc-text-tertiary);
    margin-left: auto;
}

/* Meta row */
.card-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px 14px;
}
.meta-pair {
    display: flex;
    align-items: baseline;
    gap: 6px;
}
.meta-label {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.18em;
    color: var(--color-wc-text-tertiary);
    flex-shrink: 0;
}
.meta-val {
    font-family: var(--font-sans, 'Inter', sans-serif);
    font-size: 13px;
    font-weight: 500;
    color: var(--color-wc-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 160px;
}
.meta-sep {
    color: var(--color-wc-border-2);
    font-size: 14px;
}

/* Reason */
.card-reason {
    font-family: var(--font-sans, 'Inter', sans-serif);
    font-size: 12px;
    line-height: 1.55;
    color: var(--color-wc-text-secondary);
    margin: 0;
}

/* Actions */
.card-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding-top: 6px;
    border-top: 1px solid rgba(255, 255, 255, 0.04);
}
.btn-detail {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.16em;
    color: var(--color-wc-text-secondary);
    background: transparent;
    border: 1px solid var(--color-wc-border);
    border-radius: 6px;
    padding: 5px 10px;
    cursor: pointer;
    transition: color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
    text-transform: uppercase;
}
.btn-detail:hover {
    color: var(--color-wc-text);
    border-color: var(--color-wc-border-2);
}
.btn-approve {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    background: var(--color-wc-green-soft);
    color: var(--color-wc-green-text);
    border: 1px solid rgba(16,185,129,0.28);
    border-radius: 6px;
    padding: 5px 12px;
    cursor: pointer;
    transition: background 0.15s var(--ease-out);
}
.btn-approve:hover { background: rgba(16,185,129,0.18); }

.btn-reject {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    background: var(--color-wc-red-soft);
    color: var(--color-wc-red-text);
    border: 1px solid rgba(220,38,38,0.28);
    border-radius: 6px;
    padding: 5px 12px;
    cursor: pointer;
    transition: background 0.15s var(--ease-out);
}
.btn-reject:hover { background: rgba(220,38,38,0.18); }

@media (prefers-reduced-motion: reduce) {
    .request-card,
    .btn-detail,
    .btn-approve,
    .btn-reject { transition: none !important; }
}
</style>
