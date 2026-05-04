<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: { type: String, default: 'primary' }, // primary | secondary | ghost | coach-cta | empty-cta | banner-cta | profile-cta
  to: { type: [String, Object], default: null },
  href: { type: String, default: null },
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
});
const emit = defineEmits(['click']);

const klass = computed(() => {
  if (props.variant === 'primary') return 'btn-primary';
  if (props.variant === 'secondary') return 'btn-secondary';
  if (props.variant === 'coach-cta') return 'coach-cta';
  if (props.variant === 'empty-cta') return 'empty-cta';
  if (props.variant === 'banner-cta') return 'banner-cta';
  if (props.variant === 'profile-cta') return 'profile-cta';
  return 'btn-secondary';
});

const tag = computed(() => {
  if (props.to) return 'router-link';
  if (props.href) return 'a';
  return 'button';
});
</script>

<template>
  <component
    :is="tag"
    :class="klass"
    :to="to"
    :href="href"
    :type="tag === 'button' ? type : null"
    :disabled="tag === 'button' ? disabled : null"
    @click="emit('click', $event)"
  >
    <slot />
  </component>
</template>
