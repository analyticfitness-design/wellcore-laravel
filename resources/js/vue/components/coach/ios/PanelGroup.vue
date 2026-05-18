<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  title: { type: String, default: '' },
  badge: { type: String, default: '' },
  link: { type: String, default: '' },
  linkLabel: { type: String, default: '' },
});

const emit = defineEmits(['link-click']);
const { t } = useI18n();

const resolvedLinkLabel = computed(() => props.linkLabel || t('coach_home.messages_see_all'));
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
        {{ resolvedLinkLabel }}
      </a>
    </header>
    <div class="panel">
      <slot />
    </div>
  </section>
</template>
