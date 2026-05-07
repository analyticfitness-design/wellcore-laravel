<script setup>
defineProps({
  modelValue: { type: [String, Number], default: '' },
  error: { type: String, default: '' },
  saving: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'expand', 'submit']);
</script>

<template>
  <div class="quick-log">
    <!-- Giant peso input -->
    <div class="quick-input" :class="{ 'quick-input--error': error }">
      <label for="quick-peso" class="quick-label">PESO</label>
      <input
        id="quick-peso"
        type="number"
        inputmode="decimal"
        step="0.1"
        min="20"
        max="300"
        placeholder="75.0"
        :value="modelValue"
        @input="emit('update:modelValue', $event.target.value)"
        class="quick-field"
        autocomplete="off"
        aria-label="Peso en kilogramos"
      />
      <span class="quick-unit">kg</span>
      <span class="quick-hint">en ayunas</span>
    </div>
    <p v-if="error" class="quick-error" role="alert">{{ error }}</p>

    <!-- Action row -->
    <div class="quick-actions">
      <button
        type="submit"
        class="quick-save"
        :disabled="saving"
        :aria-busy="saving"
      >
        <svg v-if="saving" class="spin" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:.25"/>
          <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" style="opacity:.75"/>
        </svg>
        {{ saving ? 'Guardando...' : 'Guardar' }}
      </button>

      <button type="button" class="quick-expand" @click="emit('expand')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Completo
      </button>
    </div>
  </div>
</template>

<style scoped>
.quick-log { display: flex; flex-direction: column; gap: 14px; }
.quick-input {
  position: relative;
  display: flex;
  align-items: baseline;
  gap: 8px;
  padding: 18px 20px;
  border-radius: 16px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-secondary);
  transition: border-color .15s;
}
.quick-input:focus-within { border-color: rgba(220,38,38,.50); background: var(--color-wc-bg); }
.quick-input--error { border-color: rgba(220,38,38,.60) !important; }
.quick-label {
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 500;
  letter-spacing: .18em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
  position: absolute;
  top: 10px;
  left: 20px;
}
.quick-field {
  font-family: var(--font-display);
  font-size: 52px;
  font-weight: 600;
  line-height: 1;
  color: var(--color-wc-text);
  background: transparent;
  border: none;
  outline: none;
  width: 100%;
  font-variant-numeric: tabular-nums;
  margin-top: 20px;
  letter-spacing: -.01em;
  -moz-appearance: textfield;
}
.quick-field::-webkit-outer-spin-button,
.quick-field::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.quick-unit {
  font-family: var(--font-mono);
  font-size: 14px;
  color: var(--color-wc-text-tertiary);
  margin-top: 20px;
  white-space: nowrap;
}
.quick-hint {
  position: absolute;
  bottom: 8px;
  right: 16px;
  font-family: var(--font-mono);
  font-size: 10.5px;
  color: var(--color-wc-text-tertiary);
  letter-spacing: .06em;
  opacity: .6;
}
.quick-error {
  font-size: 12px;
  color: #FCA5A5;
  margin: -6px 0 0;
}
.quick-actions { display: flex; gap: 10px; align-items: stretch; }
.quick-save {
  flex: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  min-height: 52px;
  padding: 14px 20px;
  border-radius: 12px;
  background: var(--color-wc-accent);
  color: #fff;
  font-family: var(--font-display);
  font-size: 16px;
  font-weight: 600;
  letter-spacing: .06em;
  text-transform: uppercase;
  border: none;
  cursor: pointer;
  transition: background .12s;
}
.quick-save:hover:not(:disabled) { background: var(--color-wc-accent-hover); }
.quick-save:disabled { opacity: .55; cursor: not-allowed; }
.quick-expand {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  min-height: 52px;
  padding: 14px 18px;
  border-radius: 12px;
  background: var(--color-wc-bg-secondary);
  border: 1px solid var(--color-wc-border);
  font-size: 13px;
  font-weight: 500;
  color: var(--color-wc-text-secondary);
  cursor: pointer;
  white-space: nowrap;
  transition: background .12s, color .12s;
}
.quick-expand:hover { background: var(--color-wc-bg-tertiary); color: var(--color-wc-text); }
.spin { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
