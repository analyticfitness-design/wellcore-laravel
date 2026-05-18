<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';
import WcPageHeader from '../../components/WcPageHeader.vue';
import AvatarConic from '../../components/coach/ios/AvatarConic.vue';
import EmptyState from '../../components/coach/ios/EmptyState.vue';

const { t, locale } = useI18n();
const api = useApi();
const loading = ref(true);
const search = ref('');
const statusFilter = ref('');
const sortBy = ref('name');
const clients = ref([]);
const expandedClient = ref(null);
const totalClients = ref(0);

let debounceTimer = null;

// ─── Toast ────────────────────────────────────────────────────────────────────
const toast = ref({ show: false, type: 'success', message: '' });
function showToast(message, type = 'success') {
  toast.value = { show: true, type, message };
  setTimeout(() => { toast.value.show = false; }, 4000);
}

// ─── Impersonation ────────────────────────────────────────────────────────────
const impersonating = ref(false);
async function impersonateClient(client) {
  if (impersonating.value) return;
  impersonating.value = true;
  try {
    const { data } = await api.post(`/api/v/coach/clients/${client.id}/impersonate`);
    // Backup the coach session
    localStorage.setItem('wc_token_backup',     localStorage.getItem('wc_token') || '');
    localStorage.setItem('wc_user_type_backup', localStorage.getItem('wc_user_type') || '');
    localStorage.setItem('wc_user_id_backup',   localStorage.getItem('wc_user_id') || '');
    localStorage.setItem('wc_user_name_backup', localStorage.getItem('wc_user_name') || '');
    localStorage.setItem('wc_user_portal_backup', localStorage.getItem('wc_user_portal') || '/coach');
    // Swap to client session
    localStorage.setItem('wc_token',      data.token);
    localStorage.setItem('wc_user_type',  'client');
    localStorage.setItem('wc_user_id',    String(data.client_id));
    localStorage.setItem('wc_user_name',  data.client_name || t('coach_home.impersonate_client_default'));
    localStorage.setItem('wc_user_portal', '/client');
    localStorage.setItem('wc_impersonating_by_coach', '1');
    localStorage.setItem('wc_impersonating_token_key', data.token);
    localStorage.setItem('wc_impersonation_client_id', String(data.client_id));
    // Timestamp del expiry para auto-restore si el coach vuelve después
    if (data.expires_at) {
      const expiresMs = new Date(data.expires_at).getTime();
      if (!isNaN(expiresMs)) {
        localStorage.setItem('wc_impersonation_expires_at', String(expiresMs));
      }
    }
    // Hard redirect so Pinia reloads with the new token
    window.location.href = data.redirect_url || '/client';
  } catch (err) {
    showToast(err.response?.data?.error || t('coach_home.impersonate_fail'), 'error');
    impersonating.value = false;
  }
}

// ─── Client action requests ───────────────────────────────────────────────────
const requestModal = ref({ show: false, action: 'deactivate', client: null, reason: '', loading: false, error: '' });
const actionLabels = computed(() => ({
  delete:     { title: t('coach_home.req_action_delete_title'),     short: t('coach_home.req_action_delete_short'),     color: 'red'    },
  deactivate: { title: t('coach_home.req_action_deactivate_title'), short: t('coach_home.req_action_deactivate_short'), color: 'amber'  },
  edit:       { title: t('coach_home.req_action_edit_title'),       short: t('coach_home.req_action_edit_short'),       color: 'blue'   },
}));

const statusLabels = computed(() => ({
  pending:   t('coach_home.req_status_pending'),
  approved:  t('coach_home.req_status_approved'),
  rejected:  t('coach_home.req_status_rejected'),
  cancelled: t('coach_home.req_status_cancelled'),
}));

function openRequest(action, client) {
  requestModal.value = { show: true, action, client, reason: '', loading: false, error: '' };
}

async function submitRequest() {
  const m = requestModal.value;
  if (!m.client) return;
  if ((m.reason || '').trim().length < 10) {
    m.error = t('coach_home.req_modal_min_chars_err');
    return;
  }
  m.loading = true;
  m.error = '';
  try {
    await api.post(`/api/v/coach/clients/${m.client.id}/requests`, {
      action: m.action,
      reason: m.reason.trim(),
    });
    m.show = false;
    showToast(t('coach_home.req_sent_toast'));
    // refresh this client's requests panel
    await loadClientRequests(m.client.id);
  } catch (err) {
    if (err.response?.status === 422) {
      m.error = Object.values(err.response.data.errors || {}).flat()[0] || t('coach_home.req_modal_invalid');
    } else {
      m.error = err.response?.data?.error || t('coach_home.req_modal_generic_err');
    }
  } finally {
    m.loading = false;
  }
}

const clientRequests = ref({});   // { [clientId]: [] }
const requestsLoading = ref({});  // { [clientId]: bool }

async function loadClientRequests(clientId) {
  requestsLoading.value = { ...requestsLoading.value, [clientId]: true };
  try {
    const { data } = await api.get(`/api/v/coach/clients/${clientId}/requests`);
    clientRequests.value = { ...clientRequests.value, [clientId]: data.requests || data || [] };
  } catch {
    clientRequests.value = { ...clientRequests.value, [clientId]: [] };
  } finally {
    requestsLoading.value = { ...requestsLoading.value, [clientId]: false };
  }
}

async function cancelRequest(clientId, requestId) {
  if (!confirm(t('coach_home.req_confirm_cancel'))) return;
  try {
    await api.delete(`/api/v/coach/client-requests/${requestId}`);
    showToast(t('coach_home.req_cancelled_toast'));
    await loadClientRequests(clientId);
  } catch (err) {
    showToast(err.response?.data?.error || t('coach_home.req_cancel_failed'), 'error');
  }
}

function requestStatusClass(status) {
  return ({
    pending:   'bg-amber-500/10 text-amber-400',
    approved:  'bg-emerald-500/10 text-emerald-500',
    rejected:  'bg-red-500/10 text-red-400',
    cancelled: 'bg-wc-bg-secondary text-wc-text-tertiary',
  })[status] || 'bg-wc-bg-secondary text-wc-text-tertiary';
}

function requestActionClass(action) {
  return ({
    delete:     'bg-red-500/10 text-red-400',
    deactivate: 'bg-amber-500/10 text-amber-400',
    edit:       'bg-wc-bg-secondary text-wc-text-secondary',
  })[action] || 'bg-wc-bg-secondary text-wc-text-tertiary';
}

function formatDateTime(iso) {
  if (!iso) return '';
  const intlLocale = locale.value === 'en' ? 'en-US' : 'es-MX';
  try {
    return new Date(iso).toLocaleString(intlLocale, { dateStyle: 'short', timeStyle: 'short' });
  } catch { return ''; }
}

function pluralFromPipe(key, count, params = {}) {
  const raw = t(key, params);
  const parts = raw.split('|');
  return count === 1 ? parts[0] : (parts[1] || parts[0]);
}

const filteredClients = computed(() => {
    let list = clients.value;
    if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(c => c.name.toLowerCase().includes(q));
    }
    if (statusFilter.value) {
        list = list.filter(c => c.status === statusFilter.value);
    }
    if (sortBy.value === 'name') {
        list = [...list].sort((a, b) => a.name.localeCompare(b.name));
    } else if (sortBy.value === 'activity') {
        list = [...list].sort((a, b) => (a.days_since_activity || 999) - (b.days_since_activity || 999));
    } else if (sortBy.value === 'adherence') {
        list = [...list].sort((a, b) => (b.adherence || 0) - (a.adherence || 0));
    }
    return list;
});

function toggleExpand(id) {
    expandedClient.value = expandedClient.value === id ? null : id;
    if (expandedClient.value === id && !clientRequests.value[id]) {
      loadClientRequests(id);
    }
}

async function loadClients() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v/coach/clients');
        clients.value = data.clients || [];
        totalClients.value = data.totalClients || clients.value.length;
    } catch (e) {
        clients.value = [];
    } finally {
        loading.value = false;
    }
}

watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(loadClients, 300);
});

onMounted(loadClients);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <WcPageHeader :contextLabel="t('coach_home.list_context')" :title="t('coach_home.list_title')" :subtitle="pluralFromPipe('coach_home.list_subtitle', totalClients, { n: totalClients })">
        <template #actions>
          <div class="flex items-center gap-2">
            <RouterLink
              to="/coach/kanban"
              class="flex h-9 w-9 items-center justify-center rounded-button border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
              :title="t('coach_home.list_view_kanban')"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z" />
              </svg>
            </RouterLink>
            <div class="relative w-64">
              <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
              </svg>
              <input
                v-model="search"
                type="text"
                :placeholder="t('coach_home.list_search_placeholder')"
                class="w-full rounded-button border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              />
            </div>
            <select
              v-model="statusFilter"
              class="rounded-button border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
            >
              <option value="">{{ t('coach_home.list_filter_all') }}</option>
              <option value="active">{{ t('coach_home.list_filter_active') }}</option>
              <option value="risk">{{ t('coach_home.list_filter_risk') }}</option>
              <option value="inactive">{{ t('coach_home.list_filter_inactive') }}</option>
            </select>
          </div>
        </template>
      </WcPageHeader>

      <!-- Loading skeletons -->
      <template v-if="loading">
        <div v-for="n in 4" :key="n" class="animate-pulse rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
          <div class="flex items-center gap-4">
            <div class="h-11 w-11 shrink-0 rounded-full bg-wc-border/50"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 w-36 rounded bg-wc-border/50"></div>
              <div class="h-3 w-48 rounded bg-wc-border/30"></div>
            </div>
            <div class="hidden sm:block h-6 w-16 rounded-full bg-wc-border/30"></div>
          </div>
        </div>
      </template>

      <!-- Client cards (iOS panel style) -->
      <div v-else-if="filteredClients.length > 0" class="space-y-3 anim-entry anim-entry-2">
        <div
          v-for="client in filteredClients"
          :key="client.id"
          class="rounded-[14px] border border-[var(--b1)] overflow-hidden transition"
          style="background: var(--s2); box-shadow: var(--shadow-card-ios);"
        >
          <!-- Client row -->
          <button
            @click="toggleExpand(client.id)"
            class="flex w-full items-center gap-4 p-4 text-left hover:bg-[var(--s2)] transition"
            style="transition-duration: var(--t-tap);"
          >
            <!-- Avatar conic ring -->
            <AvatarConic
              :initial="client.avatar_initial || (client.name || 'C').charAt(0).toUpperCase()"
              :tone="client.status === 'risk' ? 'accent' : (client.adherence >= 80 ? 'gold' : 'accent')"
              size="md"
            />

            <!-- Name + plan + badges -->
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 flex-wrap">
                <p class="text-sm font-medium text-wc-text truncate">{{ client.name }}</p>
                <span class="inline-flex shrink-0 rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold text-wc-accent">
                  {{ client.plan_label || t('coach_home.list_no_plan') }}
                </span>
                <span
                  v-if="client.pending_checkins > 0"
                  class="inline-flex shrink-0 rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold text-wc-accent"
                >{{ pluralFromPipe('coach_home.list_pending_count', client.pending_checkins, { n: client.pending_checkins }) }}</span>
                <!-- Vue enhancement: risk badge -->
                <span
                  v-if="client.status === 'risk'"
                  class="inline-flex shrink-0 rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold text-wc-accent"
                >{{ t('coach_home.list_risk_badge') }}</span>
              </div>
              <div class="mt-0.5 flex items-center gap-4 text-xs text-wc-text-tertiary">
                <span>{{ t('coach_home.list_last_checkin', { value: client.last_checkin || t('coach_home.list_never') }) }}</span>
                <span class="hidden sm:inline">{{ t('coach_home.list_last_message', { value: client.last_message || t('coach_home.list_no_messages') }) }}</span>
              </div>
            </div>

            <!-- XP Level badge + adherence (right side) -->
            <div class="hidden sm:flex items-center gap-2">
              <!-- XP Level badge (from Blade) -->
              <div class="flex items-center gap-1 rounded-full bg-wc-accent/10 px-2.5 py-1">
                <svg class="h-3 w-3 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
                <span class="text-[11px] font-semibold text-wc-accent">{{ t('coach_home.list_level_short', { n: client.xp_level || 1 }) }}</span>
              </div>
              <!-- Adherence (Vue enhancement) -->
              <div v-if="client.adherence !== undefined" class="flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2.5 py-1">
                <span class="font-data text-[11px] font-semibold text-wc-text-secondary">{{ client.adherence }}%</span>
              </div>
            </div>

            <!-- Expand chevron -->
            <svg
              class="h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform"
              :class="{ 'rotate-180': expandedClient === client.id }"
              fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </button>

          <!-- Expanded details -->
          <div v-show="expandedClient === client.id" class="border-t border-wc-border bg-wc-bg-secondary/30 px-4 py-4">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ t('coach_home.list_detail_xp_total') }}</p>
                <p class="mt-1 font-data text-sm font-semibold text-wc-text">{{ (client.xp_total || 0).toLocaleString() }}</p>
              </div>
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ t('coach_home.list_detail_streak') }}</p>
                <p class="mt-1 font-data text-sm font-semibold text-wc-text">{{ t('coach_home.list_detail_streak_days', { n: client.streak_days || 0 }) }}</p>
              </div>
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ t('coach_home.list_detail_start_date') }}</p>
                <p class="mt-1 text-sm font-semibold text-wc-text">{{ client.fecha_inicio || t('coach_home.list_na') }}</p>
              </div>
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ t('coach_home.list_detail_last_checkin') }}</p>
                <p class="mt-1 text-sm font-semibold text-wc-text">{{ client.last_checkin_date || t('coach_home.list_na') }}</p>
              </div>
            </div>

            <!-- XP level badge (visible on mobile, hidden on sm+ where it shows in the row) -->
            <div class="mt-3 flex items-center gap-2 sm:hidden">
              <div class="flex items-center gap-1 rounded-full bg-wc-accent/10 px-2.5 py-1">
                <svg class="h-3 w-3 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
                <span class="text-[11px] font-semibold text-wc-accent">{{ t('coach_home.list_level_short', { n: client.xp_level || 1 }) }}</span>
              </div>
            </div>

            <div class="mt-4 flex items-center gap-2">
              <RouterLink
                to="/coach/checkins"
                class="inline-flex items-center gap-1.5 rounded-button bg-wc-accent px-3 py-1.5 text-xs font-medium text-white hover:bg-wc-accent-hover transition-colors"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                {{ t('coach_home.list_action_checkins') }}
              </RouterLink>
              <RouterLink
                to="/coach/messages"
                class="inline-flex items-center gap-1.5 rounded-button border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
                {{ t('coach_home.list_action_message') }}
              </RouterLink>

              <!-- Impersonate: coach-only feature to see the client's portal -->
              <button
                type="button"
                @click="impersonateClient(client)"
                :disabled="impersonating"
                class="inline-flex items-center gap-1.5 rounded-button border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text hover:border-wc-accent hover:text-wc-accent transition-colors disabled:opacity-50"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                {{ t('coach_home.list_action_view_as') }}
              </button>
            </div>

            <!-- Request actions -->
            <div class="mt-4 rounded-card border border-wc-border bg-wc-bg p-4">
              <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                  <p class="font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary">{{ t('coach_home.list_restricted_title') }}</p>
                  <p class="mt-0.5 text-[11px] text-wc-text-tertiary">{{ t('coach_home.list_restricted_sub') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                  <button
                    type="button"
                    @click="openRequest('deactivate', client)"
                    class="inline-flex items-center gap-1.5 rounded-button border border-wc-accent/30 bg-wc-accent/10 px-3 py-1.5 text-xs font-medium text-wc-accent hover:bg-wc-accent/20 transition-colors"
                  >
                    {{ t('coach_home.list_request_deactivate') }}
                  </button>
                  <button
                    type="button"
                    @click="openRequest('delete', client)"
                    class="inline-flex items-center gap-1.5 rounded-button border border-red-500/40 bg-red-500/10 px-3 py-1.5 text-xs font-medium text-red-400 hover:bg-red-500/20 transition-colors"
                  >
                    {{ t('coach_home.list_request_delete') }}
                  </button>
                  <button
                    type="button"
                    @click="openRequest('edit', client)"
                    class="inline-flex items-center gap-1.5 rounded-button border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary transition-colors"
                  >
                    {{ t('coach_home.list_request_edit') }}
                  </button>
                </div>
              </div>

              <!-- Previous requests -->
              <div class="mt-4 space-y-2">
                <template v-if="requestsLoading[client.id]">
                  <div class="h-12 animate-pulse rounded-button bg-wc-bg-tertiary"></div>
                </template>
                <template v-else-if="(clientRequests[client.id] || []).length">
                  <div
                    v-for="req in clientRequests[client.id]"
                    :key="req.id"
                    class="rounded-button border border-wc-border bg-wc-bg-tertiary p-3"
                  >
                    <div class="flex flex-wrap items-center gap-2">
                      <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="requestActionClass(req.action)">
                        {{ actionLabels[req.action]?.short || req.action }}
                      </span>
                      <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="requestStatusClass(req.status)">
                        {{ statusLabels[req.status] || req.status }}
                      </span>
                      <span class="text-[11px] text-wc-text-tertiary">{{ formatDateTime(req.created_at) }}</span>
                      <button
                        v-if="req.status === 'pending'"
                        @click="cancelRequest(client.id, req.id)"
                        class="ml-auto text-[11px] font-medium text-wc-text-tertiary hover:text-red-400"
                      >{{ t('coach_home.list_request_cancel') }}</button>
                    </div>
                    <p v-if="req.reason" class="mt-1.5 text-xs text-wc-text-secondary">{{ req.reason }}</p>
                    <p v-if="req.status === 'rejected' && req.admin_notas" class="mt-1.5 rounded bg-red-500/5 p-2 text-[11px] text-red-400">
                      {{ t('coach_home.list_admin_note', { note: req.admin_notas }) }}
                    </p>
                  </div>
                </template>
                <p v-else class="text-[11px] text-wc-text-tertiary">{{ t('coach_home.list_no_requests') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state iOS -->
      <EmptyState
        v-else
        kind="search"
        :title="t('coach_home.list_empty_title')"
        :subtitle="search ? t('coach_home.list_empty_sub_search', { query: search }) : t('coach_home.list_empty_sub_default')"
      />
    </div>

    <!-- ==================== REQUEST MODAL ==================== -->
    <Transition name="fade">
      <div v-if="requestModal.show" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="requestModal.show = false"></div>
        <Transition name="slide-up">
          <div v-if="requestModal.show" class="relative z-10 w-full max-w-md rounded-card border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
            <h3 class="font-display text-xl tracking-wide text-wc-text">
              {{ (actionLabels[requestModal.action]?.title || t('coach_home.req_modal_default_title')).toUpperCase() }}
            </h3>
            <p class="mt-1 text-sm text-wc-text-secondary">
              {{ t('coach_home.req_modal_client') }} <span class="font-semibold text-wc-text">{{ requestModal.client?.name }}</span>
            </p>
            <div class="mt-4">
              <label class="mb-1.5 block font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary">
                {{ t('coach_home.req_modal_reason') }} <span class="text-wc-accent">*</span>
              </label>
              <textarea
                v-model="requestModal.reason"
                rows="4"
                :placeholder="t('coach_home.req_modal_reason_placeholder')"
                class="w-full resize-none rounded-button border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
              ></textarea>
              <p class="mt-1 text-[10px] text-wc-text-tertiary">
                {{ t('coach_home.req_modal_chars_min', { n: (requestModal.reason || '').length }) }}
              </p>
            </div>
            <p v-if="requestModal.error" class="mt-3 rounded-button border border-red-500/40 bg-red-500/10 p-3 text-xs text-red-400">
              {{ requestModal.error }}
            </p>
            <div class="mt-5 flex gap-3">
              <button @click="requestModal.show = false"
                      class="flex-1 rounded-button border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text">
                {{ t('coach_home.req_modal_cancel') }}
              </button>
              <button @click="submitRequest" :disabled="requestModal.loading"
                      class="flex-1 rounded-button bg-wc-accent py-2.5 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-60">
                {{ requestModal.loading ? t('coach_home.req_modal_sending') : t('coach_home.req_modal_submit') }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- ==================== TOAST ==================== -->
    <Transition name="slide-up">
      <div v-if="toast.show"
           class="fixed bottom-6 left-1/2 z-[100] -translate-x-1/2 rounded-card border px-5 py-3 text-sm font-medium shadow-xl"
           :class="toast.type === 'error'
              ? 'border-red-500/40 bg-red-500/10 text-red-400'
              : 'border-emerald-500/40 bg-emerald-500/10 text-emerald-400'">
        {{ toast.message }}
      </div>
    </Transition>
  </CoachLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.3s ease, opacity 0.3s ease; }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(1.5rem); opacity: 0; }
</style>
