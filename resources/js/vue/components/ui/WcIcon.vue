<script setup>
/**
 * WcIcon — wrapper unificado para iconos.
 *
 * Formatos:
 *   "ph-fill ph-trophy"  → Phosphor web font
 *   "wc:medal-hex"       → SVG sprite custom
 */
import { computed } from 'vue';

const props = defineProps({
    name: { type: String, required: true },
    size: { type: [String, Number], default: 16 },
});

const parsed = computed(() => {
    const name = props.name.trim();
    if (name.startsWith('wc:')) return { type: 'sprite', id: name.slice(3) };
    return { type: 'phosphor', klass: name };
});

const sizeStyle = computed(() => {
    const n = typeof props.size === 'number' ? `${props.size}px` : props.size;
    return { width: n, height: n, fontSize: n };
});
</script>

<template>
  <svg v-if="parsed.type === 'sprite'" :style="sizeStyle" aria-hidden="true" class="inline-block align-middle shrink-0">
    <use :href="`#wc-${parsed.id}`" />
  </svg>
  <i v-else :class="parsed.klass" :style="sizeStyle" aria-hidden="true" />
</template>
