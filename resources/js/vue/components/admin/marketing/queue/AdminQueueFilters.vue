<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    filters: { type: Object, required: true },
    rowsCount: { type: Number, default: 0 },
});

const emit = defineEmits(['update', 'reset']);

const localSearch = ref(props.filters.search || '');
const localCoach = ref(props.filters.coach_id || '');
const localYear = ref(props.filters.iso_year || '');
const localWeek = ref(props.filters.iso_week || '');
const includeDrafts = ref(!!props.filters.include_drafts);

let searchTimer = null;
let coachTimer = null;
let yearTimer = null;
let weekTimer = null;

watch(() => props.filters.search, (v) => { localSearch.value = v || ''; });
watch(() => props.filters.coach_id, (v) => { localCoach.value = v || ''; });
watch(() => props.filters.iso_year, (v) => { localYear.value = v || ''; });
watch(() => props.filters.iso_week, (v) => { localWeek.value = v || ''; });
watch(() => props.filters.include_drafts, (v) => { includeDrafts.value = !!v; });

function debouncedEmit(key, value, ms = 300) {
    const timers = { search: 'searchTimer', coach_id: 'coachTimer', iso_year: 'yearTimer', iso_week: 'weekTimer' };
    const target = { searchTimer, coachTimer, yearTimer, weekTimer };
    clearTimeout(target[timers[key]]);
    if (key === 'search') searchTimer = setTimeout(() => emit('update', { key, value }), ms);
    if (key === 'coach_id') coachTimer = setTimeout(() => emit('update', { key, value }), ms);
    if (key === 'iso_year') yearTimer = setTimeout(() => emit('update', { key, value }), ms);
    if (key === 'iso_week') weekTimer = setTimeout(() => emit('update', { key, value }), ms);
}

function onToggleDrafts() {
    includeDrafts.value = !includeDrafts.value;
    emit('update', { key: 'include_drafts', value: includeDrafts.value });
}

function onReset() {
    localSearch.value = '';
    localCoach.value = '';
    localYear.value = '';
    localWeek.value = '';
    includeDrafts.value = false;
    emit('reset');
}

function hasAnyFilter() {
    return localSearch.value || localCoach.value || localYear.value || localWeek.value || includeDrafts.value;
}
</script>

<template>
    <div class="queue-filters" role="region" aria-label="Filtros de la cola">
        <div class="filters-search-row">
            <label class="filters-search-wrap">
                <svg class="filters-search-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.6" />
                    <line x1="16.5" y1="16.5" x2="20.5" y2="20.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                </svg>
                <input
                    v-model="localSearch"
                    @input="debouncedEmit('search', localSearch)"
                    type="text"
                    class="filters-search-input"
                    placeholder="Buscar por coach"
                    aria-label="Buscar drops por nombre de coach"
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
                    @input="debouncedEmit('coach_id', localCoach)"
                    type="number"
                    inputmode="numeric"
                    class="filter-input"
                    placeholder="ej. 12"
                />
            </label>
            <label class="filter-field">
                <span class="filter-label">ISO year</span>
                <input
                    v-model="localYear"
                    @input="debouncedEmit('iso_year', localYear)"
                    type="number"
                    inputmode="numeric"
                    class="filter-input"
                    placeholder="2026"
                />
            </label>
            <label class="filter-field">
                <span class="filter-label">ISO week</span>
                <input
                    v-model="localWeek"
                    @input="debouncedEmit('iso_week', localWeek)"
                    type="number"
                    inputmode="numeric"
                    class="filter-input"
                    placeholder="17"
                    min="1"
                    max="53"
                />
            </label>
            <button
                type="button"
                class="filter-pill"
                :class="{ 'filter-pill--active': includeDrafts }"
                @click="onToggleDrafts"
                :aria-pressed="includeDrafts"
            >
                <span class="filter-pill-dot" aria-hidden="true"></span>
                Mostrar drafts
            </button>
        </div>

        <p v-if="rowsCount > 0" class="filters-count">
            {{ rowsCount }} {{ rowsCount === 1 ? 'drop visible' : 'drops visibles' }}
        </p>
    </div>
</template>

<style scoped>
.queue-filters {
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
}
@media (min-width: 768px) {
    .filters-grid { grid-template-columns: repeat(4, 1fr); }
}

.filter-field { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.filter-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.filter-input {
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
.filter-input:focus { outline: none; border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.16)); }

.filter-pill {
    height: 32px;
    align-self: end;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: transparent;
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    padding: 0 12px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.filter-pill-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--color-wc-text-tertiary);
}
.filter-pill--active {
    background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.10));
    border-color: var(--color-wc-accent, #DC2626);
    color: var(--color-wc-red-text, #F87171);
}
.filter-pill--active .filter-pill-dot { background: var(--color-wc-red-text, #F87171); }

.filters-count {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.16em;
    color: var(--color-wc-text-tertiary);
    margin: 0;
    text-transform: uppercase;
}
</style>
