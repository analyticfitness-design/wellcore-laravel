<script setup>
import { onMounted, watch, computed } from 'vue';
import { RouterLink } from 'vue-router';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import StatCard from '../../../components/admin/marketing/StatCard.vue';
import StatusPill from '../../../components/admin/marketing/StatusPill.vue';
import { useAdminMarketingStore } from '../../../stores/adminMarketing';

const store = useAdminMarketingStore();

const STATUS_OPTIONS = [
    { value: '', label: 'Todos los estados' },
    { value: 'pending', label: 'Pendiente' },
    { value: 'generating', label: 'Generando' },
    { value: 'in_review', label: 'En revisión' },
    { value: 'approved', label: 'Aprobado' },
    { value: 'ready', label: 'Listo' },
    { value: 'in_progress', label: 'En progreso' },
    { value: 'completed', label: 'Completado' },
    { value: 'archived', label: 'Archivado' },
];

const queue = computed(() => store.queue);
const meta = computed(() => store.meta);
const isLoading = computed(() => store.isLoadingQueue);

let coachTimer = null;
let yearTimer = null;
let weekTimer = null;

function refetch() {
    store.fetchQueue({ page: 1 });
}

function onStatusChange(e) {
    store.filters.status = e.target.value;
    refetch();
}

function onCoachInput(e) {
    store.filters.coach_id = e.target.value;
    clearTimeout(coachTimer);
    coachTimer = setTimeout(refetch, 300);
}

function onYearInput(e) {
    store.filters.iso_year = e.target.value;
    clearTimeout(yearTimer);
    yearTimer = setTimeout(refetch, 300);
}

function onWeekInput(e) {
    store.filters.iso_week = e.target.value;
    clearTimeout(weekTimer);
    weekTimer = setTimeout(refetch, 300);
}

function formatDate(iso) {
    if (!iso) return '—';
    try {
        const d = new Date(iso);
        return d.toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' });
    } catch {
        return iso;
    }
}

onMounted(() => {
    store.fetchQueue();
});
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-6xl px-6 py-12 space-y-8">
      <div>
        <p class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">WC · ADMIN / MARKETING / QUEUE</p>
        <h1 class="mt-2 font-display text-4xl uppercase tracking-tight text-wc-text">Cola de drops</h1>
        <p class="mt-2 text-sm text-wc-text-secondary">Revisa, aprueba y gestiona los drops semanales de los coaches.</p>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <StatCard :n="meta.pending_review_count ?? 0" label="Pending review" accent />
        <StatCard :n="meta.coaches_without_drop_this_week ?? 0" label="Sin drop esta semana" />
        <StatCard :n="meta.total ?? 0" label="Total drops" />
      </div>

      <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
          <div>
            <label class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Estado</label>
            <select :value="store.filters.status" @change="onStatusChange"
              class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
              <option v-for="opt in STATUS_OPTIONS" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
          </div>
          <div>
            <label class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Coach ID</label>
            <input type="number" :value="store.filters.coach_id" @input="onCoachInput" placeholder="ej. 12"
              class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" />
          </div>
          <div>
            <label class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">ISO year</label>
            <input type="number" :value="store.filters.iso_year" @input="onYearInput" placeholder="2026"
              class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" />
          </div>
          <div>
            <label class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">ISO week</label>
            <input type="number" :value="store.filters.iso_week" @input="onWeekInput" placeholder="17"
              class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" />
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-wc-border bg-wc-bg-secondary overflow-hidden">
        <div class="grid grid-cols-12 gap-4 border-b border-wc-border bg-wc-bg px-4 py-3">
          <div class="col-span-3 font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Coach</div>
          <div class="col-span-2 font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Semana</div>
          <div class="col-span-2 font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Estado</div>
          <div class="col-span-3 font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Última acción</div>
          <div class="col-span-2 text-right font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Acción</div>
        </div>

        <div v-if="isLoading" class="px-4 py-12 text-center text-sm text-wc-text-tertiary">Cargando…</div>

        <div v-else-if="queue.length === 0" class="px-4 py-12 text-center text-sm text-wc-text-tertiary">
          Sin drops que coincidan con los filtros.
        </div>

        <div v-else>
          <div v-for="row in queue" :key="row.id"
            class="grid grid-cols-12 items-center gap-4 border-b border-wc-border px-4 py-3 last:border-b-0 hover:bg-wc-bg">
            <div class="col-span-3 text-sm text-wc-text">
              <p class="font-medium">{{ row.coach?.name ?? '—' }}</p>
              <p class="font-mono text-[10px] uppercase text-wc-text-tertiary">ID #{{ row.coach?.id }}</p>
            </div>
            <div class="col-span-2 font-mono text-sm text-wc-text-secondary">
              {{ row.iso_year }}-W{{ String(row.iso_week).padStart(2, '0') }}
            </div>
            <div class="col-span-2">
              <StatusPill :status="row.status" />
            </div>
            <div class="col-span-3 font-mono text-xs text-wc-text-tertiary">
              {{ formatDate(row.last_action_at) }}
            </div>
            <div class="col-span-2 text-right">
              <RouterLink :to="`/admin/marketing/drops/${row.id}`"
                class="inline-flex items-center gap-1 rounded-lg border border-wc-border bg-wc-bg px-3 py-1.5 text-xs font-medium text-wc-text hover:border-wc-accent hover:text-wc-accent">
                Revisar →
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
