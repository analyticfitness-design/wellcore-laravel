<script setup>
defineProps({
  variant: { type: String, default: 'accent' }, // accent | green | amber | info | purple
  pulse: { type: Boolean, default: false },
});
</script>

<template>
  <span :class="['chip', `chip-${variant}`, { 'chip-pulse': pulse }]">
    <slot />
  </span>
</template>
