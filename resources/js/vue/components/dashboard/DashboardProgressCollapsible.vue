<script setup>
import { ref, computed } from 'vue';
import { useHaptics } from '../../composables/useHaptics';

const props = defineProps({
    // Si `true`, el panel se inicializa abierto. Por default: abierto en desktop
    // (≥1024px) y cerrado en mobile para ahorrar scroll vertical.
    defaultOpen: { type: Boolean, default: null },
});

const haptics = useHaptics();

function initialOpen() {
    if (props.defaultOpen !== null) return props.defaultOpen;
    if (typeof window === 'undefined') return false;
    return window.matchMedia?.('(min-width: 1024px)')?.matches ?? false;
}

const open = ref(initialOpen());
const toggleLabel = computed(() => open.value ? 'Ocultar detalles' : 'Ver más detalles');

function toggle() {
    open.value = !open.value;
    haptics.light();
}
</script>

<template>
  <section class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
    <!-- Header clickeable -->
    <button
      type="button"
      @click="toggle"
      :aria-expanded="open"
      class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left transition-colors hover:bg-wc-bg-secondary/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-wc-accent/40"
    >
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-wc-accent/10">
          <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
          </svg>
        </div>
        <div>
          <h2 class="text-base font-semibold text-wc-text">Tu progreso completo</h2>
          <p class="text-xs text-wc-text-secondary">Timeline · racha · peso · resumen semanal</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <span class="hidden text-xs text-wc-text-tertiary sm:inline">{{ toggleLabel }}</span>
        <svg :class="['h-4 w-4 shrink-0 text-wc-text-secondary transition-transform duration-300', open ? 'rotate-180' : '']" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
      </div>
    </button>

    <!-- Contenido colapsable -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 -translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-show="open" class="border-t border-wc-border/60 p-4 sm:p-5 space-y-4 sm:space-y-6">
        <slot />
      </div>
    </Transition>
  </section>
</template>
