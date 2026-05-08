<script setup>
import { ref, computed, onMounted, onBeforeUnmount, onUnmounted, nextTick } from 'vue';
import { RouterLink } from 'vue-router';
import { useApi } from '../../composables/useApi';
import { useToast } from '../../composables/useToast';
import { useAuthStore } from '../../stores/auth';
import { useCancellableFetch } from '../../composables/useCancellableFetch';
import ClientLayout from '../../layouts/ClientLayout.vue';
import WcErrorState from '../../components/WcErrorState.vue';
import PulsoRing from '../../components/community/PulsoRing.vue';
import PulsoViewer from '../../components/community/PulsoViewer.vue';
import PulsoUploader from '../../components/community/PulsoUploader.vue';
import GroupPulseFeed from '../../components/community/GroupPulseFeed.vue';
import MentionInput from '../../components/community/MentionInput.vue';
import MentionRenderer from '../../components/community/MentionRenderer.vue';
import ReportPostMenu from '../../components/community/ReportPostMenu.vue';
import CoachBadge from '../../components/community/CoachBadge.vue';
import OfficialBadge from '../../components/community/OfficialBadge.vue';

const api = useApi();
const authStore = useAuthStore();
const toast = useToast();
const { getSignal: getFeedSignal } = useCancellableFetch();

// ── State ────────────────────────────────────────────────────────────
const loading = ref(true);
const error = ref(null);
const posts = ref([]);
const communityStats = ref({ total_posts: 0, active_members: 0 });
const page = ref(1);
const lastPage = ref(1);
const hasMore = ref(true);
const loadingMore = ref(false);

// ── Feed tab (latido | all | following) ──────────────────────────────
// 'latido' renders <GroupPulseFeed/> (eventos agregados del grupo),
// 'all' y 'following' renderizan el feed de posts de la comunidad.
const feedTab = ref('latido'); // 'latido' | 'all' | 'following'

// ── New post ─────────────────────────────────────────────────────────
const postType = ref('text');
const postContent = ref('');
const postErrors = ref({});
const submittingPost = ref(false);
const postMediaFile = ref(null);
const postMediaPreview = ref(null);
const postMediaInputRef = ref(null);

const POST_MEDIA_MIMES = ['image/jpeg', 'image/png', 'image/webp'];
const POST_MEDIA_MAX_BYTES = 10 * 1024 * 1024;

function handlePostMediaSelect(e) {
  const file = e.target.files?.[0] ?? null;
  e.target.value = '';
  if (!file) return;
  if (!POST_MEDIA_MIMES.includes(file.type)) {
    toast.error('Formato no válido. Usa JPG, PNG o WebP.');
    return;
  }
  if (file.size > POST_MEDIA_MAX_BYTES) {
    toast.error('La imagen excede 10 MB.');
    return;
  }
  if (postMediaPreview.value) {
    URL.revokeObjectURL(postMediaPreview.value);
  }
  postMediaFile.value = file;
  postMediaPreview.value = URL.createObjectURL(file);
}

function removePostMedia() {
  if (postMediaPreview.value) {
    URL.revokeObjectURL(postMediaPreview.value);
  }
  postMediaFile.value = null;
  postMediaPreview.value = null;
}

// ── Comments ─────────────────────────────────────────────────────────
const expandedComments = ref({});
const commentTexts = ref({});
const submittingComment = ref({});

// ── Delete ───────────────────────────────────────────────────────────
const confirmDeleteId = ref(null);
const deletingPost = ref(null);

// ── Right panel data ─────────────────────────────────────────────────
const storiesMembers = ref([]);
const activeMembersList = ref([]);

// ── Pulsos ────────────────────────────────────────────────────────────
const activePulsoId = ref(null);
const showPulsoUploader = ref(false);
const pulsoUploaderPrefill = ref(null);

function openPulso(pulsoId) {
  activePulsoId.value = pulsoId;
}

function closePulsoViewer() {
  activePulsoId.value = null;
}

function openPulsoUploader(prefill) {
  pulsoUploaderPrefill.value = prefill ?? null;
  showPulsoUploader.value = true;
}

function onPulsoCreated() {
  showPulsoUploader.value = false;
  pulsoUploaderPrefill.value = null;
  fetchFeed(true);
}

function onPulsoDeleted() {
  activePulsoId.value = null;
  fetchFeed(true);
}

// ── Community tour ───────────────────────────────────────────────────
const tourSeen = ref(!!localStorage.getItem('wc_community_tour_seen'));
function dismissTour() {
  localStorage.setItem('wc_community_tour_seen', '1');
  tourSeen.value = true;
}

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
const hasActiveStories = computed(() => storiesMembers.value.some(m => m.has_new));

// ── Fetch community feed ─────────────────────────────────────────────
async function fetchFeed(reset = false) {
  if (reset) {
    page.value = 1;
    hasMore.value = true;
    posts.value = [];
  }

  if (reset) loading.value = true;
  error.value = null;

  const signal = getFeedSignal();

  try {
    const params = { page: page.value, per_page: 10 };
    if (feedTab.value === 'following') params.tab = 'following';
    const response = await api.get('/api/v/client/community', {
      params,
      signal,
    });
    const d = response.data;

    if (d.community_stats) communityStats.value = d.community_stats;
    if (d.stats) communityStats.value = d.stats;
    if (reset && d.stories_members) storiesMembers.value = d.stories_members;
    if (reset && d.active_members_list) activeMembersList.value = d.active_members_list;

    const newPosts = d.posts || [];
    if (reset) {
      // On full reload, clean up old subscriptions before replacing the list.
      unsubscribeAllPostChannels();
      posts.value = newPosts;
    } else {
      posts.value.push(...newPosts);
    }

    // Subscribe to real-time channels for freshly loaded posts.
    subscribeToPostChannels(newPosts);

    if (d.pagination) {
      lastPage.value = d.pagination.last_page;
      hasMore.value = d.pagination.current_page < d.pagination.last_page;
    } else {
      hasMore.value = newPosts.length >= 10;
    }
  } catch (err) {
    if (err.name !== 'CanceledError' && err.name !== 'AbortError') {
      error.value = err.response?.data?.message || 'Error al cargar la comunidad';
    }
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
  const trimmed = postContent.value.trim();
  if (!trimmed && !postMediaFile.value) return;
  submittingPost.value = true;
  postErrors.value = {};

  try {
    const fd = new FormData();
    fd.append('post_type', postType.value);
    fd.append('content', trimmed);
    if (postMediaFile.value) {
      fd.append('media', postMediaFile.value);
    }

    const response = await api.post('/api/v/client/community', fd);

    let displayContent = trimmed;
    if (postType.value === 'achievement' && trimmed && !trimmed.startsWith('Logro: ')) {
      displayContent = 'Logro: ' + trimmed;
    } else if (postType.value === 'pr' && trimmed && !trimmed.startsWith('Nuevo PR: ')) {
      displayContent = 'Nuevo PR: ' + trimmed;
    }

    const newPost = {
      id: response.data.id,
      client_id: currentClientId.value,
      client_name: localStorage.getItem('wc_user_name') || 'Yo',
      content: displayContent,
      post_type: postType.value,
      image_url: response.data.image_url ?? null,
      created_at: response.data.created_at || new Date().toISOString(),
      reactions_count: 0,
      comments_count: 0,
      my_reactions: [],
      reaction_counts: {},
      comments: [],
    };

    posts.value.unshift(newPost);
    communityStats.value.total_posts++;

    postContent.value = '';
    postType.value = 'text';
    removePostMedia();
  } catch (err) {
    if (err.response?.status === 422) {
      postErrors.value = err.response.data.errors || {};
    }
    toast.apiError(err, 'No pudimos publicar tu post.');
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
  } catch (err) {
    // Revert optimistic update
    if (wasActive) {
      post.my_reactions = [...post.my_reactions, reactionType];
      counts[reactionType] = (counts[reactionType] || 0) + 1;
    } else {
      post.my_reactions = post.my_reactions.filter(r => r !== reactionType);
      counts[reactionType] = Math.max(0, (counts[reactionType] || 1) - 1);
    }
    post.reaction_counts = { ...counts };
    toast.apiError(err, 'No pudimos registrar tu reacción.');
  }
}

// ── Toggle comments ──────────────────────────────────────────────────
function toggleComments(postId) {
  expandedComments.value[postId] = !expandedComments.value[postId];
}

// ── Add comment ──────────────────────────────────────────────────────
async function addComment(postId) {
  // Anti doble-submit
  if (submittingComment.value[postId]) return;

  const text = (commentTexts.value[postId] || '').trim();
  if (!text) {
    toast.warn('Escribe un comentario antes de enviar.');
    return;
  }
  if (text.length > 500) {
    toast.warn('El comentario es muy largo (máx. 500).');
    return;
  }

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
  } catch (err) {
    toast.apiError(err, 'No pudimos publicar tu comentario.');
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
    toast.success('Post eliminado.');
  } catch (err) {
    toast.apiError(err, 'No pudimos eliminar el post.');
  } finally {
    deletingPost.value = null;
    confirmDeleteId.value = null;
  }
}

function cancelDelete() {
  confirmDeleteId.value = null;
}

// ── Realtime: community-post channels ────────────────────────────
// Keeps a Set of post IDs we are currently subscribed to so we
// never double-subscribe when loadMore appends new pages.
const subscribedPostIds = new Set();

function subscribeToPostChannels(postList) {
  if (!window.Echo) return;

  postList.forEach(post => {
    if (subscribedPostIds.has(post.id)) return;
    subscribedPostIds.add(post.id);

    window.Echo.private(`community-post.${post.id}`)
      .listen('.reaction.toggled', (e) => {
        const p = posts.value.find(p => p.id === e.post_id);
        if (!p) return;
        // reaction_counts is a key-value object { like: 2, fire: 1, ... }
        p.reaction_counts = { ...(p.reaction_counts || {}), [e.reaction_type]: e.count };
      })
      .listen('.comment.added', (e) => {
        const p = posts.value.find(p => p.id === e.post_id);
        if (!p) return;
        // Only increment the counter; do not push into comments[] to avoid
        // duplicates for the author whose optimistic update already added it.
        const isOwnComment = Number(e.client_id) === currentClientId.value;
        if (!isOwnComment) {
          p.comments_count = (p.comments_count ?? 0) + 1;
          // If comments section is open, push the comment live.
          if (expandedComments.value[e.post_id]) {
            if (!p.comments) p.comments = [];
            p.comments.push({
              id: Date.now(),
              client_id: e.client_id,
              client_name: e.client_name,
              content: e.content,
              created_at: e.created_at,
            });
          }
        }
      });
  });
}

function unsubscribeAllPostChannels() {
  subscribedPostIds.forEach(id => {
    window.Echo?.leave(`community-post.${id}`);
  });
  subscribedPostIds.clear();
}

// ── Switch feed tab ──────────────────────────────────────────────────
// Latido renderiza <GroupPulseFeed/> que tiene su propio loader;
// no hace falta refetch del community feed cuando vamos a esa pestaña.
// Al volver a 'all'/'following' rehicimos infinite scroll: el sentinel
// div estaba unmounted bajo Latido y el IntersectionObserver perdió target.
function switchFeedTab(tab) {
  if (feedTab.value === tab) return;
  confirmDeleteId.value = null;
  feedTab.value = tab;
  if (tab !== 'latido') {
    fetchFeed(true);
    nextTick(() => {
      // Dispose previous observer si existía, luego re-attach al sentinel
      // recién montado por el v-else.
      if (scrollObserver) {
        scrollObserver.disconnect();
        scrollObserver = null;
      }
      setupInfiniteScroll();
    });
  }
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
  // Si arrancamos en 'latido', GroupPulseFeed se encarga de su propia carga.
  // Igual hacemos fetch del community feed para que el contador del header
  // (communityStats) y la lista de pulsos del lateral estén poblados, pero
  // no pagamos doble loading visible — el feed de posts está oculto.
  fetchFeed(true).then(() => {
    setTimeout(() => setupInfiniteScroll(), 100);
  });
});

onBeforeUnmount(() => {
  if (scrollObserver) scrollObserver.disconnect();
});

onUnmounted(() => {
  unsubscribeAllPostChannels();
  if (postMediaPreview.value?.startsWith('blob:')) {
    URL.revokeObjectURL(postMediaPreview.value);
  }
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
    <div class="wc-shell wc-shell--community">
    <!-- Loading Skeleton -->
    <div v-if="loading" class="space-y-6">
      <div class="h-32 animate-pulse rounded-2xl border border-wc-border bg-wc-bg-tertiary"></div>
      <div class="h-40 animate-pulse rounded-2xl border border-wc-border bg-wc-bg-tertiary"></div>
      <div v-for="n in 3" :key="n" class="h-48 animate-pulse rounded-2xl border border-wc-border bg-wc-bg-tertiary"></div>
    </div>

    <!-- Error State -->
    <WcErrorState v-else-if="error" :message="error" @retry="fetchFeed(true)" />

    <!-- Content: two-column grid (feed + right panel) -->
    <div v-else class="flex flex-col gap-6 lg:grid lg:grid-cols-[1fr_300px] lg:items-start">

      <!-- ──────────────────── LEFT COLUMN ──────────────────── -->
      <div class="min-w-0 space-y-6">

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- PULSOS ROW                                                     -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- ── PULSOS ROW ──────────────────────────────────────────── -->
      <div v-if="hasActiveStories" class="overflow-x-auto pb-1">
        <div class="flex gap-4 px-1">
          <!-- Botón "Crear Pulso" -->
          <button
            @click="openPulsoUploader()"
            class="flex flex-col items-center gap-1.5 focus:outline-none"
          >
            <div class="relative flex h-16 w-16 items-center justify-center rounded-full border-2 border-dashed border-zinc-700 bg-zinc-800/60 hover:border-wc-accent/60 transition-colors">
              <svg class="h-6 w-6 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
            </div>
            <span class="text-[10px] text-zinc-600">Tu Pulso</span>
          </button>

          <!-- Pulsos de miembros -->
          <PulsoRing
            v-for="member in storiesMembers"
            :key="member.pulso_id ?? member.id"
            :name="member.name ?? member.client_name ?? ''"
            :initials="member.initials ?? '?'"
            :ring-color="member.ring_color ?? 'red'"
            :has-new="member.has_new ?? false"
            :pulso-id="member.pulso_id ?? null"
            size="md"
            @click="member.pulso_id ? openPulso(member.pulso_id) : undefined"
          />
        </div>
      </div>

      <!-- Botón "Publicar Pulso" cuando no hay pulsos activos -->
      <div v-if="!hasActiveStories" class="flex justify-center py-2">
        <button @click="openPulsoUploader()"
          class="flex items-center gap-2 rounded-xl border border-dashed border-zinc-700 px-4 py-2 text-sm text-zinc-500 hover:border-wc-accent/60 hover:text-zinc-300 transition-colors">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Sé el primero en publicar un Pulso
        </button>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- TOUR DE BIENVENIDA — solo si no hay stories activas y no visto -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div
        v-if="!hasActiveStories && !tourSeen"
        class="relative rounded-2xl border border-wc-accent/30 bg-wc-bg-secondary p-5"
      >
        <button
          @click="dismissTour"
          class="absolute right-4 top-4 text-wc-text-secondary hover:text-wc-text transition-colors"
          aria-label="Cerrar"
        >✕</button>

        <p class="mb-4 text-xs font-semibold uppercase tracking-[0.25em] text-wc-accent">Bienvenido a la Comunidad</p>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
          <div class="flex items-start gap-3">
            <span class="mt-0.5 shrink-0 text-xl">🏆</span>
            <div>
              <p class="text-sm font-semibold text-wc-text">Comparte un logro</p>
              <p class="text-xs text-wc-text-secondary">Usa la pestaña "Logro" para celebrar tus victorias con el resto del equipo.</p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <span class="mt-0.5 shrink-0 text-xl">💪</span>
            <div>
              <p class="text-sm font-semibold text-wc-text">Publica un PR</p>
              <p class="text-xs text-wc-text-secondary">Rompiste tu récord personal? Compártelo en "Nuevo PR" y recibe el apoyo del grupo.</p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <span class="mt-0.5 shrink-0 text-xl">🔥</span>
            <div>
              <p class="text-sm font-semibold text-wc-text">Reacciona a los posts</p>
              <p class="text-xs text-wc-text-secondary">Dale fuego, fuerza o un aplauso a los posts de tus compañeros para motivarlos.</p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <span class="mt-0.5 shrink-0 text-xl">👥</span>
            <div>
              <p class="text-sm font-semibold text-wc-text">Pestaña "Siguiendo"</p>
              <p class="text-xs text-wc-text-secondary">Cuando sigas miembros, su contenido aparecerá en tu feed personalizado.</p>
            </div>
          </div>
        </div>

        <button
          @click="dismissTour"
          class="mt-5 w-full rounded-xl border border-wc-accent/40 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-wc-accent transition-colors hover:bg-wc-accent/10"
        >Entendido, ¡vamos!</button>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- HEADER                                                         -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div class="wc-card-hero wc-topline wc-grain relative overflow-hidden rounded-2xl border border-wc-border p-8">
        <div class="wc-orb-tr"></div>
        <div class="relative z-10 flex items-end justify-between">
          <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.3em] text-wc-accent">Comunidad WellCore</p>
            <h1 class="wc-text-gradient font-display text-5xl sm:text-6xl tracking-wide leading-none">COMUNIDAD</h1>
            <p class="mt-2 text-sm text-wc-text-secondary">Comparte tus logros &middot; Celebra los de otros</p>
          </div>
          <div class="hidden items-center gap-5 sm:flex">
            <div class="text-center">
              <p class="font-display text-4xl text-wc-accent tabular-nums">{{ communityStats.total_posts }}</p>
              <p class="text-[10px] font-semibold tracking-[0.25em] uppercase text-wc-text-secondary">Posts</p>
            </div>
            <div class="h-10 w-px bg-wc-border"></div>
            <div class="text-center">
              <p class="font-display text-4xl text-wc-accent tabular-nums">{{ communityStats.active_members }}</p>
              <p class="text-[10px] font-semibold tracking-[0.25em] uppercase text-wc-text-secondary">Miembros</p>
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
      <!-- FEED TABS (Latido | All | Following)                           -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div class="flex gap-1 rounded-xl border border-wc-border bg-wc-bg-secondary p-1">
        <button
          type="button"
          @click="switchFeedTab('latido')"
          class="flex-1 rounded-lg py-2 text-sm font-semibold uppercase tracking-wider transition-all"
          :class="feedTab === 'latido'
            ? 'bg-wc-bg-tertiary text-wc-text shadow-sm'
            : 'text-wc-text-tertiary hover:text-wc-text-secondary'"
        >
          Latido
        </button>
        <button
          type="button"
          @click="switchFeedTab('all')"
          class="flex-1 rounded-lg py-2 text-sm font-semibold uppercase tracking-wider transition-all"
          :class="feedTab === 'all'
            ? 'bg-wc-bg-tertiary text-wc-text shadow-sm'
            : 'text-wc-text-tertiary hover:text-wc-text-secondary'"
        >
          Comunidad
        </button>
        <button
          type="button"
          @click="switchFeedTab('following')"
          class="flex-1 rounded-lg py-2 text-sm font-semibold uppercase tracking-wider transition-all"
          :class="feedTab === 'following'
            ? 'bg-wc-bg-tertiary text-wc-text shadow-sm'
            : 'text-wc-text-tertiary hover:text-wc-text-secondary'"
        >
          Siguiendo
        </button>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- CREATE POST                                                     -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div class="wc-glass wc-lift overflow-hidden rounded-2xl border border-wc-border">
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
            <MentionInput
              v-model="postContent"
              :rows="3"
              :max-length="1000"
              :placeholder="getPlaceholder()"
              scope="coach-team"
            />
            <span class="absolute bottom-3 right-3 text-[10px] tabular-nums text-wc-text-tertiary pointer-events-none">
              {{ charCount }}/1000
            </span>
          </div>

          <!-- Validation errors -->
          <p v-if="postErrors.content" class="mt-1 text-sm text-red-400">{{ postErrors.content[0] || postErrors.content }}</p>
          <p v-if="postErrors.post_type" class="mt-1 text-sm text-red-400">{{ postErrors.post_type[0] }}</p>
          <p v-if="postErrors.media" class="mt-1 text-sm text-red-400">{{ postErrors.media[0] || postErrors.media }}</p>

          <!-- Media preview -->
          <div v-if="postMediaPreview" class="mt-3 relative inline-block max-w-xs">
            <img
              :src="postMediaPreview"
              alt="Vista previa"
              class="max-h-56 w-auto rounded-xl border border-wc-border object-cover"
            />
            <button
              type="button"
              @click="removePostMedia"
              class="absolute right-2 top-2 rounded-full bg-black/70 p-1.5 text-white transition-opacity hover:bg-black/90"
              aria-label="Quitar foto"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="mt-3 flex items-center justify-between gap-2">
            <!-- Attach photo button -->
            <label
              class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary transition-colors hover:border-wc-accent/40 hover:text-wc-text"
              :class="{ 'pointer-events-none opacity-50': submittingPost }"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
              </svg>
              <span class="hidden sm:inline">{{ postMediaFile ? 'Cambiar foto' : 'Adjuntar foto' }}</span>
              <span class="sm:hidden">Foto</span>
              <input
                ref="postMediaInputRef"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="hidden"
                @change="handlePostMediaSelect"
              />
            </label>

            <button
              type="submit"
              :disabled="submittingPost || (!postContent.trim() && !postMediaFile)"
              class="btn-ripple btn-press flex items-center gap-2 rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold uppercase tracking-wider text-white shadow-lg shadow-wc-accent/30 transition-all hover:bg-wc-accent/90 disabled:opacity-50"
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
      <!-- Tab Latido: render Latido del Grupo (eventos agregados) -->
      <GroupPulseFeed v-if="feedTab === 'latido'" />

      <!-- Tabs Comunidad / Siguiendo: feed tradicional de posts -->
      <div v-else class="space-y-3">
        <TransitionGroup name="feed-item" tag="div" class="space-y-3">
          <div
            v-for="post in posts"
            :key="post.id"
            class="wc-stagger-enter group relative overflow-hidden rounded-2xl border border-wc-border transition-all hover:border-wc-accent/30 hover:shadow-xl hover:shadow-black/10"
            :class="post.post_type === 'achievement' || post.post_type === 'pr' ? 'wc-card-hero wc-grain wc-lift' : 'wc-glass wc-lift'"
          >
            <div v-if="post.post_type === 'achievement' || post.post_type === 'pr'" class="wc-orb-tr"></div>
            <!-- Accent strip for special types -->
            <div
              v-if="post.post_type && post.post_type !== 'text'"
              class="h-0.5 bg-gradient-to-r"
              :class="getPostTypeInfo(post.post_type).gradient"
            ></div>

            <div class="relative z-10 p-5">
              <!-- Header row -->
              <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                  <!-- Avatar + name: clickeable → perfil publico -->
                  <RouterLink
                    :to="`/client/profile/${post.client_id}`"
                    class="group flex items-center gap-3"
                  >
                    <!-- Avatar with type badge -->
                    <div class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-full overflow-hidden bg-gradient-to-br from-wc-accent/40 to-wc-accent/10 text-sm font-bold text-wc-accent ring-2 ring-wc-accent/60 ring-offset-2 ring-offset-wc-bg transition-all group-hover:ring-wc-accent">
                      <img
                        v-if="post.client_avatar"
                        :src="post.client_avatar"
                        :alt="post.client_name"
                        class="h-full w-full object-cover"
                      />
                      <span v-else>{{ getInitials(post.client_name) }}</span>
                      <span
                        v-if="post.post_type && post.post_type !== 'text'"
                        class="absolute -bottom-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-wc-bg-tertiary text-[10px] ring-1 ring-wc-border z-10"
                      >{{ getPostTypeInfo(post.post_type).emoji }}</span>
                    </div>
                    <div>
                      <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-semibold leading-tight text-wc-text group-hover:text-wc-accent transition-colors">{{ post.client_name || post.author_name || 'Miembro' }}</p>
                        <CoachBadge v-if="post.author_type === 'coach'" size="xs" />
                        <OfficialBadge v-if="post.author_type === 'admin' || post.is_official" />
                        <!-- Post type badge -->
                        <span
                          v-if="post.post_type === 'achievement' || post.post_type === 'pr'"
                          class="wc-pr-badge rounded-full px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-[0.15em]"
                        >{{ getPostTypeInfo(post.post_type).emoji }} {{ getPostTypeInfo(post.post_type).label }}</span>
                        <span
                          v-else-if="post.post_type && post.post_type !== 'text'"
                          class="rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider"
                          :class="`${getPostTypeInfo(post.post_type).bg} ${getPostTypeInfo(post.post_type).text}`"
                        >{{ getPostTypeInfo(post.post_type).emoji }} {{ getPostTypeInfo(post.post_type).label }}</span>
                      </div>
                      <!-- Medals row (si el backend las retorna en client.medals) -->
                      <div v-if="post.client && (post.client.medals || []).length > 0" class="flex gap-0.5 mt-0.5">
                        <span
                          v-for="m in (post.client.medals || []).slice(0, 3)"
                          :key="m.name"
                          class="text-xs"
                          :title="m.name"
                        >{{ m.icon }}</span>
                      </div>
                      <p class="flex items-center gap-1 text-xs text-wc-text-tertiary mt-0.5">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        {{ timeAgo(post.created_at) }}
                      </p>
                    </div>
                  </RouterLink>
                </div>

                <!-- Right-side actions: Report (any post) + Delete (own only) -->
                <div class="shrink-0 flex items-center gap-1">
                  <ReportPostMenu v-if="!isOwnPost(post)" :post-id="post.id" />
                  <button
                    v-if="isOwnPost(post)"
                    @click="deletePost(post.id)"
                    :disabled="deletingPost === post.id"
                    :title="confirmDeleteId === post.id ? 'Confirmar eliminar' : 'Eliminar publicacion'"
                    class="rounded-lg p-1.5 text-wc-text-tertiary opacity-0 transition-all group-hover:opacity-100 hover:bg-red-500/10 hover:text-red-400 disabled:opacity-50"
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
              <MentionRenderer v-if="post.content" :content="post.content" class="mt-3" />

              <!-- Image -->
              <div v-if="post.image_url" class="mt-3 overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
                <img
                  :src="post.image_url"
                  :alt="post.client_name + ' — foto del post'"
                  loading="lazy"
                  class="w-full max-h-[480px] object-cover"
                />
              </div>

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
                      :disabled="submittingComment[post.id]"
                      placeholder="Comentar..."
                      maxlength="500"
                      class="flex-1 rounded-xl border border-wc-border bg-wc-bg px-3 py-1.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent/50 focus:outline-none focus:ring-1 focus:ring-wc-accent/20 disabled:opacity-60"
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

        <!-- Empty state: following tab with zero posts -->
        <div v-if="!loading && posts.length === 0 && feedTab === 'following'"
             class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-wc-border py-12 text-center gap-3">
          <span class="text-4xl leading-none">&#128101;</span>
          <p class="font-semibold text-wc-text">Aún no sigues a nadie</p>
          <p class="text-sm text-wc-text/50">Visita perfiles de tu comunidad y sigue a quien te inspire.</p>
          <button
            @click="switchFeedTab('all')"
            class="mt-2 rounded-xl bg-wc-accent px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-wc-accent/90 active:scale-95"
          >
            Ver comunidad
          </button>
        </div>

        <!-- Empty state: general -->
        <div v-else-if="!loading && posts.length === 0" class="wc-glass wc-grain relative overflow-hidden rounded-2xl border border-dashed border-wc-border p-16 text-center">
          <div class="wc-orb-tr"></div>
          <div class="relative z-10 mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-wc-accent/30 to-wc-accent/5 ring-1 ring-wc-accent/40">
            <svg class="h-10 w-10 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </svg>
          </div>
          <h3 class="relative z-10 mt-5 wc-text-gradient font-display text-3xl tracking-wide">SE EL PRIMERO</h3>
          <p class="relative z-10 mt-2 text-sm text-wc-text-secondary">Comparte tu logro y motiva a la comunidad WellCore</p>
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

      <!-- END LEFT COLUMN -->
      </div>

      <!-- ──────────────────── RIGHT COLUMN ──────────────────── -->
      <div class="hidden lg:sticky lg:top-6 lg:block lg:space-y-4">

        <!-- My Phase -->
        <div class="wc-glass rounded-2xl border border-wc-border p-4">
          <p class="text-[10px] font-semibold uppercase tracking-[0.25em] text-wc-text-tertiary">Mi Fase</p>
          <div class="mt-3 flex items-center gap-3">
            <div class="relative flex h-3 w-3 shrink-0">
              <span class="absolute inset-0 animate-ping rounded-full bg-wc-accent opacity-30"></span>
              <span class="relative h-3 w-3 rounded-full bg-wc-accent"></span>
            </div>
            <p class="font-display text-lg tracking-wide text-wc-text">S1 · Adaptación</p>
          </div>
          <div class="mt-3">
            <div class="mb-1 flex justify-between text-[10px] text-wc-text-tertiary">
              <span>Semana 1</span>
              <span>de 4</span>
            </div>
            <div class="h-1 overflow-hidden rounded-full bg-wc-border">
              <div class="h-full rounded-full bg-wc-accent" style="width:25%"></div>
            </div>
          </div>
        </div>

        <!-- Community stats 2×2 -->
        <div class="wc-glass rounded-2xl border border-wc-border p-4">
          <p class="mb-3 text-[10px] font-semibold uppercase tracking-[0.25em] text-wc-text-tertiary">Estadísticas</p>
          <div class="grid grid-cols-2 gap-2">
            <div class="rounded-xl bg-wc-bg-tertiary p-3 text-center">
              <p class="font-display text-2xl tabular-nums text-wc-accent">{{ communityStats.total_posts ?? 0 }}</p>
              <p class="mt-0.5 text-[9px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Posts</p>
            </div>
            <div class="rounded-xl bg-wc-bg-tertiary p-3 text-center">
              <p class="font-display text-2xl tabular-nums text-wc-accent">{{ communityStats.active_members ?? 0 }}</p>
              <p class="mt-0.5 text-[9px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Miembros</p>
            </div>
            <div class="rounded-xl bg-wc-bg-tertiary p-3 text-center">
              <p class="font-display text-2xl tabular-nums text-yellow-500">{{ communityStats.total_achievements ?? 0 }}</p>
              <p class="mt-0.5 text-[9px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Logros</p>
            </div>
            <div class="rounded-xl bg-wc-bg-tertiary p-3 text-center">
              <p class="font-display text-2xl tabular-nums text-green-500">{{ communityStats.total_prs ?? 0 }}</p>
              <p class="mt-0.5 text-[9px] font-semibold uppercase tracking-wider text-wc-text-tertiary">PRs</p>
            </div>
          </div>
        </div>

        <!-- Active members -->
        <div v-if="activeMembersList.length > 0" class="wc-glass rounded-2xl border border-wc-border p-4">
          <p class="mb-3 text-[10px] font-semibold uppercase tracking-[0.25em] text-wc-text-tertiary">Miembros Activos</p>
          <div class="space-y-2.5">
            <div v-for="member in activeMembersList" :key="member.id" class="flex items-center gap-2.5">
              <div class="relative flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-wc-accent/30 to-wc-accent/10 text-xs font-bold text-wc-accent">
                {{ member.initials }}
                <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-wc-bg bg-green-500"></span>
              </div>
              <p class="truncate text-sm text-wc-text">{{ member.name }}</p>
            </div>
          </div>
        </div>

      </div>
      <!-- END RIGHT COLUMN -->

    </div>
    <!-- END CONTENT GRID -->

    <!-- PulsoViewer modal -->
    <PulsoViewer
      v-if="activePulsoId"
      :pulso-id="activePulsoId"
      @close="closePulsoViewer"
      @deleted="onPulsoDeleted"
    />

    <!-- PulsoUploader modal -->
    <PulsoUploader
      v-if="showPulsoUploader"
      :prefill-type="pulsoUploaderPrefill?.type ?? 'libre'"
      :prefill-stats="pulsoUploaderPrefill?.stats"
      :prefill-session-id="pulsoUploaderPrefill?.sessionId"
      @close="showPulsoUploader = false"
      @created="onPulsoCreated"
    />
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
