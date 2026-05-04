<script setup>
defineProps({
  title: { type: String, required: true },
  hint: { type: String, default: '' },
  ctaText: { type: String, default: '' },
});
const emit = defineEmits(['cta']);
</script>

<template>
  <div class="empty">
    <div class="empty-svg">
      <slot name="art">
        <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
          <rect x="22" y="26" width="20" height="22" rx="1"></rect>
          <rect x="6" y="36" width="16" height="12" rx="1"></rect>
          <rect x="42" y="32" width="16" height="16" rx="1"></rect>
          <path d="M32 22 L34 16 L40 15 L35.5 11 L36.5 5 L32 8 L27.5 5 L28.5 11 L24 15 L30 16 Z"></path>
          <line x1="6" y1="52" x2="58" y2="52"></line>
        </svg>
      </slot>
    </div>
    <div class="empty-ttl" v-html="title"></div>
    <div v-if="hint" class="empty-hint">{{ hint }}</div>
    <button v-if="ctaText" class="empty-cta" @click="emit('cta', $event)">{{ ctaText }}</button>
  </div>
</template>
