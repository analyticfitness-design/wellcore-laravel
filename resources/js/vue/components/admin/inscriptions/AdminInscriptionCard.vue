<script setup>
import { computed } from 'vue';

const props = defineProps({
    inscription: { type: Object, required: true },
});

const emit = defineEmits(['contact', 'detail', 'reject']);

const PLAN_STYLES = {
    esencial:    { bg: 'var(--color-wc-blue-soft)',            color: 'var(--color-wc-blue-text)' },
    metodo:      { bg: 'rgba(139,92,246,0.1)',                 color: '#a78bfa' },
    elite:       { bg: 'var(--color-wc-amber-soft)',           color: 'var(--color-wc-amber-text)' },
    rise:        { bg: 'var(--color-wc-green-soft)',           color: 'var(--color-wc-green-text)' },
    presencial:  { bg: 'rgba(251,146,60,0.1)',                 color: '#fb923c' },
};

const planStyle = computed(() => {
    const s = PLAN_STYLES[props.inscription.plan_raw];
    return s ?? { bg: 'rgba(255,255,255,0.05)', color: 'var(--color-wc-text-tertiary)' };
});

function onDragStart(evt) {
    evt.dataTransfer.setData('text/plain', String(props.inscription.id));
    evt.dataTransfer.effectAllowed = 'move';
}
</script>

<template>
    <div
        class="insc-card"
        draggable="true"
        @dragstart="onDragStart"
        @click="$emit('detail', inscription)"
        role="button"
        :aria-label="`Lead ${inscription.nombre}, ${inscription.plan}`"
    >
        <div class="insc-card-header">
            <div class="insc-avatar">{{ inscription.initial }}</div>
            <div class="insc-card-meta">
                <span class="insc-name">{{ inscription.nombre }}</span>
                <span class="insc-time">{{ inscription.time_ago }}</span>
            </div>
            <span
                v-if="inscription.plan_raw"
                class="insc-plan-badge"
                :style="{ backgroundColor: planStyle.bg, color: planStyle.color }"
            >
                {{ inscription.plan_raw?.toUpperCase() }}
            </span>
        </div>

        <div class="insc-card-actions">
            <button
                class="insc-action-btn insc-action-contact"
                @click.stop="$emit('contact', inscription)"
                aria-label="Contactar lead"
            >
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 11.5a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .84h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91A16 16 0 0015.1 17.9l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                </svg>
                Contactar
            </button>
            <button
                class="insc-action-btn insc-action-reject"
                @click.stop="$emit('reject', inscription)"
                aria-label="Marcar como spam o no aplica"
                title="Spam / No aplica"
            >
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </div>
</template>

<style scoped>
.insc-card {
    background: var(--c-surface-2);
    border: 1px solid var(--c-border);
    border-radius: var(--r-md, 16px);
    padding: 12px;
    cursor: grab;
    transition: border-color 0.15s var(--ease-out), background 0.15s var(--ease-out);
    user-select: none;
}
.insc-card:hover {
    border-color: rgba(255,255,255,0.16);
    background: rgba(24,24,24,0.95);
}
.insc-card:active { cursor: grabbing; }

.insc-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}
.insc-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(59,130,246,0.10);
    color: #60A5FA;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    flex-shrink: 0;
}
.insc-card-meta {
    flex: 1;
    min-width: 0;
}
.insc-name {
    display: block;
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 500;
    color: var(--c-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.insc-time {
    display: block;
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 1.2px;
    color: var(--c-text-3);
    margin-top: 2px;
}
.insc-plan-badge {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 1.2px;
    padding: 3px 7px;
    border-radius: var(--r-pill, 999px);
    white-space: nowrap;
    flex-shrink: 0;
}
.insc-card-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}
.insc-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: transparent;
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 1.2px;
    color: var(--c-text-2);
    cursor: pointer;
    min-height: var(--tap-comfort, 48px);
    transition: all 0.15s var(--ease-out);
}
.insc-action-contact:hover {
    background: rgba(59,130,246,0.10);
    color: #60A5FA;
    border-color: rgba(59,130,246,0.3);
}
.insc-action-reject {
    padding: 5px 8px;
    margin-left: auto;
}
.insc-action-reject:hover {
    background: var(--c-accent-dim);
    color: #F87171;
    border-color: rgba(220,38,38,0.3);
}

@media (prefers-reduced-motion: reduce) {
    .insc-card,
    .insc-action-btn { transition: none !important; }
}
</style>
