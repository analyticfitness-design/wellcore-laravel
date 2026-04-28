<script setup>
import { computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminGreeting from '../../components/admin/dashboard/AdminGreeting.vue';
import AdminPaymentsKPIs from '../../components/admin/payments/AdminPaymentsKPIs.vue';
import AdminPaymentsFilters from '../../components/admin/payments/AdminPaymentsFilters.vue';
import AdminPaymentsTable from '../../components/admin/payments/AdminPaymentsTable.vue';
import AdminPaymentCardMobile from '../../components/admin/payments/AdminPaymentCardMobile.vue';
import AdminPaymentDetailDrawer from '../../components/admin/payments/AdminPaymentDetailDrawer.vue';
import AdminPaymentRefundModal from '../../components/admin/payments/AdminPaymentRefundModal.vue';
import AdminPaymentsExportButton from '../../components/admin/payments/AdminPaymentsExportButton.vue';
import { useAdminPaymentsStore } from '../../stores/adminPayments';

const store = useAdminPaymentsStore();
const route = useRoute();
const router = useRouter();

const monthName = new Date().toLocaleDateString('es-CO', { month: 'long' });

const initialPaint = computed(() => store.loading && store.payments.length === 0);

const paginationPages = computed(() => {
    const pg = store.pagination;
    if (!pg) return [];
    const total = pg.last_page;
    const current = pg.current_page;
    const pages = [];
    if (total <= 7) {
        for (let i = 1; i <= total; i++) pages.push(i);
        return pages;
    }
    pages.push(1);
    if (current > 3) pages.push('...');
    const start = Math.max(2, current - 1);
    const end = Math.min(total - 1, current + 1);
    for (let i = start; i <= end; i++) pages.push(i);
    if (current < total - 2) pages.push('...');
    pages.push(total);
    return pages;
});

const refreshHint = computed(() => {
    const s = store.secondsSinceRefresh;
    if (s === null) return '';
    if (s < 10) return 'Actualizado ahora';
    if (s < 60) return `Actualizado hace ${s}s`;
    if (s < 3600) return `Actualizado hace ${Math.floor(s / 60)} min`;
    return 'Actualizado hace 1h+';
});

function syncUrlFromStore() {
    const f = store.filters;
    const next = {};
    if (f.status) next.status = f.status;
    if (f.method) next.method = f.method;
    if (f.search) next.search = f.search;
    if (f.dateFrom) next.date_from = f.dateFrom;
    if (f.dateTo) next.date_to = f.dateTo;
    if (store.page > 1) next.page = String(store.page);

    const current = JSON.stringify(route.query);
    const target = JSON.stringify(next);
    if (current !== target) {
        router.replace({ query: next }).catch(() => {});
    }
}

onMounted(() => {
    store.hydrateFromQuery(route.query);
    store.fetchPayments();
    store.startPolling(60000);
});

onBeforeUnmount(() => {
    store.stopPolling();
});

watch(() => store.filters, syncUrlFromStore, { deep: true });
watch(() => store.page, syncUrlFromStore);
</script>

<template>
  <AdminLayout>
    <AdminGreeting :greeting="'Pagos'" :critical-alerts="0" />

    <!-- Sub-header con eyebrow + estado polling + export -->
    <div class="page-meta">
      <span class="page-eyebrow">FLUJO FINANCIERO</span>
      <div class="meta-actions">
        <span class="poll-hint">{{ refreshHint }}</span>
        <AdminPaymentsExportButton />
      </div>
    </div>

    <!-- KPIs hero -->
    <AdminPaymentsKPIs class="page-block" :kpis="store.kpis" :month-name="monthName" />

    <!-- Filtros -->
    <AdminPaymentsFilters class="page-block" />

    <!-- Loading skeleton (solo en first paint) -->
    <div v-if="initialPaint" class="page-loading page-block">
      <div class="page-loading-bar"></div>
      <div class="page-loading-grid">
        <div v-for="i in 4" :key="i" class="page-loading-card"></div>
      </div>
    </div>

    <!-- Error state -->
    <div v-else-if="store.error" class="error-card page-block">
      <span class="error-eyebrow">ERROR</span>
      <p class="error-msg">{{ store.error }}</p>
      <button class="btn-primary" type="button" @click="store.fetchPayments()">Reintentar</button>
    </div>

    <!-- Empty state editorial -->
    <div v-else-if="store.payments.length === 0" class="empty-card page-block">
      <div class="empty-num">—</div>
      <p class="empty-msg">"Sin pagos en el rango seleccionado. Cuando entren pagos, apareceran aqui en tiempo real."</p>
      <button v-if="store.hasActiveFilters" class="empty-cta" type="button" @click="store.clearFilters()">
        VER TODOS LOS PAGOS →
      </button>
    </div>

    <!-- Tabla desktop / cards mobile -->
    <template v-else>
      <AdminPaymentsTable class="page-block hidden lg:block" />

      <div class="page-block mobile-stack lg:hidden">
        <AdminPaymentCardMobile
          v-for="p in store.payments"
          :key="p.id"
          :payment="p"
        />
      </div>

      <!-- Pagination -->
      <nav v-if="store.pagination && store.pagination.last_page > 1" class="pagination page-block" aria-label="Paginacion de pagos">
        <span class="pagination-info">
          Mostrando {{ ((store.pagination.current_page - 1) * store.pagination.per_page) + 1 }}–{{ Math.min(store.pagination.current_page * store.pagination.per_page, store.pagination.total) }} de {{ store.pagination.total }}
        </span>
        <div class="pagination-pages">
          <button
            class="page-btn page-btn--nav"
            type="button"
            :disabled="store.pagination.current_page <= 1"
            @click="store.goToPage(store.pagination.current_page - 1)"
            aria-label="Pagina anterior"
          >‹</button>
          <template v-for="(pg, idx) in paginationPages" :key="`pg-${idx}`">
            <span v-if="pg === '...'" class="page-ellipsis">…</span>
            <button
              v-else
              class="page-btn"
              :class="{ 'page-btn--active': pg === store.pagination.current_page }"
              type="button"
              @click="store.goToPage(pg)"
            >{{ pg }}</button>
          </template>
          <button
            class="page-btn page-btn--nav"
            type="button"
            :disabled="store.pagination.current_page >= store.pagination.last_page"
            @click="store.goToPage(store.pagination.current_page + 1)"
            aria-label="Pagina siguiente"
          >›</button>
        </div>
      </nav>
    </template>

    <!-- Singletons globales -->
    <AdminPaymentDetailDrawer />
    <AdminPaymentRefundModal />
  </AdminLayout>
</template>

<style scoped>
.page-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    padding: 6px 0 14px;
}
.page-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.meta-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.poll-hint {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}

.page-block {
    margin-bottom: 12px;
}
@media (min-width: 1024px) {
    .page-block { margin-bottom: 20px; }
}

.mobile-stack {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* ── Loading skeleton ──────────────────────────────────────────────────── */
.page-loading {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.page-loading-bar {
    height: 36px;
    background: var(--color-wc-bg-tertiary);
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    animation: page-pulse 1.5s ease-in-out infinite;
}
.page-loading-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}
@media (min-width: 768px) {
    .page-loading-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1024px) {
    .page-loading-grid { grid-template-columns: repeat(4, 1fr); }
}
.page-loading-card {
    height: 124px;
    background: var(--color-wc-bg-tertiary);
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    animation: page-pulse 1.5s ease-in-out infinite;
}
@keyframes page-pulse {
    0%, 100% { opacity: 0.6; }
    50%      { opacity: 0.9; }
}

/* ── Error state ──────────────────────────────────────────────────────── */
.error-card {
    border-radius: 14px;
    border: 1px solid rgba(220, 38, 38, 0.22);
    background: rgba(220, 38, 38, 0.07);
    padding: 22px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
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
    border: 1px solid var(--color-wc-accent, #DC2626);
    border-radius: 10px;
    padding: 10px 16px;
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease);
}
.btn-primary:hover { background: #B91C1C; }

/* ── Empty state editorial ────────────────────────────────────────────── */
.empty-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 32px 18px 24px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0;
}
.empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--color-wc-bg-tertiary);
    letter-spacing: 0.1em;
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 13px;
    color: var(--color-wc-text-tertiary);
    line-height: 1.55;
    margin: 0 0 16px;
    max-width: 480px;
    text-wrap: balance;
}
.empty-cta {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    color: var(--color-wc-text-secondary);
    background: transparent;
    border: none;
    text-transform: uppercase;
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
@media (min-width: 768px) {
    .pagination {
        flex-direction: row;
        justify-content: space-between;
    }
}
.pagination-info {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
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
    .page-loading-bar,
    .page-loading-card { animation: none !important; }
}
</style>
