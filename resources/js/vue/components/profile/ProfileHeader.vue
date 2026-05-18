<script setup>
/**
 * ProfileHeader.vue — eyebrow + h1 "Tu perfil" + subtítulo.
 *
 * Tipografía: Oswald (font-display) en eyebrow + h1.
 * Subtítulo: Raleway/Inter (font-sans) max 56ch.
 *
 * Los textos por defecto vienen del namespace `client_account` para que
 * el componente sea i18n-aware sin obligar al padre a pasarlos siempre.
 */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    eyebrow:  { type: String, default: '' },
    title:    { type: String, default: '' },
    subtitle: { type: String, default: '' },
});

const eyebrowText  = computed(() => props.eyebrow  || t('client_account.profile_eyebrow'));
const titleText    = computed(() => props.title    || t('client_account.profile_title'));
const subtitleText = computed(() => props.subtitle || t('client_account.profile_subtitle'));
</script>

<template>
  <header class="profile-header">
    <p v-if="eyebrowText" class="eyebrow">
      <span class="eyebrow-dot" aria-hidden="true"></span>
      <span>{{ eyebrowText }}</span>
    </p>
    <h1 class="page-h1 font-display">{{ titleText }}</h1>
    <p v-if="subtitleText" class="page-sub">{{ subtitleText }}</p>
  </header>
</template>

<style scoped>
.profile-header { width: 100%; }

.eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin: 0 0 12px;
  font-family: 'Oswald', Impact, sans-serif;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
}
.eyebrow-dot {
  width: 4px;
  height: 4px;
  border-radius: 999px;
  background: var(--color-wc-text-quaternary);
}

.page-h1 {
  margin: 0;
  font-weight: 600;
  font-size: 28px;
  line-height: 1.1;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  color: var(--color-wc-text);
}
@media (min-width: 1024px) {
  .page-h1 { font-size: 32px; }
}

.page-sub {
  margin: 6px 0 0;
  font-size: 15px;
  line-height: 1.5;
  color: var(--color-wc-text-secondary);
  max-width: 56ch;
}
</style>
