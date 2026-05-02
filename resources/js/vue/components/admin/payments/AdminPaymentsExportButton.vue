<script setup>
import { computed } from 'vue';
import { useAdminPaymentsStore } from '../../../stores/adminPayments';
import { useToast } from '../../../composables/useToast';

const store = useAdminPaymentsStore();
const toast = useToast();

const disabled = computed(() => !store.payments || store.payments.length === 0);

function statusLabel(status) {
    const map = {
        approved: 'Aprobado',
        pending: 'Pendiente',
        declined: 'Rechazado',
        voided: 'Anulado',
        error: 'Error',
    };
    return map[status] || status || '—';
}

function exportCsv() {
    if (disabled.value) return;
    const rows = store.payments.map(p => [
        p.buyer_name || p.client_name || '—',
        p.plan || '—',
        p.amount_fmt || p.amount || '0',
        statusLabel(p.status),
        p.payment_method || '—',
        p.wompi_reference || p.payu_reference || '—',
        p.created_at || '—',
    ]);
    const headers = ['Cliente', 'Plan', 'Monto', 'Estado', 'Metodo', 'Referencia', 'Fecha'];
    const csv = [headers, ...rows]
        .map(r => r.map(c => `"${String(c).replace(/"/g, '""')}"`).join(','))
        .join('\n');

    const blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `pagos_wellcore_${new Date().toISOString().slice(0, 10)}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);

    toast.success(`${rows.length} pagos exportados a CSV.`);
}
</script>

<template>
  <button
    type="button"
    class="export-btn"
    :disabled="disabled"
    @click="exportCsv"
  >
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
      <path d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <span>EXPORTAR CSV</span>
  </button>
</template>

<style scoped>
.export-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 36px;
    padding: 0 14px;
    border-radius: var(--r-sm, 12px);
    background: rgba(17, 17, 17, 0.7);
    color: var(--c-text);
    border: 1px solid var(--c-border);
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    cursor: pointer;
    transition: border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.export-btn:hover:not(:disabled) {
    border-color: rgba(255,255,255,0.12);
    color: #F87171;
}
.export-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.export-btn svg { color: currentColor; }
</style>
