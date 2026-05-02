<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  referral: { type: Object, default: null },
  loading:  { type: Boolean, default: false },
});

const emit = defineEmits(['confirm', 'cancel']);

const method    = ref('descuento_proximo_pago');
const reference = ref('');

watch(() => props.referral, (r) => {
  if (r) { method.value = 'descuento_proximo_pago'; reference.value = ''; }
});

const isNequi = computed(() => method.value === 'nequi');

const METHODS = [
  { key: 'descuento_proximo_pago', label: 'Descuento próximo pago (20%)' },
  { key: 'saldo_cuenta',           label: 'Saldo en cuenta ($50,000 COP)' },
  { key: 'nequi',                  label: 'Transferencia manual NEQUI' },
];

const canConfirm = computed(() =>
  !props.loading && (!isNequi.value || reference.value.trim() !== '')
);

const onConfirm = () => {
  if (!canConfirm.value) return;
  emit('confirm', { method: method.value, reference: reference.value.trim() || null });
};
</script>

<template>
  <Teleport to="body">
    <div
      v-if="referral"
      class="modal-backdrop"
      role="dialog"
      aria-modal="true"
      :aria-labelledby="`payout-modal-title`"
      @click.self="emit('cancel')"
    >
      <div class="modal-panel">
        <header class="modal-header">
          <h2 id="payout-modal-title" class="modal-title">CONFIRMAR PAYOUT</h2>
          <button class="modal-close" aria-label="Cerrar modal" @click="emit('cancel')">
            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" width="16" height="16" aria-hidden="true">
              <path d="M5 5l10 10M15 5 5 15" stroke-linecap="round"/>
            </svg>
          </button>
        </header>

        <div class="modal-body">
          <p class="modal-desc">
            Marcar payout como entregado a
            <strong class="modal-name">{{ referral.referrer_name }}</strong>
            por <span class="modal-amount">$50,000 COP</span>.
          </p>

          <!-- Method selector -->
          <fieldset class="method-group">
            <legend class="method-legend">Método de entrega</legend>
            <label
              v-for="m in METHODS"
              :key="m.key"
              class="method-option"
              :class="{ 'method-option--active': method === m.key }"
            >
              <input
                type="radio"
                name="payout-method"
                :value="m.key"
                v-model="method"
                class="method-radio"
                :aria-checked="method === m.key"
              />
              <span class="method-label">{{ m.label }}</span>
            </label>
          </fieldset>

          <!-- NEQUI reference -->
          <div v-if="isNequi" class="nequi-wrap">
            <label for="nequi-ref" class="nequi-label">Referencia de transferencia</label>
            <input
              id="nequi-ref"
              v-model="reference"
              type="text"
              class="nequi-input"
              placeholder="Ej. TXN20260428-001"
              autocomplete="off"
              required
            />
          </div>
        </div>

        <footer class="modal-footer">
          <button class="btn-cancel" @click="emit('cancel')">Cancelar</button>
          <button
            class="btn-confirm"
            :disabled="!canConfirm"
            :aria-disabled="!canConfirm"
            @click="onConfirm"
          >
            <span v-if="loading">Guardando...</span>
            <span v-else>Confirmar payout</span>
          </button>
        </footer>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9000;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.modal-panel {
    width: 100%;
    max-width: 420px;
    border-radius: var(--r-md, 16px);
    border: 1px solid rgba(255,255,255,0.12);
    background: var(--c-surface);
    overflow: hidden;
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 18px;
    border-bottom: 1px solid var(--c-border);
}
.modal-title {
    font-family: var(--font-display);
    font-size: 13px;
    letter-spacing: 1.2px;
    color: var(--c-text);
    margin: 0;
}
.modal-close {
    background: transparent;
    border: none;
    color: var(--c-text-2);
    cursor: pointer;
    padding: 4px;
    border-radius: 6px;
    display: flex;
    transition: color 0.15s var(--ease-out);
}
.modal-close:hover { color: var(--c-text); }

.modal-body { padding: 18px; display: flex; flex-direction: column; gap: 16px; }
.modal-desc { font-size: 13px; color: var(--c-text-2); margin: 0; line-height: 1.55; }
.modal-name { color: var(--c-text); font-weight: 600; }
.modal-amount { font-family: var(--font-display); color: #34D399; font-weight: 700; font-feature-settings: 'tnum' 1; }

/* ── Method selector ──────────────────────────────────────────────────── */
.method-group { border: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 6px; }
.method-legend {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
    margin-bottom: 8px;
}
.method-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    cursor: pointer;
    transition: border-color 0.15s var(--ease-out), background 0.15s var(--ease-out);
}
.method-option--active {
    border-color: var(--c-accent);
    background: var(--c-accent-dim);
}
.method-radio { accent-color: var(--c-accent); flex-shrink: 0; }
.method-label { font-size: 12px; color: var(--c-text-2); }
.method-option--active .method-label { color: var(--c-text); }

/* ── NEQUI ref ────────────────────────────────────────────────────────── */
.nequi-wrap { display: flex; flex-direction: column; gap: 6px; }
.nequi-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.nequi-input {
    height: 36px;
    padding: 0 12px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255,255,255,0.03);
    font-family: var(--font-display);
    font-size: 12px;
    color: var(--c-text);
    outline: none;
    transition: border-color 0.15s var(--ease-out);
}
.nequi-input:focus { border-color: rgba(255,255,255,0.12); }

/* ── Footer ───────────────────────────────────────────────────────────── */
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 14px 18px;
    border-top: 1px solid var(--c-border);
}
.btn-cancel {
    height: 36px;
    min-height: var(--tap-comfort, 48px);
    padding: 0 16px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: transparent;
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 1.0px;
    text-transform: uppercase;
    color: var(--c-text-2);
    cursor: pointer;
    transition: background 0.15s var(--ease-out), color 0.15s var(--ease-out);
}
.btn-cancel:hover { background: rgba(255,255,255,0.04); color: var(--c-text); }
.btn-confirm {
    height: 36px;
    min-height: var(--tap-comfort, 48px);
    padding: 0 18px;
    border-radius: var(--r-sm, 12px);
    border: none;
    background: var(--c-accent);
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 1.0px;
    text-transform: uppercase;
    color: #fff;
    cursor: pointer;
    transition: opacity 0.15s var(--ease-out);
}
.btn-confirm:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-confirm:not(:disabled):hover { opacity: 0.85; }
</style>
