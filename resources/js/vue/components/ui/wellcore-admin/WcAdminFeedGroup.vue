<script setup>
defineProps({
  title: { type: String, required: true },
  count: { type: [String, Number], default: '' },
  countVariant: { type: String, default: 'green' }, // green | blue
  when: { type: String, default: '' },
});
</script>

<template>
  <div class="feed-group">
    <div :class="['feed-head', countVariant]">
      <div class="ttl">
        {{ title }}<span v-if="count !== ''" class="count-pill">{{ count }}</span>
      </div>
      <div v-if="when" class="when">{{ when }}</div>
    </div>
    <slot />
  </div>
</template>
