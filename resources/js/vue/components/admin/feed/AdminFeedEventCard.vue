<script setup>
import { computed } from 'vue';
import { useFeedEventIcon } from '../../../composables/useFeedEventIcon.js';
import { formatRelativeTime } from '../../../composables/useFormat.js';

const props = defineProps({
    event: { type: Object, required: true },
});

const { getEventMeta } = useFeedEventIcon();
const meta = computed(() => getEventMeta(props.event.type));
</script>

<template>
    <article class="feed-event" :class="`feed-event--${event.type}`" :aria-label="event.description">
        <span class="feed-event-dot" :class="`feed-event-dot--${meta.color}`" aria-hidden="true"></span>
        <div class="feed-event-body">
            <div class="feed-event-header">
                <span class="feed-event-tag" :class="`feed-event-tag--${meta.color}`">{{ meta.label }}</span>
                <span class="feed-event-time">{{ formatRelativeTime(event.timestamp) }}</span>
            </div>
            <p class="feed-event-desc">{{ event.description }}</p>
        </div>
    </article>
</template>

<style scoped>
.feed-event {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 11px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    transition: background 0.15s var(--ease-out);
}
.feed-event:last-child { border-bottom: none; }
.feed-event:hover { background: rgba(255, 255, 255, 0.02); border-radius: 6px; }

.feed-event-dot {
    flex-shrink: 0;
    width: 8px; height: 8px;
    border-radius: 50%;
    margin-top: 6px;
}
.feed-event-dot--green   { background: var(--color-wc-green-text);  box-shadow: 0 0 0 3px var(--color-wc-green-soft); }
.feed-event-dot--blue    { background: var(--color-wc-blue-text);   box-shadow: 0 0 0 3px var(--color-wc-blue-soft); }
.feed-event-dot--amber   { background: var(--color-wc-amber-text);  box-shadow: 0 0 0 3px var(--color-wc-amber-soft); }
.feed-event-dot--red     { background: var(--color-wc-red-text);    box-shadow: 0 0 0 3px var(--color-wc-red-soft); }
.feed-event-dot--neutral { background: var(--color-wc-text-tertiary); }

.feed-event-body { flex: 1; min-width: 0; }

.feed-event-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 3px;
}

.feed-event-tag {
    font-family: var(--font-mono);
    font-size: 8px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 600;
    flex-shrink: 0;
}
.feed-event-tag--green   { background: var(--color-wc-green-soft);  color: var(--color-wc-green-text); }
.feed-event-tag--blue    { background: var(--color-wc-blue-soft);   color: var(--color-wc-blue-text); }
.feed-event-tag--amber   { background: var(--color-wc-amber-soft);  color: var(--color-wc-amber-text); }
.feed-event-tag--red     { background: var(--color-wc-red-soft);    color: var(--color-wc-red-text); }
.feed-event-tag--neutral { background: rgba(255,255,255,0.05); color: var(--color-wc-text-tertiary); }

.feed-event-time {
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.12em;
    color: var(--color-wc-text-tertiary);
    flex-shrink: 0;
}

.feed-event-desc {
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--color-wc-text-secondary);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
