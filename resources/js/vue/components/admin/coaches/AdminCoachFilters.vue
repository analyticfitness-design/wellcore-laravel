<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';
import { useAdminCoachListStore } from '../../../stores/adminCoachList';

const store = useAdminCoachListStore();

const localSearch = ref(store.filters.search);
let debounceTimer = null;

watch(localSearch, (val) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => store.setSearch(val), 280);
});

watch(() => store.filters.search, (val) => {
    if (val !== localSearch.value) localSearch.value = val;
});

onBeforeUnmount(() => clearTimeout(debounceTimer));

const STATUS_PILLS = [
    { value: 'active', label: 'ACTIVOS' },
    { value: 'inactive', label: 'INACTIVOS' },
    { value: 'all', label: 'TODOS' },
];
</script>

<template>
  <div class="filters-card">
    <div class="filters-row">
      <div class="search-wrap">
        <svg class="search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
          <path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <input
          v-model="localSearch"
          type="text"
          placeholder="Buscar por nombre, usuario o email..."
          class="search-input"
        />
        <button
          v-if="localSearch"
          type="button"
          class="search-clear"
          aria-label="Limpiar busqueda"
          @click="localSearch = ''"
        >×</button>
      </div>

      <div class="status-pills" role="group" aria-label="Filtrar por estado">
        <button
          v-for="pill in STATUS_PILLS"
          :key="pill.value"
          type="button"
          class="pill"
          :class="{ 'pill--active': store.filters.status === pill.value }"
          @click="store.setStatus(pill.value)"
        >
          {{ pill.label }}
        </button>
      </div>

      <button
        v-if="store.hasActiveFilters"
        type="button"
        class="clear-all"
        @click="store.clearFilters()"
      >
        LIMPIAR FILTROS
      </button>
    </div>
  </div>
</template>

<style scoped>
.filters-card {
    border-radius: 12px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.6);
    padding: 12px;
}
.filters-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}
.search-wrap {
    flex: 1 1 240px;
    position: relative;
    min-width: 0;
}
.search-icon {
    position: absolute;
    left: 11px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--color-wc-text-tertiary);
    pointer-events: none;
}
.search-input {
    width: 100%;
    height: 36px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: rgba(255, 255, 255, 0.03);
    padding: 0 32px 0 32px;
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--color-wc-text);
    transition: border-color 0.15s var(--ease-out, ease), background 0.15s var(--ease-out, ease);
}
.search-input::placeholder { color: var(--color-wc-text-tertiary); }
.search-input:focus {
    outline: none;
    border-color: var(--color-wc-accent, #DC2626);
    background: rgba(255, 255, 255, 0.05);
}
.search-clear {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: var(--color-wc-text-tertiary);
    width: 22px;
    height: 22px;
    border-radius: 50%;
    font-size: 18px;
    line-height: 1;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.search-clear:hover {
    background: rgba(255, 255, 255, 0.06);
    color: var(--color-wc-text);
}

.status-pills {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}
.pill {
    height: 28px;
    padding: 0 12px;
    border-radius: 999px;
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
.pill:hover {
    background: rgba(255, 255, 255, 0.04);
    color: var(--color-wc-text);
}
.pill--active {
    background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.1));
    border-color: var(--color-wc-accent, #DC2626);
    color: var(--color-wc-red-text, #F87171);
}

.clear-all {
    margin-left: auto;
    background: transparent;
    border: none;
    color: var(--color-wc-text-tertiary);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    cursor: pointer;
    padding: 6px 8px;
    border-radius: 6px;
    transition: color 0.15s var(--ease-out, ease);
}
.clear-all:hover { color: var(--color-wc-red-text, #F87171); }
</style>
