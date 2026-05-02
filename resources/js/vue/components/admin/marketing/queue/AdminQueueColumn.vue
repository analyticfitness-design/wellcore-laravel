<script setup>
import { ref } from 'vue';

const props = defineProps({
    columnKey: { type: String, required: true },
    label: { type: String, required: true },
    accent: { type: String, default: 'neutral' },
    count: { type: Number, default: 0 },
    droppable: { type: Boolean, default: false },
    emptyMessage: { type: String, default: 'Sin drops en esta columna.' },
});

const emit = defineEmits(['drop']);

const isOver = ref(false);

function onDragOver(e) {
    if (!props.droppable) return;
    e.preventDefault();
    if (e.dataTransfer) e.dataTransfer.dropEffect = 'move';
    isOver.value = true;
}
function onDragLeave() {
    isOver.value = false;
}
function onDrop(e) {
    if (!props.droppable) return;
    e.preventDefault();
    isOver.value = false;
    const id = Number(e.dataTransfer?.getData('text/plain'));
    if (Number.isFinite(id)) emit('drop', { id, target: props.columnKey });
}
</script>

<template>
    <section
        class="queue-col"
        :class="[
            `queue-col--${accent}`,
            { 'queue-col--droppable': droppable, 'queue-col--over': isOver },
        ]"
        :data-column="columnKey"
        @dragover="onDragOver"
        @dragleave="onDragLeave"
        @drop="onDrop"
        :aria-label="`Columna ${label}, ${count} drops`"
    >
        <header class="queue-col-head">
            <span class="queue-col-dot" aria-hidden="true"></span>
            <span class="queue-col-label">{{ label }}</span>
            <span class="queue-col-count">{{ count }}</span>
        </header>

        <div class="queue-col-body">
            <slot v-if="count > 0" />
            <div v-else class="queue-col-empty">
                <p class="queue-col-empty-msg">{{ emptyMessage }}</p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.queue-col {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 14px 12px 16px;
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.55);
    min-height: 280px;
    min-width: 0;
    transition: border-color 0.15s var(--ease-out, ease), background 0.15s var(--ease-out, ease);
}
.queue-col--droppable.queue-col--over {
    border-color: var(--c-accent);
    background: rgba(220, 38, 38, 0.04);
}

.queue-col-head {
    display: grid;
    grid-template-columns: 8px 1fr auto;
    gap: 8px;
    align-items: center;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--c-border);
}
.queue-col-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--c-text-3);
}
.queue-col-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-2);
}
.queue-col-count {
    font-family: var(--font-display);
    font-feature-settings: 'tnum' 1;
    font-size: 13px;
    font-weight: 600;
    color: var(--c-text);
    background: rgba(255, 255, 255, 0.04);
    padding: 1px 8px;
    border-radius: var(--r-pill, 999px);
    min-width: 22px;
    text-align: center;
}

.queue-col--in_review .queue-col-dot { background: #FCD34D; }
.queue-col--approved  .queue-col-dot { background: #34D399; }
.queue-col--ready     .queue-col-dot { background: #34D399; }
.queue-col--published .queue-col-dot { background: #60A5FA; }
.queue-col--archived  .queue-col-dot { background: var(--c-text-3); }

.queue-col-body {
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex: 1 1 auto;
    min-height: 100px;
}

.queue-col-empty {
    flex: 1 1 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 18px 8px;
    border-radius: 10px;
    border: 1px dashed var(--c-border);
    background: transparent;
    min-height: 120px;
}
.queue-col-empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 11px;
    line-height: 1.5;
    color: var(--c-text-3);
    text-align: center;
    margin: 0;
    text-wrap: balance;
    max-width: 26ch;
}

@media (prefers-reduced-motion: reduce) {
    .queue-col { transition: none !important; }
}
</style>
