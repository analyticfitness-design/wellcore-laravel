<script setup>
import { ref, watch } from 'vue';
import { useApi } from '../../../composables/useApi';

const props = defineProps({
    coach: { type: Object, default: null },
});
const emit = defineEmits(['close', 'success']);

const api = useApi();

const form = ref({ name: '', email: '', whatsapp: '', active: true });
const errors = ref({});
const saving = ref(false);
const apiError = ref('');

watch(() => props.coach, (val) => {
    if (val) {
        form.value = {
            name: val.name ?? '',
            email: val.email ?? '',
            whatsapp: val.whatsapp ?? '',
            active: !!val.active,
        };
        errors.value = {};
        apiError.value = '';
    }
}, { immediate: true });

function close() {
    if (saving.value) return;
    emit('close');
}

async function submit() {
    if (!props.coach) return;
    saving.value = true;
    errors.value = {};
    apiError.value = '';
    try {
        await api.put(`/api/v/admin/coaches/manage/${props.coach.id}`, form.value);
        emit('success', { ...props.coach, ...form.value });
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors ?? {};
        } else {
            apiError.value = err.response?.data?.error || 'No se pudo guardar.';
        }
    } finally {
        saving.value = false;
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
            <span class="eyebrow">PERFIL DE COACH</span>
            <h2 class="title">EDITAR DATOS</h2>
            <button class="close-btn" type="button" aria-label="Cerrar" @click="close">×</button>
          </header>

          <form class="card-body" @submit.prevent="submit">
            <label class="field">
              <span class="field-label">NOMBRE</span>
              <input v-model="form.name" type="text" class="field-input" :disabled="saving" />
              <p v-if="errors.name" class="field-error">{{ errors.name[0] }}</p>
            </label>

            <label class="field">
              <span class="field-label">EMAIL</span>
              <input v-model="form.email" type="email" class="field-input" :disabled="saving" />
              <p v-if="errors.email" class="field-error">{{ errors.email[0] }}</p>
            </label>

            <label class="field">
              <span class="field-label">WHATSAPP</span>
              <input v-model="form.whatsapp" type="tel" class="field-input field-input--mono" placeholder="+57 300 123 4567" :disabled="saving" />
              <p v-if="errors.whatsapp" class="field-error">{{ errors.whatsapp[0] }}</p>
            </label>

            <div class="toggle-row">
              <div class="toggle-label">
                <span class="toggle-title">COACH ACTIVO</span>
                <span class="toggle-hint">Puede iniciar sesion y gestionar clientes.</span>
              </div>
              <button
                type="button"
                role="switch"
                class="toggle"
                :class="{ 'toggle--on': form.active }"
                :aria-checked="String(form.active)"
                :disabled="saving"
                @click="form.active = !form.active"
              ><span class="toggle-knob"></span></button>
            </div>

            <div v-if="apiError" class="error-block">{{ apiError }}</div>

            <div class="actions">
              <button type="button" class="btn btn--secondary" :disabled="saving" @click="close">Cancelar</button>
              <button type="submit" class="btn btn--primary" :disabled="saving">
                {{ saving ? 'Guardando…' : 'Guardar cambios' }}
              </button>
            </div>
          </form>
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
.close-btn:hover { background: rgba(255, 255, 255, 0.04); color: var(--color-wc-text); }

.card-body {
    padding: 18px 22px 22px;
    display: flex; flex-direction: column; gap: 12px;
}

.field { display: flex; flex-direction: column; gap: 5px; }
.field-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--color-wc-text-secondary);
}
.field-input {
    height: 38px;
    border-radius: 10px;
    border: 1px solid var(--color-wc-border);
    background: rgba(255, 255, 255, 0.03);
    padding: 0 12px;
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--color-wc-text);
}
.field-input--mono { font-family: var(--font-mono, monospace); letter-spacing: 0.08em; }
.field-input:focus { outline: none; border-color: var(--color-wc-accent, #DC2626); }
.field-error {
    margin: 0;
    font-size: 11px;
    color: var(--color-wc-red-text, #F87171);
}

.toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-radius: 10px;
    border: 1px solid var(--color-wc-border);
    padding: 12px;
    background: rgba(255, 255, 255, 0.02);
    gap: 10px;
}
.toggle-label { display: flex; flex-direction: column; gap: 2px; }
.toggle-title {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--color-wc-text);
}
.toggle-hint {
    font-family: var(--font-mono, monospace);
    font-size: 8px; letter-spacing: 0.14em;
    color: var(--color-wc-text-tertiary);
}
.toggle {
    width: 44px; height: 24px;
    border-radius: 12px;
    border: 1px solid var(--color-wc-border);
    background: rgba(255, 255, 255, 0.04);
    position: relative;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
    flex-shrink: 0;
}
.toggle--on {
    background: var(--color-wc-green-soft, rgba(16, 185, 129, 0.18));
    border-color: rgba(16, 185, 129, 0.45);
}
.toggle-knob {
    position: absolute;
    top: 2px; left: 2px;
    width: 18px; height: 18px;
    border-radius: 50%;
    background: var(--color-wc-text-tertiary);
    transition: transform 0.18s var(--ease-out, ease), background 0.15s var(--ease-out, ease);
}
.toggle--on .toggle-knob {
    transform: translateX(20px);
    background: var(--color-wc-green-text, #34D399);
}

.error-block {
    border-radius: 10px;
    border: 1px solid rgba(220, 38, 38, 0.35);
    background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.1));
    padding: 10px 12px;
    font-size: 12px;
    color: var(--color-wc-red-text, #F87171);
}

.actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 4px; }
.btn {
    padding: 9px 16px;
    border-radius: 10px;
    font-family: var(--font-sans);
    font-size: 13px; font-weight: 600;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease);
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
