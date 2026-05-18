<script setup>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useCoachCommunity } from '../../../composables/useCoachCommunity';
import EmptyState from '../../../components/coach/ios/EmptyState.vue';

const { t } = useI18n();

const { fetchThreads, loading, error } = useCoachCommunity();
const threads = ref([]);
const FILTERS = computed(() => [
    { key: 'all',         label: t('coach_inbox.threads_filter_all') },
    { key: 'unanswered',  label: t('coach_inbox.threads_filter_unanswered') },
    { key: 'large',       label: t('coach_inbox.threads_filter_large') },
    { key: 'conflicted',  label: t('coach_inbox.threads_filter_conflicted') },
]);
const activeFilter = ref('all');

const filtered = computed(() => {
    if (activeFilter.value === 'all') return threads.value;
    if (activeFilter.value === 'unanswered') return threads.value.filter(item => !item.has_coach_reply);
    if (activeFilter.value === 'large') return threads.value.filter(item => item.thread_size >= 50);
    if (activeFilter.value === 'conflicted') return threads.value.filter(item => item.is_conflicted);
    return threads.value;
});

async function load() {
    const data = await fetchThreads({ sinceDays: 7, page: 1, perPage: 30 });
    if (data) threads.value = data.data || [];
}

function timeAgo(iso) {
    if (!iso) return '';
    const diffMs = Date.now() - new Date(iso).getTime();
    const minutes = Math.floor(diffMs / 60000);
    if (minutes < 60) return t('coach_inbox.threads_time_ago_minutes', { value: minutes });
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return t('coach_inbox.threads_time_ago_hours', { value: hours });
    return t('coach_inbox.threads_time_ago_days', { value: Math.floor(hours / 24) });
}

onMounted(() => load());
</script>

<template>
  <div class="anim-entry anim-entry-2 space-y-4">
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
      <button
        v-for="f in FILTERS" :key="f.key"
        @click="activeFilter = f.key"
        :class="activeFilter === f.key ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary'"
        class="shrink-0 rounded-full px-4 py-1.5 text-xs font-semibold transition-colors"
      >{{ f.label }}</button>
    </div>

    <div v-if="loading && !threads.length" class="space-y-3">
      <div v-for="i in 4" :key="i" class="h-20 rounded-xl border border-wc-border bg-wc-bg-secondary animate-pulse"></div>
    </div>
    <div v-else-if="error" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center text-sm text-wc-text">
      {{ error }}
    </div>
    <EmptyState
      v-else-if="!filtered.length"
      kind="activity"
      :title="t('coach_inbox.threads_empty_title')"
      :subtitle="t('coach_inbox.threads_empty_subtitle')"
    />
    <div v-else class="space-y-2">
      <article
        v-for="thread in filtered" :key="thread.post_id"
        class="rounded-[14px] border border-[var(--b1)] p-4 hover:border-wc-accent/30 transition-colors cursor-pointer"
        style="background: var(--s2); box-shadow: var(--shadow-card-ios);"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-wc-text truncate">{{ thread.post_author_name }}</p>
            <p class="text-sm text-wc-text-secondary truncate">"{{ thread.post_excerpt }}"</p>
            <div class="mt-2 flex items-center gap-3 text-xs text-wc-text-tertiary">
              <span>{{ thread.thread_size === 1 ? t('coach_inbox.threads_comments_count_one') : t('coach_inbox.threads_comments_count_other', { count: thread.thread_size }) }}</span>
              <span>·</span>
              <span>{{ thread.participants_count === 1 ? t('coach_inbox.threads_participants_count_one') : t('coach_inbox.threads_participants_count_other', { count: thread.participants_count }) }}</span>
              <span>·</span>
              <span>{{ timeAgo(thread.last_activity_at) }}</span>
            </div>
          </div>
          <div class="shrink-0 flex flex-col items-end gap-1">
            <span v-if="thread.has_coach_reply" class="rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 text-[10px] font-semibold">
              {{ t('coach_inbox.threads_status_replied') }}
            </span>
            <span v-else class="rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 px-2 py-0.5 text-[10px] font-semibold">
              {{ t('coach_inbox.threads_status_unanswered') }}
            </span>
            <span v-if="thread.is_conflicted" class="rounded-full bg-rose-500/10 text-rose-600 dark:text-rose-400 px-2 py-0.5 text-[10px] font-semibold">
              {{ t('coach_inbox.threads_status_conflicted') }}
            </span>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>
