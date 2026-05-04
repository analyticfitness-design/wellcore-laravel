<script setup>
import { useRouter } from 'vue-router';

const props = defineProps({
  to: { type: [String, Object], default: '' },
  iconVariant: { type: String, default: '' }, // red | green | blue | amber | purple | gold | (default neutral)
  name: { type: String, required: true },
  meta: { type: String, default: '' },
  pulse: { type: Boolean, default: false },
});
const emit = defineEmits(['click']);
const router = useRouter();

function handleClick() {
  if (props.to) router.push(props.to);
  emit('click');
}
</script>

<template>
  <div class="tool-row" @click="handleClick">
    <div :class="['tool-icon', iconVariant]">
      <slot name="icon">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="7" height="9" rx="1.5"/>
          <rect x="14" y="3" width="7" height="5" rx="1.5"/>
          <rect x="14" y="12" width="7" height="9" rx="1.5"/>
          <rect x="3" y="16" width="7" height="5" rx="1.5"/>
        </svg>
      </slot>
    </div>
    <div class="tool-name">
      {{ name }}
      <span v-if="pulse" class="pulse-dot"></span>
    </div>
    <span v-if="meta" :class="['meta', /^\d/.test(meta) ? 'tnum' : '']">{{ meta }}</span>
    <svg class="chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <polyline points="9 18 15 12 9 6"></polyline>
    </svg>
  </div>
</template>
