<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useApi } from '../composables/useApi';

const props = defineProps({
  // Base endpoint without trailing slash, e.g. /api/v/admin/plan-tickets/123 or /api/v/coach/plan-tickets/123
  endpointBase: { type: String, required: true },
  // 'admin' | 'coach' — affects role label fallback
  role: { type: String, default: 'coach' },
  // Polling every 30s when tab visible
  pollInterval: { type: Number, default: 30_000 },
});

const api = useApi();

const comments = ref([]);
const loading = ref(false);
const posting = ref(false);
const draft = ref('');
const error = ref(null);

let pollTimer = null;

const commentsEndpoint = computed(() => `${props.endpointBase}/comments`);

async function fetchComments() {
  loading.value = comments.value.length === 0;
  try {
    const { data } = await api.get(commentsEndpoint.value);
    comments.value = data.comments || [];
  } catch (e) {
    error.value = 'No se pudieron cargar los comentarios.';
  } finally {
    loading.value = false;
  }
}

async function submit() {
  const body = draft.value.trim();
  if (!body || posting.value) return;
  posting.value = true;
  // Optimistic append
  const tempId = `tmp-${Date.now()}`;
  const optimistic = {
    id: tempId,
    author_type: props.role === 'admin' ? 'admin' : 'coach',
    author_name: props.role === 'admin' ? 'WellCore Team' : 'Tu',
    body,
    created_at: new Date().toISOString(),
    _pending: true,
  };
  comments.value.push(optimistic);
  const original = draft.value;
  draft.value = '';
  try {
    const { data } = await api.post(commentsEndpoint.value, { body });
    // Replace temp with server record
    const idx = comments.value.findIndex(c => c.id === tempId);
    if (idx !== -1 && data.comment) {
      comments.value.splice(idx, 1, data.comment);
    } else {
      await fetchComments();
    }
  } catch (e) {
    // Roll back optimistic
    comments.value = comments.value.filter(c => c.id !== tempId);
    draft.value = original;
    error.value = 'No se pudo enviar el comentario.';
    setTimeout(() => { error.value = null; }, 3000);
  } finally {
    posting.value = false;
  }
}

function authorInitial(c) {
  const n = c.author_name || (c.author_type === 'admin' ? 'W' : 'C');
  return String(n).trim().charAt(0).toUpperCase() || '?';
}

function authorRoleLabel(c) {
  if (c.author_type === 'admin') return 'WellCore Team';
  if (c.author_type === 'coach') return 'Coach';
  return c.author_type || '';
}

function isAdmin(c) {
  return c.author_type === 'admin';
}

function relativeTime(iso) {
  if (!iso) return '';
  const then = new Date(iso).getTime();
  if (Number.isNaN(then)) return iso;
  const diff = Date.now() - then;
  const sec = Math.floor(diff / 1000);
  if (sec < 60) return 'hace unos segundos';
  const min = Math.floor(sec / 60);
  if (min < 60) return `hace ${min} min`;
  const hr = Math.floor(min / 60);
  if (hr < 24) return `hace ${hr} h`;
  const days = Math.floor(hr / 24);
  if (days < 7) return `hace ${days} d`;
  try {
    return new Date(iso).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
  } catch {
    return iso;
  }
}

function onVisibilityChange() {
  if (document.visibilityState === 'visible') {
    fetchComments();
  }
}

onMounted(() => {
  fetchComments();
  pollTimer = setInterval(() => {
    if (document.visibilityState === 'visible') fetchComments();
  }, props.pollInterval);
  document.addEventListener('visibilitychange', onVisibilityChange);
});

onBeforeUnmount(() => {
  clearInterval(pollTimer);
  document.removeEventListener('visibilitychange', onVisibilityChange);
});
</script>

<template>
  <section class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
    <div class="mb-4 flex items-center justify-between">
      <h2 class="font-display text-lg tracking-wide text-wc-text">Comentarios</h2>
      <span class="text-xs text-wc-text-tertiary">{{ comments.length }} mensaje{{ comments.length === 1 ? '' : 's' }}</span>
    </div>

    <!-- Loading skeleton -->
    <div v-if="loading" class="space-y-3">
      <div v-for="n in 2" :key="n" class="animate-pulse rounded-lg border border-wc-border bg-wc-bg-secondary h-16"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="comments.length === 0" class="rounded-lg border border-dashed border-wc-border p-6 text-center text-xs text-wc-text-tertiary">
      Todavia no hay comentarios. Inicia la conversacion.
    </div>

    <!-- List -->
    <div v-else class="space-y-3">
      <div
        v-for="c in comments"
        :key="c.id"
        class="flex gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary p-3"
        :class="c._pending ? 'opacity-60' : ''"
      >
        <div
          class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-semibold"
          :class="isAdmin(c) ? 'bg-wc-accent/20 text-wc-accent' : 'bg-blue-500/15 text-blue-500'"
        >
          {{ authorInitial(c) }}
        </div>
        <div class="min-w-0 flex-1">
          <div class="flex flex-wrap items-center gap-2">
            <p class="text-sm font-semibold text-wc-text">{{ c.author_name || 'Sin nombre' }}</p>
            <span
              class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
              :class="isAdmin(c) ? 'bg-wc-accent/10 text-wc-accent' : 'bg-blue-500/10 text-blue-500'"
            >{{ authorRoleLabel(c) }}</span>
            <span class="text-[11px] text-wc-text-tertiary">{{ relativeTime(c.created_at) }}</span>
          </div>
          <p class="mt-1 whitespace-pre-wrap text-sm leading-relaxed text-wc-text-secondary">{{ c.body }}</p>
        </div>
      </div>
    </div>

    <!-- Input -->
    <div class="mt-4 space-y-2">
      <textarea
        v-model="draft"
        rows="3"
        placeholder="Escribe un comentario para la otra parte..."
        class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
      ></textarea>
      <div class="flex items-center justify-between gap-3">
        <p v-if="error" class="text-xs text-red-400">{{ error }}</p>
        <p v-else class="text-[11px] text-wc-text-tertiary">Los comentarios son visibles para el otro equipo.</p>
        <button
          @click="submit"
          :disabled="posting || !draft.trim()"
          class="shrink-0 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:opacity-90 transition disabled:opacity-50"
        >{{ posting ? 'Enviando...' : 'Enviar comentario' }}</button>
      </div>
    </div>
  </section>
</template>
