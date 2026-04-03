<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useApi } from '../../composables/useApi';
import { useAuthStore } from '../../stores/auth';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();
const authStore = useAuthStore();

// ── State ────────────────────────────────────────────────────────────
const loading = ref(true);
const error = ref(null);
const posts = ref([]);
const communityStats = ref({ total_posts: 0, active_members: 0 });
const page = ref(1);
const lastPage = ref(1);
const hasMore = ref(true);
const loadingMore = ref(false);

// ── New post ─────────────────────────────────────────────────────────
const postType = ref('text');
const postContent = ref('');
const postErrors = ref({});
const submittingPost = ref(false);

// ── Comments ─────────────────────────────────────────────────────────
const expandedComments = ref({});
const commentTexts = ref({});
const submittingComment = ref({});

// ── Delete ───────────────────────────────────────────────────────────
const confirmDeleteId = ref(null);
const deletingPost = ref(null);

// ── Constants (module-level, non-reactive) ───────────────────────────
const REACTION_TYPES = [
  { type: 'like',   emoji: '\uD83D\uDC4D', label: 'Genial' },
  { type: 'fire',   emoji: '\uD83D\uDD25', label: 'Fuego' },
  { type: 'muscle', emoji: '\uD83D\uDCAA', label: 'Fuerza' },
  { type: 'clap',   emoji: '\uD83D\uDC4F', label: 'Bravo' },
];

const POST_TYPE_COLORS = {
  achievement: { border: 'border-yellow-500/30', text: 'text-yellow-500', bg: 'bg-yellow-500/10', gradient: 'from-yellow-500 to-yellow-500/20', emoji: '\uD83C\uDFC6', label: 'Logro' },
  pr:          { border: 'border-green-500/30',  text: 'text-green-500',  bg: 'bg-green-500/10',  gradient: 'from-green-500 to-green-500/20',  emoji: '\uD83D\uDCAA', label: 'PR' },
  photo:       { border: 'border-blue-500/30',   text: 'text-blue-400',   bg: 'bg-blue-500/10',   gradient: 'from-blue-500 to-blue-500/20',   emoji: '\uD83D\uDCF8', label: 'Foto' },
  text:        { border: '',                      text: '',                bg: '',                  gradient: '',                                emoji: '',              label: '' },
};

const POST_TYPE_TABS = [
  { key: 'text',        label: 'Post',     borderColor: 'border-wc-accent',   textColor: 'text-wc-text', bgColor: 'bg-wc-accent/5',    icon: 'chat' },
  { key: 'achievement', label: 'Logro',    borderColor: 'border-yellow-500',  textColor: 'text-yellow-500', bgColor: 'bg-yellow-500/5', icon: 'trophy' },
  { key: 'pr',          label: 'Nuevo PR', borderColor: 'border-green-500',   textColor: 'text-green-500',  bgColor: 'bg-green-500/5',  icon: 'muscle' },
];

// ── Infinite scroll handle (module-level) ────────────────────────────
let scrollObserver = null;
const sentinelRef = ref(null);

// ── Computed ─────────────────────────────────────────────────────────
const charCount = computed(() => postContent.value.length);
const currentClientId = computed(() => Number(authStore.userId));

// ── Fetch community feed ─────────────────────────────────────────────
async function fetchFeed(reset = false) {
  if (reset) {
    page.value = 1;
    hasMore.value = true;
    posts.value = [];
  }

  if (reset) loading.value = true;
  error.value = null;

  try {
    const response = await api.get('/api/v/client/community', {
      params: { page: page.value, per_page: 10 },
    });
    const d = response.data;

    if (d.community_stats) communityStats.value = d.community_stats;
    if (d.stats) communityStats.value = d.stats;

    const newPosts = d.posts || [];
    if (reset) {
      posts.value = newPosts;
    } else {
      posts.value.push(...newPosts);
    }

    if (d.pagination) {
      lastPage.value = d.pagination.last_page;
      hasMore.value = d.pagination.current_page < d.pagination.last_page;
    } else {
      hasMore.value = newPosts.length >= 10;
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar la comunidad';
  } finally {
    loading.value = false;
    loadingMore.value = false;
  }
}

// ── Load more ────────────────────────────────────────────────────────
async function loadMore() {
  if (loadingMore.value || !hasMore.value) return;
  loadingMore.value = true;
  page.value++;
  await fetchFeed(false);
}

// ── Create post ──────────────────────────────────────────────────────
async function createPost() {
  if (!postContent.value.trim()) return;
  submittingPost.value = true;
  postErrors.value = {};

  try {
    const response = await api.post('/api/v/client/community', {
      post_type: postType.value,
      content: postContent.value.trim(),
    });

    // Prepend new post locally
    const newPost = {
      id: response.data.id,
      client_id: currentClientId.value,
      client_name: localStorage.getItem('wc_user_name') || 'Yo',
      content: postContent.value.trim(),
      post_type: postType.value,
      created_at: response.data.created_at || new Date().toISOString(),
      reactions_count: 0,
      comments_count: 0,
      my_reactions: [],
      reaction_counts: {},
      comments: [],
    };

    // Apply prefix matching backend logic
    if (postType.value === 'achievement' && !newPost.content.startsWith('Logro: ')) {
      newPost.content = 'Logro: ' + newPost.content;
    } else if (postType.value === 'pr' && !newPost.content.startsWith('Nuevo PR: ')) {
      newPost.content = 'Nuevo PR: ' + newPost.content;
    }

    posts.value.unshift(newPost);
    communityStats.value.total_posts++;

    postContent.value = '';
    postType.value = 'text';
  } catch (err) {
    if (err.response?.status === 422) {
      postErrors.value = err.response.data.errors || {};
    } else {
      postErrors.value = { content: [err.response?.data?.message || 'Error al publicar'] };
    }
  } finally {
    submittingPost.value = false;
  }
}

// ── Toggle reaction ──────────────────────────────────────────────────
async function toggleReaction(postId, reactionType) {
  const post = posts.value.find(p => p.id === postId);
  if (!post) return;

  // Optimistic update
  const myReactions = post.my_reactions || [];
  const wasActive = myReactions.includes(reactionType);
  const counts = post.reaction_counts || {};

  if (wasActive) {
    post.my_reactions = myReactions.filter(r => r !== reactionType);
    counts[reactionType] = Math.max(0, (counts[reactionType] || 1) - 1);
  } else {
    post.my_reactions = [...myReactions, reactionType];
    counts[reactionType] = (counts[reactionType] || 0) + 1;
  }
  post.reaction_counts = { ...counts };

  try {
    await api.post(`/api/v/client/community/${postId}/react`, {
      reaction_type: reactionType,
    });
  } catch {
    // Revert optimistic update
    if (wasActive) {
      post.my_reactions = [...post.my_reactions, reactionType];
      counts[reactionType] = (counts[reactionType] || 0) + 1;
    } else {
      post.my_reactions = post.my_reactions.filter(r => r !== reactionType);
      counts[reactionType] = Math.max(0, (counts[reactionType] || 1) - 1);
    }
    post.reaction_counts = { ...counts };
  }
}

// ── Toggle comments ──────────────────────────────────────────────────
function toggleComments(postId) {
  expandedComments.value[postId] = !expandedComments.value[postId];
}

// ── Add comment ──────────────────────────────────────────────────────
async function addComment(postId) {
  const text = (commentTexts.value[postId] || '').trim();
  if (!text || text.length > 500) return;

  submittingComment.value[postId] = true;
  try {
    const response = await api.post(`/api/v/client/community/${postId}/comment`, {
      content: text,
    });
    commentTexts.value[postId] = '';

    const post = posts.value.find(p => p.id === postId);
    if (post) {
      if (!post.comments) post.comments = [];
      post.comments.push({
        id: response.data.id,
        client_name: response.data.client_name || localStorage.getItem('wc_user_name') || 'Yo',
        client_id: currentClientId.value,
        content: text,
        created_at: response.data.created_at || new Date().toISOString(),
      });
      post.comments_count = (post.comments_count || 0) + 1;
    }
  } catch {
    // Fail silently
  } finally {
    submittingComment.value[postId] = false;
  }
}

// ── Delete post ──────────────────────────────────────────────────────
async function deletePost(postId) {
  if (confirmDeleteId.value !== postId) {
    confirmDeleteId.value = postId;
    return;
  }

  deletingPost.value = postId;
  try {
    await api.delete(`/api/v/client/community/${postId}`);
    posts.value = posts.value.filter(p => p.id !== postId);
    communityStats.value.total_posts = Math.max(0, communityStats.value.total_posts - 1);
  } catch {
    // Fail silently
  } finally {
    deletingPost.value = null;
    confirmDeleteId.value = null;
  }
}

function cancelDelete() {
  confirmDeleteId.value = null;
}

// ── Infinite scroll ──────────────────────────────────────────────────
function setupInfiniteScroll() {
  if (!sentinelRef.value) return;
  scrollObserver = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting && hasMore.value && !loadingMore.value) {
        loadMore();
      }
    },
    { rootMargin: '200px' }
  );
  scrollObserver.observe(sentinelRef.value);
}

onMounted(() => {
  fetchFeed(true).then(() => {
    setTimeout(() => setupInfiniteScroll(), 100);
  });
});

onBeforeUnmount(() => {
  if (scrollObserver) scrollObserver.disconnect();
});

// ── Helpers ──────────────────────────────────────────────────────────
function timeAgo(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const now = new Date();
  const seconds = Math.floor((now - date) / 1000);
  if (seconds < 60) return 'hace un momento';
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `hace ${minutes}m`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `hace ${hours}h`;
  const days = Math.floor(hours / 24);
  if (days < 7) return `hace ${days}d`;
  return date.toLocaleDateString('es-CO', { day: 'numeric', month: 'short' });
}

function getInitials(name) {
  return (name || 'MI').substring(0, 2).toUpperCase();
}

function getPlaceholder() {
  if (postType.value === 'achievement') return '\u00BFQu\u00E9 lograste hoy? Cu\u00E9ntalo...';
  if (postType.value === 'pr') return 'Describe tu nuevo r\u00E9cord personal...';
  return 'Comparte algo con la comunidad...';
}

function isOwnPost(post) {
  return Number(post.client_id) === currentClientId.value;
}

function getPostTypeInfo(type) {
  return POST_TYPE_COLORS[type] || POST_TYPE_COLORS.text;
}

function getVisibleComments(post) {
  if (!post.comments || post.comments.length === 0) return [];
  return [...post.comments].sort((a, b) => new Date(b.created_at) - new Date(a.created_at)).slice(0, 5);
}

function getHiddenCommentCount(post) {
  return Math.max(0, (post.comments_count || post.comments?.length || 0) - 5);
}

function isReactionActive(post, type) {
  return (post.my_reactions || []).includes(type);
}

function getReactionCount(post, type) {
  return post.reaction_counts?.[type] || 0;
}
</script>

<template>
  <ClientLayout>
    <!-- Loading Skeleton -->
    <div v-if="loading" class="space-y-6">
      <div class="h-32 animate-pulse rounded-2xl border border-wc-border bg-wc-bg-tertiary"></div>
      <div class="h-40 animate-pulse rounded-2xl border border-wc-border bg-wc-bg-tertiary"></div>
      <div v-for="n in 3" :key="n" class="h-48 animate-pulse rounded-2xl border border-wc-border bg-wc-bg-tertiary"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex flex-col items-center justify-center py-20">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
        <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
      </div>
      <h2 class="mt-4 font-display text-xl tracking-wide text-wc-text">ERROR AL CARGAR</h2>
      <p class="mt-2 text-sm text-wc-text-secondary">{{ error }}</p>
      <button
        @click="fetchFeed(true)"
        class="mt-6 rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent/90 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg"
      >
        Reintentar
      </button>
    </div>

    <!-- Content -->
    <div v-else class="space-y-6">

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- HEADER                                                         -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-gradient-to-br from-wc-bg-secondary via-wc-bg-tertiary to-wc-bg-secondary p-6">
        <div class="relative z-10 flex items-end justify-between">
          <div>
            <p class="mb-1 text-xs font-semibold uppercase tracking-widest text-wc-accent">Comunidad WellCore</p>
            <h1 class="font-display text-4xl tracking-wide text-wc-text">FEED</h1>
            <p class="mt-1 text-sm text-wc-text-secondary">Comparte tus logros &middot; Celebra los de otros</p>
          </div>
          <div class="hidden items-center gap-4 sm:flex">
            <div class="text-center">
              <p class="font-display text-2xl text-wc-accent">{{ communityStats.total_posts }}</p>
              <p class="text-xs uppercase tracking-wider text-wc-text-tertiary">Posts</p>
            </div>
            <div class="h-8 w-px bg-wc-border"></div>
            <div class="text-center">
              <p class="font-display text-2xl text-wc-accent">{{ communityStats.active_members }}</p>
              <p class="text-xs uppercase tracking-wider text-wc-text-tertiary">Miembros</p>
            </div>
          </div>
        </div>
        <!-- Decorative circles -->
        <div class="absolute bottom-0 right-0 h-32 w-32 opacity-5">
          <svg viewBox="0 0 100 100" fill="none" class="h-full w-full text-wc-accent">
            <circle cx="50" cy="50" r="45" stroke="currentColor" stroke-width="1"/>
            <circle cx="50" cy="50" r="30" stroke="currentColor" stroke-width="1"/>
            <circle cx="50" cy="50" r="15" stroke="currentColor" stroke-width="1"/>
          </svg>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- CREATE POST                                                     -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div class="overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary">
        <!-- Post type tabs -->
        <div class="flex border-b border-wc-border">
          <button
            v-for="tab in POST_TYPE_TABS"
            :key="tab.key"
            type="button"
            @click="postType = tab.key"
            class="flex flex-1 items-center justify-center gap-2 py-3 text-sm font-semibold uppercase tracking-wider transition-colors"
            :class="postType === tab.key
              ? `border-b-2 ${tab.borderColor} ${tab.textColor} ${tab.bgColor}`
              : 'text-wc-text-tertiary hover:text-wc-text-secondary'"
          >
            <!-- Tab icons matching Blade -->
            <svg v-if="tab.key === 'text'" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
            </svg>
            <span v-else-if="tab.key === 'achievement'">&#127942;</span>
            <span v-else-if="tab.key === 'pr'">&#128170;</span>
            {{ tab.label }}
          </button>
        </div>

        <!-- Input area -->
        <form @submit.prevent="createPost" class="p-4">
          <div class="relative">
            <textarea
              v-model="postContent"
              rows="3"
              maxlength="1000"
              :placeholder="getPlaceholder()"
              class="w-full resize-none rounded-xl border border-wc-border bg-wc-bg px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary transition-all focus:border-wc-accent/50 focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
            ></textarea>
            <span class="absolute bottom-3 right-3 text-[10px] tabular-nums text-wc-text-tertiary">
              {{ charCount }}/1000
            </span>
          </div>

          <!-- Validation errors -->
          <p v-if="postErrors.content" class="mt-1 text-sm text-red-400">{{ postErrors.content[0] || postErrors.content }}</p>
          <p v-if="postErrors.post_type" class="mt-1 text-sm text-red-400">{{ postErrors.post_type[0] }}</p>

          <div class="mt-3 flex justify-end">
            <button
              type="submit"
              :disabled="submittingPost || !postContent.trim()"
              class="btn-press flex items-center gap-2 rounded-xl bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-wc-accent/20 transition-all hover:bg-wc-accent/90 disabled:opacity-50"
            >
              <!-- Send icon -->
              <svg v-if="!submittingPost" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
              </svg>
              <!-- Spinner -->
              <svg v-else class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              Publicar
            </button>
          </div>
        </form>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- FEED                                                            -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div class="space-y-3">
        <TransitionGroup name="feed-item" tag="div" class="space-y-3">
          <div
            v-for="post in posts"
            :key="post.id"
            class="group overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary transition-all hover:border-wc-border/80 hover:shadow-lg hover:shadow-black/5"
          >
            <!-- Accent strip for special types -->
            <div
              v-if="post.post_type && post.post_type !== 'text'"
              class="h-0.5 bg-gradient-to-r"
              :class="getPostTypeInfo(post.post_type).gradient"
            ></div>

            <div class="p-4">
              <!-- Header row -->
              <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                  <!-- Avatar with type badge -->
                  <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-wc-accent/30 to-wc-accent/10 text-sm font-bold text-wc-accent">
                    {{ getInitials(post.client_name) }}
                    <span
                      v-if="post.post_type && post.post_type !== 'text'"
                      class="absolute -bottom-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-wc-bg-tertiary text-[10px]"
                    >{{ getPostTypeInfo(post.post_type).emoji }}</span>
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <p class="text-sm font-semibold leading-tight text-wc-text">{{ post.client_name || 'Miembro' }}</p>
                      <!-- Post type badge -->
                      <span
                        v-if="post.post_type && post.post_type !== 'text'"
                        class="rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider"
                        :class="`${getPostTypeInfo(post.post_type).bg} ${getPostTypeInfo(post.post_type).text}`"
                      >{{ getPostTypeInfo(post.post_type).emoji }} {{ getPostTypeInfo(post.post_type).label }}</span>
                    </div>
                    <p class="text-xs text-wc-text-tertiary">{{ timeAgo(post.created_at) }}</p>
                  </div>
                </div>

                <!-- Delete (own posts only, visible on group hover) -->
                <button
                  v-if="isOwnPost(post)"
                  @click="deletePost(post.id)"
                  :disabled="deletingPost === post.id"
                  :title="confirmDeleteId === post.id ? 'Confirmar eliminar' : 'Eliminar publicacion'"
                  class="shrink-0 rounded-lg p-1.5 text-wc-text-tertiary opacity-0 transition-all group-hover:opacity-100 hover:bg-red-500/10 hover:text-red-400 disabled:opacity-50"
                  :class="confirmDeleteId === post.id ? 'opacity-100 bg-red-500/10 text-red-400' : ''"
                >
                  <svg v-if="deletingPost !== post.id" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                  </svg>
                  <svg v-else class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                  </svg>
                </button>
              </div>

              <!-- Delete confirmation banner -->
              <Transition name="fade">
                <div
                  v-if="confirmDeleteId === post.id && deletingPost !== post.id"
                  class="mt-2 flex items-center justify-between rounded-lg bg-red-500/10 border border-red-500/20 px-3 py-2"
                >
                  <p class="text-sm text-red-400">Haz clic de nuevo en el icono para confirmar</p>
                  <button @click.stop="cancelDelete" class="text-[10px] font-semibold uppercase text-wc-text-tertiary hover:text-wc-text-secondary transition-colors">
                    Cancelar
                  </button>
                </div>
              </Transition>

              <!-- Content -->
              <div class="mt-3 whitespace-pre-line text-sm leading-relaxed text-wc-text">{{ post.content }}</div>

              <!-- Reaction bar -->
              <div class="mt-4 flex flex-wrap items-center gap-1.5">
                <button
                  v-for="reaction in REACTION_TYPES"
                  :key="reaction.type"
                  @click="toggleReaction(post.id, reaction.type)"
                  :title="reaction.label"
                  class="btn-press flex items-center gap-1.5 rounded-full border px-3 py-2 text-xs transition-all duration-200"
                  :class="isReactionActive(post, reaction.type)
                    ? 'bg-wc-accent border-wc-accent text-white shadow-sm scale-110'
                    : 'bg-wc-bg-tertiary border-wc-border text-wc-text-secondary hover:border-wc-accent/40 hover:scale-105'"
                >
                  <span aria-hidden="true">{{ reaction.emoji }}</span>
                  <span class="hidden font-sans text-[10px] font-medium sm:inline">{{ reaction.label }}</span>
                  <span v-if="getReactionCount(post, reaction.type) > 0" class="font-data tabular-nums font-bold">{{ getReactionCount(post, reaction.type) }}</span>
                </button>

                <!-- Comment toggle -->
                <button
                  @click="toggleComments(post.id)"
                  class="ml-auto flex items-center gap-1.5 rounded-full border border-transparent px-3 py-1 text-xs text-wc-text-tertiary transition-all hover:border-wc-border hover:text-wc-text-secondary"
                >
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                  </svg>
                  <span class="font-data font-semibold">{{ post.comments_count || 0 }}</span>
                </button>
              </div>

              <!-- Comments section -->
              <Transition name="slide-down">
                <div v-if="expandedComments[post.id]" class="mt-3 space-y-2 rounded-xl border border-wc-border/50 bg-wc-bg/60 p-3">
                  <!-- Comments list (max 5 most recent) -->
                  <template v-if="post.comments && post.comments.length > 0">
                    <div
                      v-for="comment in getVisibleComments(post)"
                      :key="comment.id"
                      class="flex gap-2.5"
                    >
                      <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-wc-accent/15 text-[10px] font-bold text-wc-accent">
                        {{ (comment.client_name || 'M').charAt(0).toUpperCase() }}
                      </div>
                      <div class="min-w-0 flex-1">
                        <span class="text-sm font-semibold text-wc-text">{{ comment.client_name || 'Miembro' }}</span>
                        <span class="ml-1.5 text-xs text-wc-text-tertiary">{{ timeAgo(comment.created_at) }}</span>
                        <p class="mt-0.5 text-sm leading-relaxed text-wc-text-secondary">{{ comment.content }}</p>
                      </div>
                    </div>

                    <!-- "X more" indicator -->
                    <p v-if="getHiddenCommentCount(post) > 0" class="pl-8 text-xs text-wc-text-tertiary">
                      + {{ getHiddenCommentCount(post) }} comentarios mas
                    </p>
                  </template>

                  <p v-else class="py-2 text-center text-sm text-wc-text-tertiary">Sin comentarios aun</p>

                  <!-- Add comment input -->
                  <div class="mt-2 flex gap-2 border-t border-wc-border/40 pt-2">
                    <input
                      type="text"
                      v-model="commentTexts[post.id]"
                      @keydown.enter="addComment(post.id)"
                      placeholder="Comentar..."
                      maxlength="500"
                      class="flex-1 rounded-xl border border-wc-border bg-wc-bg px-3 py-1.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent/50 focus:outline-none focus:ring-1 focus:ring-wc-accent/20"
                    />
                    <button
                      @click="addComment(post.id)"
                      :disabled="submittingComment[post.id] || !(commentTexts[post.id] || '').trim()"
                      class="btn-press shrink-0 rounded-xl border border-wc-accent/20 bg-wc-accent/10 px-3 py-1.5 text-wc-accent transition-colors hover:bg-wc-accent/20 disabled:opacity-50"
                    >
                      <svg v-if="!submittingComment[post.id]" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                      </svg>
                      <svg v-else class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                      </svg>
                    </button>
                  </div>
                </div>
              </Transition>
            </div>
          </div>
        </TransitionGroup>

        <!-- Empty state -->
        <div v-if="!loading && posts.length === 0" class="rounded-2xl border border-dashed border-wc-border bg-wc-bg-tertiary/50 p-16 text-center">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
            <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </svg>
          </div>
          <h3 class="mt-4 font-display text-xl text-wc-text">SIN PUBLICACIONES AUN</h3>
          <p class="mt-2 text-sm text-wc-text-secondary">Se el primero en compartir algo con la comunidad</p>
        </div>

        <!-- Load more button (explicit, matching Blade) -->
        <div v-if="hasMore && posts.length > 0 && !loadingMore" class="flex justify-center">
          <button
            @click="loadMore"
            class="btn-press flex items-center gap-2 rounded-xl border border-wc-border px-6 py-2.5 text-sm font-medium text-wc-text-secondary transition-all hover:border-wc-accent/40 hover:text-wc-accent"
          >
            Cargar mas
          </button>
        </div>

        <!-- Loading more / Sentinel for infinite scroll -->
        <div ref="sentinelRef" class="py-4 text-center">
          <div v-if="loadingMore" class="flex items-center justify-center gap-2">
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-sm text-wc-text-tertiary">Cargando mas...</span>
          </div>
          <p v-else-if="!hasMore && posts.length > 0" class="text-sm text-wc-text-tertiary">Has visto todas las publicaciones</p>
        </div>
      </div>
    </div>
  </ClientLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-down-enter-active, .slide-down-leave-active { transition: all 0.25s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; max-height: 0; overflow: hidden; }
.slide-down-enter-to, .slide-down-leave-from { opacity: 1; max-height: 600px; }

.feed-item-enter-active { transition: all 0.3s ease; }
.feed-item-leave-active { transition: all 0.2s ease; }
.feed-item-enter-from { opacity: 0; transform: translateY(-12px); }
.feed-item-leave-to { opacity: 0; transform: translateX(20px); }
.feed-item-move { transition: transform 0.3s ease; }
</style>
