<script setup>
/**
 * WcListRow — Fila grouped inset con leading-color opcional.
 *
 * Mapea a las utilities .wc-list-row + .wc-list-row-leading-* de app.css.
 * El leading-color es un bar de 4px a la izquierda. Los semanticos usan vars
 * del @theme (accent/green/blue/amber) que son neutrales en theme.
 *
 * Props:
 *   leadingColor 'accent' | 'success' | 'info' | 'amber' | 'none'  (default: 'none')
 *   interactive  boolean — si true, cursor pointer (no hace falta para hover, ya lo tiene CSS)
 *   as           string  — etiqueta HTML del root (default 'div', usar 'button' o 'router-link' si clickable)
 *
 * Slots:
 *   leading  — icono/avatar
 *   default  — contenido principal
 *   trailing — meta info / chevron / dropdown
 *
 * Uso:
 *   <WcListRow leading-color="success">
 *     <template #leading><WcIcon name="ph-fill ph-check-circle"/></template>
 *     Entrenamiento completado
 *     <template #trailing><span class="text-xs">hace 2d</span></template>
 *   </WcListRow>
 */
import { computed, useSlots } from 'vue';

const props = defineProps({
    leadingColor: { type: String,  default: 'none', validator: (v) => ['accent', 'success', 'info', 'amber', 'none'].includes(v) },
    interactive:  { type: Boolean, default: false },
    as:           { type: String,  default: 'div' },
});

defineEmits(['click']);

const slots = useSlots();

const leadingClass = computed(() => {
    if (props.leadingColor === 'none') return null;
    return `wc-list-row-leading wc-list-row-leading-${props.leadingColor}`;
});
</script>

<template>
  <component
    :is="as"
    :class="['wc-list-row', { 'cursor-pointer': interactive }]"
    @click="interactive ? $emit('click', $event) : null"
  >
    <span v-if="leadingClass" :class="leadingClass" aria-hidden="true" />
    <span v-if="slots.leading" class="shrink-0">
      <slot name="leading" />
    </span>
    <span class="flex-1 min-w-0">
      <slot />
    </span>
    <span v-if="slots.trailing" class="shrink-0">
      <slot name="trailing" />
    </span>
  </component>
</template>
