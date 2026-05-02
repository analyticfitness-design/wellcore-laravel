<script setup>
import { computed } from 'vue';

const props = defineProps({
    client: { type: Object, default: null },
});

const payments = computed(() => props.client?.payments || []);

const STATUS_VARIANT = {
    approved:   'success',
    completed:  'success',
    pending:    'amber',
    rejected:   'danger',
    failed:     'danger',
    refunded:   'neutral',
};
function statusVariant(s) {
    const k = (s || '').toLowerCase();
    return STATUS_VARIANT[k] || 'neutral';
}
function formatAmount(p) {
    if (p.amount == null) return '—';
    const n = Number(p.amount);
    if (Number.isNaN(n)) return String(p.amount);
    return new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 }).format(n);
}
</script>

<template>
  <div class="payments-panel">
    <article class="card">
      <header class="card-head">
        <span class="card-eyebrow">PAGOS DEL CLIENTE · {{ payments.length }}</span>
      </header>

      <div v-if="payments.length" class="pay-table">
        <div class="pay-head">
          <span class="th col-desc">DESCRIPCION</span>
          <span class="th col-date">FECHA</span>
          <span class="th col-amt">MONTO</span>
          <span class="th col-status">ESTADO</span>
        </div>
        <div v-for="(p, i) in payments" :key="i" class="pay-row">
          <div class="cell col-desc">
            <span class="desc-text">{{ p.description || '—' }}</span>
          </div>
          <div class="cell col-date">
            <span class="line-mono">{{ p.date || '—' }}</span>
          </div>
          <div class="cell col-amt">
            <span class="line-data">${{ formatAmount(p) }}</span>
            <span class="line-mono line-mono--dim">{{ (p.currency || 'COP').toUpperCase() }}</span>
          </div>
          <div class="cell col-status">
            <span class="pill" :class="`pill--${statusVariant(p.status)}`">
              {{ (p.status || 'PENDIENTE').toUpperCase() }}
            </span>
          </div>
        </div>
      </div>

      <div v-else class="card-empty">
        <div class="empty-num">—</div>
        <p class="empty-msg">"Sin pagos registrados todavía. La lista mostrará Wompi, MercadoPago y Nequi cuando llegue el primer movimiento."</p>
      </div>
    </article>
  </div>
</template>

<style scoped>
.payments-panel { display: flex; flex-direction: column; gap: 12px; }

.card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.65);
    padding: 16px;
}
.card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.card-eyebrow {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
}

.pay-table {
    display: flex;
    flex-direction: column;
    gap: 0;
}
.pay-head,
.pay-row {
    display: grid;
    grid-template-columns: minmax(140px, 1.5fr) minmax(80px, 0.8fr) minmax(90px, 0.8fr) minmax(90px, 0.8fr);
    gap: 10px;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}
.pay-row:last-child { border-bottom: none; }

.th {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.cell { min-width: 0; }
.desc-text {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--c-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
}
.line-mono {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.4px;
    color: var(--c-text-2);
    text-transform: uppercase;
}
.line-mono--dim { color: var(--c-text-3); display: inline-block; margin-left: 4px; }
.line-data {
    font-family: var(--font-display);
    font-feature-settings: 'tnum' 1;
    font-size: 14px;
    font-weight: 700;
    color: var(--c-text);
}

.pill { display: inline-block; font-family: var(--font-display); font-size: 8px; letter-spacing: 1.6px; text-transform: uppercase; padding: 3px 7px; border-radius: var(--r-pill, 999px); line-height: 1.4; }
.pill--success { background: rgba(16,185,129,0.1); color: #34D399; }
.pill--amber   { background: rgba(245,158,11,0.1); color: #FCD34D; }
.pill--danger  { background: var(--c-accent-dim); color: #F87171; }
.pill--neutral { background: rgba(255, 255, 255, 0.04); color: var(--c-text-3); }

.card-empty {
    text-align: center;
    padding: 24px 8px 16px;
}
.empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--c-surface-2);
    letter-spacing: 0.8px;
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: var(--c-text-3);
    margin: 0;
    max-width: 380px;
    margin-inline: auto;
    text-wrap: balance;
}
</style>
