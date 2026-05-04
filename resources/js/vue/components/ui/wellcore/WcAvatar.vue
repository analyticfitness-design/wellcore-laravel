<script setup>
/**
 * WcAvatar — Avatar con conic ring opcional del Design System v1.
 *
 * Si hay src renderiza la imagen; si no, fallback a la inicial del name.
 * El conic ring usa --grad-conic-avatar (vive en :root, neutral en ambos
 * temas porque combina --color-wc-accent + un gris #71717A).
 *
 * Props:
 *   name string  — nombre (se toma la inicial si no hay src)
 *   src  string  — URL de la imagen (opcional)
 *   size 'sm' | 'md' | 'lg'   (default: 'md')
 *   ring boolean — si true (default), aplica conic-gradient en border
 *
 * Uso:
 *   <WcAvatar name="Silvia"/>
 *   <WcAvatar :name="user.name" :src="user.avatar_url" size="lg"/>
 *   <WcAvatar name="C" :ring="false"/>
 */
import { computed } from 'vue';

const props = defineProps({
    name: { type: String, default: '' },
    src:  { type: String, default: null },
    size: { type: String, default: 'md', validator: (v) => ['sm', 'md', 'lg'].includes(v) },
    ring: { type: Boolean, default: true },
});

const sizeClass = computed(() => ({
    'sm': 'wc-avatar-sm',
    'md': '',
    'lg': 'wc-avatar-lg',
})[props.size]);

const initial = computed(() => {
    const trimmed = (props.name || '').trim();
    return trimmed ? trimmed.charAt(0).toUpperCase() : '?';
});

const containerClass = computed(() => [
    'wc-avatar',
    sizeClass.value,
    { 'wc-avatar-no-ring': !props.ring },
]);
</script>

<template>
  <span :class="containerClass" role="img" :aria-label="name || 'avatar'">
    <img
      v-if="src"
      :src="src"
      :alt="name"
      class="w-full h-full rounded-full object-cover"
      draggable="false"
    />
    <span v-else aria-hidden="true">{{ initial }}</span>
  </span>
</template>
