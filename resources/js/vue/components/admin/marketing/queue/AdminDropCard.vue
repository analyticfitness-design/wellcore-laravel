<script setup>
import { computed } from 'vue';

const props = defineProps({
    drop: { type: Object, required: true },
    flash: { type: Boolean, default: false },
    draggable: { type: Boolean, default: false },
});

const emit = defineEmits(['review', 'dragstart', 'dragend']);

const STATUS_LABEL = {
    pending: 'Pendiente',
    generating: 'Generando',
    in_review: 'En revision',
    approved: 'Aprobado',
    ready: 'Listo',
    in_progress: 'En progreso',
    completed: 'Completado',
    archived: 'Archivado',
};

const STATUS_VARIANT = {
    pending: 'amber',
    generating: 'blue',
    in_review: 'amber',
    approved: 'green',
    ready: 'green',
    in_progress: 'blue',
    completed: 'green',
    archived: 'muted',
};

const isoCode = computed(() => {
    const y = props.drop?.iso_year ?? '----';
    const w = String(props.drop?.iso_week ?? '00').padStart(2, '0');
    return `${y}-W${w}`;
});

const statusLabel = computed(() => STATUS_LABEL[props.drop?.status] ?? '—');
const statusVariant = computed(() => STATUS_VARIANT[props.drop?.status] ?? 'muted');

const lastAction = computed(() => {
    const iso = props.drop?.last_action_at;
    if (!iso) return '—';
    try {
        const d = new Date(iso);
        const now = Date.now();
        const diffMs = now - d.getTime();
        const diffMin = Math.round(diffMs / 60000);
        if (diffMin < 1) return 'hace instantes';
        if (diffMin < 60) return `hace ${diffMin} min`;
        const diffH = Math.round(diffMin / 60);
        if (diffH < 24) return `hace ${diffH} h`;
        const diffD = Math.round(diffH / 24);
        if (diffD < 7) return `hace ${diffD} d`;
        return d.toLocaleDateString('es-CO', { day: 'numeric', month: 'short' });
    } catch {
        return iso;
    }
});

const coachInitial = computed(() => {
    const name = props.drop?.coach?.name ?? '';
    return name.trim().charAt(0).toUpperCase() || '·';
});

function onClick() {
    emit('review', props.drop.id);
}

function onKey(e) {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        onClick();
    }
}

function onDragStart(e) {
    if (!props.draggable) return;
    try { e.dataTransfer?.setData('text/plain', String(props.drop.id)); } catch {}
    if (e.dataTransfer) e.dataTransfer.effectAllowed = 'move';
    emit('dragstart', { id: props.drop.id, status: props.drop.status });
}
function onDragEnd() {
    if (!props.draggable) return;
    emit('dragend', { id: props.drop.id });
}
</script>

<template>
    <article
        class="drop-card"
        :class="{ 'drop-card--flash': flash, 'drop-card--draggable': draggable }"
        :draggable="draggable"
        tabindex="0"
        role="button"
        :aria-label="`Drop ${isoCode} de ${drop.coach?.name ?? 'coach desconocido'}, estado ${statusLabel}`"
        @click="onClick"
        @keydown="onKey"
        @dragstart="onDragStart"
        @dragend="onDragEnd"
    >
        <header class="drop-card-head">
            <div class="drop-card-avatar" aria-hidden="true">{{ coachInitial }}</div>
            <div class="drop-card-head-text">
                <span class="drop-card-coach">{{ drop.coach?.name ?? 'Coach #' + drop.coach?.id }}</span>
                <span class="drop-card-iso">{{ isoCode }}</span>
            </div>
            <span class="drop-card-status" :class="`drop-card-status--${statusVariant}`">{{ statusLabel }}</span>
        </header>

        <div class="drop-card-thumb" aria-hidden="true">
            <div class="drop-card-thumb-content">
                <span class="drop-card-thumb-num">{{ String(drop.iso_week ?? '').padStart(2, '0') }}</span>
                <span class="drop-card-thumb-label">SEMANA</span>
            </div>
        </div>

        <footer class="drop-card-foot">
            <span class="drop-card-time">{{ lastAction }}</span>
            <span class="drop-card-cta">REVISAR <span aria-hidden="true">→</span></span>
        </footer>
    </article>
</template>

<style scoped>
.drop-card {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    cursor: pointer;
    text-align: left;
    color: inherit;
    transition: border-color 0.15s var(--ease-out, ease), background 0.15s var(--ease-out, ease), transform 0.15s var(--ease-out, ease);
    position: relative;
}
.drop-card:hover { border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.16)); background: rgba(24, 24, 24, 0.85); }
.drop-card:focus-visible {
    outline: none;
    border-color: var(--color-wc-accent, #DC2626);
    box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.18);
}
.drop-card--draggable { cursor: grab; }
.drop-card--draggable:active { cursor: grabbing; }

.drop-card--flash {
    animation: drop-card-flash 1.4s var(--ease-out, ease);
}
@keyframes drop-card-flash {
    0% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); border-color: var(--color-wc-border); }
    20% { box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.25); border-color: var(--color-wc-green-text, #34D399); }
    100% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); border-color: var(--color-wc-border); }
}

.drop-card-head {
    display: grid;
    grid-template-columns: 28px 1fr auto;
    gap: 8px;
    align-items: center;
    min-width: 0;
}
.drop-card-avatar {
    width: 28px; height: 28px;
    border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(220, 38, 38, 0.10);
    color: var(--color-wc-red-text, #F87171);
    font-family: var(--font-display);
    font-size: 14px;
    letter-spacing: 0.04em;
}
.drop-card-head-text { min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.drop-card-coach {
    font-family: var(--font-sans);
    font-size: 12.5px;
    font-weight: 600;
    color: var(--color-wc-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.drop-card-iso {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    color: var(--color-wc-text-tertiary);
    text-transform: uppercase;
}

.drop-card-status {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: 4px;
    border: 1px solid transparent;
    white-space: nowrap;
}
.drop-card-status--amber  { background: var(--color-wc-amber-soft, rgba(245,158,11,0.10)); color: var(--color-wc-amber-text, #FCD34D); border-color: rgba(245,158,11,0.20); }
.drop-card-status--green  { background: var(--color-wc-green-soft, rgba(16,185,129,0.10)); color: var(--color-wc-green-text, #34D399); border-color: rgba(16,185,129,0.20); }
.drop-card-status--blue   { background: var(--color-wc-blue-soft, rgba(59,130,246,0.10)); color: var(--color-wc-blue-text, #60A5FA); border-color: rgba(59,130,246,0.20); }
.drop-card-status--muted  { background: rgba(255,255,255,0.03); color: var(--color-wc-text-tertiary); border-color: var(--color-wc-border); }

.drop-card-thumb {
    aspect-ratio: 4 / 5;
    border-radius: 10px;
    background:
        radial-gradient(ellipse 80% 60% at 30% 20%, rgba(220,38,38,0.10), transparent 70%),
        rgba(10, 10, 10, 0.85);
    border: 1px solid var(--color-wc-border);
    position: relative;
    overflow: hidden;
}
.drop-card-thumb::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    background-size: 160px;
    opacity: 0.04;
    pointer-events: none;
}
.drop-card-thumb-content {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
}
.drop-card-thumb-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: rgba(255, 255, 255, 0.10);
    letter-spacing: 0.06em;
    line-height: 1;
    user-select: none;
}
.drop-card-thumb-label {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.28em;
    color: var(--color-wc-text-tertiary);
}

.drop-card-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
}
.drop-card-time {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.10em;
    color: var(--color-wc-text-tertiary);
    text-transform: lowercase;
}
.drop-card-cta {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    color: var(--color-wc-text-secondary);
    text-transform: uppercase;
    border-bottom: 1px solid var(--color-wc-border);
    padding-bottom: 2px;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.drop-card:hover .drop-card-cta { color: var(--color-wc-text); border-bottom-color: var(--color-wc-accent, #DC2626); }

@media (prefers-reduced-motion: reduce) {
    .drop-card { transition: none !important; }
    .drop-card--flash { animation: none !important; }
}
</style>
