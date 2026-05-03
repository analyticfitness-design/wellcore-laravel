<script setup>
/**
 * WcBannerAlert — Banner con gradient radial + grain noise overlay del DS v1.
 *
 * Mapea a .wc-banner-alert de app.css (variant accent default). Las 3 variantes
 * adicionales (info/warn/success) overridean inline el rgba del radial gradient
 * y del inset border, manteniendo el SVG noise igual. El bg base usa
 * --color-wc-bg-secondary (theme-aware).
 *
 * Props:
 *   title    string — heading del banner
 *   subtitle string — texto secundario (opcional)
 *   variant  'alert' | 'info' | 'warn' | 'success'  (default: 'alert')
 *   to       string — si pasado, todo el banner es router-link (opcional)
 *
 * Slots:
 *   default  — override del cuerpo (si no pasas title/subtitle)
 *   trailing — chevron / icono / boton a la derecha
 *
 * Eventos:
 *   @click — emitido si el banner es interactivo (con `to`)
 *
 * Uso:
 *   <WcBannerAlert
 *     title="CHECK-IN PENDIENTE"
 *     subtitle="Tu check-in semanal te espera"
 *     to="/client/checkin"
 *   >
 *     <template #trailing><WcIcon name="ph-caret-right"/></template>
 *   </WcBannerAlert>
 */
import { computed, useSlots } from 'vue';

const props = defineProps({
    title:    { type: String, default: '' },
    subtitle: { type: String, default: '' },
    variant:  { type: String, default: 'alert', validator: (v) => ['alert', 'info', 'warn', 'success'].includes(v) },
    to:       { type: String, default: '' },
});

defineEmits(['click']);

const slots = useSlots();

// Cada variant override del rgba accent que usa la utility .wc-banner-alert.
// Mantenemos el SVG noise y el bg de superficie identicos en todas — solo
// cambia el tinte del radial gradient + el inset border ring.
const variantStyle = computed(() => {
    const colors = {
        'alert':   'rgba(220, 38, 38, 0.18)',  // rojo accent
        'info':    'rgba(59, 130, 246, 0.18)', // --color-wc-blue
        'warn':    'rgba(245, 158, 11, 0.18)', // --color-wc-amber
        'success': 'rgba(16, 185, 129, 0.18)', // --color-wc-green
    };
    const rings = {
        'alert':   'rgba(220, 38, 38, 0.20)',
        'info':    'rgba(59, 130, 246, 0.20)',
        'warn':    'rgba(245, 158, 11, 0.20)',
        'success': 'rgba(16, 185, 129, 0.20)',
    };
    const tint = colors[props.variant];
    const ring = rings[props.variant];
    return {
        background: `radial-gradient(circle at 100% 0%, ${tint}, transparent 60%), var(--color-wc-bg-secondary)`,
        boxShadow: `inset 0 0 0 1px ${ring}, var(--sh-soft)`,
    };
});

const isInteractive = computed(() => !!props.to);
</script>

<template>
  <component
    :is="isInteractive ? 'router-link' : 'div'"
    :to="to || undefined"
    class="wc-banner-alert flex items-center"
    style="gap: var(--ds-s-3);"
    :style="variantStyle"
    :role="isInteractive ? 'link' : 'alert'"
    @click="$emit('click', $event)"
  >
    <div class="flex-1 min-w-0 relative" style="z-index: 1;">
      <slot>
        <p v-if="title" class="font-display text-base m-0" style="color: var(--color-wc-text);">
          {{ title }}
        </p>
        <p v-if="subtitle" class="text-sm mt-1 mb-0" style="color: var(--color-wc-text-secondary);">
          {{ subtitle }}
        </p>
      </slot>
    </div>
    <div v-if="$slots.trailing" class="shrink-0 relative" style="z-index: 1; color: var(--color-wc-text-secondary);">
      <slot name="trailing" />
    </div>
  </component>
</template>
