<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import { useCoachCommunity } from '../../../composables/useCoachCommunity';
import CoachBadge from '../../../components/community/CoachBadge.vue';
import OfficialBadge from '../../../components/community/OfficialBadge.vue';
import PinnedIndicator from '../../../components/community/PinnedIndicator.vue';
import PostCardCoachActions from '../../../components/community/PostCardCoachActions.vue';
import CommunityEmptyIllustration from '../../../components/community/CommunityEmptyIllustration.vue';
import AvatarConic from '../../../components/coach/ios/AvatarConic.vue';
import EmptyState from '../../../components/coach/ios/EmptyState.vue';

const { t } = useI18n();

const props = defineProps({
    triggerRefresh: { type: Number, default: 0 },
});
const emit = defineEmits(['open-announce']);

const { fetchFeed, loading, error } = useCoachCommunity();

const FILTERS = computed(() => [
    { key: 'all',          label: t('coach_inbox.posts_filter_all') },
    { key: 'pinned',       label: t('coach_inbox.posts_filter_pinned') },
    { key: 'reported',     label: t('coach_inbox.posts_filter_reported') },
    { key: 'achievements', label: t('coach_inbox.posts_filter_achievements') },
    { key: 'prs',          label: t('coach_inbox.posts_filter_prs') },
]);

const activeFilter = ref('all');
const posts = ref([]);
const page = ref(1);
const lastPage = ref(1);
const hasMore = ref(true);
const loadingMore = ref(false);
const newPostsBuffer = ref(0);
const sentinelRef = ref(null);
let scrollObserver = null;

async function load(reset = false) {
    if (reset) {
        page.value = 1;
        posts.value = [];
        hasMore.value = true;
    }
    const data = await fetchFeed({ filter: activeFilter.value, page: page.value, force: reset });
    if (!data) return;

    const newItems = data.data || data.posts || [];
    if (reset) {
        posts.value = newItems;
    } else {
        posts.value.push(...newItems);
    }
    if (data.last_page !== undefined) {
        lastPage.value = data.last_page;
        hasMore.value = page.value < data.last_page;
    } else if (data.pagination) {
        lastPage.value = data.pagination.last_page;
        hasMore.value = page.value < data.pagination.last_page;
    } else {
        hasMore.value = newItems.length >= 20;
    }
}

async function loadMore() {
    if (loadingMore.value || !hasMore.value) return;
    loadingMore.value = true;
    page.value++;
    await load(false);
    loadingMore.value = false;
}

function setupScrollObserver() {
    if (scrollObserver || !sentinelRef.value) return;
    scrollObserver = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && hasMore.value && !loadingMore.value) {
            loadMore();
        }
    }, { rootMargin: '200px' });
    scrollObserver.observe(sentinelRef.value);
}

function flushBuffer() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    newPostsBuffer.value = 0;
    load(true);
}

function onPostUpdated() {
    load(true);
}

function onPostDeleted(postId) {
    posts.value = posts.value.filter(p => p.id !== postId);
}

watch(activeFilter, () => {
    load(true);
    nextTick(setupScrollObserver);
});

watch(() => props.triggerRefresh, () => load(true));

function handleNewPost(e) {
    const post = e.detail;
    if (!post) return;
    if (window.scrollY < 200) {
        posts.value.unshift(post);
    } else {
        newPostsBuffer.value++;
    }
}

onMounted(async () => {
    await load(true);
    nextTick(setupScrollObserver);
    window.addEventListener('coach-community:new-post', handleNewPost);
});

onBeforeUnmount(() => {
    window.removeEventListener('coach-community:new-post', handleNewPost);
    if (scrollObserver) scrollObserver.disconnect();
});
</script>

<template>
  <div class="anim-entry anim-entry-2 space-y-4">
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
      <button
        v-for="f in FILTERS" :key="f.key"
        @click="activeFilter = f.key"
        :class="activeFilter === f.key ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:bg-wc-bg-tertiary/70'"
        class="shrink-0 rounded-full px-4 py-1.5 text-xs font-semibold transition-colors"
      >
        {{ f.label }}
      </button>
    </div>

    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      leave-active-class="transition-all duration-200"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <button
        v-if="newPostsBuffer > 0"
        @click="flushBuffer"
        class="fixed top-20 left-1/2 -translate-x-1/2 z-30 bg-wc-accent text-white rounded-full px-4 py-2 shadow-lg text-sm font-semibold cursor-pointer hover:scale-105 transition-transform"
      >
        {{ newPostsBuffer === 1 ? t('coach_inbox.posts_new_post_one') : t('coach_inbox.posts_new_post_other', { count: newPostsBuffer }) }}
      </button>
    </Transition>

    <div v-if="loading && !posts.length" class="space-y-3">
      <div v-for="i in 3" :key="i" class="rounded-[14px] border border-[var(--b1)] p-5 animate-pulse" style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
        <div class="flex gap-3">
          <div class="h-10 w-10 rounded-full bg-wc-bg-tertiary"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 w-1/3 bg-wc-bg-tertiary rounded"></div>
            <div class="h-3 w-2/3 bg-wc-bg-tertiary rounded"></div>
          </div>
        </div>
        <div class="h-32 bg-wc-bg-tertiary rounded mt-3"></div>
      </div>
    </div>

    <div v-else-if="error && !posts.length" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center">
      <p class="text-wc-text">{{ error }}</p>
      <button @click="load(true)" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white">
        ↻ {{ t('coach_inbox.posts_retry') }}
      </button>
    </div>

    <EmptyState
      v-else-if="!posts.length"
      kind="activity"
      :title="t('coach_inbox.posts_empty_title')"
      :subtitle="t('coach_inbox.posts_empty_subtitle')"
    >
      <button @click="emit('open-announce')" class="mt-4 rounded-full bg-wc-accent text-white px-5 py-2 text-sm font-semibold">
        {{ t('coach_inbox.posts_empty_cta') }}
      </button>
    </EmptyState>

    <div v-else class="space-y-3">
      <article
        v-for="post in posts" :key="post.id"
        class="rounded-[14px] border p-5 transition-all"
        :class="post.pinned ? 'border-wc-accent/40' : 'border-[var(--b1)]'"
        style="background: var(--s2); box-shadow: var(--shadow-card-ios);"
      >
        <div v-if="post.pinned" class="mb-3">
          <PinnedIndicator :pinned-until="post.pinned.pinned_until" :note="post.pinned.note" />
        </div>

        <header class="flex items-start gap-3">
          <AvatarConic
            :initial="(post.author_name || post.client_name || '?').charAt(0).toUpperCase()"
            :image-url="post.author_avatar || post.avatar_url || ''"
            tone="accent"
            size="md"
          />
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-semibold text-wc-text truncate">{{ post.author_name || post.client_name || t('coach_inbox.posts_author_fallback') }}</span>
              <CoachBadge v-if="post.author_type === 'coach'" size="xs" />
              <OfficialBadge v-if="post.is_official" />
            </div>
            <p class="text-xs text-wc-text-tertiary">{{ post.created_at_human || post.created_at }}</p>
          </div>
          <PostCardCoachActions :post="post" @updated="onPostUpdated" @deleted="onPostDeleted" />
        </header>

        <div class="mt-3 text-sm text-wc-text whitespace-pre-wrap">{{ post.content }}</div>
        <img v-if="post.image_url" :src="post.image_url" alt="" class="mt-3 rounded-xl w-full max-h-96 object-cover" />

        <footer class="mt-3 flex items-center gap-4 text-xs text-wc-text-tertiary">
          <span v-if="post.reactions_count">{{ post.reactions_count === 1 ? t('coach_inbox.posts_reactions_count_one') : t('coach_inbox.posts_reactions_count_other', { count: post.reactions_count }) }}</span>
          <span v-if="post.comments_count">{{ post.comments_count === 1 ? t('coach_inbox.posts_comments_count_one') : t('coach_inbox.posts_comments_count_other', { count: post.comments_count }) }}</span>
          <span v-if="post.report_count" class="text-rose-500 font-semibold">{{ post.report_count === 1 ? t('coach_inbox.posts_report_count_one') : t('coach_inbox.posts_report_count_other', { count: post.report_count }) }}</span>
        </footer>
      </article>

      <div ref="sentinelRef" class="h-4"></div>
      <div v-if="loadingMore" class="text-center text-xs text-wc-text-tertiary py-3">{{ t('coach_inbox.posts_loading_more') }}</div>
    </div>
  </div>
</template>
