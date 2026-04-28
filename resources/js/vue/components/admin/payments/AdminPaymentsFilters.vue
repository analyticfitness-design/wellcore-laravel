<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';
import { useAdminPaymentsStore } from '../../../stores/adminPayments';

const store = useAdminPaymentsStore();

const STATUS_OPTIONS = [
    { value: '', label: 'TODOS' },
    { value: 'approved', label: 'APROBADOS' },
    { value: 'pending', label: 'PENDIENTES' },
    { value: 'declined', label: 'RECHAZADOS' },
    { value: 'voided', label: 'ANULADOS' },
    { value: 'error', label: 'ERROR' },
];

const METHOD_OPTIONS = [
    { value: '', label: 'TODOS' },
    { value: 'NEQUI', label: 'NEQUI' },
    { value: 'PSE', label: 'PSE' },
    { value: 'CARD', label: 'TARJETA' },
    { value: 'BANCOLOMBIA_TRANSFER', label: 'BANCOLOMBIA' },
    { value: 'MANUAL', label: 'MANUAL' },
];

const RANGE_OPTIONS = [
    { id: 'all', label: 'SIEMPRE' },
    { id: 'today', label: 'HOY' },
    { id: 'week', label: 'SEMANA' },
    { id: 'month', label: 'MES' },
    { id: 'custom', label: 'CUSTOM' },
];

const localSearch = ref(store.filters.search);
const customOpen = ref(false);

let searchTimer = null;
watch(localSearch, (val) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        if (val !== store.filters.search) store.setSearch(val);
    }, 250);
});

onBeforeUnmount(() => clearTimeout(searchTimer));

function selectStatus(value) {
    if (store.filters.status !== value) store.setFilters({ status: value });
}

function selectMethod(value) {
    if (store.filters.method !== value) store.setFilters({ method: value });
}

function activeRange() {
    const f = store.filters;
    if (!f.dateFrom && !f.dateTo) return 'all';
    if (customOpen.value) return 'custom';
    const today = isoDate(new Date());
    if (f.dateFrom === today && f.dateTo === today) return 'today';
    const weekStart = isoDate(daysAgo(6));
    if (f.dateFrom === weekStart && f.dateTo === today) return 'week';
    const monthStart = isoDate(firstOfMonth());
    if (f.dateFrom === monthStart && f.dateTo === today) return 'month';
    return 'custom';
}

function selectRange(id) {
    customOpen.value = false;
    if (id === 'all') {
        store.setFilters({ dateFrom: '', dateTo: '' });
        return;
    }
    if (id === 'today') {
        const t = isoDate(new Date());
        store.setFilters({ dateFrom: t, dateTo: t });
        return;
    }
    if (id === 'week') {
        store.setFilters({
            dateFrom: isoDate(daysAgo(6)),
            dateTo: isoDate(new Date()),
        });
        return;
    }
    if (id === 'month') {
        store.setFilters({
            dateFrom: isoDate(firstOfMonth()),
            dateTo: isoDate(new Date()),
        });
        return;
    }
    customOpen.value = true;
}

function applyCustom(field, value) {
    store.setFilters({ [field]: value });
}

function isoDate(d) {
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
}
function daysAgo(n) {
    const d = new Date();
    d.setDate(d.getDate() - n);
    return d;
}
function firstOfMonth() {
    const d = new Date();
    return new Date(d.getFullYear(), d.getMonth(), 1);
}

function clearAll() {
    localSearch.value = '';
    customOpen.value = false;
    store.clearFilters();
}
</script>

<template>
  <section class="filters-card">
    <header class="filters-head">
      <span class="filters-eyebrow">FILTROS</span>
      <button v-if="store.hasActiveFilters" class="filters-clear" @click="clearAll">
        LIMPIAR TODO
      </button>
    </header>

    <div class="filters-row">
      <label class="filters-label">ESTADO</label>
      <div class="filters-chips">
        <button
          v-for="opt in STATUS_OPTIONS"
          :key="`s-${opt.value}`"
          class="chip"
          :class="{ 'chip--active': store.filters.status === opt.value }"
          @click="selectStatus(opt.value)"
        >{{ opt.label }}</button>
      </div>
    </div>

    <div class="filters-row">
      <label class="filters-label">METODO</label>
      <div class="filters-chips">
        <button
          v-for="opt in METHOD_OPTIONS"
          :key="`m-${opt.value}`"
          class="chip"
          :class="{ 'chip--active': store.filters.method === opt.value }"
          @click="selectMethod(opt.value)"
        >{{ opt.label }}</button>
      </div>
    </div>

    <div class="filters-row">
      <label class="filters-label">FECHA</label>
      <div class="filters-chips">
        <button
          v-for="r in RANGE_OPTIONS"
          :key="`r-${r.id}`"
          class="chip"
          :class="{ 'chip--active': activeRange() === r.id }"
          @click="selectRange(r.id)"
        >{{ r.label }}</button>
      </div>
    </div>

    <div v-if="customOpen || (store.filters.dateFrom && activeRange() === 'custom')" class="filters-custom">
      <div class="custom-field">
        <label class="form-label">DESDE</label>
        <input
          type="date"
          class="form-input"
          :value="store.filters.dateFrom"
          @change="applyCustom('dateFrom', $event.target.value)"
        />
      </div>
      <div class="custom-field">
        <label class="form-label">HASTA</label>
        <input
          type="date"
          class="form-input"
          :value="store.filters.dateTo"
          @change="applyCustom('dateTo', $event.target.value)"
        />
      </div>
    </div>

    <div class="filters-row filters-row--search">
      <div class="search-wrap">
        <svg class="search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
          <circle cx="11" cy="11" r="7" />
          <path d="m20 20-3.5-3.5" stroke-linecap="round" />
        </svg>
        <input
          v-model="localSearch"
          type="text"
          class="search-input"
          placeholder="Cliente, email o referencia"
          aria-label="Buscar pago"
        />
      </div>
    </div>
  </section>
</template>

<style scoped>
.filters-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 16px 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
@media (min-width: 1024px) {
    .filters-card { padding: 18px; gap: 14px; }
}

.filters-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.filters-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.filters-clear {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-red-text, #F87171);
    background: transparent;
    border: none;
    border-bottom: 1px solid transparent;
    cursor: pointer;
    padding: 2px 0;
    transition: border-color 0.15s var(--ease-out, ease);
}
.filters-clear:hover {
    border-bottom-color: var(--color-wc-red-text, #F87171);
}

.filters-row {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
@media (min-width: 768px) {
    .filters-row {
        flex-direction: row;
        align-items: center;
        gap: 10px;
    }
}

.filters-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    flex-shrink: 0;
    width: 70px;
}

.filters-chips {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    min-width: 0;
}

.chip {
    display: inline-flex;
    align-items: center;
    height: 28px;
    padding: 0 10px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text-tertiary);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease),
                border-color 0.15s var(--ease-out, ease),
                color 0.15s var(--ease-out, ease);
}
.chip:hover { border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.12)); color: var(--color-wc-text-secondary); }
.chip--active {
    background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.1));
    border-color: rgba(220, 38, 38, 0.4);
    color: var(--color-wc-red-text, #F87171);
}

.filters-custom {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.custom-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 140px;
}
.form-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.form-input {
    height: 36px;
    padding: 0 10px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text);
    font-family: var(--font-sans);
    font-size: 13px;
    transition: border-color 0.15s var(--ease-out, ease);
    color-scheme: dark;
}
.form-input:focus {
    outline: none;
    border-color: var(--color-wc-accent, #DC2626);
    background: rgba(255, 255, 255, 0.05);
}

.filters-row--search { width: 100%; }
.search-wrap {
    position: relative;
    width: 100%;
    flex: 1;
}
.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--color-wc-text-tertiary);
    pointer-events: none;
}
.search-input {
    width: 100%;
    height: 36px;
    padding: 0 12px 0 34px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text);
    font-family: var(--font-sans);
    font-size: 13px;
    transition: border-color 0.15s var(--ease-out, ease);
}
.search-input:focus {
    outline: none;
    border-color: var(--color-wc-accent, #DC2626);
    background: rgba(255, 255, 255, 0.05);
}
.search-input::placeholder { color: var(--color-wc-text-tertiary); }
</style>
