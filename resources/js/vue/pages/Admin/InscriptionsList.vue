<script setup>
import { onMounted, onUnmounted } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AdminGreeting from '@/components/admin/dashboard/AdminGreeting.vue';
import { useAdminInscriptionsStore } from '@/stores/adminInscriptions';
import AdminInscriptionFilters from '@/components/admin/inscriptions/AdminInscriptionFilters.vue';
import AdminInscriptionsBoard from '@/components/admin/inscriptions/AdminInscriptionsBoard.vue';
import AdminInscriptionContactModal from '@/components/admin/inscriptions/AdminInscriptionContactModal.vue';
import AdminInscriptionTimeline from '@/components/admin/inscriptions/AdminInscriptionTimeline.vue';

const store = useAdminInscriptionsStore();

onMounted(() => {
    store.fetchAll();
    store.startPolling(30000);
});

onUnmounted(() => {
    store.stopPolling();
});
</script>

<template>
    <AdminLayout>
        <!-- Greeting header -->
        <AdminGreeting
            greeting="Inscripciones"
            :critical-alerts="store.newLeadsCount"
            :pending-tickets="store.kanban?.sin_contactar?.length ?? 0"
            :review-tickets="store.kanban?.contactado?.length ?? 0"
        />

        <!-- New leads notification badge -->
        <Transition name="slide-badge">
            <div
                v-if="store.newLeadsCount > 0"
                class="new-leads-badge"
                @click="store.dismissNewLeads"
                role="status"
                aria-live="polite"
            >
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ store.newLeadsCount }} lead{{ store.newLeadsCount !== 1 ? 's' : '' }} nuevo{{ store.newLeadsCount !== 1 ? 's' : '' }} desde el último refresh
                <button class="new-leads-dismiss" aria-label="Descartar notificación">
                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
        </Transition>

        <!-- Filters + meta row -->
        <div class="insc-toolbar">
            <AdminInscriptionFilters />
            <div class="insc-meta">
                <span class="insc-meta-count">
                    <span class="font-data">{{ store.totalLeads }}</span> leads activos
                </span>
                <span v-if="store.lastRefresh" class="insc-meta-refresh">
                    {{ store.lastRefresh.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' }) }}
                </span>
            </div>
        </div>

        <!-- Loading skeleton -->
        <div v-if="store.loading && !store.all.length" class="page-loading">
            <div class="page-loading-bar" style="height: 44px; margin-bottom: 12px;"></div>
            <div class="page-loading-grid">
                <div v-for="i in 4" :key="i" class="page-loading-card" style="height: 280px;"></div>
            </div>
        </div>

        <!-- Error state -->
        <div v-else-if="store.error && !store.all.length" class="insc-error" role="alert">
            <p class="insc-error-msg">{{ store.error }}</p>
            <button class="insc-retry" @click="store.fetchAll()">Reintentar</button>
        </div>

        <!-- Kanban Board -->
        <AdminInscriptionsBoard v-else />

        <!-- Modals (Teleport to body) -->
        <AdminInscriptionContactModal />
        <AdminInscriptionTimeline
            :inscription="store.detailTarget"
            @close="store.closeDetail"
        />
    </AdminLayout>
</template>

<style scoped>
.insc-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}
.insc-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}
.insc-meta-count {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.2px;
    color: var(--c-text-3);
}
.insc-meta-refresh {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 0.8px;
    color: var(--c-text-3);
    padding: 3px 8px;
    border: 1px solid var(--c-border);
    border-radius: var(--r-pill, 999px);
}

/* New leads badge */
.new-leads-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 13px;
    background: rgba(59,130,246,0.1);
    border: 1px solid rgba(96,165,250,0.22);
    border-radius: var(--r-sm, 12px);
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.2px;
    color: #60A5FA;
    margin-bottom: 12px;
    cursor: pointer;
    width: fit-content;
}
.new-leads-dismiss {
    margin-left: auto;
    background: transparent;
    border: none;
    color: inherit;
    cursor: pointer;
    opacity: 0.7;
    padding: 0;
    display: inline-flex;
}

/* Error state */
.insc-error {
    padding: 20px;
    text-align: center;
    border: 1px solid rgba(220,38,38,0.18);
    border-radius: 12px;
    background: var(--c-accent-dim);
}
.insc-error-msg {
    font-family: var(--font-sans);
    font-size: 13px;
    color: #F87171;
    margin: 0 0 10px;
}
.insc-retry {
    padding: 7px 16px;
    border-radius: var(--r-sm, 12px);
    background: var(--c-accent);
    color: white;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.2px;
    border: none;
    cursor: pointer;
    min-height: var(--tap-comfort, 48px);
    transition: background 0.15s;
}
.insc-retry:hover { background: #b91c1c; }

/* Loading skeleton (patrón del master design system) */
.page-loading-bar,
.page-loading-card {
    background: var(--c-surface-2);
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    animation: page-pulse 1.5s ease-in-out infinite;
}
.page-loading-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}
@media (max-width: 1023px) {
    .page-loading-grid { grid-template-columns: 1fr; }
    .page-loading-card:not(:first-child) { display: none; }
}
@keyframes page-pulse {
    0%, 100% { opacity: 0.6; }
    50%       { opacity: 0.9; }
}

/* Slide transition para badge */
.slide-badge-enter-active { transition: all 0.2s var(--ease-out); }
.slide-badge-leave-active { transition: all 0.15s; }
.slide-badge-enter-from { opacity: 0; transform: translateY(-6px); }
.slide-badge-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .page-loading-bar, .page-loading-card { animation: none !important; }
    .slide-badge-enter-active, .slide-badge-leave-active { transition: none !important; }
    .insc-retry { transition: none !important; }
}
</style>
