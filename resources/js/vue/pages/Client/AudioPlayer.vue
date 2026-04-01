<template>
  <div class="space-y-6">
    <div>
      <h1 class="font-display text-3xl tracking-wide text-wc-text">AUDIO COACHING</h1>
      <p class="mt-1 text-sm text-wc-text-secondary">Sesiones de coaching en audio: motivacion, tecnica y mindset para tu entrenamiento.</p>
    </div>

    <!-- Category tabs -->
    <div class="flex flex-wrap gap-2">
      <button
        @click="categoryFilter = 'all'"
        :class="categoryFilter === 'all' ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'"
        class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium transition-colors"
      >
        Todos <span class="ml-1 text-xs opacity-70">({{ AUDIOS.length }})</span>
      </button>
      <button
        v-for="c in CATEGORIES"
        :key="c.id"
        @click="categoryFilter = c.id"
        :class="categoryFilter === c.id ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'"
        class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium transition-colors"
      >
        {{ c.icon }} {{ c.label }}
      </button>
    </div>

    <!-- Results count -->
    <p class="text-xs text-wc-text-tertiary">
      {{ filteredAudios.length }} sesion{{ filteredAudios.length !== 1 ? 'es' : '' }} disponible{{ filteredAudios.length !== 1 ? 's' : '' }}
    </p>

    <!-- Audio list -->
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="track in filteredAudios"
        :key="track.id"
        @click="playTrack(track)"
        :class="currentTrack?.id === track.id ? 'border-wc-accent/60 bg-wc-accent/5' : ''"
        class="group cursor-pointer rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 transition-all hover:border-wc-accent/40"
      >
        <div class="flex items-start gap-3">
          <!-- Play indicator -->
          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg transition-colors"
            :class="currentTrack?.id === track.id && isPlaying
              ? 'bg-wc-accent text-white'
              : 'bg-wc-bg-secondary text-wc-text-tertiary group-hover:bg-wc-accent/20 group-hover:text-wc-accent'"
          >
            <svg v-if="!(currentTrack?.id === track.id && isPlaying)" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M8 5.14v14l11-7-11-7z"/>
            </svg>
            <svg v-else class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
            </svg>
          </div>

          <div class="min-w-0 flex-1">
            <h3 class="text-sm font-semibold text-wc-text">{{ track.title }}</h3>
            <p class="mt-0.5 line-clamp-2 text-xs text-wc-text-tertiary">{{ track.description }}</p>
            <div class="mt-2 flex items-center gap-3">
              <span
                class="rounded-full px-2 py-0.5 text-[10px] font-medium"
                :class="{
                  'bg-orange-500/10 text-orange-400': track.category === 'motivacion',
                  'bg-blue-500/10 text-blue-400': track.category === 'tecnica',
                  'bg-purple-500/10 text-purple-400': track.category === 'mindset'
                }"
              >{{ CATEGORIES.find(c => c.id === track.category)?.label }}</span>
              <span class="flex items-center gap-1 text-[10px] text-wc-text-tertiary">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                {{ formatTime(track.duration) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Mini progress bar for current track -->
        <div v-if="currentTrack?.id === track.id" class="mt-3">
          <div class="h-1 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
            <div class="h-full rounded-full bg-wc-accent transition-all duration-200" :style="{ width: progress + '%' }"></div>
          </div>
          <div class="mt-1 flex justify-between text-[10px] text-wc-text-tertiary">
            <span>{{ formatTime(currentTime) }}</span>
            <span>{{ formatTime(track.duration) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Persistent Player Bar -->
    <Transition name="slide-up">
      <div
        v-if="currentTrack"
        class="fixed inset-x-0 bottom-0 z-50 border-t border-wc-border bg-wc-bg-secondary/95 backdrop-blur-xl"
        :class="isMobile ? 'bottom-14' : 'bottom-0'"
      >
        <div class="mx-auto max-w-7xl px-4 py-3 sm:px-6">
          <!-- Progress bar -->
          <div @click="seek" class="group mb-2 cursor-pointer">
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-wc-border transition-all group-hover:h-2">
              <div class="h-full rounded-full bg-wc-accent transition-all duration-200" :style="{ width: progress + '%' }"></div>
            </div>
          </div>

          <div class="flex items-center justify-between gap-4">
            <!-- Track info -->
            <div class="flex min-w-0 flex-1 items-center gap-3">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-wc-accent/20">
                <span class="text-lg">{{ currentTrack.icon }}</span>
              </div>
              <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-wc-text">{{ currentTrack.title }}</p>
                <p class="text-xs text-wc-text-tertiary">{{ formatTime(currentTime) }} / {{ formatTime(currentTrack.duration) }}</p>
              </div>
            </div>

            <!-- Controls -->
            <div class="flex items-center gap-2">
              <button @click="prevTrack" class="flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-secondary transition-colors hover:text-wc-text">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 6h2v12H6zm3.5 6 8.5 6V6z"/></svg>
              </button>
              <button @click="togglePlay" class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent text-white transition-colors hover:bg-red-600">
                <svg v-if="!isPlaying" class="ml-0.5 h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5.14v14l11-7-11-7z"/></svg>
                <svg v-else class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
              </button>
              <button @click="nextTrack" class="flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-secondary transition-colors hover:text-wc-text">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/></svg>
              </button>
            </div>

            <!-- Volume (desktop) -->
            <div class="hidden items-center gap-2 sm:flex">
              <button @click="toggleMute" class="text-wc-text-secondary transition-colors hover:text-wc-text">
                <svg v-if="volume > 0" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75 19.5 12m0 0 2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6 4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />
                </svg>
              </button>
              <input
                type="range" min="0" max="1" step="0.05"
                v-model.number="volume"
                @input="updateVolume"
                class="h-1 w-20 cursor-pointer appearance-none rounded-full bg-wc-border accent-wc-accent"
              />
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Spacer when player bar is visible -->
    <div v-if="currentTrack" class="h-20"></div>
  </div>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';

const CATEGORIES = [
  { id: 'motivacion', label: 'Motivacion', icon: '🔥' },
  { id: 'tecnica', label: 'Tecnica', icon: '🎯' },
  { id: 'mindset', label: 'Mindset', icon: '🧠' },
];

const AUDIOS = [
  { id: 1, title: 'Poder Interior', description: 'Visualizacion de fuerza y determinacion para despertar tu potencial fisico y mental.', category: 'motivacion', icon: '💪', duration: 180, frequency: 440, waveform: 'sine' },
  { id: 2, title: 'Rompe Limites', description: 'Afirmaciones para superar barreras mentales que frenan tu progreso en el gym.', category: 'motivacion', icon: '⚡', duration: 240, frequency: 528, waveform: 'sine' },
  { id: 3, title: 'Mentalidad de Campeon', description: 'Coaching mental para competidores y atletas que buscan la excelencia.', category: 'motivacion', icon: '🏆', duration: 300, frequency: 396, waveform: 'sine' },
  { id: 4, title: 'Tu Mejor Version', description: 'Reflexion guiada sobre tu progreso personal y metas alcanzadas.', category: 'motivacion', icon: '🌟', duration: 210, frequency: 417, waveform: 'sine' },
  { id: 5, title: 'Accion Imparable', description: 'Energia y motivacion pura para entrenamientos de alta intensidad.', category: 'motivacion', icon: '🚀', duration: 180, frequency: 639, waveform: 'triangle' },
  { id: 6, title: 'Activacion de Gluteos', description: 'Tecnica de activacion muscular previa al entrenamiento de pierna y gluteo.', category: 'tecnica', icon: '🎯', duration: 150, frequency: 285, waveform: 'sine' },
  { id: 7, title: 'Respiracion en Press', description: 'Patron respiratorio optimo para movimientos de press y empuje.', category: 'tecnica', icon: '🫁', duration: 180, frequency: 432, waveform: 'sine' },
  { id: 8, title: 'Control del Core', description: 'Bracing y activacion abdominal para estabilidad en compuestos.', category: 'tecnica', icon: '🧱', duration: 210, frequency: 369, waveform: 'sine' },
  { id: 9, title: 'Ritmo de Sentadilla', description: 'Tempo y cadencia optima para maximizar la hipertrofia en sentadilla.', category: 'tecnica', icon: '🏋️', duration: 180, frequency: 396, waveform: 'triangle' },
  { id: 10, title: 'Conexion Mente-Musculo', description: 'Practica de conexion neuromuscular para aislamientos y accesorios.', category: 'tecnica', icon: '🧬', duration: 240, frequency: 528, waveform: 'sine' },
  { id: 11, title: 'Meditacion Pre-Entreno', description: '5 minutos de meditacion enfocada para preparar mente y cuerpo antes del gym.', category: 'mindset', icon: '🧘', duration: 300, frequency: 174, waveform: 'sine' },
  { id: 12, title: 'Gestion del Esfuerzo', description: 'Aprender a distinguir entre esfuerzo productivo e innecesario durante el entrenamiento.', category: 'mindset', icon: '⚖️', duration: 210, frequency: 285, waveform: 'sine' },
  { id: 13, title: 'Paciencia y Proceso', description: 'Aceptar el proceso del fitness y confiar en la consistencia a largo plazo.', category: 'mindset', icon: '🌱', duration: 240, frequency: 396, waveform: 'sine' },
  { id: 14, title: 'Descanso Consciente', description: 'Meditacion de recuperacion post-entrenamiento para optimizar la restauracion.', category: 'mindset', icon: '😌', duration: 300, frequency: 432, waveform: 'sine' },
  { id: 15, title: 'Visualizacion de Metas', description: 'Sesion de visualizacion para conectar con tu cuerpo y rendimiento ideal.', category: 'mindset', icon: '🎯', duration: 240, frequency: 528, waveform: 'sine' },
];

const categoryFilter = ref('all');
const currentTrack = ref(null);
const isPlaying = ref(false);
const currentTime = ref(0);
const progress = ref(0);
const volume = ref(0.7);
const isMobile = ref(window.innerWidth < 1024);

let audioContext = null;
let oscillator = null;
let gainNode = null;
let timer = null;

const filteredAudios = computed(() => {
  if (categoryFilter.value === 'all') return AUDIOS;
  return AUDIOS.filter(a => a.category === categoryFilter.value);
});

function playTrack(track) {
  if (currentTrack.value?.id === track.id) {
    togglePlay();
    return;
  }
  stopAudio();
  currentTrack.value = track;
  currentTime.value = 0;
  progress.value = 0;
  startAudio();
}

function togglePlay() {
  if (!currentTrack.value) return;
  isPlaying.value ? pauseAudio() : startAudio();
}

function startAudio() {
  if (!currentTrack.value) return;
  try {
    if (!audioContext) {
      audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }
    if (audioContext.state === 'suspended') audioContext.resume();

    oscillator = audioContext.createOscillator();
    gainNode = audioContext.createGain();
    oscillator.type = currentTrack.value.waveform || 'sine';
    oscillator.frequency.setValueAtTime(currentTrack.value.frequency, audioContext.currentTime);
    gainNode.gain.setValueAtTime(volume.value * 0.15, audioContext.currentTime);
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    oscillator.start();
  } catch (e) {
    console.warn('Web Audio API not available:', e);
  }
  isPlaying.value = true;
  timer = setInterval(() => {
    currentTime.value += 0.1;
    progress.value = (currentTime.value / currentTrack.value.duration) * 100;
    if (currentTime.value >= currentTrack.value.duration) {
      nextTrack();
    }
  }, 100);
}

function pauseAudio() {
  isPlaying.value = false;
  clearInterval(timer);
  timer = null;
  try {
    if (oscillator) { oscillator.stop(); oscillator.disconnect(); oscillator = null; }
  } catch (e) {}
}

function stopAudio() {
  pauseAudio();
  currentTime.value = 0;
  progress.value = 0;
}

function nextTrack() {
  if (!currentTrack.value) return;
  const list = filteredAudios.value;
  const idx = list.findIndex(a => a.id === currentTrack.value.id);
  const next = list[(idx + 1) % list.length];
  stopAudio();
  currentTrack.value = next;
  startAudio();
}

function prevTrack() {
  if (!currentTrack.value) return;
  if (currentTime.value > 3) {
    const t = currentTrack.value;
    stopAudio();
    currentTrack.value = t;
    startAudio();
    return;
  }
  const list = filteredAudios.value;
  const idx = list.findIndex(a => a.id === currentTrack.value.id);
  const prev = list[(idx - 1 + list.length) % list.length];
  stopAudio();
  currentTrack.value = prev;
  startAudio();
}

function seek(event) {
  if (!currentTrack.value) return;
  const rect = event.currentTarget.getBoundingClientRect();
  const pct = Math.max(0, Math.min(1, (event.clientX - rect.left) / rect.width));
  currentTime.value = pct * currentTrack.value.duration;
  progress.value = pct * 100;
}

function toggleMute() {
  volume.value = volume.value > 0 ? 0 : 0.7;
  updateVolume();
}

function updateVolume() {
  try {
    if (gainNode) gainNode.gain.setValueAtTime(volume.value * 0.15, audioContext.currentTime);
  } catch (e) {}
}

function formatTime(seconds) {
  const s = Math.floor(seconds);
  const m = Math.floor(s / 60);
  const sec = s % 60;
  return m + ':' + (sec < 10 ? '0' : '') + sec;
}

onBeforeUnmount(() => {
  stopAudio();
  if (audioContext) { audioContext.close(); audioContext = null; }
});
</script>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.25s ease, opacity 0.25s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}
</style>
