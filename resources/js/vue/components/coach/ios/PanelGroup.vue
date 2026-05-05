<script setup>
defineProps({
  title: { type: String, default: '' },
  badge: { type: String, default: '' },
  link: { type: String, default: '' },
  linkLabel: { type: String, default: 'Ver todos →' },
});

const emit = defineEmits(['link-click']);
</script>

<template>
  <section>
    <header v-if="title || badge || link" class="section-header">
      <span v-if="title" class="section-title">{{ title }}</span>
      <span v-if="badge" class="section-badge">{{ badge }}</span>
      <a
        v-if="link"
        :href="link"
        class="section-link"
        @click.prevent="emit('link-click')"
      >
        {{ linkLabel }}
      </a>
    </header>
    <div class="panel">
      <slot />
    </div>
  </section>
</template>
