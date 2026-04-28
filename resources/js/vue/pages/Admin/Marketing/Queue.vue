<script setup>
import { onMounted, onBeforeUnmount, ref, computed } from 'vue';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import AdminGreeting from '../../../components/admin/dashboard/AdminGreeting.vue';
import AdminQueueKPIs from '../../../components/admin/marketing/queue/AdminQueueKPIs.vue';
import AdminQueueFilters from '../../../components/admin/marketing/queue/AdminQueueFilters.vue';
import AdminQueueBoard from '../../../components/admin/marketing/queue/AdminQueueBoard.vue';
import AdminDropReviewDrawer from '../../../components/admin/marketing/queue/AdminDropReviewDrawer.vue';
import AdminDropApproveModal from '../../../components/admin/marketing/queue/AdminDropApproveModal.vue';
import AdminDropRejectModal from '../../../components/admin/marketing/queue/AdminDropRejectModal.vue';
import { useAdminMarketingQueueStore } from '../../../stores/adminMarketingQueue';

const store = useAdminMarketingQueueStore();

const approveOpen = ref(false);
const rejectOpen = ref(false);
const targetDropId = ref(null);
const actionError = ref('');
const submitting = ref(false);

const targetDrop = computed(() => {
    if (!targetDropId.value) return null;
    return store.drawerDrop ?? store.rows.find((r) => r.id === targetDropId.value) ?? null;
});

function openReviewDrawer(id) {
    store.openDrawer(id);
}

function closeDrawer() {
    store.closeDrawer();
}

function onApproveCta(id) {
    targetDropId.value = id;
    actionError.value = '';
    approveOpen.value = true;
}

function onRejectCta(id) {
    targetDropId.value = id;
    actionError.value = '';
    rejectOpen.value = true;
}

async function confirmApprove(notes) {
    if (!targetDropId.value) return;
    submitting.value = true;
    actionError.value = '';
    try {
        await store.approve(targetDropId.value, notes);
        approveOpen.value = false;
        store.closeDrawer();
    } catch (err) {
        actionError.value = err.response?.data?.message || 'No se pudo aprobar el drop. Recarga e intenta otra vez.';
    } finally {
        submitting.value = false;
    }
}

async function confirmReject(reason) {
    if (!targetDropId.value) return;
    submitting.value = true;
    actionError.value = '';
    try {
        await store.requestRegenerate(targetDropId.value, reason);
        rejectOpen.value = false;
        store.closeDrawer();
    } catch (err) {
        actionError.value = err.response?.data?.message || 'No se pudo enviar la solicitud al coach.';
    } finally {
        submitting.value = false;
    }
}

function onFilterUpdate({ key, value }) {
    store.setFilter(key, value);
    store.fetchQueue();
}

function onFilterReset() {
    store.clearFilters();
    store.fetchQueue();
}

function onColumnDrop(/* { id, target } */) {
    /*
     * Intencionalmente no-op en v1: el backend no expone PATCH generico de status.
     * Las transiciones validas (in_review → approved, in_review → pending) se
     * disparan desde los CTAs explicitos del drawer (Aprobar / Devolver al coach).
     * Cuando el contrato API exponga PATCH /:id, este handler emitira la accion.
     */
}

onMounted(() => {
    store.fetchQueue();
    store.startPolling(60000);
});

onBeforeUnmount(() => {
    store.stopPolling();
});
</script>

<template>
    <AdminLayout>
        <div class="queue-page">
            <p class="queue-eyebrow">WC · ADMIN / MARKETING / COLA DE DROPS</p>

            <AdminGreeting
                greeting="Cola de drops"
                :critical-alerts="0"
                :pending-tickets="0"
                :review-tickets="0"
            />

            <p class="queue-tagline">
                "Aprobar es un compromiso con la calidad del mensaje. Cada drop que pasa esta cola
                es una conversacion con el cliente."
            </p>

            <div v-if="store.loading && store.rows.length === 0" class="queue-loading" aria-live="polite">
                <div class="queue-loading-bar"></div>
                <div class="queue-loading-grid">
                    <div v-for="i in 4" :key="i" class="queue-loading-card"></div>
                </div>
                <div class="queue-loading-board"></div>
            </div>

            <template v-else>
                <AdminQueueKPIs :kpis="store.kpis" />
                <AdminQueueFilters
                    :filters="store.filters"
                    :rows-count="store.visibleRows.length"
                    @update="onFilterUpdate"
                    @reset="onFilterReset"
                />
                <AdminQueueBoard
                    :rows-by-column="store.rowsByColumn"
                    :flash-row-id="store.flashRowId"
                    @review="openReviewDrawer"
                    @drop="onColumnDrop"
                />
                <p v-if="store.error" class="queue-error" role="alert">{{ store.error }}</p>
            </template>
        </div>

        <AdminDropReviewDrawer
            :open="store.drawerOpen"
            :drop="store.drawerDrop"
            :loading="store.drawerLoading"
            :error="store.drawerError"
            @close="closeDrawer"
            @approve="onApproveCta"
            @reject="onRejectCta"
        />

        <AdminDropApproveModal
            :open="approveOpen"
            :drop="targetDrop"
            :submitting="submitting"
            :error="actionError"
            @close="approveOpen = false"
            @confirm="confirmApprove"
        />

        <AdminDropRejectModal
            :open="rejectOpen"
            :drop="targetDrop"
            :submitting="submitting"
            :error="actionError"
            @close="rejectOpen = false"
            @confirm="confirmReject"
        />
    </AdminLayout>
</template>

<style scoped>
.queue-page {
    display: flex;
    flex-direction: column;
    gap: 18px;
    padding-top: 8px;
    min-width: 0;
}

.queue-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    margin: 0;
}

.queue-tagline {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 12.5px;
    line-height: 1.55;
    color: var(--color-wc-gold, #C8A769);
    margin: -4px 0 0;
    text-wrap: balance;
    max-width: 64ch;
}

.queue-error {
    font-family: var(--font-sans);
    font-size: 12.5px;
    color: var(--color-wc-red-text, #F87171);
    background: rgba(220, 38, 38, 0.07);
    border: 1px solid rgba(220, 38, 38, 0.20);
    border-radius: 10px;
    padding: 10px 14px;
    margin: 0;
}

.queue-loading {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.queue-loading-bar {
    height: 32px;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary, #181818);
    width: 60%;
    animation: queue-pulse 1.5s ease-in-out infinite;
}
.queue-loading-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(2, 1fr);
}
@media (min-width: 1024px) {
    .queue-loading-grid { grid-template-columns: repeat(4, 1fr); }
}
.queue-loading-card,
.queue-loading-board {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary, #181818);
    animation: queue-pulse 1.5s ease-in-out infinite;
}
.queue-loading-card { height: 124px; }
.queue-loading-board { height: 360px; }
@keyframes queue-pulse {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 0.9; }
}

@media (prefers-reduced-motion: reduce) {
    .queue-loading-bar, .queue-loading-card, .queue-loading-board { animation: none !important; }
}
</style>
