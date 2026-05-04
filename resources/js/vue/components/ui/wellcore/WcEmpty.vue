<script setup>
defineProps({
  title: { type: String, required: true },
  sub: { type: String, default: '' },
  ctaText: { type: String, default: '' },
});
const emit = defineEmits(['cta']);
</script>

<template>
  <div class="empty">
    <div class="empty-art">
      <slot name="art">
        <svg width="64" height="64" viewBox="0 0 64 64" fill="none" stroke="rgba(255,255,255,.22)" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="32" cy="32" r="20"></circle>
          <path d="M22 32h20M32 22v20"></path>
        </svg>
      </slot>
    </div>
    <div>
      <div class="empty-title">{{ title }}</div>
      <p v-if="sub" class="empty-sub">{{ sub }}</p>
    </div>
    <button v-if="ctaText" class="empty-cta" @click="emit('cta', $event)">
      <slot name="cta-icon">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M12 5v14M5 12h14"></path>
        </svg>
      </slot>
      {{ ctaText }}
    </button>
  </div>
</template>
