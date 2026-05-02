<script setup>
import { computed, onMounted, onBeforeUnmount } from 'vue';
import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminGreeting from '../../components/admin/dashboard/AdminGreeting.vue';
import AdminAlertsRow from '../../components/admin/dashboard/AdminAlertsRow.vue';
import AdminFeedStream from '../../components/admin/feed/AdminFeedStream.vue';
import AdminFeedFilters from '../../components/admin/feed/AdminFeedFilters.vue';
import AdminFeedToolbar from '../../components/admin/feed/AdminFeedToolbar.vue';
import { useAdminFeedStore } from '../../stores/adminFeed.js';

const store = useAdminFeedStore();

const greetingText = computed(() => {
    const n = store.stats.eventsToday;
    return n > 0 ? `Live Feed — ${n} eventos hoy` : 'Live Feed';
});

function exportCsv() {
    const headers = ['tipo', 'descripcion', 'timestamp'];
    const rows = store.filteredEvents.map(e => [
        e.type,
        (e.description ?? '').replace(/,/g, ' '),
        e.timestamp ?? '',
    ]);
    const csv = [headers, ...rows].map(r => r.join(',')).join('\n');
    const blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `feed-${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}

onMounted(() => {
    store.fetchInitial();
    store.startPolling();
});

onBeforeUnmount(() => {
    store.stopPolling();
});
</script>

<template>
    <AdminLayout>
        <AdminGreeting
            :greeting="greetingText"
            :critical-alerts="0"
            :pending-tickets="0"
            :review-tickets="0"
        />
        <AdminAlertsRow :alerts="[]" />

        <!-- Toolbar: EN VIVO / pausar + search + export -->
        <div class="feed-toolbar-row">
            <AdminFeedToolbar
                :search="store.filters.search"
                :paused="store.paused"
                :event-count="store.filteredEvents.length"
                @update:search="store.filters.search = $event"
                @toggle-pause="store.togglePause()"
                @export-csv="exportCsv"
            />
        </div>

        <!-- Filters + count -->
        <div class="feed-filters-row">
            <AdminFeedFilters
                :active-types="store.filters.types"
                @update:activeTypes="store.filters.types = $event"
            />
            <span class="feed-count" aria-live="polite">{{ store.filteredEvents.length }} eventos</span>
        </div>

        <!-- Error state -->
        <div v-if="store.error" class="feed-error" role="alert">
            <p>{{ store.error }}</p>
            <button type="button" class="feed-retry" @click="store.fetchInitial()">Reintentar</button>
        </div>

        <!-- Main stream card -->
        <div class="feed-card">
            <AdminFeedStream
                :events="store.filteredEvents"
                :loading="store.loading"
            />
        </div>
    </AdminLayout>
</template>

<style scoped>
.feed-toolbar-row {
    margin-bottom: 12px;
}

.feed-filters-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 12px;
}

.feed-count {
    margin-left: auto;
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 1.2px;
    color: var(--c-text-3);
    white-space: nowrap;
    text-transform: uppercase;
}

.feed-error {
    display: flex;
    align-items: center;
    gap: 12px;
    border-radius: 12px;
    border: 1px solid var(--c-accent-dim);
    background: var(--c-accent-dim);
    padding: 12px 16px;
    margin-bottom: 12px;
}
.feed-error p {
    flex: 1;
    font-family: var(--font-sans);
    font-size: 13px;
    color: #F87171;
    margin: 0;
}
.feed-retry {
    height: 28px;
    padding: 0 10px;
    border-radius: var(--r-pill, 999px);
    border: 1px solid var(--c-accent);
    background: var(--c-accent-dim);
    color: var(--c-text);
    cursor: pointer;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    transition: background 0.15s var(--ease-out);
}
.feed-retry:hover { background: rgba(220, 38, 38, 0.2); }

.feed-card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 18px;
}
</style>
