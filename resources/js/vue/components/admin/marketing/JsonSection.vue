<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    value: { type: [Object, Array], required: true },
    rows: { type: Number, default: 12 },
    statusLabel: { type: String, default: '' },
    statusClass: { type: String, default: '' },
});
const emit = defineEmits(['save']);

const raw = ref(JSON.stringify(props.value ?? {}, null, 2));

watch(() => props.value, (val) => {
    raw.value = JSON.stringify(val ?? {}, null, 2);
}, { deep: true });

function onSave() {
    try {
        const parsed = JSON.parse(raw.value);
        emit('save', parsed);
    } catch (e) {
        alert(`JSON invalido en ${props.title}: ${e.message}`);
    }
}
</script>

<template>
  <section class="rounded-xl border border-wc-border bg-wc-bg-secondary p-5">
    <div class="flex items-center justify-between">
      <h3 class="font-display text-xl uppercase tracking-tight text-wc-text">{{ title }}</h3>
      <span class="font-mono text-[10px] uppercase" :class="statusClass">{{ statusLabel }}</span>
    </div>
    <p v-if="description" class="mt-1 text-xs text-wc-text-tertiary">{{ description }}</p>
    <textarea
      v-model="raw"
      :rows="rows"
      class="mt-3 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 font-mono text-xs text-wc-text"
    ></textarea>
    <div class="mt-3 flex justify-end">
      <button @click="onSave" class="rounded-lg border border-wc-border bg-wc-bg px-4 py-1.5 text-xs font-medium text-wc-text hover:border-wc-accent hover:text-wc-accent">
        Guardar {{ title.toLowerCase() }}
      </button>
    </div>
  </section>
</template>
