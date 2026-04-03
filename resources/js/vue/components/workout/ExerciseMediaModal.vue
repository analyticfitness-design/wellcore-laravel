<script setup>
import { onMounted, onBeforeUnmount } from 'vue';
import { getEmbedUrl } from '../../composables/useExerciseMedia';

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

onMounted(() => {
  window.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeydown);
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
            <!-- YouTube iframe (priority over GIF) -->
            <div v-if="exVideoUrl(exercise)" class="aspect-video w-full">
              <iframe
                :src="getEmbedUrl(exVideoUrl(exercise))"
                class="h-full w-full"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
              ></iframe>
            </div>
            <!-- GIF fallback when no video -->
            <div v-else-if="exImageUrl(exercise)" class="flex items-center justify-center bg-wc-bg p-4">
              <img
                :src="exImageUrl(exercise)"
                :alt="exName(exercise)"
                class="max-h-80 w-full object-contain"
              />
            </div>
          </div>

          <!-- Footer hint -->
          <div class="px-4 py-3 text-center">
            <p class="text-xs text-wc-text-tertiary">Toca fuera para cerrar</p>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>
