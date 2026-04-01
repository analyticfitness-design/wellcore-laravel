<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import RiseLayout from '../../layouts/RiseLayout.vue';

const api = useApi();
const route = useRoute();
const router = useRouter();

const loading = ref(true);
const error = ref(null);
const savingFeedback = ref(false);
const feedbackSaved = ref(false);
const showConfetti = ref(true);

// Session data
const session = ref(null);
const stats = ref({});
const prs = ref([]);
const xpEarned = ref(0);
const sessionHistory = ref([]);

// Feedback
const feeling = ref(null);
const notes = ref('');

const feelings = [
    { value: 1, emoji: '\u{1F62B}', label: 'Muy dificil' },
    { value: 2, emoji: '\u{1F615}', label: 'Dificil' },
    { value: 3, emoji: '\u{1F610}', label: 'Normal' },
    { value: 4, emoji: '\u{1F60A}', label: 'Bien' },
    { value: 5, emoji: '\u{1F4AA}', label: 'Increible' },
];

const motivationalPhrase = computed(() => {
    const sets = stats.value.sets_completed || 0;
    if (sets >= 15) return 'Sesion increible! Asi se escribe el progreso.';
    if (sets >= 10) return 'Excelente trabajo! Asi se construyen los resultados.';
    if (sets >= 5) return 'Muy bien! La constancia hace la diferencia.';
    return 'Lo lograste! Cada sesion te acerca a tu meta.';
});

const heroEmoji = computed(() => {
    return (stats.value.sets_completed || 0) >= 10 ? '\u{1F3C6}' : '\u{26A1}';
});

async function fetchSummary() {
    loading.value = true;
    error.value = null;
    try {
        const { sessionId } = route.params;
        const response = await api.get(`/api/v/rise/workout-summary/${sessionId}`);
        session.value = response.data.session || {};
        stats.value = response.data.stats || {};
        prs.value = response.data.prs || [];
        xpEarned.value = response.data.xpEarned || 0;
        sessionHistory.value = response.data.sessionHistory || [];

        // Confetti for 4s
        setTimeout(() => { showConfetti.value = false; }, 4000);
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar resumen';
    } finally {
        loading.value = false;
    }
}

async function saveFeedback() {
    savingFeedback.value = true;
    try {
        await api.post(`/api/v/rise/workout-summary/${route.params.sessionId}`, {
            feeling: feeling.value,
            notes: notes.value,
        });
        feedbackSaved.value = true;
        setTimeout(() => { feedbackSaved.value = false; }, 3000);
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al guardar feedback';
    } finally {
        savingFeedback.value = false;
    }
}

onMounted(() => {
    fetchSummary();
});
</script>

<template>
  <RiseLayout>
    <!-- Loading -->
    <div v-if="loading" class="space-y-6">
      <div class="h-56 animate-pulse rounded-2xl bg-wc-bg-tertiary"></div>
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
        <div v-for="i in 6" :key="i" class="h-24 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="error && !session" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
      <p class="text-sm font-medium text-wc-text">{{ error }}</p>
      <RouterLink to="/v/rise" class="mt-4 inline-block rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
        Volver al Dashboard
      </RouterLink>
    </div>

    <div v-else class="space-y-8 pb-24 lg:pb-8">

      <!-- Confetti -->
      <Teleport to="body">
        <div v-if="showConfetti" class="pointer-events-none fixed inset-0 z-50 overflow-hidden" aria-hidden="true">
          <div v-for="i in 8" :key="i"
            class="absolute top-[-10px] h-[10px] w-[10px]"
            :style="{
              left: [8, 22, 38, 52, 65, 78, 90, 45][i-1] + '%',
              background: ['#DC2626', '#F59E0B', '#10B981', '#DC2626', '#8B5CF6', '#F59E0B', '#10B981', '#8B5CF6'][i-1],
              borderRadius: [1, 3, 5, 7].includes(i) ? '50%' : '0',
              animation: `confettiFall ${[2.8, 3.2, 2.5, 3, 2.7, 3.4, 2.6, 3.1][i-1]}s ease-in forwards ${[0.1, 0.3, 0, 0.5, 0.2, 0.4, 0.15, 0.6][i-1]}s`
            }"
          ></div>
        </div>
      </Teleport>

      <!-- Motivational Hero -->
      <div class="relative overflow-hidden rounded-2xl" style="min-height: 220px; background: linear-gradient(160deg, #0C1015 0%, #131F2B 50%, #0C1015 100%);">
        <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(ellipse at 50% -5%, rgba(255,255,255,0.08) 0%, transparent 60%);"></div>

        <div class="relative z-10 flex flex-col items-center justify-center px-6 py-10 text-center">
          <span class="text-6xl sm:text-7xl mb-4" aria-hidden="true" style="animation: heroTrophyBounce 2s ease-in-out infinite; display: inline-block;">{{ heroEmoji }}</span>

          <div class="flex items-center gap-2 mb-3">
            <span class="font-display text-2xl tracking-[0.25em] text-white/90 sm:text-3xl">WELLCORE RISE</span>
            <span class="inline-block h-2.5 w-2.5 rounded-full bg-white/30" aria-hidden="true"></span>
          </div>

          <p class="font-sans text-lg font-medium text-white/80 sm:text-xl max-w-sm">{{ motivationalPhrase }}</p>

          <div class="mt-4 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.07] px-4 py-1.5">
            <svg class="h-4 w-4 text-white/60" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
            </svg>
            <span class="text-xs font-semibold uppercase tracking-wider text-white/65">Sesion completada</span>
          </div>

          <p v-if="session?.day_name" class="mt-3 text-base font-medium text-white/60">{{ session.day_name }}</p>
          <p v-if="session?.session_date" class="mt-1 text-sm text-white/40">{{ session.session_date }}</p>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
          <p class="text-[11px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Duracion</p>
          <p class="mt-2 font-data text-3xl font-bold text-wc-text sm:text-4xl">{{ stats.duration || 0 }}</p>
          <p class="mt-0.5 text-xs text-wc-text-tertiary">min</p>
        </div>

        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
          <p class="text-[11px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Volumen Total</p>
          <p class="mt-2 font-data text-3xl font-bold text-wc-text sm:text-4xl">{{ (stats.volume || 0).toLocaleString() }}</p>
          <p class="mt-0.5 text-xs text-wc-text-tertiary">kg</p>
        </div>

        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
          <p class="text-[11px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Reps Totales</p>
          <p class="mt-2 font-data text-3xl font-bold text-wc-text sm:text-4xl">{{ (stats.reps || 0).toLocaleString() }}</p>
          <p class="mt-0.5 text-xs text-wc-text-tertiary">reps</p>
        </div>

        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
          <p class="text-[11px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Sets</p>
          <p class="mt-2 font-data text-3xl font-bold text-wc-text sm:text-4xl">
            {{ stats.sets_completed || 0 }}<span class="text-lg text-wc-text-tertiary">/{{ stats.sets_total || 0 }}</span>
          </p>
          <p class="mt-0.5 text-xs text-wc-text-tertiary">completados</p>
        </div>

        <div class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-4 text-center">
          <p class="text-[11px] font-semibold uppercase tracking-widest text-wc-accent">XP Ganados</p>
          <p class="mt-2 font-data text-3xl font-bold text-wc-accent sm:text-4xl">+{{ xpEarned }}</p>
          <p class="mt-0.5 text-xs text-wc-accent/70">XP</p>
        </div>

        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
          <p class="text-[11px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Ejercicios</p>
          <p class="mt-2 font-data text-3xl font-bold text-wc-text sm:text-4xl">{{ stats.exercises_count || 0 }}</p>
          <p class="mt-0.5 text-xs text-wc-text-tertiary">completados</p>
        </div>
      </div>

      <!-- PR Achievements -->
      <div v-if="prs.length > 0" class="relative overflow-hidden rounded-xl border border-amber-500/30 bg-gradient-to-br from-amber-500/10 via-yellow-500/5 to-amber-600/10 p-5">
        <div class="mb-4 flex items-center gap-3">
          <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/20">
            <svg class="h-7 w-7 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
              <path d="M5 3h14c.6 0 1 .4 1 1v2c0 2.8-2.2 5-5 5h-.2c-.5 1.5-1.5 2.7-2.8 3.4V18h3c.6 0 1 .4 1 1v2H7v-2c0-.6.4-1 1-1h3v-3.6c-1.3-.7-2.3-1.9-2.8-3.4H8c-2.8 0-5-2.2-5-5V4c0-.6.4-1 1-1Zm1 2v1c0 1.7 1.3 3 3 3h.1C9 8.4 9 7.7 9 7V5H6Zm12 0h-3v2c0 .7 0 1.4-.1 2H15c1.7 0 3-1.3 3-3V5Z"/>
            </svg>
          </div>
          <div>
            <p class="font-display text-lg tracking-wider text-amber-500">Nuevo record personal!</p>
            <p class="text-xs text-amber-500/70">{{ prs.length }} {{ prs.length === 1 ? 'record superado' : 'records superados' }} en esta sesion</p>
          </div>
        </div>

        <div class="space-y-2">
          <div v-for="(pr, idx) in prs" :key="idx" class="flex items-center gap-3 rounded-lg bg-amber-500/10 px-4 py-3">
            <svg class="h-5 w-5 shrink-0 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z" clip-rule="evenodd" />
            </svg>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-wc-text truncate">{{ pr.exercise }}</p>
            </div>
            <p class="font-data text-sm font-bold text-amber-500 whitespace-nowrap">
              {{ Number(pr.weight).toFixed(1) }} kg x {{ pr.reps }}
            </p>
          </div>
        </div>
      </div>

      <!-- Feeling Selector -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h3 class="mb-4 text-center font-display text-xl tracking-wide text-wc-text">Como te sentiste hoy?</h3>
        <div class="flex items-center justify-center gap-3 sm:gap-5">
          <button
            v-for="f in feelings" :key="f.value"
            @click="feeling = f.value"
            :class="[
              'flex flex-col items-center gap-1.5 rounded-xl px-3 py-3 transition-all duration-200',
              feeling === f.value ? 'scale-110 bg-wc-accent/10 ring-2 ring-wc-accent/50' : 'hover:bg-wc-bg-secondary hover:scale-105'
            ]"
            :title="f.label"
          >
            <span class="text-2xl sm:text-3xl" aria-hidden="true">{{ f.emoji }}</span>
            <span :class="['text-[10px] font-medium', feeling === f.value ? 'text-wc-accent' : 'text-wc-text-tertiary']">{{ f.label }}</span>
          </button>
        </div>
      </div>

      <!-- Session Notes -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <label for="session-notes" class="mb-2 block text-sm font-medium text-wc-text">
          Notas de la sesion <span class="text-wc-text-tertiary">(opcional)</span>
        </label>
        <textarea
          v-model="notes"
          id="session-notes"
          rows="3"
          maxlength="1000"
          placeholder="Como te sentiste? Notas de la sesion..."
          class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
        ></textarea>
        <p class="mt-1 text-right text-xs text-wc-text-tertiary">{{ notes.length }}/1000</p>
      </div>

      <!-- Save & Actions -->
      <div class="space-y-3">
        <!-- Success toast -->
        <Transition
          enter-active-class="transition ease-out duration-300"
          enter-from-class="opacity-0 -translate-y-2"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition ease-in duration-200"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div v-if="feedbackSaved" class="flex items-center gap-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20 px-4 py-3">
            <svg class="h-5 w-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm font-medium text-emerald-500">Listo! Tu sesion fue guardada.</span>
          </div>
        </Transition>

        <button
          @click="saveFeedback"
          :disabled="savingFeedback"
          class="flex w-full items-center justify-center gap-2 rounded-xl bg-wc-accent px-6 py-4 font-display text-lg tracking-wider text-white transition-colors hover:bg-wc-accent-hover disabled:opacity-50"
        >
          {{ savingFeedback ? 'Guardando...' : 'GUARDAR' }}
        </button>

        <div class="flex flex-col gap-3 sm:flex-row">
          <RouterLink to="/v/rise"
            class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-wc-border bg-wc-bg-tertiary px-5 py-3 text-sm font-medium text-wc-text-secondary transition-colors hover:text-wc-text">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
            </svg>
            Volver al Dashboard
          </RouterLink>
        </div>
      </div>

      <!-- Session History -->
      <div v-if="sessionHistory.length > 0">
        <div class="mb-6 h-px bg-wc-border"></div>
        <h3 class="mb-4 font-display text-xl tracking-wide text-wc-text">Historial reciente</h3>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="(past, idx) in sessionHistory.slice(0, 5)" :key="idx"
            class="flex items-center gap-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 transition-all hover:border-wc-accent/20">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary">
              <svg class="h-5 w-5 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-wc-text truncate">{{ past.day_name }}</p>
              <p class="text-xs text-wc-text-tertiary">{{ past.date }}</p>
            </div>
            <div class="text-right">
              <p class="font-data text-sm font-bold text-wc-text">{{ past.duration }}</p>
              <p class="text-xs text-wc-text-tertiary">{{ Number(past.volume).toLocaleString() }} kg</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </RiseLayout>
</template>

<style scoped>
@keyframes confettiFall {
    0%   { transform: translateY(-20px) rotate(0deg); opacity: 1; }
    100% { transform: translateY(110vh) rotate(720deg); opacity: 0; }
}
@keyframes heroTrophyBounce {
    0%, 100% { transform: scale(1) rotate(-3deg); }
    50%       { transform: scale(1.15) rotate(3deg); }
}
</style>
