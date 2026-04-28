<script setup>
import { ref, computed, watch } from 'vue';
import { useAuthStore } from '../../../stores/auth';

const props = defineProps({
    coach: { type: Object, default: null },
});
const emit = defineEmits(['close', 'success']);

const authStore = useAuthStore();

const reason = ref('');
const stage = ref('confirm'); // confirm | reason | loading
const error = ref('');

const minReasonLen = 10;

const reasonValid = computed(() => reason.value.trim().length >= minReasonLen);

watch(() => props.coach, (val) => {
    if (val) {
        reason.value = '';
        stage.value = 'confirm';
        error.value = '';
    }
});

function close() {
    if (stage.value === 'loading') return;
    emit('close');
}

function goReason() {
    stage.value = 'reason';
}

async function submit() {
    if (!reasonValid.value || !props.coach) return;
    stage.value = 'loading';
    error.value = '';
    try {
        // Backend log captura admin/target/timestamp/IP. La razon se envia
        // como header informativo para correlacion en auditoria.
        sessionStorage.setItem('wc_impersonation_reason', reason.value.trim().slice(0, 200));
        await authStore.startImpersonation({
            type: 'admin',
            targetId: props.coach.id,
        });
        emit('success', props.coach);
        // Forzamos navegacion full reload para que el banner de impersonacion
        // monte limpio en el portal del coach.
        window.location.href = '/coach';
    } catch (e) {
        error.value = e.message || 'No se pudo iniciar la impersonacion.';
        stage.value = 'reason';
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
            <span class="eyebrow">SESION DELEGADA</span>
            <h2 class="title">VER PORTAL DE COACH</h2>
            <button class="close-btn" type="button" aria-label="Cerrar" @click="close">×</button>
          </header>

          <!-- Stage: confirm ───────────────────────────────────────────── -->
          <section v-if="stage === 'confirm'" class="card-body">
            <p class="lede">
              Estas por entrar al portal de
              <span class="coach-name">{{ coach?.name }}</span>
              <span class="coach-handle">(@{{ coach?.username }})</span>.
            </p>

            <ul class="rules">
              <li>Veras todo lo que ve el coach.</li>
              <li>Cada accion queda en su nombre, marcada en auditoria.</li>
              <li>Vuelves cuando quieras desde el banner de impersonacion.</li>
              <li>Sesion delegada: 60 minutos.</li>
            </ul>

            <div v-if="coach && !coach.active" class="warn-block">
              Este coach esta inactivo. Estas entrando solo en modo soporte.
            </div>

            <p class="philosophical">
              "Impersonar es un acto de confianza, no de poder. Documenta la razon."
            </p>

            <div class="actions">
              <button class="btn btn--secondary" type="button" @click="close">Cancelar</button>
              <button class="btn btn--primary" type="button" @click="goReason">Continuar</button>
            </div>
          </section>

          <!-- Stage: reason ────────────────────────────────────────────── -->
          <section v-else class="card-body">
            <label class="reason-label">
              <span class="reason-eyebrow">RAZON DEL ACCESO</span>
              <span class="reason-hint">Visible en el log de auditoria. Minimo {{ minReasonLen }} caracteres.</span>
            </label>
            <textarea
              v-model="reason"
              class="reason-input"
              rows="3"
              placeholder="Soporte cliente XX, validar respuesta del ticket, revisar plan asignado..."
              :disabled="stage === 'loading'"
            ></textarea>
            <p class="reason-counter" :class="{ 'reason-counter--ok': reasonValid }">
              {{ reason.trim().length }} / {{ minReasonLen }}+ caracteres
            </p>

            <div v-if="error" class="error-block">{{ error }}</div>

            <div class="actions">
              <button
                class="btn btn--secondary"
                type="button"
                :disabled="stage === 'loading'"
                @click="stage = 'confirm'"
              >Atras</button>
              <button
                class="btn btn--primary"
                type="button"
                :disabled="!reasonValid || stage === 'loading'"
                @click="submit"
              >
                {{ stage === 'loading' ? 'Entrando…' : 'Entrar al portal' }}
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
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(6px);
    z-index: 80;
}
.modal-frame {
    position: fixed;
    inset: 0;
    z-index: 90;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    pointer-events: none;
}
.modal-card {
    pointer-events: auto;
    width: 100%;
    max-width: 460px;
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
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.title {
    font-family: var(--font-display);
    font-size: 22px;
    letter-spacing: 0.04em;
    color: var(--color-wc-text);
    margin: 0;
}
.close-btn {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: transparent;
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text-secondary);
    font-size: 18px;
    line-height: 1;
    cursor: pointer;
}
.close-btn:hover {
    background: rgba(255, 255, 255, 0.04);
    color: var(--color-wc-text);
}

.card-body {
    padding: 18px 22px 22px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.lede {
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--color-wc-text-secondary);
    line-height: 1.55;
    margin: 0;
}
.coach-name { color: var(--color-wc-text); font-weight: 600; }
.coach-handle {
    font-family: var(--font-mono, monospace);
    font-size: 11px;
    letter-spacing: 0.12em;
    color: var(--color-wc-text-tertiary);
    margin-left: 4px;
}

.rules {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--color-wc-text-tertiary);
}
.rules li::before {
    content: '— ';
    color: var(--color-wc-red-text, #F87171);
    margin-right: 4px;
}

.warn-block {
    border: 1px solid rgba(245, 158, 11, 0.32);
    background: var(--color-wc-amber-soft, rgba(245, 158, 11, 0.1));
    color: var(--color-wc-amber-text, #FCD34D);
    border-radius: 10px;
    padding: 10px 12px;
    font-family: var(--font-mono, monospace);
    font-size: 10px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.philosophical {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 13px;
    color: var(--color-wc-gold, #C8A769);
    line-height: 1.55;
    margin: 0;
    padding-top: 4px;
    border-top: 1px solid var(--color-wc-border);
}

.reason-label {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.reason-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-secondary);
}
.reason-hint {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.16em;
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
    min-height: 84px;
}
.reason-input:focus {
    outline: none;
    border-color: var(--color-wc-accent, #DC2626);
}
.reason-counter {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.16em;
    color: var(--color-wc-text-tertiary);
    margin: 0;
}
.reason-counter--ok { color: var(--color-wc-green-text, #34D399); }

.error-block {
    border-radius: 10px;
    border: 1px solid rgba(220, 38, 38, 0.35);
    background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.1));
    padding: 10px 12px;
    font-size: 12px;
    color: var(--color-wc-red-text, #F87171);
}

.actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 4px;
}
.btn {
    padding: 9px 16px;
    border-radius: 10px;
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.btn:disabled { opacity: 0.55; cursor: not-allowed; }
.btn--primary {
    background: var(--color-wc-accent, #DC2626);
    border: 1px solid var(--color-wc-accent, #DC2626);
    color: #fff;
}
.btn--primary:hover:not(:disabled) { background: #B91C1C; }
.btn--secondary {
    background: transparent;
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text-secondary);
}
.btn--secondary:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.04);
    color: var(--color-wc-text);
}

/* ── Transitions ─────────────────────────────────────────────────────── */
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
