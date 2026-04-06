<script setup>
import { ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { extractYouTubeId, getWatchUrl } from '../../composables/useExerciseMedia';

const props = defineProps({
  exercise: { type: Object, default: null },
  show: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const playingVideo = ref(false);
const playerReady = ref(false);
const playerError = ref(false);
let ytPlayer = null;

// Reset state when exercise or visibility changes
watch(() => props.exercise, () => { playingVideo.value = false; playerError.value = false; destroyPlayer(); });
watch(() => props.show, (v) => { if (!v) { playingVideo.value = false; destroyPlayer(); } });

function exName(ex) { return ex?.nombre || ex?.name || ex?.ejercicio || 'Ejercicio'; }
function exVideoUrl(ex) { return ex?.video_url || ex?.video || null; }
function exImageUrl(ex) { return ex?.image_url || ex?.gif_url || ex?.imagen || ex?.thumbnail_url || null; }
function hasYouTube(ex) { return !!extractYouTubeId(exVideoUrl(ex)); }

function onBackdropClick(e) { if (e.target === e.currentTarget) emit('close'); }
function onKeydown(e) { if (e.key === 'Escape') emit('close'); }

// Build YouTube embed src — uses standard embed URL
function ytEmbedSrc() {
  const videoId = extractYouTubeId(exVideoUrl(props.exercise));
  if (!videoId) return null;
  return `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0&modestbranding=1&playsinline=1`;
}

function startVideo() {
  if (!ytEmbedSrc()) return;
  playingVideo.value = true;
  playerError.value = false;
  playerReady.value = true;
}

function destroyPlayer() {
  if (ytPlayer) {
    try { ytPlayer.destroy(); } catch {}
    ytPlayer = null;
  }
  playerReady.value = false;
}

function stopAndShowGif() {
  playingVideo.value = false;
  destroyPlayer();
}

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => { window.removeEventListener('keydown', onKeydown); destroyPlayer(); });
</script>

<template>
  <Transition
    enter-active-class="transition ease-out duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition ease-in duration-150"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="show && exercise"
      class="fixed inset-0 z-[70] flex items-center justify-center bg-black/75 px-4 backdrop-blur-sm"
      @click="onBackdropClick"
      role="dialog"
      aria-modal="true"
    >
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          v-if="show && exercise"
          class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-wc-border bg-wc-bg shadow-2xl"
        >
          <!-- Header -->
          <div class="flex items-center justify-between border-b border-wc-border px-4 py-3">
            <h3 class="font-display text-lg tracking-wide text-wc-text uppercase truncate pr-4">
              {{ exName(exercise) }}
            </h3>
            <button
              @click="$emit('close')"
              class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text transition-colors"
              aria-label="Cerrar"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Media content -->
          <div class="bg-wc-bg-secondary">
            <!-- YouTube iframe (shown after clicking play) -->
            <div v-if="playingVideo && ytEmbedSrc() && !playerError" class="aspect-video w-full bg-black relative">
              <iframe
                :src="ytEmbedSrc()"
                class="absolute inset-0 h-full w-full"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
              ></iframe>
            </div>

            <!-- GIF with play overlay (default state + error fallback) -->
            <div v-else class="relative cursor-pointer group" @click="hasYouTube(exercise) ? startVideo() : null">
              <img
                v-if="exImageUrl(exercise)"
                :src="exImageUrl(exercise)"
                :alt="exName(exercise)"
                class="w-full object-contain max-h-80 bg-wc-bg"
              />
              <div v-else class="flex items-center justify-center h-48 bg-wc-bg">
                <p class="text-sm text-wc-text-tertiary">Sin imagen disponible</p>
              </div>

              <!-- Error message overlay -->
              <div v-if="playerError" class="absolute top-2 left-2 right-2 rounded-lg bg-black/80 px-3 py-2 text-center">
                <p class="text-xs text-wc-text-secondary">Video no disponible para embed. Toca YouTube para verlo.</p>
              </div>

              <!-- Play button overlay -->
              <div
                v-if="hasYouTube(exercise) && !playerError"
                class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/40 transition-colors"
              >
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-red-600 shadow-lg shadow-red-600/30 group-hover:scale-110 transition-transform">
                  <svg class="h-7 w-7 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-center gap-3 px-4 py-3">
            <p class="text-xs text-wc-text-tertiary">Toca fuera para cerrar</p>
            <button
              v-if="playingVideo"
              @click="stopAndShowGif"
              class="flex items-center gap-1.5 rounded-lg border border-wc-border px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:text-wc-text transition-colors"
            >
              Ver GIF
            </button>
            <a
              v-if="hasYouTube(exercise)"
              :href="getWatchUrl(exVideoUrl(exercise))"
              target="_blank"
              rel="noopener"
              class="flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700 transition-colors"
            >
              <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
              </svg>
              YouTube
            </a>
            <a
              v-else-if="exVideoUrl(exercise)"
              :href="exVideoUrl(exercise)"
              target="_blank"
              rel="noopener"
              class="flex items-center gap-1.5 rounded-lg bg-wc-accent px-3 py-1.5 text-xs font-semibold text-white hover:bg-wc-accent-hover transition-colors"
            >
              Ver video
            </a>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>
