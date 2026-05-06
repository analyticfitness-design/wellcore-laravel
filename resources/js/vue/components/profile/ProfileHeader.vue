<script setup>
/**
 * ProfileHeader.vue — eyebrow + h1 "MI PERFIL" + subtítulo.
 *
 * Tipografía: Oswald (font-display) en eyebrow + h1.
 * Subtítulo: Raleway/Inter (font-sans) max 56ch.
 */
defineProps({
    eyebrow:  { type: String, default: 'CUENTA · IDENTIDAD' },
    title:    { type: String, default: 'MI PERFIL' },
    subtitle: { type: String, default: 'Mantén actualizada tu información para que tu coach pueda personalizar tu plan al máximo.' },
});
</script>

<template>
  <header class="profile-header">
    <p v-if="eyebrow" class="eyebrow">
      <span class="eyebrow-dot" aria-hidden="true"></span>
      <span>{{ eyebrow }}</span>
    </p>
    <h1 class="page-h1 font-display">{{ title }}</h1>
    <p v-if="subtitle" class="page-sub">{{ subtitle }}</p>
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
