<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { Teleport } from 'vue';
import AdminToolOutputStream from './AdminToolOutputStream.vue';
import { useToolStream } from '../../../composables/useToolStream';
import { useAdminToolsStore } from '../../../stores/adminTools';

const props = defineProps({
  tool: { type: Object, default: null },   // null = closed
});

const emit = defineEmits(['close']);

const store       = useAdminToolsStore();
const { isStreaming, lines, status, durationMs, error, start, abort, reset } = useToolStream();

// Form params — object with keys from params_schema
const formValues     = ref({});
const confirmText    = ref('');
const phase          = ref('form');    // 'form' | 'running' | 'done'

const needsConfirm = computed(() => props.tool?.destructive);
const confirmReady = computed(() =>
  ! needsConfirm.value || confirmText.value === 'EJECUTAR'
);

// Reset state when tool changes
watch(() => props.tool, (t) => {
  if (t) {
    formValues.value  = {};
    confirmText.value = '';
    phase.value       = 'form';
    reset();
    // Initialize form values from schema
    (t.params_schema || []).forEach(p => {
      formValues.value[p.name] = '';
    });
  }
});

// Prevent body scroll when modal open
watch(() => props.tool, (t) => {
  if (typeof document !== 'undefined') {
    document.body.style.overflow = t ? 'hidden' : '';
  }
});

function close() {
  if (isStreaming.value) abort();
  document.body.style.overflow = '';
  emit('close');
}

function handleBackdrop(e) {
  if (e.target === e.currentTarget) close();
}

async function runTool() {
  if (! props.tool || ! confirmReady.value) return;
  phase.value = 'running';
  reset();

  await start(
    props.tool.id,
    { ...formValues.value },
    {
      onDone: ({ status: s, durationMs: ms }) => {
        phase.value = 'done';
        // Add to store history
        store.prependHistory({
          tool_id:      props.tool.id,
          actor_name:   'Daniel Esparza',
          target_label: props.tool.title,
          status:       s,
          duration_ms:  ms,
          output_preview: lines.value.slice(0, 3).join('').slice(0, 200),
          created_at:   new Date().toISOString(),
        });
      },
      onError: () => {
        phase.value = 'done';
      },
    }
  );
}

function stopRun() {
  abort();
  phase.value = 'done';
}

function runAgain() {
  phase.value       = 'form';
  confirmText.value = '';
  reset();
}
</script>

<template>
  <Teleport to="body">
    <div
      v-if="tool"
      class="tool-modal-backdrop"
      @click="handleBackdrop"
      role="dialog"
      :aria-label="`Ejecutar ${tool.title}`"
      aria-modal="true"
    >
      <div class="tool-modal">
        <!-- Header -->
        <div class="tool-modal-header">
          <div>
            <h2 class="tool-modal-title">{{ tool.title }}</h2>
            <p class="tool-modal-sub">{{ tool.category }}</p>
          </div>
          <button class="tool-modal-close" @click="close" aria-label="Cerrar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Phase: form -->
        <template v-if="phase === 'form'">
          <!-- Params form -->
          <div v-if="tool.params_schema && tool.params_schema.length" class="tool-modal-form">
            <div v-for="param in tool.params_schema" :key="param.name" class="tool-modal-field">
              <label class="tool-modal-label" :for="`tool-param-${param.name}`">
                {{ param.label }}
                <span v-if="param.required" class="tool-modal-required">*</span>
              </label>

              <textarea
                v-if="param.type === 'textarea'"
                :id="`tool-param-${param.name}`"
                v-model="formValues[param.name]"
                class="tool-modal-textarea"
                :placeholder="param.placeholder || ''"
                rows="6"
              />
              <input
                v-else
                :id="`tool-param-${param.name}`"
                :type="param.type"
                v-model="formValues[param.name]"
                class="tool-modal-input"
                :placeholder="param.placeholder || ''"
                :autocomplete="param.type === 'password' ? 'new-password' : undefined"
              />
            </div>
          </div>

          <!-- Double confirmation for destructive -->
          <div v-if="needsConfirm" class="tool-modal-confirm">
            <div class="tool-modal-confirm-banner">
              <svg class="tool-modal-confirm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
              </svg>
              <span>Esta herramienta es <strong>destructiva</strong>. Para confirmar, escribe <code>EJECUTAR</code> en el campo de abajo.</span>
            </div>
            <input
              v-model="confirmText"
              class="tool-modal-input tool-modal-confirm-input"
              placeholder="EJECUTAR"
              autocomplete="off"
              spellcheck="false"
              aria-label="Campo de confirmacion"
            />
          </div>

          <!-- Actions -->
          <div class="tool-modal-actions">
            <button class="tool-modal-btn-cancel" @click="close">CANCELAR</button>
            <button
              class="tool-modal-btn-run"
              :disabled="!confirmReady"
              :class="{ 'tool-modal-btn-run--disabled': !confirmReady }"
              @click="runTool"
            >
              EJECUTAR →
            </button>
          </div>
        </template>

        <!-- Phase: running / done -->
        <template v-else>
          <!-- Output terminal -->
          <div class="tool-modal-output">
            <AdminToolOutputStream
              :lines="lines"
              :is-streaming="isStreaming"
              :status="phase === 'done' ? status : null"
              :duration-ms="durationMs"
            />
          </div>

          <!-- Error overlay -->
          <div v-if="error && phase === 'done'" class="tool-modal-error">
            {{ error }}
          </div>

          <!-- Actions while running / done -->
          <div class="tool-modal-actions">
            <button v-if="isStreaming" class="tool-modal-btn-stop" @click="stopRun">
              PARAR
            </button>
            <template v-else-if="phase === 'done'">
              <button class="tool-modal-btn-cancel" @click="close">CERRAR</button>
              <button class="tool-modal-btn-run" @click="runAgain">EJECUTAR OTRA VEZ &rarr;</button>
            </template>
          </div>
        </template>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.tool-modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 9000;
  background: rgba(0,0,0,0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.tool-modal {
  background: var(--color-wc-bg-secondary);
  border: 1px solid var(--color-wc-border-2);
  border-radius: 14px;
  width: 100%;
  max-width: 560px;
  max-height: 90vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 0;
  animation: modal-in 0.18s var(--ease-out);
}
@keyframes modal-in {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}

.tool-modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  padding: 20px 20px 16px;
  border-bottom: 1px solid var(--color-wc-border);
}
.tool-modal-title {
  font-family: var(--font-display);
  font-size: 20px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--color-wc-text);
}
.tool-modal-sub {
  font-family: var(--font-mono);
  font-size: 9px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
  margin-top: 2px;
}
.tool-modal-close {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: rgba(255,255,255,0.04);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-wc-text-secondary);
  flex-shrink: 0;
  transition: background 0.12s;
}
.tool-modal-close:hover { background: rgba(255,255,255,0.08); }
.tool-modal-close svg { width: 14px; height: 14px; }

.tool-modal-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding: 18px 20px 0;
}
.tool-modal-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.tool-modal-label {
  font-family: var(--font-mono);
  font-size: 9px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
}
.tool-modal-required { color: var(--color-wc-red-text); margin-left: 2px; }
.tool-modal-input, .tool-modal-textarea {
  width: 100%;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--color-wc-border);
  border-radius: 8px;
  padding: 10px 12px;
  font-family: var(--font-mono);
  font-size: 11px;
  color: var(--color-wc-text);
  outline: none;
  transition: border-color 0.12s;
  resize: vertical;
}
.tool-modal-input:focus, .tool-modal-textarea:focus {
  border-color: var(--color-wc-border-2);
}
.tool-modal-textarea { min-height: 100px; }

.tool-modal-confirm {
  padding: 16px 20px 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.tool-modal-confirm-banner {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  background: var(--color-wc-red-soft);
  border: 1px solid rgba(220,38,38,0.2);
  border-radius: 8px;
  padding: 10px 12px;
  font-family: var(--font-sans);
  font-size: 12px;
  color: var(--color-wc-red-text);
  line-height: 1.5;
}
.tool-modal-confirm-icon { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }
.tool-modal-confirm-banner code {
  font-family: var(--font-mono);
  background: rgba(220,38,38,0.15);
  padding: 1px 5px;
  border-radius: 4px;
  font-size: 10px;
}
.tool-modal-confirm-input {
  letter-spacing: 0.15em;
  text-align: center;
}

.tool-modal-output { padding: 16px 20px 0; }
.tool-modal-error {
  margin: 10px 20px 0;
  background: var(--color-wc-red-soft);
  border: 1px solid rgba(220,38,38,0.2);
  border-radius: 8px;
  padding: 10px 12px;
  font-family: var(--font-sans);
  font-size: 12px;
  color: var(--color-wc-red-text);
}

.tool-modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 16px 20px 20px;
}
.tool-modal-btn-cancel, .tool-modal-btn-run, .tool-modal-btn-stop {
  padding: 9px 18px;
  border-radius: 8px;
  font-family: var(--font-mono);
  font-size: 10px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  cursor: pointer;
  border: 1px solid;
  transition: background 0.12s, border-color 0.12s;
}
.tool-modal-btn-cancel {
  background: transparent;
  border-color: var(--color-wc-border);
  color: var(--color-wc-text-secondary);
}
.tool-modal-btn-cancel:hover {
  border-color: var(--color-wc-border-2);
  color: var(--color-wc-text);
}
.tool-modal-btn-run {
  background: rgba(220,38,38,0.1);
  border-color: rgba(220,38,38,0.3);
  color: var(--color-wc-red-text);
}
.tool-modal-btn-run:hover:not(:disabled) {
  background: rgba(220,38,38,0.18);
  border-color: rgba(220,38,38,0.5);
}
.tool-modal-btn-run--disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.tool-modal-btn-stop {
  background: rgba(220,38,38,0.15);
  border-color: var(--color-wc-accent);
  color: var(--color-wc-accent);
  animation: pulse-red 1.2s ease-in-out infinite;
}
.tool-modal-btn-stop:hover {
  background: rgba(220,38,38,0.25);
}
@keyframes pulse-red {
  0%, 100% { box-shadow: 0 0 0 0 rgba(220,38,38,0); }
  50%       { box-shadow: 0 0 0 4px rgba(220,38,38,0.15); }
}

@media (prefers-reduced-motion: reduce) {
  .tool-modal, .tool-modal-btn-stop { animation: none !important; }
  .tool-modal-btn-cancel, .tool-modal-btn-run, .tool-modal-close { transition: none !important; }
}
</style>
