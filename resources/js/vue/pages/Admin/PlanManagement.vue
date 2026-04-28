<script setup>
import { ref, computed, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout  from '../../layouts/AdminLayout.vue';
import AdminGreeting from '../../components/admin/dashboard/AdminGreeting.vue';
import AdminPlanCard        from '../../components/admin/plans/AdminPlanCard.vue';
import AdminPlansFilters    from '../../components/admin/plans/AdminPlansFilters.vue';
import AdminPlanEditModal   from '../../components/admin/plans/AdminPlanEditModal.vue';
import AdminPlanViewModal   from '../../components/admin/plans/AdminPlanViewModal.vue';
import AdminPlanDeleteModal from '../../components/admin/plans/AdminPlanDeleteModal.vue';
import { useAdminPlansStore } from '../../stores/adminPlans';

const store = useAdminPlansStore();
const api   = useApi();

// ── Modal state ──────────────────────────────────────────────────────────────
const editModalOpen   = ref(false);
const editingPlan     = ref(null);
const isDuplicate     = ref(false);

const viewModalOpen   = ref(false);
const viewingPlan     = ref(null);

const deleteModalOpen = ref(false);
const deletingPlan    = ref(null);

// ── Derived state ─────────────────────────────────────────────────────────────
const initialPaint = computed(() => store.loading && store.plans.length === 0);

const paginationPages = computed(() => {
    const pg = store.pagination;
    const total   = pg.last_page;
    const current = pg.current_page;
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    const pages = [1];
    if (current > 3) pages.push('...');
    const start = Math.max(2, current - 1);
    const end   = Math.min(total - 1, current + 1);
    for (let i = start; i <= end; i++) pages.push(i);
    if (current < total - 2) pages.push('...');
    pages.push(total);
    return pages;
});

// ── Actions ───────────────────────────────────────────────────────────────────
function openCreate() {
    editingPlan.value   = null;
    isDuplicate.value   = false;
    editModalOpen.value = true;
}

function openEdit(plan) {
    editingPlan.value   = plan;
    isDuplicate.value   = false;
    editModalOpen.value = true;
}

function openDuplicate(plan) {
    editingPlan.value   = plan;
    isDuplicate.value   = true;
    editModalOpen.value = true;
}

function openView(plan) {
    viewingPlan.value  = plan;
    viewModalOpen.value = true;
}

function openDelete(plan) {
    deletingPlan.value  = plan;
    deleteModalOpen.value = true;
}

function onSaved() {
    store.fetchPlans({ silent: true });
}

function onDeleted() {
    store.fetchPlans({ silent: true });
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => store.fetchPlans());
</script>

<template>
  <AdminLayout>
    <!-- Greeting -->
    <AdminGreeting :greeting="'Templates de Planes'" :critical-alerts="0" />

    <!-- Sub-header -->
    <div class="page-meta">
      <span class="page-eyebrow">BIBLIOTECA DE TEMPLATES</span>
      <button type="button" class="btn-new" @click="openCreate" aria-label="Crear nuevo template">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        NUEVO TEMPLATE
      </button>
    </div>

    <!-- Stats bar -->
    <div class="stats-bar page-block">
      <div class="stat-chip stat-chip--total">
        <span class="stat-num">{{ store.stats.total }}</span>
        <span class="stat-label">TOTAL</span>
      </div>
      <div class="stat-chip">
        <span class="stat-num stat-num--blue">{{ store.stats.entrenamiento }}</span>
        <span class="stat-label">ENTRENA.</span>
      </div>
      <div class="stat-chip">
        <span class="stat-num stat-num--green">{{ store.stats.nutricion }}</span>
        <span class="stat-label">NUTRICION</span>
      </div>
      <div class="stat-chip">
        <span class="stat-num stat-num--purple">{{ store.stats.habitos }}</span>
        <span class="stat-label">HABITOS</span>
      </div>
      <div class="stat-chip">
        <span class="stat-num stat-num--amber">{{ store.stats.suplementacion }}</span>
        <span class="stat-label">SUPLEM.</span>
      </div>
      <div class="stat-chip">
        <span class="stat-num stat-num--pink">{{ store.stats.ciclo }}</span>
        <span class="stat-label">CICLO</span>
      </div>
      <div class="stat-chip">
        <span class="stat-num stat-num--violet">{{ store.stats.ai_generated }}</span>
        <span class="stat-label">AI GEN.</span>
      </div>
    </div>

    <!-- Filters -->
    <AdminPlansFilters class="page-block" />

    <!-- Loading skeleton -->
    <div v-if="initialPaint" class="page-loading page-block" aria-busy="true" aria-label="Cargando templates">
      <div class="plans-grid">
        <div v-for="i in 6" :key="i" class="skeleton-card"></div>
      </div>
    </div>

    <!-- Error state -->
    <div v-else-if="store.error" class="error-card page-block" role="alert">
      <span class="error-eyebrow">ERROR</span>
      <p class="error-msg">{{ store.error }}</p>
      <button type="button" class="btn-primary" @click="store.fetchPlans()">Reintentar</button>
    </div>

    <!-- Empty state editorial -->
    <div v-else-if="store.plans.length === 0" class="empty-card page-block">
      <div class="empty-num" aria-hidden="true">—</div>
      <p class="empty-msg">"Sin templates en la biblioteca. El primer plan define el estandar del resto."</p>
      <button
        v-if="store.hasActiveFilters"
        type="button"
        class="empty-cta"
        @click="store.clearFilters()"
      >VER TODOS LOS TEMPLATES →</button>
      <button
        v-else
        type="button"
        class="empty-cta"
        @click="openCreate"
      >CREAR PRIMER TEMPLATE →</button>
    </div>

    <!-- Plans grid -->
    <template v-else>
      <div class="plans-grid page-block" role="list" aria-label="Templates de planes">
        <AdminPlanCard
          v-for="plan in store.plans"
          :key="plan.id"
          :plan="plan"
          role="listitem"
          @view="openView"
          @edit="openEdit"
          @duplicate="openDuplicate"
          @delete="openDelete"
        />
      </div>

      <!-- Pagination -->
      <nav
        v-if="store.pagination.last_page > 1"
        class="pagination page-block"
        aria-label="Paginacion de templates"
      >
        <span class="pagination-info">
          Pagina {{ store.pagination.current_page }} de {{ store.pagination.last_page }}
          <span class="pagination-total">({{ store.pagination.total }} templates)</span>
        </span>
        <div class="pagination-pages">
          <button
            type="button"
            class="page-btn page-btn--nav"
            :disabled="store.pagination.current_page <= 1"
            aria-label="Pagina anterior"
            @click="store.goToPage(store.pagination.current_page - 1)"
          >‹</button>
          <template v-for="(pg, idx) in paginationPages" :key="`pg-${idx}`">
            <span v-if="pg === '...'" class="page-ellipsis">…</span>
            <button
              v-else
              type="button"
              class="page-btn"
              :class="{ 'page-btn--active': pg === store.pagination.current_page }"
              :aria-current="pg === store.pagination.current_page ? 'page' : undefined"
              @click="store.goToPage(pg)"
            >{{ pg }}</button>
          </template>
          <button
            type="button"
            class="page-btn page-btn--nav"
            :disabled="store.pagination.current_page >= store.pagination.last_page"
            aria-label="Pagina siguiente"
            @click="store.goToPage(store.pagination.current_page + 1)"
          >›</button>
        </div>
      </nav>
    </template>

    <!-- Modals -->
    <AdminPlanEditModal
      :open="editModalOpen"
      :editing-plan="editingPlan"
      :is-duplicate="isDuplicate"
      :coaches="store.coaches"
      @close="editModalOpen = false"
      @saved="onSaved"
    />
    <AdminPlanViewModal
      :open="viewModalOpen"
      :plan="viewingPlan"
      @close="viewModalOpen = false"
    />
    <AdminPlanDeleteModal
      :open="deleteModalOpen"
      :plan-id="deletingPlan?.id ?? null"
      :plan-name="deletingPlan?.name ?? ''"
      @close="deleteModalOpen = false"
      @deleted="onDeleted"
    />
  </AdminLayout>
</template>

<style scoped>
/* ── Sub-header ──────────────────────────────────────────────────────── */
.page-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 6px 0 16px;
    flex-wrap: wrap;
}
.page-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.btn-new {
    height: 34px;
    padding: 0 14px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-accent, #DC2626);
    background: var(--color-wc-red-soft, rgba(220,38,38,0.1));
    color: var(--color-wc-accent, #DC2626);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    display: flex; align-items: center; gap: 6px;
    transition: background 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.btn-new:hover {
    background: var(--color-wc-accent, #DC2626);
    color: #fff;
}

/* ── Block spacing ───────────────────────────────────────────────────── */
.page-block { margin-bottom: 12px; }
@media (min-width: 1024px) { .page-block { margin-bottom: 20px; } }

/* ── Stats bar ───────────────────────────────────────────────────────── */
.stats-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.stat-chip {
    border-radius: 10px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 8px 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    min-width: 58px;
}
.stat-chip--total {
    border-color: var(--color-wc-border-2);
}
.stat-num {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 20px;
    font-weight: 700;
    color: var(--color-wc-text);
    font-feature-settings: 'tnum' 1;
    line-height: 1;
}
.stat-num--blue   { color: var(--color-wc-blue-text, #60A5FA); }
.stat-num--green  { color: var(--color-wc-green-text, #34D399); }
.stat-num--purple { color: #A78BFA; }
.stat-num--amber  { color: var(--color-wc-amber-text, #FCD34D); }
.stat-num--pink   { color: #F472B6; }
.stat-num--violet { color: #C084FC; }
.stat-label {
    font-family: var(--font-mono, monospace);
    font-size: 7px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}

/* ── Plans grid ──────────────────────────────────────────────────────── */
.plans-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}
@media (min-width: 640px) {
    .plans-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1024px) {
    .plans-grid { grid-template-columns: repeat(3, 1fr); gap: 16px; }
}

/* ── Loading skeleton ────────────────────────────────────────────────── */
.skeleton-card {
    height: 220px;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary);
    animation: page-pulse 1.5s ease-in-out infinite;
}
@keyframes page-pulse {
    0%, 100% { opacity: 0.6; }
    50%       { opacity: 0.9; }
}

/* ── Error state ─────────────────────────────────────────────────────── */
.error-card {
    border-radius: 14px;
    border: 1px solid rgba(220, 38, 38, 0.22);
    background: rgba(220, 38, 38, 0.07);
    padding: 24px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}
.error-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-red-text, #F87171);
}
.error-msg {
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--color-wc-text);
    margin: 0;
}
.btn-primary {
    background: var(--color-wc-accent, #DC2626);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 10px 18px;
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease);
}
.btn-primary:hover { background: #B91C1C; }

/* ── Empty state editorial ───────────────────────────────────────────── */
.empty-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 40px 24px 32px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0;
}
.empty-num {
    font-family: var(--font-display);
    font-size: 64px;
    color: var(--color-wc-bg-tertiary);
    letter-spacing: 0.1em;
    line-height: 1;
    margin-bottom: 14px;
    user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 13px;
    color: var(--color-wc-text-tertiary);
    line-height: 1.55;
    margin: 0 0 20px;
    max-width: 420px;
    text-wrap: balance;
}
.empty-cta {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-secondary);
    background: transparent;
    border: none;
    border-bottom: 1px solid var(--color-wc-border);
    padding-bottom: 4px;
    cursor: pointer;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.empty-cta:hover {
    color: var(--color-wc-text);
    border-bottom-color: var(--color-wc-accent, #DC2626);
}

/* ── Pagination ──────────────────────────────────────────────────────── */
.pagination {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: center;
    padding: 14px;
    border-radius: 12px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
}
@media (min-width: 640px) {
    .pagination { flex-direction: row; justify-content: space-between; }
}
.pagination-info {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.pagination-total { margin-left: 4px; }
.pagination-pages {
    display: flex;
    align-items: center;
    gap: 4px;
}
.page-btn {
    min-width: 28px;
    height: 28px;
    padding: 0 8px;
    border-radius: 8px;
    background: transparent;
    color: var(--color-wc-text-secondary);
    border: 1px solid transparent;
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.page-btn:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.04);
    border-color: var(--color-wc-border);
}
.page-btn--active {
    background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.1));
    color: var(--color-wc-red-text, #F87171);
    border-color: rgba(220, 38, 38, 0.4);
}
.page-btn--nav {
    font-family: var(--font-display);
    font-size: 18px;
    line-height: 1;
}
.page-btn:disabled { opacity: 0.3; cursor: not-allowed; }
.page-ellipsis {
    color: var(--color-wc-text-tertiary);
    padding: 0 4px;
    font-family: var(--font-mono, monospace);
    font-size: 12px;
}

@media (prefers-reduced-motion: reduce) {
    .skeleton-card { animation: none !important; }
    .btn-new, .btn-primary, .empty-cta,
    .page-btn { transition: none !important; }
}
</style>
