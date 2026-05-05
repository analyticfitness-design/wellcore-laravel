<script setup>
defineProps({
    count: { type: [Number, null], default: null },
    sending: { type: Boolean, default: false },
    canSend: { type: Boolean, default: false },
});
const emit = defineEmits(['send']);
</script>

<template>
  <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3 flex items-center justify-between gap-3">
    <div>
      <p class="text-xs uppercase tracking-widest text-wc-text-tertiary">Recipientes</p>
      <p class="font-display text-2xl text-wc-text">
        <span v-if="count !== null">{{ count }}</span>
        <span v-else class="text-wc-text-tertiary text-base">Calculando…</span>
      </p>
    </div>
    <button
      @click="emit('send')"
      :disabled="!canSend || sending"
      class="rounded-full bg-wc-accent text-white px-5 py-2.5 font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
    >
      {{ sending ? 'Enviando…' : 'Enviar broadcast' }}
    </button>
  </div>
</template>
