<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import { useAdminProofsStore } from '../../../stores/adminProofs';
import { formatCOP, formatRelativeTime } from '../../../composables/useFormat';

const store = useAdminProofsStore();

const TABS = [
    { value: 'pendiente', label: 'PENDIENTE', variant: 'urgent' },
    { value: 'aprobado',  label: 'APROBADO',  variant: 'healthy' },
    { value: 'rechazado', label: 'RECHAZADO', variant: 'warn' },
    { value: 'expirado',  label: 'EXPIRADO',  variant: 'info' },
    { value: 'all',       label: 'TODOS',     variant: 'info' },
];

const STATUS_PILL_CLASS = {
    pendiente: 'pill--warn',
    aprobado:  'pill--success',
    rechazado: 'pill--urgent',
    expirado:  'pill--info',
};

const STATUS_LABEL = {
    pendiente: 'Pendiente',
    aprobado:  'Aprobado',
    rechazado: 'Rechazado',
    expirado:  'Expirado',
};

// Local search con debounce 300ms
const localSearch = ref(store.filters.search || '');
let debounceTimer = null;

watch(localSearch, (value) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        store.setSearch(value);
    }, 300);
});

onBeforeUnmount(() => clearTimeout(debounceTimer));

function selectTab(value) {
    store.setStatus(value);
}

function openProof(proof) {
    store.openDrawer(proof);
}

function refreshNow() {
    store.acknowledgeNew();
    store.fetchProofs();
}

function planLabel(plan) {
    if (!plan) return '—';
    const map = {
        rise: 'Plan Rise',
        metodo: 'Plan Método',
        ascenso: 'Plan Ascenso',
        elite: 'Plan Élite',
    };
    return map[plan] || plan;
}

function methodLabel(method) {
    if (!method) return '—';
    return method.charAt(0).toUpperCase() + method.slice(1);
}

const filteredProofs = computed(() => store.proofs);
</script>

<template>
  <section class="queue-card">
    <!-- Header con tabs + search + new badge -->
    <header class="queue-header">
      <div class="queue-tabs">
        <button
          v-for="t in TABS"
          :key="t.value"
          class="queue-tab"
          :class="{ 'queue-tab--active': store.filters.status === t.value }"
          @click="selectTab(t.value)"
        >
          {{ t.label }}
          <span
            v-if="t.value === 'pendiente' && store.pendingCount > 0"
            class="queue-tab-count"
          >{{ store.pendingCount > 99 ? '99+' : store.pendingCount }}</span>
        </button>
      </div>

      <div class="queue-search">
        <svg class="queue-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
          <circle cx="11" cy="11" r="7" />
          <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3" />
        </svg>
        <input
          v-model="localSearch"
          type="search"
          placeholder="Buscar por email del cliente"
          class="queue-search-input"
          aria-label="Buscar comprobante por email"
        />
      </div>
    </header>

    <!-- Banner: hay nuevos durante polling silent -->
    <Transition name="fade">
      <button
        v-if="store.newSinceLastRefresh > 0"
        class="queue-new-banner"
        @click="refreshNow"
      >
        <span class="queue-new-dot"></span>
        {{ store.newSinceLastRefresh }} {{ store.newSinceLastRefresh === 1 ? 'comprobante nuevo' : 'comprobantes nuevos' }}
        <span class="queue-new-cta">VER →</span>
      </button>
    </Transition>

    <!-- Loading skeleton -->
    <div v-if="store.loading && !store.proofs.length" class="queue-skeleton">
      <div v-for="i in 4" :key="i" class="queue-skeleton-card"></div>
    </div>

    <!-- Empty state editorial -->
    <div v-else-if="!filteredProofs.length" class="queue-empty">
      <div class="queue-empty-num">—</div>
      <p class="queue-empty-msg">
        <template v-if="store.filters.status === 'pendiente'">
          "Cola limpia. No hay comprobantes pendientes de revisión."
        </template>
        <template v-else-if="store.filters.search">
          "Sin resultados para esta búsqueda. La consistencia se verifica por email."
        </template>
        <template v-else>
          "Sin comprobantes en este estado. La cola se llena cuando los coaches envían pagos manuales."
        </template>
      </p>
      <button v-if="store.filters.status !== 'pendiente'" class="queue-empty-cta" @click="selectTab('pendiente')">
        VER PENDIENTES →
      </button>
    </div>

    <!-- Cola: cards desktop con thumb 16:9, lista compacta mobile -->
    <div v-else class="queue-list">
      <article
        v-for="proof in filteredProofs"
        :key="proof.id"
        class="proof-card"
        :class="{ 'proof-card--pending': proof.status === 'pendiente' }"
        tabindex="0"
        role="button"
        :aria-label="`Revisar comprobante de ${proof.clientName || proof.clientEmail}`"
        @click="openProof(proof)"
        @keydown.enter="openProof(proof)"
        @keydown.space.prevent="openProof(proof)"
      >
        <!-- Thumb placeholder con icono — el file URL se carga al abrir el drawer (lazy) -->
        <div class="proof-thumb">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
            <rect x="3" y="3" width="18" height="18" rx="2" />
            <circle cx="9" cy="9" r="1.5" />
            <path d="m21 15-5-5L5 21" />
          </svg>
          <span class="proof-thumb-mime">{{ proof.fileMime?.includes('pdf') ? 'PDF' : 'IMG' }}</span>
        </div>

        <div class="proof-meta">
          <header class="proof-meta-head">
            <h3 class="proof-name">{{ proof.clientName || 'Sin nombre' }}</h3>
            <span class="pill" :class="STATUS_PILL_CLASS[proof.status] || 'pill--info'">
              {{ STATUS_LABEL[proof.status] || proof.status }}
            </span>
          </header>

          <p class="proof-email">{{ proof.clientEmail || '—' }}</p>

          <div class="proof-data-row">
            <span class="proof-data-chip">
              <span class="proof-data-label">PLAN</span>
              <span class="proof-data-value">{{ planLabel(proof.plan) }}</span>
            </span>
            <span class="proof-data-chip">
              <span class="proof-data-label">MONTO</span>
              <span class="proof-data-value proof-data-value--amount">{{ formatCOP(proof.amount) }}</span>
            </span>
            <span class="proof-data-chip">
              <span class="proof-data-label">MÉTODO</span>
              <span class="proof-data-value">{{ methodLabel(proof.paymentMethod) }}</span>
            </span>
          </div>

          <footer class="proof-foot">
            <span class="proof-coach">{{ proof.coach?.name || 'Coach desconocido' }}</span>
            <span class="proof-time">{{ formatRelativeTime(proof.submittedAt) }}</span>
          </footer>
        </div>

        <button
          v-if="proof.status === 'pendiente'"
          type="button"
          class="proof-cta"
          @click.stop="openProof(proof)"
          aria-label="Abrir revisión"
        >
          REVISAR →
        </button>
      </article>
    </div>

    <!-- Pagination -->
    <nav
      v-if="store.pagination?.lastPage > 1"
      class="queue-pagination"
      aria-label="Paginación comprobantes"
    >
      <span class="queue-pagination-meta">
        Pág. {{ store.pagination.currentPage }} / {{ store.pagination.lastPage }}
        · {{ store.pagination.total }} comprobantes
      </span>
      <div class="queue-pagination-buttons">
        <button
          :disabled="store.pagination.currentPage <= 1"
          @click="store.goToPage(store.pagination.currentPage - 1)"
        >ANTERIOR</button>
        <button
          :disabled="store.pagination.currentPage >= store.pagination.lastPage"
          @click="store.goToPage(store.pagination.currentPage + 1)"
        >SIGUIENTE</button>
      </div>
    </nav>
  </section>
</template>

<style scoped>
.queue-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
@media (min-width: 1024px) {
    .queue-card { padding: 18px; gap: 16px; }
}

/* ── Header ─────────────────────────────────────────────────────────────── */
.queue-header {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
@media (min-width: 1024px) {
    .queue-header { flex-direction: row; align-items: center; justify-content: space-between; gap: 16px; }
}
.queue-tabs {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    scrollbar-width: none;
    padding-bottom: 2px;
}
.queue-tabs::-webkit-scrollbar { display: none; }

.queue-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 28px;
    padding: 0 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text-tertiary);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.queue-tab:hover { border-color: var(--color-wc-border-2, rgba(255,255,255,0.12)); color: var(--color-wc-text-secondary); }
.queue-tab--active {
    background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.1));
    border-color: rgba(220, 38, 38, 0.4);
    color: var(--color-wc-red-text, #F87171);
}
.queue-tab-count {
    background: rgba(220, 38, 38, 0.18);
    color: var(--color-wc-red-text, #F87171);
    border-radius: 999px;
    padding: 1px 6px;
    font-family: var(--font-data, sans-serif);
    font-weight: 700;
    font-size: 10px;
    letter-spacing: 0;
}

.queue-search {
    position: relative;
    width: 100%;
}
@media (min-width: 1024px) {
    .queue-search { width: 280px; }
}
.queue-search-icon {
    position: absolute;
    top: 50%;
    left: 12px;
    transform: translateY(-50%);
    width: 14px;
    height: 14px;
    color: var(--color-wc-text-tertiary);
    pointer-events: none;
}
.queue-search-input {
    width: 100%;
    height: 36px;
    padding: 0 12px 0 34px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text);
    font-family: var(--font-sans);
    font-size: 13px;
    transition: border-color 0.15s var(--ease-out, ease);
}
.queue-search-input:focus {
    outline: none;
    border-color: var(--color-wc-accent, #DC2626);
    background: rgba(255, 255, 255, 0.05);
}
.queue-search-input::placeholder { color: var(--color-wc-text-tertiary); }

/* ── New badge ──────────────────────────────────────────────────────────── */
.queue-new-banner {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 9px 14px;
    border-radius: 10px;
    background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.1));
    border: 1px solid rgba(220, 38, 38, 0.32);
    color: var(--color-wc-red-text, #F87171);
    font-family: var(--font-mono, monospace);
    font-size: 10px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    cursor: pointer;
    align-self: flex-start;
    transition: background 0.15s var(--ease-out, ease);
}
.queue-new-banner:hover { background: rgba(220, 38, 38, 0.16); }
.queue-new-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--color-wc-red-text, #F87171);
    animation: queue-pulse 1.6s ease-in-out infinite;
}
@keyframes queue-pulse { 0%, 100% { opacity: 1 } 50% { opacity: 0.4 } }
.queue-new-cta { font-weight: 700; letter-spacing: 0.22em; }

/* ── Skeleton ──────────────────────────────────────────────────────────── */
.queue-skeleton { display: flex; flex-direction: column; gap: 10px; }
.queue-skeleton-card {
    height: 96px;
    border-radius: 12px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary, #181818);
    animation: queue-skeleton 1.5s ease-in-out infinite;
}
@keyframes queue-skeleton { 0%, 100% { opacity: 0.6 } 50% { opacity: 0.9 } }

/* ── Empty state editorial ──────────────────────────────────────────────── */
.queue-empty {
    padding: 32px 12px 24px;
    text-align: center;
}
.queue-empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--color-wc-bg-tertiary, #181818);
    letter-spacing: 0.1em;
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}
.queue-empty-msg {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 13px;
    color: var(--color-wc-text-tertiary);
    line-height: 1.55;
    margin: 0 auto 14px;
    max-width: 380px;
    text-wrap: balance;
}
.queue-empty-cta {
    background: none;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    color: var(--color-wc-text-secondary);
    text-transform: uppercase;
    border-bottom: 1px solid var(--color-wc-border);
    padding: 0 0 4px;
    cursor: pointer;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.queue-empty-cta:hover {
    color: var(--color-wc-text);
    border-bottom-color: var(--color-wc-accent, #DC2626);
}

/* ── List ──────────────────────────────────────────────────────────────── */
.queue-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.proof-card {
    display: grid;
    grid-template-columns: 88px 1fr;
    gap: 12px;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid var(--color-wc-border);
    background: rgba(20, 20, 20, 0.5);
    cursor: pointer;
    transition: transform 0.18s var(--ease-out, ease), border-color 0.18s var(--ease-out, ease), background 0.18s var(--ease-out, ease);
    text-align: left;
    align-items: stretch;
}
@media (min-width: 1024px) {
    .proof-card {
        grid-template-columns: 140px 1fr 110px;
        padding: 14px;
        align-items: center;
    }
}
.proof-card:hover {
    border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.12));
    background: rgba(24, 24, 24, 0.7);
    transform: translateY(-1px);
}
.proof-card:active { transform: scale(0.99); }
.proof-card:focus-visible {
    outline: 2px solid var(--color-wc-accent, #DC2626);
    outline-offset: 2px;
}
.proof-card--pending {
    border-color: rgba(245, 158, 11, 0.22);
}
.proof-card--pending:hover { border-color: rgba(245, 158, 11, 0.4); }

.proof-thumb {
    width: 100%;
    aspect-ratio: 16 / 9;
    align-self: start;
    border-radius: 8px;
    background: var(--color-wc-bg-tertiary, #181818);
    border: 1px solid var(--color-wc-border);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-wc-text-tertiary);
    position: relative;
}
.proof-thumb svg { width: 28px; height: 28px; }
.proof-thumb-mime {
    position: absolute;
    bottom: 4px;
    right: 4px;
    font-family: var(--font-mono, monospace);
    font-size: 7px;
    letter-spacing: 0.16em;
    color: var(--color-wc-text-tertiary);
    background: rgba(0, 0, 0, 0.4);
    padding: 1px 5px;
    border-radius: 3px;
}

.proof-meta {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}
.proof-meta-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.proof-name {
    margin: 0;
    font-family: var(--font-sans);
    font-size: 14px;
    font-weight: 600;
    color: var(--color-wc-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.proof-email {
    margin: 0;
    font-family: var(--font-mono, monospace);
    font-size: 10px;
    color: var(--color-wc-text-tertiary);
    letter-spacing: 0.04em;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.proof-data-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 2px;
}
.proof-data-chip {
    display: inline-flex;
    flex-direction: column;
    gap: 1px;
    padding: 4px 8px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--color-wc-border);
    min-width: 0;
}
.proof-data-label {
    font-family: var(--font-mono, monospace);
    font-size: 7px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.proof-data-value {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 12px;
    font-weight: 600;
    color: var(--color-wc-text);
    font-variant-numeric: tabular-nums;
}
.proof-data-value--amount { color: var(--color-wc-text); }

.proof-foot {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    margin-top: 4px;
    align-items: baseline;
}
.proof-coach {
    font-family: var(--font-sans);
    font-size: 11px;
    color: var(--color-wc-text-secondary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.proof-time {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    white-space: nowrap;
}

.proof-cta {
    display: none;
}
@media (min-width: 1024px) {
    .proof-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 36px;
        padding: 0 14px;
        border-radius: 8px;
        background: rgba(220, 38, 38, 0.08);
        border: 1px solid rgba(220, 38, 38, 0.28);
        color: var(--color-wc-red-text, #F87171);
        font-family: var(--font-mono, monospace);
        font-size: 9px;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        cursor: pointer;
        transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
    }
    .proof-cta:hover {
        background: rgba(220, 38, 38, 0.16);
        border-color: rgba(220, 38, 38, 0.5);
    }
}

/* ── Pills (estado) ────────────────────────────────────────────────────── */
.pill {
    display: inline-block;
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    padding: 2px 6px;
    border-radius: 4px;
    white-space: nowrap;
    flex-shrink: 0;
}
.pill--urgent  { background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.1)); color: var(--color-wc-red-text, #F87171); }
.pill--warn    { background: var(--color-wc-amber-soft, rgba(245, 158, 11, 0.1)); color: var(--color-wc-amber-text, #FCD34D); }
.pill--success { background: var(--color-wc-green-soft, rgba(16, 185, 129, 0.1)); color: var(--color-wc-green-text, #34D399); }
.pill--info    { background: var(--color-wc-blue-soft, rgba(59, 130, 246, 0.1)); color: var(--color-wc-blue-text, #60A5FA); }

/* ── Pagination ────────────────────────────────────────────────────────── */
.queue-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding-top: 8px;
    border-top: 1px solid var(--color-wc-border);
}
.queue-pagination-meta {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.queue-pagination-buttons { display: flex; gap: 8px; }
.queue-pagination-buttons button {
    height: 30px;
    padding: 0 14px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    transition: border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.queue-pagination-buttons button:hover:not(:disabled) {
    border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.12));
    color: var(--color-wc-text);
}
.queue-pagination-buttons button:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

/* ── Transitions ───────────────────────────────────────────────────────── */
.fade-enter-active, .fade-leave-active { transition: opacity 0.18s var(--ease-out, ease); }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .queue-new-dot,
    .queue-skeleton-card,
    .proof-card { animation: none !important; transition: none !important; }
}
</style>
