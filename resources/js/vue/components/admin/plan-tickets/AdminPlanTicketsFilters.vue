<script setup>
import { ref, watch } from 'vue';

const PLAN_TYPES = [
    { value: '',           label: 'TODOS LOS PLANES' },
    { value: 'esencial',   label: 'ESENCIAL' },
    { value: 'metodo',     label: 'METODO' },
    { value: 'elite',      label: 'ELITE' },
    { value: 'rise',       label: 'RISE' },
    { value: 'presencial', label: 'PRESENCIAL' },
    { value: 'trial',      label: 'TRIAL' },
];

const STATUSES = [
    { value: '',            label: 'TODOS' },
    { value: 'pendiente',   label: 'PENDIENTES' },
    { value: 'en_revision', label: 'EN REVISION' },
    { value: 'completado',  label: 'APROBADOS' },
    { value: 'rechazado',   label: 'RECHAZADOS' },
];

const props = defineProps({
    filters: { type: Object, required: true },
    rowsCount: { type: Number, default: 0 },
});

const emit = defineEmits(['update', 'reset']);

const localSearch = ref(props.filters.search || '');
const localCoach = ref(props.filters.coach_id || '');
const localPlan = ref(props.filters.plan_type || '');
const localStatus = ref(props.filters.status || '');

let searchTimer = null;
let coachTimer = null;

watch(() => props.filters.search,    (v) => { localSearch.value = v || ''; });
watch(() => props.filters.coach_id,  (v) => { localCoach.value = v || ''; });
watch(() => props.filters.plan_type, (v) => { localPlan.value = v || ''; });
watch(() => props.filters.status,    (v) => { localStatus.value = v || ''; });

function debouncedSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => emit('update', { key: 'search', value: localSearch.value }), 300);
}
function debouncedCoach() {
    clearTimeout(coachTimer);
    coachTimer = setTimeout(() => emit('update', { key: 'coach_id', value: localCoach.value }), 300);
}
function onPlan() {
    emit('update', { key: 'plan_type', value: localPlan.value });
}
function onStatus() {
    emit('update', { key: 'status', value: localStatus.value });
}

function onReset() {
    localSearch.value = '';
    localCoach.value = '';
    localPlan.value = '';
    localStatus.value = '';
    emit('reset');
}

function hasAnyFilter() {
    return localSearch.value || localCoach.value || localPlan.value || localStatus.value;
}
</script>

<template>
    <div class="pt-filters" role="region" aria-label="Filtros de tickets de planes">
        <div class="filters-search-row">
            <label class="filters-search-wrap">
                <svg class="filters-search-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.6" />
                    <line x1="16.5" y1="16.5" x2="20.5" y2="20.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                </svg>
                <input
                    v-model="localSearch"
                    @input="debouncedSearch"
                    type="text"
                    class="filters-search-input"
                    placeholder="Buscar por cliente o coach"
                    aria-label="Buscar tickets por nombre"
                />
            </label>
            <button
                v-if="hasAnyFilter()"
                type="button"
                class="filters-reset"
                @click="onReset"
                aria-label="Limpiar todos los filtros"
            >
                Limpiar
            </button>
        </div>

        <div class="filters-grid">
            <label class="filter-field">
                <span class="filter-label">Coach ID</span>
                <input
                    v-model="localCoach"
                    @input="debouncedCoach"
                    type="number"
                    inputmode="numeric"
                    class="filter-input"
                    placeholder="ej. 12"
                />
            </label>
            <label class="filter-field">
                <span class="filter-label">Plan</span>
                <select v-model="localPlan" @change="onPlan" class="filter-select">
                    <option v-for="p in PLAN_TYPES" :key="p.value" :value="p.value">{{ p.label }}</option>
                </select>
            </label>
            <label class="filter-field">
                <span class="filter-label">Estado</span>
                <select v-model="localStatus" @change="onStatus" class="filter-select">
                    <option v-for="s in STATUSES" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
            </label>
            <p v-if="rowsCount > 0" class="filters-count" aria-live="polite">
                {{ rowsCount }} {{ rowsCount === 1 ? 'ticket visible' : 'tickets visibles' }}
            </p>
        </div>
    </div>
</template>

<style scoped>
.pt-filters {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 14px 14px 12px;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
}

.filters-search-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.filters-search-wrap {
    flex: 1;
    position: relative;
    display: flex;
    align-items: center;
}
.filters-search-icon {
    position: absolute;
    left: 12px;
    width: 14px; height: 14px;
    color: var(--color-wc-text-tertiary);
}
.filters-search-input {
    width: 100%;
    height: 36px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text);
    font-family: var(--font-sans);
    font-size: 13px;
    padding: 0 12px 0 34px;
    transition: border-color 0.15s var(--ease-out, ease);
}
.filters-search-input::placeholder { color: var(--color-wc-text-tertiary); }
.filters-search-input:focus {
    outline: none;
    border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.16));
}

.filters-reset {
    height: 36px;
    padding: 0 14px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: transparent;
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    cursor: pointer;
    transition: border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.filters-reset:hover { border-color: var(--color-wc-accent, #DC2626); color: var(--color-wc-text); }

.filters-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    align-items: end;
}
@media (min-width: 768px) {
    .filters-grid { grid-template-columns: 1fr 1fr 1fr auto; }
}

.filter-field { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.filter-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.filter-input,
.filter-select {
    height: 32px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: rgba(255, 255, 255, 0.03);
    color: var(--color-wc-text);
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-feature-settings: 'tnum' 1;
    font-size: 13px;
    padding: 0 10px;
    transition: border-color 0.15s var(--ease-out, ease);
}
.filter-input::placeholder { color: var(--color-wc-text-tertiary); opacity: 0.7; }
.filter-input:focus,
.filter-select:focus { outline: none; border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.16)); }
.filter-select {
    appearance: none;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='rgb(140,140,140)' stroke-width='1.6'><path d='M6 9l6 6 6-6'/></svg>");
    background-repeat: no-repeat;
    background-position: right 8px center;
    background-size: 14px;
    padding-right: 28px;
    font-family: var(--font-mono, monospace);
    font-size: 10px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.filters-count {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.16em;
    color: var(--color-wc-text-tertiary);
    margin: 0;
    text-transform: uppercase;
    align-self: end;
    padding-bottom: 6px;
    grid-column: 1 / -1;
}
@media (min-width: 768px) {
    .filters-count { grid-column: auto; padding-bottom: 8px; white-space: nowrap; }
}
</style>
