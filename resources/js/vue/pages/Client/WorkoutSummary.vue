<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();
const route = useRoute();
const router = useRouter();

// State
const loading = ref(true);
const error = ref(null);
const saving = ref(false);
const feedbackSaved = ref(false);
const showConfetti = ref(true);

// Data
const session = ref(null);
const stats = ref({
  duration: 0,
  sets_completed: 0,
  sets_total: 0,
  reps: 0,
  max_weight: 0,
  max_weight_exercise: null,
  exercises_count: 0,
  volume: 0,
});
const xpEarned = ref(0);
const prs = ref([]);
const sessionHistory = ref([]);
const feeling = ref(null);
const notes = ref('');

// Feelings data
const feelings = [
  { value: 1, emoji: '\u{1F62B}', label: 'Muy dificil' },
  { value: 2, emoji: '\u{1F615}', label: 'Dificil' },
  { value: 3, emoji: '\u{1F610}', label: 'Normal' },
  { value: 4, emoji: '\u{1F60A}', label: 'Bien' },
  { value: 5, emoji: '\u{1F4AA}', label: 'Increible' },
];

// Motivational phrase
const motivationalPhrase = computed(() => {
  const sets = stats.value.sets_completed || 0;
  if (sets >= 15) return 'Sesion increible! Asi se escribe el progreso.';
  if (sets >= 10) return 'Excelente trabajo! Asi se construyen los resultados.';
  if (sets >= 5) return 'Muy bien! La constancia hace la diferencia.';
  return 'Lo lograste! Cada sesion te acerca a tu meta.';
});

// Volume chart data
const historyForChart = computed(() => {
  return [...sessionHistory.value].reverse().slice(0, 5);
});

const maxVolume = computed(() => {
  const volumes = historyForChart.value.map(h => h.volume || 0);
  volumes.push(stats.value.volume || 0);
  return Math.max(...volumes, 1);
});

function barHeight(volume) {
  const pct = (volume / maxVolume.value) * 100;
  return Math.max(pct, 8) + '%';
}

// Fetch
async function fetchSummary() {
  loading.value = true;
  error.value = null;
  try {
    const sid = route.params.sessionId;
    const response = await api.get(`/api/v/client/workout-summary/${sid}`);
    const d = response.data;
    session.value = d.session || null;
    stats.value = d.stats || stats.value;
    xpEarned.value = d.xpEarned || 0;
    prs.value = d.prs || [];
    sessionHistory.value = d.sessionHistory || [];
    feeling.value = d.feeling || null;
    notes.value = d.notes || '';
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar el resumen';
  } finally {
    loading.value = false;
  }
}

async function saveFeedback() {
  saving.value = true;
  try {
    const sid = route.params.sessionId;
    await api.post(`/api/v/client/workout-summary/${sid}`, {
      feeling: feeling.value,
      notes: notes.value,
    });
    feedbackSaved.value = true;
    setTimeout(() => { feedbackSaved.value = false; }, 3000);
  } catch (err) {
    // Silent
  } finally {
    saving.value = false;
  }
}

async function shareToCommunity() {
  // Navigate to community with share intent
  router.push({ name: 'client-community', query: { share: route.params.sessionId } });
}

onMounted(() => {
  fetchSummary();
  setTimeout(() => { showConfetti.value = false; }, 4000);
});
</script>

<template>
  <ClientLayout>
    <div class="space-y-8 pb-24 lg:pb-8">

      <!-- Loading -->
      <template v-if="loading">
        <div class="space-y-4 animate-pulse">
          <div class="h-56 rounded-2xl bg-wc-bg-tertiary"></div>
          <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            <div v-for="n in 6" :key="n" class="h-32 rounded-xl bg-wc-bg-tertiary"></div>
          </div>
          <div class="h-40 rounded-xl bg-wc-bg-tertiary"></div>
        </div>
      </template>

      <!-- Error -->
      <div v-else-if="error" class="rounded-xl border border-red-500/30 bg-red-500/10 p-6 text-center">
        <p class="text-sm text-red-400">{{ error }}</p>
        <button @click="fetchSummary" class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">Reintentar</button>
      </div>

      <template v-else>
        <!-- Confetti -->
        <Teleport to="body">
          <div v-if="showConfetti" class="pointer-events-none fixed inset-0 z-50 overflow-hidden" aria-hidden="true">
            <div v-for="(particle, i) in [
              { left: '8%', bg: '#DC2626', dur: '2.8s', delay: '0.1s' },
              { left: '22%', bg: '#F59E0B', dur: '3.2s', delay: '0.3s', round: true },
              { left: '38%', bg: '#10B981', dur: '2.5s', delay: '0s' },
              { left: '52%', bg: '#DC2626', dur: '3s', delay: '0.5s', round: true },
              { left: '65%', bg: '#8B5CF6', dur: '2.7s', delay: '0.2s' },
              { left: '78%', bg: '#F59E0B', dur: '3.4s', delay: '0.4s', round: true },
              { left: '90%', bg: '#10B981', dur: '2.6s', delay: '0.15s' },
              { left: '45%', bg: '#8B5CF6', dur: '3.1s', delay: '0.6s' },
            ]" :key="i"
              class="absolute top-[-10px] w-[10px] h-[10px]"
              :style="{
                left: particle.left,
                background: particle.bg,
                borderRadius: particle.round ? '50%' : '0',
                animation: `confettiFall ${particle.dur} ease-in forwards ${particle.delay}`,
              }"
            ></div>
          </div>
        </Teleport>

        <!-- Motivational Hero -->
        <div class="relative overflow-hidden rounded-2xl" style="min-height: 220px; background: linear-gradient(160deg, #0C1015 0%, #131F2B 50%, #0C1015 100%);">
          <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(ellipse at 50% -5%, rgba(255,255,255,0.08) 0%, transparent 60%);"></div>
          <div class="relative z-10 flex flex-col items-center justify-center px-6 py-10 text-center">
            <span class="text-6xl sm:text-7xl mb-4 inline-block animate-bounce" aria-hidden="true">{{ stats.sets_completed >= 10 ? '\u{1F3C6}' : '\u{26A1}' }}</span>
            <div class="flex items-center gap-2 mb-3">
              <span class="font-display text-2xl tracking-[0.25em] text-white/90 sm:text-3xl">WELLCORE</span>
              <span class="inline-block h-2.5 w-2.5 rounded-full bg-white/30" aria-hidden="true"></span>
            </div>
            <p class="font-sans text-lg font-medium text-white/80 sm:text-xl max-w-sm">{{ motivationalPhrase }}</p>
            <div class="mt-4 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.07] px-4 py-1.5">
              <svg class="h-4 w-4 text-white/60" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" /></svg>
              <span class="text-xs font-semibold uppercase tracking-wider text-white/65">Sesion completada</span>
            </div>
            <p v-if="session?.day_name" class="mt-3 text-base font-medium text-white/60">{{ session.day_name }}</p>
            <p v-if="session?.session_date" class="mt-1 text-sm text-white/40">{{ session.session_date }}</p>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
          <!-- Duracion -->
          <div class="relative overflow-hidden rounded-xl border border-emerald-500/20 bg-emerald-500/5 p-4 text-center">
            <div class="pointer-events-none absolute -right-3 -top-3 h-12 w-12 rounded-full bg-emerald-500/[0.08]"></div>
            <div class="mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/15">
              <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-400">Duracion</p>
            <p class="mt-1 font-data text-3xl font-bold text-wc-text sm:text-4xl">{{ stats.duration }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">min</p>
          </div>

          <!-- Peso Maximo -->
          <div class="relative overflow-hidden rounded-xl border border-amber-500/20 bg-amber-500/5 p-4 text-center">
            <div class="pointer-events-none absolute -right-3 -top-3 h-12 w-12 rounded-full bg-amber-500/[0.08]"></div>
            <div class="mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/15">
              <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-amber-400">Peso Maximo</p>
            <p class="mt-1 font-data text-3xl font-bold text-wc-text sm:text-4xl">{{ stats.max_weight > 0 ? stats.max_weight.toFixed(1) : '\u2014' }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">
              <template v-if="stats.max_weight_exercise">kg &middot; {{ stats.max_weight_exercise.substring(0, 20) }}</template>
              <template v-else>kg</template>
            </p>
          </div>

          <!-- Reps Totales -->
          <div class="relative overflow-hidden rounded-xl border border-blue-500/20 bg-blue-500/5 p-4 text-center">
            <div class="pointer-events-none absolute -right-3 -top-3 h-12 w-12 rounded-full bg-blue-500/[0.08]"></div>
            <div class="mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/15">
              <svg class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h5.25m5.25-.75L17.25 9m0 0L21 12.75M17.25 9v12" /></svg>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-blue-400">Reps Totales</p>
            <p class="mt-1 font-data text-3xl font-bold text-wc-text sm:text-4xl">{{ stats.reps.toLocaleString() }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">repeticiones</p>
          </div>

          <!-- Sets Completados -->
          <div class="relative overflow-hidden rounded-xl border border-violet-500/20 bg-violet-500/5 p-4 text-center">
            <div class="pointer-events-none absolute -right-3 -top-3 h-12 w-12 rounded-full bg-violet-500/[0.08]"></div>
            <div class="mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/15">
              <svg class="h-4 w-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-violet-400">Sets</p>
            <p class="mt-1 font-data text-3xl font-bold text-wc-text sm:text-4xl">{{ stats.sets_completed }}<span class="text-lg text-wc-text-tertiary">/{{ stats.sets_total }}</span></p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">completados</p>
          </div>

          <!-- XP Ganados -->
          <div class="relative overflow-hidden rounded-xl border border-wc-accent/25 bg-gradient-to-br from-wc-accent/10 to-transparent p-4 text-center">
            <div class="pointer-events-none absolute -right-3 -top-3 h-12 w-12 rounded-full bg-wc-accent/[0.08]"></div>
            <div class="mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-lg bg-wc-accent/15">
              <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" /></svg>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-wc-accent">XP Ganados</p>
            <p class="mt-1 font-data text-3xl font-bold text-wc-accent sm:text-4xl">+{{ xpEarned }}</p>
            <p class="mt-0.5 text-xs text-wc-accent/70">experiencia</p>
          </div>

          <!-- Ejercicios -->
          <div class="relative overflow-hidden rounded-xl border border-sky-500/20 bg-sky-500/5 p-4 text-center">
            <div class="pointer-events-none absolute -right-3 -top-3 h-12 w-12 rounded-full bg-sky-500/[0.08]"></div>
            <div class="mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/15">
              <svg class="h-4 w-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-4.5 0a1.5 1.5 0 0 0-1.5 1.5v3a1.5 1.5 0 0 0 1.5 1.5h1.5a1.5 1.5 0 0 0 1.5-1.5v-3m12-4.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5m3 0a1.5 1.5 0 0 1-1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3m0-4.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3" /></svg>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-sky-400">Ejercicios</p>
            <p class="mt-1 font-data text-3xl font-bold text-wc-text sm:text-4xl">{{ stats.exercises_count }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">completados</p>
          </div>
        </div>

        <!-- PR Achievements -->
        <div v-if="prs.length > 0" class="relative overflow-hidden rounded-xl border border-amber-500/30 bg-gradient-to-br from-amber-500/10 via-yellow-500/5 to-amber-600/10 p-5">
          <div class="mb-4 flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/20">
              <svg class="h-7 w-7 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M5 3h14c.6 0 1 .4 1 1v2c0 2.8-2.2 5-5 5h-.2c-.5 1.5-1.5 2.7-2.8 3.4V18h3c.6 0 1 .4 1 1v2H7v-2c0-.6.4-1 1-1h3v-3.6c-1.3-.7-2.3-1.9-2.8-3.4H8c-2.8 0-5-2.2-5-5V4c0-.6.4-1 1-1Zm1 2v1c0 1.7 1.3 3 3 3h.1C9 8.4 9 7.7 9 7V5H6Zm12 0h-3v2c0 .7 0 1.4-.1 2H15c1.7 0 3-1.3 3-3V5Z"/></svg>
            </div>
            <div>
              <p class="font-display text-lg tracking-wider text-amber-500">Nuevo record personal!</p>
              <p class="text-xs text-amber-500/70">{{ prs.length }} {{ prs.length === 1 ? 'record superado' : 'records superados' }} en esta sesion</p>
            </div>
          </div>
          <div class="space-y-2">
            <div v-for="(pr, pIdx) in prs" :key="pIdx" class="flex items-center gap-3 rounded-lg bg-amber-500/10 px-4 py-3">
              <svg class="h-5 w-5 shrink-0 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z" clip-rule="evenodd" /></svg>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-wc-text truncate">{{ pr.exercise }}</p>
              </div>
              <p class="font-data text-sm font-bold text-amber-500 whitespace-nowrap">{{ pr.weight.toFixed(1) }} kg &times; {{ pr.reps }}</p>
            </div>
          </div>
        </div>

        <!-- Feeling Selector -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h3 class="mb-4 text-center font-display text-xl tracking-wide text-wc-text">Como te sentiste hoy?</h3>
          <div class="flex items-center justify-center gap-3 sm:gap-5">
            <button
              v-for="f in feelings"
              :key="f.value"
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
            <div v-if="feedbackSaved" class="flex items-center gap-2 rounded-lg bg-green-500/10 border border-green-500/20 px-4 py-3">
              <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" /></svg>
              <span class="text-sm font-medium text-green-500">Listo! Tu sesion fue guardada.</span>
            </div>
          </Transition>

          <!-- Save Button -->
          <button
            @click="saveFeedback"
            :disabled="saving"
            class="flex w-full items-center justify-center gap-2 rounded-xl bg-wc-accent px-6 py-4 font-display text-lg tracking-wider text-white transition-colors hover:bg-red-700 disabled:opacity-50"
          >
            <template v-if="!saving">GUARDAR</template>
            <template v-else>
              <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
              Guardando...
            </template>
          </button>

          <!-- Secondary Actions -->
          <div class="flex flex-col gap-3 sm:flex-row">
            <button
              @click="shareToCommunity"
              class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-wc-border bg-wc-bg-tertiary px-5 py-3 text-sm font-medium text-wc-text transition-colors hover:border-wc-accent/30 hover:text-wc-accent"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" /></svg>
              Compartir en Comunidad
            </button>
            <RouterLink
              to="/client"
              class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-wc-border bg-wc-bg-tertiary px-5 py-3 text-sm font-medium text-wc-text-secondary transition-colors hover:text-wc-text"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
              Volver al Dashboard
            </RouterLink>
          </div>
        </div>

        <!-- Session History -->
        <template v-if="sessionHistory.length > 0">
          <div class="border-t border-wc-border pt-6">
            <h3 class="mb-4 font-display text-xl tracking-wide text-wc-text">Historial reciente</h3>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
              <div v-for="(past, pIdx) in sessionHistory.slice(0, 5)" :key="pIdx" class="flex items-center gap-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 transition-all hover:border-wc-accent/20">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary">
                  <svg class="h-5 w-5 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-wc-text truncate">{{ past.day_name }}</p>
                  <p class="text-xs text-wc-text-tertiary">{{ past.date }}</p>
                </div>
                <div class="text-right">
                  <p class="font-data text-sm font-bold text-wc-text">{{ past.duration }}</p>
                  <p class="text-xs text-wc-text-tertiary">{{ (past.volume || 0).toLocaleString() }} kg</p>
                </div>
              </div>
            </div>

            <!-- Volume Trend -->
            <div v-if="historyForChart.length >= 2" class="mt-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
              <p class="mb-4 text-sm font-semibold text-wc-text">Tendencia de volumen</p>
              <div class="flex items-end gap-2 sm:gap-3" style="height: 120px;">
                <div v-for="(entry, eIdx) in historyForChart" :key="eIdx" class="flex flex-1 flex-col items-center gap-1">
                  <span class="font-data text-[10px] font-bold text-wc-text-tertiary">{{ (entry.volume || 0).toLocaleString() }}</span>
                  <div class="w-full rounded-t-md bg-wc-accent/70 transition-all duration-700" :style="{ height: barHeight(entry.volume || 0) }"></div>
                  <span class="text-[10px] text-wc-text-tertiary truncate max-w-full">{{ entry.date }}</span>
                </div>
                <!-- Current session bar -->
                <div class="flex flex-1 flex-col items-center gap-1">
                  <span class="font-data text-[10px] font-bold text-wc-accent">{{ (stats.volume || 0).toLocaleString() }}</span>
                  <div class="w-full rounded-t-md bg-wc-accent transition-all duration-700" :style="{ height: barHeight(stats.volume || 0) }"></div>
                  <span class="text-[10px] font-semibold text-wc-accent">Hoy</span>
                </div>
              </div>
            </div>

            <!-- Link to training view -->
            <div class="mt-4 text-center">
              <RouterLink to="/client/training" class="inline-flex items-center gap-1.5 text-sm font-medium text-wc-accent hover:underline">
                Ver historial completo
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
              </RouterLink>
            </div>
          </div>
        </template>
      </template>
    </div>
  </ClientLayout>
</template>

<style scoped>
@keyframes confettiFall {
  0%   { transform: translateY(-20px) rotate(0deg); opacity: 1; }
  100% { transform: translateY(110vh) rotate(720deg); opacity: 0; }
}
</style>
