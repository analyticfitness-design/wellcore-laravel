<script setup>
import { ref, watch, computed } from 'vue';
import { useAdminFormsStore } from '@/stores/adminForms';
import { formatDateLong } from '@/composables/useFormat';
import { useApi } from '@/composables/useApi';

const store = useAdminFormsStore();

const localSearch = ref(store.responses.search);
const localDateFrom = ref(store.responses.dateFrom);
const localDateTo = ref(store.responses.dateTo);

let searchTimeout = null;

watch(localSearch, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        store.responses.search = val;
        store.fetchResponses(1);
    }, 350);
});

function applyDateFilter() {
    store.responses.dateFrom = localDateFrom.value;
    store.responses.dateTo = localDateTo.value;
    store.fetchResponses(1);
}

function clearFilters() {
    localSearch.value = '';
    localDateFrom.value = '';
    localDateTo.value = '';
    store.responses.search = '';
    store.responses.dateFrom = '';
    store.responses.dateTo = '';
    store.fetchResponses(1);
}

const hasFilters = computed(() =>
    localSearch.value || localDateFrom.value || localDateTo.value
);

async function exportCsv() {
    const url = store.exportCsvUrl();
    if (!url) return;
    try {
        const api = useApi();
        const response = await api.get(url, { responseType: 'blob' });
        const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        const form = store.selectedForm;
        link.download = `formulario-${form?.area}-${form?.slug}-${new Date().toISOString().slice(0,10)}.csv`;
        link.click();
        URL.revokeObjectURL(link.href);
    } catch {
        // silently ignore export errors — user can retry
    }
}

const isEmpty = computed(() => !store.responses.loading && store.responses.data.length === 0);
const isFirstLoad = computed(() => store.responses.loading && store.responses.data.length === 0);

const meta = computed(() => store.responses.meta);
</script>

<template>
  <section class="responses" aria-label="Historial de submissions">
    <!-- Header -->
    <div class="responses__header">
      <div class="responses__title-row">
        <h3 class="responses__title">HISTORIAL</h3>
        <button
          v-if="store.selectedForm?.has_submissions"
          class="responses__export"
          :disabled="store.responses.loading"
          aria-label="Exportar CSV"
          @click="exportCsv"
        >
          <svg aria-hidden="true" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
          </svg>
          EXPORTAR CSV
        </button>
      </div>

      <!-- Filters -->
      <div class="responses__filters">
        <div class="responses__search-wrap">
          <svg aria-hidden="true" class="responses__search-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input
            v-model="localSearch"
            type="search"
            placeholder="Buscar cliente..."
            class="responses__search-input"
            aria-label="Buscar en historial"
          />
        </div>
        <input
          v-model="localDateFrom"
          type="date"
          class="responses__date-input"
          aria-label="Fecha desde"
          @change="applyDateFilter"
        />
        <input
          v-model="localDateTo"
          type="date"
          class="responses__date-input"
          aria-label="Fecha hasta"
          @change="applyDateFilter"
        />
        <button
          v-if="hasFilters"
          class="responses__clear"
          aria-label="Limpiar filtros"
          @click="clearFilters"
        >×</button>
      </div>
    </div>

    <!-- Loading skeleton (first load) -->
    <div v-if="isFirstLoad" class="responses__skeleton" aria-busy="true">
      <div v-for="i in 5" :key="i" class="responses__skeleton-row"></div>
    </div>

    <!-- Empty state -->
    <div v-else-if="isEmpty" class="responses__empty">
      <div class="empty-num" aria-hidden="true">—</div>
      <p class="empty-msg">"El sistema funciona cuando el coach se compromete con el cliente."</p>
      <span class="empty-hint">Sin submissions para los filtros actuales.</span>
    </div>

    <!-- Table -->
    <div v-else class="responses__table-wrap">
      <table class="responses__table" aria-label="Submissions del formulario">
        <thead>
          <tr>
            <th scope="col">CLIENTE</th>
            <th scope="col">FECHA</th>
            <th scope="col" class="responses__th-summary">RESUMEN</th>
          </tr>
        </thead>
        <tbody :class="{ 'is-refreshing': store.responses.loading }">
          <tr v-for="row in store.responses.data" :key="row.id ?? row.date + row.client_name">
            <td class="responses__td-client">{{ row.client_name ?? '—' }}</td>
            <td class="responses__td-date">{{ formatDateLong(row.date) }}</td>
            <td class="responses__td-summary">{{ row.summary }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="meta.last_page > 1" class="responses__pagination">
      <button
        class="responses__page-btn"
        :disabled="meta.page <= 1 || store.responses.loading"
        aria-label="Página anterior"
        @click="store.fetchResponses(meta.page - 1)"
      >←</button>
      <span class="responses__page-info">
        {{ meta.page }} / {{ meta.last_page }}
        <span class="responses__total">({{ meta.total }} total)</span>
      </span>
      <button
        class="responses__page-btn"
        :disabled="meta.page >= meta.last_page || store.responses.loading"
        aria-label="Página siguiente"
        @click="store.fetchResponses(meta.page + 1)"
      >→</button>
    </div>
  </section>
</template>

<style scoped>
.responses {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.7);
    overflow: hidden;
}

.responses__header {
    padding: 14px 16px 12px;
    border-bottom: 1px solid var(--c-border);
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.responses__title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.responses__title {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
    margin: 0;
}

.responses__export {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid var(--c-border);
    background: transparent;
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-2);
    cursor: pointer;
    transition: color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
}
.responses__export:hover {
    color: #34D399;
    border-color: #34D399;
}
.responses__export:disabled { opacity: 0.4; cursor: not-allowed; }

.responses__filters {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.responses__search-wrap {
    position: relative;
    flex: 1;
    min-width: 120px;
}
.responses__search-icon {
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--c-text-3);
    pointer-events: none;
}
.responses__search-input {
    width: 100%;
    height: 32px;
    padding: 0 8px 0 26px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255, 255, 255, 0.03);
    font-family: var(--font-sans);
    font-size: 11px;
    color: var(--c-text);
    outline: none;
    transition: border-color 0.15s var(--ease-out);
}
.responses__search-input::placeholder { color: var(--c-text-3); }
.responses__search-input:focus { border-color: rgba(255,255,255,0.12); }

.responses__date-input {
    height: 32px;
    padding: 0 8px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255, 255, 255, 0.03);
    font-family: var(--font-display);
    font-size: 10px;
    color: var(--c-text-2);
    outline: none;
    transition: border-color 0.15s var(--ease-out);
    color-scheme: dark;
}
.responses__date-input:focus { border-color: rgba(255,255,255,0.12); }

.responses__clear {
    width: 32px;
    height: 32px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: transparent;
    color: var(--c-text-3);
    font-size: 16px;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.15s var(--ease-out);
}
.responses__clear:hover { color: var(--c-text); }

/* Skeleton */
.responses__skeleton {
    padding: 12px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.responses__skeleton-row {
    height: 32px;
    border-radius: var(--r-sm, 12px);
    background: var(--c-surface-2);
    animation: sk-pulse 1.5s ease-in-out infinite;
}
@keyframes sk-pulse {
    0%, 100% { opacity: 0.6; }
    50%       { opacity: 0.9; }
}

/* Empty */
.responses__empty {
    padding: 28px 16px 20px;
    text-align: center;
}
.empty-num {
    font-family: var(--font-display);
    font-size: 40px;
    color: var(--c-surface-2);
    letter-spacing: 0.1em;
    user-select: none;
    margin-bottom: 8px;
}
.empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 11px;
    color: var(--c-text-3);
    line-height: 1.55;
    margin: 0 0 6px;
    text-wrap: balance;
}
.empty-hint {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
}

/* Table */
.responses__table-wrap {
    overflow-x: auto;
}
.responses__table {
    width: 100%;
    border-collapse: collapse;
    font-family: var(--font-sans);
    font-size: 12px;
}
.responses__table th {
    padding: 10px 16px 8px;
    text-align: left;
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
    font-weight: 400;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    white-space: nowrap;
}
.responses__table td {
    padding: 10px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    color: var(--c-text-2);
    vertical-align: top;
}
.responses__table tr:last-child td { border-bottom: none; }
.responses__table tbody.is-refreshing { opacity: 0.6; }

.responses__td-client {
    color: var(--c-text) !important;
    font-weight: 500;
    white-space: nowrap;
}
.responses__td-date {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 0.08em;
    color: var(--c-text-3) !important;
    white-space: nowrap;
}
.responses__th-summary,
.responses__td-summary {
    width: 100%;
}

/* Pagination */
.responses__pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 12px 16px;
    border-top: 1px solid var(--c-border);
}
.responses__page-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: 1px solid var(--c-border);
    background: transparent;
    color: var(--c-text-2);
    font-family: var(--font-display);
    font-size: 11px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
}
.responses__page-btn:hover:not(:disabled) {
    color: var(--c-text);
    border-color: rgba(255,255,255,0.12);
}
.responses__page-btn:disabled { opacity: 0.3; cursor: not-allowed; }

.responses__page-info {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 0.1em;
    color: var(--c-text-2);
}
.responses__total {
    color: var(--c-text-3);
    font-size: 9px;
}

@media (prefers-reduced-motion: reduce) {
    .responses__skeleton-row { animation: none; opacity: 0.7; }
    .responses__export, .responses__search-input, .responses__clear, .responses__page-btn { transition: none; }
}
</style>
