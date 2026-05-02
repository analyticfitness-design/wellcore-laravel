<script setup>
import { computed, ref, watch, onBeforeUnmount } from 'vue';
import { RouterLink } from 'vue-router';

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

const props = defineProps({
    open: { type: Boolean, default: false },
    drop: { type: Object, default: null },
    loading: { type: Boolean, default: false },
    error: { type: String, default: '' },
});

const emit = defineEmits(['close', 'approve', 'reject']);

const drawerRef = ref(null);
const dragX = ref(0);
const isDragging = ref(false);
let touchStartX = null;
let touchStartT = null;

function lockBodyScroll(lock) {
    if (typeof document === 'undefined') return;
    if (lock) document.body.style.overflow = 'hidden';
    else document.body.style.overflow = '';
}

watch(() => props.open, (isOpen) => {
    lockBodyScroll(isOpen);
    if (!isOpen) { dragX.value = 0; isDragging.value = false; }
});

onBeforeUnmount(() => lockBodyScroll(false));

function onTouchStart(e) {
    if (e.touches.length !== 1) return;
    touchStartX = e.touches[0].clientX;
    touchStartT = Date.now();
    isDragging.value = true;
}
function onTouchMove(e) {
    if (touchStartX == null) return;
    const delta = e.touches[0].clientX - touchStartX;
    if (delta > 0) dragX.value = delta;
}
function onTouchEnd() {
    if (touchStartX == null) return;
    const elapsed = Date.now() - (touchStartT ?? 0);
    const isFlick = elapsed < 300 && dragX.value > 40;
    const isFar = dragX.value > 120;
    if (isFlick || isFar) emit('close');
    dragX.value = 0;
    touchStartX = null;
    touchStartT = null;
    isDragging.value = false;
}

function onKey(e) {
    if (e.key === 'Escape') emit('close');
}

const drawerStyle = computed(() => {
    if (!isDragging.value) return {};
    return { transform: `translateX(${dragX.value}px)` };
});

const isoCode = computed(() => {
    if (!props.drop) return '';
    const y = props.drop.iso_year ?? '----';
    const w = String(props.drop.iso_week ?? '00').padStart(2, '0');
    return `${y}-W${w}`;
});

const statusLabel = computed(() => STATUS_LABEL[props.drop?.status] ?? '—');

const isInReview = computed(() => props.drop?.status === 'in_review');

const dates = computed(() => {
    if (!props.drop) return [];
    const items = [];
    const fmt = (iso) => {
        try {
            return new Date(iso).toLocaleDateString('es-CO', { day: 'numeric', month: 'short', year: 'numeric' });
        } catch {
            return iso;
        }
    };
    if (props.drop.generated_at) items.push({ label: 'Generado', value: fmt(props.drop.generated_at) });
    if (props.drop.reviewed_at) items.push({ label: 'Revisado', value: fmt(props.drop.reviewed_at) });
    if (props.drop.approved_at) items.push({ label: 'Aprobado', value: fmt(props.drop.approved_at) });
    if (props.drop.ready_at) items.push({ label: 'Listo', value: fmt(props.drop.ready_at) });
    return items;
});

const weeklyMessage = computed(() => {
    const msg = props.drop?.content?.weekly_message;
    if (typeof msg === 'string' && msg.trim().length > 0) return msg.trim();
    return null;
});

const pieces = computed(() => {
    const c = props.drop?.content;
    if (!c) return [];
    if (Array.isArray(c.pieces)) {
        return c.pieces.map((p, i) => ({
            key: p.key ?? p.id ?? `piece_${i}`,
            type: p.type ?? p.platform ?? 'pieza',
            title: p.title ?? p.headline ?? null,
            excerpt: typeof p.copy === 'string' ? p.copy.slice(0, 220) : null,
        }));
    }
    if (typeof c.pieces === 'object' && c.pieces) {
        return Object.entries(c.pieces).map(([key, p]) => ({
            key,
            type: p?.platform ?? p?.type ?? 'pieza',
            title: p?.title ?? p?.headline ?? null,
            excerpt: typeof p?.copy === 'string' ? p.copy.slice(0, 220) : null,
        }));
    }
    return [];
});

function onApprove() { if (props.drop?.id) emit('approve', props.drop.id); }
function onReject()  { if (props.drop?.id) emit('reject', props.drop.id); }
</script>

<template>
    <Teleport to="body">
        <Transition name="drawer">
            <div
                v-if="open"
                class="drawer-root"
                role="dialog"
                aria-modal="true"
                aria-labelledby="drawer-title"
                @keydown="onKey"
            >
                <div class="drawer-backdrop" @click="emit('close')" aria-hidden="true"></div>

                <aside
                    ref="drawerRef"
                    class="drawer-panel"
                    :style="drawerStyle"
                    :class="{ 'drawer-panel--dragging': isDragging }"
                    @touchstart.passive="onTouchStart"
                    @touchmove.passive="onTouchMove"
                    @touchend.passive="onTouchEnd"
                >
                    <div class="drawer-handle" aria-hidden="true"></div>

                    <header class="drawer-head">
                        <div class="drawer-head-left">
                            <span class="drawer-eyebrow">REVISION</span>
                            <h2 id="drawer-title" class="drawer-title">{{ isoCode || 'Drop' }}</h2>
                            <p v-if="drop?.coach?.name" class="drawer-coach">
                                {{ drop.coach.name }}
                                <span v-if="drop.coach.id" class="drawer-coach-id">· #{{ drop.coach.id }}</span>
                            </p>
                        </div>
                        <button
                            type="button"
                            class="drawer-close"
                            @click="emit('close')"
                            aria-label="Cerrar revision"
                        >
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </header>

                    <div v-if="loading" class="drawer-skeleton">
                        <div class="skeleton-line skeleton-line--lg"></div>
                        <div class="skeleton-block"></div>
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line skeleton-line--sm"></div>
                    </div>

                    <div v-else-if="error" class="drawer-error" role="alert">
                        <p class="drawer-error-msg">{{ error }}</p>
                        <button type="button" class="drawer-btn drawer-btn--ghost" @click="emit('close')">Cerrar</button>
                    </div>

                    <div v-else-if="drop" class="drawer-body">
                        <div class="drawer-meta">
                            <div class="drawer-meta-row">
                                <span class="drawer-meta-label">Estado</span>
                                <span class="drawer-meta-value drawer-meta-value--accent">{{ statusLabel }}</span>
                            </div>
                            <div v-for="d in dates" :key="d.label" class="drawer-meta-row">
                                <span class="drawer-meta-label">{{ d.label }}</span>
                                <span class="drawer-meta-value">{{ d.value }}</span>
                            </div>
                        </div>

                        <section v-if="weeklyMessage" class="drawer-section">
                            <span class="drawer-section-label">Mensaje semanal</span>
                            <p class="drawer-section-text">{{ weeklyMessage }}</p>
                        </section>

                        <section v-if="pieces.length > 0" class="drawer-section">
                            <span class="drawer-section-label">Piezas ({{ pieces.length }})</span>
                            <ul class="drawer-pieces">
                                <li v-for="p in pieces" :key="p.key" class="drawer-piece">
                                    <span class="drawer-piece-type">{{ p.type }}</span>
                                    <p v-if="p.title" class="drawer-piece-title">{{ p.title }}</p>
                                    <p v-if="p.excerpt" class="drawer-piece-excerpt">{{ p.excerpt }}<span v-if="p.excerpt.length >= 220">…</span></p>
                                </li>
                            </ul>
                        </section>

                        <section v-else-if="!weeklyMessage" class="drawer-empty-content">
                            <p class="drawer-empty-content-msg">
                                "Sin contenido textual extraible. Abre el editor para revisar la estructura completa."
                            </p>
                        </section>

                        <RouterLink
                            v-if="drop?.id"
                            :to="`/admin/marketing/drops/${drop.id}`"
                            class="drawer-edit-link"
                            @click="emit('close')"
                        >
                            EDITAR DETALLES COMPLETOS <span aria-hidden="true">→</span>
                        </RouterLink>
                    </div>

                    <footer v-if="drop && isInReview" class="drawer-foot">
                        <button type="button" class="drawer-btn drawer-btn--reject" @click="onReject">
                            Devolver al coach
                        </button>
                        <button type="button" class="drawer-btn drawer-btn--approve" @click="onApprove">
                            Aprobar drop
                        </button>
                    </footer>
                    <footer v-else-if="drop" class="drawer-foot drawer-foot--readonly">
                        <p class="drawer-readonly-msg">
                            "Este drop ya esta fuera de revision. Solo lectura."
                        </p>
                    </footer>
                </aside>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.drawer-root {
    position: fixed;
    inset: 0;
    z-index: 80;
    display: flex;
    justify-content: flex-end;
}
.drawer-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(2px);
}
.drawer-panel {
    position: relative;
    width: 100%;
    max-width: 520px;
    height: 100%;
    background: var(--c-surface-2);
    border-left: 1px solid var(--c-border);
    display: flex;
    flex-direction: column;
    box-shadow: -24px 0 64px rgba(0, 0, 0, 0.5);
    transition: transform 0.22s var(--ease-out, ease);
    will-change: transform;
}
.drawer-panel--dragging { transition: none; }

.drawer-handle {
    position: absolute;
    top: 14px;
    left: 50%;
    transform: translateX(-50%);
    width: 40px;
    height: 4px;
    border-radius: var(--r-pill, 999px);
    background: rgba(255, 255, 255, 0.14);
    pointer-events: none;
}
@media (min-width: 768px) {
    .drawer-handle { display: none; }
}

.drawer-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    padding: 28px 22px 16px;
    border-bottom: 1px solid var(--c-border);
}
.drawer-head-left { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.drawer-eyebrow {
    font-family: var(--font-display);
    font-size: 9px; letter-spacing: 1.8px; text-transform: uppercase;
    color: var(--c-text-3);
}
.drawer-title {
    font-family: var(--font-display);
    font-size: 28px;
    letter-spacing: 0.04em;
    color: var(--c-text);
    line-height: 1;
    margin: 0;
}
.drawer-coach {
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--c-text-2);
    margin: 0;
}
.drawer-coach-id {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 1.2px;
    color: var(--c-text-3);
    margin-left: 4px;
}
.drawer-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px; height: 36px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: transparent;
    color: var(--c-text-2);
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.drawer-close svg { width: 18px; height: 18px; }
.drawer-close:hover { color: var(--c-text); border-color: rgba(255,255,255,0.12); }
.drawer-close:focus-visible {
    outline: none;
    border-color: var(--c-accent);
    box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2);
}

.drawer-body {
    flex: 1 1 auto;
    overflow-y: auto;
    padding: 18px 22px 22px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.drawer-meta {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid var(--c-border);
    background: rgba(10, 10, 10, 0.55);
}
.drawer-meta-row {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 8px;
    font-family: var(--font-sans);
    font-size: 12.5px;
    align-items: baseline;
}
.drawer-meta-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.drawer-meta-value {
    color: var(--c-text);
    font-family: var(--font-display);
    font-feature-settings: 'tnum' 1;
}
.drawer-meta-value--accent { color: #FCD34D; }

.drawer-section { display: flex; flex-direction: column; gap: 8px; }
.drawer-section-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.drawer-section-text {
    font-family: var(--font-sans);
    font-size: 13px;
    line-height: 1.6;
    color: var(--c-text);
    margin: 0;
    padding: 12px 14px;
    border-radius: 10px;
    background: rgba(10, 10, 10, 0.55);
    border: 1px solid var(--c-border);
}

.drawer-pieces { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; }
.drawer-piece {
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid var(--c-border);
    background: rgba(10, 10, 10, 0.55);
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.drawer-piece-type {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.drawer-piece-title {
    font-family: var(--font-display);
    font-size: 14px;
    letter-spacing: 0.04em;
    color: var(--c-text);
    margin: 0;
    line-height: 1.2;
    text-transform: uppercase;
}
.drawer-piece-excerpt {
    font-family: var(--font-sans);
    font-size: 12px;
    line-height: 1.5;
    color: var(--c-text-2);
    margin: 0;
}

.drawer-empty-content {
    padding: 16px 14px;
    border-radius: 10px;
    border: 1px dashed var(--c-border);
    background: transparent;
}
.drawer-empty-content-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    line-height: 1.55;
    color: var(--c-text-3);
    margin: 0;
    text-wrap: balance;
}

.drawer-edit-link {
    align-self: flex-start;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-2);
    border-bottom: 1px solid var(--c-border);
    padding-bottom: 4px;
    text-decoration: none;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.drawer-edit-link:hover {
    color: var(--c-text);
    border-bottom-color: var(--c-accent);
}

.drawer-foot {
    display: flex;
    gap: 10px;
    padding: 14px 22px 22px;
    border-top: 1px solid var(--c-border);
    background: rgba(10, 10, 10, 0.55);
}
.drawer-foot--readonly { justify-content: center; }
.drawer-readonly-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 11.5px;
    color: var(--c-text-3);
    margin: 0;
    text-align: center;
}
.drawer-btn {
    flex: 1;
    height: 42px;
    min-height: var(--tap-comfort, 48px);
    border-radius: 10px;
    border: 1px solid transparent;
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.drawer-btn--ghost {
    background: transparent;
    color: var(--c-text-2);
    border-color: var(--c-border);
}
.drawer-btn--ghost:hover { color: var(--c-text); border-color: rgba(255,255,255,0.12); }
.drawer-btn--reject {
    background: transparent;
    color: #F87171;
    border-color: rgba(220, 38, 38, 0.4);
}
.drawer-btn--reject:hover { background: rgba(220, 38, 38, 0.10); border-color: var(--c-accent); }
.drawer-btn--approve {
    background: #34D399;
    color: #04221A;
}
.drawer-btn--approve:hover { filter: brightness(1.08); }

.drawer-skeleton {
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.skeleton-line, .skeleton-block {
    background: var(--c-surface-2);
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm, 12px);
    animation: drawer-pulse 1.5s ease-in-out infinite;
}
.skeleton-line { height: 14px; width: 100%; }
.skeleton-line--lg { height: 32px; width: 70%; }
.skeleton-line--sm { height: 12px; width: 40%; }
.skeleton-block { height: 120px; }
@keyframes drawer-pulse {
    0%, 100% { opacity: 0.55; }
    50% { opacity: 0.85; }
}

.drawer-error {
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.drawer-error-msg {
    font-family: var(--font-sans);
    font-size: 13px;
    line-height: 1.55;
    color: #F87171;
    background: rgba(220, 38, 38, 0.07);
    border: 1px solid rgba(220, 38, 38, 0.20);
    border-radius: 10px;
    padding: 12px;
    margin: 0;
}

.drawer-enter-active, .drawer-leave-active { transition: opacity 0.18s var(--ease-out, ease); }
.drawer-enter-active .drawer-panel, .drawer-leave-active .drawer-panel { transition: transform 0.22s var(--ease-out, ease), opacity 0.18s var(--ease-out, ease); }
.drawer-enter-from, .drawer-leave-to { opacity: 0; }
.drawer-enter-from .drawer-panel, .drawer-leave-to .drawer-panel { transform: translateX(28px); opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .drawer-panel,
    .drawer-enter-active, .drawer-leave-active,
    .drawer-enter-active .drawer-panel, .drawer-leave-active .drawer-panel,
    .skeleton-line, .skeleton-block { transition: none !important; animation: none !important; }
}
</style>
