<script setup>
import { useAdminClientDetailStore } from '../../../stores/adminClientDetail';

const store = useAdminClientDetailStore();

const TABS = [
    { id: 'resumen',      label: 'RESUMEN' },
    { id: 'plan',         label: 'PLAN' },
    { id: 'checkins',     label: 'CHECK-INS' },
    { id: 'pagos',        label: 'PAGOS' },
    { id: 'comunicacion', label: 'COMUNICACION' },
    { id: 'notas',        label: 'NOTAS' },
    { id: 'intake',       label: 'INTAKE' },
];
</script>

<template>
  <nav class="client-tabs" role="tablist" aria-label="Secciones del cliente">
    <button
      v-for="tab in TABS"
      :key="tab.id"
      type="button"
      role="tab"
      class="tab"
      :class="{ 'tab--active': store.activeTab === tab.id }"
      :aria-selected="store.activeTab === tab.id"
      :aria-controls="`panel-${tab.id}`"
      :id="`tab-${tab.id}`"
      @click="store.setTab(tab.id)"
    >
      {{ tab.label }}
    </button>
  </nav>
</template>

<style scoped>
.client-tabs {
    display: flex;
    gap: 4px;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scroll-snap-type: x proximity;
    padding-bottom: 4px;
    border-bottom: 1px solid var(--c-border);
}
.client-tabs::-webkit-scrollbar { display: none; }
.client-tabs { scrollbar-width: none; }

.tab {
    flex-shrink: 0;
    scroll-snap-align: start;
    height: var(--tap-comfort, 48px);
    padding: 0 14px;
    border: none;
    background: transparent;
    color: var(--c-text-3);
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.tab:hover { color: var(--c-text-2); }
.tab:focus-visible {
    outline: 1px solid var(--c-accent);
    outline-offset: 2px;
    border-radius: 4px;
}
.tab--active {
    color: var(--c-text);
    border-bottom-color: var(--c-accent);
}
</style>
