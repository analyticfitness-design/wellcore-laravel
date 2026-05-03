<script setup>
/**
 * WcCard — Surface card del Design System v1.
 *
 * Variantes mappean a las utilities .wc-card-* de app.css. Theme-aware: las
 * sombras layered y los tokens de superficie viven en :root (light) y .dark
 * (override), asi que esta card se ve correctamente en ambos temas sin que
 * el consumer tenga que pasar nada.
 *
 * Props:
 *   variant     'default' | 'elevated' | 'prominent' | 'glass'   (default: 'default')
 *   padding     5 | 6 | 7                                         (override del default por variant)
 *   interactive boolean — si true, aplica hover lift + glow (solo default/elevated tienen hover en CSS)
 *
 * Slots:
 *   header  — opcional, renderiza arriba con margin-bottom
 *   default — body de la card
 *   footer  — opcional, renderiza al final con margin-top y separador wc-divider
 *
 * Eventos: re-emite @click si se le pasa (no obligatorio).
 *
 * Uso:
 *   <WcCard variant="elevated" interactive>
 *     <template #header><h3 class="wc-caption">Misiones</h3></template>
 *     <p>Body</p>
 *     <template #footer><WcButton>Ver mas</WcButton></template>
 *   </WcCard>
 */
import { computed, useSlots } from 'vue';

const props = defineProps({
    variant:     { type: String,  default: 'default', validator: (v) => ['default', 'elevated', 'prominent', 'glass'].includes(v) },
    padding:     { type: [Number, String], default: null },
    interactive: { type: Boolean, default: false },
});

defineEmits(['click']);

const slots = useSlots();

const variantClass = computed(() => ({
    'default':   'wc-card',
    'elevated':  'wc-card-elevated',
    'prominent': 'wc-card-prominent',
    'glass':     'wc-card-glass',
})[props.variant]);

const paddingStyle = computed(() => {
    if (props.padding == null) return null;
    return { padding: `var(--ds-s-${props.padding})` };
});

const hasHeader = computed(() => !!slots.header);
const hasFooter = computed(() => !!slots.footer);
</script>

<template>
  <div
    :class="[variantClass, { 'wc-card-interactive': interactive }]"
    :style="paddingStyle"
    @click="$emit('click', $event)"
  >
    <header v-if="hasHeader" class="mb-4">
      <slot name="header" />
    </header>
    <slot />
    <footer v-if="hasFooter">
      <hr class="wc-divider" />
      <slot name="footer" />
    </footer>
  </div>
</template>
