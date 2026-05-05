<script setup>
import { ref, onMounted } from 'vue';
import { useAdminClientDetailStore } from '../../../stores/adminClientDetail';
import { useApi } from '../../../composables/useApi';

const store = useAdminClientDetailStore();
const api   = useApi();

const loading = ref(false);
const error   = ref(null);
const events  = ref([]);
const total   = ref(0);

// ─── Type metadata ───────────────────────────────────────────────────────────
const TYPE_META = {
    workout:           { color: '#4ADE80',  label: 'Entrenamiento' },
    workout_abandoned: { color: '#DC2626',  label: 'Abandono' },
    checkin:           { color: '#60A5FA',  label: 'Check-in' },
    payment:           { color: '#FCD34D',  label: 'Pago' },
    login:             { color: null,       label: 'Acceso' },      // null → var(--c-text-3)
    access:            { color: null,       label: 'Acceso' },
    message:           { color: '#A78BFA',  label: 'Mensaje' },
};

function typeMeta(type) {
    return TYPE_META[type] || { color: null, label: type };
}

// ─── Date formatting ─────────────────────────────────────────────────────────
const TODAY_YEAR = new Date().getFullYear();

function formatDate(isoString) {
    if (!isoString) return '';
    const d = new Date(isoString);
    if (isNaN(d)) return isoString;
    const opts = { day: '2-digit', month: 'short' };
    if (d.getFullYear() !== TODAY_YEAR) opts.year = 'numeric';
    return d.toLocaleDateString('es-CO', opts);
}

// ─── Fetch ───────────────────────────────────────────────────────────────────
async function fetchActivity() {
    if (!store.clientId) return;
    loading.value = true;
    error.value   = null;
    try {
        const { data } = await api.get(`/api/v/admin/clients/${store.clientId}/activity`);
        events.value = data.events ?? [];
        total.value  = data.total  ?? events.value.length;
    } catch (e) {
        error.value = e?.response?.data?.message || 'No se pudo cargar la actividad.';
    } finally {
        loading.value = false;
    }
}

onMounted(fetchActivity);
</script>

<template>
  <div class="activity-panel">

    <!-- Loading -->
    <div v-if="loading" class="skeleton-stack">
      <div class="skeleton sk-row" />
      <div class="skeleton sk-row" />
      <div class="skeleton sk-row sk-row--short" />
      <div class="skeleton sk-row" />
      <div class="skeleton sk-row sk-row--short" />
    </div>

    <!-- Error -->
    <div v-else-if="error" class="state-card state-card--error">
      <span class="state-glyph">!</span>
      <p class="state-msg">{{ error }}</p>
      <button type="button" class="btn-retry" @click="fetchActivity">REINTENTAR</button>
    </div>

    <!-- Empty -->
    <div v-else-if="events.length === 0" class="state-card">
      <span class="state-glyph state-glyph--muted">—</span>
      <p class="state-msg">Sin actividad registrada para este cliente.</p>
    </div>

    <!-- Timeline -->
    <template v-else>

      <!-- Total pill -->
      <div class="total-row">
        <span class="total-label">TOTAL DE EVENTOS</span>
        <span class="total-count">{{ total }}</span>
      </div>

      <!-- Feed -->
      <ol class="timeline" role="list">
        <li
          v-for="(ev, idx) in events"
          :key="idx"
          class="tl-item"
        >
          <!-- Connector line -->
          <div class="tl-line-wrap" aria-hidden="true">
            <div
              class="tl-dot"
              :style="typeMeta(ev.type).color
                ? { background: typeMeta(ev.type).color, boxShadow: `0 0 0 3px ${typeMeta(ev.type).color}22` }
                : { background: 'var(--c-text-3)', boxShadow: '0 0 0 3px rgba(160,160,160,0.15)' }"
            />
            <div v-if="idx < events.length - 1" class="tl-connector" />
          </div>

          <!-- Content -->
          <div class="tl-content">
            <div class="tl-head">
              <span class="tl-title">{{ ev.title }}</span>
              <span class="tl-date">{{ ev.date || formatDate(ev.date_iso) }}</span>
            </div>
            <p v-if="ev.desc" class="tl-desc">{{ ev.desc }}</p>

            <!-- Meta pill if present -->
            <div v-if="ev.meta && Object.keys(ev.meta).length" class="tl-meta-row">
              <span
                v-for="(val, key) in ev.meta"
                :key="key"
                class="tl-meta-pill"
              >{{ String(key).toUpperCase() }}: {{ val }}</span>
            </div>
          </div>
        </li>
      </ol>

    </template>

  </div>
</template>

<style scoped>
.activity-panel { display: flex; flex-direction: column; gap: 12px; }

/* ── Skeletons ──────────────────────────────────────────────────────────── */
.skeleton-stack { display: flex; flex-direction: column; gap: 8px; }
.skeleton {
    border-radius: var(--r-md, 16px);
    background: var(--c-surface-2);
    animation: sk-pulse 1.5s ease-in-out infinite;
}
.sk-row       { height: 62px; }
.sk-row--short { height: 48px; }
@keyframes sk-pulse { 0%,100%{opacity:.55} 50%{opacity:.85} }
@media (prefers-reduced-motion: reduce) { .skeleton { animation: none; opacity: .55; } }

/* ── Error / Empty state card ───────────────────────────────────────────── */
.state-card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: var(--c-surface-2);
    padding: 32px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    text-align: center;
}
.state-card--error { border-color: rgba(220,38,38,.3); background: rgba(220,38,38,.04); }

.state-glyph {
    font-family: var(--font-display);
    font-size: 36px;
    color: #F87171;
    line-height: 1;
}
.state-glyph--muted { color: var(--c-text-3); }

.state-msg {
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--c-text-2);
    margin: 0;
}

.btn-retry {
    background: transparent;
    border: 1px solid var(--c-border);
    color: #F87171;
    padding: 6px 14px;
    border-radius: var(--r-sm, 12px);
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    cursor: pointer;
    transition: background .12s;
}
.btn-retry:hover { background: rgba(255,255,255,.04); }

/* ── Total row ──────────────────────────────────────────────────────────── */
.total-row {
    display: flex;
    align-items: center;
    gap: 8px;
}
.total-label {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    color: var(--c-text-3);
}
.total-count {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 13px;
    font-weight: 600;
    color: var(--c-text);
}

/* ── Timeline ───────────────────────────────────────────────────────────── */
.timeline {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0;
}

.tl-item {
    display: grid;
    grid-template-columns: 28px 1fr;
    gap: 0 12px;
}

/* Dot + connector column */
.tl-line-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 4px;
}
.tl-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}
.tl-connector {
    width: 1px;
    flex: 1;
    min-height: 18px;
    background: var(--c-border);
    margin-top: 4px;
    margin-bottom: 4px;
}

/* Content column */
.tl-content {
    padding-bottom: 18px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.tl-item:last-child .tl-content { padding-bottom: 4px; }

.tl-head {
    display: flex;
    align-items: baseline;
    gap: 8px;
    flex-wrap: wrap;
}
.tl-title {
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 600;
    color: var(--c-text);
    line-height: 1.4;
}
.tl-date {
    font-family: var(--font-sans);
    font-size: 11px;
    color: var(--c-text-3);
    white-space: nowrap;
    margin-left: auto;
}

.tl-desc {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--c-text-2);
    margin: 0;
    line-height: 1.5;
}

/* Meta pills */
.tl-meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 2px;
}
.tl-meta-pill {
    font-family: var(--font-sans);
    font-size: 10px;
    font-weight: 500;
    padding: 2px 7px;
    border-radius: var(--r-pill, 999px);
    border: 1px solid var(--c-border);
    color: var(--c-text-3);
    background: var(--c-surface-2);
}
</style>
