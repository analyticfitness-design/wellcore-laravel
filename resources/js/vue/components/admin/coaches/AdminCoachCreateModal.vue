<script setup>
import { ref, computed, watch } from 'vue';
import { useApi } from '../../../composables/useApi';
import EmailPreview from '../../../pages/Coach/Invitations/EmailPreview.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
});
const emit = defineEmits(['close', 'success']);

const api = useApi();

const form = ref({ name: '', username: '', email: '', whatsapp: '', password: '' });
const errors = ref({});
const apiError = ref('');
const creating = ref(false);

const previewing = ref(false);
const previewHtml = ref('');
const previewSeen = ref(false);
const previewError = ref('');

watch(() => props.show, (val) => {
    if (val) {
        form.value = { name: '', username: '', email: '', whatsapp: '', password: '' };
        errors.value = {};
        apiError.value = '';
        previewing.value = false;
        previewHtml.value = '';
        previewSeen.value = false;
        previewError.value = '';
        creating.value = false;
    }
});

const passwordStrength = computed(() => {
    const p = form.value.password || '';
    let s = 0;
    if (p.length >= 10) s++;
    if (/[a-z]/.test(p) && /[A-Z]/.test(p)) s++;
    if (/\d/.test(p)) s++;
    if (/[^A-Za-z0-9]/.test(p)) s++;
    return s; // 0..4
});
const strengthLabel = computed(() => ['MUY DEBIL', 'DEBIL', 'REGULAR', 'BUENA', 'FUERTE'][passwordStrength.value]);

function generatePassword() {
    const lower = 'abcdefghijkmnpqrstuvwxyz';
    const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const digits = '23456789';
    const symbols = '!@#$%&*';
    const all = lower + upper + digits + symbols;
    let p = '';
    p += lower[Math.floor(Math.random() * lower.length)];
    p += upper[Math.floor(Math.random() * upper.length)];
    p += digits[Math.floor(Math.random() * digits.length)];
    p += symbols[Math.floor(Math.random() * symbols.length)];
    for (let i = 0; i < 10; i++) p += all[Math.floor(Math.random() * all.length)];
    form.value.password = p.split('').sort(() => Math.random() - 0.5).join('');
}

function close() {
    if (creating.value || previewing.value) return;
    emit('close');
}

async function handlePreview() {
    previewError.value = '';
    const f = form.value;
    if (!f.name.trim() || !f.email.trim() || !f.username.trim()) {
        previewError.value = 'Nombre, usuario y email son obligatorios para la vista previa.';
        return;
    }
    previewing.value = true;
    try {
        const { data } = await api.post('/api/v/admin/coaches/manage/preview', {
            name: f.name.trim(),
            username: f.username.trim(),
            email: f.email.trim(),
        });
        previewHtml.value = data.html ?? '';
        previewSeen.value = true;
    } catch {
        previewError.value = 'No se pudo generar la vista previa. Verifica los datos.';
    } finally {
        previewing.value = false;
    }
}

async function submit() {
    creating.value = true;
    errors.value = {};
    apiError.value = '';
    try {
        await api.post('/api/v/admin/coaches/manage', form.value);
        const to = form.value.email || 'el nuevo coach';
        emit('success', { email: to });
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors ?? {};
            previewHtml.value = '';
        } else {
            apiError.value = err.response?.data?.error || 'No se pudo crear el coach.';
        }
    } finally {
        creating.value = false;
    }
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="show" class="modal-backdrop" @click="close" aria-hidden="true"></div>
    </Transition>

    <Transition name="modal-pop">
      <div v-if="show" class="modal-frame" role="dialog" aria-modal="true">
        <div class="modal-card" @click.stop>
          <header class="card-head">
            <span class="eyebrow">ALTA EN EL EQUIPO</span>
            <h2 class="title">NUEVO COACH</h2>
            <button class="close-btn" type="button" aria-label="Cerrar" @click="close">×</button>
          </header>

          <Transition name="fade" mode="out-in">
            <!-- Email preview ─────────────────────────────────────────── -->
            <section v-if="previewHtml" key="preview" class="card-body">
              <span class="block-label">VISTA PREVIA DEL EMAIL</span>
              <EmailPreview
                :html="previewHtml"
                @back="previewHtml = ''"
                @confirm="previewHtml = ''; submit()"
              />
            </section>

            <!-- Form ─────────────────────────────────────────────────── -->
            <form v-else key="form" class="card-body" @submit.prevent>
              <div v-if="previewError" class="error-block">{{ previewError }}</div>

              <label class="field">
                <span class="field-label">NOMBRE COMPLETO</span>
                <input v-model="form.name" type="text" class="field-input" placeholder="Carlos Rodriguez" />
                <p v-if="errors.name" class="field-error">{{ errors.name[0] }}</p>
              </label>

              <label class="field">
                <span class="field-label">USUARIO</span>
                <input
                  v-model="form.username"
                  type="text"
                  class="field-input field-input--mono"
                  placeholder="carlos.rodriguez"
                  @input="form.username = form.username.replace(/\s+/g, '')"
                />
                <p class="field-hint">Sin espacios. Debe ser unico.</p>
                <p v-if="errors.username" class="field-error">{{ errors.username[0] }}</p>
              </label>

              <label class="field">
                <span class="field-label">EMAIL</span>
                <input v-model="form.email" type="email" class="field-input" placeholder="coach@ejemplo.com" />
                <p v-if="errors.email" class="field-error">{{ errors.email[0] }}</p>
              </label>

              <label class="field">
                <span class="field-label">WHATSAPP</span>
                <input v-model="form.whatsapp" type="tel" class="field-input field-input--mono" placeholder="+57 300 123 4567" />
                <p v-if="errors.whatsapp" class="field-error">{{ errors.whatsapp[0] }}</p>
              </label>

              <div class="field">
                <div class="password-head">
                  <span class="field-label">CONTRASENA TEMPORAL</span>
                  <button type="button" class="generate-btn" @click="generatePassword">GENERAR</button>
                </div>
                <input v-model="form.password" type="text" class="field-input field-input--mono" placeholder="Minimo 10 caracteres" />
                <div v-if="form.password" class="strength-row">
                  <div class="strength-bars">
                    <span
                      v-for="n in 4"
                      :key="n"
                      class="strength-bar"
                      :class="`strength-bar--${passwordStrength}-${n}`"
                    ></span>
                  </div>
                  <span class="strength-label">{{ strengthLabel }}</span>
                </div>
                <p v-if="errors.password" class="field-error">{{ errors.password[0] }}</p>
              </div>

              <div v-if="apiError" class="error-block">{{ apiError }}</div>

              <div class="actions">
                <button type="button" class="btn btn--secondary" :disabled="creating" @click="close">
                  Cancelar
                </button>
                <button
                  type="button"
                  class="btn btn--ghost"
                  :disabled="previewing"
                  @click="handlePreview"
                >
                  {{ previewing ? 'Generando…' : 'Vista previa email' }}
                </button>
                <button
                  type="button"
                  class="btn btn--primary"
                  :disabled="creating || !previewSeen"
                  :title="!previewSeen ? 'Revisa el email antes de crear' : ''"
                  @click="submit"
                >
                  {{ creating ? 'Creando…' : 'Crear coach' }}
                </button>
              </div>
            </form>
          </Transition>
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
    width: 100%; max-width: 520px;
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--r-md, 16px);
    overflow: hidden;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

.card-head {
    padding: 20px 22px 16px;
    border-bottom: 1px solid var(--c-border);
    position: relative;
    display: flex; flex-direction: column; gap: 4px;
    flex-shrink: 0;
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
    display: flex; flex-direction: column; gap: 12px;
    overflow-y: auto;
}

.block-label {
    font-family: var(--font-display);
    font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--c-text-3);
}

.field { display: flex; flex-direction: column; gap: 5px; }
.field-label {
    font-family: var(--font-display);
    font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--c-text-2);
}
.field-input {
    height: 38px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255, 255, 255, 0.03);
    padding: 0 12px;
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--c-text);
}
.field-input--mono { font-family: var(--font-display); letter-spacing: 0.08em; }
.field-input:focus { outline: none; border-color: var(--c-accent); }
.field-hint {
    margin: 0;
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 0.16em;
    color: var(--c-text-3);
}
.field-error {
    margin: 0;
    font-size: 11px;
    color: #F87171;
}

.password-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
.generate-btn {
    background: transparent;
    border: 1px solid var(--c-border);
    border-radius: 6px;
    color: var(--c-text-2);
    font-family: var(--font-display);
    font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase;
    padding: 4px 10px;
    cursor: pointer;
}
.generate-btn:hover {
    color: #F87171;
    border-color: rgba(220, 38, 38, 0.4);
}

.strength-row { display: flex; align-items: center; gap: 10px; margin-top: 4px; }
.strength-bars { display: flex; gap: 3px; flex: 1; }
.strength-bar {
    flex: 1;
    height: 3px;
    border-radius: 2px;
    background: var(--c-border);
    transition: background 0.15s var(--ease-out, ease);
}

/* Niveles 1..4 — colores */
.strength-bar--0-1, .strength-bar--0-2, .strength-bar--0-3, .strength-bar--0-4 { background: var(--c-border); }
.strength-bar--1-1 { background: #F87171; }
.strength-bar--2-1, .strength-bar--2-2 { background: #FCD34D; }
.strength-bar--3-1, .strength-bar--3-2, .strength-bar--3-3 { background: #60A5FA; }
.strength-bar--4-1, .strength-bar--4-2, .strength-bar--4-3, .strength-bar--4-4 { background: #34D399; }

.strength-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 0.18em;
    color: var(--c-text-3);
    flex-shrink: 0;
}

.error-block {
    border-radius: var(--r-sm, 12px);
    border: 1px solid rgba(220, 38, 38, 0.35);
    background: var(--c-accent-dim);
    padding: 10px 12px;
    font-size: 12px;
    color: #F87171;
}

.actions {
    display: flex; gap: 8px; justify-content: flex-end; flex-wrap: wrap; margin-top: 4px;
    border-top: 1px solid var(--c-border);
    padding-top: 12px;
}
.btn {
    padding: 9px 14px;
    border-radius: var(--r-sm, 12px);
    font-family: var(--font-sans);
    font-size: 12px; font-weight: 600;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn--primary {
    background: var(--c-accent);
    border: 1px solid var(--c-accent);
    color: #fff;
}
.btn--primary:hover:not(:disabled) { background: #B91C1C; }
.btn--ghost {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--c-border);
    color: var(--c-text);
}
.btn--ghost:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.12);
}
.btn--secondary {
    background: transparent;
    border: 1px solid var(--c-border);
    color: var(--c-text-2);
}
.btn--secondary:hover:not(:disabled) { background: rgba(255, 255, 255, 0.04); color: var(--c-text); }

.fade-enter-active, .fade-leave-active { transition: opacity 0.18s var(--ease-out, ease); }
.fade-enter-from, .fade-leave-to { opacity: 0; }

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
