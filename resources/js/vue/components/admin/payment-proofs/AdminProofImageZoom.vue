<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    src: { type: String, default: null },
    alt: { type: String, default: 'Comprobante de pago' },
});

const emit = defineEmits(['close']);

const scale = ref(1);
const tx = ref(0);
const ty = ref(0);

// Drag-to-pan state
const dragging = ref(false);
const dragStart = ref({ x: 0, y: 0, tx: 0, ty: 0 });

const MIN_SCALE = 1;
const MAX_SCALE = 5;
const ZOOM_STEP = 0.25;

function reset() {
    scale.value = 1;
    tx.value = 0;
    ty.value = 0;
}

function close() {
    reset();
    emit('close');
}

function zoomIn() {
    scale.value = Math.min(MAX_SCALE, +(scale.value + ZOOM_STEP).toFixed(2));
}

function zoomOut() {
    const next = Math.max(MIN_SCALE, +(scale.value - ZOOM_STEP).toFixed(2));
    scale.value = next;
    if (next === 1) { tx.value = 0; ty.value = 0; }
}

function onWheel(e) {
    e.preventDefault();
    const delta = e.deltaY < 0 ? ZOOM_STEP : -ZOOM_STEP;
    const next = Math.max(MIN_SCALE, Math.min(MAX_SCALE, +(scale.value + delta).toFixed(2)));
    scale.value = next;
    if (next === 1) { tx.value = 0; ty.value = 0; }
}

function onPointerDown(e) {
    if (scale.value <= 1) return;
    dragging.value = true;
    dragStart.value = { x: e.clientX, y: e.clientY, tx: tx.value, ty: ty.value };
    e.target.setPointerCapture?.(e.pointerId);
}
function onPointerMove(e) {
    if (!dragging.value) return;
    const dx = e.clientX - dragStart.value.x;
    const dy = e.clientY - dragStart.value.y;
    tx.value = dragStart.value.tx + dx;
    ty.value = dragStart.value.ty + dy;
}
function onPointerUp() {
    dragging.value = false;
}

function onKeydown(e) {
    if (!props.open) return;
    if (e.key === 'Escape') close();
    if (e.key === '+' || e.key === '=') zoomIn();
    if (e.key === '-') zoomOut();
    if (e.key === '0') reset();
}

watch(() => props.open, (open) => {
    if (open) {
        reset();
        document.addEventListener('keydown', onKeydown);
    } else {
        document.removeEventListener('keydown', onKeydown);
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', onKeydown);
});

const transform = computed(() => `translate(${tx.value}px, ${ty.value}px) scale(${scale.value})`);
const cursorClass = computed(() => {
    if (scale.value <= 1) return 'cursor-zoom-in';
    return dragging.value ? 'cursor-grabbing' : 'cursor-grab';
});
</script>

<template>
  <Teleport to="body">
    <Transition name="zoom-fade">
      <div v-if="open" class="zoom-modal" @click.self="close" role="dialog" aria-label="Vista ampliada del comprobante">
        <!-- Toolbar -->
        <div class="zoom-toolbar">
          <button class="zoom-tool" @click="zoomOut" aria-label="Reducir">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M8 11h6"/><path d="m21 21-4.3-4.3"/></svg>
          </button>
          <span class="zoom-level">{{ Math.round(scale * 100) }}%</span>
          <button class="zoom-tool" @click="zoomIn" aria-label="Aumentar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M8 11h6M11 8v6"/><path d="m21 21-4.3-4.3"/></svg>
          </button>
          <button class="zoom-tool zoom-tool--reset" @click="reset" aria-label="Restablecer zoom">RESET</button>
          <button class="zoom-tool zoom-tool--close" @click="close" aria-label="Cerrar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Canvas -->
        <div
          class="zoom-canvas"
          :class="cursorClass"
          @wheel="onWheel"
          @pointerdown="onPointerDown"
          @pointermove="onPointerMove"
          @pointerup="onPointerUp"
          @pointercancel="onPointerUp"
        >
          <img
            v-if="src"
            :src="src"
            :alt="alt"
            class="zoom-img"
            :style="{ transform }"
            draggable="false"
          />
          <div v-else class="zoom-empty">No se pudo cargar la imagen.</div>
        </div>

        <!-- Hint -->
        <div class="zoom-hint">
          Scroll para zoom · Arrastra para mover · ESC para cerrar
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.zoom-modal {
    position: fixed;
    inset: 0;
    z-index: 95;
    background: rgba(0, 0, 0, 0.92);
    backdrop-filter: blur(6px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.zoom-toolbar {
    position: absolute;
    top: 16px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(17, 17, 17, 0.92);
    border: 1px solid var(--color-wc-border);
    border-radius: 999px;
    padding: 6px 8px;
    z-index: 2;
}
.zoom-tool {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: transparent;
    border: 1px solid transparent;
    color: var(--color-wc-text-secondary);
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.zoom-tool:hover { background: rgba(255, 255, 255, 0.08); color: var(--color-wc-text); }
.zoom-tool svg { width: 16px; height: 16px; }
.zoom-tool--reset {
    width: auto;
    padding: 0 12px;
    border-radius: 999px;
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
}
.zoom-tool--close { color: var(--color-wc-red-text, #F87171); }
.zoom-tool--close:hover { background: rgba(220, 38, 38, 0.16); color: var(--color-wc-red-text, #F87171); }
.zoom-level {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-weight: 600;
    font-variant-numeric: tabular-nums;
    font-size: 12px;
    color: var(--color-wc-text);
    min-width: 44px;
    text-align: center;
}

.zoom-canvas {
    flex: 1;
    width: 100%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    touch-action: pinch-zoom;
}
.cursor-zoom-in  { cursor: zoom-in; }
.cursor-grab     { cursor: grab; }
.cursor-grabbing { cursor: grabbing; }

.zoom-img {
    max-width: 92vw;
    max-height: 80vh;
    object-fit: contain;
    transform-origin: center;
    transition: transform 0.06s linear;
    user-select: none;
    -webkit-user-drag: none;
}

.zoom-empty {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    color: var(--color-wc-text-tertiary);
    font-size: 14px;
}

.zoom-hint {
    position: absolute;
    bottom: 16px;
    left: 50%;
    transform: translateX(-50%);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    background: rgba(17, 17, 17, 0.7);
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid var(--color-wc-border);
}

.zoom-fade-enter-active, .zoom-fade-leave-active { transition: opacity 0.2s var(--ease-out, ease); }
.zoom-fade-enter-from, .zoom-fade-leave-to { opacity: 0; }

@media (max-width: 640px) {
    .zoom-hint { font-size: 8px; padding: 5px 10px; }
    .zoom-tool { width: 32px; height: 32px; }
}

@media (prefers-reduced-motion: reduce) {
    .zoom-img { transition: none !important; }
    .zoom-fade-enter-active, .zoom-fade-leave-active { transition: none !important; }
}
</style>
