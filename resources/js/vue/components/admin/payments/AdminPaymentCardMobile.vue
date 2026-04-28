<script setup>
import { useAdminPaymentsStore } from '../../../stores/adminPayments';

const props = defineProps({
    payment: { type: Object, required: true },
});

const store = useAdminPaymentsStore();

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

function avatarInitial(name) {
    return (name || '?').trim().charAt(0).toUpperCase() || '?';
}
</script>

<template>
  <button class="payment-card" type="button" @click="store.openDetail(payment)">
    <div class="card-head">
      <span class="avatar" aria-hidden="true">{{ avatarInitial(payment.buyer_name || payment.client_name) }}</span>
      <div class="head-info">
        <span class="name">{{ payment.buyer_name || payment.client_name || 'Sin nombre' }}</span>
        <span class="meta">
          <span v-if="payment.plan && payment.plan !== '-'">{{ payment.plan }}</span>
          <span v-if="payment.payment_method && payment.payment_method !== '-'"> · {{ payment.payment_method }}</span>
        </span>
      </div>
      <span class="pill" :class="`pill--${statusVariant(payment.status)}`">{{ statusLabel(payment.status) }}</span>
    </div>

    <div class="card-foot">
      <div class="amount">
        <span class="amount-num">${{ payment.amount_fmt || payment.amount || '0' }}</span>
        <span class="amount-cop">COP</span>
      </div>
      <span class="time">{{ payment.time_ago || payment.created_at || '—' }}</span>
    </div>
  </button>
</template>

<style scoped>
.payment-card {
    display: block;
    width: 100%;
    text-align: left;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 14px;
    color: inherit;
    font: inherit;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), transform 0.15s var(--ease-out, ease);
}
.payment-card:active { transform: scale(0.98); }
.payment-card:hover { background: rgba(17, 17, 17, 0.85); }

.card-head {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 10px;
    align-items: center;
}
.avatar {
    width: 34px;
    height: 34px;
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
.head-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.name {
    font-family: var(--font-sans);
    font-size: 14px;
    font-weight: 600;
    color: var(--color-wc-text);
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.meta {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    margin-top: 2px;
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
    flex-shrink: 0;
}
.pill--success { background: var(--color-wc-green-soft, rgba(16, 185, 129, 0.1)); color: var(--color-wc-green-text, #34D399); }
.pill--warn    { background: var(--color-wc-amber-soft, rgba(245, 158, 11, 0.1)); color: var(--color-wc-amber-text, #FCD34D); }
.pill--urgent  { background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.1)); color: var(--color-wc-red-text, #F87171); }
.pill--info    { background: var(--color-wc-blue-soft, rgba(59, 130, 246, 0.1)); color: var(--color-wc-blue-text, #60A5FA); }

.card-foot {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid rgba(255, 255, 255, 0.04);
}
.amount {
    display: flex;
    align-items: baseline;
    gap: 6px;
}
.amount-num {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-feature-settings: 'tnum' 1;
    font-size: 16px;
    font-weight: 700;
    color: var(--color-wc-text);
}
.amount-cop {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.18em;
    color: var(--color-wc-text-tertiary);
}
.time {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.14em;
    color: var(--color-wc-text-tertiary);
}
</style>
