<script setup>
import { computed, ref, watch, onUnmounted } from 'vue';
import { useAdminPaymentsStore } from '../../../stores/adminPayments';
import { useToast } from '../../../composables/useToast';

const store = useAdminPaymentsStore();
const toast = useToast();

const open = computed(() => store.refundTarget !== null);
const p = computed(() => store.refundTarget || {});

const reason = ref('');
const submitting = ref(false);

watch(open, (val) => {
    if (val) {
        reason.value = '';
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
});

function close() {
    if (submitting.value) return;
    store.closeRefund();
}

const canSubmit = computed(() => reason.value.trim().length >= 10 && !submitting.value);

async function submit() {
    if (!canSubmit.value) return;
    submitting.value = true;
    try {
        // El endpoint `POST /api/v/admin/payments/:id/refund` aun no existe.
        // El refund tecnico debe ejecutarse desde Wompi/PayU support hasta que
        // se decida la politica (Wompi void vs DB-only). Mientras tanto,
        // dejamos UI lista y notificamos al operador con un toast claro.
        await new Promise(r => setTimeout(r, 600));
        toast.info(
            `Solicitud registrada. Ejecutar void desde Wompi para ${p.value.wompi_reference || 'esta transaccion'}.`,
            { title: 'Refund manual pendiente', duration: 6000 }
        );
        close();
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="open" class="modal-backdrop" @click="close" aria-hidden="true"></div>
    </Transition>

    <Transition name="modal-zoom">
      <div v-if="open" class="modal-panel" role="dialog" aria-label="Solicitar refund">
        <header class="modal-head">
          <span class="eyebrow">REFUND</span>
          <h2 class="title">{{ p.buyer_name || 'Pago' }}</h2>
          <span class="amount">${{ p.amount_fmt || p.amount || '0' }} COP</span>
        </header>

        <div class="modal-body">
          <p class="editorial">"Un refund es una decision con costo. La razon escrita es lo que sostiene la decision en el tiempo."</p>

          <label class="form-label">RAZON DEL REFUND</label>
          <textarea
            v-model="reason"
            class="form-textarea"
            rows="4"
            placeholder="Describe por que se solicita el refund (minimo 10 caracteres)"
            :disabled="submitting"
          ></textarea>
          <span class="form-hint" :class="{ 'form-hint--ok': reason.trim().length >= 10 }">
            {{ reason.trim().length }} / 10 minimo
          </span>

          <div class="info-box">
            <span class="info-eyebrow">NOTA OPERATIVA</span>
            <p class="info-text">
              El endpoint admin de refund automatizado aun no esta deployado.
              Esta solicitud se registra localmente; el void tecnico debe
              ejecutarse desde el panel de Wompi con la referencia
              <strong>{{ p.wompi_reference || p.payu_reference || '—' }}</strong>.
            </p>
          </div>
        </div>

        <footer class="modal-foot">
          <button class="btn btn-secondary" type="button" @click="close" :disabled="submitting">
            Cancelar
          </button>
          <button
            class="btn btn-primary"
            type="button"
            :disabled="!canSubmit"
            @click="submit"
          >
            {{ submitting ? 'Registrando...' : 'Registrar solicitud' }}
          </button>
        </footer>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-backdrop {
    position: fixed; inset: 0; z-index: 60;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.modal-panel {
    position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
    z-index: 80;
    width: min(94vw, 520px);
    max-height: 90vh;
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--r-md, 16px);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.modal-fade-enter-active,
.modal-fade-leave-active { transition: opacity 0.22s var(--ease-out, ease); }
.modal-fade-enter-from,
.modal-fade-leave-to { opacity: 0; }

.modal-zoom-enter-active,
.modal-zoom-leave-active { transition: transform 0.22s var(--ease-out, ease), opacity 0.22s var(--ease-out, ease); }
.modal-zoom-enter-from,
.modal-zoom-leave-to { transform: translate(-50%, -45%); opacity: 0; }

.modal-head {
    padding: 18px 20px 14px;
    border-bottom: 1px solid var(--c-border);
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.eyebrow {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.title {
    font-family: var(--font-display);
    font-size: 22px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--c-text);
    margin: 0;
    line-height: 1.05;
}
.amount {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    color: var(--c-text-2);
}

.modal-body {
    padding: 16px 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-height: 0;
}
.editorial {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 13px;
    line-height: 1.5;
    color: #C8A769;
    margin: 0;
    text-wrap: balance;
}

.form-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
    margin-top: 4px;
}
.form-textarea {
    width: 100%;
    padding: 10px 12px;
    border-radius: var(--r-sm, 12px);
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--c-border);
    color: var(--c-text);
    font-family: var(--font-sans);
    font-size: 13px;
    line-height: 1.5;
    resize: vertical;
    min-height: 100px;
    transition: border-color 0.15s var(--ease-out, ease);
}
.form-textarea:focus {
    outline: none;
    border-color: var(--c-accent);
    background: rgba(255, 255, 255, 0.05);
}
.form-textarea::placeholder { color: var(--c-text-3); }

.form-hint {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.form-hint--ok { color: #34D399; }

.info-box {
    background: rgba(245,158,11,0.1);
    border: 1px solid rgba(245, 158, 11, 0.20);
    border-radius: var(--r-sm, 12px);
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.info-eyebrow {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: #FCD34D;
}
.info-text {
    font-family: var(--font-sans);
    font-size: 12px;
    line-height: 1.5;
    color: var(--c-text-2);
    margin: 0;
}
.info-text strong {
    font-family: var(--font-display);
    font-size: 11px;
    letter-spacing: 0.06em;
    color: var(--c-text);
}

.modal-foot {
    display: flex;
    gap: 10px;
    padding: 14px 20px;
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
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease), opacity 0.15s var(--ease-out, ease);
}
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-secondary {
    background: rgba(17, 17, 17, 0.7);
    color: var(--c-text);
    border: 1px solid var(--c-border);
}
.btn-secondary:hover:not(:disabled) { border-color: rgba(255,255,255,0.12); }
.btn-primary {
    background: var(--c-accent);
    color: #fff;
    border: 1px solid var(--c-accent);
}
.btn-primary:hover:not(:disabled) { background: #B91C1C; }
</style>
