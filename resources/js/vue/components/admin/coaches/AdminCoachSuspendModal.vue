<script setup>
import { ref, computed, watch } from 'vue';
import { useApi } from '../../../composables/useApi';
import { useAdminCoachListStore } from '../../../stores/adminCoachList';

const props = defineProps({
    coach: { type: Object, default: null },
});
const emit = defineEmits(['close', 'success']);

const api = useApi();
const list = useAdminCoachListStore();

const reason = ref('');
const loading = ref(false);
const error = ref('');

const clientCount = computed(() => Number(props.coach?.client_count ?? 0));
const isBlocked = computed(() => clientCount.value > 0);

const loadBalance = computed(() => {
    if (!isBlocked.value) return [];
    return list.loadBalancedOptions
        .filter((c) => c.id !== props.coach?.id)
        .slice(0, 5);
});

const reasonValid = computed(() => reason.value.trim().length >= 5);

watch(() => props.coach, (val) => {
    if (val) {
        reason.value = '';
        error.value = '';
        loading.value = false;
    }
});

function close() {
    if (loading.value) return;
    emit('close');
}

async function submit() {
    if (isBlocked.value) return;
    if (!reasonValid.value || !props.coach) return;

    loading.value = true;
    error.value = '';
    try {
        await api.delete(`/api/v/admin/coaches/manage/${props.coach.id}`);
        // Backend persiste deactivation. La razon queda en sessionStorage para
        // rastreo correlacionable con audit logs hasta que el endpoint reciba
        // un reason en el body (follow-up backend).
        sessionStorage.setItem(`wc_suspend_reason_${props.coach.id}`, reason.value.trim().slice(0, 280));
        emit('success', props.coach);
    } catch (err) {
        if (err.response?.status === 409) {
            error.value = err.response.data?.error
                || 'Este coach tiene clientes activos. Reasignalos antes de desactivar.';
        } else {
            error.value = err.response?.data?.error
                || err.message
                || 'No se pudo desactivar el coach.';
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="coach" class="modal-backdrop" @click="close" aria-hidden="true"></div>
    </Transition>

    <Transition name="modal-pop">
      <div v-if="coach" class="modal-frame" role="dialog" aria-modal="true">
        <div class="modal-card" @click.stop>
          <header class="card-head">
            <span class="eyebrow">DESACTIVACION DE COACH</span>
            <h2 class="title">SUSPENDER ACCESO</h2>
            <button class="close-btn" type="button" aria-label="Cerrar" @click="close">×</button>
          </header>

          <section class="card-body">
            <p class="lede">
              Estas por desactivar a
              <span class="coach-name">{{ coach?.name }}</span>.
              Perdera acceso a la plataforma y dejara de gestionar clientes.
            </p>

            <!-- ── Bloqueo: tiene clientes activos ─────────────────── -->
            <div v-if="isBlocked" class="block-card block-card--warn">
              <span class="block-eyebrow">PRIMERO REASIGNA</span>
              <p class="block-msg">
                Este coach tiene <strong>{{ clientCount }}</strong>
                cliente{{ clientCount === 1 ? '' : 's' }} activo{{ clientCount === 1 ? '' : 's' }} a su cargo.
                <br>
                Reasignalos a otro coach antes de suspender el acceso.
              </p>

              <div v-if="loadBalance.length" class="balance-block">
                <span class="balance-label">CARGA ACTUAL DEL EQUIPO</span>
                <ul class="balance-list">
                  <li v-for="c in loadBalance" :key="c.id" class="balance-row">
                    <span class="balance-name">{{ c.name }}</span>
                    <span class="balance-clients">{{ c.client_count || 0 }} clientes</span>
                  </li>
                </ul>
                <p class="balance-hint">
                  "{{ loadBalance[0]?.name }} tiene la carga mas baja del equipo. Seria el balance natural."
                </p>
              </div>

              <p class="follow-up">
                La transferencia masiva drag-drop llega en la siguiente fase. Por ahora,
                editalos uno a uno desde la pestana Clientes.
              </p>
            </div>

            <!-- ── OK: sin clientes ────────────────────────────────── -->
            <template v-else>
              <label class="reason-label">
                <span class="reason-eyebrow">RAZON DE LA SUSPENSION</span>
                <span class="reason-hint">Para auditoria interna. Minimo 5 caracteres.</span>
              </label>
              <textarea
                v-model="reason"
                class="reason-input"
                rows="3"
                placeholder="Coach renuncio, baja temporal, traslado, no responde..."
                :disabled="loading"
              ></textarea>
            </template>

            <div v-if="error" class="error-block">{{ error }}</div>

            <div class="actions">
              <button class="btn btn--secondary" type="button" :disabled="loading" @click="close">
                Cancelar
              </button>
              <button
                v-if="!isBlocked"
                class="btn btn--danger"
                type="button"
                :disabled="!reasonValid || loading"
                @click="submit"
              >
                {{ loading ? 'Desactivando…' : 'Desactivar coach' }}
              </button>
            </div>
          </section>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-backdrop {
    position: fixed; inset: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(6px);
    z-index: 80;
}
.modal-frame {
    position: fixed; inset: 0; z-index: 90;
    display: flex; align-items: center; justify-content: center;
    padding: 16px;
    pointer-events: none;
}
.modal-card {
    pointer-events: auto;
    width: 100%; max-width: 460px;
    background: var(--color-wc-bg-secondary, #111111);
    border: 1px solid var(--color-wc-border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
}

.card-head {
    padding: 20px 22px 16px;
    border-bottom: 1px solid var(--color-wc-border);
    position: relative;
    display: flex; flex-direction: column; gap: 4px;
}
.eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 8px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.title {
    font-family: var(--font-display);
    font-size: 22px; letter-spacing: 0.04em;
    color: var(--color-wc-text);
    margin: 0;
}
.close-btn {
    position: absolute; top: 14px; right: 14px;
    width: 28px; height: 28px;
    border-radius: 8px;
    background: transparent;
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text-secondary);
    font-size: 18px; line-height: 1; cursor: pointer;
}
.close-btn:hover { background: rgba(255,255,255,0.04); color: var(--color-wc-text); }

.card-body {
    padding: 18px 22px 22px;
    display: flex; flex-direction: column; gap: 14px;
}
.lede {
    font-family: var(--font-sans);
    font-size: 13px; line-height: 1.55;
    color: var(--color-wc-text-secondary);
    margin: 0;
}
.coach-name { color: var(--color-wc-text); font-weight: 600; }

.block-card {
    border-radius: 12px;
    padding: 14px;
    display: flex; flex-direction: column; gap: 10px;
}
.block-card--warn {
    border: 1px solid rgba(245, 158, 11, 0.32);
    background: rgba(245, 158, 11, 0.06);
}
.block-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--color-wc-amber-text, #FCD34D);
}
.block-msg {
    font-family: var(--font-sans);
    font-size: 13px; line-height: 1.55;
    color: var(--color-wc-text-secondary);
    margin: 0;
}
.block-msg strong {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 14px;
    color: var(--color-wc-amber-text, #FCD34D);
}

.balance-block {
    border-top: 1px solid rgba(245, 158, 11, 0.2);
    padding-top: 10px;
    display: flex; flex-direction: column; gap: 8px;
}
.balance-label {
    font-family: var(--font-mono, monospace);
    font-size: 8px; letter-spacing: 0.2em; text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.balance-list {
    list-style: none; padding: 0; margin: 0;
    display: flex; flex-direction: column;
}
.balance-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    padding: 5px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    font-family: var(--font-sans);
    font-size: 12px;
}
.balance-row:last-child { border-bottom: none; }
.balance-name {
    color: var(--color-wc-text-secondary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex: 1;
    margin-right: 10px;
}
.balance-clients {
    font-family: var(--font-mono, monospace);
    font-size: 10px;
    letter-spacing: 0.14em;
    color: var(--color-wc-text-tertiary);
    flex-shrink: 0;
}
.balance-hint {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 12px;
    color: var(--color-wc-gold, #C8A769);
    line-height: 1.5;
    margin: 0;
}
.follow-up {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.14em;
    color: var(--color-wc-text-tertiary);
    line-height: 1.5;
    margin: 0;
    opacity: 0.75;
}

.reason-label { display: flex; flex-direction: column; gap: 2px; }
.reason-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--color-wc-text-secondary);
}
.reason-hint {
    font-family: var(--font-mono, monospace);
    font-size: 8px; letter-spacing: 0.16em;
    color: var(--color-wc-text-tertiary);
}
.reason-input {
    width: 100%;
    border-radius: 10px;
    border: 1px solid var(--color-wc-border);
    background: rgba(255, 255, 255, 0.03);
    padding: 10px 12px;
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--color-wc-text);
    line-height: 1.5;
    resize: vertical;
    min-height: 78px;
}
.reason-input:focus { outline: none; border-color: var(--color-wc-accent, #DC2626); }

.error-block {
    border-radius: 10px;
    border: 1px solid rgba(220, 38, 38, 0.35);
    background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.1));
    padding: 10px 12px;
    font-size: 12px;
    color: var(--color-wc-red-text, #F87171);
}

.actions {
    display: flex; gap: 8px; justify-content: flex-end; margin-top: 4px;
}
.btn {
    padding: 9px 16px;
    border-radius: 10px;
    font-family: var(--font-sans);
    font-size: 13px; font-weight: 600;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.btn:disabled { opacity: 0.55; cursor: not-allowed; }
.btn--secondary {
    background: transparent;
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text-secondary);
}
.btn--secondary:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.04);
    color: var(--color-wc-text);
}
.btn--danger {
    background: var(--color-wc-accent, #DC2626);
    border: 1px solid var(--color-wc-accent, #DC2626);
    color: #fff;
}
.btn--danger:hover:not(:disabled) { background: #B91C1C; }

/* Transitions identicas al impersonate */
.modal-fade-enter-active,
.modal-fade-leave-active { transition: opacity 0.2s var(--ease-out, ease); }
.modal-fade-enter-from,
.modal-fade-leave-to { opacity: 0; }

.modal-pop-enter-active { transition: opacity 0.22s var(--ease-out, ease), transform 0.22s var(--ease-out, ease); }
.modal-pop-leave-active { transition: opacity 0.18s var(--ease-out, ease), transform 0.18s var(--ease-out, ease); }
.modal-pop-enter-from,
.modal-pop-leave-to { opacity: 0; transform: translateY(8px) scale(0.98); }

@media (prefers-reduced-motion: reduce) {
    .modal-fade-enter-active,
    .modal-fade-leave-active,
    .modal-pop-enter-active,
    .modal-pop-leave-active { transition: none !important; }
    .modal-pop-enter-from,
    .modal-pop-leave-to { transform: none; }
}
</style>
