<script setup>
/**
 * PhotoValidationChips — pill-style indicators rendered over a photo preview.
 *
 * Props:
 *   chips: { lighting: 'good'|'low', framing: 'good'|'warn' }
 *
 * Phase 1 only renders lighting + framing. v2.1 may add posture/exposure.
 * Each chip is a button-less span; clicks bubble up — purely visual.
 */
import { computed } from 'vue';

const props = defineProps({
  chips: {
    type: Object,
    default: () => ({ lighting: 'good', framing: 'good' }),
  },
});

const items = computed(() => {
  const out = [];
  if (props.chips.lighting === 'low') {
    out.push({ key: 'light-low', label: 'Luz baja', tone: 'warn' });
  } else {
    out.push({ key: 'light-ok', label: 'Luz', tone: 'ok' });
  }
  if (props.chips.framing === 'warn') {
    out.push({ key: 'frame-warn', label: 'Encuadre', tone: 'warn' });
  } else {
    out.push({ key: 'frame-ok', label: 'Encuadre', tone: 'ok' });
  }
  return out;
});
</script>

<template>
  <div class="flex flex-wrap gap-1.5" role="list" aria-label="Validación de la foto">
    <!-- HTML ref: .dz-check — fondo negro semi-transparente + texto verde/ámbar -->
    <span
      v-for="item in items"
      :key="item.key"
      role="listitem"
      class="inline-flex items-center gap-1 rounded-full border border-white/15 bg-black/55 px-2 py-0.5 font-mono text-[10px] font-medium uppercase tracking-wider backdrop-blur"
      :class="item.tone === 'ok' ? 'text-emerald-300' : 'text-amber-300'"
    >
      <span class="font-sans font-bold leading-none" aria-hidden="true">{{ item.tone === 'ok' ? '✓' : '!' }}</span>
      {{ item.label }}
    </span>
  </div>
</template>
