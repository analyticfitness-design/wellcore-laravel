<script setup>
/**
 * InputField.vue — input base 48px del Profile Editor v2.
 *
 * Slots:
 *   - prefix:  contenido a la izquierda (ej. flag + "+57", ícono globo).
 *              Se renderiza dentro de .input-prefix con border-right.
 *   - suffix:  contenido a la derecha (ej. "KG", "CM"). Pointer-events:none por defecto.
 *   - hint:    texto bajo el input cuando NO hay error.
 *
 * Props básicas:
 *   - id, label, type, modelValue, placeholder, autocomplete, inputmode,
 *     step, min, max, maxlength, required, disabled, error, hint, counter
 *
 * v-model standard via defineModel.
 *
 * Notas:
 *   - font-size 16px en input para evitar zoom auto en iOS.
 *   - height 48px ≥ 44px touch target.
 *   - focus-visible: border accent-glow + box-shadow ring rojo soft.
 */
import { computed, useSlots } from 'vue';

const props = defineProps({
    id:           { type: String, required: true },
    label:        { type: String, default: '' },
    type:         { type: String, default: 'text' },
    placeholder:  { type: String, default: '' },
    autocomplete: { type: String, default: 'off' },
    inputmode:    { type: String, default: undefined },
    step:         { type: [String, Number], default: undefined },
    min:          { type: [String, Number], default: undefined },
    max:          { type: [String, Number], default: undefined },
    maxlength:    { type: [String, Number], default: undefined },
    pattern:      { type: String, default: undefined },
    required:     { type: Boolean, default: false },
    disabled:     { type: Boolean, default: false },
    error:        { type: String, default: '' },
    hint:         { type: String, default: '' },
    hintLabel:    { type: String, default: '' }, // opcional: texto al lado del label
    counter:      { type: String, default: '' }, // texto a mostrar en counter (ej "12/160")
    counterWarn:  { type: Boolean, default: false }, // si true, counter en ámbar
});

const model = defineModel({ default: '' });

const slots = useSlots();

const hasPrefix = computed(() => !!slots.prefix);
const hasSuffix = computed(() => !!slots.suffix);

const inputClass = computed(() => ({
    input: true,
    'input--with-suffix': hasSuffix.value,
    'input--with-prefix': hasPrefix.value,
    'is-invalid': !!props.error,
    'is-disabled': props.disabled,
}));

const describedBy = computed(() => {
    const ids = [];
    if (props.error) ids.push(`${props.id}-error`);
    else if (props.hint) ids.push(`${props.id}-hint`);
    return ids.length ? ids.join(' ') : undefined;
});
</script>

<template>
  <div class="field">
    <div v-if="label || counter || hintLabel" class="field-label-row">
      <label v-if="label" :for="id" class="field-label">
        {{ label }}
        <span v-if="hintLabel" class="field-label__hint">{{ hintLabel }}</span>
      </label>
      <span
        v-if="counter"
        class="field-counter tabular-nums"
        :class="{ 'is-warn': counterWarn }"
      >{{ counter }}</span>
    </div>

    <div class="input-wrap">
      <span v-if="hasPrefix" class="input-prefix"><slot name="prefix" /></span>

      <input
        :id="id"
        v-model="model"
        :type="type"
        :class="inputClass"
        :placeholder="placeholder || undefined"
        :autocomplete="autocomplete"
        :inputmode="inputmode"
        :step="step"
        :min="min"
        :max="max"
        :maxlength="maxlength"
        :pattern="pattern"
        :required="required"
        :disabled="disabled"
        :aria-invalid="error ? 'true' : 'false'"
        :aria-describedby="describedBy"
      />

      <span v-if="hasSuffix" class="input-suffix"><slot name="suffix" /></span>
    </div>

    <p
      v-if="error"
      :id="`${id}-error`"
      class="field-error"
      role="alert"
    >{{ error }}</p>
    <p
      v-else-if="hint"
      :id="`${id}-hint`"
      class="field-hint"
    >{{ hint }}</p>

    <slot name="below" />
  </div>
</template>

<style scoped>
.field {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 0;
}

.field-label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

.field-label {
  font-size: 14px;
  font-weight: 500;
  color: var(--color-wc-text);
  letter-spacing: 0.005em;
}

.field-label__hint {
  margin-left: 6px;
  font-size: 12px;
  font-weight: 400;
  color: var(--color-wc-text-tertiary);
}

.field-counter {
  font-size: 12px;
  color: var(--color-wc-text-tertiary);
}
.field-counter.is-warn { color: #F59E0B; }

.input-wrap { position: relative; }

.input {
  width: 100%;
  height: 48px;
  padding: 0 14px;
  border-radius: 10px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-secondary);
  color: var(--color-wc-text);
  font-size: 16px;
  font-weight: 400;
  font-family: inherit;
  transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
}
.input::placeholder { color: var(--color-wc-text-quaternary, var(--color-wc-text-tertiary)); }
.input:hover:not(:disabled) { border-color: var(--color-wc-border-strong, var(--color-wc-border)); }
.input:focus,
.input:focus-visible {
  outline: none;
  border-color: var(--color-wc-accent-glow, #EF4444);
  background: var(--color-wc-bg-tertiary);
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
}
.input.is-invalid {
  border-color: var(--color-wc-accent, #DC2626);
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.10);
}
.input.is-disabled,
.input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.input--with-suffix { padding-right: 56px; }
.input--with-prefix { padding-left: 64px; }

.input-suffix {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  font-family: 'Oswald', Impact, sans-serif;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.12em;
  color: var(--color-wc-text-tertiary);
  text-transform: uppercase;
}

.input-prefix {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  font-size: 14px;
  color: var(--color-wc-text-tertiary);
  display: flex;
  align-items: center;
  gap: 6px;
  border-right: 1px solid var(--color-wc-border);
  padding-right: 10px;
  height: 22px;
}

.field-error {
  margin: 0;
  font-size: 12px;
  color: var(--color-wc-accent, #DC2626);
  line-height: 1.4;
}
.field-hint {
  margin: 0;
  font-size: 12px;
  color: var(--color-wc-text-tertiary);
  line-height: 1.4;
}

@media (prefers-reduced-motion: reduce) {
  .input { transition-duration: 0.01ms; }
}
</style>
