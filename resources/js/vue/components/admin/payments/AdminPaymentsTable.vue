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
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: var(--c-surface);
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
    border-bottom: 1px solid var(--c-border);
}

.th {
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-3);
    cursor: default;
    user-select: none;
}
.th[click],
.col-client.th,
.col-amount.th,
.col-date.th { cursor: pointer; }

.sort-glyph {
    color: #F87171;
    font-family: var(--font-display);
    margin-left: 2px;
}

.row {
    width: 100%;
    text-align: left;
    background: transparent;
    border: none;
    padding: 11px 0;
    border-bottom: 1px solid var(--c-border);
    cursor: pointer;
    color: inherit;
    font: inherit;
    transition: background 0.15s var(--ease-out, ease);
}
.row:last-child { border-bottom: none; }
.row:hover { background: rgba(255,255,255,0.02); }
.row:focus-visible {
    outline: 1px solid var(--c-accent);
    outline-offset: -1px;
    background: var(--c-accent-dim);
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
    background: var(--c-accent-dim);
    border: 1px solid rgba(220,38,38,0.25);
    color: #F87171;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-size: 13px; font-weight: 700;
    letter-spacing: var(--ls-display, -0.02em);
}
.client-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.client-name {
    font-family: var(--font-sans);
    font-size: 14px; font-weight: 600;
    color: var(--c-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.client-sub {
    font-family: var(--font-display);
    font-size: 9px; font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-3);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.pill {
    display: inline-block;
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: var(--r-pill, 999px);
    line-height: 1.4;
}
.pill--success { background: var(--c-success-dim); color: #34D399; }
.pill--warn    { background: var(--c-amber-dim); color: #FCD34D; }
.pill--urgent  { background: var(--c-accent-dim); color: #F87171; }
.pill--info    { background: rgba(59,130,246,0.12); color: #60A5FA; }
.pill--gold    { background: rgba(200,167,105,0.12); color: var(--c-amber, #D4A80E); border: 1px solid rgba(200,167,105,0.25); }
.pill--amber   { background: var(--c-amber-dim); color: #FCD34D; }
.pill--red     { background: var(--c-accent-dim); color: #F87171; }
.pill--neutral { background: rgba(255,255,255,0.05); color: var(--c-text-3); }

.amount-num {
    font-family: var(--font-display);
    font-variant-numeric: tabular-nums; font-feature-settings: "tnum";
    font-size: 14px; font-weight: 700;
    color: var(--c-text);
}
.amount-cop {
    display: block;
    font-family: var(--font-display);
    font-size: 9px; font-weight: 600;
    letter-spacing: 1.2px;
    color: var(--c-text-3);
    margin-top: 2px;
}

.method {
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600;
    letter-spacing: 1.2px;
    color: var(--c-text-2);
}

.date-rel {
    display: block;
    font-family: var(--font-sans);
    font-size: 13px; font-weight: 400;
    color: var(--c-text-2);
}
.date-abs {
    display: block;
    font-family: var(--font-display);
    font-size: 9px; font-weight: 600;
    letter-spacing: 1.2px;
    color: var(--c-text-3);
    margin-top: 1px;
}

.col-actions { text-align: right; }
.chev {
    color: var(--c-text-3);
    font-size: 18px;
    line-height: 1;
}
.row:hover .chev { color: #F87171; }

.dash { color: var(--c-text-3); }
</style>
