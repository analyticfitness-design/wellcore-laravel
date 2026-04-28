<script setup>
import { onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminGreeting from '../../components/admin/dashboard/AdminGreeting.vue';
import AdminPlanTicketsKPIs from '../../components/admin/plan-tickets/AdminPlanTicketsKPIs.vue';
import AdminPlanTicketsFilters from '../../components/admin/plan-tickets/AdminPlanTicketsFilters.vue';
import AdminPlanTicketsBoard from '../../components/admin/plan-tickets/AdminPlanTicketsBoard.vue';
import { useAdminPlanTicketsListStore } from '../../stores/adminPlanTicketsList';

const router = useRouter();
const store = useAdminPlanTicketsListStore();

function openTicket(id) {
    router.push({ name: 'admin-plan-ticket-detail', params: { id } });
}

function onFilterUpdate({ key, value }) {
    store.setFilter(key, value);
    store.fetchTickets();
}

function onFilterReset() {
    store.clearFilters();
    store.fetchTickets();
}

onMounted(() => {
    store.fetchTickets();
    store.startPolling(30000);
});

onBeforeUnmount(() => {
    store.stopPolling();
});
</script>

<template>
    <AdminLayout>
        <div class="ptickets-page">
            <p class="ptickets-eyebrow">WC · ADMIN / OPERACIONES / TICKETS DE PLANES</p>

            <AdminGreeting
                greeting="Tickets de planes"
                :critical-alerts="0"
                :pending-tickets="store.counts.pendiente || 0"
                :review-tickets="store.counts.en_revision || 0"
            />

            <p class="ptickets-tagline">
                "Aprobar es activar el compromiso del coach con el cliente. Cada decision aqui
                se vuelve plan en la app."
            </p>

            <div v-if="store.loading && store.rows.length === 0" class="ptickets-loading" aria-live="polite">
                <div class="ptickets-loading-bar"></div>
                <div class="ptickets-loading-grid">
                    <div v-for="i in 4" :key="i" class="ptickets-loading-card"></div>
                </div>
                <div class="ptickets-loading-board"></div>
            </div>

            <template v-else>
                <AdminPlanTicketsKPIs :kpis="store.kpis" />
                <AdminPlanTicketsFilters
                    :filters="store.filters"
                    :rows-count="store.visibleRows.length"
                    @update="onFilterUpdate"
                    @reset="onFilterReset"
                />
                <AdminPlanTicketsBoard
                    :rows-by-column="store.rowsByColumn"
                    :flash-row-id="store.flashRowId"
                    @open="openTicket"
                />
                <p v-if="store.error" class="ptickets-error" role="alert">{{ store.error }}</p>
            </template>
        </div>
    </AdminLayout>
</template>

<style scoped>
.ptickets-page {
    display: flex;
    flex-direction: column;
    gap: 18px;
    padding-top: 8px;
    min-width: 0;
}

.ptickets-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    margin: 0;
}

.ptickets-tagline {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 12.5px;
    line-height: 1.55;
    color: var(--color-wc-gold, #C8A769);
    margin: -4px 0 0;
    text-wrap: balance;
    max-width: 64ch;
}

.ptickets-error {
    font-family: var(--font-sans);
    font-size: 12.5px;
    color: var(--color-wc-red-text, #F87171);
    background: rgba(220, 38, 38, 0.07);
    border: 1px solid rgba(220, 38, 38, 0.20);
    border-radius: 10px;
    padding: 10px 14px;
    margin: 0;
}

.ptickets-loading {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.ptickets-loading-bar {
    height: 32px;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary, #181818);
    width: 60%;
    animation: ptickets-pulse 1.5s ease-in-out infinite;
}
.ptickets-loading-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(2, 1fr);
}
@media (min-width: 1024px) {
    .ptickets-loading-grid { grid-template-columns: repeat(4, 1fr); }
}
.ptickets-loading-card,
.ptickets-loading-board {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary, #181818);
    animation: ptickets-pulse 1.5s ease-in-out infinite;
}
.ptickets-loading-card { height: 124px; }
.ptickets-loading-board { height: 360px; }

@keyframes ptickets-pulse {
    0%, 100% { opacity: 0.6; }
    50%      { opacity: 0.9; }
}

@media (prefers-reduced-motion: reduce) {
    .ptickets-loading-bar,
    .ptickets-loading-card,
    .ptickets-loading-board { animation: none !important; }
}
</style>
