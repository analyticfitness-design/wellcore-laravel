<template>
  <div class="relative">
    <div class="h-px w-full bg-gradient-to-r from-wc-accent/0 via-wc-accent/40 to-wc-accent/0"></div>
    <div class="flex items-center justify-between gap-3 px-4 py-2.5">
      <div class="flex min-w-0 items-center gap-2.5 rounded-full border border-wc-border bg-wc-bg-secondary/50 px-3 py-1 backdrop-blur-sm">
        <span class="relative flex h-1 w-1 shrink-0">
          <span class="absolute inline-flex h-full w-full motion-safe:animate-ping rounded-full bg-wc-accent opacity-60"></span>
          <span class="relative inline-flex h-1 w-1 rounded-full bg-wc-accent"></span>
        </span>
        <span class="font-display text-[9px] tracking-[0.22em] text-wc-text-tertiary">REEMPLAZADO POR</span>
        <span class="min-w-0 truncate font-display text-xs tracking-wider text-wc-text">{{ replacementName }}</span>
      </div>
      <button
        type="button"
        :disabled="restoring"
        aria-label="Restaurar comida original"
        class="flex min-h-[36px] shrink-0 items-center gap-1.5 rounded-full border border-wc-border bg-wc-bg-secondary/50 px-3 py-1 font-display text-[10px] tracking-[0.15em] text-wc-text-secondary transition hover:border-wc-accent/40 hover:text-wc-accent active:scale-95 disabled:opacity-40 disabled:hover:border-wc-border"
        @click="emit('restore')"
      >
        <X :size="11" :stroke-width="2.5" />
        <span>RESTAURAR</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { X } from 'lucide-vue-next';

defineProps({
  replacementName: {
    type: String,
    required: true,
  },
  restoring: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['restore']);
</script>
