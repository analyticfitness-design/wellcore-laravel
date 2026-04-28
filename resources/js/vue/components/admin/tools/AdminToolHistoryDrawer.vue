<script setup>
import { ref, computed } from 'vue';
import { Teleport } from 'vue';
import { useAdminToolsStore } from '../../../stores/adminTools';
import { storeToRefs } from 'pinia';

const props = defineProps({
  open: { type: Boolean, default: false },
});
const emit = defineEmits(['close']);

const store = useAdminToolsStore();
const { history, loadingHistory } = storeToRefs(store);

// Detail modal for full output
const detailEntry = ref(null);

function close() { emit('close'); }
function handleBackdrop(e) {
  if (e.target === e.currentTarget) close();
}

function formatRelTime(iso) {
  if (! iso) return '';
  const diff = Math.floor((Date.now() - new Date(iso).getTime()) / 1000);
  if (diff < 60)   return `hace ${diff}s`;
  if (diff < 3600) return `hace ${Math.floor(diff / 60)}min`;
  if (diff < 86400) return `hace ${Math.floor(diff / 3600)}h`;
  return `hace ${Math.floor(diff / 86400)}d`;
}

function formatDuration(ms) {
  if (ms == null) return '';
  if (ms < 1000)  return `${ms}ms`;
  return `${(ms / 1000).toFixed(1)}s`;
}
</script>

<template>
  <Teleport to="body">
    <!-- Backdrop -->
    <div
      v-if="open"
      class="drawer-backdrop"
      @click="handleBackdrop"
      aria-hidden="true"
    />

    <!-- Drawer -->
    <aside
      v-if="open"
      class="history-drawer"
      :class="{ 'history-drawer--open': open }"
      role="complementary"
      aria-label="Historial de ejecuciones"
    >
      <div class="drawer-header">
        <div>
          <h2 class="drawer-title">HISTORIAL</h2>
          <p class="drawer-sub">Ultimas {{ history.length }} ejecuciones</p>
        </div>
        <button class="drawer-close" @click="close" aria-label="Cerrar historial">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loadingHistory" class="drawer-loading">
        <div v-for="i in 5" :key="i" class="drawer-skeleton" />
      </div>

      <!-- Empty -->
      <div v-else-if="!history.length" class="drawer-empty">
        <div class="drawer-empty-num">—</div>
        <p class="drawer-empty-msg">"Sin ejecuciones registradas. El historial se llena cuando se ejecuta la primera herramienta."</p>
      </div>

      <!-- List -->
      <ul v-else class="drawer-list" role="list">
        <li
          v-for="(entry, i) in history"
          :key="i"
          class="drawer-entry"
          @click="detailEntry = entry"
          role="button"
          :aria-label="`Ver detalle de ${entry.target_label}`"
          tabindex="0"
          @keydown.enter="detailEntry = entry"
        >
          <div class="entry-row-top">
            <span class="entry-tool">{{ entry.target_label }}</span>
            <span
              class="entry-status"
              :class="entry.status === 'success' ? 'status-ok' : 'status-fail'"
            >{{ entry.status === 'success' ? 'OK' : 'FAIL' }}</span>
          </div>
          <div class="entry-row-meta">
            <span class="entry-actor">{{ entry.actor_name }}</span>
            <span class="entry-dot">·</span>
            <span class="entry-time">{{ formatRelTime(entry.created_at) }}</span>
            <span v-if="entry.duration_ms != null" class="entry-dot">·</span>
            <span v-if="entry.duration_ms != null" class="entry-duration">{{ formatDuration(entry.duration_ms) }}</span>
          </div>
          <p v-if="entry.output_preview" class="entry-preview">{{ entry.output_preview }}</p>
        </li>
      </ul>
    </aside>
  </Teleport>

  <!-- Detail modal -->
  <Teleport to="body">
    <div v-if="detailEntry" class="detail-backdrop" @click.self="detailEntry = null">
      <div class="detail-modal" role="dialog" aria-modal="true" aria-label="Detalle de ejecucion">
        <div class="detail-header">
          <div>
            <h3 class="detail-title">{{ detailEntry.target_label }}</h3>
            <p class="detail-meta">{{ detailEntry.actor_name }} · {{ formatRelTime(detailEntry.created_at) }}</p>
          </div>
          <button class="drawer-close" @click="detailEntry = null" aria-label="Cerrar detalle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="detail-output">{{ detailEntry.output_preview || 'Sin output registrado.' }}</div>
        <div class="detail-footer">
          <span class="entry-status" :class="detailEntry.status === 'success' ? 'status-ok' : 'status-fail'">
            {{ detailEntry.status === 'success' ? 'COMPLETADO' : 'FALLIDO' }}
          </span>
          <span v-if="detailEntry.duration_ms != null" class="detail-dur">{{ formatDuration(detailEntry.duration_ms) }}</span>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
/* Backdrop */
.drawer-backdrop {
  position: fixed;
  inset: 0;
  z-index: 8000;
  background: rgba(0,0,0,0.5);
}

/* Drawer */
.history-drawer {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  z-index: 8100;
  width: min(380px, 100vw);
  background: var(--color-wc-bg-secondary);
  border-left: 1px solid var(--color-wc-border-2);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transform: translateX(100%);
  transition: transform 0.22s var(--ease-out);
}
.history-drawer--open { transform: translateX(0); }

.drawer-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 20px;
  border-bottom: 1px solid var(--color-wc-border);
  flex-shrink: 0;
}
.drawer-title {
  font-family: var(--font-display);
  font-size: 20px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--color-wc-text);
}
.drawer-sub {
  font-family: var(--font-mono);
  font-size: 9px;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
  margin-top: 2px;
}
.drawer-close {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: rgba(255,255,255,0.04);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-wc-text-secondary);
  flex-shrink: 0;
  transition: background 0.12s;
}
.drawer-close:hover { background: rgba(255,255,255,0.08); }
.drawer-close svg { width: 14px; height: 14px; }

.drawer-loading {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.drawer-skeleton {
  height: 64px;
  border-radius: 8px;
  background: var(--color-wc-bg-tertiary);
  animation: page-pulse 1.5s ease-in-out infinite;
}
@keyframes page-pulse {
  0%, 100% { opacity: 0.6; }
  50%       { opacity: 0.9; }
}

.drawer-empty {
  padding: 32px 16px;
  text-align: center;
}
.drawer-empty-num {
  font-family: var(--font-display);
  font-size: 48px;
  color: var(--color-wc-bg-tertiary);
  letter-spacing: 0.1em;
  margin-bottom: 10px;
}
.drawer-empty-msg {
  font-family: var(--font-editorial);
  font-style: italic;
  font-size: 11px;
  color: var(--color-wc-text-tertiary);
  line-height: 1.55;
}

.drawer-list {
  flex: 1;
  overflow-y: auto;
  padding: 8px 0;
  list-style: none;
  margin: 0;
}
.drawer-list::-webkit-scrollbar { width: 3px; }
.drawer-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 3px; }

.drawer-entry {
  padding: 12px 20px;
  border-bottom: 1px solid rgba(255,255,255,0.04);
  cursor: pointer;
  transition: background 0.1s;
}
.drawer-entry:hover { background: rgba(255,255,255,0.03); }

.entry-row-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}
.entry-tool {
  font-family: var(--font-sans);
  font-size: 12px;
  font-weight: 500;
  color: var(--color-wc-text);
}
.entry-status {
  font-family: var(--font-mono);
  font-size: 8px;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  padding: 2px 6px;
  border-radius: 4px;
  flex-shrink: 0;
}
.status-ok   { background: var(--color-wc-green-soft); color: var(--color-wc-green-text); }
.status-fail { background: var(--color-wc-red-soft);   color: var(--color-wc-red-text); }

.entry-row-meta {
  display: flex;
  align-items: center;
  gap: 5px;
  margin-top: 2px;
}
.entry-actor, .entry-time, .entry-duration {
  font-family: var(--font-mono);
  font-size: 9px;
  color: var(--color-wc-text-tertiary);
}
.entry-dot { color: var(--color-wc-text-tertiary); font-size: 8px; }
.entry-preview {
  margin-top: 5px;
  font-family: var(--font-mono);
  font-size: 9px;
  color: var(--color-wc-text-tertiary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Detail modal */
.detail-backdrop {
  position: fixed;
  inset: 0;
  z-index: 9200;
  background: rgba(0,0,0,0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.detail-modal {
  background: var(--color-wc-bg-secondary);
  border: 1px solid var(--color-wc-border-2);
  border-radius: 14px;
  width: 100%;
  max-width: 520px;
  max-height: 80vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  animation: modal-in 0.16s var(--ease-out);
}
@keyframes modal-in {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}
.detail-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 18px 18px 14px;
  border-bottom: 1px solid var(--color-wc-border);
}
.detail-title {
  font-family: var(--font-display);
  font-size: 18px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--color-wc-text);
}
.detail-meta {
  font-family: var(--font-mono);
  font-size: 9px;
  color: var(--color-wc-text-tertiary);
  margin-top: 2px;
  letter-spacing: 0.1em;
}
.detail-output {
  flex: 1;
  padding: 14px 18px;
  font-family: var(--font-mono);
  font-size: 10px;
  color: rgba(250,250,250,0.72);
  line-height: 1.7;
  white-space: pre-wrap;
  word-break: break-all;
  background: #0a0a0a;
}
.detail-footer {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 18px;
  border-top: 1px solid var(--color-wc-border);
}
.detail-dur {
  font-family: var(--font-mono);
  font-size: 9px;
  color: var(--color-wc-text-tertiary);
}

@media (prefers-reduced-motion: reduce) {
  .history-drawer { transition: none !important; }
  .detail-modal { animation: none !important; }
  .drawer-skeleton { animation: none !important; }
}
</style>
