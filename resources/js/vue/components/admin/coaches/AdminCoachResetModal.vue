<script setup>
import { ref, computed, watch } from 'vue';
import { useApi } from '../../../composables/useApi';

const props = defineProps({
    coach: { type: Object, default: null },
});
const emit = defineEmits(['close', 'success']);

const api = useApi();
const loading = ref(false);
const error = ref('');

const hasEmail = computed(() => !!props.coach?.email);

watch(() => props.coach, (val) => {
    if (val) { error.value = ''; loading.value = false; }
});

function close() {
    if (loading.value) return;
    emit('close');
}

async function submit() {
    if (!props.coach || !hasEmail.value) return;
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.post(`/api/v/admin/coaches/manage/${props.coach.id}/reset-password`);
        emit('success', { email: data.password_sent_to_email || props.coach.email });
    } catch (err) {
        error.value = err.response?.data?.error || 'No se pudo resetear la contrasena.';
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
            <span class="eyebrow">SEGURIDAD</span>
            <h2 class="title">RESETEAR CONTRASENA</h2>
            <button class="close-btn" type="button" aria-label="Cerrar" @click="close">×</button>
          </header>

          <section class="card-body">
            <p class="lede">
              Generaremos una contrasena temporal y la enviaremos por email a
              <span v-if="hasEmail" class="dest">{{ coach?.email }}</span>
              <span v-else class="dest dest--missing">— sin email configurado —</span>.
            </p>

            <div v-if="!hasEmail" class="warn-block">
              Este coach no tiene email configurado. Edita primero su perfil para anadir uno.
            </div>

            <p class="philosophical">
              "La contrasena temporal expira en el primer login. Despues, la responsabilidad es del coach."
            </p>

            <div v-if="error" class="error-block">{{ error }}</div>

            <div class="actions">
              <button class="btn btn--secondary" type="button" :disabled="loading" @click="close">
                Cancelar
              </button>
              <button
                class="btn btn--amber"
                type="button"
                :disabled="!hasEmail || loading"
                @click="submit"
              >
                {{ loading ? 'Enviando…' : 'Enviar nueva contrasena' }}
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
    width: 100%; max-width: 420px;
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--r-md, 16px);
    overflow: hidden;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
}

.card-head {
    padding: 20px 22px 16px;
    border-bottom: 1px solid var(--c-border);
    position: relative;
    display: flex; flex-direction: column; gap: 4px;
}
.eyebrow {
    font-family: var(--font-display);
    font-size: 8px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--c-text-3);
}
.title {
    font-family: var(--font-display);
    font-size: 22px; letter-spacing: 0.04em;
    color: var(--c-text);
    margin: 0;
}
.close-btn {
    position: absolute; top: 14px; right: 14px;
    width: var(--tap-comfort, 48px); height: var(--tap-comfort, 48px);
    border-radius: var(--r-sm, 12px);
    background: transparent;
    border: 1px solid var(--c-border);
    color: var(--c-text-2);
    font-size: 18px; line-height: 1; cursor: pointer;
}
.close-btn:hover { background: rgba(255, 255, 255, 0.04); color: var(--c-text); }

.card-body {
    padding: 18px 22px 22px;
    display: flex; flex-direction: column; gap: 14px;
}
.lede {
    font-family: var(--font-sans);
    font-size: 13px; line-height: 1.55;
    color: var(--c-text-2);
    margin: 0;
}
.dest {
    font-family: var(--font-display);
    color: var(--c-text);
    font-size: 12px;
    letter-spacing: 0.08em;
}
.dest--missing { color: #FCD34D; }

.warn-block {
    border: 1px solid rgba(245, 158, 11, 0.32);
    background: rgba(245, 158, 11, 0.1);
    color: #FCD34D;
    border-radius: var(--r-sm, 12px);
    padding: 10px 12px;
    font-family: var(--font-sans);
    font-size: 12px;
}

.philosophical {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: #C8A769;
    line-height: 1.55;
    margin: 0;
    padding-top: 4px;
    border-top: 1px solid var(--c-border);
}

.error-block {
    border-radius: var(--r-sm, 12px);
    border: 1px solid rgba(220, 38, 38, 0.35);
    background: var(--c-accent-dim);
    padding: 10px 12px;
    font-size: 12px;
    color: #F87171;
}

.actions { display: flex; gap: 8px; justify-content: flex-end; }
.btn {
    min-height: var(--tap-comfort, 48px);
    padding: 0 16px;
    border-radius: var(--r-sm, 12px);
    font-family: var(--font-sans);
    font-size: 13px; font-weight: 600;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease);
}
.btn:disabled { opacity: 0.55; cursor: not-allowed; }
.btn--secondary {
    background: transparent;
    border: 1px solid var(--c-border);
    color: var(--c-text-2);
}
.btn--secondary:hover:not(:disabled) { background: rgba(255, 255, 255, 0.04); color: var(--c-text); }
.btn--amber {
    background: #FCD34D;
    border: 1px solid #FCD34D;
    color: #1c1300;
}
.btn--amber:hover:not(:disabled) { background: #F59E0B; border-color: #F59E0B; color: #1c1300; }

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
