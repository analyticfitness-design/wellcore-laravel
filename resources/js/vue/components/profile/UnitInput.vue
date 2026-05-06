<script setup>
/**
 * UnitInput.vue — wrapper de InputField type="number" con sufijo de unidad
 * (KG / CM / etc.) en estilo Oswald 12px tracking 0.12em.
 *
 * v-model standard. Coerciona vacío → '' (no 0).
 */
import { computed } from 'vue';
import InputField from './InputField.vue';

const props = defineProps({
    id:          { type: String, required: true },
    label:       { type: String, default: '' },
    unit:        { type: String, default: '' },     // ej: 'KG', 'CM'
    placeholder: { type: String, default: '' },
    step:        { type: [String, Number], default: 0.1 },
    min:         { type: [String, Number], default: 0 },
    max:         { type: [String, Number], default: undefined },
    error:       { type: String, default: '' },
    hint:        { type: String, default: '' },
    disabled:    { type: Boolean, default: false },
    required:    { type: Boolean, default: false },
});

const model = defineModel({ default: '' });

// Pasthrough robusto: dejamos que InputField maneje el v-model — pero garantizamos
// que el tipo number no inserte NaN. Vue ya emite '' cuando el input está vacío.
const proxy = computed({
    get: () => (model.value ?? ''),
    set: (v) => { model.value = v === '' || v === null ? '' : v; },
});
</script>

<template>
  <InputField
    :id="id"
    v-model="proxy"
    :label="label"
    type="number"
    inputmode="decimal"
    :step="step"
    :min="min"
    :max="max"
    :placeholder="placeholder"
    :error="error"
    :hint="hint"
    :disabled="disabled"
    :required="required"
  >
    <template v-if="unit" #suffix>{{ unit }}</template>
  </InputField>
</template>
