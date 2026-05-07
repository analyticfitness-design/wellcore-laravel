<script setup>
/**
 * PhotoGuide — collapsible guide section combining AnglesGrid + TipsList.
 * Persists open/closed state in localStorage('wc_photo_guide').
 *
 * Props:
 *   defaultOpen: bool   first-render fallback when no localStorage value
 *   storageKey:  string override for the LS key (rare)
 *
 * Emits:
 *   toggle (newState: boolean)
 */
import { ref, watch } from 'vue';
import AnglesGrid from './AnglesGrid.vue';
import TipsList from './TipsList.vue';

const props = defineProps({
  defaultOpen: { type: Boolean, default: true },
  storageKey: { type: String, default: 'wc_photo_guide' },
  genero: { type: String, default: '' }, // 'mujer' | 'hombre' | '' — propaga a AnglesGrid
});
const emit = defineEmits(['toggle']);

function _initial() {
  try {
    const stored = localStorage.getItem(props.storageKey);
    if (stored === 'open') return true;
    if (stored === 'closed') return false;
  } catch { /* SSR / private mode */ }
  return props.defaultOpen;
}

const open = ref(_initial());

function toggle() {
  open.value = !open.value;
  try { localStorage.setItem(props.storageKey, open.value ? 'open' : 'closed'); } catch { /* noop */ }
  emit('toggle', open.value);
}

function openGuide() {
  if (open.value) return;
  open.value = true;
  try { localStorage.setItem(props.storageKey, 'open'); } catch { /* noop */ }
  emit('toggle', true);
}

function closeGuide() {
  if (!open.value) return;
  open.value = false;
  try { localStorage.setItem(props.storageKey, 'closed'); } catch { /* noop */ }
  emit('toggle', false);
}

watch(() => props.storageKey, () => { open.value = _initial(); });

defineExpose({ open: openGuide, close: closeGuide, toggle, isOpen: () => open.value });
</script>

<template>
  <section
    class="overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary"
    aria-labelledby="photo-guide-title"
  >
    <button
      type="button"
      class="flex min-h-[56px] w-full items-center justify-between gap-3 px-4 py-3.5 text-left transition-colors hover:bg-wc-bg-secondary/50"
      :aria-expanded="open"
      aria-controls="photo-guide-body"
      @click="toggle"
    >
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-accent">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
            <path d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
          </svg>
        </div>
        <div>
          <h2 id="photo-guide-title" class="font-display text-base font-semibold uppercase tracking-wider text-wc-text">
            Guía para tus fotos
          </h2>
          <p class="text-xs text-wc-text-tertiary">Cómo tomarte las fotos para un progreso preciso</p>
        </div>
      </div>
      <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
        :class="open ? 'rotate-180' : ''"
        aria-hidden="true"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
      </svg>
    </button>

    <Transition name="guide">
      <div
        v-show="open"
        id="photo-guide-body"
        class="border-t border-wc-border px-4 pb-6 pt-5 sm:px-6 sm:pb-8 sm:pt-6"
      >
        <div class="mb-5">
          <p class="mb-2 font-mono text-[11px] uppercase tracking-[0.16em] text-wc-text-tertiary">
            / guía visual
          </p>
          <h3 class="font-display text-2xl font-medium uppercase tracking-wide text-wc-text sm:text-[28px]">
            Tres ángulos. Una rutina.
          </h3>
          <p class="mt-1.5 max-w-[52ch] text-sm text-wc-text-secondary">
            La técnica importa porque la comparativa solo es honesta si las fotos son consistentes.
          </p>
        </div>
        <AnglesGrid :genero="genero" />

        <div class="mt-8">
          <TipsList />
        </div>
      </div>
    </Transition>
  </section>
</template>

<style scoped>
.guide-enter-active, .guide-leave-active {
  transition: max-height 0.3s ease, opacity 0.25s ease;
  overflow: hidden;
  max-height: 1200px;
}
.guide-enter-from, .guide-leave-to {
  max-height: 0;
  opacity: 0;
}
</style>
