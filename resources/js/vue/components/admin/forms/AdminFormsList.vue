<script setup>
import AdminFormCard from './AdminFormCard.vue';

const props = defineProps({
    forms: { type: Array, required: true },
    selectedSlug: { type: String, default: null },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['select', 'preview']);
</script>

<template>
  <!-- Loading skeleton -->
  <div v-if="loading && !forms.length" class="forms-grid" aria-busy="true" aria-label="Cargando formularios">
    <div v-for="i in 6" :key="i" class="forms-skeleton-card"></div>
  </div>

  <!-- Empty state -->
  <div v-else-if="!loading && !forms.length" class="forms-empty">
    <div class="empty-num" aria-hidden="true">—</div>
    <p class="empty-msg">"El dato sin acción es solo decoración."</p>
    <span class="empty-hint">Ningún formulario coincide con los filtros activos.</span>
  </div>

  <!-- Grid -->
  <div v-else class="forms-grid">
    <AdminFormCard
      v-for="form in forms"
      :key="`${form.area}/${form.slug}`"
      :form="form"
      :is-active="selectedSlug === `${form.area}/${form.slug}`"
      @select="$emit('select', form)"
      @preview="$emit('preview', form)"
    />
  </div>
</template>

<style scoped>
.forms-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}
@media (min-width: 640px) {
    .forms-grid { grid-template-columns: repeat(2, 1fr); }
}

.forms-skeleton-card {
    height: 152px;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary);
    animation: forms-pulse 1.5s ease-in-out infinite;
}
@keyframes forms-pulse {
    0%, 100% { opacity: 0.6; }
    50%       { opacity: 0.9; }
}

.forms-empty {
    padding: 48px 16px;
    text-align: center;
}
.empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--color-wc-bg-tertiary);
    letter-spacing: 0.1em;
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial);
    font-style: italic;
    font-size: 12px;
    color: var(--color-wc-text-tertiary);
    line-height: 1.55;
    margin: 0 0 8px;
    text-wrap: balance;
}
.empty-hint {
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}

@media (prefers-reduced-motion: reduce) {
    .forms-skeleton-card { animation: none; opacity: 0.7; }
}
</style>
