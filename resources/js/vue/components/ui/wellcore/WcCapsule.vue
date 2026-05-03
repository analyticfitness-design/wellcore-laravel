<script setup>
/**
 * WcCapsule — Chip pill 999px del Design System v1.
 *
 * Mapea a las utilities .wc-chip / .wc-chip-* de app.css. Tokens semanticos
 * usan vars del @theme (--color-wc-green/blue/amber) por lo que se adaptan
 * a light/dark sin overrides locales.
 *
 * Props:
 *   variant 'accent' | 'neutral' | 'success' | 'amber'  (default: 'accent')
 *   size    'sm' | 'md'                                  (default: 'md')
 *
 * Slots:
 *   leading  — icono/dot a la izquierda (ej: <WcPulseDot/>)
 *   default  — texto del chip
 *   trailing — icono/badge a la derecha (ej: <WcIcon name="ph-x"/>)
 *
 * Uso:
 *   <WcCapsule variant="accent">Plan Elite</WcCapsule>
 *   <WcCapsule variant="success">
 *     <template #leading><WcPulseDot color="success"/></template>
 *     Activa
 *   </WcCapsule>
 */
import { computed } from 'vue';

const props = defineProps({
    variant: { type: String, default: 'accent',  validator: (v) => ['accent', 'neutral', 'success', 'amber'].includes(v) },
    size:    { type: String, default: 'md',      validator: (v) => ['sm', 'md'].includes(v) },
});

const variantClass = computed(() => ({
    'accent':  'wc-chip',
    'neutral': 'wc-chip wc-chip-neutral',
    'success': 'wc-chip wc-chip-success',
    'amber':   'wc-chip wc-chip-amber',
})[props.variant]);

const sizeStyle = computed(() =>
    props.size === 'sm'
        ? { padding: '2px 8px', fontSize: '10px' }
        : null
);
</script>

<template>
  <span :class="variantClass" :style="sizeStyle">
    <slot name="leading" />
    <slot />
    <slot name="trailing" />
  </span>
</template>
