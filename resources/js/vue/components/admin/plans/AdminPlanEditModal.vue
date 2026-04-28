<script setup>
import { ref, watch, computed } from 'vue';
import { useApi } from '../../../composables/useApi';

const props = defineProps({
    open:       { type: Boolean, default: false },
    editingPlan: { type: Object, default: null },
    isDuplicate: { type: Boolean, default: false },
    coaches:    { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'saved']);

const api = useApi();

const formName        = ref('');
const formPlanType    = ref('entrenamiento');
const formMethodology = ref('');
const formDescription = ref('');
const formContentJson = ref('');
const formIsPublic    = ref(false);
const formCoachId     = ref('');
const formErrors      = ref({});
const saving          = ref(false);
const loadingContent  = ref(false);

const isEditing = computed(() => !!props.editingPlan && !props.isDuplicate);

const modalTitle = computed(() => {
    if (props.isDuplicate) return 'DUPLICAR TEMPLATE';
    if (isEditing.value)   return 'EDITAR TEMPLATE';
    return 'NUEVO TEMPLATE';
});

watch(() => props.open, async (opened) => {
    if (!opened) return;
    formErrors.value = {};

    if (props.editingPlan) {
        formName.value        = props.isDuplicate
            ? props.editingPlan.name + ' (copia)'
            : props.editingPlan.name;
        formPlanType.value    = props.editingPlan.plan_type ?? 'entrenamiento';
        formMethodology.value = props.editingPlan.methodology ?? '';
        formDescription.value = props.editingPlan.description ?? '';
        formIsPublic.value    = !!props.editingPlan.is_public;
        formCoachId.value     = props.editingPlan.coach_id ? String(props.editingPlan.coach_id) : '';

        // Fetch full content_json
        loadingContent.value = true;
        try {
            const res = await api.get(`/api/v/admin/plans/${props.editingPlan.id}`);
            const content = res.data.plan?.content_json;
            formContentJson.value = content ? JSON.stringify(content, null, 2) : '';
        } catch {
            formContentJson.value = '';
        } finally {
            loadingContent.value = false;
        }
    } else {
        formName.value        = '';
        formPlanType.value    = 'entrenamiento';
        formMethodology.value = '';
        formDescription.value = '';
        formContentJson.value = '';
        formIsPublic.value    = false;
        formCoachId.value     = '';
    }
});

function close() {
    if (saving.value) return;
    emit('close');
}

async function save() {
    formErrors.value = {};

    if (!formName.value.trim()) {
        formErrors.value = { name: ['El nombre es obligatorio.'] };
        return;
    }
    if (!formContentJson.value.trim()) {
        formErrors.value = { content_json: ['El contenido JSON es obligatorio.'] };
        return;
    }

    let parsedJson;
    try {
        parsedJson = JSON.parse(formContentJson.value);
    } catch (e) {
        formErrors.value = { content_json: ['JSON invalido: ' + e.message] };
        return;
    }

    saving.value = true;
    const payload = {
        name:         formName.value.trim(),
        plan_type:    formPlanType.value,
        methodology:  formMethodology.value.trim() || null,
        description:  formDescription.value.trim() || null,
        content_json: parsedJson,
        is_public:    formIsPublic.value,
        coach_id:     formCoachId.value ? Number(formCoachId.value) : null,
    };

    try {
        if (isEditing.value) {
            await api.put(`/api/v/admin/plans/${props.editingPlan.id}`, payload);
        } else {
            await api.post('/api/v/admin/plans', payload);
        }
        emit('saved');
        close();
    } catch (e) {
        if (e.response?.status === 422) {
            formErrors.value = e.response.data.errors ?? {};
        } else {
            formErrors.value = { _global: [e.response?.data?.message ?? 'Error al guardar el template.'] };
        }
    } finally {
        saving.value = false;
    }
}

function onBackdropKey(e) {
    if (e.key === 'Escape') close();
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div
        v-if="open"
        class="modal-backdrop"
        role="dialog"
        aria-modal="true"
        :aria-label="modalTitle"
        @keydown="onBackdropKey"
      >
        <div class="modal-overlay" @click="close"></div>

        <Transition name="modal-slide">
          <div v-if="open" class="modal-panel">
            <!-- Header -->
            <div class="modal-header">
              <h2 class="modal-title">{{ modalTitle }}</h2>
              <button
                type="button"
                class="modal-close"
                aria-label="Cerrar modal"
                @click="close"
              >
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <!-- Global error -->
            <div v-if="formErrors._global" class="modal-error-global">
              <p>{{ formErrors._global[0] }}</p>
            </div>

            <!-- Loading content overlay -->
            <div v-if="loadingContent" class="modal-loading">
              <div class="modal-loading-bar"></div>
              <div class="modal-loading-bar" style="width: 60%"></div>
            </div>

            <form v-else @submit.prevent="save" class="modal-form">
              <!-- Name -->
              <div class="field">
                <label class="field-label" for="pm-name">Nombre <span class="field-required" aria-hidden="true">*</span></label>
                <input
                  id="pm-name"
                  v-model="formName"
                  type="text"
                  class="field-input"
                  :class="{ 'field-input--error': formErrors.name }"
                  placeholder="Nombre del template"
                  autocomplete="off"
                />
                <p v-if="formErrors.name" class="field-error">{{ formErrors.name[0] }}</p>
              </div>

              <!-- Type + Coach row -->
              <div class="field-row">
                <div class="field">
                  <label class="field-label" for="pm-type">Tipo <span class="field-required" aria-hidden="true">*</span></label>
                  <select id="pm-type" v-model="formPlanType" class="field-select">
                    <option value="entrenamiento">Entrenamiento</option>
                    <option value="nutricion">Nutricion</option>
                    <option value="habitos">Habitos</option>
                    <option value="suplementacion">Suplementacion</option>
                    <option value="ciclo">Ciclo</option>
                  </select>
                </div>
                <div class="field">
                  <label class="field-label" for="pm-coach">Coach</label>
                  <select id="pm-coach" v-model="formCoachId" class="field-select">
                    <option value="">Sin asignar</option>
                    <option v-for="c in coaches" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                  </select>
                </div>
              </div>

              <!-- Methodology -->
              <div class="field">
                <label class="field-label" for="pm-methodology">Metodologia</label>
                <input
                  id="pm-methodology"
                  v-model="formMethodology"
                  type="text"
                  class="field-input"
                  placeholder="Push/Pull/Legs, Full Body, etc."
                  autocomplete="off"
                />
              </div>

              <!-- Description -->
              <div class="field">
                <label class="field-label" for="pm-desc">Descripcion</label>
                <textarea
                  id="pm-desc"
                  v-model="formDescription"
                  rows="2"
                  class="field-textarea"
                  placeholder="Resumen breve del template..."
                ></textarea>
              </div>

              <!-- Content JSON -->
              <div class="field">
                <label class="field-label" for="pm-json">
                  Contenido JSON <span class="field-required" aria-hidden="true">*</span>
                </label>
                <textarea
                  id="pm-json"
                  v-model="formContentJson"
                  rows="10"
                  class="field-textarea field-textarea--mono"
                  :class="{ 'field-input--error': formErrors.content_json }"
                  placeholder='{"weeks": [{"week": 1, "sessions": [...]}]}'
                ></textarea>
                <p v-if="formErrors.content_json" class="field-error">{{ formErrors.content_json[0] }}</p>
              </div>

              <!-- Public toggle -->
              <div class="field-toggle">
                <button
                  type="button"
                  class="toggle-track"
                  :class="{ 'toggle-track--on': formIsPublic }"
                  role="switch"
                  :aria-checked="formIsPublic"
                  aria-label="Template publico"
                  @click="formIsPublic = !formIsPublic"
                >
                  <span class="toggle-thumb" :class="{ 'toggle-thumb--on': formIsPublic }"></span>
                </button>
                <span class="toggle-label">Template publico</span>
              </div>

              <!-- Actions -->
              <div class="modal-actions">
                <button type="button" class="btn-cancel" @click="close">Cancelar</button>
                <button type="submit" class="btn-save" :disabled="saving">
                  <svg v-if="saving" class="btn-spinner" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  {{ saving ? 'Guardando...' : (isEditing ? 'Actualizar' : 'Crear template') }}
                </button>
              </div>
            </form>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 200;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding: 16px;
}
@media (min-width: 640px) {
    .modal-backdrop { align-items: center; }
}

.modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(4px);
}

.modal-panel {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 640px;
    max-height: 90vh;
    overflow-y: auto;
    border-radius: 16px;
    border: 1px solid var(--color-wc-border-2);
    background: var(--color-wc-bg-secondary, #111111);
    padding: 24px;
    box-shadow: 0 32px 80px rgba(0, 0, 0, 0.6);
}

/* Header */
.modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
}
.modal-title {
    font-family: var(--font-display);
    font-size: 22px;
    letter-spacing: 0.04em;
    color: var(--color-wc-text);
    margin: 0;
}
.modal-close {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: transparent;
    color: var(--color-wc-text-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.modal-close:hover {
    color: var(--color-wc-text);
    border-color: var(--color-wc-border-2);
}

/* Error banner */
.modal-error-global {
    border-radius: 10px;
    border: 1px solid rgba(220, 38, 38, 0.22);
    background: rgba(220, 38, 38, 0.07);
    padding: 10px 14px;
    margin-bottom: 16px;
}
.modal-error-global p {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--color-wc-red-text, #F87171);
    margin: 0;
}

/* Loading skeleton */
.modal-loading {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 20px 0;
}
.modal-loading-bar {
    height: 14px;
    width: 100%;
    border-radius: 6px;
    background: var(--color-wc-bg-tertiary);
    animation: modal-pulse 1.5s ease-in-out infinite;
}
@keyframes modal-pulse {
    0%, 100% { opacity: 0.5; }
    50%       { opacity: 0.85; }
}

/* Form */
.modal-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.field-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
}
@media (min-width: 480px) {
    .field-row { grid-template-columns: 1fr 1fr; }
}
.field-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-secondary);
}
.field-required { color: var(--color-wc-accent, #DC2626); }

.field-input,
.field-select,
.field-textarea {
    border-radius: 10px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary, #181818);
    color: var(--color-wc-text);
    font-family: var(--font-sans);
    font-size: 13px;
    padding: 10px 12px;
    width: 100%;
    transition: border-color 0.15s var(--ease-out, ease);
    box-sizing: border-box;
}
.field-input::placeholder,
.field-textarea::placeholder { color: var(--color-wc-text-tertiary); }
.field-input:focus,
.field-select:focus,
.field-textarea:focus {
    outline: none;
    border-color: var(--color-wc-accent, #DC2626);
}
.field-input--error { border-color: rgba(220, 38, 38, 0.6) !important; }
.field-select { appearance: none; cursor: pointer; }
.field-textarea { resize: vertical; }
.field-textarea--mono { font-family: var(--font-mono, monospace); font-size: 11px; }

.field-error {
    font-family: var(--font-sans);
    font-size: 11px;
    color: var(--color-wc-red-text, #F87171);
    margin: 0;
}

/* Toggle */
.field-toggle {
    display: flex;
    align-items: center;
    gap: 10px;
}
.toggle-track {
    position: relative;
    width: 40px;
    height: 22px;
    border-radius: 20px;
    border: 2px solid transparent;
    background: rgba(255, 255, 255, 0.08);
    cursor: pointer;
    transition: background 0.2s ease;
    flex-shrink: 0;
}
.toggle-track--on { background: var(--color-wc-green-soft, rgba(16,185,129,0.3)); }
.toggle-thumb {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: var(--color-wc-text-tertiary);
    transition: transform 0.2s ease, background 0.2s ease;
}
.toggle-thumb--on {
    transform: translateX(18px);
    background: var(--color-wc-green-text, #34D399);
}
.toggle-label {
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--color-wc-text-secondary);
}

/* Modal actions */
.modal-actions {
    display: flex;
    gap: 10px;
    padding-top: 4px;
}
.btn-cancel {
    flex: 1;
    height: 40px;
    border-radius: 10px;
    border: 1px solid var(--color-wc-border);
    background: transparent;
    color: var(--color-wc-text-secondary);
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.btn-cancel:hover {
    color: var(--color-wc-text);
    border-color: var(--color-wc-border-2);
}
.btn-save {
    flex: 1;
    height: 40px;
    border-radius: 10px;
    border: none;
    background: var(--color-wc-accent, #DC2626);
    color: #fff;
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background 0.15s var(--ease-out, ease);
}
.btn-save:hover:not(:disabled) { background: #B91C1C; }
.btn-save:disabled { opacity: 0.65; cursor: not-allowed; }

.btn-spinner {
    width: 14px;
    height: 14px;
    animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Transitions */
.modal-fade-enter-active,
.modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter-from,
.modal-fade-leave-to { opacity: 0; }

.modal-slide-enter-active,
.modal-slide-leave-active { transition: transform 0.25s var(--ease-out, ease), opacity 0.25s ease; }
.modal-slide-enter-from,
.modal-slide-leave-to { transform: translateY(32px); opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .modal-fade-enter-active,
    .modal-fade-leave-active,
    .modal-slide-enter-active,
    .modal-slide-leave-active { transition: none !important; }
    .modal-loading-bar { animation: none !important; }
}
</style>
