<script setup>
import { computed } from 'vue';

const props = defineProps({
    search:     { type: String,  default: '' },
    paused:     { type: Boolean, default: false },
    eventCount: { type: Number,  default: 0 },
});

const emit = defineEmits(['update:search', 'toggle-pause', 'export-csv']);

const liveLabel = computed(() => props.paused ? 'PAUSADO' : 'EN VIVO');
</script>

<template>
    <div class="feed-toolbar">
        <!-- Live indicator / pause -->
        <button
            type="button"
            :aria-label="paused ? 'Reanudar actualizaciones automáticas' : 'Pausar actualizaciones automáticas'"
            :class="['live-btn', { 'live-btn--paused': paused }]"
            @click="$emit('toggle-pause')"
        >
            <span class="live-dot" aria-hidden="true"></span>
            {{ liveLabel }}
        </button>

        <!-- Search -->
        <div class="search-wrap">
            <svg class="search-icon" aria-hidden="true" viewBox="0 0 20 20" fill="none">
                <circle cx="8.5" cy="8.5" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                <path d="M13.5 13.5L17 17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <input
                type="search"
                :value="search"
                placeholder="Buscar en el feed"
                class="search-input"
                :aria-label="`Buscar eventos — ${eventCount} visibles`"
                @input="$emit('update:search', $event.target.value)"
            />
        </div>

        <!-- Export CSV -->
        <button
            type="button"
            class="toolbar-btn"
            aria-label="Exportar eventos filtrados como CSV"
            @click="$emit('export-csv')"
        >
            <svg aria-hidden="true" viewBox="0 0 16 16" fill="none">
                <path stroke="currentColor" stroke-width="1.5" stroke-linecap="round" d="M8 2v8m-3-3 3 3 3-3M3 13h10"/>
            </svg>
            CSV
        </button>
    </div>
</template>

<style scoped>
.feed-toolbar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.live-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    height: 32px;
    padding: 0 10px;
    border-radius: 6px;
    border: 1px solid var(--color-wc-green-text);
    background: var(--color-wc-green-soft);
    color: var(--color-wc-green-text);
    cursor: pointer;
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    transition: background 0.15s var(--ease-out), border-color 0.15s var(--ease-out), color 0.15s var(--ease-out);
    flex-shrink: 0;
}
.live-btn--paused {
    border-color: var(--color-wc-amber-text);
    background: var(--color-wc-amber-soft);
    color: var(--color-wc-amber-text);
}

.live-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: currentColor;
    animation: live-pulse 1.5s ease-in-out infinite;
    flex-shrink: 0;
}
.live-btn--paused .live-dot { animation: none; }
@keyframes live-pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.35; }
}
@media (prefers-reduced-motion: reduce) {
    .live-dot { animation: none !important; }
}

.search-wrap {
    position: relative;
    flex: 1;
    min-width: 160px;
}
.search-icon {
    position: absolute;
    left: 10px; top: 50%; transform: translateY(-50%);
    width: 14px; height: 14px;
    color: var(--color-wc-text-tertiary);
    pointer-events: none;
}
.search-input {
    width: 100%;
    height: 32px;
    padding: 0 10px 0 30px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: rgba(255, 255, 255, 0.03);
    color: var(--color-wc-text);
    font-family: var(--font-sans);
    font-size: 12px;
    outline: none;
    transition: border-color 0.15s var(--ease-out);
}
.search-input:focus { border-color: var(--color-wc-border-2); }
.search-input::placeholder { color: var(--color-wc-text-tertiary); }

.toolbar-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    height: 32px;
    padding: 0 10px;
    border-radius: 6px;
    border: 1px solid var(--color-wc-border);
    background: transparent;
    color: var(--color-wc-text-secondary);
    cursor: pointer;
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    transition: background 0.15s var(--ease-out), color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
    flex-shrink: 0;
}
.toolbar-btn:hover {
    background: rgba(255, 255, 255, 0.05);
    color: var(--color-wc-text);
    border-color: var(--color-wc-border-2);
}
.toolbar-btn svg { width: 14px; height: 14px; }
</style>
