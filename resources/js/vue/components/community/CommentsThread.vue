<script setup>
import { ref, watch } from 'vue';
import { useApi } from '../../composables/useApi';

const props = defineProps({
  postId: {
    type: Number,
    required: true,
  },
  visible: {
    type: Boolean,
    default: false,
  },
});

const api = useApi();
const comments = ref([]);
const newComment = ref('');
const sending = ref(false);
const loaded = ref(false);

function initials(name) {
  if (!name) return '?';
  return name.split(' ').slice(0, 2).map(w => w[0] || '').join('').toUpperCase() || '?';
}

function timeAgo(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const now = new Date();
  const seconds = Math.floor((now - date) / 1000);
  if (seconds < 60) return 'ahora';
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `${minutes}m`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours}h`;
  const days = Math.floor(hours / 24);
  if (days < 7) return `${days}d`;
  return date.toLocaleDateString('es-CO', { day: 'numeric', month: 'short' });
}

async function load() {
  if (loaded.value) return;
  try {
    const response = await api.get(`/api/v/community/posts/${props.postId}/comments`);
    comments.value = response.data.comments ?? [];
    loaded.value = true;
  } catch {
    comments.value = [];
  }
}

async function send() {
  const text = newComment.value.trim();
  if (!text || sending.value) return;
  sending.value = true;
  try {
    const response = await api.post(`/api/v/community/posts/${props.postId}/comments`, { content: text });
    newComment.value = '';
    // Append locally for instant feedback; also reload to get server-assigned id/timestamps
    const created = response.data ?? {};
    comments.value.push({
      id: created.id ?? Date.now(),
      content: text,
      created_at: created.created_at ?? new Date().toISOString(),
      client: {
        id: created.client_id ?? 0,
        name: created.client_name ?? 'Yo',
        avatar_url: null,
      },
    });
  } catch {
    // Silently ignore — user sees no change; toast handled by parent if needed
  } finally {
    sending.value = false;
  }
}

// Load when first becoming visible
watch(
  () => props.visible,
  (val) => {
    if (val && !loaded.value) load();
  },
  { immediate: true }
);
</script>

<template>
  <!-- v-show preserves DOM state (input text) across toggles -->
  <div v-show="visible" class="border-t border-wc-border bg-wc-bg-secondary rounded-b-2xl">

    <!-- Comments list -->
    <div class="max-h-40 overflow-y-auto space-y-2 px-4 pt-3">
      <div v-if="comments.length === 0 && loaded" class="py-2 text-center text-xs text-wc-text-secondary">
        Se el primero en comentar.
      </div>
      <div
        v-for="c in comments"
        :key="c.id"
        class="flex items-start gap-2 text-sm"
      >
        <div class="h-6 w-6 shrink-0 rounded-full bg-wc-accent/20 flex items-center justify-center text-[10px] font-bold text-wc-accent">
          {{ initials(c.client?.name) }}
        </div>
        <div class="min-w-0 flex-1">
          <div class="flex items-baseline gap-1.5">
            <span class="font-semibold text-xs text-wc-text">{{ c.client?.name ?? 'Miembro' }}</span>
            <span class="text-[10px] text-wc-text-tertiary tabular-nums">{{ timeAgo(c.created_at) }}</span>
          </div>
          <span class="text-sm text-wc-text-secondary">{{ c.content }}</span>
        </div>
      </div>
    </div>

    <!-- Input -->
    <div class="flex gap-2 px-4 py-3">
      <input
        v-model="newComment"
        @keyup.enter="send"
        placeholder="Comentar..."
        maxlength="500"
        class="flex-1 rounded-lg border border-wc-border bg-wc-bg px-3 py-1.5 text-sm text-wc-text placeholder-wc-text-tertiary outline-none transition-colors focus:border-wc-accent focus:ring-1 focus:ring-wc-accent/50"
      />
      <button
        @click="send"
        :disabled="sending || !newComment.trim()"
        class="rounded-lg bg-wc-accent px-3 py-1.5 text-xs font-semibold text-white transition-all duration-150 hover:bg-wc-accent/90 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="!sending">Enviar</span>
        <svg v-else class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
      </button>
    </div>
  </div>
</template>
