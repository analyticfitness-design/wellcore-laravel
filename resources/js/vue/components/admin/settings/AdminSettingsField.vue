<script setup>
import { useId } from 'vue';

const props = defineProps({
  label: { type: String, required: true },
  modelValue: { default: null },
  type: { type: String, default: 'text' }, // text | number | email | url | textarea | select | toggle
  options: { type: Array, default: () => [] }, // [{ value, label }]
  placeholder: { type: String, default: '' },
  hint: { type: String, default: '' },
  error: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);
const fieldId = useId();

function onInput(e) {
  const val = props.type === 'number' ? Number(e.target.value) : e.target.value;
  emit('update:modelValue', val);
}
function onToggle(e) {
  emit('update:modelValue', e.target.checked);
}
function onSelect(e) {
  emit('update:modelValue', e.target.value);
}
</script>

<template>
  <div class="sf-wrap">
    <!-- Toggle -->
    <template v-if="type === 'toggle'">
      <label :for="fieldId" class="sf-toggle-row">
        <span class="sf-toggle-info">
          <span class="sf-label">{{ label }}<span v-if="required" class="sf-req" aria-hidden="true"> *</span></span>
          <span v-if="hint" class="sf-hint">{{ hint }}</span>
        </span>
        <span class="sf-toggle-control" :class="{ 'sf-toggle-control--disabled': disabled }">
          <input
            :id="fieldId"
            type="checkbox"
            class="sr-only"
            :checked="!!modelValue"
            :disabled="disabled"
            @change="onToggle"
          />
          <span class="sf-toggle-track" :class="{ 'sf-toggle-track--on': !!modelValue }"></span>
        </span>
        <span v-if="disabled" class="sf-lock-icon" role="img" title="Solo Superadmin puede modificar esta seccion" aria-label="Restringido">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </span>
      </label>
    </template>

    <!-- Select -->
    <template v-else-if="type === 'select'">
      <label :for="fieldId" class="sf-label-block">
        {{ label }}<span v-if="required" class="sf-req" aria-hidden="true"> *</span>
        <span v-if="disabled" class="sf-lock-icon" title="Solo Superadmin puede modificar esta seccion">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </span>
      </label>
      <select
        :id="fieldId"
        class="sf-input"
        :value="modelValue"
        :disabled="disabled"
        @change="onSelect"
      >
        <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
      </select>
      <p v-if="hint" class="sf-hint-block">{{ hint }}</p>
    </template>

    <!-- Textarea -->
    <template v-else-if="type === 'textarea'">
      <label :for="fieldId" class="sf-label-block">
        {{ label }}<span v-if="required" class="sf-req" aria-hidden="true"> *</span>
        <span v-if="disabled" class="sf-lock-icon" title="Solo Superadmin puede modificar esta seccion">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </span>
      </label>
      <textarea
        :id="fieldId"
        class="sf-input sf-textarea"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        rows="3"
        @input="onInput"
      ></textarea>
      <p v-if="hint" class="sf-hint-block">{{ hint }}</p>
    </template>

    <!-- Text / number / email / url -->
    <template v-else>
      <label :for="fieldId" class="sf-label-block">
        {{ label }}<span v-if="required" class="sf-req" aria-hidden="true"> *</span>
        <span v-if="disabled" class="sf-lock-icon" title="Solo Superadmin puede modificar esta seccion">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </span>
      </label>
      <input
        :id="fieldId"
        class="sf-input"
        :class="{ 'sf-input--error': error }"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        @input="onInput"
      />
      <p v-if="hint && !error" class="sf-hint-block">{{ hint }}</p>
    </template>

    <p v-if="error" class="sf-error" role="alert">{{ error }}</p>
  </div>
</template>

<style scoped>
.sf-wrap { display: flex; flex-direction: column; gap: 6px; }

.sf-label-block {
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: var(--font-display);
  font-size: 9px;
  letter-spacing: 1.6px;
  text-transform: uppercase;
  color: var(--c-text-3);
}
.sf-req { color: var(--c-accent); }
.sf-lock-icon { color: var(--c-text-3); display: flex; align-items: center; cursor: help; }

.sf-input {
  width: 100%;
  height: 36px;
  padding: 0 10px;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--c-border);
  border-radius: var(--r-sm, 12px);
  color: var(--c-text);
  font-family: var(--font-sans);
  font-size: 13px;
  transition: border-color 0.15s var(--ease-out);
  outline: none;
}
.sf-input:focus { border-color: var(--c-accent); box-shadow: 0 0 0 2px rgba(220,38,38,0.12); }
.sf-input:disabled { opacity: 0.45; cursor: not-allowed; }
.sf-input--error { border-color: var(--c-accent); }
.sf-textarea { height: auto; padding: 8px 10px; resize: vertical; }

.sf-hint-block {
  font-family: var(--font-sans);
  font-size: 11px;
  color: var(--c-text-3);
  margin: 0;
  line-height: 1.4;
}
.sf-error {
  font-family: var(--font-sans);
  font-size: 11px;
  color: #F87171;
  margin: 0;
}

/* Toggle row */
.sf-toggle-row {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  padding: 10px 0;
  border-bottom: 1px solid var(--c-border);
}
.sf-toggle-row:last-child { border-bottom: none; }
.sf-toggle-info { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.sf-label { font-family: var(--font-sans); font-size: 13px; color: var(--c-text); }
.sf-hint { font-family: var(--font-sans); font-size: 11px; color: var(--c-text-3); }

.sf-toggle-control { flex-shrink: 0; position: relative; }
.sf-toggle-control--disabled { opacity: 0.45; pointer-events: none; }
.sf-toggle-track {
  display: block;
  width: 40px;
  height: 22px;
  border-radius: 11px;
  background: rgba(255,255,255,0.12);
  position: relative;
  transition: background 0.18s var(--ease-out);
}
.sf-toggle-track::after {
  content: '';
  position: absolute;
  top: 2px; left: 2px;
  width: 18px; height: 18px;
  border-radius: 50%;
  background: #fff;
  transition: transform 0.18s var(--ease-out);
}
.sf-toggle-track--on {
  background: var(--c-accent);
}
.sf-toggle-track--on::after {
  transform: translateX(18px);
}
</style>
