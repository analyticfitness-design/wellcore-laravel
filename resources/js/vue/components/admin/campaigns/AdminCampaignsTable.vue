<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { formatCOP, formatNumber } from '../../../composables/useFormat';

const props = defineProps({
    campaigns: { type: Array,   default: () => [] },
    loading:   { type: Boolean, default: false },
    sortBy:    { type: String,  default: 'created_at' },
    sortDir:   { type: String,  default: 'desc' },
});

const emit = defineEmits(['open-detail', 'pause', 'resume', 'duplicate', 'sort']);

const PLATFORM_LABELS = {
    meta:   'Meta',
    google: 'Google',
    tiktok: 'TikTok',
    email:  'Email',
};

const PLATFORM_COLORS = {
    meta:   { bg: 'rgba(59,130,246,0.1)', text: '#60A5FA' },
    google: { bg: 'rgba(220,38,38,0.1)',  text: '#F87171' },
    tiktok: { bg: 'rgba(6,182,212,0.08)', text: '#67E8F9' },
    email:  { bg: 'rgba(245,158,11,0.1)', text: '#FCD34D' },
};

const STATUS_CONFIG = {
    active: { label: 'Activa',    bg: 'var(--color-wc-green-soft)',  text: 'var(--color-wc-green-text)' },
    paused: { label: 'Pausada',   bg: 'var(--color-wc-amber-soft)',  text: 'var(--color-wc-amber-text)' },
    ended:  { label: 'Terminada', bg: 'rgba(255,255,255,0.06)',      text: 'var(--color-wc-text-tertiary)' },
};

const openKebab = ref(null);

function closeKebabOnOutside(e) {
    if (!e.target.closest('.kebab-wrap')) {
        openKebab.value = null;
    }
}

onMounted(() => document.addEventListener('click', closeKebabOnOutside));
onBeforeUnmount(() => document.removeEventListener('click', closeKebabOnOutside));

function roasColor(roas) {
    if (roas >= 3) return 'var(--color-wc-green-text)';
    if (roas >= 1) return 'var(--color-wc-amber-text)';
    return 'var(--color-wc-red-text)';
}

function platformStyle(platform) {
    return PLATFORM_COLORS[platform] ?? { bg: 'rgba(255,255,255,0.06)', text: 'var(--color-wc-text-tertiary)' };
}

function toggleKebab(id) {
    openKebab.value = openKebab.value === id ? null : id;
}

function sortIcon(col) {
    if (props.sortBy !== col) return '↕';
    return props.sortDir === 'asc' ? '↑' : '↓';
}

function handleKebabAction(action, campaign) {
    openKebab.value = null;
    emit(action, campaign);
}
</script>

<template>
    <div class="table-card">
        <!-- Skeleton loading -->
        <div v-if="loading && !campaigns.length" class="table-skeleton">
            <div v-for="i in 5" :key="i" class="row-skeleton"></div>
        </div>

        <!-- Table desktop -->
        <div v-else-if="campaigns.length" class="table-scroll">
            <table class="camp-table">
                <thead>
                    <tr>
                        <th class="th-sortable" @click="$emit('sort', 'name')">
                            CAMPAÑA {{ sortIcon('name') }}
                        </th>
                        <th>STATUS</th>
                        <th class="th-num th-sortable" @click="$emit('sort', 'budget_cop')">
                            BUDGET {{ sortIcon('budget_cop') }}
                        </th>
                        <th class="th-num th-sortable" @click="$emit('sort', 'impressions')">
                            IMPRES. {{ sortIcon('impressions') }}
                        </th>
                        <th class="th-num th-sortable" @click="$emit('sort', 'clicks')">
                            CLICKS {{ sortIcon('clicks') }}
                        </th>
                        <th class="th-num th-sortable" @click="$emit('sort', 'sales')">
                            CONV. {{ sortIcon('sales') }}
                        </th>
                        <th class="th-num th-sortable" @click="$emit('sort', 'spent_cop')">
                            ROAS {{ sortIcon('spent_cop') }}
                        </th>
                        <th class="th-actions">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="c in campaigns"
                        :key="c.id"
                        class="table-row"
                        @click="$emit('open-detail', c)"
                    >
                        <!-- Nombre + badge plataforma -->
                        <td class="td-name">
                            <div class="name-cell">
                                <span class="camp-name">{{ c.name }}</span>
                                <span
                                    class="platform-badge"
                                    :style="{ background: platformStyle(c.platform).bg, color: platformStyle(c.platform).text }"
                                >{{ PLATFORM_LABELS[c.platform] ?? c.platform }}</span>
                            </div>
                        </td>

                        <!-- Status -->
                        <td>
                            <span
                                class="status-pill"
                                :style="{
                                    background: STATUS_CONFIG[c.status]?.bg,
                                    color:      STATUS_CONFIG[c.status]?.text
                                }"
                            >{{ STATUS_CONFIG[c.status]?.label }}</span>
                        </td>

                        <!-- Budget/Spent -->
                        <td class="td-num">
                            <div class="budget-cell">
                                <span class="val-main">{{ formatCOP(c.budget_cop) }}</span>
                                <span class="val-sub">{{ formatCOP(c.spent_cop) }} gastado</span>
                            </div>
                        </td>

                        <!-- Impresiones -->
                        <td class="td-num">
                            <span class="val-main">{{ formatNumber(c.impressions) }}</span>
                        </td>

                        <!-- Clicks + CTR -->
                        <td class="td-num">
                            <div class="budget-cell">
                                <span class="val-main">{{ formatNumber(c.clicks) }}</span>
                                <span class="val-sub">CTR {{ c.ctr }}%</span>
                            </div>
                        </td>

                        <!-- Conversiones + CR -->
                        <td class="td-num">
                            <div class="budget-cell">
                                <span class="val-main">{{ c.sales }}</span>
                                <span class="val-sub">CR {{ c.cr }}%</span>
                            </div>
                        </td>

                        <!-- ROAS coloreado -->
                        <td class="td-num">
                            <span class="roas-val" :style="{ color: roasColor(c.roas) }">
                                {{ c.roas.toFixed(2) }}x
                            </span>
                        </td>

                        <!-- Acciones kebab -->
                        <td class="td-actions" @click.stop>
                            <div class="kebab-wrap">
                                <button
                                    class="kebab-btn"
                                    @click="toggleKebab(c.id)"
                                    :aria-label="'Acciones para ' + c.name"
                                    :aria-expanded="openKebab === c.id"
                                >
                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                                    </svg>
                                </button>
                                <div v-if="openKebab === c.id" class="kebab-menu">
                                    <button
                                        v-if="c.status === 'active'"
                                        class="kebab-item"
                                        @click="handleKebabAction('pause', c)"
                                    >Pausar</button>
                                    <button
                                        v-else-if="c.status === 'paused'"
                                        class="kebab-item"
                                        @click="handleKebabAction('resume', c)"
                                    >Reactivar</button>
                                    <button
                                        class="kebab-item"
                                        @click="handleKebabAction('duplicate', c)"
                                    >Duplicar</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Empty state -->
        <div v-else class="empty-state">
            <div class="empty-num">—</div>
            <p class="empty-msg">"Sin campañas activas. Importá data desde Meta Business Manager o Google Ads para empezar a trackear."</p>
        </div>
    </div>
</template>

<style scoped>
.table-card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17,17,17,0.7);
    padding: 18px;
    overflow: hidden;
}

.table-scroll {
    overflow-x: auto;
}

.camp-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 760px;
}

thead th {
    padding: 0 12px 11px 0;
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
    text-align: left;
    white-space: nowrap;
    border-bottom: 1px solid var(--c-border);
}

.th-num { text-align: right; }
.th-actions { text-align: right; width: 56px; }

.th-sortable {
    cursor: pointer;
    user-select: none;
    transition: color 0.15s var(--ease-out);
}

.th-sortable:hover { color: var(--c-text-2); }

.table-row {
    cursor: pointer;
    transition: background 0.12s var(--ease-out);
}

.table-row:hover td { background: rgba(255,255,255,0.02); }

.table-row td {
    padding: 11px 12px 11px 0;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    vertical-align: middle;
}

.table-row:last-child td { border-bottom: none; }

.td-name { min-width: 200px; }
.td-num  { text-align: right; font-family: var(--font-display); font-feature-settings: 'tnum' 1; }
.td-actions { text-align: right; }

.name-cell {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.camp-name {
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 500;
    color: var(--c-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 220px;
}

.platform-badge {
    font-family: var(--font-display);
    font-size: 7px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    padding: 2px 7px;
    border-radius: var(--r-pill, 999px);
    align-self: flex-start;
}

.status-pill {
    font-family: var(--font-display);
    font-size: 7px;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    padding: 3px 9px;
    border-radius: var(--r-pill, 999px);
    white-space: nowrap;
}

.budget-cell {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
}

.val-main {
    font-size: 13px;
    color: var(--c-text);
    font-feature-settings: 'tnum' 1;
}

.val-sub {
    font-size: 10px;
    color: var(--c-text-3);
    font-feature-settings: 'tnum' 1;
}

.roas-val {
    font-size: 13px;
    font-weight: 700;
    font-feature-settings: 'tnum' 1;
}

/* Kebab menu */
.kebab-wrap {
    position: relative;
    display: inline-block;
}

.kebab-btn {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 6px;
    color: var(--c-text-3);
    cursor: pointer;
    transition: background 0.15s var(--ease-out), color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
}

.kebab-btn:hover {
    background: rgba(255,255,255,0.05);
    border-color: var(--c-border);
    color: var(--c-text-2);
}

.kebab-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 4px);
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: 10px;
    padding: 6px;
    min-width: 130px;
    z-index: 20;
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
}

.kebab-item {
    display: block;
    width: 100%;
    padding: 8px 12px;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: var(--c-text-2);
    background: transparent;
    border: none;
    border-radius: 6px;
    text-align: left;
    cursor: pointer;
    transition: background 0.12s var(--ease-out), color 0.12s var(--ease-out);
}

.kebab-item:hover {
    background: rgba(255,255,255,0.05);
    color: var(--c-text);
}

/* Skeleton */
.table-skeleton {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.row-skeleton {
    height: 44px;
    background: var(--c-surface-2);
    border-radius: var(--r-sm, 12px);
    animation: page-pulse 1.5s ease-in-out infinite;
}

/* Empty state */
.empty-state {
    padding: 24px 8px 18px;
    text-align: center;
}

.empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--c-surface-2);
    letter-spacing: 0.8px;
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}

.empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: var(--c-text-3);
    line-height: 1.55;
    margin: 0 0 16px;
    text-wrap: balance;
}

@keyframes page-pulse {
    0%, 100% { opacity: 0.6; }
    50%       { opacity: 0.9; }
}

@media (prefers-reduced-motion: reduce) {
    .row-skeleton { animation: none !important; }
}
</style>
