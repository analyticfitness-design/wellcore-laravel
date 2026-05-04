<script setup>
defineProps({
  variant: { type: String, default: 'accent' }, // accent | warning | info | success
  title: { type: String, required: true },
  sub: { type: String, default: '' },
  ctaText: { type: String, default: '' },
  delay: { type: [Number, String], default: 0 },
});
const emit = defineEmits(['cta', 'click']);
</script>

<template>
  <div
    :class="['banner', 'section', 'grain', `banner--${variant}`]"
    :style="delay ? { animationDelay: typeof delay === 'number' ? delay + 'ms' : delay } : null"
    role="button"
    tabindex="0"
    @click="emit('click', $event)"
    @keydown.enter="emit('click', $event)"
  >
    <div class="banner-icon">
      <slot name="icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 9v4M12 17h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path>
        </svg>
      </slot>
    </div>
    <div style="flex:1; min-width:0">
      <div class="banner-title">{{ title }}</div>
      <div v-if="sub" class="banner-sub">{{ sub }}</div>
    </div>
    <button v-if="ctaText" class="banner-cta" @click.stop="emit('cta', $event)">
      {{ ctaText }}
    </button>
    <svg v-else class="banner-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <polyline points="9 18 15 12 9 6"></polyline>
    </svg>
  </div>
</template>
