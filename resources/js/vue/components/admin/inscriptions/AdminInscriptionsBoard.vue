<script setup>
import { ref } from 'vue';
import { useAdminInscriptionsStore } from '@/stores/adminInscriptions';
import AdminInscriptionCard from './AdminInscriptionCard.vue';

const store = useAdminInscriptionsStore();

const COLUMNS = [
    { key: 'sin_contactar', label: 'SIN CONTACTAR',  dotColor: '#FCD34D' },
    { key: 'contactado',    label: 'CONTACTADO',      dotColor: '#60A5FA' },
    { key: 'plan_enviado',  label: 'PLAN ENVIADO',    dotColor: 'rgba(250,250,250,0.56)' },
    { key: 'activo',        label: 'ACTIVO / PAGADO', dotColor: '#34D399' },
];

const EMPTY_MSGS = {
    sin_contactar: 'Sin leads pendientes de contactar. Pipeline limpio.',
    contactado:    'Los leads contactados aparecen aquí.',
    plan_enviado:  'Los planes enviados aparecen en esta columna.',
    activo:        'Los leads convertidos aparecen aquí.',
};

const activeTab = ref(0);
const dragOver = ref(null);

function onDragOver(colKey, evt) {
    evt.preventDefault();
    evt.dataTransfer.dropEffect = 'move';
    dragOver.value = colKey;
}

function onDragLeave(evt) {
    // Solo limpiar si el cursor salió del elemento raíz de la columna
    if (!evt.currentTarget.contains(evt.relatedTarget)) {
        dragOver.value = null;
    }
}

function onDrop(colKey, evt) {
    evt.preventDefault();
    dragOver.value = null;
    const id = Number(evt.dataTransfer.getData('text/plain'));
    if (!id) return;
    store.moveCard(id, colKey);
}

function cards(colKey) {
    return store.kanban[colKey] ?? [];
}
</script>

<template>
    <!-- Mobile: tab bar (lg:hidden) -->
    <div class="board-tabs" role="tablist" aria-label="Columnas del pipeline">
        <button
            v-for="(col, idx) in COLUMNS"
            :key="col.key"
            role="tab"
            :aria-selected="activeTab === idx"
            :aria-controls="`board-col-mobile-${col.key}`"
            @click="activeTab = idx"
            :class="['board-tab', { 'board-tab--active': activeTab === idx }]"
        >
            <span class="board-tab-dot" :style="{ background: col.dotColor }"></span>
            <span class="board-tab-label">{{ col.label }}</span>
            <span class="board-tab-count">{{ cards(col.key).length }}</span>
        </button>
    </div>

    <!-- Board -->
    <div class="board-grid">
        <!-- Desktop: 4 columnas visibles -->
        <div
            v-for="col in COLUMNS"
            :key="`desk-${col.key}`"
            class="board-col board-col--desktop"
            :class="{ 'board-col--dragover': dragOver === col.key }"
            @dragover="onDragOver(col.key, $event)"
            @dragleave="onDragLeave"
            @drop="onDrop(col.key, $event)"
            role="region"
            :aria-label="`Columna ${col.label}`"
        >
            <div class="board-col-header">
                <span class="board-col-dot" :style="{ background: col.dotColor }"></span>
                <span class="board-col-label">{{ col.label }}</span>
                <span class="board-col-count">{{ cards(col.key).length }}</span>
            </div>
            <div class="board-col-cards">
                <AdminInscriptionCard
                    v-for="item in cards(col.key)"
                    :key="item.id"
                    :inscription="item"
                    @contact="store.openContact"
                    @detail="store.openDetail"
                    @reject="store.markRejected(item.id)"
                />
                <div v-if="!cards(col.key).length" class="board-col-empty">
                    <div class="empty-num">—</div>
                    <p class="empty-msg">"{{ EMPTY_MSGS[col.key] }}"</p>
                </div>
            </div>
        </div>

        <!-- Mobile: columna activa -->
        <div
            :id="`board-col-mobile-${COLUMNS[activeTab].key}`"
            class="board-col board-col--mobile"
            :class="{ 'board-col--dragover': dragOver === COLUMNS[activeTab].key }"
            @dragover="onDragOver(COLUMNS[activeTab].key, $event)"
            @dragleave="onDragLeave"
            @drop="onDrop(COLUMNS[activeTab].key, $event)"
            role="tabpanel"
        >
            <AdminInscriptionCard
                v-for="item in cards(COLUMNS[activeTab].key)"
                :key="item.id"
                :inscription="item"
                @contact="store.openContact"
                @detail="store.openDetail"
                @reject="store.markRejected(item.id)"
            />
            <div v-if="!cards(COLUMNS[activeTab].key).length" class="board-col-empty">
                <div class="empty-num">—</div>
                <p class="empty-msg">"{{ EMPTY_MSGS[COLUMNS[activeTab].key] }}"</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Tab bar — mobile only */
.board-tabs {
    display: flex;
    border-bottom: 1px solid var(--color-wc-border);
    overflow-x: auto;
    scrollbar-width: none;
    margin-bottom: 12px;
}
.board-tabs::-webkit-scrollbar { display: none; }
@media (min-width: 1024px) {
    .board-tabs { display: none; }
}
.board-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 14px;
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.14em;
    color: var(--color-wc-text-tertiary);
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    white-space: nowrap;
    transition: color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
}
.board-tab--active {
    color: var(--color-wc-text);
    border-bottom-color: var(--color-wc-accent);
}
.board-tab-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}
.board-tab-label {
    display: none;
}
@media (min-width: 480px) {
    .board-tab-label { display: inline; }
}
.board-tab-count {
    background: rgba(255,255,255,0.07);
    border-radius: 8px;
    padding: 1px 6px;
    font-size: 9px;
}

/* Board grid */
.board-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}
@media (min-width: 1024px) {
    .board-grid {
        grid-template-columns: repeat(4, 1fr);
        align-items: start;
    }
}

/* Desktop columns */
.board-col--desktop {
    display: none;
    flex-direction: column;
    gap: 8px;
    border-radius: 12px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17,17,17,0.5);
    padding: 12px;
    min-height: 180px;
    transition: border-color 0.15s var(--ease-out), background 0.15s var(--ease-out);
}
@media (min-width: 1024px) {
    .board-col--desktop { display: flex; }
}

/* Mobile column */
.board-col--mobile {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 4px 0;
}
@media (min-width: 1024px) {
    .board-col--mobile { display: none; }
}

.board-col--dragover {
    border-color: var(--color-wc-accent) !important;
    background: var(--color-wc-red-soft) !important;
}
.board-col-header {
    display: flex;
    align-items: center;
    gap: 7px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--color-wc-border);
    margin-bottom: 2px;
}
.board-col-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}
.board-col-label {
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.18em;
    color: var(--color-wc-text-tertiary);
    flex: 1;
}
.board-col-count {
    font-family: var(--font-data);
    font-size: 11px;
    color: var(--color-wc-text-tertiary);
}
.board-col-cards {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* Empty state (del master design system) */
.board-col-empty {
    padding: 20px 6px 14px;
    text-align: center;
}
.empty-num {
    font-family: var(--font-display);
    font-size: 42px;
    color: var(--color-wc-bg-tertiary);
    letter-spacing: 0.1em;
    line-height: 1;
    margin-bottom: 8px;
    user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial);
    font-style: italic;
    font-size: 11px;
    color: var(--color-wc-text-tertiary);
    line-height: 1.55;
    margin: 0;
    text-wrap: balance;
}

@media (prefers-reduced-motion: reduce) {
    .board-tab, .board-col--desktop { transition: none !important; }
}
</style>
