<script setup>
defineProps({
    html: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['back', 'confirm']);
</script>

<template>
  <div class="space-y-4">
    <!-- Header -->
    <div>
      <h2 class="font-display text-2xl tracking-wide text-wc-text">Vista Previa del Email</h2>
      <p class="mt-1 text-sm text-wc-text-secondary">
        Revisa como se vera el correo antes de enviarlo.
      </p>
    </div>

    <!-- Tracking notice -->
    <div class="flex items-start gap-3 rounded-lg border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-400">
      <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
      </svg>
      <span>El tracking de apertura es aproximado (iOS 15+ y Gmail pueden pre-cargar imagenes).</span>
    </div>

    <!-- Email iframe -->
    <div class="overflow-hidden rounded-xl border border-wc-border bg-white">
      <iframe
        sandbox="allow-same-origin"
        :srcdoc="html"
        class="w-full"
        style="min-height: 500px; border: none;"
        title="Vista previa del email"
      ></iframe>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between gap-3 pt-2">
      <button
        type="button"
        @click="emit('back')"
        class="rounded-lg bg-wc-bg-tertiary px-4 py-2 text-sm font-semibold text-wc-text hover:bg-zinc-700 transition-colors"
      >
        Volver y editar
      </button>
      <button
        type="button"
        @click="emit('confirm')"
        class="rounded-lg bg-wc-accent px-5 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors"
      >
        Confirmar y enviar
      </button>
    </div>
  </div>
</template>
