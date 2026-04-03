<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();

// State
const loading = ref(true);
const error = ref(null);
const challenges = ref([]);
const joiningId = ref(null);

// Stats
const joinedCount = ref(0);
const completedCount = ref(0);

// Fetch challenges
async function fetchChallenges() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/client/challenges');
        const d = response.data;
        challenges.value = d.challenges || [];
        joinedCount.value = d.joined_count || challenges.value.filter(c => c.is_joined).length;
        completedCount.value = d.completed_count || challenges.value.filter(c => c.is_completed).length;
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar los retos';
    } finally {
        loading.value = false;
    }
}

// Join challenge
async function joinChallenge(challengeId) {
    joiningId.value = challengeId;
    try {
        await api.post('/api/v/client/challenges/join', { challenge_id: challengeId });
        const challenge = challenges.value.find(c => c.id === challengeId);
        if (challenge) {
            challenge.is_joined = true;
            challenge.participants_count = (challenge.participants_count || 0) + 1;
            joinedCount.value++;
        }
    } catch {
        // Fail silently
    } finally {
        joiningId.value = null;
    }
}

onMounted(() => {
    fetchChallenges();
});

// Helpers
function daysLeft(endDate) {
    if (!endDate) return 0;
    const end = new Date(endDate);
    const now = new Date();
    return Math.max(0, Math.ceil((end - now) / (1000 * 60 * 60 * 24)));
}

function isExpired(endDate) {
    if (!endDate) return false;
    return new Date(endDate) < new Date();
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('es-CO', { day: 'numeric', month: 'short' });
}

function getTypeConfig(type) {
    const configs = {
        fuerza: { color: 'red', label: 'Fuerza' },
        cardio: { color: 'orange', label: 'Cardio' },
        nutricion: { color: 'green', label: 'Nutricion' },
        habitos: { color: 'blue', label: 'Habitos' },
    };
    return configs[type] || { color: 'red', label: 'General' };
}

function progressColor(pct) {
    if (pct >= 100) return 'bg-emerald-500';
    if (pct >= 50) return 'bg-wc-accent';
    return 'bg-wc-accent/70';
}
</script>

<template>
  <ClientLayout>
    <!-- Loading Skeleton -->
    <div v-if="loading" class="space-y-6">
      <div class="h-36 animate-pulse rounded-2xl border border-wc-border bg-wc-bg-tertiary"></div>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="i in 6" :key="i" class="h-64 animate-pulse rounded-2xl border border-wc-border bg-wc-bg-tertiary"></div>
      </div>
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
        @click="fetchChallenges"
        class="mt-6 rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg"
      >
        Reintentar
      </button>
    </div>

    <!-- Content -->
    <div v-else class="space-y-6">

      <!-- Header -->
      <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-gradient-to-br from-wc-bg-secondary via-wc-bg-tertiary to-wc-bg-secondary p-6">
        <div class="relative z-10">
          <p class="mb-1 text-xs font-semibold uppercase tracking-widest text-wc-accent">WellCore</p>
          <h1 class="font-display text-4xl tracking-wide text-wc-text">RETOS</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">Supera tus limites - Compite - Gana reconocimientos</p>

          <div class="mt-4 flex items-center gap-4">
            <div class="flex items-center gap-1.5">
              <div class="flex h-6 w-6 items-center justify-center rounded-full bg-wc-accent/20">
                <svg class="h-3.5 w-3.5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
              </div>
              <span class="text-sm text-wc-text-secondary">
                <span class="font-data font-bold text-wc-text">{{ joinedCount }}</span> activos
              </span>
            </div>
            <div v-if="completedCount > 0" class="flex items-center gap-1.5">
              <div class="flex h-6 w-6 items-center justify-center rounded-full bg-green-500/20">
                <svg class="h-3.5 w-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                </svg>
              </div>
              <span class="text-sm text-wc-text-secondary">
                <span class="font-data font-bold text-green-400">{{ completedCount }}</span> completados
              </span>
            </div>
          </div>
        </div>
        <div class="absolute -right-4 -top-4 h-32 w-32 text-wc-accent opacity-5">
          <svg viewBox="0 0 100 100" fill="currentColor" class="h-full w-full">
            <path d="M50 5 L60 35 L90 35 L67 54 L76 84 L50 65 L24 84 L33 54 L10 35 L40 35 Z"/>
          </svg>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="challenges.length === 0" class="rounded-2xl border border-dashed border-wc-border bg-wc-bg-tertiary/50 p-16 text-center">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-wc-accent/10">
          <svg class="h-10 w-10 text-wc-accent/50" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172" />
          </svg>
        </div>
        <h3 class="mt-5 font-display text-2xl tracking-wide text-wc-text">SIN RETOS ACTIVOS</h3>
        <p class="mx-auto mt-2 max-w-xs text-sm text-wc-text-secondary">No hay retos disponibles en este momento. Tu coach los activara pronto.</p>
      </div>

      <!-- Challenges Grid -->
      <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="challenge in challenges"
          :key="challenge.id"
          class="group relative flex flex-col overflow-hidden rounded-2xl border bg-wc-bg-tertiary transition-all duration-300 hover:shadow-lg hover:shadow-black/10"
          :class="challenge.is_completed
            ? 'border-green-500/40 hover:border-green-500/60'
            : challenge.is_joined
              ? 'border-wc-accent/30 hover:border-wc-accent/60'
              : 'border-wc-border hover:border-wc-border/80'"
        >
          <!-- Top accent strip -->
          <div
            class="h-1 w-full"
            :class="challenge.is_completed
              ? 'bg-gradient-to-r from-green-500 to-green-500/20'
              : challenge.is_joined
                ? 'bg-gradient-to-r from-wc-accent to-wc-accent/20'
                : 'bg-transparent'"
          ></div>

          <!-- Status badge -->
          <div v-if="challenge.is_completed" class="absolute right-3 top-4 z-10">
            <span class="flex items-center gap-1 rounded-full border border-green-500/30 bg-green-500/20 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-green-400">
              <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
              </svg>
              Completado
            </span>
          </div>
          <div v-else-if="challenge.is_joined" class="absolute right-3 top-4 z-10">
            <span class="flex items-center gap-1 rounded-full border border-wc-accent/20 bg-wc-accent/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-wc-accent">
              Participando
            </span>
          </div>

          <!-- Content -->
          <div class="flex flex-1 flex-col p-5 pt-3">
            <!-- Type badge -->
            <div class="mb-2">
              <span class="rounded bg-wc-bg-secondary px-2 py-0.5 text-xs font-semibold tracking-widest uppercase text-wc-text-secondary">
                {{ getTypeConfig(challenge.challenge_type).label }}
              </span>
            </div>

            <h3 class="text-base font-semibold text-wc-text">{{ challenge.title }}</h3>
            <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-wc-text-secondary">{{ challenge.description }}</p>

            <!-- Progress bar -->
            <div v-if="challenge.is_joined" class="mt-4">
              <div class="flex items-center justify-between text-sm">
                <span class="text-wc-text-tertiary">Progreso</span>
                <span class="font-data font-bold" :class="challenge.progress_pct >= 100 ? 'text-emerald-500' : 'text-wc-text'">
                  {{ challenge.progress_pct || 0 }}%
                </span>
              </div>
              <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                <div
                  class="h-full rounded-full transition-all duration-700"
                  :class="progressColor(challenge.progress_pct || 0)"
                  :style="{ width: `${Math.min(100, challenge.progress_pct || 0)}%` }"
                ></div>
              </div>
            </div>

            <div class="mt-auto pt-4">
              <!-- Dates & participants -->
              <div class="flex items-center justify-between text-sm text-wc-text-tertiary">
                <div class="flex items-center gap-1">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                  </svg>
                  <span>{{ formatDate(challenge.start_date) }} - {{ formatDate(challenge.end_date) }}</span>
                </div>
                <div class="flex items-center gap-1">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                  </svg>
                  <span>{{ challenge.participants_count || 0 }}</span>
                </div>
              </div>

              <!-- Days left -->
              <div v-if="!isExpired(challenge.end_date) && !challenge.is_completed" class="mt-2">
                <span :class="daysLeft(challenge.end_date) <= 3 ? 'text-sm font-medium text-wc-accent' : 'text-sm text-wc-text-tertiary'">
                  {{ daysLeft(challenge.end_date) }} dias restantes
                </span>
              </div>

              <!-- Join button -->
              <button
                v-if="!challenge.is_joined && !isExpired(challenge.end_date)"
                @click="joinChallenge(challenge.id)"
                :disabled="joiningId === challenge.id"
                class="mt-3 w-full rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent/90 disabled:opacity-50"
              >
                <span v-if="joiningId === challenge.id">Uniendose...</span>
                <span v-else>Unirme al reto</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </ClientLayout>
</template>
