<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';
import DeadlineBadge from '../../components/DeadlineBadge.vue';
import WcPageHeader from '../../components/WcPageHeader.vue';

const { t } = useI18n();
const api = useApi();
const router = useRouter();

const loading = ref(true);
const tickets = ref([]);
const activeStatus = ref('all');
const duplicatingId = ref(null);
const toast = ref(null);

const STATUS_TABS = computed(() => [
  { key: 'all', label: t('coach_ops.tickets_tab_all') },
  { key: 'borrador', label: t('coach_ops.tickets_tab_drafts') },
  { key: 'pendiente', label: t('coach_ops.tickets_tab_sent') },
  { key: 'en_revision', label: t('coach_ops.tickets_tab_under_review') },
  { key: 'completado', label: t('coach_ops.tickets_tab_completed') },
  { key: 'rechazado', label: t('coach_ops.tickets_tab_rejected') },
]);

const PLAN_TYPE_META = computed(() => ({
  esencial: { label: t('coach_ops.tickets_plan_type_essential'), bg: 'bg-wc-bg-secondary', text: 'text-wc-text-secondary' },
  metodo: { label: t('coach_ops.tickets_plan_type_method'), bg: 'bg-wc-accent/10', text: 'text-wc-accent' },
  elite: { label: t('coach_ops.tickets_plan_type_elite'), bg: 'bg-wc-accent/20', text: 'text-wc-accent' },
}));

const CATEGORY_META = computed(() => ({
  plan_nuevo: { label: t('coach_ops.tickets_category_new_plan'), bg: 'bg-wc-bg-secondary', text: 'text-wc-text-secondary' },
  ajuste_plan: { label: t('coach_ops.tickets_category_adjustment'), bg: 'bg-wc-accent/10', text: 'text-wc-accent' },
}));
function categoryMeta(c) { return CATEGORY_META.value[c] || CATEGORY_META.value.plan_nuevo; }

const STATUS_META = computed(() => ({
  borrador: { label: t('coach_ops.tickets_status_draft'), bg: 'bg-wc-bg-tertiary', text: 'text-wc-text-secondary border border-wc-border' },
  pendiente: { label: t('coach_ops.tickets_status_sent'), bg: 'bg-wc-accent/15', text: 'text-wc-accent border border-wc-accent/30' },
  en_revision: { label: t('coach_ops.tickets_status_under_review'), bg: 'bg-wc-accent/30', text: 'text-wc-accent border border-wc-accent/50' },
  completado: { label: t('coach_ops.tickets_status_completed'), bg: 'bg-emerald-500/10', text: 'text-emerald-400 border border-emerald-500/30' },
  rechazado: { label: t('coach_ops.tickets_status_rejected'), bg: 'bg-wc-accent', text: 'text-white' },
}));

const counts = computed(() => {
  const out = { all: tickets.value.length, borrador: 0, pendiente: 0, en_revision: 0, completado: 0, rechazado: 0 };
  for (const t of tickets.value) {
    if (out[t.status] !== undefined) out[t.status]++;
  }
  return out;
});

const filteredTickets = computed(() => {
  if (activeStatus.value === 'all') return tickets.value;
  return tickets.value.filter(t => t.status === activeStatus.value);
});

async function fetchTickets() {
  loading.value = true;
  try {
    const { data } = await api.get('/api/v/coach/plan-tickets');
    tickets.value = data.tickets || [];
  } catch (e) {
    tickets.value = [];
  } finally {
    loading.value = false;
  }
}

function formatDate(d) {
  if (!d) return '-';
  try {
    return new Date(d).toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' });
  } catch {
    return d;
  }
}

function goToNew() {
  router.push('/coach/plan-tickets/nuevo');
}

function goToTicket(id) {
  router.push(`/coach/plan-tickets/${id}`);
}

async function duplicateTicket(id, event) {
  if (event) event.stopPropagation();
  if (duplicatingId.value) return;
  duplicatingId.value = id;
  try {
    const { data } = await api.post(`/api/v/coach/plan-tickets/${id}/duplicate`);
    if (data?.ticket?.id) {
      showToast('success', t('coach_ops.tickets_toast_duplicate_success'));
      setTimeout(() => router.push(`/coach/plan-tickets/${data.ticket.id}`), 400);
    } else {
      showToast('error', t('coach_ops.tickets_toast_duplicate_error'));
    }
  } catch (e) {
    showToast('error', t('coach_ops.tickets_toast_duplicate_error'));
  } finally {
    duplicatingId.value = null;
  }
}

function showToast(type, message) {
  toast.value = { type, message };
  setTimeout(() => { toast.value = null; }, 3000);
}

function planTypeMeta(type) {
  return PLAN_TYPE_META.value[type] || PLAN_TYPE_META.value.esencial;
}

function statusMeta(s) {
  return STATUS_META.value[s] || STATUS_META.value.borrador;
}

onMounted(fetchTickets);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Toast -->
      <Transition name="fade">
        <div
          v-if="toast"
          class="fixed top-20 right-4 z-50 rounded-card border px-4 py-3 shadow-lg"
          :class="toast.type === 'success' ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-500' : 'border-red-500/30 bg-red-500/10 text-red-400'"
        >
          {{ toast.message }}
        </div>
      </Transition>

      <WcPageHeader :contextLabel="t('coach_ops.tickets_context_label')" :title="t('coach_ops.tickets_title')" :subtitle="t('coach_ops.tickets_subtitle')">
        <template #actions>
          <button
            @click="goToNew"
            class="inline-flex items-center gap-2 rounded-button bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            {{ t('coach_ops.tickets_create_cta') }}
          </button>
        </template>
      </WcPageHeader>

      <!-- Status tabs -->
      <div class="flex flex-wrap items-center gap-1 border-b border-wc-border">
        <button
          v-for="tab in STATUS_TABS"
          :key="tab.key"
          @click="activeStatus = tab.key"
          class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium transition-colors border-b-2 -mb-px"
          :class="activeStatus === tab.key ? 'border-wc-accent text-wc-text' : 'border-transparent text-wc-text-tertiary hover:text-wc-text'"
        >
          {{ tab.label }}
          <span
            class="rounded-full px-1.5 text-[10px] font-semibold bg-wc-bg-secondary"
            :class="activeStatus === tab.key ? 'text-wc-accent' : 'text-wc-text-tertiary'"
          >{{ counts[tab.key] ?? 0 }}</span>
        </button>
      </div>

      <!-- Loading -->
      <template v-if="loading">
        <div v-for="n in 3" :key="n" class="animate-pulse rounded-card border border-wc-border bg-wc-bg-tertiary h-24"></div>
      </template>

      <!-- Empty -->
      <div v-else-if="filteredTickets.length === 0" class="rounded-card border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <svg class="mx-auto h-10 w-10 text-wc-text-tertiary/50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
        </svg>
        <p class="mt-3 text-sm text-wc-text-secondary">{{ t('coach_ops.tickets_empty_msg') }}</p>
        <button
          @click="goToNew"
          class="mt-4 rounded-button bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors"
        >{{ t('coach_ops.tickets_empty_cta') }}</button>
      </div>

      <!-- List -->
      <div v-else class="space-y-3">
        <div
          v-for="ticket in filteredTickets"
          :key="ticket.id"
          @click="goToTicket(ticket.id)"
          class="group cursor-pointer rounded-card border border-wc-border bg-wc-bg-tertiary p-4 hover:border-wc-accent/40 transition"
        >
          <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 flex-wrap">
                <p class="font-display text-lg tracking-wide text-wc-text truncate">{{ ticket.client_name || t('coach_ops.tickets_no_client_name') }}</p>
                <span
                  class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                  :class="[planTypeMeta(ticket.plan_type).bg, planTypeMeta(ticket.plan_type).text]"
                >{{ planTypeMeta(ticket.plan_type).label }}</span>
                <span
                  v-if="ticket.category"
                  class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                  :class="[categoryMeta(ticket.category).bg, categoryMeta(ticket.category).text]"
                >{{ categoryMeta(ticket.category).label }}</span>
                <span
                  class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                  :class="[statusMeta(ticket.status).bg, statusMeta(ticket.status).text]"
                >{{ statusMeta(ticket.status).label }}</span>
                <DeadlineBadge :deadline="ticket.deadline_at" :status="ticket.status" />
              </div>
              <div class="mt-2 flex items-center gap-4 text-xs text-wc-text-tertiary">
                <span>{{ t('coach_ops.tickets_created_at', { date: formatDate(ticket.created_at) }) }}</span>
                <span v-if="ticket.submitted_at">{{ t('coach_ops.tickets_submitted_at', { date: formatDate(ticket.submitted_at) }) }}</span>
              </div>
            </div>
            <div class="flex items-center gap-2 shrink-0" @click.stop>
              <button
                type="button"
                @click="duplicateTicket(ticket.id, $event)"
                :disabled="duplicatingId === ticket.id"
                class="inline-flex items-center gap-1 rounded-md border border-wc-border bg-wc-bg-secondary px-2.5 py-1 text-xs font-medium text-wc-text-secondary hover:border-wc-accent/40 hover:text-wc-text transition disabled:opacity-50"
                :title="t('coach_ops.tickets_action_duplicate_title')"
                :aria-label="t('coach_ops.tickets_action_duplicate_title')"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m0 0h5.625c.621 0 1.125.504 1.125 1.125v4.125M8.25 6.75h6" />
                </svg>
                {{ duplicatingId === ticket.id ? t('coach_ops.tickets_action_duplicate_progress') : t('coach_ops.tickets_action_duplicate') }}
              </button>
              <button
                type="button"
                @click="goToTicket(ticket.id)"
                class="inline-flex items-center gap-1 rounded-md bg-wc-accent/10 px-2.5 py-1 text-xs font-semibold text-wc-accent hover:bg-wc-accent/20 transition"
              >
                {{ ticket.is_editable ? t('coach_ops.tickets_action_edit') : t('coach_ops.tickets_action_view') }}
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </CoachLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
