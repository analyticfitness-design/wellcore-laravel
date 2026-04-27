<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: Object, required: true },
});
const emit = defineEmits(['update:modelValue']);

const AGE_RANGES = [
    { value: '18-25', label: '18-25' },
    { value: '25-35', label: '25-35' },
    { value: '35-45', label: '35-45' },
    { value: '45+', label: '45+' },
];

const GENDERS = [
    { value: 'mujeres', label: 'Mujeres' },
    { value: 'hombres', label: 'Hombres' },
    { value: 'mixto', label: 'Mixto' },
];

const OFFER_MAINS = [
    { value: 'esencial', label: 'Esencial' },
    { value: 'metodo', label: 'Metodo' },
    { value: 'elite', label: 'Elite' },
    { value: 'presencial', label: 'Presencial' },
    { value: 'otro', label: 'Otro' },
];

function update(field, value) {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
}

function isSelected(field, value) {
    return props.modelValue[field] === value;
}

const painCount = computed(() => (props.modelValue.audience_pain_main || '').length);
</script>

<template>
  <section class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-8 space-y-8">
    <header>
      <p class="font-mono text-xs uppercase tracking-[0.15em] text-wc-accent">03 / AUDIENCIA</p>
      <h2 class="mt-2 font-display text-3xl uppercase tracking-tight text-wc-text">A quien le hablas</h2>
      <p class="mt-2 font-editorial italic text-base text-wc-text-secondary">A quien le hablas cuando publicas.</p>
    </header>

    <div>
      <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">Rango de edad</label>
      <div class="mt-3 flex flex-wrap gap-2">
        <button
          v-for="opt in AGE_RANGES"
          :key="opt.value"
          type="button"
          :class="[
            'rounded-full border px-4 py-2 text-sm uppercase tracking-wide transition-colors',
            isSelected('audience_age_range', opt.value)
              ? 'border-wc-accent bg-wc-accent text-white'
              : 'border-wc-border bg-wc-bg text-wc-text-secondary hover:border-wc-accent/50',
          ]"
          @click="update('audience_age_range', opt.value)"
        >
          {{ opt.label }}
        </button>
      </div>
    </div>

    <div>
      <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">Genero</label>
      <div class="mt-3 flex flex-wrap gap-2">
        <button
          v-for="opt in GENDERS"
          :key="opt.value"
          type="button"
          :class="[
            'rounded-full border px-4 py-2 text-sm uppercase tracking-wide transition-colors',
            isSelected('audience_gender', opt.value)
              ? 'border-wc-accent bg-wc-accent text-white'
              : 'border-wc-border bg-wc-bg text-wc-text-secondary hover:border-wc-accent/50',
          ]"
          @click="update('audience_gender', opt.value)"
        >
          {{ opt.label }}
        </button>
      </div>
    </div>

    <div>
      <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
        Dolor principal de tu audiencia
      </label>
      <textarea
        :value="modelValue.audience_pain_main"
        @input="update('audience_pain_main', $event.target.value)"
        rows="3"
        maxlength="200"
        placeholder="No saben combinar entrenamiento con vida real..."
        class="mt-2 w-full resize-none rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
      ></textarea>
      <div class="mt-1 flex justify-end font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">
        {{ painCount }} / 200
      </div>
    </div>

    <div>
      <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">Oferta principal</label>
      <div class="mt-3 flex flex-wrap gap-2">
        <button
          v-for="opt in OFFER_MAINS"
          :key="opt.value"
          type="button"
          :class="[
            'rounded-full border px-4 py-2 text-sm uppercase tracking-wide transition-colors',
            isSelected('audience_offer_main', opt.value)
              ? 'border-wc-accent bg-wc-accent text-white'
              : 'border-wc-border bg-wc-bg text-wc-text-secondary hover:border-wc-accent/50',
          ]"
          @click="update('audience_offer_main', opt.value)"
        >
          {{ opt.label }}
        </button>
      </div>
    </div>
  </section>
</template>
