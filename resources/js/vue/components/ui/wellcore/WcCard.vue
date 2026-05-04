<script setup>
defineProps({
  title: { type: String, default: '' },
  meta: { type: String, default: '' },
  variant: { type: String, default: 'default' }, // default | grain
  delay: { type: [Number, String], default: 0 },
  cardClass: { type: String, default: '' },
});
</script>

<template>
  <section
    :class="['card', 'section', variant === 'grain' ? 'grain' : '', cardClass]"
    :style="delay ? { animationDelay: typeof delay === 'number' ? delay + 'ms' : delay } : null"
  >
    <div v-if="title || meta || $slots.head" class="card-head">
      <div class="card-head-left">
        <slot name="head">
          <span v-if="title" class="card-title">{{ title }}</span>
        </slot>
      </div>
      <span v-if="meta || $slots.meta" class="card-meta">
        <slot name="meta">{{ meta }}</slot>
      </span>
    </div>
    <slot />
  </section>
</template>
