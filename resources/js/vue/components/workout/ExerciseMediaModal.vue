<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { getEmbedUrl, getWatchUrl } from '../../composables/useExerciseMedia';

const props = defineProps({
  exercise: {
    type: Object,
    default: null,
  },
  show: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close']);

const videoBlocked = ref(false);

// Reset blocked state when a new exercise is shown
watch(() => props.exercise, () => { videoBlocked.value = false; });

function exName(ex) {
  return ex?.nombre || ex?.name || ex?.ejercicio || 'Ejercicio';
}

function exVideoUrl(ex) {
  return ex?.video_url || ex?.video || null;
}

function exImageUrl(ex) {
  return ex?.image_url || ex?.gif_url || ex?.imagen || ex?.thumbnail_url || null;
}

function onBackdropClick(e) {
  if (e.target === e.currentTarget) {
    emit('close');
  }
}

function onKeydown(e) {
  if (e.key === 'Escape') {
    emit('close');
  }
}

// YouTube sends a postMessage when embedding is blocked
function onMessage(e) {
  if (typeof e.data === 'string' && e.data.includes('embeddingDisabled')) {
    videoBlocked.value = true;
  }
}

onMounted(() => {
  window.addEventListener('keydown', onKeydown);
  window.addEventListener('message', onMessage);
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeydown);
  window.removeEventListener('message', onMessage);
});
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
            <!-- YouTube iframe — only when URL is a valid YouTube URL and not blocked -->
            <div v-if="getEmbedUrl(exVideoUrl(exercise)) && !videoBlocked" class="aspect-video w-full">
              <iframe
                :src="getEmbedUrl(exVideoUrl(exercise))"
                class="h-full w-full"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
              ></iframe>
            </div>
            <!-- GIF shown when no YouTube embed available OR video is blocked -->
            <div v-if="!getEmbedUrl(exVideoUrl(exercise)) || videoBlocked" class="flex items-center justify-center bg-wc-bg p-4">
              <img
                v-if="exImageUrl(exercise)"
                :src="exImageUrl(exercise)"
                :alt="exName(exercise)"
                class="max-h-80 w-full object-contain"
              />
              <p v-else class="text-sm text-wc-text-tertiary">Sin imagen disponible</p>
            </div>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-center gap-3 px-4 py-3">
            <p class="text-xs text-wc-text-tertiary">Toca fuera para cerrar</p>
            <!-- YouTube link when it's a valid YouTube URL -->
            <a
              v-if="getWatchUrl(exVideoUrl(exercise))"
              :href="getWatchUrl(exVideoUrl(exercise))"
              target="_blank"
              rel="noopener"
              class="flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700 transition-colors"
            >
              <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
              </svg>
              Ver en YouTube
            </a>
            <!-- Fitcron/external video link when URL exists but is NOT YouTube -->
            <a
              v-else-if="exVideoUrl(exercise)"
              :href="exVideoUrl(exercise)"
              target="_blank"
              rel="noopener"
              class="flex items-center gap-1.5 rounded-lg bg-wc-accent px-3 py-1.5 text-xs font-semibold text-white hover:bg-wc-accent-hover transition-colors"
            >
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/>
              </svg>
              Ver video
            </a>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>
