<script setup>
import { computed } from 'vue';
import { formatCOP } from '../../../composables/useFormat';

const props = defineProps({
  referrals:  { type: Array,   default: () => [] },
  loading:    { type: Boolean, default: false },
  totalPages: { type: Number,  default: 1 },
  page:       { type: Number,  default: 1 },
  filters:    { type: Object,  default: () => ({}) },
});

const emit = defineEmits(['filter', 'page', 'mark-paid', 'expire']);

const STATUS_TABS = [
  { key: 'all',       label: 'Todos' },
  { key: 'pending',   label: 'Pendiente' },
  { key: 'qualified', label: 'Qualified' },
  { key: 'paid',      label: 'Pagados' },
  { key: 'expired',   label: 'Expirados' },
];

const PERIOD_TABS = [
  { key: 'today',   label: 'HOY' },
  { key: 'week',    label: 'SEMANA' },
  { key: 'month',   label: 'MES' },
  { key: 'quarter', label: 'TRIMESTRE' },
  { key: 'year',    label: 'AÑO' },
];

const statusMeta = (status) => {
  const map = {
    pending:   { cls: 'pill-pending',   label: 'Pendiente' },
    qualified: { cls: 'pill-qualified', label: 'Qualified' },
    paid:      { cls: 'pill-paid',      label: 'Pagado' },
    expired:   { cls: 'pill-expired',   label: 'Expirado' },
  };
  return map[status] ?? { cls: '', label: status };
};

const pages = computed(() => {
  const arr = [];
  for (let i = 1; i <= props.totalPages; i++) arr.push(i);
  return arr;
});
</script>

<template>
  <div class="referrals-table-wrap">

    <!-- Filters row -->
    <div class="filters-row" role="toolbar" aria-label="Filtros referidos">
      <!-- Search -->
      <div class="search-wrap">
        <svg class="search-icon" aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
          <circle cx="8.5" cy="8.5" r="5.75"/><path d="m13.25 13.25 3 3" stroke-linecap="round"/>
        </svg>
        <input
          class="search-input"
          type="search"
          placeholder="Buscar referidor o referido..."
          :value="filters.search"
          aria-label="Buscar referidos"
          @input="emit('filter', 'search', $event.target.value)"
        />
      </div>

      <!-- Period pills -->
      <div class="filter-pills" role="group" aria-label="Período">
        <button
          v-for="p in PERIOD_TABS"
          :key="p.key"
          class="filter-pill"
          :class="{ 'filter-pill--active': filters.periodo === p.key }"
          :aria-pressed="filters.periodo === p.key"
          @click="emit('filter', 'periodo', p.key)"
        >{{ p.label }}</button>
      </div>
    </div>

    <!-- Status tabs -->
    <div class="status-tabs" role="tablist" aria-label="Estado referidos">
      <button
        v-for="tab in STATUS_TABS"
        :key="tab.key"
        class="status-tab"
        :class="{ 'status-tab--active': filters.status === tab.key }"
        role="tab"
        :aria-selected="filters.status === tab.key"
        @click="emit('filter', 'status', tab.key)"
      >{{ tab.label }}</button>
    </div>

    <!-- Table card -->
    <div class="table-card">

      <!-- Loading skeleton -->
      <div v-if="loading && !referrals.length" class="table-skeleton" aria-hidden="true">
        <div v-for="i in 5" :key="i" class="table-skeleton-row" />
      </div>

      <!-- Empty state -->
      <div v-else-if="!referrals.length" class="empty-state">
        <div class="empty-num" aria-hidden="true">—</div>
        <p class="empty-msg">"Sin referidos en el período. El programa se activa automáticamente al primer cliente referido."</p>
        <a href="#" class="empty-cta" aria-label="Ver política del programa">VER POLÍTICA DEL PROGRAMA <span aria-hidden="true">→</span></a>
      </div>

      <!-- Desktop table -->
      <div v-else class="table-scroll">
        <table class="data-table" aria-label="Tabla de referidos">
          <thead>
            <tr class="table-head-row">
              <th class="th">Referidor</th>
              <th class="th">Referido</th>
              <th class="th">Estado</th>
              <th class="th th--num">Recompensa</th>
              <th class="th">Referido</th>
              <th class="th">Qualified</th>
              <th class="th th--right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="r in referrals"
              :key="r.id"
              class="table-row"
            >
              <!-- Referidor -->
              <td class="td">
                <p class="td-name">{{ r.referrer_name }}</p>
                <p class="td-sub">{{ r.referrer_email }}</p>
              </td>

              <!-- Referido -->
              <td class="td">
                <p v-if="r.referred_name" class="td-name">{{ r.referred_name }}</p>
                <p class="td-sub">{{ r.referred_email }}</p>
              </td>

              <!-- Status pill -->
              <td class="td">
                <span class="pill" :class="statusMeta(r.status).cls" role="img" :aria-label="`Estado: ${statusMeta(r.status).label}`">
                  {{ statusMeta(r.status).label }}
                </span>
              </td>

              <!-- Reward -->
              <td class="td td--num">
                <span class="reward-cop">{{ formatCOP(r.reward_cop) }}</span>
              </td>

              <!-- Fecha referral -->
              <td class="td">
                <p class="td-sub">{{ r.created_at_relative }}</p>
              </td>

              <!-- Fecha qualified -->
              <td class="td">
                <p class="td-sub">{{ r.qualified_at_relative ?? '—' }}</p>
              </td>

              <!-- Acciones kebab -->
              <td class="td td--right">
                <div class="actions-wrap">
                  <button
                    v-if="r.status === 'qualified'"
                    class="action-btn action-btn--primary"
                    :aria-label="`Marcar payout como entregado a ${r.referrer_name}`"
                    @click="emit('mark-paid', r)"
                  >Pagar</button>
                  <button
                    v-if="r.status === 'pending'"
                    class="action-btn action-btn--ghost"
                    :aria-label="`Expirar referral de ${r.referrer_name}`"
                    @click="emit('expire', r.id)"
                  >Expirar</button>
                  <span v-if="!['qualified','pending'].includes(r.status)" class="td-sub">—</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination" role="navigation" aria-label="Paginación">
        <button
          class="page-btn"
          :disabled="page <= 1"
          aria-label="Página anterior"
          @click="emit('page', page - 1)"
        >←</button>
        <button
          v-for="p in pages"
          :key="p"
          class="page-btn"
          :class="{ 'page-btn--active': p === page }"
          :aria-current="p === page ? 'page' : undefined"
          @click="emit('page', p)"
        >{{ p }}</button>
        <button
          class="page-btn"
          :disabled="page >= totalPages"
          aria-label="Página siguiente"
          @click="emit('page', page + 1)"
        >→</button>
      </div>

    </div>
  </div>
</template>

<style scoped>
/* ── Filters ──────────────────────────────────────────────────────────── */
.filters-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
}
.search-wrap {
    position: relative;
    flex: 1;
    min-width: 180px;
}
.search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 14px;
    height: 14px;
    color: var(--c-text-3);
    pointer-events: none;
}
.search-input {
    width: 100%;
    height: 36px;
    padding: 0 10px 0 30px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255,255,255,0.03);
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--c-text);
    outline: none;
    transition: border-color 0.15s var(--ease-out);
}
.search-input::placeholder { color: var(--c-text-3); }
.search-input:focus { border-color: rgba(255,255,255,0.12); }

.filter-pills { display: flex; gap: 4px; flex-wrap: wrap; }
.filter-pill {
    height: 28px;
    padding: 0 10px;
    border-radius: 6px;
    border: 1px solid var(--c-border);
    background: transparent;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-2);
    cursor: pointer;
    transition: background 0.15s var(--ease-out), border-color 0.15s var(--ease-out), color 0.15s var(--ease-out);
}
.filter-pill--active {
    background: var(--c-accent-dim);
    border-color: var(--c-accent);
    color: var(--c-text);
}

/* ── Status tabs ──────────────────────────────────────────────────────── */
.status-tabs {
    display: flex;
    gap: 2px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}
.status-tab {
    padding: 6px 14px;
    border-radius: 6px;
    border: none;
    background: transparent;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-2);
    cursor: pointer;
    transition: background 0.12s var(--ease-out), color 0.12s var(--ease-out);
}
.status-tab--active {
    background: var(--c-accent-dim);
    color: var(--c-text);
}

/* ── Table card ───────────────────────────────────────────────────────── */
.table-card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 18px;
    overflow: hidden;
}

/* ── Skeleton ─────────────────────────────────────────────────────────── */
.table-skeleton { display: flex; flex-direction: column; gap: 8px; }
.table-skeleton-row {
    height: 48px;
    border-radius: var(--r-sm, 12px);
    background: var(--c-surface-2);
    animation: page-pulse 1.5s ease-in-out infinite;
}
@keyframes page-pulse {
    0%, 100% { opacity: 0.6; }
    50%       { opacity: 0.9; }
}

/* ── Empty state ──────────────────────────────────────────────────────── */
.empty-state { padding: 24px 8px 18px; text-align: center; }
.empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--c-surface-2);
    letter-spacing: 0.8px;
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: var(--c-text-3);
    line-height: 1.55;
    margin: 0 0 16px;
    text-wrap: balance;
}
.empty-cta {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    color: var(--c-text-2);
    text-decoration: none;
    text-transform: uppercase;
    border-bottom: 1px solid var(--c-border);
    padding-bottom: 4px;
    transition: color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
}
.empty-cta:hover { color: var(--c-text); border-bottom-color: var(--c-accent); }

/* ── Data table ───────────────────────────────────────────────────────── */
.table-scroll { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }

.table-head-row { border-bottom: 1px solid var(--c-border); }
.th {
    padding: 8px 10px;
    text-align: left;
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
    white-space: nowrap;
}
.th--num, .th--right { text-align: right; }

.table-row {
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: background 0.12s var(--ease-out);
}
.table-row:last-child { border-bottom: none; }
.table-row:hover { background: rgba(255,255,255,0.02); }

.td { padding: 11px 10px; vertical-align: middle; }
.td--num, .td--right { text-align: right; }
.td-name { font-size: 12px; font-weight: 500; color: var(--c-text); margin: 0; }
.td-sub  { font-size: 11px; color: var(--c-text-2); margin: 0; }

/* ── Status pills ─────────────────────────────────────────────────────── */
.pill {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: var(--r-pill, 999px);
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    white-space: nowrap;
}
.pill-pending   { background: rgba(245,158,11,0.1); color: #FCD34D; }
.pill-qualified { background: rgba(59,130,246,0.1);  color: #60A5FA; }
.pill-paid      { background: rgba(16,185,129,0.1); color: #34D399; }
.pill-expired   { background: var(--c-accent-dim);  color: #F87171; }

/* ── Reward ───────────────────────────────────────────────────────────── */
.reward-cop {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    color: var(--c-text);
    font-feature-settings: 'tnum' 1;
}

/* ── Actions ──────────────────────────────────────────────────────────── */
.actions-wrap { display: flex; gap: 6px; justify-content: flex-end; align-items: center; }
.action-btn {
    height: 28px;
    padding: 0 10px;
    border-radius: 6px;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out), color 0.15s var(--ease-out);
}
.action-btn--primary {
    background: rgba(16,185,129,0.1);
    border: 1px solid rgba(52,211,153,0.2);
    color: #34D399;
}
.action-btn--primary:hover { background: rgba(52,211,153,0.2); }
.action-btn--ghost {
    background: transparent;
    border: 1px solid var(--c-border);
    color: var(--c-text-2);
}
.action-btn--ghost:hover { border-color: rgba(255,255,255,0.12); color: var(--c-text); }

/* ── Pagination ───────────────────────────────────────────────────────── */
.pagination {
    display: flex;
    align-items: center;
    gap: 4px;
    justify-content: center;
    padding-top: 14px;
    border-top: 1px solid var(--c-border);
    margin-top: 14px;
}
.page-btn {
    min-width: 28px;
    height: 28px;
    padding: 0 6px;
    border-radius: 6px;
    border: 1px solid var(--c-border);
    background: transparent;
    font-family: var(--font-display);
    font-size: 10px;
    color: var(--c-text-2);
    cursor: pointer;
    transition: background 0.12s var(--ease-out), color 0.12s var(--ease-out);
}
.page-btn--active { background: var(--c-accent-dim); border-color: var(--c-accent); color: var(--c-text); }
.page-btn:disabled { opacity: 0.3; cursor: default; }

@media (prefers-reduced-motion: reduce) {
    .table-skeleton-row { animation: none !important; }
}
</style>
