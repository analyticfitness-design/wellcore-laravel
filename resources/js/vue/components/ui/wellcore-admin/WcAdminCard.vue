<script setup>
defineProps({
  title: { type: String, default: '' },
  meta: { type: String, default: '' },
  link: { type: String, default: '' },
  variant: { type: String, default: 'gb3' }, // gb2 | gb3
  cardClass: { type: String, default: '' },
});
const emit = defineEmits(['link']);
</script>

<template>
  <section :class="['wc-admin-card', variant, cardClass]">
    <div v-if="title || meta || link || $slots.head" class="section-h">
      <div v-if="title || $slots.head" class="ttl">
        <slot name="head">{{ title }}</slot>
      </div>
      <div v-if="meta" class="meta">{{ meta }}</div>
      <div v-if="link" class="lnk" @click="emit('link', $event)">{{ link }}</div>
    </div>
    <slot />
  </section>
</template>
