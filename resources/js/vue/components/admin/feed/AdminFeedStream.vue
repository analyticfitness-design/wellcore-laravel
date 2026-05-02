<script setup>
import AdminFeedEventCard from './AdminFeedEventCard.vue';

defineProps({
    events: { type: Array, required: true },
    loading: { type: Boolean, default: false },
});
</script>

<template>
    <div class="feed-stream">
        <!-- Loading skeleton -->
        <div v-if="loading && !events.length" class="feed-skeleton" aria-label="Cargando eventos" aria-busy="true">
            <div v-for="i in 8" :key="i" class="feed-skeleton-row"></div>
        </div>

        <!-- Event list -->
        <div v-else-if="events.length" class="feed-stream-list" role="feed" aria-label="Eventos del sistema">
            <div class="feed-timeline-line" aria-hidden="true"></div>
            <TransitionGroup name="feed-enter" tag="div" class="feed-items">
                <AdminFeedEventCard
                    v-for="event in events"
                    :key="event.id ?? (event.timestamp + event.type)"
                    :event="event"
                />
            </TransitionGroup>
        </div>

        <!-- Empty state editorial -->
        <div v-else class="feed-empty" role="status">
            <div class="empty-num" aria-hidden="true">—</div>
            <p class="empty-msg">"El silencio también es una métrica. Cuando no pasa nada, algo está pasando."</p>
        </div>
    </div>
</template>

<style scoped>
.feed-stream { position: relative; }

.feed-skeleton-row {
    height: 52px;
    background: var(--c-surface-2);
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    margin-bottom: 4px;
    animation: feed-pulse 1.5s ease-in-out infinite;
}
@keyframes feed-pulse {
    0%, 100% { opacity: 0.6; }
    50%       { opacity: 0.9; }
}
@media (prefers-reduced-motion: reduce) {
    .feed-skeleton-row { animation: none; }
}

.feed-stream-list { position: relative; }

.feed-timeline-line {
    position: absolute;
    left: 3px; top: 20px; bottom: 20px;
    width: 1px;
    background: var(--c-border);
    z-index: 0;
}

.feed-items {
    position: relative;
    z-index: 1;
    padding-left: 20px;
}

.feed-empty {
    padding: 40px 8px 32px;
    text-align: center;
}
.empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--c-surface-2);
    letter-spacing: 0.1em;
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
    margin: 0 auto;
    text-wrap: balance;
    max-width: 320px;
}

.feed-enter-enter-active {
    transition: opacity 0.2s var(--ease-out), transform 0.2s var(--ease-out);
}
.feed-enter-enter-from {
    opacity: 0;
    transform: translateY(-6px);
}
@media (prefers-reduced-motion: reduce) {
    .feed-enter-enter-active { transition: none !important; }
}
</style>
