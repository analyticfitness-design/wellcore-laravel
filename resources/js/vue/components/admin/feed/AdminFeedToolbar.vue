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
    height: var(--tap-comfort, 48px);
    padding: 0 12px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid #34D399;
    background: rgba(16,185,129,0.10);
    color: #34D399;
    cursor: pointer;
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    transition: background 0.15s var(--ease-out), border-color 0.15s var(--ease-out), color 0.15s var(--ease-out);
    flex-shrink: 0;
}
.live-btn--paused {
    border-color: #FCD34D;
    background: rgba(245,158,11,0.10);
    color: #FCD34D;
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
    color: var(--c-text-3);
    pointer-events: none;
}
.search-input {
    width: 100%;
    height: 40px;
    padding: 0 10px 0 30px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255,255,255,0.03);
    color: var(--c-text);
    font-family: var(--font-sans);
    font-size: 12px;
    outline: none;
    transition: border-color 0.15s var(--ease-out);
}
.search-input:focus { border-color: rgba(255,255,255,0.16); }
.search-input::placeholder { color: var(--c-text-3); }

.toolbar-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    height: var(--tap-comfort, 48px);
    padding: 0 12px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: transparent;
    color: var(--c-text-2);
    cursor: pointer;
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    transition: background 0.15s var(--ease-out), color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
    flex-shrink: 0;
}
.toolbar-btn:hover {
    background: rgba(255,255,255,0.05);
    color: var(--c-text);
    border-color: rgba(255,255,255,0.16);
}
.toolbar-btn svg { width: 14px; height: 14px; }
</style>
