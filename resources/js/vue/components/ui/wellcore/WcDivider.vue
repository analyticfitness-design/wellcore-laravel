<script setup>
/**
 * WcDivider — Divisor con gradient fade del Design System v1.
 *
 * Usa --grad-divider (light: rgba(0,0,0,X), dark: rgba(255,255,255,X)) que
 * vive en :root con override en .dark. Se ve correcto en ambos temas.
 *
 * Props:
 *   tight boolean — si true, margin reducido (var(--ds-s-2) en lugar de var(--ds-s-4))
 *
 * Uso:
 *   <WcDivider/>
 *   <WcDivider tight/>
 */
defineProps({
    tight: { type: Boolean, default: false },
});
</script>

<template>
  <hr
    role="separator"
    :class="['wc-divider', { 'wc-divider-tight': tight }]"
  />
</template>
