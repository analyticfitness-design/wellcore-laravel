<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { usePaymentProofs, type PaymentProof } from '../../composables/usePaymentProofs';
import EmptyState from './ios/EmptyState.vue';

const { proofs, loading, error, fetchProofs, fetchProofFileUrl } = usePaymentProofs();

const openingFileId = ref<number | null>(null);

const PLAN_LABELS: Record<string, string> = {
    rise: 'RISE',
    esencial: 'Esencial',
    metodo: 'Metodo',
    elite: 'Elite',
    presencial: 'Presencial',
};

const METHOD_LABELS: Record<string, string> = {
    transferencia: 'Transferencia',
    efectivo: 'Efectivo',
    nequi: 'Nequi',
    otro: 'Otro',
};

function statusBadgeClass(status: PaymentProof['status']): string {
    switch (status) {
        case 'pendiente':  return 'bg-yellow-500/15 text-yellow-300';
        case 'aprobado':   return 'bg-green-500/15 text-green-400';
        case 'rechazado':  return 'bg-red-600/15 text-red-400';
        case 'expirado':   return 'bg-gray-500/15 text-gray-400';
        default:           return 'bg-gray-500/15 text-gray-400';
    }
}

function statusLabel(status: PaymentProof['status']): string {
    const map: Record<PaymentProof['status'], string> = {
        pendiente: 'Pendiente',
        aprobado:  'Aprobado',
        rechazado: 'Rechazado',
        expirado:  'Expirado',
    };
    return map[status] ?? status;
}

function formatDate(iso: string | null | undefined): string {
    if (!iso) return '—';
    const d = new Date(iso);
    return d.toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatAmount(amount: number | null): string {
    if (amount == null) return '—';
    return `$${amount.toLocaleString('es-CO')}`;
}

async function openFile(proof: PaymentProof) {
    openingFileId.value = proof.id;
    try {
        const url = await fetchProofFileUrl(proof.id);
        if (url) {
            window.open(url, '_blank', 'noopener,noreferrer');
        }
    } finally {
        openingFileId.value = null;
    }
}

function reload() {
    fetchProofs();
}
defineExpose({ reload });

onMounted(fetchProofs);
</script>

<template>
  <div class="rounded-[14px] border border-[var(--b1)] p-5 space-y-4" style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
    <div class="flex items-center justify-between">
      <h3 class="font-display text-lg tracking-wide text-wc-text">Comprobantes enviados</h3>
      <button
        type="button"
        @click="reload"
        class="text-xs text-wc-text-tertiary hover:text-wc-accent transition-colors"
        :disabled="loading"
      >
        Recargar
      </button>
    </div>

    <!-- Loading skeleton -->
    <template v-if="loading">
      <div v-for="n in 3" :key="n" class="animate-pulse flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary p-4">
        <div class="h-4 w-32 rounded bg-wc-border/60"></div>
        <div class="h-4 w-20 rounded bg-wc-border/60 ml-auto"></div>
        <div class="h-4 w-16 rounded bg-wc-border/60"></div>
      </div>
    </template>

    <!-- Error -->
    <div
      v-else-if="error"
      class="rounded-lg border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-400"
    >
      {{ error }}
    </div>

    <!-- Empty state -->
    <EmptyState
      v-else-if="proofs.length === 0"
      kind="tickets"
      title="No hay comprobantes aun"
      subtitle="Los comprobantes que subas apareceran aqui."
    />

    <!-- List -->
    <div v-else class="space-y-2 anim-entry anim-entry-2">
      <div
        v-for="proof in proofs"
        :key="proof.id"
        class="rounded-[14px] border border-[var(--b1)] p-4 space-y-3"
        style="background: var(--s3, var(--wc-bg-tertiary, #1a1a1d));"
      >
        <!-- Top row: client + status badge -->
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <p class="text-sm font-medium text-wc-text truncate">{{ proof.clientName }}</p>
            <p class="text-xs text-wc-text-tertiary truncate">{{ proof.clientEmail }}</p>
          </div>
          <span
            class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide"
            :class="statusBadgeClass(proof.status)"
          >
            {{ statusLabel(proof.status) }}
          </span>
        </div>

        <!-- Middle row: plan, amount, method, date -->
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-wc-text-secondary">
          <span v-if="proof.plan">
            <span class="text-wc-text-tertiary">Plan:</span> {{ PLAN_LABELS[proof.plan] ?? proof.plan }}
          </span>
          <span>
            <span class="text-wc-text-tertiary">Monto:</span>
            <span class="font-data">{{ formatAmount(proof.amount) }}</span>
          </span>
          <span v-if="proof.paymentMethod">
            <span class="text-wc-text-tertiary">Metodo:</span> {{ METHOD_LABELS[proof.paymentMethod] ?? proof.paymentMethod }}
          </span>
          <span class="text-wc-text-tertiary ml-auto">{{ formatDate(proof.submittedAt) }}</span>
        </div>

        <!-- Rejection / review note (rechazado) -->
        <div
          v-if="proof.status === 'rechazado' && proof.reviewNote"
          class="rounded-md border border-red-500/20 bg-red-500/5 px-3 py-2 text-xs text-red-400"
        >
          <span class="font-medium">Motivo de rechazo:</span> {{ proof.reviewNote }}
        </div>

        <!-- Coach note -->
        <div v-if="proof.coachNote" class="text-xs text-wc-text-tertiary italic">
          Nota: {{ proof.coachNote }}
        </div>

        <!-- File button -->
        <div>
          <button
            type="button"
            @click="openFile(proof)"
            :disabled="openingFileId === proof.id"
            class="inline-flex items-center gap-1 text-xs text-wc-accent hover:underline disabled:opacity-50 disabled:cursor-wait"
          >
            <svg v-if="openingFileId === proof.id" class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            <svg v-else class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            {{ openingFileId === proof.id ? 'Abriendo...' : 'Ver comprobante' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
