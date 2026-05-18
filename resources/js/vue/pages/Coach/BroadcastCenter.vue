<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';
import WcPageHeader from '../../components/WcPageHeader.vue';
import AvatarConic from '../../components/coach/ios/AvatarConic.vue';
import EmptyState from '../../components/coach/ios/EmptyState.vue';

const { t } = useI18n();
const api = useApi();
const loading = ref(false);
const sending = ref(false);
const success = ref(false);
const error = ref('');

const recipientMode = ref('all');
const selectedClients = ref([]);
const messageText = ref('');
const clients = ref([]);

const builtInTemplates = computed(() => [
    { id: 'b1', category: t('coach_growth.broadcast.tpl_b1_category'), name: t('coach_growth.broadcast.tpl_b1_name'), message: t('coach_growth.broadcast.tpl_b1_message') },
    { id: 'b2', category: t('coach_growth.broadcast.tpl_b2_category'), name: t('coach_growth.broadcast.tpl_b2_name'), message: t('coach_growth.broadcast.tpl_b2_message') },
    { id: 'b3', category: t('coach_growth.broadcast.tpl_b3_category'), name: t('coach_growth.broadcast.tpl_b3_name'), message: t('coach_growth.broadcast.tpl_b3_message') },
    { id: 'b4', category: t('coach_growth.broadcast.tpl_b4_category'), name: t('coach_growth.broadcast.tpl_b4_name'), message: t('coach_growth.broadcast.tpl_b4_message') },
    { id: 'b5', category: t('coach_growth.broadcast.tpl_b5_category'), name: t('coach_growth.broadcast.tpl_b5_name'), message: t('coach_growth.broadcast.tpl_b5_message') },
    { id: 'b6', category: t('coach_growth.broadcast.tpl_b6_category'), name: t('coach_growth.broadcast.tpl_b6_name'), message: t('coach_growth.broadcast.tpl_b6_message') },
]);

const showTemplates = ref(false);

const recipientLabel = computed(() => {
    switch (recipientMode.value) {
        case 'all': return t('coach_growth.broadcast.recipient_label_all');
        case 'plan': return t('coach_growth.broadcast.recipient_label_plan');
        case 'status': return t('coach_growth.broadcast.recipient_label_status');
        case 'individual': return t('coach_growth.broadcast.recipient_label_individual');
        default: return t('coach_growth.broadcast.recipient_label_default');
    }
});

function useTemplate(template) {
    messageText.value = template.message;
    showTemplates.value = false;
}

async function sendBroadcast() {
    if (!messageText.value.trim()) return;
    sending.value = true;
    success.value = false;
    try {
        error.value = '';
        await api.post('/api/v/coach/broadcast', {
            recipient_mode: recipientMode.value,
            selected_client_ids: selectedClients.value,
            message: messageText.value,
        });
        success.value = true;
        messageText.value = '';
        selectedClients.value = [];
        setTimeout(() => { success.value = false; }, 4000);
    } catch (e) {
        error.value = e?.response?.data?.message || t('coach_growth.broadcast.toast_error_default');
    } finally {
        sending.value = false;
    }
}

async function loadClients() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v/coach/clients');
        clients.value = data.clients || [];
    } catch (e) {
        // silent
    } finally {
        loading.value = false;
    }
}

function toggleClient(id) {
    const idx = selectedClients.value.indexOf(id);
    if (idx !== -1) {
        selectedClients.value.splice(idx, 1);
    } else {
        selectedClients.value.push(id);
    }
}

onMounted(loadClients);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <WcPageHeader :contextLabel="t('coach_growth.broadcast.page_context')" :title="t('coach_growth.broadcast.page_title')" :subtitle="t('coach_growth.broadcast.page_subtitle')" />

      <!-- Success toast -->
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="success" class="flex items-center gap-3 rounded-card border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          {{ t('coach_growth.broadcast.toast_success') }}
        </div>
      </Transition>

      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="error" class="flex items-center gap-3 rounded-card border border-wc-accent/20 bg-wc-accent/10 px-4 py-3 text-sm text-wc-accent">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
          </svg>
          {{ error }}
        </div>
      </Transition>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 anim-entry anim-entry-2">

        <!-- Compose (2 cols) -->
        <div class="space-y-5 lg:col-span-2">

          <!-- Recipient mode -->
          <div class="rounded-[14px] border border-[var(--b1)] p-5" style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
            <p class="font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary mb-3">{{ t('coach_growth.broadcast.recipients_label') }}</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
              <button
                v-for="mode in [{ key: 'all', label: t('coach_growth.broadcast.mode_all') }, { key: 'plan', label: t('coach_growth.broadcast.mode_plan') }, { key: 'status', label: t('coach_growth.broadcast.mode_status') }, { key: 'individual', label: t('coach_growth.broadcast.mode_individual') }]"
                :key="mode.key"
                @click="recipientMode = mode.key"
                class="relative overflow-hidden rounded-card p-4 text-sm font-medium transition-colors text-center"
                :class="recipientMode === mode.key ? 'wc-stat-primary text-wc-text' : 'wc-stat-muted text-wc-text-secondary hover:text-wc-text'"
              >
                {{ mode.label }}
              </button>
            </div>

            <!-- Individual selection -->
            <div v-if="recipientMode === 'individual'" class="mt-4 max-h-48 overflow-y-auto space-y-1">
              <label
                v-for="client in clients"
                :key="client.id"
                class="flex items-center gap-3 rounded-button px-3 py-2 hover:bg-wc-bg-secondary/50 cursor-pointer transition-colors"
              >
                <input
                  type="checkbox"
                  :checked="selectedClients.includes(client.id)"
                  @change="toggleClient(client.id)"
                  class="h-4 w-4 rounded border-wc-border text-wc-accent focus:ring-wc-accent"
                />
                <AvatarConic
                  :initial="(client.name || 'C').charAt(0).toUpperCase()"
                  :image-url="client.photo_url || ''"
                  tone="accent"
                  size="sm"
                />
                <span class="text-sm text-wc-text">{{ client.name }}</span>
              </label>
            </div>
          </div>

          <!-- Message compose -->
          <div class="rounded-[14px] border border-[var(--b1)] p-5" style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
            <div class="flex items-center justify-between mb-3">
              <p class="font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary">{{ t('coach_growth.broadcast.message_label') }}</p>
              <button
                @click="showTemplates = !showTemplates"
                class="inline-flex items-center gap-1.5 rounded-button border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:text-wc-text transition-colors"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                {{ t('coach_growth.broadcast.templates_btn') }}
              </button>
            </div>
            <textarea
              v-model="messageText"
              rows="5"
              :placeholder="t('coach_growth.broadcast.message_placeholder')"
              class="w-full rounded-button border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-none"
            ></textarea>
            <div class="mt-3 flex items-center justify-between">
              <p class="text-xs text-wc-text-tertiary">{{ recipientLabel }}</p>
              <button
                @click="sendBroadcast"
                :disabled="sending || !messageText.trim()"
                class="inline-flex items-center gap-2 rounded-button bg-wc-accent px-5 py-2.5 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50"
              >
                <svg v-if="sending" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                </svg>
                {{ sending ? t('coach_growth.broadcast.sending') : t('coach_growth.broadcast.send_btn') }}
              </button>
            </div>
          </div>
        </div>

        <!-- Templates panel -->
        <div class="rounded-[14px] border border-[var(--b1)] p-5" style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
          <p class="font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary mb-4">{{ t('coach_growth.broadcast.templates_label') }}</p>
          <div class="space-y-2">
            <button
              v-for="tpl in builtInTemplates"
              :key="tpl.id"
              @click="useTemplate(tpl)"
              class="w-full rounded-[12px] border border-[var(--b1)] p-3 text-left hover:border-wc-accent/40 transition-colors"
              style="background: var(--s2);"
            >
              <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-wc-text">{{ tpl.name }}</p>
                <span class="rounded-full bg-wc-bg-tertiary px-2 py-0.5 text-[10px] text-wc-text-tertiary capitalize">{{ tpl.category }}</span>
              </div>
              <p class="mt-1 text-[11px] text-wc-text-tertiary line-clamp-2">{{ tpl.message }}</p>
            </button>
          </div>
        </div>
      </div>
    </div>
  </CoachLayout>
</template>
