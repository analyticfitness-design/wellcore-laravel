<script setup>
import AdminToolCard from './AdminToolCard.vue';
import { useAdminToolsStore } from '../../../stores/adminTools';
import { storeToRefs } from 'pinia';

const emit = defineEmits(['run']);

const store = useAdminToolsStore();
const { filteredTools, categories, activeCategory, searchQuery, loadingCatalog, error, isSuperadmin, hasFilters } = storeToRefs(store);
</script>

<template>
  <!-- Filters bar -->
  <div class="catalog-filters">
    <!-- Search -->
    <div class="catalog-search-wrap">
      <svg class="catalog-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
      </svg>
      <input
        v-model="store.searchQuery"
        class="catalog-search"
        placeholder="Buscar herramienta..."
        aria-label="Buscar herramienta"
        type="search"
      />
    </div>

    <!-- Category chips -->
    <div class="catalog-chips" role="group" aria-label="Filtrar por categoria">
      <button
        v-for="cat in categories"
        :key="cat"
        class="catalog-chip"
        :class="{ 'catalog-chip--active': activeCategory === cat }"
        @click="store.setCategory(cat)"
        :aria-pressed="activeCategory === cat"
      >
        {{ cat }}
      </button>
    </div>
  </div>

  <!-- Loading skeleton -->
  <div v-if="loadingCatalog" class="catalog-skeleton">
    <div v-for="i in 6" :key="i" class="catalog-skeleton-card" />
  </div>

  <!-- Error -->
  <div v-else-if="error" class="catalog-error">
    {{ error }}
  </div>

  <!-- Empty state (filtered) -->
  <div v-else-if="!filteredTools.length" class="catalog-empty">
    <div class="catalog-empty-num">—</div>
    <p class="catalog-empty-msg">"Sin herramientas que coincidan con el filtro. Cambia categoria o limpia el filtro."</p>
    <button v-if="hasFilters" class="catalog-empty-cta" @click="store.clearFilters()">LIMPIAR FILTROS →</button>
  </div>

  <!-- Grid -->
  <div v-else class="catalog-grid">
    <AdminToolCard
      v-for="tool in filteredTools"
      :key="tool.id"
      :tool="tool"
      :is-superadmin="isSuperadmin"
      @run="emit('run', $event)"
    />
  </div>
</template>

<style scoped>
.catalog-filters {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 16px;
}
@media (min-width: 768px) {
  .catalog-filters {
    flex-direction: row;
    align-items: center;
    flex-wrap: wrap;
  }
}

.catalog-search-wrap {
  position: relative;
  flex: 1;
  min-width: 180px;
  max-width: 280px;
}
.catalog-search-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  width: 14px;
  height: 14px;
  color: var(--color-wc-text-tertiary);
  pointer-events: none;
}
.catalog-search {
  width: 100%;
  height: 36px;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--color-wc-border);
  border-radius: 8px;
  padding: 0 12px 0 30px;
  font-family: var(--font-sans);
  font-size: 12px;
  color: var(--color-wc-text);
  outline: none;
  transition: border-color 0.12s;
}
.catalog-search::placeholder { color: var(--color-wc-text-tertiary); }
.catalog-search:focus { border-color: var(--color-wc-border-2); }

.catalog-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.catalog-chip {
  height: 28px;
  padding: 0 12px;
  border-radius: 6px;
  font-family: var(--font-mono);
  font-size: 9px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  border: 1px solid var(--color-wc-border);
  background: transparent;
  color: var(--color-wc-text-secondary);
  cursor: pointer;
  transition: background 0.12s, border-color 0.12s, color 0.12s;
}
.catalog-chip:hover {
  border-color: var(--color-wc-border-2);
  color: var(--color-wc-text);
}
.catalog-chip--active {
  background: var(--color-wc-red-soft);
  border-color: var(--color-wc-accent);
  color: var(--color-wc-red-text);
}

/* Skeleton */
.catalog-skeleton {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}
@media (min-width: 768px) { .catalog-skeleton { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 1280px) { .catalog-skeleton { grid-template-columns: repeat(4, 1fr); } }
.catalog-skeleton-card {
  height: 180px;
  border-radius: 14px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-tertiary);
  animation: page-pulse 1.5s ease-in-out infinite;
}
@keyframes page-pulse {
  0%, 100% { opacity: 0.6; }
  50%       { opacity: 0.9; }
}

/* Error */
.catalog-error {
  padding: 20px;
  border-radius: 10px;
  background: var(--color-wc-red-soft);
  border: 1px solid rgba(220,38,38,0.2);
  font-family: var(--font-sans);
  font-size: 13px;
  color: var(--color-wc-red-text);
}

/* Empty state */
.catalog-empty {
  padding: 32px 8px 24px;
  text-align: center;
}
.catalog-empty-num {
  font-family: var(--font-display);
  font-size: 56px;
  color: var(--color-wc-bg-tertiary);
  letter-spacing: 0.1em;
  line-height: 1;
  margin-bottom: 12px;
  user-select: none;
}
.catalog-empty-msg {
  font-family: var(--font-editorial);
  font-style: italic;
  font-size: 12px;
  color: var(--color-wc-text-tertiary);
  line-height: 1.55;
  margin: 0 0 16px;
  text-wrap: balance;
}
.catalog-empty-cta {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-family: var(--font-mono);
  font-size: 9px;
  letter-spacing: 0.22em;
  color: var(--color-wc-text-secondary);
  text-transform: uppercase;
  border: none;
  background: none;
  border-bottom: 1px solid var(--color-wc-border);
  padding-bottom: 4px;
  cursor: pointer;
  transition: color 0.12s, border-color 0.12s;
}
.catalog-empty-cta:hover {
  color: var(--color-wc-text);
  border-bottom-color: var(--color-wc-accent);
}

/* Grid */
.catalog-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}
@media (min-width: 768px) { .catalog-grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 1280px) { .catalog-grid { grid-template-columns: repeat(4, 1fr); } }

@media (prefers-reduced-motion: reduce) {
  .catalog-skeleton-card { animation: none !important; }
}
</style>
