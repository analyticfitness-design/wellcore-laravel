<script setup>
/**
 * WcSectionHeader — Header de seccion con caption uppercase + CTA opcional.
 *
 * Tipico patron del DS v1 para abrir secciones del dashboard ("Misiones diarias",
 * "Actividad reciente"). El title usa wc-caption (uppercase + spacing). El CTA
 * a la derecha es opcional y puede ser link, button, o slot custom.
 *
 * Props:
 *   title    string — texto del caption (uppercase via wc-caption)
 *   ctaLabel string — label del CTA (opcional)
 *   ctaTo    string — ruta para router-link (opcional)
 *
 * Slots:
 *   actions — override del CTA si necesitas algo custom (count, multiple buttons, etc)
 *
 * Eventos:
 *   @cta — emitido al click del CTA
 *
 * Uso:
 *   <WcSectionHeader title="Misiones diarias">
 *     <template #actions><span class="wc-tnum">0/4 completadas</span></template>
 *   </WcSectionHeader>
 *
 *   <WcSectionHeader title="Actividad reciente" cta-label="Ver todo" cta-to="/client/activity"/>
 */
import { useSlots } from 'vue';

defineProps({
    title:    { type: String, required: true },
    ctaLabel: { type: String, default: '' },
    ctaTo:    { type: String, default: '' },
});

defineEmits(['cta']);

const slots = useSlots();
</script>

<template>
  <header class="flex items-center justify-between mb-3">
    <h2 class="wc-caption m-0">{{ title }}</h2>
    <slot name="actions">
      <router-link
        v-if="ctaLabel && ctaTo"
        :to="ctaTo"
        class="text-xs"
        style="color: var(--color-wc-accent);"
        @click="$emit('cta', $event)"
      >
        {{ ctaLabel }}
      </router-link>
      <button
        v-else-if="ctaLabel"
        type="button"
        class="text-xs"
        style="color: var(--color-wc-accent);"
        @click="$emit('cta', $event)"
      >
        {{ ctaLabel }}
      </button>
    </slot>
  </header>
</template>
