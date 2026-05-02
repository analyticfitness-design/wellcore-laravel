<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminGreeting from '../../components/admin/dashboard/AdminGreeting.vue';
import AdminRequestCard from '../../components/admin/client-requests/AdminRequestCard.vue';
import AdminRequestDetailDrawer from '../../components/admin/client-requests/AdminRequestDetailDrawer.vue';
import AdminRequestRejectModal from '../../components/admin/client-requests/AdminRequestRejectModal.vue';
import { useAdminClientRequestsStore } from '../../stores/adminClientRequests';

const store = useAdminClientRequestsStore();

let debounceTimer = null;

// ─── Approve confirm state ─────────────────────────────────────────────────────
const approveLoading = ref(false);
const approveError   = ref('');

async function confirmApprove() {
    if (!store.approveTarget) return;
    approveLoading.value = true;
    approveError.value   = '';
    try {
        await store.doApprove(store.approveTarget.id);
    } catch (err) {
        approveError.value = err.response?.data?.error || 'No se pudo aprobar la solicitud.';
    } finally {
        approveLoading.value = false;
    }
}

// ─── Filter debounce ───────────────────────────────────────────────────────────
watch(() => store.search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => store.fetch(), 300);
});
watch(() => [store.statusFilter, store.actionFilter, store.coachFilter], () => {
    store.fetch();
});

// ─── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
    store.fetch();
    store.startPolling();
});
onBeforeUnmount(() => {
    store.stopPolling();
    clearTimeout(debounceTimer);
});

const ACTION_OPTIONS = [
    { value: '',           label: 'Todas las acciones' },
    { value: 'delete',     label: 'Eliminar cliente'   },
    { value: 'deactivate', label: 'Desactivar cliente' },
    { value: 'edit',       label: 'Editar datos'       },
];
</script>

<template>
  <AdminLayout>
    <div class="requests-page">

      <!-- Greeting -->
      <AdminGreeting
        greeting="Solicitudes de Coaches"
        :critical-alerts="store.pendingCount"
      />

      <!-- KPI counters -->
      <div class="kpi-row" role="group" aria-label="Filtros por estado">
        <button
          class="kpi-card"
          :class="{ 'kpi-card--active kpi-card--amber': store.statusFilter === 'pendiente' }"
          @click="store.statusFilter = 'pendiente'"
          :aria-pressed="store.statusFilter === 'pendiente'"
        >
          <span class="kpi-num kpi-num--amber">{{ store.counts.pendiente ?? 0 }}</span>
          <span class="kpi-label">PENDIENTES</span>
        </button>
        <button
          class="kpi-card"
          :class="{ 'kpi-card--active kpi-card--green': store.statusFilter === 'aprobado' }"
          @click="store.statusFilter = 'aprobado'"
          :aria-pressed="store.statusFilter === 'aprobado'"
        >
          <span class="kpi-num kpi-num--green">{{ store.counts.aprobado ?? 0 }}</span>
          <span class="kpi-label">APROBADAS</span>
        </button>
        <button
          class="kpi-card"
          :class="{ 'kpi-card--active kpi-card--red': store.statusFilter === 'rechazado' }"
          @click="store.statusFilter = 'rechazado'"
          :aria-pressed="store.statusFilter === 'rechazado'"
        >
          <span class="kpi-num kpi-num--red">{{ store.counts.rechazado ?? 0 }}</span>
          <span class="kpi-label">RECHAZADAS</span>
        </button>
        <button
          class="kpi-card"
          :class="{ 'kpi-card--active kpi-card--accent': store.statusFilter === '' }"
          @click="store.statusFilter = ''"
          :aria-pressed="store.statusFilter === ''"
        >
          <span class="kpi-num">{{ store.totalCount }}</span>
          <span class="kpi-label">TODAS</span>
        </button>
      </div>

      <!-- Filters row -->
      <div class="filters-row" role="search">
        <!-- Search -->
        <div class="search-wrap">
          <svg class="search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input
            v-model="store.search"
            type="search"
            placeholder="Buscar coach o cliente…"
            class="search-input"
            aria-label="Buscar solicitudes"
          />
        </div>

        <!-- Action filter chips -->
        <div class="chips-wrap" role="group" aria-label="Filtrar por tipo de acción">
          <button
            v-for="opt in ACTION_OPTIONS"
            :key="opt.value"
            type="button"
            class="filter-chip"
            :class="{ 'filter-chip--active': store.actionFilter === opt.value }"
            @click="store.actionFilter = opt.value"
          >{{ opt.label.toUpperCase() }}</button>
        </div>

        <!-- Coach filter -->
        <select
          v-if="store.uniqueCoaches.length"
          v-model="store.coachFilter"
          class="coach-select"
          aria-label="Filtrar por coach"
        >
          <option value="">Todos los coaches</option>
          <option v-for="c in store.uniqueCoaches" :key="c.id" :value="c.id">
            {{ c.name }}
          </option>
        </select>
      </div>

      <!-- Loading skeleton -->
      <div v-if="store.loading && !store.requests.length" class="skeleton-list" aria-label="Cargando solicitudes…">
        <div v-for="i in 4" :key="i" class="skeleton-card"></div>
      </div>

      <!-- Empty state editorial -->
      <div v-else-if="!store.requests.length" class="empty-state">
        <div class="empty-num" aria-hidden="true">—</div>
        <p class="empty-msg">
          "Sin solicitudes pendientes.
          Los coaches están operando con autonomía. Buen sistema."
        </p>
        <button
          type="button"
          class="empty-cta"
          @click="store.statusFilter = ''"
        >VER HISTÓRICO →</button>
      </div>

      <!-- Request list -->
      <div v-else class="requests-list" role="list" aria-label="Solicitudes de coaches">
        <AdminRequestCard
          v-for="req in store.requests"
          :key="req.id"
          :request="req"
        />
      </div>
    </div>

    <!-- Detail drawer -->
    <AdminRequestDetailDrawer />

    <!-- Reject modal -->
    <AdminRequestRejectModal />

    <!-- Approve confirm overlay -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div
          v-if="store.approveOpen"
          class="approve-overlay"
          role="dialog"
          aria-modal="true"
          aria-label="Confirmar aprobación"
          @click.self="store.closeApprove()"
        >
          <Transition name="modal-scale">
            <div v-if="store.approveOpen" class="approve-panel">
              <header class="approve-head">
                <h3 class="approve-title">APROBAR SOLICITUD</h3>
              </header>
              <div class="approve-body">
                <div v-if="store.approveTarget" class="approve-info">
                  <div class="a-pair">
                    <span class="a-label">COACH</span>
                    <span class="a-val">{{ store.approveTarget.coach_name || '—' }}</span>
                  </div>
                  <div class="a-pair">
                    <span class="a-label">CLIENTE</span>
                    <span class="a-val">{{ store.approveTarget.client_name || '—' }}</span>
                  </div>
                </div>
                <p class="approve-warning">
                  Esta acción se ejecutará de inmediato y notificará al coach.
                  Verifica que la solicitud es válida antes de confirmar.
                </p>
                <p v-if="approveError" role="alert" class="approve-error">{{ approveError }}</p>
              </div>
              <footer class="approve-footer">
                <button
                  type="button"
                  class="a-cancel"
                  @click="store.closeApprove()"
                  :disabled="approveLoading"
                >Cancelar</button>
                <button
                  type="button"
                  class="a-confirm"
                  @click="confirmApprove"
                  :disabled="approveLoading"
                  :aria-busy="approveLoading"
                >{{ approveLoading ? 'Aprobando…' : 'Confirmar aprobación' }}</button>
              </footer>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- Toast -->
    <Teleport to="body">
      <Transition name="toast-up">
        <div
          v-if="store.toast.show"
          class="toast"
          :class="store.toast.type === 'error' ? 'toast--error' : 'toast--success'"
          role="status"
          aria-live="polite"
        >{{ store.toast.message }}</div>
      </Transition>
    </Teleport>
  </AdminLayout>
</template>

<style scoped>
.requests-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* KPI counters */
.kpi-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}
@media (min-width: 640px) { .kpi-row { grid-template-columns: repeat(4, 1fr); } }

.kpi-card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 14px 16px;
    text-align: left;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 6px;
    transition: border-color 0.15s var(--ease-out), background 0.15s var(--ease-out);
}
.kpi-card:hover { border-color: rgba(255,255,255,0.12); }
.kpi-card--active { border-width: 1px; }
.kpi-card--amber.kpi-card--active { border-color: rgba(245,158,11,0.5); background: rgba(245,158,11,0.06); }
.kpi-card--green.kpi-card--active  { border-color: rgba(16,185,129,0.5); background: rgba(16,185,129,0.06); }
.kpi-card--red.kpi-card--active    { border-color: rgba(220,38,38,0.5);  background: rgba(220,38,38,0.06); }
.kpi-card--accent.kpi-card--active { border-color: rgba(220,38,38,0.4);  background: rgba(220,38,38,0.05); }

.kpi-num {
    font-family: var(--font-display);
    font-feature-settings: 'tnum' 1;
    font-size: 26px;
    font-weight: 700;
    color: var(--c-text);
    line-height: 1;
}
.kpi-num--amber { color: #FCD34D; }
.kpi-num--green { color: #34D399; }
.kpi-num--red   { color: #F87171; }

.kpi-label {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    color: var(--c-text-3);
    display: block;
}

/* Filters */
.filters-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}
.search-wrap {
    position: relative;
    flex: 1;
    min-width: 200px;
}
.search-icon {
    position: absolute;
    left: 11px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--c-text-3);
    pointer-events: none;
}
.search-input {
    width: 100%;
    height: 36px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255,255,255,0.03);
    color: var(--c-text);
    font-family: var(--font-sans);
    font-size: 13px;
    padding: 0 12px 0 34px;
    transition: border-color 0.15s var(--ease-out);
    box-sizing: border-box;
}
.search-input::placeholder { color: var(--c-text-3); }
.search-input:focus { outline: none; border-color: var(--c-accent); }

.chips-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.filter-chip {
    height: 28px;
    padding: 0 10px;
    border-radius: var(--r-pill, 999px);
    border: 1px solid var(--c-border);
    background: transparent;
    color: var(--c-text-3);
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    cursor: pointer;
    transition: color 0.15s var(--ease-out), background 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
    white-space: nowrap;
}
.filter-chip:hover { color: var(--c-text-2); border-color: rgba(255,255,255,0.12); }
.filter-chip--active {
    background: var(--c-accent-dim);
    border-color: var(--c-accent);
    color: #F87171;
}

.coach-select {
    height: 36px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255,255,255,0.03);
    color: var(--c-text);
    font-family: var(--font-sans);
    font-size: 12px;
    padding: 0 10px;
    cursor: pointer;
    transition: border-color 0.15s var(--ease-out);
}
.coach-select:focus { outline: none; border-color: var(--c-accent); }

/* Skeleton */
.skeleton-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.skeleton-card {
    height: 110px;
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: var(--c-surface-2);
    animation: page-pulse 1.5s ease-in-out infinite;
}
@keyframes page-pulse {
    0%, 100% { opacity: 0.6; }
    50%       { opacity: 0.9; }
}

/* Empty state */
.empty-state {
    padding: 32px 12px 24px;
    text-align: center;
    border-radius: var(--r-md, 16px);
    border: 1px dashed var(--c-border);
    background: rgba(255,255,255,0.01);
}
.empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--c-surface-2);
    letter-spacing: 0.1em;
    line-height: 1;
    margin-bottom: 14px;
    user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 13px;
    color: var(--c-text-3);
    line-height: 1.6;
    margin: 0 0 18px;
    text-wrap: balance;
    white-space: pre-line;
}
.empty-cta {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    color: var(--c-text-2);
    background: transparent;
    border: none;
    border-bottom: 1px solid var(--c-border);
    padding-bottom: 4px;
    cursor: pointer;
    text-transform: uppercase;
    transition: color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
}
.empty-cta:hover {
    color: var(--c-text);
    border-bottom-color: var(--c-accent);
}

/* List */
.requests-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Approve overlay */
.approve-overlay {
    position: fixed;
    inset: 0;
    z-index: 110;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    background: rgba(0,0,0,0.72);
    backdrop-filter: blur(8px);
}
.approve-panel {
    width: 100%;
    max-width: 400px;
    border-radius: 16px;
    border: 1px solid var(--c-border);
    background: var(--c-surface);
    overflow: hidden;
    box-shadow: 0 24px 64px rgba(0,0,0,0.6);
}
.approve-head {
    padding: 18px 20px 14px;
    border-bottom: 1px solid var(--c-border);
}
.approve-title {
    font-family: var(--font-display);
    font-size: 18px;
    letter-spacing: 0.04em;
    color: var(--c-text);
    margin: 0;
}
.approve-body {
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.approve-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.a-pair {
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255,255,255,0.02);
    padding: 8px 10px;
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.a-label {
    font-family: var(--font-display);
    font-size: 7px;
    letter-spacing: 1.6px;
    color: var(--c-text-3);
}
.a-val {
    font-family: var(--font-sans);
    font-size: 12px;
    font-weight: 500;
    color: var(--c-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.approve-warning {
    font-family: var(--font-sans);
    font-size: 12px;
    line-height: 1.55;
    color: #FCD34D;
    border-radius: var(--r-sm, 12px);
    border: 1px solid rgba(245,158,11,0.22);
    background: rgba(245,158,11,0.1);
    padding: 10px 12px;
    margin: 0;
}
.approve-error {
    font-family: var(--font-sans);
    font-size: 12px;
    color: #F87171;
    border-radius: var(--r-sm, 12px);
    border: 1px solid rgba(220,38,38,0.2);
    background: var(--c-accent-dim);
    padding: 10px 12px;
    margin: 0;
}
.approve-footer {
    display: flex;
    gap: 10px;
    padding: 12px 20px 18px;
    border-top: 1px solid var(--c-border);
}
.a-cancel {
    flex: 1;
    border-radius: 10px;
    border: 1px solid var(--c-border);
    background: transparent;
    color: var(--c-text-2);
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    padding: 12px;
    cursor: pointer;
    transition: color 0.15s var(--ease-out);
}
.a-cancel:hover:not(:disabled) { color: var(--c-text); }
.a-cancel:disabled { opacity: 0.5; cursor: not-allowed; }

.a-confirm {
    flex: 2;
    border-radius: 10px;
    background: rgba(16,185,129,0.1);
    border: 1px solid rgba(16,185,129,0.3);
    color: #34D399;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    font-weight: 600;
    padding: 12px;
    cursor: pointer;
    min-height: var(--tap-comfort, 48px);
    transition: background 0.15s var(--ease-out);
}
.a-confirm:hover:not(:disabled) { background: rgba(16,185,129,0.18); }
.a-confirm:disabled { opacity: 0.5; cursor: not-allowed; }

/* Toast */
.toast {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 200;
    border-radius: 10px;
    border: 1px solid;
    padding: 11px 20px;
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 1.2px;
    white-space: nowrap;
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);
}
.toast--success {
    border-color: rgba(16,185,129,0.3);
    background: rgba(16,185,129,0.12);
    color: #34D399;
}
.toast--error {
    border-color: rgba(220,38,38,0.3);
    background: var(--c-accent-dim);
    color: #F87171;
}

/* Transitions */
.modal-fade-enter-active,
.modal-fade-leave-active { transition: opacity 0.18s ease; }
.modal-fade-enter-from,
.modal-fade-leave-to { opacity: 0; }

.modal-scale-enter-active,
.modal-scale-leave-active { transition: transform 0.22s var(--ease-out, ease), opacity 0.18s ease; }
.modal-scale-enter-from,
.modal-scale-leave-to { transform: scale(0.96) translateY(8px); opacity: 0; }

.toast-up-enter-active,
.toast-up-leave-active { transition: transform 0.22s var(--ease-out, ease), opacity 0.18s ease; }
.toast-up-enter-from,
.toast-up-leave-to { transform: translateX(-50%) translateY(12px); opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .skeleton-card { animation: none !important; }
    .kpi-card, .filter-chip, .search-input, .coach-select,
    .empty-cta, .a-cancel, .a-confirm { transition: none !important; }
    .modal-fade-enter-active, .modal-fade-leave-active,
    .modal-scale-enter-active, .modal-scale-leave-active,
    .toast-up-enter-active, .toast-up-leave-active { transition: none !important; }
}
</style>
