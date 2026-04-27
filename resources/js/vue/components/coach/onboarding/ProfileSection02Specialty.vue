<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: Object, required: true },
});
const emit = defineEmits(['update:modelValue']);

const PRIMARY_OPTIONS = [
    { value: '', label: 'Selecciona especialidad' },
    { value: 'fuerza', label: 'Fuerza' },
    { value: 'hipertrofia', label: 'Hipertrofia' },
    { value: 'recomposicion', label: 'Recomposicion' },
    { value: 'perdida_grasa', label: 'Perdida de grasa' },
    { value: 'mujeres_postparto', label: 'Mujeres postparto' },
    { value: 'funcional', label: 'Funcional' },
    { value: 'otro', label: 'Otro' },
];

const SECONDARY_OPTIONS = [
    { value: '', label: 'Ninguna' },
    { value: 'fuerza', label: 'Fuerza' },
    { value: 'hipertrofia', label: 'Hipertrofia' },
    { value: 'recomposicion', label: 'Recomposicion' },
    { value: 'perdida_grasa', label: 'Perdida de grasa' },
    { value: 'mujeres_postparto', label: 'Mujeres postparto' },
    { value: 'funcional', label: 'Funcional' },
    { value: 'otro', label: 'Otro' },
];

function update(field, value) {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
}

const differentiatorCount = computed(() => (props.modelValue.differentiator || '').length);
</script>

<template>
  <section class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-8">
    <header class="mb-6">
      <p class="font-mono text-xs uppercase tracking-[0.15em] text-wc-accent">02 / ESPECIALIDAD</p>
      <h2 class="mt-2 font-display text-3xl uppercase tracking-tight text-wc-text">Lo que dominas</h2>
      <p class="mt-2 font-editorial italic text-base text-wc-text-secondary">Lo que dominas mejor que cualquier otro coach.</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">Especialidad principal</label>
        <select
          :value="modelValue.specialty_primary ?? ''"
          @change="update('specialty_primary', $event.target.value || null)"
          class="mt-2 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        >
          <option v-for="o in PRIMARY_OPTIONS" :key="o.value" :value="o.value">{{ o.label }}</option>
        </select>
      </div>

      <div v-if="modelValue.specialty_primary === 'otro'">
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">Especifica principal</label>
        <input
          type="text"
          :value="modelValue.specialty_primary_other ?? ''"
          @input="update('specialty_primary_other', $event.target.value || null)"
          maxlength="80"
          class="mt-2 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        />
      </div>

      <div>
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">Especialidad secundaria</label>
        <select
          :value="modelValue.specialty_secondary ?? ''"
          @change="update('specialty_secondary', $event.target.value || null)"
          class="mt-2 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        >
          <option v-for="o in SECONDARY_OPTIONS" :key="o.value" :value="o.value">{{ o.label }}</option>
        </select>
      </div>

      <div v-if="modelValue.specialty_secondary === 'otro'">
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">Especifica secundaria</label>
        <input
          type="text"
          :value="modelValue.specialty_secondary_other ?? ''"
          @input="update('specialty_secondary_other', $event.target.value || null)"
          maxlength="80"
          class="mt-2 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        />
      </div>

      <div class="md:col-span-2">
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          Que te diferencia (min 20 caracteres)
        </label>
        <textarea
          :value="modelValue.differentiator"
          @input="update('differentiator', $event.target.value)"
          rows="4"
          maxlength="1000"
          placeholder="Mi metodo combina entrenamiento de fuerza progresivo con un seguimiento nutricional adaptado..."
          class="mt-2 w-full resize-none rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        ></textarea>
        <div class="mt-1 flex justify-end font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          {{ differentiatorCount }} / 1000
        </div>
      </div>
    </div>
  </section>
</template>
