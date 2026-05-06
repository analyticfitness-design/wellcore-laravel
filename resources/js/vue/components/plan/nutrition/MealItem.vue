<template>
  <li v-if="formattedFood" class="flex items-start gap-2.5">
    <span v-if="icon" class="shrink-0 text-base leading-none">{{ icon }}</span>
    <span v-else class="mt-2 h-1 w-1 shrink-0 rounded-full bg-wc-accent"></span>
    <span class="text-sm leading-relaxed text-wc-text-secondary">{{ formattedFood }}</span>
  </li>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  food: {
    type: String,
    required: true,
  },
  icon: {
    type: String,
    default: '',
  },
});

const formattedFood = computed(() => {
  if (!props.food) return '';
  const trimmed = props.food.trim();
  if (!trimmed) return '';
  const first = trimmed.charAt(0);
  if (first === first.toLowerCase() && first !== first.toUpperCase()) {
    return first.toUpperCase() + trimmed.slice(1);
  }
  return trimmed;
});
</script>
