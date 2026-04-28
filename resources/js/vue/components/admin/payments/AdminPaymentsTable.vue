<script setup>
import { computed } from 'vue';
import { useAdminPaymentsStore } from '../../../stores/adminPayments';

const store = useAdminPaymentsStore();

const sortedPayments = computed(() => {
    const list = [...(store.payments || [])];
    const { sortBy, sortDir } = store.filters;
    list.sort((a, b) => {
        let av, bv;
        if (sortBy === 'amount') {
            av = Number(a.amount || 0);
            bv = Number(b.amount || 0);
        } else if (sortBy === 'buyer_name') {
            av = (a.buyer_name || '').toLowerCase();
            bv = (b.buyer_name || '').toLowerCase();
        } else {
            av = a.created_at_iso || a.created_at || '';
            bv = b.created_at_iso || b.created_at || '';
        }
        if (av < bv) return sortDir === 'asc' ? -1 : 1;
        if (av > bv) return sortDir === 'asc' ? 1 : -1;
        return 0;
    });
    return list;
});

function statusVariant(status) {
    if (status === 'approved') return 'success';
    if (status === 'pending') return 'warn';
    if (status === 'declined' || status === 'error') return 'urgent';
    if (status === 'voided') return 'info';
    return 'info';
}

function statusLabel(status) {
    const map = {
        approved: 'APROBADO',
        pending: 'PENDIENTE',
        declined: 'RECHAZADO',
        voided: 'ANULADO',
        error: 'ERROR',
    };
    return map[status] || (status || '—').toUpperCase();
}

function planVariant(plan) {
    if (!plan) return 'neutral';
    const lower = String(plan).toLowerCase();
    if (lower.includes('rise')) return 'gold';
    if (lower.includes('elite')) return 'amber';
    if (lower.includes('metodo') || lower.includes('método')) return 'red';
    if (lower.includes('esencial')) return 'info';
    if (lower.includes('presencial')) return 'amber';
    return 'neutral';
}

function avatarInitial(name) {
    return (name || '?').trim().charAt(0).toUpperCase() || '?';
}

function applySort(field) {
    store.setSort(field);
}

function sortGlyph(field) {
    if (store.filters.sortBy !== field) return '';
    return store.filters.sortDir === 'asc' ? '↑' : '↓';
}
</script>

<template>
  <div class="table-card">
    <div class="table-head">
      <span class="th col-client" @click="applySort('buyer_name')">
        CLIENTE <span class="sort-glyph">{{ sortGlyph('buyer_name') }}</span>
      </span>
      <span class="th col-plan">PLAN</span>
      <span class="th col-amount" @click="applySort('amount')">
        MONTO <span class="sort-glyph">{{ sortGlyph('amount') }}</span>
      </span>
      <span class="th col-method">METODO</span>
      <span class="th col-status">ESTADO</span>
      <span class="th col-date" @click="applySort('created_at')">
        FECHA <span class="sort-glyph">{{ sortGlyph('created_at') }}</span>
      </span>
      <span class="th col-actions">DETALLE</span>
    </div>

    <button
      v-for="p in sortedPayments"
      :key="p.id"
      class="row"
      type="button"
      @click="store.openDetail(p)"
    >
      <div class="cell col-client">
        <span class="avatar" aria-hidden="true">{{ avatarInitial(p.buyer_name || p.client_name) }}</span>
        <div class="client-info">
          <span class="client-name">{{ p.buyer_name || p.client_name || 'Sin nombre' }}</span>
          <span v-if="p.client_name && p.client_name !== p.buyer_name" class="client-sub">{{ p.client_name }}</span>
        </div>
      </div>

      <div class="cell col-plan">
        <span v-if="p.plan && p.plan !== '-'" class="pill" :class="`pill--${planVariant(p.plan)}`">
          {{ p.plan }}
        </span>
        <span v-else class="dash">—</span>
      </div>

      <div class="cell col-amount">
        <span class="amount-num">${{ p.amount_fmt || p.amount || '0' }}</span>
        <span class="amount-cop">COP</span>
      </div>

      <div class="cell col-method">
        <span class="method">{{ (p.payment_method || '—').toUpperCase() }}</span>
      </div>

      <div class="cell col-status">
        <span class="pill" :class="`pill--${statusVariant(p.status)}`">{{ statusLabel(p.status) }}</span>
      </div>

      <div class="cell col-date">
        <span class="date-rel">{{ p.time_ago || '—' }}</span>
        <span class="date-abs">{{ p.created_at }}</span>
      </div>

      <div class="cell col-actions">
        <span class="chev" aria-hidden="true">›</span>
      </div>
    </button>
  </div>
</template>

<style scoped>
.table-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 18px;
}

.table-head,
.row {
    display: grid;
    grid-template-columns:
        minmax(180px, 1.6fr)
        minmax(80px, 0.7fr)
        minmax(100px, 0.9fr)
        minmax(100px, 0.8fr)
        minmax(110px, 0.9fr)
        minmax(120px, 1fr)
        32px;
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
    cursor: default;
    user-select: none;
}
.th[click],
.col-client.th,
.col-amount.th,
.col-date.th { cursor: pointer; }

.sort-glyph {
    color: var(--color-wc-red-text, #F87171);
    font-family: var(--font-data, sans-serif);
    margin-left: 2px;
}

.row {
    width: 100%;
    text-align: left;
    background: transparent;
    border: none;
    padding: 11px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    cursor: pointer;
    color: inherit;
    font: inherit;
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

.col-client {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}
.avatar {
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    border-radius: 50%;
    background: rgba(220, 38, 38, 0.12);
    border: 1px solid rgba(220, 38, 38, 0.25);
    color: var(--color-wc-red-text, #F87171);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-size: 13px;
    letter-spacing: 0.04em;
}
.client-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.client-name {
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 600;
    color: var(--color-wc-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.client-sub {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

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
.pill--warn    { background: var(--color-wc-amber-soft, rgba(245, 158, 11, 0.1)); color: var(--color-wc-amber-text, #FCD34D); }
.pill--urgent  { background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.1)); color: var(--color-wc-red-text, #F87171); }
.pill--info    { background: var(--color-wc-blue-soft, rgba(59, 130, 246, 0.1)); color: var(--color-wc-blue-text, #60A5FA); }
.pill--gold    { background: rgba(200, 167, 105, 0.12); color: var(--color-wc-gold, #C8A769); border: 1px solid rgba(200, 167, 105, 0.25); }
.pill--amber   { background: var(--color-wc-amber-soft, rgba(245, 158, 11, 0.1)); color: var(--color-wc-amber-text, #FCD34D); }
.pill--red     { background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.1)); color: var(--color-wc-red-text, #F87171); }
.pill--neutral { background: rgba(255, 255, 255, 0.04); color: var(--color-wc-text-tertiary); }

.amount-num {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-feature-settings: 'tnum' 1;
    font-size: 14px;
    font-weight: 600;
    color: var(--color-wc-text);
}
.amount-cop {
    display: block;
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.18em;
    color: var(--color-wc-text-tertiary);
    margin-top: 2px;
}

.method {
    font-family: var(--font-mono, monospace);
    font-size: 10px;
    letter-spacing: 0.16em;
    color: var(--color-wc-text-secondary);
}

.date-rel {
    display: block;
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 12px;
    color: var(--color-wc-text-secondary);
}
.date-abs {
    display: block;
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.14em;
    color: var(--color-wc-text-tertiary);
    margin-top: 1px;
}

.col-actions { text-align: right; }
.chev {
    color: var(--color-wc-text-tertiary);
    font-size: 18px;
    line-height: 1;
}
.row:hover .chev { color: var(--color-wc-red-text, #F87171); }

.dash { color: var(--color-wc-text-tertiary); }
</style>
