<script setup>
import { ref, watch, onMounted } from 'vue';
import { useAdminAIGeneratorStore } from '../../../stores/adminAIGenerator';

const props = defineProps({
    open: { type: Boolean, default: false },
});
const emit = defineEmits(['close', 'load']);

const store = useAdminAIGeneratorStore();
const loadingId = ref(null);

watch(() => props.open, (v) => {
    if (v) store.fetchHistory();
});

onMounted(() => { if (props.open) store.fetchHistory(); });

const STATUS_LABEL = {
    streaming: 'Generando',
    completed: 'Borrador',
    aborted: 'Cancelado',
    discarded: 'Descartado',
    approved: 'Aprobado',
};

async function loadEntry(row) {
    loadingId.value = row.id;
    try {
        await store.loadHistoryDetail(row.id);
        emit('load', row);
        emit('close');
    } finally {
        loadingId.value = null;
    }
}

function fmtDate(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    const today = new Date();
    const same = d.toDateString() === today.toDateString();
    if (same) return d.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
    return d.toLocaleDateString('es-CO', { day: '2-digit', month: 'short' });
}
</script>

<template>
  <Teleport to="body">
    <Transition name="hist-fade">
      <div v-if="open" class="hist-overlay" @click.self="emit('close')">
        <aside class="hist-drawer" role="dialog" aria-label="Historial de generaciones">
          <header class="hist-head">
            <div>
              <p class="hist-eyebrow">HISTORIAL</p>
              <h2 class="hist-title">Últimas generaciones</h2>
              <p class="hist-tagline">"Todo draft queda registrado, asignado o descartado — la trazabilidad es disciplina."</p>
            </div>
            <button type="button" class="hist-close" @click="emit('close')" aria-label="Cerrar historial">×</button>
          </header>

          <div v-if="store.loadingHistory && !store.history.length" class="hist-skeleton">
            <div v-for="i in 6" :key="i" class="hist-skeleton-row"></div>
          </div>

          <div v-else-if="!store.history.length" class="hist-empty">
            <div class="hist-empty-num">—</div>
            <p class="hist-empty-msg">"Aún no hay generaciones. La primera que ejecutes aparecerá aquí."</p>
          </div>

          <ul v-else class="hist-list">
            <li v-for="row in store.history" :key="row.id" class="hist-item">
              <button type="button" class="hist-item-btn" :disabled="loadingId === row.id" @click="loadEntry(row)">
                <div class="hist-item-line">
                  <span class="hist-tag" :data-status="row.status">{{ STATUS_LABEL[row.status] || row.status }}</span>
                  <span class="hist-tag hist-tag--muted">{{ row.plan_type }}</span>
                  <span class="hist-date">{{ fmtDate(row.created_at) }}</span>
                </div>
                <div class="hist-item-line">
                  <span class="hist-detail" v-if="row.target_client?.name">{{ row.target_client.name }}</span>
                  <span class="hist-detail hist-detail--muted" v-else>Sin cliente target</span>
                  <span class="hist-detail hist-detail--muted">·</span>
                  <span class="hist-detail hist-detail--muted">{{ row.duration_weeks }} sem</span>
                  <span v-if="row.methodology" class="hist-detail hist-detail--muted">· {{ row.methodology }}</span>
                </div>
              </button>
            </li>
          </ul>
        </aside>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.hist-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(2px);
    z-index: 70;
    display: flex;
    justify-content: flex-end;
}
.hist-drawer {
    width: min(100vw, 420px);
    height: 100vh;
    background: var(--color-wc-bg-secondary);
    border-left: 1px solid var(--color-wc-border);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: -20px 0 40px rgba(0, 0, 0, 0.6);
}
.hist-head {
    padding: 18px 20px 14px;
    border-bottom: 1px solid var(--color-wc-border);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 14px;
}
.hist-eyebrow {
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.22em;
    color: var(--color-wc-text-tertiary);
    text-transform: uppercase;
    margin: 0 0 3px;
}
.hist-title {
    font-family: var(--font-display);
    font-size: 22px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--color-wc-text);
    margin: 0 0 4px;
    line-height: 1.05;
}
.hist-tagline {
    font-family: var(--font-editorial);
    font-style: italic;
    font-size: 11.5px;
    color: var(--color-wc-gold);
    margin: 0;
    line-height: 1.5;
    max-width: 30ch;
}
.hist-close {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 1px solid var(--color-wc-border);
    background: transparent;
    color: var(--color-wc-text-secondary);
    font-size: 18px;
    line-height: 1;
    cursor: pointer;
    transition: background 0.15s var(--ease-out);
}
.hist-close:hover { background: rgba(255, 255, 255, 0.04); color: var(--color-wc-text); }

.hist-skeleton { padding: 14px; display: flex; flex-direction: column; gap: 8px; }
.hist-skeleton-row {
    height: 56px;
    border-radius: 10px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary);
    animation: hist-pulse 1.5s ease-in-out infinite;
}
@keyframes hist-pulse { 0%,100% { opacity: 0.5; } 50% { opacity: 0.85; } }

.hist-empty { padding: 38px 22px 16px; text-align: center; }
.hist-empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--color-wc-bg-tertiary);
    letter-spacing: 0.1em;
    line-height: 1;
    user-select: none;
    margin-bottom: 12px;
}
.hist-empty-msg {
    font-family: var(--font-editorial);
    font-style: italic;
    font-size: 12.5px;
    color: var(--color-wc-text-tertiary);
    line-height: 1.6;
    margin: 0;
    max-width: 32ch;
    margin-inline: auto;
}

.hist-list {
    list-style: none;
    margin: 0;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    overflow-y: auto;
    flex: 1;
}
.hist-item-btn {
    width: 100%;
    text-align: left;
    background: rgba(17, 17, 17, 0.55);
    border: 1px solid var(--color-wc-border);
    border-radius: 10px;
    padding: 10px 12px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 4px;
    transition: background 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
    color: inherit;
}
.hist-item-btn:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.04);
    border-color: var(--color-wc-border-2);
}
.hist-item-btn:disabled { opacity: 0.6; cursor: wait; }
.hist-item-line { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }

.hist-tag {
    font-family: var(--font-mono);
    font-size: 8.5px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    padding: 2px 7px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.05);
    color: var(--color-wc-text);
}
.hist-tag--muted { background: rgba(255, 255, 255, 0.03); color: var(--color-wc-text-tertiary); }
.hist-tag[data-status="approved"]  { background: var(--color-wc-green-soft); color: var(--color-wc-green-text); }
.hist-tag[data-status="discarded"] { background: rgba(255, 255, 255, 0.04); color: var(--color-wc-text-tertiary); }
.hist-tag[data-status="aborted"]   { background: var(--color-wc-amber-soft); color: var(--color-wc-amber-text); }
.hist-tag[data-status="streaming"] { background: var(--color-wc-red-soft); color: var(--color-wc-red-text); }

.hist-date {
    margin-left: auto;
    font-family: var(--font-mono);
    font-size: 9px;
    color: var(--color-wc-text-tertiary);
    letter-spacing: 0.06em;
}
.hist-detail {
    font-family: var(--font-sans);
    font-size: 12.5px;
    color: var(--color-wc-text);
}
.hist-detail--muted { color: var(--color-wc-text-tertiary); }

.hist-fade-enter-active, .hist-fade-leave-active { transition: opacity 0.2s var(--ease-out); }
.hist-fade-enter-from,  .hist-fade-leave-to    { opacity: 0; }
</style>
