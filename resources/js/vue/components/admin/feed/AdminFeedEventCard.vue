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
    border-bottom: 1px solid var(--c-border);
    transition: background 0.15s var(--ease-out);
}
.feed-event:last-child { border-bottom: none; }
.feed-event:hover { background: rgba(255,255,255,0.02); border-radius: var(--r-sm, 12px); }

.feed-event-dot {
    flex-shrink: 0;
    width: 8px; height: 8px;
    border-radius: 50%;
    margin-top: 6px;
}
.feed-event-dot--green   { background: #34D399; box-shadow: 0 0 0 3px rgba(52,211,153,0.2); }
.feed-event-dot--blue    { background: #60A5FA; box-shadow: 0 0 0 3px rgba(96,165,250,0.2); }
.feed-event-dot--amber   { background: #FCD34D; box-shadow: 0 0 0 3px rgba(212,168,14,0.2); }
.feed-event-dot--red     { background: #F87171; box-shadow: 0 0 0 3px rgba(220,38,38,0.2); }
.feed-event-dot--neutral { background: var(--c-text-3); }

.feed-event-body { flex: 1; min-width: 0; }

.feed-event-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 3px;
}

.feed-event-tag {
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: var(--r-pill, 999px);
    flex-shrink: 0;
}
.feed-event-tag--green   { background: var(--c-success-dim); color: #34D399; }
.feed-event-tag--blue    { background: rgba(59,130,246,0.12); color: #60A5FA; }
.feed-event-tag--amber   { background: var(--c-amber-dim); color: #FCD34D; }
.feed-event-tag--red     { background: var(--c-accent-dim); color: #F87171; }
.feed-event-tag--neutral { background: rgba(255,255,255,0.05); color: var(--c-text-3); }

.feed-event-time {
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600;
    letter-spacing: 1.2px;
    color: var(--c-text-3);
    flex-shrink: 0;
}

.feed-event-desc {
    font-family: var(--font-sans);
    font-size: 14px; font-weight: 400;
    color: var(--c-text-2);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
