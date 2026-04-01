<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();

// State
const loading = ref(true);
const error = ref(null);
const posts = ref([]);
const communityStats = ref({ total_posts: 0, active_members: 0 });
const page = ref(1);
const hasMore = ref(true);
const loadingMore = ref(false);

// New post
const postType = ref('text');
const postContent = ref('');
const postError = ref(null);
const submittingPost = ref(false);

// Comments
const expandedComments = ref({});
const commentTexts = ref({});
const submittingComment = ref({});

// Fetch community feed
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
            params: { page: page.value },
        });
        const d = response.data;
        if (d.stats) communityStats.value = d.stats;

        const newPosts = d.posts?.data || d.posts || [];
        if (reset) {
            posts.value = newPosts;
        } else {
            posts.value.push(...newPosts);
        }
        hasMore.value = newPosts.length >= 15;
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar la comunidad';
    } finally {
        loading.value = false;
        loadingMore.value = false;
    }
}

// Load more
async function loadMore() {
    if (loadingMore.value || !hasMore.value) return;
    loadingMore.value = true;
    page.value++;
    await fetchFeed(false);
}

// Create post
async function createPost() {
    if (!postContent.value.trim()) return;
    submittingPost.value = true;
    postError.value = null;

    try {
        const response = await api.post('/api/v/client/community', {
            type: postType.value,
            content: postContent.value.trim(),
        });
        postContent.value = '';
        postType.value = 'text';
        // Prepend new post
        if (response.data.post) {
            posts.value.unshift(response.data.post);
            communityStats.value.total_posts++;
        } else {
            await fetchFeed(true);
        }
    } catch (err) {
        postError.value = err.response?.data?.message || 'Error al publicar';
    } finally {
        submittingPost.value = false;
    }
}

// React to post
async function toggleReaction(postId, type) {
    const post = posts.value.find(p => p.id === postId);
    if (!post) return;

    try {
        const response = await api.post('/api/v/client/community/react', {
            post_id: postId,
            type: type,
        });
        // Update local state
        if (response.data.reactions) {
            post.reactions = response.data.reactions;
        }
        if (response.data.user_reacted !== undefined) {
            if (!post.user_reactions) post.user_reactions = {};
            post.user_reactions[type] = response.data.user_reacted;
        }
    } catch {
        // Fail silently
    }
}

// Toggle comments visibility
function toggleComments(postId) {
    expandedComments.value[postId] = !expandedComments.value[postId];
}

// Add comment
async function addComment(postId) {
    const text = (commentTexts.value[postId] || '').trim();
    if (!text) return;

    submittingComment.value[postId] = true;
    try {
        const response = await api.post('/api/v/client/community/comment', {
            post_id: postId,
            content: text,
        });
        commentTexts.value[postId] = '';
        const post = posts.value.find(p => p.id === postId);
        if (post) {
            if (!post.comments) post.comments = [];
            if (response.data.comment) {
                post.comments.push(response.data.comment);
            }
            post.comments_count = (post.comments_count || 0) + 1;
        }
    } catch {
        // Fail silently
    } finally {
        submittingComment.value[postId] = false;
    }
}

// Infinite scroll
let scrollObserver = null;
const sentinelRef = ref(null);

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

onUnmounted(() => {
    if (scrollObserver) scrollObserver.disconnect();
});

// Helpers
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

function getInitial(name) {
    return (name || 'U').charAt(0).toUpperCase();
}

function getReactionEmoji(type) {
    const map = { fire: '\uD83D\uDD25', muscle: '\uD83D\uDCAA', heart: '\u2764\uFE0F' };
    return map[type] || type;
}

const charCount = computed(() => postContent.value.length);

const postTypeTabs = [
    { key: 'text', label: 'Post', borderColor: 'border-wc-accent', textColor: 'text-wc-text', bgColor: 'bg-wc-accent/5' },
    { key: 'achievement', label: 'Logro', borderColor: 'border-yellow-500', textColor: 'text-yellow-500', bgColor: 'bg-yellow-500/5' },
    { key: 'pr', label: 'Nuevo PR', borderColor: 'border-green-500', textColor: 'text-green-500', bgColor: 'bg-green-500/5' },
];

function getPlaceholder() {
    if (postType.value === 'achievement') return 'Que lograste hoy? Cuentalo...';
    if (postType.value === 'pr') return 'Describe tu nuevo record personal...';
    return 'Comparte algo con la comunidad...';
}
</script>

<template>
  <ClientLayout>
    <!-- Loading Skeleton -->
    <div v-if="loading" class="space-y-6">
      <div class="h-32 animate-pulse rounded-2xl border border-wc-border bg-wc-bg-tertiary"></div>
      <div class="h-40 animate-pulse rounded-2xl border border-wc-border bg-wc-bg-tertiary"></div>
      <div v-for="i in 3" :key="i" class="h-48 animate-pulse rounded-2xl border border-wc-border bg-wc-bg-tertiary"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex flex-col items-center justify-center py-20">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
        <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
      </div>
      <h2 class="mt-4 font-display text-xl tracking-wide text-wc-text">Error al cargar</h2>
      <p class="mt-2 text-sm text-wc-text-secondary">{{ error }}</p>
      <button
        @click="fetchFeed(true)"
        class="mt-6 rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg"
      >
        Reintentar
      </button>
    </div>

    <!-- Content -->
    <div v-else class="space-y-6">

      <!-- Header -->
      <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-gradient-to-br from-wc-bg-secondary via-wc-bg-tertiary to-wc-bg-secondary p-6">
        <div class="relative z-10 flex items-end justify-between">
          <div>
            <p class="mb-1 text-xs font-semibold uppercase tracking-widest text-wc-accent">Comunidad WellCore</p>
            <h1 class="font-display text-4xl tracking-wide text-wc-text">FEED</h1>
            <p class="mt-1 text-sm text-wc-text-secondary">Comparte tus logros - Celebra los de otros</p>
          </div>
          <div class="hidden items-center gap-4 sm:flex">
            <div class="text-center">
              <p class="font-display text-2xl text-wc-accent">{{ communityStats.total_posts }}</p>
              <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Posts</p>
            </div>
            <div class="h-8 w-px bg-wc-border"></div>
            <div class="text-center">
              <p class="font-display text-2xl text-wc-accent">{{ communityStats.active_members }}</p>
              <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Miembros</p>
            </div>
          </div>
        </div>
        <div class="absolute bottom-0 right-0 h-32 w-32 opacity-5">
          <svg viewBox="0 0 100 100" fill="none" class="h-full w-full text-wc-accent">
            <circle cx="50" cy="50" r="45" stroke="currentColor" stroke-width="1"/>
            <circle cx="50" cy="50" r="30" stroke="currentColor" stroke-width="1"/>
            <circle cx="50" cy="50" r="15" stroke="currentColor" stroke-width="1"/>
          </svg>
        </div>
      </div>

      <!-- Create Post -->
      <div class="overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary">
        <!-- Post type tabs -->
        <div class="flex border-b border-wc-border">
          <button
            v-for="tab in postTypeTabs"
            :key="tab.key"
            @click="postType = tab.key"
            class="flex flex-1 items-center justify-center gap-2 py-3 text-xs font-semibold uppercase tracking-wider transition-colors"
            :class="postType === tab.key
              ? `border-b-2 ${tab.borderColor} ${tab.textColor} ${tab.bgColor}`
              : 'text-wc-text-tertiary hover:text-wc-text-secondary'"
          >
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
          <p v-if="postError" class="mt-1 text-xs text-red-400">{{ postError }}</p>
          <div class="mt-3 flex justify-end">
            <button
              type="submit"
              :disabled="submittingPost || !postContent.trim()"
              class="flex items-center gap-2 rounded-xl bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-wc-accent/20 transition-all hover:bg-wc-accent/90 disabled:opacity-50"
            >
              <svg v-if="!submittingPost" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
              </svg>
              <svg v-else class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              Publicar
            </button>
          </div>
        </form>
      </div>

      <!-- Feed Posts -->
      <div class="space-y-3">
        <div
          v-for="post in posts"
          :key="post.id"
          class="overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary transition-all hover:border-wc-border/80"
        >
          <!-- Post header -->
          <div class="flex items-start gap-3 p-4 pb-0">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent/20">
              <span class="text-sm font-bold text-wc-accent">{{ getInitial(post.author_name) }}</span>
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <p class="text-sm font-semibold text-wc-text">{{ post.author_name || 'Usuario' }}</p>
                <span
                  v-if="post.type === 'achievement'"
                  class="rounded-full bg-yellow-500/10 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-yellow-400"
                >Logro</span>
                <span
                  v-else-if="post.type === 'pr'"
                  class="rounded-full bg-green-500/10 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-green-400"
                >PR</span>
              </div>
              <p class="text-xs text-wc-text-tertiary">{{ timeAgo(post.created_at) }}</p>
            </div>
          </div>

          <!-- Post content -->
          <div class="px-4 py-3">
            <p class="whitespace-pre-line text-sm leading-relaxed text-wc-text">{{ post.content }}</p>
          </div>

          <!-- Reactions -->
          <div class="flex items-center gap-1 border-t border-wc-border/50 px-4 py-2">
            <button
              v-for="rType in ['fire', 'muscle', 'heart']"
              :key="rType"
              @click="toggleReaction(post.id, rType)"
              class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs transition-all"
              :class="post.user_reactions?.[rType]
                ? 'bg-wc-accent/10 text-wc-accent'
                : 'bg-wc-bg-secondary text-wc-text-tertiary hover:bg-wc-bg hover:text-wc-text-secondary'"
            >
              <span>{{ getReactionEmoji(rType) }}</span>
              <span class="font-data font-semibold">{{ post.reactions?.[rType] || 0 }}</span>
            </button>

            <div class="flex-1"></div>

            <!-- Comments toggle -->
            <button
              @click="toggleComments(post.id)"
              class="flex items-center gap-1.5 rounded-full bg-wc-bg-secondary px-3 py-1.5 text-xs text-wc-text-tertiary transition-colors hover:text-wc-text-secondary"
            >
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
              </svg>
              <span class="font-data font-semibold">{{ post.comments_count || 0 }}</span>
            </button>
          </div>

          <!-- Comments section -->
          <div v-if="expandedComments[post.id]" class="border-t border-wc-border/50 bg-wc-bg-secondary/30 px-4 py-3">
            <div v-if="post.comments && post.comments.length" class="space-y-3 mb-3">
              <div v-for="comment in post.comments" :key="comment.id" class="flex items-start gap-2">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-wc-bg-secondary">
                  <span class="text-[10px] font-bold text-wc-text-tertiary">{{ getInitial(comment.author_name) }}</span>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex items-baseline gap-2">
                    <span class="text-xs font-semibold text-wc-text">{{ comment.author_name }}</span>
                    <span class="text-[10px] text-wc-text-tertiary">{{ timeAgo(comment.created_at) }}</span>
                  </div>
                  <p class="text-xs text-wc-text-secondary">{{ comment.content }}</p>
                </div>
              </div>
            </div>
            <div v-else class="mb-3 text-center text-xs text-wc-text-tertiary">Sin comentarios aun</div>

            <!-- Add comment -->
            <div class="flex items-center gap-2">
              <input
                v-model="commentTexts[post.id]"
                @keyup.enter="addComment(post.id)"
                placeholder="Escribe un comentario..."
                class="flex-1 rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-xs text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent/50 focus:outline-none"
              />
              <button
                @click="addComment(post.id)"
                :disabled="submittingComment[post.id] || !(commentTexts[post.id] || '').trim()"
                class="rounded-lg bg-wc-accent px-3 py-2 text-xs font-semibold text-white transition-colors hover:bg-wc-accent/90 disabled:opacity-50"
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
        </div>

        <!-- Empty state -->
        <div v-if="posts.length === 0" class="rounded-2xl border border-dashed border-wc-border bg-wc-bg-tertiary/50 p-16 text-center">
          <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-wc-accent/10">
            <svg class="h-10 w-10 text-wc-accent/50" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </svg>
          </div>
          <h3 class="mt-5 font-display text-2xl tracking-wide text-wc-text">SIN PUBLICACIONES</h3>
          <p class="mx-auto mt-2 max-w-xs text-sm text-wc-text-secondary">Se el primero en compartir algo con la comunidad.</p>
        </div>

        <!-- Loading more / Sentinel -->
        <div ref="sentinelRef" class="py-4 text-center">
          <div v-if="loadingMore" class="flex items-center justify-center gap-2">
            <svg class="h-5 w-5 animate-spin text-wc-accent" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-sm text-wc-text-tertiary">Cargando mas...</span>
          </div>
          <p v-else-if="!hasMore && posts.length > 0" class="text-xs text-wc-text-tertiary">Has visto todas las publicaciones</p>
        </div>
      </div>
    </div>
  </ClientLayout>
</template>
