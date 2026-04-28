<script setup>
import { computed, ref, watch } from 'vue';
import { useAdminPlansStore } from '../../../stores/adminPlans';

const store = useAdminPlansStore();

const localSearch = ref(store.filters.search);

let debounce = null;
watch(localSearch, (val) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => store.setFilter('search', val), 280);
});

const typeOptions = [
    { value: 'all',           label: 'Todos' },
    { value: 'entrenamiento', label: 'Entrena.' },
    { value: 'nutricion',     label: 'Nutricion' },
    { value: 'habitos',       label: 'Habitos' },
    { value: 'suplementacion',label: 'Suplem.' },
    { value: 'ciclo',         label: 'Ciclo' },
];

const publicOptions = [
    { value: 'all', label: 'Todos' },
    { value: 'yes', label: 'Publicos' },
    { value: 'no',  label: 'Privados' },
];

const aiOptions = [
    { value: 'all', label: 'Todos' },
    { value: 'yes', label: 'AI' },
    { value: 'no',  label: 'Manual' },
];

const coachOptions = computed(() => [
    { value: 'all', label: 'Coaches' },
    ...store.coaches.map(c => ({ value: String(c.id), label: c.name })),
]);
</script>

<template>
  <div class="filters-bar">
    <!-- Search -->
    <div class="search-wrap">
      <svg class="search-icon" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
      </svg>
      <input
        v-model="localSearch"
        type="search"
        class="search-input"
        placeholder="Buscar por nombre, metodologia..."
        aria-label="Buscar templates"
      />
    </div>

    <!-- Type pills -->
    <div class="pills-row" role="group" aria-label="Filtro por tipo">
      <button
        v-for="opt in typeOptions"
        :key="opt.value"
        type="button"
        class="pill"
        :class="{ 'pill--active': store.filters.type === opt.value }"
        @click="store.setFilter('type', opt.value)"
      >{{ opt.label }}</button>
    </div>

    <!-- Right selects -->
    <div class="selects-row">
      <select
        :value="store.filters.public"
        class="filter-select"
        aria-label="Filtro visibilidad"
        @change="store.setFilter('public', $event.target.value)"
      >
        <option v-for="o in publicOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
      </select>

      <select
        :value="store.filters.coach"
        class="filter-select"
        aria-label="Filtro por coach"
        @change="store.setFilter('coach', $event.target.value)"
      >
        <option v-for="o in coachOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
      </select>

      <select
        :value="store.filters.ai"
        class="filter-select"
        aria-label="Filtro por origen"
        @change="store.setFilter('ai', $event.target.value)"
      >
        <option v-for="o in aiOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
      </select>

      <!-- Clear -->
      <button
        v-if="store.hasActiveFilters"
        type="button"
        class="btn-clear"
        aria-label="Limpiar filtros"
        @click="store.clearFilters(); localSearch = '';"
      >
        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
        </svg>
        Limpiar
      </button>
    </div>
  </div>
</template>

<style scoped>
.filters-bar {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
@media (min-width: 768px) {
    .filters-bar { flex-direction: row; align-items: center; flex-wrap: wrap; gap: 10px; }
}

/* Search */
.search-wrap {
    position: relative;
    flex: 1;
    min-width: 180px;
}
.search-icon {
    position: absolute;
    left: 10px; top: 50%;
    transform: translateY(-50%);
    color: var(--color-wc-text-tertiary);
    pointer-events: none;
}
.search-input {
    width: 100%;
    height: 36px;
    padding: 0 12px 0 32px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: rgba(255, 255, 255, 0.03);
    color: var(--color-wc-text);
    font-family: var(--font-sans);
    font-size: 12px;
    transition: border-color 0.15s var(--ease-out, ease);
    box-sizing: border-box;
}
.search-input::placeholder { color: var(--color-wc-text-tertiary); }
.search-input:focus { outline: none; border-color: var(--color-wc-accent, #DC2626); }

/* Pills */
.pills-row {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}
.pill {
    height: 28px;
    padding: 0 10px;
    border-radius: 20px;
    border: 1px solid var(--color-wc-border);
    background: transparent;
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.pill:hover { border-color: var(--color-wc-border-2); color: var(--color-wc-text); }
.pill--active {
    background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.1));
    border-color: var(--color-wc-accent, #DC2626);
    color: var(--color-wc-accent, #DC2626);
}

/* Selects row */
.selects-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.filter-select {
    height: 36px;
    padding: 0 10px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: rgba(255, 255, 255, 0.03);
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    cursor: pointer;
    appearance: none;
    transition: border-color 0.15s ease;
}
.filter-select:focus { outline: none; border-color: var(--color-wc-accent); }

.btn-clear {
    height: 28px;
    padding: 0 10px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: transparent;
    color: var(--color-wc-text-tertiary);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    cursor: pointer;
    display: flex; align-items: center; gap: 4px;
    transition: color 0.15s ease, border-color 0.15s ease;
}
.btn-clear:hover { color: var(--color-wc-text); border-color: var(--color-wc-border-2); }

@media (prefers-reduced-motion: reduce) {
    .pill, .search-input, .filter-select, .btn-clear { transition: none !important; }
}
</style>
