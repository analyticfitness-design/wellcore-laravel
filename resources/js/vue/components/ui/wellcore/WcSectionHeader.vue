<script setup>
defineProps({
  title: { type: String, required: true },
  meta: { type: String, default: '' },
});
</script>

<template>
  <div class="card-head">
    <div class="card-head-left">
      <span class="card-title">{{ title }}</span>
    </div>
    <span v-if="meta || $slots.meta" class="card-meta">
      <slot name="meta">{{ meta }}</slot>
    </span>
  </div>
</template>
