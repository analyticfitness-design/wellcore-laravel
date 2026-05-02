<script setup>
import { ref, useId } from 'vue';

const props = defineProps({
  label: { type: String, required: true },
  modelValue: { default: '' },
  placeholder: { type: String, default: '' },
  hint: { type: String, default: '' },
  error: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const fieldId = useId();
const visible = ref(false);
const copied = ref(false);

function onInput(e) {
  emit('update:modelValue', e.target.value);
}

async function copyToClipboard() {
  const value = props.modelValue;
  if (!value || typeof value !== 'string' || value.startsWith('•')) return;
  try {
    await navigator.clipboard.writeText(value);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 1800);
  } catch {
    // Silenciosamente ignorar errores de clipboard
  }
}
</script>

<template>
  <div class="ssf-wrap">
    <label :for="fieldId" class="ssf-label">
      {{ label }}<span v-if="required" class="ssf-req" aria-hidden="true"> *</span>
      <span v-if="disabled" class="ssf-lock" title="Solo Superadmin puede modificar esta seccion">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
      </span>
    </label>

    <div class="ssf-input-row">
      <input
        :id="fieldId"
        class="ssf-input"
        :class="{ 'ssf-input--error': error }"
        :type="visible ? 'text' : 'password'"
        :value="modelValue"
        :placeholder="placeholder || (disabled ? '••••••••' : 'Ingresa el valor')"
        :disabled="disabled"
        :required="required"
        autocomplete="new-password"
        spellcheck="false"
        @input="onInput"
      />
      <button
        type="button"
        class="ssf-btn"
        :title="visible ? 'Ocultar' : 'Mostrar'"
        :aria-label="visible ? 'Ocultar valor' : 'Mostrar valor'"
        :disabled="disabled"
        @click="visible = !visible"
      >
        <!-- eye -->
        <svg v-if="!visible" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
        <!-- eye-off -->
        <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
          <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
          <line x1="1" y1="1" x2="23" y2="23"/>
        </svg>
      </button>
      <button
        v-if="!disabled"
        type="button"
        class="ssf-btn"
        :title="copied ? 'Copiado' : 'Copiar al portapapeles'"
        :aria-label="copied ? 'Copiado al portapapeles' : 'Copiar al portapapeles'"
        @click="copyToClipboard"
      >
        <!-- check-copied -->
        <svg v-if="copied" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        <!-- copy -->
        <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
          <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
          <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
        </svg>
      </button>
    </div>

    <p v-if="hint && !error" class="ssf-hint">{{ hint }}</p>
    <p v-if="error" class="ssf-error" role="alert">{{ error }}</p>
  </div>
</template>

<style scoped>
.ssf-wrap { display: flex; flex-direction: column; gap: 6px; }

.ssf-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: var(--font-display);
  font-size: 9px;
  letter-spacing: 1.6px;
  text-transform: uppercase;
  color: var(--c-text-3);
}
.ssf-req { color: var(--c-accent); }
.ssf-lock { display: flex; align-items: center; color: var(--c-text-3); cursor: help; }

.ssf-input-row {
  display: flex;
  gap: 4px;
  align-items: center;
}
.ssf-input {
  flex: 1;
  height: 36px;
  padding: 0 10px;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--c-border);
  border-radius: var(--r-sm, 12px);
  color: var(--c-text);
  font-family: var(--font-display);
  font-size: 13px;
  outline: none;
  transition: border-color 0.15s var(--ease-out);
  min-width: 0;
}
.ssf-input:focus { border-color: var(--c-accent); box-shadow: 0 0 0 2px rgba(220,38,38,0.12); }
.ssf-input:disabled { opacity: 0.45; cursor: not-allowed; }
.ssf-input--error { border-color: var(--c-accent); }

.ssf-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 36px;
  border-radius: var(--r-sm, 12px);
  border: 1px solid var(--c-border);
  background: rgba(255,255,255,0.03);
  color: var(--c-text-3);
  cursor: pointer;
  flex-shrink: 0;
  transition: color 0.12s, border-color 0.12s;
}
.ssf-btn:hover:not(:disabled) {
  color: var(--c-text);
  border-color: rgba(255,255,255,0.12);
}
.ssf-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.ssf-hint {
  font-family: var(--font-sans);
  font-size: 11px;
  color: var(--c-text-3);
  margin: 0;
}
.ssf-error {
  font-family: var(--font-sans);
  font-size: 11px;
  color: #F87171;
  margin: 0;
}
</style>
