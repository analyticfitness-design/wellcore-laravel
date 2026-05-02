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
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: var(--c-surface);
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
    color: var(--c-text-3);
    pointer-events: none;
}
.search-input {
    width: 100%;
    height: 40px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255,255,255,0.03);
    padding: 0 32px 0 32px;
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--c-text);
    transition: border-color 0.15s var(--ease-out, ease), background 0.15s var(--ease-out, ease);
}
.search-input::placeholder { color: var(--c-text-3); }
.search-input:focus {
    outline: none;
    border-color: var(--c-accent);
    background: rgba(255,255,255,0.05);
}
.search-clear {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: var(--c-text-3);
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
    background: rgba(255,255,255,0.06);
    color: var(--c-text);
}

.status-pills {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}
.pill {
    height: var(--tap-comfort, 48px);
    padding: 0 12px;
    border-radius: var(--r-pill, 999px);
    border: 1px solid var(--c-border);
    background: transparent;
    color: var(--c-text-2);
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.pill:hover {
    background: rgba(255,255,255,0.04);
    color: var(--c-text);
}
.pill--active {
    background: var(--c-accent-dim);
    border-color: var(--c-accent);
    color: #F87171;
}

.clear-all {
    margin-left: auto;
    background: transparent;
    border: none;
    color: var(--c-text-3);
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    cursor: pointer;
    padding: 6px 8px;
    min-height: var(--tap-comfort, 48px);
    border-radius: var(--r-sm, 12px);
    transition: color 0.15s var(--ease-out, ease);
}
.clear-all:hover { color: #F87171; }
</style>
