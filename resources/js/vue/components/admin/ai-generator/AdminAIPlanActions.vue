<script setup>
import { computed, ref, watch } from 'vue';
import { useAdminAIGeneratorStore } from '../../../stores/adminAIGenerator';
import { useToast } from '../../../composables/useToast';

const props = defineProps({
    text: { type: String, default: '' },
    historyId: { type: Number, default: null },
});
const emit = defineEmits(['approved', 'discarded']);

const store = useAdminAIGeneratorStore();
const toast = useToast();

const showApprove = ref(false);
const showEditor = ref(false);
const showDiscardConfirm = ref(false);
const isDiscarding = ref(false);
const editedText = ref('');

const templateName = ref('');
const isPublic = ref(false);
const saveMode = ref('template_only');
const targetClient = computed(() => store.brief.target_client_id);

const isApproving = ref(false);
const errorMsg = ref(null);

watch(() => props.text, (v) => { if (!editedText.value) editedText.value = v; });

function suggestTemplateName() {
    const t = store.brief.plan_type || 'plan';
    const m = store.brief.methodology || '';
    const w = store.brief.duration_weeks || '';
    return [t, m, `${w}sem`].filter(Boolean).join(' · ').slice(0, 80);
}

function openApprove() {
    if (!templateName.value) templateName.value = suggestTemplateName();
    showApprove.value = true;
    errorMsg.value = null;
}

async function confirmApprove() {
    if (!templateName.value.trim()) { errorMsg.value = 'Asigna un nombre al template'; return; }
    isApproving.value = true;
    errorMsg.value = null;
    try {
        const out = await store.approve({
            templateName: templateName.value.trim(),
            isPublic: isPublic.value,
            saveMode: saveMode.value,
            targetClientId: targetClient.value,
            editedText: showEditor.value ? editedText.value : null,
        });
        showApprove.value = false;
        showEditor.value = false;
        emit('approved', out);
    } catch (e) {
        errorMsg.value = e?.response?.data?.error || 'Error al aprobar';
    } finally {
        isApproving.value = false;
    }
}

function onDiscard() {
    showDiscardConfirm.value = true;
}

async function confirmDiscard() {
    isDiscarding.value = true;
    try {
        await store.discard();
        showDiscardConfirm.value = false;
        emit('discarded');
    } catch {
        toast.show('No se pudo descartar el plan. Intenta de nuevo.', 'error');
    } finally {
        isDiscarding.value = false;
    }
}
</script>

<template>
  <section v-if="text && historyId" class="actions-card">
    <header class="actions-head">
      <div>
        <p class="actions-eyebrow">ACCIONES</p>
        <h2 class="actions-title">¿Qué hacer con el draft?</h2>
      </div>
    </header>

    <div class="actions-row">
      <button type="button" class="actions-btn actions-btn--primary" @click="openApprove">
        Aprobar y guardar
      </button>
      <button type="button" class="actions-btn" @click="showEditor = !showEditor">
        {{ showEditor ? 'Cerrar editor' : 'Editar manualmente' }}
      </button>
      <button type="button" class="actions-btn actions-btn--danger" @click="onDiscard">
        Descartar
      </button>
    </div>

    <!-- Inline editor -->
    <textarea
      v-if="showEditor"
      class="actions-editor"
      v-model="editedText"
      rows="14"
    ></textarea>

    <!-- Discard confirmation modal -->
    <Teleport to="body">
      <div v-if="showDiscardConfirm" class="approve-overlay" @click.self="showDiscardConfirm = false">
        <div class="approve-modal" role="dialog" aria-modal="true">
          <p class="approve-eyebrow">CONFIRMAR ACCION</p>
          <h3 class="approve-title">Descartar draft</h3>
          <p class="approve-tagline">"La generación se marcará como descartada en el historial."</p>
          <div class="approve-foot">
            <button type="button" class="actions-btn" :disabled="isDiscarding" @click="showDiscardConfirm = false">
              Cancelar
            </button>
            <button
              type="button"
              class="actions-btn actions-btn--danger"
              :disabled="isDiscarding"
              @click="confirmDiscard"
            >
              {{ isDiscarding ? 'Descartando...' : 'Sí, descartar' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Approve modal (inline) -->
    <Teleport to="body">
      <div v-if="showApprove" class="approve-overlay" @click.self="showApprove = false">
        <div class="approve-modal" role="dialog" aria-modal="true">
          <p class="approve-eyebrow">GUARDAR DRAFT</p>
          <h3 class="approve-title">Aprobar plan</h3>
          <p class="approve-tagline">"Lo asistido se valida con disciplina humana."</p>

          <label class="approve-label" for="ai-tpl-name">Nombre del template</label>
          <input
            id="ai-tpl-name"
            type="text"
            class="approve-input"
            v-model="templateName"
            placeholder="Hipertrofia · DUP · 8 sem"
            maxlength="160"
          >

          <label class="approve-label">Modo de guardado</label>
          <div class="approve-radio-group">
            <label class="approve-radio">
              <input type="radio" v-model="saveMode" value="template_only">
              <span>Solo template (no asignar)</span>
            </label>
            <label class="approve-radio" :class="{ 'approve-radio--disabled': !targetClient }">
              <input type="radio" v-model="saveMode" value="template_and_assign" :disabled="!targetClient">
              <span>Template + asignar a cliente</span>
            </label>
          </div>
          <p v-if="!targetClient" class="approve-hint">
            Sin cliente seleccionado en el brief. Asigna uno desde el formulario para habilitar la asignación directa.
          </p>

          <label class="approve-radio approve-radio--public">
            <input type="checkbox" v-model="isPublic">
            <span>Marcar como template público (otros coaches lo verán)</span>
          </label>

          <p v-if="errorMsg" class="approve-error">{{ errorMsg }}</p>

          <div class="approve-foot">
            <button type="button" class="actions-btn" :disabled="isApproving" @click="showApprove = false">
              Cancelar
            </button>
            <button
              type="button"
              class="actions-btn actions-btn--primary"
              :disabled="isApproving || !templateName.trim()"
              @click="confirmApprove"
            >
              {{ isApproving ? 'Guardando...' : 'Confirmar' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </section>
</template>

<style scoped>
.actions-card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    position: relative;
    z-index: 1;
}
.actions-head { display: flex; justify-content: space-between; align-items: flex-start; }
.actions-eyebrow {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    color: var(--c-text-3);
    text-transform: uppercase;
    margin: 0 0 3px;
}
.actions-title {
    font-family: var(--font-display);
    font-size: 20px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--c-text);
    margin: 0;
    line-height: 1.05;
}
.actions-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.actions-btn {
    height: 36px;
    min-height: var(--tap-comfort, 48px);
    padding: 0 16px;
    border-radius: var(--r-pill, 999px);
    border: 1px solid var(--c-border);
    background: transparent;
    color: var(--c-text);
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
}
.actions-btn:hover:not(:disabled) { border-color: rgba(255,255,255,0.12); }
.actions-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.actions-btn--primary {
    background: var(--c-accent);
    border-color: var(--c-accent);
    color: #fff;
}
.actions-btn--primary:hover:not(:disabled) { background: #B91C1C; }
.actions-btn--danger {
    color: #F87171;
    border-color: rgba(220, 38, 38, 0.4);
}
.actions-btn--danger:hover:not(:disabled) { background: var(--c-accent-dim); }

.actions-editor {
    width: 100%;
    min-height: 280px;
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid var(--c-border);
    background: rgba(0, 0, 0, 0.4);
    color: var(--c-text);
    font-family: var(--font-display);
    font-size: 12px;
    line-height: 1.55;
    resize: vertical;
}

/* Approve modal */
.approve-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(2px);
    z-index: 80;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.approve-modal {
    background: var(--c-surface-2);
    border: 1px solid var(--c-border);
    border-radius: var(--r-md, 16px);
    padding: 22px 22px 20px;
    width: 100%;
    max-width: 480px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.6);
}
.approve-eyebrow {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    color: var(--c-text-3);
    text-transform: uppercase;
    margin: 0;
}
.approve-title {
    font-family: var(--font-display);
    font-size: 24px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin: 0;
}
.approve-tagline {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: #C8A769;
    margin: 0;
}
.approve-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    color: var(--c-text-3);
    text-transform: uppercase;
    margin-top: 6px;
}
.approve-input {
    height: 38px;
    padding: 0 12px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255, 255, 255, 0.03);
    color: var(--c-text);
    font-family: var(--font-sans);
    font-size: 13px;
}
.approve-input:focus { outline: none; border-color: rgba(255,255,255,0.12); }

.approve-radio-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.approve-radio {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--c-text-2);
    cursor: pointer;
}
.approve-radio--public { margin-top: 8px; }
.approve-radio--disabled { opacity: 0.5; cursor: not-allowed; }

.approve-hint {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 11px;
    color: var(--c-text-3);
    margin: 0;
    line-height: 1.5;
}
.approve-error {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 0.06em;
    color: #F87171;
    margin: 0;
}

.approve-foot {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 8px;
    padding-top: 12px;
    border-top: 1px solid var(--c-border);
}
</style>
