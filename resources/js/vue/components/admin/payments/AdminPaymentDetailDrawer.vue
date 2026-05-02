<script setup>
import { computed, watch, onUnmounted } from 'vue';
import { useAdminPaymentsStore } from '../../../stores/adminPayments';

const store = useAdminPaymentsStore();

const open = computed(() => store.selectedPayment !== null);
const p = computed(() => store.selectedPayment || {});

watch(open, (val) => {
    if (val) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
});

function close() {
    store.closeDetail();
}

function onBackdrop() {
    close();
}

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

const wompiRef = computed(() => p.value.wompi_reference || p.value.payu_reference || '—');
const wompiTx = computed(() => p.value.wompi_transaction_id || p.value.payu_transaction_id || '—');

function openRefund() {
    if (!p.value.id) return;
    store.openRefund(p.value);
    close();
}
</script>

<template>
  <Teleport to="body">
    <Transition name="drawer-fade">
      <div v-if="open" class="drawer-backdrop" @click="onBackdrop" aria-hidden="true"></div>
    </Transition>

    <Transition name="drawer-slide">
      <aside v-if="open" class="drawer-panel" role="dialog" aria-label="Detalle de pago">
        <header class="drawer-head">
          <div class="head-text">
            <span class="eyebrow">DETALLE PAGO</span>
            <h2 class="title">{{ p.buyer_name || 'Sin nombre' }}</h2>
            <span class="pill" :class="`pill--${statusVariant(p.status)}`">{{ statusLabel(p.status) }}</span>
          </div>
          <button class="head-close" type="button" aria-label="Cerrar" @click="close">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
              <path d="M6 6l12 12M6 18L18 6" stroke-linecap="round" />
            </svg>
          </button>
        </header>

        <div class="drawer-body">
          <section class="block">
            <span class="block-label">MONTO</span>
            <div class="amount-row">
              <span class="amount-num">${{ p.amount_fmt || p.amount || '0' }}</span>
              <span class="amount-cop">COP</span>
            </div>
            <span class="block-sub">{{ p.plan || '—' }} · {{ p.payment_method || '—' }}</span>
          </section>

          <section class="block">
            <span class="block-label">CLIENTE</span>
            <p class="meta-line"><span class="meta-label">NOMBRE</span><span class="meta-val">{{ p.buyer_name || '—' }}</span></p>
            <p v-if="p.client_name && p.client_name !== p.buyer_name" class="meta-line">
              <span class="meta-label">PERFIL</span><span class="meta-val">{{ p.client_name }}</span>
            </p>
            <p v-if="p.email" class="meta-line"><span class="meta-label">EMAIL</span><span class="meta-val">{{ p.email }}</span></p>
          </section>

          <section class="block">
            <span class="block-label">TRANSACCION</span>
            <p class="meta-line"><span class="meta-label">REFERENCIA</span><span class="meta-val mono">{{ wompiRef }}</span></p>
            <p class="meta-line"><span class="meta-label">TX ID</span><span class="meta-val mono">{{ wompiTx }}</span></p>
            <p class="meta-line"><span class="meta-label">CREADO</span><span class="meta-val">{{ p.created_at || '—' }}</span></p>
            <p v-if="p.time_ago" class="meta-line"><span class="meta-label">HACE</span><span class="meta-val">{{ p.time_ago }}</span></p>
          </section>

          <section v-if="p.payu_response || p.wompi_payload" class="block">
            <span class="block-label">PAYLOAD</span>
            <pre class="payload">{{ JSON.stringify(p.wompi_payload || p.payu_response, null, 2) }}</pre>
          </section>

          <section class="block block--editorial">
            <p class="editorial">"El historial completo del pago vive en el panel de Wompi. Aqui solo lo que necesita el operador para decidir."</p>
          </section>
        </div>

        <footer class="drawer-foot">
          <button class="btn btn-secondary" type="button" @click="close">Cerrar</button>
          <button
            v-if="p.status === 'approved'"
            class="btn btn-primary"
            type="button"
            @click="openRefund"
          >
            Solicitar refund
          </button>
        </footer>
      </aside>
    </Transition>
  </Teleport>
</template>

<style scoped>
.drawer-backdrop {
    position: fixed; inset: 0; z-index: 60;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.drawer-panel {
    position: fixed; right: 0; top: 0; bottom: 0; z-index: 70;
    width: min(92vw, 440px);
    background: var(--c-surface);
    border-left: 1px solid var(--c-border);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.drawer-fade-enter-active,
.drawer-fade-leave-active { transition: opacity 0.22s var(--ease-out, ease); }
.drawer-fade-enter-from,
.drawer-fade-leave-to { opacity: 0; }

.drawer-slide-enter-active,
.drawer-slide-leave-active { transition: transform 0.28s var(--ease-out, ease); }
.drawer-slide-enter-from,
.drawer-slide-leave-to { transform: translateX(100%); }

.drawer-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 18px 18px 14px;
    border-bottom: 1px solid var(--c-border);
    gap: 12px;
}
.head-text { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.eyebrow {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.title {
    font-family: var(--font-display);
    font-size: 24px;
    letter-spacing: 0.04em;
    color: var(--c-text);
    line-height: 1.05;
    margin: 0;
    text-transform: uppercase;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.pill {
    display: inline-block;
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: var(--r-pill, 999px);
    align-self: flex-start;
}
.pill--success { background: rgba(16,185,129,0.1); color: #34D399; }
.pill--warn    { background: rgba(245,158,11,0.1); color: #FCD34D; }
.pill--urgent  { background: var(--c-accent-dim); color: #F87171; }
.pill--info    { background: rgba(59,130,246,0.1); color: #60A5FA; }

.head-close {
    width: 32px; height: 32px;
    border-radius: var(--r-sm, 12px);
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--c-border);
    color: var(--c-text-2);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    min-height: var(--tap-comfort, 48px);
    transition: border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.head-close:hover { border-color: rgba(255,255,255,0.12); color: var(--c-text); }

.drawer-body {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: 16px 18px 24px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.block { display: flex; flex-direction: column; gap: 6px; }
.block-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.block-sub {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: var(--c-text-3);
    margin-top: 2px;
}

.amount-row {
    display: flex;
    align-items: baseline;
    gap: 8px;
}
.amount-num {
    font-family: var(--font-display);
    font-size: 36px;
    letter-spacing: 0.03em;
    color: var(--c-text);
    line-height: 1;
}
.amount-cop {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 1.6px;
    color: var(--c-text-3);
}

.meta-line {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin: 0;
    padding: 6px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}
.meta-line:last-child { border-bottom: none; }
.meta-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
    flex-shrink: 0;
}
.meta-val {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--c-text-2);
    text-align: right;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    min-width: 0;
}
.meta-val.mono {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 0.06em;
    color: var(--c-text);
}

.payload {
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm, 12px);
    padding: 10px 12px;
    font-family: var(--font-display);
    font-size: 10px;
    line-height: 1.5;
    color: var(--c-text-2);
    overflow-x: auto;
    margin: 0;
    max-height: 280px;
    overflow-y: auto;
}

.block--editorial { border-top: 1px solid var(--c-border); padding-top: 14px; }
.editorial {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    line-height: 1.55;
    color: #C8A769;
    margin: 0;
    text-wrap: balance;
}

.drawer-foot {
    display: flex;
    gap: 10px;
    padding: 14px 18px;
    border-top: 1px solid var(--c-border);
    background: rgba(0, 0, 0, 0.25);
}
.btn {
    flex: 1;
    min-height: var(--tap-comfort, 48px);
    border-radius: var(--r-sm, 12px);
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.btn-secondary {
    background: rgba(17, 17, 17, 0.7);
    color: var(--c-text);
    border: 1px solid var(--c-border);
}
.btn-secondary:hover { border-color: rgba(255,255,255,0.12); }
.btn-primary {
    background: var(--c-accent);
    color: #fff;
    border: 1px solid var(--c-accent);
}
.btn-primary:hover { background: #B91C1C; }
</style>
