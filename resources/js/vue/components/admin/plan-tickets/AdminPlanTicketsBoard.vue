<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import AdminPlanTicketCard from './AdminPlanTicketCard.vue';

const props = defineProps({
    rowsByColumn: { type: Object, required: true },
    flashRowId: { type: [Number, String, null], default: null },
});

const emit = defineEmits(['open']);

const COLUMNS = [
    { key: 'pendiente',   label: 'Pendientes',   accent: 'amber',  empty: '"Sin tickets pendientes. Bienvenida la calma operativa."' },
    { key: 'en_revision', label: 'En revision',  accent: 'blue',   empty: '"Nada en revision. Cuando abras un detail aparecera aqui."' },
    { key: 'completado',  label: 'Aprobados',    accent: 'green',  empty: '"Sin aprobaciones recientes. Cada decision deja huella."' },
    { key: 'rechazado',   label: 'Rechazados',   accent: 'red',    empty: '"Sin rechazos. Cuando los hay, regresan al coach con razon."' },
];

const activeMobileTab = ref('pendiente');
const tablistRef = ref(null);
const panelsRef = ref(null);

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

function onOpen(id) {
    emit('open', id);
}
</script>

<template>
    <div class="pt-board" :class="{ 'pt-board--empty': isEmpty }">
        <div v-if="isEmpty" class="pt-board-empty">
            <div class="pt-board-empty-num" aria-hidden="true">—</div>
            <p class="pt-board-empty-msg">
                "No hay tickets pendientes de revision. Cuando los coaches completen un plan,
                aparecera aqui."
            </p>
            <span class="pt-board-empty-cta">VER HISTORICO <span aria-hidden="true">→</span></span>
        </div>

        <template v-else>
            <div ref="tablistRef" class="pt-tablist lg:hidden" role="tablist">
                <button
                    v-for="col in COLUMNS"
                    :key="col.key"
                    type="button"
                    role="tab"
                    :aria-selected="activeMobileTab === col.key"
                    :data-tab="col.key"
                    class="pt-tab"
                    :class="{ 'pt-tab--active': activeMobileTab === col.key }"
                    @click="setMobileTab(col.key)"
                >
                    <span>{{ col.label }}</span>
                    <span class="pt-tab-count">{{ rowsByColumn[col.key]?.length ?? 0 }}</span>
                </button>
            </div>

            <div ref="panelsRef" class="pt-panels">
                <section
                    v-for="col in COLUMNS"
                    :key="col.key"
                    :data-panel="col.key"
                    class="pt-col"
                    :class="`pt-col--${col.accent}`"
                    :aria-label="`Columna ${col.label}, ${rowsByColumn[col.key]?.length ?? 0} tickets`"
                >
                    <header class="pt-col-head">
                        <span class="pt-col-dot" aria-hidden="true"></span>
                        <span class="pt-col-label">{{ col.label }}</span>
                        <span class="pt-col-count">{{ rowsByColumn[col.key]?.length ?? 0 }}</span>
                    </header>
                    <div class="pt-col-body">
                        <template v-if="(rowsByColumn[col.key]?.length ?? 0) > 0">
                            <AdminPlanTicketCard
                                v-for="row in rowsByColumn[col.key]"
                                :key="row.id"
                                :ticket="row"
                                :flash="flashRowId === row.id"
                                @open="onOpen"
                            />
                        </template>
                        <div v-else class="pt-col-empty">
                            <p class="pt-col-empty-msg">{{ col.empty }}</p>
                        </div>
                    </div>
                </section>
            </div>
        </template>
    </div>
</template>

<style scoped>
.pt-board {
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-width: 0;
}

.pt-tablist {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    scrollbar-width: none;
    padding: 4px 2px;
    margin: 0 -2px;
}
.pt-tablist::-webkit-scrollbar { display: none; }

.pt-tab {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 32px;
    padding: 0 12px;
    border-radius: 8px;
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
.pt-tab--active {
    background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.10));
    border-color: var(--color-wc-accent, #DC2626);
    color: var(--color-wc-red-text, #F87171);
}
.pt-tab-count {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-feature-settings: 'tnum' 1;
    font-size: 11px;
    font-weight: 600;
    background: rgba(255, 255, 255, 0.05);
    padding: 1px 6px;
    border-radius: 999px;
    color: var(--color-wc-text);
}

.pt-panels {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
    padding-bottom: 4px;
    min-width: 0;
}
.pt-panels::-webkit-scrollbar { display: none; }
.pt-panels > * {
    flex: 0 0 calc(100% - 16px);
    scroll-snap-align: start;
    min-width: 0;
}

@media (min-width: 1024px) {
    .pt-tablist { display: none; }
    .pt-panels {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        overflow: visible;
        scroll-snap-type: none;
        padding-bottom: 0;
    }
    .pt-panels > * { flex: none; }
}

.pt-col {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 14px 12px 16px;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.55);
    min-height: 280px;
    min-width: 0;
}

.pt-col-head {
    display: grid;
    grid-template-columns: 8px 1fr auto;
    gap: 8px;
    align-items: center;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--color-wc-border);
}
.pt-col-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--color-wc-text-tertiary);
}
.pt-col-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-secondary);
}
.pt-col-count {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-feature-settings: 'tnum' 1;
    font-size: 13px;
    font-weight: 600;
    color: var(--color-wc-text);
    background: rgba(255, 255, 255, 0.04);
    padding: 1px 8px;
    border-radius: 999px;
    min-width: 22px;
    text-align: center;
}

.pt-col--amber  .pt-col-dot { background: var(--color-wc-amber-text, #FCD34D); }
.pt-col--blue   .pt-col-dot { background: var(--color-wc-blue-text, #60A5FA); }
.pt-col--green  .pt-col-dot { background: var(--color-wc-green-text, #34D399); }
.pt-col--red    .pt-col-dot { background: var(--color-wc-red-text, #F87171); }

.pt-col-body {
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex: 1 1 auto;
    min-height: 100px;
}
.pt-col-empty {
    flex: 1 1 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 18px 8px;
    border-radius: 10px;
    border: 1px dashed var(--color-wc-border);
    background: transparent;
    min-height: 120px;
}
.pt-col-empty-msg {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 11px;
    line-height: 1.5;
    color: var(--color-wc-text-tertiary);
    text-align: center;
    margin: 0;
    text-wrap: balance;
    max-width: 26ch;
}

.pt-board-empty {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.55);
    padding: 56px 20px 48px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
}
.pt-board-empty-num {
    font-family: var(--font-display);
    font-size: 88px;
    color: var(--color-wc-bg-tertiary, #181818);
    letter-spacing: 0.1em;
    line-height: 1;
    user-select: none;
}
.pt-board-empty-msg {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 13px;
    line-height: 1.6;
    color: var(--color-wc-text-tertiary);
    margin: 0;
    text-wrap: balance;
    max-width: 44ch;
}
.pt-board-empty-cta {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    color: var(--color-wc-text-secondary);
    text-transform: uppercase;
    border-bottom: 1px solid var(--color-wc-border);
    padding-bottom: 4px;
}

@media (prefers-reduced-motion: reduce) {
    .pt-tab { transition: none !important; }
    .pt-panels { scroll-behavior: auto !important; }
}
</style>
