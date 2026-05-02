<script setup>
import { computed } from 'vue';
import { useAdminInscriptionsStore } from '@/stores/adminInscriptions';

const store = useAdminInscriptionsStore();

const PLANS = [
    { value: '',           label: 'TODOS' },
    { value: 'esencial',   label: 'ESENCIAL' },
    { value: 'metodo',     label: 'MÉTODO' },
    { value: 'elite',      label: 'ELITE' },
    { value: 'rise',       label: 'RISE' },
    { value: 'presencial', label: 'PRESENCIAL' },
];

const hasFilters = computed(() =>
    store.filters.plan !== '' || store.filters.search !== ''
);
</script>

<template>
    <div class="insc-filters">
        <div class="insc-search-wrap">
            <svg class="insc-search-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input
                :value="store.filters.search"
                @input="store.setFilters({ search: $event.target.value })"
                type="search"
                placeholder="Buscar lead..."
                class="insc-search"
                aria-label="Buscar inscripción"
            />
        </div>

        <div class="insc-pills" role="group" aria-label="Filtrar por plan">
            <button
                v-for="plan in PLANS"
                :key="plan.value"
                @click="store.setFilters({ plan: plan.value })"
                :class="['insc-pill', { 'insc-pill--active': store.filters.plan === plan.value }]"
                :aria-pressed="store.filters.plan === plan.value"
            >
                {{ plan.label }}
            </button>
        </div>

        <Transition name="fade">
            <button v-if="hasFilters" @click="store.clearFilters" class="insc-clear" aria-label="Limpiar filtros">
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                LIMPIAR
            </button>
        </Transition>
    </div>
</template>

<style scoped>
.insc-filters {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    flex: 1;
}
.insc-search-wrap {
    position: relative;
    min-width: 160px;
    max-width: 260px;
    flex: 1;
}
.insc-search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--c-text-3);
    pointer-events: none;
}
.insc-search {
    width: 100%;
    height: 40px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm, 12px);
    padding: 0 12px 0 30px;
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--c-text);
    outline: none;
    transition: border-color 0.15s var(--ease-out);
}
.insc-search:focus { border-color: rgba(255,255,255,0.16); }
.insc-search::placeholder { color: var(--c-text-3); }
.insc-pills {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}
.insc-pill {
    height: var(--tap-comfort, 48px);
    padding: 0 12px;
    border-radius: var(--r-pill, 999px);
    border: 1px solid var(--c-border);
    background: transparent;
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1.2px;
    color: var(--c-text-3);
    cursor: pointer;
    transition: all 0.15s var(--ease-out);
}
.insc-pill:hover {
    border-color: rgba(255,255,255,0.16);
    color: var(--c-text-2);
}
.insc-pill--active {
    background: var(--c-accent-dim);
    border-color: var(--c-accent);
    color: var(--c-text);
}
.insc-clear {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 1.2px;
    color: var(--c-text-3);
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
    min-height: var(--tap-comfort, 48px);
    transition: color 0.15s var(--ease-out);
}
.insc-clear:hover { color: var(--c-accent); }

.fade-enter-active, .fade-leave-active { transition: opacity 0.15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .insc-pill, .insc-search, .insc-clear { transition: none !important; }
    .fade-enter-active, .fade-leave-active { transition: none !important; }
}
</style>
