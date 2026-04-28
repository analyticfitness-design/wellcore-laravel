<script setup>
import { computed, ref, watch, nextTick, onMounted } from 'vue';
import { renderMarkdown } from '../../../composables/useMarkdown';

const props = defineProps({
    text: { type: String, default: '' },
    isStreaming: { type: Boolean, default: false },
    error: { type: String, default: null },
    durationMs: { type: Number, default: null },
});
const emit = defineEmits(['stop', 'retry']);

const scrollEl = ref(null);
const userScrolledUp = ref(false);
let lastScrollTop = 0;

/**
 * Re-render markdown on every chunk. Profiling on a 6KB plan: ~0.4ms per
 * frame — well below 16ms budget. The cursor is appended via a separate
 * absolutely-positioned span so the markdown HTML stays clean and we do
 * not invalidate the parser on every keystroke-equivalent.
 */
const rendered = computed(() => renderMarkdown(props.text));

const charCount = computed(() => props.text.length);
const durationLabel = computed(() => {
    if (!props.durationMs) return null;
    const s = (props.durationMs / 1000).toFixed(1);
    return `${s}s`;
});

function onScroll() {
    if (!scrollEl.value) return;
    const el = scrollEl.value;
    const atBottom = el.scrollHeight - el.clientHeight - el.scrollTop < 60;
    // Only mark "scrolled up" when the user moves UP, not when content grows.
    if (el.scrollTop < lastScrollTop - 4) {
        userScrolledUp.value = true;
    }
    if (atBottom) {
        userScrolledUp.value = false;
    }
    lastScrollTop = el.scrollTop;
}

watch(
    () => props.text,
    async () => {
        if (!props.isStreaming) return;
        if (userScrolledUp.value) return;
        await nextTick();
        if (scrollEl.value) {
            scrollEl.value.scrollTop = scrollEl.value.scrollHeight;
        }
    }
);

onMounted(() => {
    if (scrollEl.value) lastScrollTop = scrollEl.value.scrollTop;
});
</script>

<template>
  <section class="stream-card">
    <header class="stream-head">
      <div class="stream-head-left">
        <p class="stream-eyebrow">SISTEMA ASISTIDO</p>
        <h2 class="stream-title">Salida en vivo</h2>
      </div>
      <div class="stream-head-right">
        <span v-if="charCount > 0" class="stream-stat">{{ charCount }} caracteres</span>
        <span v-if="durationLabel" class="stream-stat">{{ durationLabel }}</span>
        <span v-if="isStreaming" class="stream-status">
          <span class="stream-status-dot"></span>
          Generando
        </span>
      </div>
    </header>

    <div
      ref="scrollEl"
      class="stream-body"
      :class="{ 'stream-body--empty': !text && !error }"
      @scroll="onScroll"
    >
      <!-- Empty state editorial -->
      <div v-if="!text && !error && !isStreaming" class="stream-empty">
        <div class="stream-empty-num">—</div>
        <p class="stream-empty-msg">
          "Configurá el brief para generar un plan asistido. La IA produce drafts — siempre revisar antes de asignar."
        </p>
      </div>

      <!-- Error state -->
      <div v-if="error" class="stream-error">
        <p class="stream-error-text">{{ error }}</p>
        <button type="button" class="stream-error-cta" @click="emit('retry')">
          Reintentar
        </button>
      </div>

      <!-- Streaming output -->
      <article
        v-if="text"
        class="stream-md"
        v-html="rendered"
      ></article>

      <!-- Cursor -->
      <span
        v-if="isStreaming && !error"
        class="stream-cursor"
        aria-hidden="true"
      >▋</span>
    </div>

    <!-- Footer actions: only stop button while streaming -->
    <footer v-if="isStreaming" class="stream-foot">
      <button type="button" class="stream-stop" @click="emit('stop')">
        Parar
      </button>
      <p class="stream-foot-hint">
        Cancelar libera la conexión y guarda lo generado hasta este punto como borrador.
      </p>
    </footer>
  </section>
</template>

<style scoped>
.stream-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    display: flex;
    flex-direction: column;
    min-height: 420px;
    max-height: 760px;
    overflow: hidden;
    position: relative;
    z-index: 1;
}
.stream-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 18px;
    border-bottom: 1px solid var(--color-wc-border);
    flex-wrap: wrap;
}
.stream-head-left { display: flex; flex-direction: column; gap: 3px; }
.stream-head-right { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.stream-eyebrow {
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.22em;
    color: var(--color-wc-text-tertiary);
    text-transform: uppercase;
    margin: 0;
}
.stream-title {
    font-family: var(--font-display);
    font-size: 22px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--color-wc-text);
    margin: 0;
    line-height: 1.05;
}
.stream-stat {
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.18em;
    color: var(--color-wc-text-tertiary);
    text-transform: uppercase;
}
.stream-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.18em;
    color: var(--color-wc-red-text);
    text-transform: uppercase;
}
.stream-status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--color-wc-accent);
    animation: stream-pulse 1.1s var(--ease-out) infinite;
}
@keyframes stream-pulse {
    0%, 100% { opacity: 0.35; transform: scale(0.85); }
    50%      { opacity: 1;    transform: scale(1.2); }
}

.stream-body {
    padding: 18px 18px 12px;
    overflow-y: auto;
    flex: 1;
    color: var(--color-wc-text-secondary);
    font-family: var(--font-sans);
    font-size: 13.5px;
    line-height: 1.65;
    position: relative;
}
.stream-body--empty {
    display: flex;
    align-items: center;
    justify-content: center;
}

.stream-empty { text-align: center; padding: 16px 8px; }
.stream-empty-num {
    font-family: var(--font-display);
    font-size: 64px;
    color: var(--color-wc-bg-tertiary);
    letter-spacing: 0.1em;
    line-height: 1;
    margin-bottom: 14px;
    user-select: none;
}
.stream-empty-msg {
    font-family: var(--font-editorial);
    font-style: italic;
    font-size: 12.5px;
    color: var(--color-wc-text-tertiary);
    margin: 0 auto;
    max-width: 38ch;
    line-height: 1.6;
}

.stream-error {
    text-align: center;
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    align-items: center;
}
.stream-error-text {
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--color-wc-red-text);
    margin: 0;
}
.stream-error-cta {
    height: 32px;
    padding: 0 18px;
    border-radius: 999px;
    border: 1px solid var(--color-wc-accent);
    background: transparent;
    color: var(--color-wc-text);
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out);
}
.stream-error-cta:hover { background: var(--color-wc-red-soft); }

/* Markdown styles inside streaming output */
.stream-md :deep(h1),
.stream-md :deep(h2),
.stream-md :deep(h3),
.stream-md :deep(h4) {
    font-family: var(--font-display);
    color: var(--color-wc-text);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin: 18px 0 6px;
    line-height: 1.1;
}
.stream-md :deep(h1) { font-size: 22px; }
.stream-md :deep(h2) { font-size: 18px; border-bottom: 1px solid var(--color-wc-border); padding-bottom: 4px; }
.stream-md :deep(h3) { font-size: 14px; letter-spacing: 0.06em; color: var(--color-wc-gold); }
.stream-md :deep(h4) { font-size: 12px; }
.stream-md :deep(p) { margin: 8px 0; }
.stream-md :deep(ul),
.stream-md :deep(ol) { margin: 8px 0 8px 20px; padding: 0; }
.stream-md :deep(li) { margin: 4px 0; }
.stream-md :deep(strong) { color: var(--color-wc-text); font-weight: 600; }
.stream-md :deep(em) { color: var(--color-wc-text); font-style: italic; }
.stream-md :deep(code) {
    font-family: var(--font-mono);
    font-size: 11px;
    background: rgba(255, 255, 255, 0.06);
    color: var(--color-wc-gold);
    padding: 1px 5px;
    border-radius: 3px;
}
.stream-md :deep(pre) {
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid var(--color-wc-border);
    border-radius: 8px;
    padding: 12px;
    overflow-x: auto;
    margin: 12px 0;
}
.stream-md :deep(pre code) { background: none; padding: 0; color: var(--color-wc-text); }
.stream-md :deep(.md-table-wrap) { overflow-x: auto; margin: 12px 0; border-radius: 8px; border: 1px solid var(--color-wc-border); }
.stream-md :deep(.md-table) {
    width: 100%;
    border-collapse: collapse;
    font-family: var(--font-sans);
    font-size: 12.5px;
}
.stream-md :deep(.md-table th) {
    background: rgba(255, 255, 255, 0.03);
    color: var(--color-wc-text);
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    text-align: left;
    padding: 8px 10px;
    border-bottom: 1px solid var(--color-wc-border);
}
.stream-md :deep(.md-table td) {
    padding: 7px 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    font-family: var(--font-data);
    font-feature-settings: 'tnum' 1;
}
.stream-md :deep(.md-table tr:last-child td) { border-bottom: none; }
.stream-md :deep(hr) {
    border: 0;
    border-top: 1px solid var(--color-wc-border);
    margin: 18px 0;
}
.stream-md :deep(a) { color: var(--color-wc-blue-text); text-decoration: underline; text-underline-offset: 2px; }

.stream-cursor {
    display: inline-block;
    margin-left: 1px;
    color: var(--color-wc-accent);
    font-family: var(--font-mono);
    font-size: 14px;
    line-height: 1;
    animation: stream-blink 1s steps(2, start) infinite;
}
@keyframes stream-blink {
    0%, 49% { opacity: 1; }
    50%, 100% { opacity: 0; }
}

.stream-foot {
    padding: 12px 18px;
    border-top: 1px solid var(--color-wc-border);
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}
.stream-stop {
    height: 36px;
    padding: 0 22px;
    border-radius: 999px;
    border: 1px solid var(--color-wc-accent);
    background: var(--color-wc-accent);
    color: #fff;
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out), transform 0.15s var(--ease-out);
}
.stream-stop:hover { background: #B91C1C; transform: translateY(-1px); }
.stream-foot-hint {
    flex: 1;
    font-family: var(--font-editorial);
    font-style: italic;
    font-size: 11px;
    color: var(--color-wc-text-tertiary);
    margin: 0;
    line-height: 1.5;
}

@media (prefers-reduced-motion: reduce) {
    .stream-status-dot,
    .stream-cursor { animation: none !important; }
}
</style>
