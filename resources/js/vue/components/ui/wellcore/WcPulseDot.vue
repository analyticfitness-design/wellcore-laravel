<script setup>
/**
 * WcPulseDot — Dot de notificacion con breath ring del Design System v1.
 *
 * Usa .wc-pulse-dot de app.css. El color base es --color-wc-accent; con la
 * prop `color` se overridea localmente para variantes semanticas (success
 * verde, amber). El ring expanding respeta @media (prefers-reduced-motion).
 *
 * Props:
 *   color 'accent' | 'success' | 'amber'  (default: 'accent')
 *
 * Uso:
 *   <WcPulseDot/>
 *   <WcPulseDot color="success"/>
 */
import { computed } from 'vue';

const props = defineProps({
    color: { type: String, default: 'accent', validator: (v) => ['accent', 'success', 'amber'].includes(v) },
});

const colorVar = computed(() => ({
    'accent':  'var(--color-wc-accent)',
    'success': 'var(--color-wc-green)',
    'amber':   'var(--color-wc-amber)',
})[props.color]);

const localStyle = computed(() => ({
    '--color-wc-accent': colorVar.value,
}));
</script>

<template>
  <span
    class="wc-pulse-dot"
    :style="localStyle"
    role="status"
    aria-label="notificacion activa"
  />
</template>
