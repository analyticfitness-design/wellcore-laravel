<script setup>
const props = defineProps({
    modelValue: { type: Object, required: true },
});
const emit = defineEmits(['update:modelValue']);

const COUNTRIES = [
    { code: '', label: 'Selecciona pais' },
    { code: 'CO', label: 'Colombia' },
    { code: 'MX', label: 'Mexico' },
    { code: 'AR', label: 'Argentina' },
    { code: 'CL', label: 'Chile' },
    { code: 'PE', label: 'Peru' },
    { code: 'EC', label: 'Ecuador' },
    { code: 'VE', label: 'Venezuela' },
    { code: 'US', label: 'Estados Unidos' },
    { code: 'ES', label: 'Espana' },
    { code: 'XX', label: 'Otro' },
];

function update(field, value) {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
}
</script>

<template>
  <section class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-8">
    <header class="mb-6">
      <p class="font-mono text-xs uppercase tracking-[0.15em] text-wc-accent">01 / IDENTIDAD</p>
      <h2 class="mt-2 font-display text-3xl uppercase tracking-tight text-wc-text">Tu marca personal</h2>
      <p class="mt-2 font-editorial italic text-base text-wc-text-secondary">Como te encuentran y de donde eres.</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          Nombre de marca
        </label>
        <input
          type="text"
          :value="modelValue.brand_name"
          @input="update('brand_name', $event.target.value)"
          maxlength="120"
          placeholder="Ej. Coach Daniel Esparza"
          class="mt-2 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        />
      </div>

      <div>
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          Ciudad
        </label>
        <input
          type="text"
          :value="modelValue.city ?? ''"
          @input="update('city', $event.target.value || null)"
          maxlength="80"
          placeholder="Bogota"
          class="mt-2 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        />
      </div>

      <div>
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          Pais
        </label>
        <select
          :value="modelValue.country_code ?? ''"
          @change="update('country_code', $event.target.value || null)"
          class="mt-2 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        >
          <option v-for="c in COUNTRIES" :key="c.code" :value="c.code">{{ c.label }}</option>
        </select>
      </div>
    </div>
  </section>
</template>
