<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useApi } from '../../../composables/useApi';
import { useToast } from '../../../composables/useToast';
import PulsoStatCard from './PulsoStatCard.vue';

interface PulsoDetail {
  id: number;
  client_id: number;
  client_name: string;
  initials: string;
  pulso_type: string;
  ring_color: string;
  media_type: string;
  media_url: string | null;
  caption: string | null;
  stats_overlay: Record<string, any> | null;
  expires_at: string;
  views_count: number;
  reaction_counts: Record<string, number>;
  my_reactions: string[];
  is_mine: boolean;
  viewers?: Array<{ name: string; initials: string; viewed_at: string }>;
}

const props = defineProps<{ pulsoId: number }>();
const emit = defineEmits<{ close: []; deleted: [] }>();

const api = useApi();
const toast = useToast();

const pulso = ref<PulsoDetail | null>(null);
const loading = ref(true);
const showViewers = ref(false);
const deleting = ref(false);

const reactionEmojis: Record<string, string> = {
  fire:   '🔥',
  muscle: '💪',
  trophy: '🏆',
  energy: '⚡',
};

const expiryProgress = ref(100);
let expiryTimer: ReturnType<typeof setInterval>;

function calcExpiryProgress(expiresAt: string) {
  const expiresMs = new Date(expiresAt).getTime();
  const now = Date.now();
  const totalMs = 24 * 60 * 60 * 1000;
  const remainingMs = expiresMs - now;
  expiryProgress.value = Math.max(0, Math.min(100, (remainingMs / totalMs) * 100));
}

onMounted(async () => {
  try {
    const response = await api.get(`/api/v/client/pulsos/${props.pulsoId}`);
    if (response?.data?.pulso) {
      pulso.value = response.data.pulso;
      calcExpiryProgress(response.data.pulso.expires_at);
      expiryTimer = setInterval(() => {
        if (pulso.value) calcExpiryProgress(pulso.value.expires_at);
      }, 30_000);
    }
  } catch {
    // silently handle — loading state will show nothing
  } finally {
    loading.value = false;
  }
});

onBeforeUnmount(() => clearInterval(expiryTimer));

async function toggleReaction(type: string) {
  if (!pulso.value) return;
  try {
    const response = await api.post(`/api/v/client/pulsos/${pulso.value.id}/react`, {
      reaction_type: type,
    });
    if (response?.data) {
      pulso.value.reaction_counts = response.data.reaction_counts;
      if (response.data.toggled) {
        pulso.value.my_reactions = [...pulso.value.my_reactions, type];
      } else {
        pulso.value.my_reactions = pulso.value.my_reactions.filter((r: string) => r !== type);
      }
    }
  } catch {
    // silently ignore reaction errors
  }
}

async function deletePulso() {
  if (!pulso.value) return;
  deleting.value = true;
  try {
    await api.delete(`/api/v/client/pulsos/${pulso.value.id}`);
    toast.success('Pulso eliminado');
    emit('deleted');
  } catch {
    toast.error('No se pudo eliminar');
  } finally {
    deleting.value = false;
  }
}

function formatTimeLeft(expiresAt: string): string {
  const ms = new Date(expiresAt).getTime() - Date.now();
  if (ms <= 0) return 'Expirado';
  const h = Math.floor(ms / 3_600_000);
  const m = Math.floor((ms % 3_600_000) / 60_000);
  return h > 0 ? `${h}h ${m}m` : `${m}m`;
}
</script>

<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
    @click.self="emit('close')"
  >
    <div v-if="loading" class="flex items-center justify-center">
      <div class="h-10 w-10 animate-spin rounded-full border-2 border-wc-accent border-t-transparent"></div>
    </div>

    <div v-else-if="pulso" class="relative flex w-full max-w-sm flex-col overflow-hidden rounded-3xl bg-zinc-900">

      <!-- Barra de expiración -->
      <div class="h-1 w-full bg-zinc-800">
        <div
          class="h-full bg-wc-accent transition-all duration-1000"
          :style="{ width: expiryProgress + '%' }"
        ></div>
      </div>

      <!-- Header -->
      <div class="flex items-center justify-between px-4 py-3">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-full bg-wc-bg-secondary text-xs font-bold text-wc-text">
            {{ pulso.initials }}
          </div>
          <div>
            <p class="text-sm font-semibold text-wc-text">{{ pulso.client_name }}</p>
            <p class="text-[10px] text-wc-text-secondary">Expira en {{ formatTimeLeft(pulso.expires_at) }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button v-if="pulso.is_mine" @click="deletePulso" :disabled="deleting"
            class="rounded-lg p-1.5 text-zinc-500 hover:bg-zinc-800 hover:text-red-400 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </button>
          <button @click="emit('close')" class="rounded-lg p-1.5 text-zinc-500 hover:bg-zinc-800 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Contenido -->
      <div class="relative aspect-square w-full overflow-hidden bg-zinc-950">
        <video v-if="pulso.media_type === 'video' && pulso.media_url"
          :src="pulso.media_url" autoplay loop muted playsinline
          class="h-full w-full object-cover"/>
        <img v-else-if="pulso.media_type === 'photo' && pulso.media_url"
          :src="pulso.media_url" class="h-full w-full object-cover" :alt="pulso.caption ?? 'Pulso'"/>
        <PulsoStatCard
          v-else
          :pulso-type="pulso.pulso_type"
          :caption="pulso.caption ?? undefined"
          :stats="pulso.stats_overlay"
          :client-name="pulso.client_name"
        />
      </div>

      <!-- Caption extra (si tiene media Y caption) -->
      <p v-if="pulso.caption && pulso.media_type !== 'stat_card'"
        class="px-4 py-2 text-sm text-wc-text-secondary">
        {{ pulso.caption }}
      </p>

      <!-- Reacciones -->
      <div class="flex items-center justify-around border-t border-zinc-800 px-4 py-3">
        <button
          v-for="(emoji, type) in reactionEmojis" :key="type"
          @click="toggleReaction(type as string)"
          :class="[
            'flex flex-col items-center gap-0.5 rounded-xl px-3 py-1.5 transition-colors',
            pulso.my_reactions.includes(type as string)
              ? 'bg-wc-accent/20 text-wc-accent'
              : 'text-zinc-400 hover:bg-zinc-800',
          ]"
        >
          <span class="text-lg">{{ emoji }}</span>
          <span class="text-[10px] font-semibold">{{ pulso.reaction_counts[type as string] ?? 0 }}</span>
        </button>
      </div>

      <!-- Viewers (solo owner) -->
      <div v-if="pulso.is_mine" class="border-t border-zinc-800 px-4 py-3">
        <button @click="showViewers = !showViewers"
          class="flex w-full items-center justify-between text-sm text-zinc-400 hover:text-zinc-200 transition-colors">
          <span>👁 {{ pulso.views_count }} vieron tu Pulso</span>
          <svg :class="['h-4 w-4 transition-transform', showViewers ? 'rotate-180' : '']"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>
        <div v-if="showViewers && pulso.viewers?.length" class="mt-2 flex flex-wrap gap-2">
          <div v-for="v in pulso.viewers" :key="v.name + v.viewed_at"
            class="flex items-center gap-1.5 rounded-full bg-zinc-800 px-2 py-1">
            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-zinc-700 text-[9px] font-bold text-white">
              {{ v.initials }}
            </span>
            <span class="text-[11px] text-zinc-300">{{ v.name.split(' ')[0] }}</span>
          </div>
        </div>
        <p v-else-if="showViewers" class="mt-2 text-xs text-zinc-600">Nadie lo ha visto todavía.</p>
      </div>
    </div>
  </div>
</template>
