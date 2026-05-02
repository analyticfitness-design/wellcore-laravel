<script setup>
import { ref, watch, nextTick } from 'vue';

const props = defineProps({
  lines:       { type: Array,   default: () => [] },
  isStreaming: { type: Boolean, default: false },
  status:      { type: String,  default: null },   // null | 'success' | 'failed'
  durationMs:  { type: Number,  default: null },
});

const terminalEl = ref(null);

watch(() => props.lines.length, async () => {
  await nextTick();
  if (terminalEl.value) {
    terminalEl.value.scrollTop = terminalEl.value.scrollHeight;
  }
});

</script>

<template>
  <div class="tool-terminal" ref="terminalEl" aria-label="Terminal de salida">
    <div class="tool-terminal-inner">
      <span
        v-for="(line, i) in lines"
        :key="i"
        class="tool-terminal-line"
        :class="{
          'line-error': line.startsWith('ERROR:'),
          'line-warn':  line.startsWith('AVISO:') || line.startsWith('Nota:') || line.startsWith('GUARDA'),
        }"
      >{{ line }}</span>

      <!-- cursor while streaming -->
      <span v-if="isStreaming" class="tool-terminal-cursor" aria-hidden="true">_</span>

      <!-- done status badge -->
      <div v-if="status && !isStreaming" class="tool-terminal-footer">
        <span class="tool-terminal-status" :class="status === 'success' ? 'status-ok' : 'status-fail'">
          {{ status === 'success' ? 'COMPLETADO' : 'FALLIDO' }}
        </span>
        <span v-if="durationMs !== null" class="tool-terminal-duration">{{ durationMs }}ms</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.tool-terminal {
  background: #0a0a0a;
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 10px;
  padding: 14px 16px;
  min-height: 140px;
  max-height: 340px;
  overflow-y: auto;
  font-family: var(--font-display);
  font-size: 11px;
  line-height: 1.7;
  color: rgba(250,250,250,0.72);
  scroll-behavior: smooth;
}
.tool-terminal::-webkit-scrollbar { width: 4px; }
.tool-terminal::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
.tool-terminal-inner { display: flex; flex-direction: column; }

.tool-terminal-line {
  white-space: pre-wrap;
  word-break: break-all;
}
.line-error { color: #F87171; }
.line-warn  { color: #FCD34D; }

.tool-terminal-cursor {
  display: inline-block;
  color: var(--c-accent);
  animation: term-blink 0.9s step-end infinite;
  margin-left: 2px;
}
@keyframes term-blink {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0; }
}

.tool-terminal-footer {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid rgba(255,255,255,0.06);
}
.tool-terminal-status {
  font-family: var(--font-display);
  font-size: 9px;
  letter-spacing: 1.6px;
  text-transform: uppercase;
  padding: 2px 8px;
  border-radius: var(--r-pill, 999px);
}
.status-ok   { background: rgba(16,185,129,0.1);  color: #34D399; }
.status-fail { background: var(--c-accent-dim);    color: #F87171; }
.tool-terminal-duration {
  font-family: var(--font-display);
  font-size: 9px;
  color: var(--c-text-3);
  letter-spacing: 0.8px;
}

@media (prefers-reduced-motion: reduce) {
  .tool-terminal-cursor { animation: none !important; }
}
</style>
