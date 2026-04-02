<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

// ── Tab state ──────────────────────────────────────────────────────────────
const activeTab = ref('overview');
const TABS = [
    { key: 'overview',      label: 'Overview' },
    { key: 'participants',  label: 'Participantes' },
    { key: 'progress',      label: 'Progreso' },
    { key: 'payments',      label: 'Pagos' },
];

// ── Overview / Participants shared state ───────────────────────────────────
const loadingRise   = ref(false);
const errorRise     = ref(null);
const overview      = ref(null);
const participants  = ref([]);
const pagination    = ref({ current_page: 1, last_page: 1, total: 0 });

// Participants filters
const search        = ref('');
const statusFilter  = ref('all');
const sortBy        = ref('enrollment_date');
const sortDir       = ref('desc');
const currentPage   = ref(1);

// ── Progress state ─────────────────────────────────────────────────────────
const loadingProgress = ref(false);
const errorProgress   = ref(null);
const progress        = ref(null);

// ── Payments state ─────────────────────────────────────────────────────────
const loadingPayments   = ref(false);
const errorPayments     = ref(null);
const payments          = ref([]);
const paymentStats      = ref(null);
const paymentPagination = ref({ current_page: 1, last_page: 1, total: 0 });
const paymentSearch     = ref('');
const paymentStatus     = ref('all');
const paymentPage       = ref(1);

// ── Detail modal ───────────────────────────────────────────────────────────
const showDetail    = ref(false);
const detailProgram = ref(null);

// ── Status config ──────────────────────────────────────────────────────────
const STATUS_COLORS = {
    active:    { bg: 'bg-emerald-500/10', text: 'text-emerald-500', bar: 'bg-emerald-500', label: 'Activo' },
    activo:    { bg: 'bg-emerald-500/10', text: 'text-emerald-500', bar: 'bg-emerald-500', label: 'Activo' },
    completed: { bg: 'bg-blue-500/10',    text: 'text-blue-500',    bar: 'bg-blue-500',    label: 'Completado' },
    paused:    { bg: 'bg-amber-500/10',   text: 'text-amber-500',   bar: 'bg-amber-500',   label: 'Pausado' },
    cancelled: { bg: 'bg-red-500/10',     text: 'text-red-400',     bar: 'bg-red-500',     label: 'Cancelado' },
};

const PAYMENT_COLORS = {
    approved: { bg: 'bg-emerald-500/10', text: 'text-emerald-500' },
    pending:  { bg: 'bg-amber-500/10',   text: 'text-amber-500'   },
    declined: { bg: 'bg-red-500/10',     text: 'text-red-400'     },
    voided:   { bg: 'bg-gray-500/10',    text: 'text-gray-400'    },
    error:    { bg: 'bg-red-500/10',     text: 'text-red-400'     },
};

const NUTRITION_COLORS = {
    excellent: { bar: 'bg-emerald-500', text: 'text-emerald-500', label: 'Excelente' },
    good:      { bar: 'bg-blue-500',    text: 'text-blue-500',    label: 'Buena'     },
    fair:      { bar: 'bg-amber-500',   text: 'text-amber-500',   label: 'Regular'   },
    poor:      { bar: 'bg-red-500',     text: 'text-red-400',     label: 'Pobre'     },
};

// ── Helpers ────────────────────────────────────────────────────────────────
function statusClass(s, type = 'badge') {
    const c = STATUS_COLORS[s] ?? { bg: 'bg-wc-bg', text: 'text-wc-text-tertiary', label: s };
    return type === 'badge' ? `${c.bg} ${c.text}` : c.text;
}

function statusLabel(s) {
    return STATUS_COLORS[s]?.label ?? s;
}

function paymentClass(s) {
    const c = PAYMENT_COLORS[s] ?? { bg: 'bg-wc-bg', text: 'text-wc-text-tertiary' };
    return `${c.bg} ${c.text}`;
}

function locationLabel(loc) {
    const map = { gym: 'Gym', home: 'Casa', hybrid: 'Hibrido' };
    return map[loc] ?? loc ?? '-';
}

function fmtNumber(n, decimals = 0) {
    if (n == null) return '-';
    return Number(n).toLocaleString('es-CO', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
}

function deltaClass(val, positiveIsGood = false) {
    if (val === 0 || val == null) return 'text-wc-text-secondary';
    if (positiveIsGood) return val > 0 ? 'text-emerald-500' : 'text-red-400';
    return val < 0 ? 'text-emerald-500' : 'text-red-400';
}

function deltaPrefix(val) {
    return val > 0 ? '+' : '';
}

// ── Computed: filtered payments ────────────────────────────────────────────
const filteredPayments = computed(() => {
    let list = payments.value;
    // Keep only RISE plan payments
    list = list.filter(p => (p.plan ?? '').toLowerCase() === 'rise');
    if (paymentStatus.value !== 'all') {
        list = list.filter(p => p.status === paymentStatus.value);
    }
    if (paymentSearch.value.trim()) {
        const q = paymentSearch.value.toLowerCase();
        list = list.filter(p =>
            (p.buyer_name ?? '').toLowerCase().includes(q) ||
            (p.client_name ?? '').toLowerCase().includes(q)
        );
    }
    return list;
});

// ── Computed: status breakdown from participants ───────────────────────────
const statusBreakdown = computed(() => {
    if (!overview.value) return {};
    // Use overview data if available, otherwise derive from participants
    return overview.value.statusBreakdown ?? {};
});

const totalForBar = computed(() => {
    const vals = Object.values(statusBreakdown.value);
    return Math.max(vals.reduce((a, b) => a + b, 0), 1);
});

// ── API calls ──────────────────────────────────────────────────────────────
async function fetchRise() {
    loadingRise.value = true;
    errorRise.value = null;
    try {
        const params = { page: currentPage.value };
        if (search.value.trim()) params.search = search.value;
        if (statusFilter.value !== 'all') params.status = statusFilter.value;

        const resp = await api.get('/api/v/admin/rise', { params });
        overview.value = resp.data.overview ?? null;
        participants.value = resp.data.participants ?? [];
        pagination.value = resp.data.pagination ?? { current_page: 1, last_page: 1, total: 0 };
    } catch (err) {
        errorRise.value = err.response?.data?.message || 'Error al cargar datos RISE';
    } finally {
        loadingRise.value = false;
    }
}

async function fetchProgress() {
    if (progress.value) return; // cached for session
    loadingProgress.value = true;
    errorProgress.value = null;
    try {
        // The rise endpoint contains basic stats; for progress we re-use it
        // with full dataset (no filter) to get activity counts from overview
        const resp = await api.get('/api/v/admin/rise', { params: {} });
        const ov = resp.data.overview ?? {};
        // Build a progress object from what the API provides
        progress.value = {
            totalLogs:           ov.totalTracking   ?? 0,
            totalMeasurements:   ov.totalMeasurements ?? 0,
            workoutRate:         null,
            workoutCompleted:    null,
            avgMood:             null,
            avgEnergy:           null,
            avgWeight:           null,
            avgWaist:            null,
            avgFat:              null,
            avgMuscle:           null,
            avgWater:            null,
            avgSleep:            null,
            trainingDoneCount:   null,
            nutritionDoneCount:  null,
            clientsMeasured:     null,
            nutritionBreakdown:  {},
            measurementDeltas:   [],
        };
    } catch (err) {
        errorProgress.value = err.response?.data?.message || 'Error al cargar progreso';
    } finally {
        loadingProgress.value = false;
    }
}

async function fetchPayments() {
    loadingPayments.value = true;
    errorPayments.value = null;
    try {
        const resp = await api.get('/api/v/admin/payments', {
            params: { per_page: 200 },
        });
        const all = resp.data.payments ?? [];
        // Filter to RISE plan only
        payments.value = all.filter(p => (p.plan ?? '').toLowerCase() === 'rise');

        const approved = payments.value.filter(p => p.status === 'approved');
        const pending  = payments.value.filter(p => p.status === 'pending');
        paymentStats.value = {
            total:        payments.value.length,
            totalRevenue: approved.reduce((s, p) => s + (p.amount ?? 0), 0),
            approved:     approved.length,
            pending:      pending.length,
        };
    } catch (err) {
        errorPayments.value = err.response?.data?.message || 'Error al cargar pagos';
    } finally {
        loadingPayments.value = false;
    }
}

// ── Sorting ────────────────────────────────────────────────────────────────
function sortByColumn(col) {
    if (sortBy.value === col) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = col;
        sortDir.value = 'desc';
    }
}

const sortedParticipants = computed(() => {
    const list = [...participants.value];
    list.sort((a, b) => {
        let va = a[sortBy.value] ?? '';
        let vb = b[sortBy.value] ?? '';
        if (typeof va === 'string') va = va.toLowerCase();
        if (typeof vb === 'string') vb = vb.toLowerCase();
        if (va < vb) return sortDir.value === 'asc' ? -1 : 1;
        if (va > vb) return sortDir.value === 'asc' ? 1 : -1;
        return 0;
    });
    return list;
});

// ── Participant detail modal ───────────────────────────────────────────────
function viewParticipant(program) {
    detailProgram.value = program;
    showDetail.value = true;
}

function closeDetail() {
    showDetail.value = false;
    detailProgram.value = null;
}

// Close modal on Escape
function onKeydown(e) {
    if (e.key === 'Escape' && showDetail.value) closeDetail();
}

// ── Tab switching ──────────────────────────────────────────────────────────
function switchTab(tab) {
    activeTab.value = tab;
    if (tab === 'overview' || tab === 'participants') {
        currentPage.value = 1;
        fetchRise();
    }
    if (tab === 'progress') fetchProgress();
    if (tab === 'payments') fetchPayments();
}

// ── Debounced search ───────────────────────────────────────────────────────
let debounceTimer = null;
watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        currentPage.value = 1;
        fetchRise();
    }, 300);
});
watch(statusFilter, () => {
    currentPage.value = 1;
    fetchRise();
});

// ── Pagination helpers ─────────────────────────────────────────────────────
function goToPage(page) {
    if (page < 1 || page > pagination.value.last_page) return;
    currentPage.value = page;
    fetchRise();
}

// ── Init ───────────────────────────────────────────────────────────────────
onMounted(() => {
    fetchRise();
    document.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    clearTimeout(debounceTimer);
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- ── Header ──────────────────────────────────────────────────────── -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
          <h1 class="font-display text-3xl tracking-wide text-wc-text">PROGRAMA RISE</h1>
          <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-emerald-500">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
            </svg>
            Reto 12 Semanas
          </span>
        </div>
      </div>

      <!-- ── Tab Bar ─────────────────────────────────────────────────────── -->
      <div class="flex gap-1 rounded-xl border border-wc-border bg-wc-bg-secondary p-1">
        <button
          v-for="tab in TABS"
          :key="tab.key"
          @click="switchTab(tab.key)"
          :class="activeTab === tab.key
            ? 'bg-emerald-500/10 text-emerald-500 shadow-sm'
            : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary'"
          class="flex-1 rounded-lg px-4 py-2 text-sm font-medium transition-colors"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- ════════════════════════════════════════════════════════════════ -->
      <!-- OVERVIEW TAB                                                      -->
      <!-- ════════════════════════════════════════════════════════════════ -->
      <Transition name="fade" mode="out-in">
        <div v-if="activeTab === 'overview'" key="overview" class="space-y-6">

          <!-- Loading -->
          <template v-if="loadingRise">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
              <div v-for="n in 4" :key="n" class="h-24 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
            </div>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
              <div class="h-48 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
              <div class="h-48 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
            </div>
          </template>

          <!-- Error -->
          <div v-else-if="errorRise" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
            <p class="text-sm text-wc-text">{{ errorRise }}</p>
            <button @click="fetchRise" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:opacity-90 transition-opacity">
              Reintentar
            </button>
          </div>

          <template v-else-if="overview">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

              <!-- Total Participantes -->
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Participantes</p>
                    <p class="mt-1 font-display text-3xl tracking-wide text-wc-text">{{ overview.totalPrograms ?? 0 }}</p>
                  </div>
                  <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10">
                    <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Activos -->
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Activos</p>
                    <p class="mt-1 font-display text-3xl tracking-wide text-emerald-500">{{ overview.activePrograms ?? 0 }}</p>
                  </div>
                  <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10">
                    <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Mediciones -->
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Mediciones</p>
                    <p class="mt-1 font-display text-3xl tracking-wide text-wc-text">{{ overview.totalMeasurements ?? 0 }}</p>
                    <p class="mt-0.5 text-[11px] text-wc-text-tertiary">registros totales</p>
                  </div>
                  <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10">
                    <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Revenue RISE -->
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Revenue RISE</p>
                    <p class="mt-1 font-display text-3xl tracking-wide text-wc-text">${{ fmtNumber(overview.totalRevenue) }}</p>
                  </div>
                  <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500/10">
                    <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>

            <!-- Status Breakdown + Activity Summary -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

              <!-- Status Breakdown (derived from participants) -->
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="mb-4 text-sm font-semibold text-wc-text">Estado de Programas</h3>
                <div class="space-y-3">
                  <template v-for="(cfg, key) in STATUS_COLORS" :key="key">
                    <template v-if="key !== 'activo'">
                      <div>
                        <div class="mb-1 flex items-center justify-between text-xs">
                          <span :class="cfg.text" class="font-medium">{{ cfg.label }}</span>
                          <span class="text-wc-text-tertiary">
                            {{ participants.filter(p => p.status === key || (key === 'active' && p.status === 'activo')).length }}
                          </span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-wc-bg">
                          <div
                            :class="cfg.bar"
                            class="h-full rounded-full transition-all duration-500"
                            :style="{ width: `${(participants.filter(p => p.status === key || (key === 'active' && p.status === 'activo')).length / Math.max(participants.length, 1)) * 100}%` }"
                          ></div>
                        </div>
                      </div>
                    </template>
                  </template>
                  <p v-if="!participants.length" class="text-sm text-wc-text-tertiary">Sin datos</p>
                </div>
              </div>

              <!-- Activity Summary -->
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="mb-4 text-sm font-semibold text-wc-text">Actividad del Programa</h3>
                <div class="grid grid-cols-3 gap-4">
                  <div class="rounded-lg bg-wc-bg p-3 text-center">
                    <p class="font-display text-2xl text-wc-text">{{ overview.totalTracking ?? 0 }}</p>
                    <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Tracking</p>
                  </div>
                  <div class="rounded-lg bg-wc-bg p-3 text-center">
                    <p class="font-display text-2xl text-wc-text">{{ overview.totalMeasurements ?? 0 }}</p>
                    <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Mediciones</p>
                  </div>
                  <div class="rounded-lg bg-wc-bg p-3 text-center">
                    <p class="font-display text-2xl text-wc-text">{{ overview.totalPrograms ?? 0 }}</p>
                    <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Programas</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recent Enrollments (last 5 from participants list) -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
              <h3 class="mb-4 text-sm font-semibold text-wc-text">Inscripciones Recientes</h3>
              <div v-if="!participants.length" class="flex flex-col items-center justify-center py-8 text-center">
                <svg class="mb-3 h-10 w-10 text-wc-text-tertiary/50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
                <p class="text-sm text-wc-text-tertiary">No hay inscripciones recientes</p>
              </div>
              <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                  <thead>
                    <tr class="border-b border-wc-border">
                      <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cliente</th>
                      <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
                      <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nivel</th>
                      <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-wc-border/50">
                    <tr v-for="enrollment in participants.slice(0, 5)" :key="enrollment.id" class="group">
                      <td class="py-2.5">
                        <div class="flex items-center gap-2">
                          <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500/20">
                            <span class="text-xs font-semibold text-emerald-500">{{ (enrollment.client_name || '?').charAt(0).toUpperCase() }}</span>
                          </div>
                          <span class="font-medium text-wc-text">{{ enrollment.client_name || 'N/A' }}</span>
                        </div>
                      </td>
                      <td class="py-2.5 text-wc-text-secondary">{{ enrollment.created_at || '-' }}</td>
                      <td class="py-2.5">
                        <span class="inline-flex rounded-full bg-wc-bg px-2 py-0.5 text-[10px] font-semibold capitalize text-wc-text-secondary">
                          {{ enrollment.experience_level || '-' }}
                        </span>
                      </td>
                      <td class="py-2.5">
                        <span :class="statusClass(enrollment.status)" class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold capitalize">
                          {{ statusLabel(enrollment.status) }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </template>
        </div>
      </Transition>

      <!-- ════════════════════════════════════════════════════════════════ -->
      <!-- PARTICIPANTS TAB                                                  -->
      <!-- ════════════════════════════════════════════════════════════════ -->
      <Transition name="fade" mode="out-in">
        <div v-if="activeTab === 'participants'" key="participants" class="space-y-4">

          <!-- Search + Filter -->
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
              <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
              </svg>
              <input
                v-model="search"
                type="text"
                placeholder="Buscar participante..."
                class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-9 pr-4 text-sm text-wc-text placeholder:text-wc-text-tertiary outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
              />
            </div>
            <select
              v-model="statusFilter"
              class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text outline-none focus:border-emerald-500"
            >
              <option value="all">Todos los estados</option>
              <option value="active">Activo</option>
              <option value="completed">Completado</option>
              <option value="paused">Pausado</option>
              <option value="cancelled">Cancelado</option>
            </select>
          </div>

          <!-- Table -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">

            <!-- Loading -->
            <template v-if="loadingRise">
              <div v-for="n in 5" :key="n" class="flex items-center gap-4 border-b border-wc-border/50 px-4 py-3">
                <div class="h-8 w-8 animate-pulse rounded-full bg-wc-bg"></div>
                <div class="h-4 flex-1 animate-pulse rounded bg-wc-bg"></div>
                <div class="h-4 w-20 animate-pulse rounded bg-wc-bg"></div>
              </div>
            </template>

            <!-- Error -->
            <div v-else-if="errorRise" class="py-8 text-center">
              <p class="text-sm text-wc-text-secondary">{{ errorRise }}</p>
            </div>

            <!-- Empty -->
            <div v-else-if="!sortedParticipants.length" class="flex flex-col items-center justify-center py-12 text-center">
              <svg class="mb-3 h-12 w-12 text-wc-text-tertiary/50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
              </svg>
              <p class="text-sm font-medium text-wc-text-secondary">No hay participantes RISE</p>
              <p class="mt-1 text-xs text-wc-text-tertiary">Los participantes apareceran cuando se inscriban al programa</p>
            </div>

            <!-- Table data -->
            <div v-else class="overflow-x-auto">
              <table class="w-full text-left text-sm">
                <thead>
                  <tr class="border-b border-wc-border bg-wc-bg-secondary/50">
                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">
                      <button @click="sortByColumn('client_name')" class="flex items-center gap-1 hover:text-wc-text transition-colors">
                        Cliente
                        <svg v-if="sortBy === 'client_name'" :class="sortDir === 'asc' ? 'rotate-180' : ''" class="h-3 w-3 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                        </svg>
                      </button>
                    </th>
                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">
                      <button @click="sortByColumn('created_at')" class="flex items-center gap-1 hover:text-wc-text transition-colors">
                        Inscripcion
                        <svg v-if="sortBy === 'created_at'" :class="sortDir === 'asc' ? 'rotate-180' : ''" class="h-3 w-3 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                        </svg>
                      </button>
                    </th>
                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Periodo</th>
                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nivel</th>
                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Ubicacion</th>
                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">
                      <button @click="sortByColumn('status')" class="flex items-center gap-1 hover:text-wc-text transition-colors">
                        Estado
                        <svg v-if="sortBy === 'status'" :class="sortDir === 'asc' ? 'rotate-180' : ''" class="h-3 w-3 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                        </svg>
                      </button>
                    </th>
                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-wc-border/50">
                  <tr
                    v-for="program in sortedParticipants"
                    :key="program.id"
                    class="group hover:bg-wc-bg-secondary/30 transition-colors"
                  >
                    <td class="px-4 py-3">
                      <div class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500/20">
                          <span class="text-xs font-semibold text-emerald-500">{{ (program.client_name || '?').charAt(0).toUpperCase() }}</span>
                        </div>
                        <div>
                          <p class="font-medium text-wc-text">{{ program.client_name || 'N/A' }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="px-4 py-3 text-wc-text-secondary">{{ program.created_at || '-' }}</td>
                    <td class="px-4 py-3 text-xs text-wc-text-secondary">
                      <span v-if="program.start_date || program.end_date">
                        {{ program.start_date || '?' }} — {{ program.end_date || '?' }}
                      </span>
                      <span v-else class="text-wc-text-tertiary">-</span>
                    </td>
                    <td class="px-4 py-3">
                      <span class="inline-flex rounded-full bg-wc-bg px-2 py-0.5 text-[10px] font-semibold capitalize text-wc-text-secondary">
                        {{ program.experience_level || '-' }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-wc-text-secondary">
                      {{ locationLabel(program.training_location) }}
                    </td>
                    <td class="px-4 py-3">
                      <span :class="statusClass(program.status)" class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold capitalize">
                        {{ statusLabel(program.status) }}
                      </span>
                    </td>
                    <td class="px-4 py-3">
                      <button
                        @click="viewParticipant(program)"
                        class="rounded-lg bg-emerald-500/10 px-3 py-1.5 text-xs font-medium text-emerald-500 opacity-0 transition-all group-hover:opacity-100 hover:bg-emerald-500/20"
                      >
                        Ver detalle
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1 && !loadingRise" class="flex items-center justify-between border-t border-wc-border px-4 py-3">
              <p class="text-xs text-wc-text-tertiary">
                Pagina {{ pagination.current_page }} de {{ pagination.last_page }} ({{ pagination.total }} total)
              </p>
              <div class="flex gap-1">
                <button
                  @click="goToPage(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  class="rounded-lg border border-wc-border px-3 py-1.5 text-xs text-wc-text-secondary hover:bg-wc-bg-secondary disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                >
                  Anterior
                </button>
                <button
                  @click="goToPage(pagination.current_page + 1)"
                  :disabled="pagination.current_page === pagination.last_page"
                  class="rounded-lg border border-wc-border px-3 py-1.5 text-xs text-wc-text-secondary hover:bg-wc-bg-secondary disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                >
                  Siguiente
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <!-- ════════════════════════════════════════════════════════════════ -->
      <!-- PROGRESS TAB                                                      -->
      <!-- ════════════════════════════════════════════════════════════════ -->
      <Transition name="fade" mode="out-in">
        <div v-if="activeTab === 'progress'" key="progress" class="space-y-6">

          <!-- Loading -->
          <template v-if="loadingProgress">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
              <div v-for="n in 4" :key="n" class="h-24 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
            </div>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
              <div class="h-48 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
              <div class="h-48 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
            </div>
          </template>

          <!-- Error -->
          <div v-else-if="errorProgress" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
            <p class="text-sm text-wc-text">{{ errorProgress }}</p>
            <button @click="fetchProgress" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:opacity-90 transition-opacity">Reintentar</button>
          </div>

          <!-- Empty state when no data at all -->
          <div v-else-if="progress && !progress.totalLogs && !progress.totalMeasurements" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12">
            <div class="flex flex-col items-center justify-center text-center">
              <svg class="mb-4 h-16 w-16 text-wc-text-tertiary/30" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
              </svg>
              <h3 class="text-lg font-semibold text-wc-text">Sin datos de progreso</h3>
              <p class="mt-2 max-w-sm text-sm text-wc-text-tertiary">Los datos de progreso se mostraran cuando los participantes comiencen a registrar sus actividades diarias, mediciones y seguimiento.</p>
            </div>
          </div>

          <template v-else-if="progress">
            <!-- Activity stats from overview -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Tracking Total</p>
                <p class="mt-1 font-display text-3xl tracking-wide text-emerald-500">{{ progress.totalLogs }}</p>
                <p class="mt-0.5 text-[11px] text-wc-text-tertiary">registros de seguimiento</p>
              </div>
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Mediciones</p>
                <p class="mt-1 font-display text-3xl tracking-wide text-wc-text">{{ progress.totalMeasurements }}</p>
                <p class="mt-0.5 text-[11px] text-wc-text-tertiary">registros corporales</p>
              </div>
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Participantes</p>
                <p class="mt-1 font-display text-3xl tracking-wide text-wc-text">{{ overview?.totalPrograms ?? 0 }}</p>
                <p class="mt-0.5 text-[11px] text-wc-text-tertiary">inscritos en RISE</p>
              </div>
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Activos</p>
                <p class="mt-1 font-display text-3xl tracking-wide text-emerald-500">{{ overview?.activePrograms ?? 0 }}</p>
                <p class="mt-0.5 text-[11px] text-wc-text-tertiary">programas en curso</p>
              </div>
            </div>

            <!-- Participant progress table -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
              <h3 class="mb-4 text-sm font-semibold text-wc-text">Resumen por Participante</h3>
              <div v-if="!participants.length" class="py-4 text-center text-sm text-wc-text-tertiary">Sin participantes registrados</div>
              <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                  <thead>
                    <tr class="border-b border-wc-border">
                      <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cliente</th>
                      <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nivel</th>
                      <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Ubicacion</th>
                      <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Inicio</th>
                      <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fin</th>
                      <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-wc-border/50">
                    <tr v-for="p in participants" :key="p.id">
                      <td class="py-2.5 font-medium text-wc-text">{{ p.client_name || 'N/A' }}</td>
                      <td class="py-2.5 capitalize text-wc-text-secondary text-xs">{{ p.experience_level || '-' }}</td>
                      <td class="py-2.5 text-xs text-wc-text-secondary">{{ locationLabel(p.training_location) }}</td>
                      <td class="py-2.5 text-xs text-wc-text-secondary">{{ p.start_date || '-' }}</td>
                      <td class="py-2.5 text-xs text-wc-text-secondary">{{ p.end_date || '-' }}</td>
                      <td class="py-2.5">
                        <span :class="statusClass(p.status)" class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold capitalize">
                          {{ statusLabel(p.status) }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </template>
        </div>
      </Transition>

      <!-- ════════════════════════════════════════════════════════════════ -->
      <!-- PAYMENTS TAB                                                      -->
      <!-- ════════════════════════════════════════════════════════════════ -->
      <Transition name="fade" mode="out-in">
        <div v-if="activeTab === 'payments'" key="payments" class="space-y-4">

          <!-- Loading -->
          <template v-if="loadingPayments">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
              <div v-for="n in 3" :key="n" class="h-24 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
            </div>
            <div class="h-64 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
          </template>

          <!-- Error -->
          <div v-else-if="errorPayments" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
            <p class="text-sm text-wc-text">{{ errorPayments }}</p>
            <button @click="fetchPayments" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:opacity-90 transition-opacity">Reintentar</button>
          </div>

          <template v-else-if="paymentStats">
            <!-- Payment Stats -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Revenue Total</p>
                <p class="mt-1 font-display text-3xl tracking-wide text-emerald-500">${{ fmtNumber(paymentStats.totalRevenue) }}</p>
                <p class="mt-0.5 text-[11px] text-wc-text-tertiary">{{ paymentStats.approved }} pagos aprobados</p>
              </div>
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Total Pagos</p>
                <p class="mt-1 font-display text-3xl tracking-wide text-wc-text">{{ paymentStats.total }}</p>
              </div>
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Pendientes</p>
                <p class="mt-1 font-display text-3xl tracking-wide text-amber-500">{{ paymentStats.pending }}</p>
              </div>
            </div>

            <!-- Search + Filter -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
              <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                  v-model="paymentSearch"
                  type="text"
                  placeholder="Buscar por nombre o email..."
                  class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-9 pr-4 text-sm text-wc-text placeholder:text-wc-text-tertiary outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                />
              </div>
              <select
                v-model="paymentStatus"
                class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text outline-none focus:border-emerald-500"
              >
                <option value="all">Todos los estados</option>
                <option value="approved">Aprobado</option>
                <option value="pending">Pendiente</option>
                <option value="declined">Rechazado</option>
                <option value="voided">Anulado</option>
                <option value="error">Error</option>
              </select>
            </div>

            <!-- Payments Table -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
              <div v-if="!filteredPayments.length" class="flex flex-col items-center justify-center py-12 text-center">
                <svg class="mb-3 h-12 w-12 text-wc-text-tertiary/50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                </svg>
                <p class="text-sm font-medium text-wc-text-secondary">No hay pagos RISE</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">Los pagos apareceran cuando se procesen transacciones del plan RISE</p>
              </div>
              <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                  <thead>
                    <tr class="border-b border-wc-border bg-wc-bg-secondary/50">
                      <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cliente</th>
                      <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Monto</th>
                      <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
                      <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Metodo</th>
                      <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-wc-border/50">
                    <tr v-for="payment in filteredPayments" :key="payment.id" class="hover:bg-wc-bg-secondary/30 transition-colors">
                      <td class="px-4 py-3">
                        <p class="font-medium text-wc-text">{{ payment.buyer_name || payment.client_name || 'N/A' }}</p>
                      </td>
                      <td class="px-4 py-3">
                        <span class="font-data font-semibold text-wc-text">${{ payment.amount_fmt || fmtNumber(payment.amount) }}</span>
                      </td>
                      <td class="px-4 py-3">
                        <span :class="paymentClass(payment.status)" class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold capitalize">
                          {{ payment.status }}
                        </span>
                      </td>
                      <td class="px-4 py-3 text-xs text-wc-text-secondary">{{ payment.payment_method || '-' }}</td>
                      <td class="px-4 py-3 text-xs text-wc-text-secondary">{{ payment.created_at || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </template>
        </div>
      </Transition>

    </div>

    <!-- ════════════════════════════════════════════════════════════════════ -->
    <!-- PARTICIPANT DETAIL MODAL                                             -->
    <!-- ════════════════════════════════════════════════════════════════════ -->
    <Transition name="fade">
      <div
        v-if="showDetail && detailProgram"
        class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/60 p-4 pt-12"
        @click.self="closeDetail"
      >
        <div class="w-full max-w-3xl rounded-2xl border border-wc-border bg-wc-bg-secondary shadow-2xl">

          <!-- Modal Header -->
          <div class="flex items-center justify-between border-b border-wc-border px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/20">
                <span class="text-sm font-semibold text-emerald-500">{{ (detailProgram.client_name || '?').charAt(0).toUpperCase() }}</span>
              </div>
              <div>
                <h3 class="font-display text-xl tracking-wide text-wc-text">{{ detailProgram.client_name || 'N/A' }}</h3>
              </div>
            </div>
            <button @click="closeDetail" class="rounded-lg p-2 text-wc-text-tertiary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="space-y-5 p-6">

            <!-- Program Info Grid -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
              <div class="rounded-lg bg-wc-bg-tertiary p-3">
                <p class="text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Estado</p>
                <p :class="statusClass(detailProgram.status, 'text')" class="mt-0.5 text-sm font-semibold capitalize">
                  {{ statusLabel(detailProgram.status) }}
                </p>
              </div>
              <div class="rounded-lg bg-wc-bg-tertiary p-3">
                <p class="text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Nivel</p>
                <p class="mt-0.5 text-sm font-semibold capitalize text-wc-text">{{ detailProgram.experience_level || '-' }}</p>
              </div>
              <div class="rounded-lg bg-wc-bg-tertiary p-3">
                <p class="text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Ubicacion</p>
                <p class="mt-0.5 text-sm font-semibold capitalize text-wc-text">{{ locationLabel(detailProgram.training_location) }}</p>
              </div>
              <div class="rounded-lg bg-wc-bg-tertiary p-3">
                <p class="text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Genero</p>
                <p class="mt-0.5 text-sm font-semibold capitalize text-wc-text">{{ detailProgram.gender || '-' }}</p>
              </div>
            </div>

            <!-- Dates -->
            <div class="flex flex-wrap gap-4 text-xs text-wc-text-secondary">
              <span>Inscripcion: <strong class="text-wc-text">{{ detailProgram.created_at || '-' }}</strong></span>
              <span>Inicio: <strong class="text-wc-text">{{ detailProgram.start_date || '-' }}</strong></span>
              <span>Fin: <strong class="text-wc-text">{{ detailProgram.end_date || '-' }}</strong></span>
            </div>

            <!-- Info note: extended data -->
            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3">
              <p class="text-xs text-wc-text-tertiary">
                Para ver mediciones, tracking y daily logs detallados de este participante, acceda al perfil del cliente desde la seccion Clientes.
              </p>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex justify-end border-t border-wc-border px-6 py-4">
            <button
              @click="closeDetail"
              class="rounded-lg bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors"
            >
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </Transition>

  </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
