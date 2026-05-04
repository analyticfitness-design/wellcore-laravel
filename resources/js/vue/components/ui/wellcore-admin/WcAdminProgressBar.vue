<script setup>
defineProps({
  percent: { type: Number, default: 0 }, // 0..100
  tall: { type: Boolean, default: false },
});
</script>

<template>
  <div :class="['pbar', { tall }]">
    <div class="fill" :style="{ width: percent + '%' }"></div>
  </div>
</template>
