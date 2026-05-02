<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import AdminQueueColumn from './AdminQueueColumn.vue';
import AdminDropCard from './AdminDropCard.vue';

const props = defineProps({
    rowsByColumn: { type: Object, required: true },
    flashRowId: { type: [Number, String, null], default: null },
});

const emit = defineEmits(['review', 'drop']);

const COLUMNS = [
    { key: 'in_review', label: 'En revision',  empty: '"Sin drops en revision. Bienvenida la calma operativa."', droppable: false, accent: 'in_review' },
    { key: 'approved',  label: 'Aprobados',    empty: '"Sin aprobaciones recientes. Cada decision deja huella."', droppable: false, accent: 'approved' },
    { key: 'ready',     label: 'Listos',       empty: '"Nada listo todavia. Lo que se aprueba hoy se publica manana."', droppable: false, accent: 'ready' },
    { key: 'published', label: 'En proceso',   empty: '"Sin piezas en circulacion. La publicacion es la prueba final."', droppable: false, accent: 'published' },
    { key: 'archived',  label: 'Archivados',   empty: '"Archivo limpio. La memoria selectiva es disciplina."', droppable: false, accent: 'archived' },
];

const activeMobileTab = ref('in_review');
const tablistRef = ref(null);

function onReview(id) {
    emit('review', id);
}

function setMobileTab(key) {
    activeMobileTab.value = key;
    const el = tablistRef.value?.querySelector(`[data-tab="${key}"]`);
    if (el && typeof el.scrollIntoView === 'function') {
        try { el.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' }); } catch {}
    }
}

const totalVisible = computed(() => {
    return COLUMNS.reduce((sum, c) => sum + (props.rowsByColumn[c.key]?.length ?? 0), 0);
});

const isEmpty = computed(() => totalVisible.value === 0);

let scrollObserver = null;
const panelsRef = ref(null);

onMounted(() => {
    if (typeof IntersectionObserver === 'undefined' || !panelsRef.value) return;
    scrollObserver = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (entry.isIntersecting && entry.intersectionRatio > 0.6) {
                const key = entry.target.getAttribute('data-panel');
                if (key) activeMobileTab.value = key;
            }
        }
    }, { root: panelsRef.value, threshold: [0.6] });

    panelsRef.value.querySelectorAll('[data-panel]').forEach((el) => scrollObserver.observe(el));
});

onBeforeUnmount(() => {
    scrollObserver?.disconnect();
    scrollObserver = null;
});
</script>

<template>
    <div class="queue-board" :class="{ 'queue-board--empty': isEmpty }">
        <div v-if="isEmpty" class="queue-board-empty">
            <div class="queue-board-empty-num" aria-hidden="true">—</div>
            <p class="queue-board-empty-msg">
                "Cola limpia. Todos los drops estan programados o publicados.
                Excelente trabajo del equipo de coaches."
            </p>
            <span class="queue-board-empty-cta">VER PIEZAS PUBLICADAS <span aria-hidden="true">→</span></span>
        </div>

        <template v-else>
            <div ref="tablistRef" class="queue-tablist lg:hidden" role="tablist">
                <button
                    v-for="col in COLUMNS"
                    :key="col.key"
                    type="button"
                    role="tab"
                    :aria-selected="activeMobileTab === col.key"
                    :data-tab="col.key"
                    class="queue-tab"
                    :class="{ 'queue-tab--active': activeMobileTab === col.key }"
                    @click="setMobileTab(col.key)"
                >
                    <span>{{ col.label }}</span>
                    <span class="queue-tab-count">{{ rowsByColumn[col.key]?.length ?? 0 }}</span>
                </button>
            </div>

            <div ref="panelsRef" class="queue-panels">
                <AdminQueueColumn
                    v-for="col in COLUMNS"
                    :key="col.key"
                    :column-key="col.key"
                    :data-panel="col.key"
                    :label="col.label"
                    :accent="col.accent"
                    :count="rowsByColumn[col.key]?.length ?? 0"
                    :droppable="col.droppable"
                    :empty-message="col.empty"
                    @drop="emit('drop', $event)"
                >
                    <AdminDropCard
                        v-for="row in rowsByColumn[col.key]"
                        :key="row.id"
                        :drop="row"
                        :flash="flashRowId === row.id"
                        :draggable="false"
                        @review="onReview"
                    />
                </AdminQueueColumn>
            </div>
        </template>
    </div>
</template>

<style scoped>
.queue-board {
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-width: 0;
}

.queue-tablist {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    scrollbar-width: none;
    padding: 4px 2px;
    margin: 0 -2px;
}
.queue-tablist::-webkit-scrollbar { display: none; }

.queue-tab {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 32px;
    padding: 0 12px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: transparent;
    color: var(--c-text-2);
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.queue-tab--active {
    background: var(--c-accent-dim);
    border-color: var(--c-accent);
    color: #F87171;
}
.queue-tab-count {
    font-family: var(--font-display);
    font-feature-settings: 'tnum' 1;
    font-size: 11px;
    font-weight: 600;
    background: rgba(255, 255, 255, 0.05);
    padding: 1px 6px;
    border-radius: var(--r-pill, 999px);
    color: var(--c-text);
}

.queue-panels {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
    padding-bottom: 4px;
    min-width: 0;
}
.queue-panels::-webkit-scrollbar { display: none; }
.queue-panels > * {
    flex: 0 0 calc(100% - 16px);
    scroll-snap-align: start;
    min-width: 0;
}

@media (min-width: 1024px) {
    .queue-tablist { display: none; }
    .queue-panels {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
        overflow: visible;
        scroll-snap-type: none;
        padding-bottom: 0;
    }
    .queue-panels > * { flex: none; }
}

.queue-board-empty {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.55);
    padding: 56px 20px 48px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
}
.queue-board-empty-num {
    font-family: var(--font-display);
    font-size: 88px;
    color: var(--c-surface-2);
    letter-spacing: 0.1em;
    line-height: 1;
    user-select: none;
}
.queue-board-empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 13px;
    line-height: 1.6;
    color: var(--c-text-3);
    margin: 0;
    text-wrap: balance;
    max-width: 44ch;
}
.queue-board-empty-cta {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    color: var(--c-text-2);
    text-transform: uppercase;
    border-bottom: 1px solid var(--c-border);
    padding-bottom: 4px;
}

@media (prefers-reduced-motion: reduce) {
    .queue-tab { transition: none !important; }
    .queue-panels { scroll-behavior: auto !important; }
}
</style>
