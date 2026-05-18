<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';
import WcPageHeader from '../../components/WcPageHeader.vue';
import AvatarConic from '../../components/coach/ios/AvatarConic.vue';
import EmptyState from '../../components/coach/ios/EmptyState.vue';

const api = useApi();
const { t } = useI18n();
const loading = ref(true);
const error = ref('');
const checkins = ref([]);
const showReplied = ref(false);
const replyingTo = ref(null);
const replyText = ref('');
const replyError = ref('');
const submitting = ref(false);

const pendingCount = computed(() => checkins.value.filter(c => !c.coach_reply).length);

const checkinSubtitle = computed(() => {
    if (pendingCount.value === 0) return t('coach_inbox.checkins_subtitle_all_caught_up');
    if (pendingCount.value === 1) return t('coach_inbox.checkins_subtitle_pending_one');
    return t('coach_inbox.checkins_subtitle_pending_other', { count: pendingCount.value });
});

const filteredCheckins = computed(() => {
    if (showReplied.value) return checkins.value;
    return checkins.value.filter(c => !c.coach_reply);
});

function startReply(id) {
    replyingTo.value = id;
    replyText.value = '';
    replyError.value = '';
}

function cancelReply() {
    replyingTo.value = null;
    replyText.value = '';
}

async function submitReply(checkinId) {
    if (!replyText.value.trim()) return;
    submitting.value = true;
    replyError.value = '';
    try {
        await api.post(`/api/v/coach/checkins/${checkinId}/reply`, { reply: replyText.value });
        const checkin = checkins.value.find(c => c.id === checkinId);
        if (checkin) {
            checkin.coach_reply = replyText.value;
            checkin.replied_at = t('coach_inbox.checkins_replied_at_now');
        }
        replyingTo.value = null;
        replyText.value = '';
    } catch (e) {
        replyError.value = t('coach_inbox.checkins_reply_error');
    } finally {
        submitting.value = false;
    }
}

async function loadCheckins() {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.get('/api/v/coach/checkins');
        checkins.value = data.checkins || [];
    } catch (e) {
        error.value = t('coach_inbox.checkins_load_error');
    } finally {
        loading.value = false;
    }
}

onMounted(loadCheckins);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <WcPageHeader :contextLabel="t('coach_inbox.context_main')" :title="t('coach_inbox.checkins_title')" :subtitle="checkinSubtitle">
        <template #actions>
          <button
            @click="showReplied = !showReplied"
            class="inline-flex items-center gap-2 rounded-button border px-4 py-2 text-sm font-medium transition-colors"
            :class="showReplied ? 'bg-wc-accent border-wc-accent text-white' : 'border-wc-border text-wc-text hover:bg-wc-bg-tertiary'"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            {{ showReplied ? t('coach_inbox.checkins_filter_show_all') : t('coach_inbox.checkins_filter_pending_only') }}
          </button>
        </template>
      </WcPageHeader>

      <!-- Load error -->
      <div v-if="error" class="rounded-lg bg-red-900/20 border border-red-500/30 px-4 py-3 text-sm text-red-400">
        {{ error }}
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <svg class="h-8 w-8 animate-spin text-wc-accent" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      </div>

      <!-- Check-in cards (iOS style) -->
      <div v-else-if="filteredCheckins.length > 0" class="space-y-4 anim-entry anim-entry-2">
        <div
          v-for="checkin in filteredCheckins"
          :key="checkin.id"
          class="rounded-[14px] border border-[var(--b1)] overflow-hidden"
          :class="{ 'border-l-[3px] border-l-wc-accent': !checkin.coach_reply }"
          style="background: var(--s2); box-shadow: var(--shadow-card-ios);"
        >
          <!-- Header -->
          <div class="flex items-center gap-3 p-4 sm:p-5">
            <AvatarConic
              :initial="(checkin.client_name || 'C').charAt(0).toUpperCase()"
              :tone="!checkin.coach_reply ? 'accent' : 'gold'"
              size="md"
            />
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <p class="text-sm font-medium text-wc-text">{{ checkin.client_name }}</p>
                <span class="inline-flex shrink-0 rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold text-wc-accent">{{ checkin.client_plan }}</span>
                <span v-if="!checkin.coach_reply" class="inline-flex shrink-0 rounded-full bg-wc-accent/15 px-2 py-0.5 text-[10px] font-semibold text-wc-accent">{{ t('coach_inbox.checkins_badge_pending') }}</span>
                <span v-else class="inline-flex shrink-0 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-500">{{ t('coach_inbox.checkins_badge_replied') }}</span>
              </div>
              <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ checkin.checkin_date }} -- {{ checkin.checkin_date_ago }}</p>
            </div>
          </div>

          <!-- Metrics -->
          <div class="border-t border-wc-border bg-wc-bg-secondary/30 px-4 py-4 sm:px-5">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
              <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ t('coach_inbox.checkins_metric_wellbeing') }}</p>
                <p class="mt-1 font-data text-xl font-bold text-wc-text">{{ checkin.bienestar ?? '-' }}</p>
                <div class="mt-1 h-1 w-full rounded-full bg-wc-bg-secondary">
                  <div class="h-1 rounded-full" :class="(checkin.bienestar || 0) >= 7 ? 'bg-emerald-400' : (checkin.bienestar || 0) >= 4 ? 'bg-amber-400' : 'bg-wc-accent'" :style="{ width: Math.min((checkin.bienestar || 0) * 10, 100) + '%' }"></div>
                </div>
              </div>
              <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ t('coach_inbox.checkins_metric_days_trained') }}</p>
                <p class="mt-1 font-data text-xl font-bold text-wc-text">{{ checkin.dias_entrenados ?? '-' }}</p>
                <p class="mt-1 text-[10px] text-wc-text-tertiary">{{ t('coach_inbox.checkins_metric_days_trained_of') }}</p>
              </div>
              <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ t('coach_inbox.checkins_metric_nutrition') }}</p>
                <p class="mt-1 font-data text-xl font-bold text-wc-text">{{ checkin.nutricion ?? '-' }}</p>
                <div class="mt-1 h-1 w-full rounded-full bg-wc-bg-secondary">
                  <div class="h-1 rounded-full" :class="(checkin.nutricion || 0) >= 7 ? 'bg-emerald-400' : (checkin.nutricion || 0) >= 4 ? 'bg-amber-400' : 'bg-wc-accent'" :style="{ width: Math.min((checkin.nutricion || 0) * 10, 100) + '%' }"></div>
                </div>
              </div>
              <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ t('coach_inbox.checkins_metric_rpe') }}</p>
                <p class="mt-1 font-data text-xl font-bold text-wc-text">{{ checkin.rpe ?? '-' }}</p>
                <p class="mt-1 text-[10px] text-wc-text-tertiary">{{ t('coach_inbox.checkins_metric_rpe_label') }}</p>
              </div>
            </div>

            <!-- Comment -->
            <div v-if="checkin.comentario" class="mt-4 rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
              <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ t('coach_inbox.checkins_client_comment_label') }}</p>
              <p class="mt-2 text-sm text-wc-text-secondary leading-relaxed">{{ checkin.comentario }}</p>
            </div>

            <!-- Coach reply (existing) -->
            <div v-if="checkin.coach_reply" class="mt-4 rounded-card border border-emerald-500/20 bg-emerald-500/5 p-3">
              <div class="flex items-center gap-2">
                <svg class="h-3.5 w-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <p class="font-sans text-[10px] font-bold uppercase tracking-widest text-wc-text-secondary">{{ t('coach_inbox.checkins_your_reply_label') }}</p>
                <span v-if="checkin.replied_at" class="text-[10px] text-wc-text-tertiary">{{ t('coach_inbox.checkins_replied_at_prefix', { when: checkin.replied_at }) }}</span>
              </div>
              <p class="mt-2 text-sm text-wc-text-secondary leading-relaxed">{{ checkin.coach_reply }}</p>
            </div>

            <!-- Reply form -->
            <div v-if="!checkin.coach_reply">
              <div v-if="replyingTo === checkin.id" class="mt-4 space-y-3">
                <label class="text-xs font-medium text-wc-text-secondary">{{ t('coach_inbox.checkins_reply_form_label') }}</label>
                <textarea
                  v-model="replyText"
                  rows="3"
                  :placeholder="t('coach_inbox.checkins_reply_placeholder')"
                  class="w-full rounded-button border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-none"
                ></textarea>
                <div class="flex items-center gap-2">
                  <button
                    @click="submitReply(checkin.id)"
                    :disabled="submitting || !replyText.trim()"
                    class="inline-flex items-center gap-1.5 rounded-button bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                    {{ t('coach_inbox.checkins_reply_send') }}
                  </button>
                  <button @click="cancelReply" class="inline-flex items-center rounded-button border border-wc-border px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                    {{ t('coach_inbox.checkins_reply_cancel') }}
                  </button>
                </div>
                <p v-if="replyError" class="mt-2 text-xs text-red-400">{{ replyError }}</p>
              </div>
              <div v-else class="mt-4">
                <button
                  @click="startReply(checkin.id)"
                  class="inline-flex items-center gap-1.5 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                  </svg>
                  {{ t('coach_inbox.checkins_reply_cta') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state iOS -->
      <EmptyState
        v-else
        :kind="showReplied ? 'activity' : 'success'"
        :title="showReplied ? t('coach_inbox.checkins_empty_all_title') : t('coach_inbox.checkins_empty_pending_title')"
        :subtitle="showReplied ? t('coach_inbox.checkins_empty_all_subtitle') : t('coach_inbox.checkins_empty_pending_subtitle')"
      />
    </div>
  </CoachLayout>
</template>
