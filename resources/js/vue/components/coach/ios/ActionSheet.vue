<script setup>
import { computed, watch, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: '' },
  actions: {
    type: Array,
    default: () => [],
    // [{id, label, iconColor, iconStrokeColor, iconSvgPath, to?, onClick?}]
  },
});

const emit = defineEmits(['update:open', 'select']);

const { t } = useI18n();
const resolvedTitle = computed(() => props.title || t('coach_nav.quick_actions'));

watch(() => props.open, (val) => {
  if (val) document.body.style.overflow = 'hidden';
  else document.body.style.overflow = '';
});

function close() { emit('update:open', false); }

function selectAction(a) {
  emit('select', a);
  if (a.onClick) a.onClick();
  close();
}

function rgbaFromHex(hex, alpha) {
  if (!hex || !hex.startsWith('#')) return `rgba(220,38,38,${alpha})`;
  const h = hex.replace('#', '');
  const bigint = parseInt(h.length === 3 ? h.split('').map(c => c + c).join('') : h, 16);
  const r = (bigint >> 16) & 255;
  const g = (bigint >> 8) & 255;
  const b = bigint & 255;
  return `rgba(${r},${g},${b},${alpha})`;
}

function onKey(e) {
  if (props.open && e.key === 'Escape') close();
}
onMounted(() => window.addEventListener('keydown', onKey));
onUnmounted(() => {
  window.removeEventListener('keydown', onKey);
  document.body.style.overflow = '';
});
</script>

<template>
  <Teleport to="body">
    <div class="coach-ios">
    <div
      :class="['sheet-backdrop', { open }]"
      @click="close"
      :aria-hidden="!open"
    />
    <div
      :class="['action-sheet', { open }]"
      role="dialog"
      :aria-modal="open"
      :aria-label="resolvedTitle"
    >
      <div class="sheet-handle" aria-hidden="true" />
      <p class="text-center font-display text-[13px] font-semibold tracking-[0.1em] uppercase text-[var(--color-wc-text-3)] py-3 pb-2">
        {{ resolvedTitle }}
      </p>
      <div class="h-px mx-4" style="background: var(--b1);" aria-hidden="true" />
      <div class="px-3 py-2 flex flex-col gap-1">
        <button
          v-for="a in actions"
          :key="a.id"
          class="flex items-center gap-3.5 p-3 px-3 rounded-xl text-left transition active:scale-[0.98] hover:bg-[var(--s1)]"
          style="transition-duration: var(--t-tap); transition-timing-function: var(--ease-spring-ios);"
          :aria-label="a.label"
          @click="selectAction(a)"
        >
          <span
            class="w-9 h-9 rounded-[10px] flex items-center justify-center flex-shrink-0"
            :style="{ background: rgbaFromHex(a.iconColor || '#DC2626', 0.12) }"
          >
            <svg
              class="h-4 w-4"
              fill="none" viewBox="0 0 24 24" stroke-width="2"
              :stroke="a.iconStrokeColor || a.iconColor || '#f87171'"
              aria-hidden="true"
              v-html="a.iconSvgPath"
            />
          </span>
          <span class="font-sans text-[15px] font-semibold text-wc-text">{{ a.label }}</span>
        </button>
      </div>
      <div class="h-px mx-4" style="background: var(--b1);" aria-hidden="true" />
      <button
        class="m-3 mt-1 p-3.5 text-center rounded-xl border font-sans text-[14px] font-semibold transition active:scale-[0.98]"
        style="background: var(--s1); border-color: var(--b1); color: var(--color-wc-text-2); transition-duration: var(--t-tap);"
        @click="close"
      >
        {{ t('coach_nav.palette_cancel') }}
      </button>
    </div>
    </div>
  </Teleport>
</template>
