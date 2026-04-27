<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    modelValue: { type: Object, required: true },
});
const emit = defineEmits(['update:modelValue']);

const newAdjective = ref('');

function update(field, value) {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
}

const adjectives = computed(() => Array.isArray(props.modelValue.voice_adjectives) ? props.modelValue.voice_adjectives : []);
const samples = computed(() => Array.isArray(props.modelValue.voice_samples) ? props.modelValue.voice_samples : []);

const adjectivesFull = computed(() => adjectives.value.length >= 3);

function addAdjective() {
    const v = (newAdjective.value || '').trim();
    if (!v || adjectivesFull.value) return;
    if (v.length > 30) return;
    if (adjectives.value.includes(v)) {
        newAdjective.value = '';
        return;
    }
    update('voice_adjectives', [...adjectives.value, v]);
    newAdjective.value = '';
}

function removeAdjective(value) {
    update('voice_adjectives', adjectives.value.filter((a) => a !== value));
}

function addSample() {
    if (samples.value.length >= 3) return;
    update('voice_samples', [...samples.value, { caption: '', source_url: null, note: null }]);
}

function removeSample(idx) {
    const next = [...samples.value];
    next.splice(idx, 1);
    update('voice_samples', next);
}

function updateSampleField(idx, field, value) {
    const next = samples.value.map((s, i) => (i === idx ? { ...s, [field]: value } : s));
    update('voice_samples', next);
}
</script>

<template>
  <section class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-8 space-y-8">
    <header>
      <p class="font-mono text-xs uppercase tracking-[0.15em] text-wc-accent">05 / VOZ Y TONO</p>
      <h2 class="mt-2 font-display text-3xl uppercase tracking-tight text-wc-text">Como suena tu marca</h2>
      <p class="mt-2 font-editorial italic text-base text-wc-text-secondary">Como suena tu marca cuando habla.</p>
    </header>

    <div>
      <div class="flex items-baseline justify-between">
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          3 adjetivos que la describen
        </label>
        <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          {{ adjectives.length }}/3
        </span>
      </div>

      <div v-if="adjectives.length" class="mt-3 flex flex-wrap gap-2">
        <span
          v-for="adj in adjectives"
          :key="adj"
          class="inline-flex items-center gap-2 rounded-full border border-wc-accent/40 bg-wc-accent/10 px-3 py-1.5 text-xs uppercase tracking-wide text-wc-accent"
        >
          {{ adj }}
          <button type="button" class="hover:text-white" @click="removeAdjective(adj)">&times;</button>
        </span>
      </div>

      <div class="mt-3 flex gap-2">
        <input
          v-model="newAdjective"
          type="text"
          maxlength="30"
          :disabled="adjectivesFull"
          :placeholder="adjectivesFull ? 'Maximo alcanzado' : 'Ej. Directa'"
          class="flex-1 rounded-lg border border-wc-border bg-wc-bg px-4 py-2 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30 disabled:opacity-50"
          @keydown.enter.prevent="addAdjective"
        />
        <button
          type="button"
          :disabled="adjectivesFull"
          class="rounded-lg border border-wc-border bg-wc-bg px-4 py-2 font-mono text-xs uppercase tracking-[0.15em] text-wc-text-secondary hover:border-wc-accent hover:text-wc-text disabled:opacity-50"
          @click="addAdjective"
        >
          Agregar
        </button>
      </div>
    </div>

    <div>
      <div class="flex items-baseline justify-between">
        <label class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          Muestras de tu voz (opcional, max 3)
        </label>
        <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">
          {{ samples.length }}/3
        </span>
      </div>

      <div class="mt-3 space-y-4">
        <div
          v-for="(sample, idx) in samples"
          :key="idx"
          class="rounded-xl border border-wc-border bg-wc-bg p-4 space-y-3"
        >
          <div class="flex justify-between">
            <p class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary">
              Muestra {{ idx + 1 }}
            </p>
            <button type="button" class="font-mono text-[11px] uppercase tracking-[0.15em] text-wc-text-tertiary hover:text-wc-accent" @click="removeSample(idx)">
              Eliminar
            </button>
          </div>

          <textarea
            :value="sample.caption"
            @input="updateSampleField(idx, 'caption', $event.target.value)"
            rows="3"
            maxlength="2200"
            placeholder="Pega aqui un caption tuyo que sienta autentico..."
            class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
          ></textarea>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <input
              type="url"
              :value="sample.source_url ?? ''"
              @input="updateSampleField(idx, 'source_url', $event.target.value || null)"
              placeholder="URL fuente (opcional)"
              class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
            />
            <input
              type="text"
              :value="sample.note ?? ''"
              @input="updateSampleField(idx, 'note', $event.target.value || null)"
              maxlength="200"
              placeholder="Nota corta (opcional)"
              class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
            />
          </div>
        </div>
      </div>

      <button
        v-if="samples.length < 3"
        type="button"
        class="mt-3 rounded-lg border border-dashed border-wc-border bg-wc-bg px-4 py-3 w-full font-mono text-xs uppercase tracking-[0.15em] text-wc-text-secondary hover:border-wc-accent hover:text-wc-text"
        @click="addSample"
      >
        + Agregar muestra
      </button>
    </div>
  </section>
</template>
