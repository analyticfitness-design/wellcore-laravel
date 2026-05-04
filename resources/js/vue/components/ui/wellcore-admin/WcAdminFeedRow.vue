<script setup>
defineProps({
  variant: { type: String, default: 'pago' }, // pago | insc
  name: { type: String, required: true },
  plan: { type: String, default: '' },
  when: { type: String, default: '' },
  amount: { type: String, default: '' },
  pending: { type: String, default: '' },
  ctaText: { type: String, default: '' },
  meta: { type: String, default: '' },
});
const emit = defineEmits(['cta', 'click']);
</script>

<template>
  <div :class="['feed-row', variant]" @click="emit('click', $event)">
    <span class="led"></span>
    <div class="feed-icon">
      <slot name="icon">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <rect x="2" y="6" width="20" height="13" rx="2"></rect>
          <circle cx="12" cy="12.5" r="2"></circle>
        </svg>
      </slot>
    </div>
    <div class="feed-body">
      <div class="feed-name"><span class="nm">{{ name }}</span></div>
      <div class="feed-meta">
        <span v-if="plan" class="plan">{{ plan }}</span>
        <span v-if="meta">{{ meta }}</span>
        <span v-if="when" class="when">{{ when }}</span>
      </div>
    </div>
    <div v-if="amount" class="feed-amount tnum">{{ amount }}</div>
    <span v-if="pending" class="feed-pending">{{ pending }}</span>
    <button v-if="ctaText" class="feed-cta" @click.stop="emit('cta', $event)">{{ ctaText }}</button>
  </div>
</template>
