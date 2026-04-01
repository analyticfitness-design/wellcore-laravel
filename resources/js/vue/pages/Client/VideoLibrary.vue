<template>
  <div class="space-y-6">
    <div>
      <h1 class="font-display text-3xl tracking-wide text-wc-text">VIDEOS</h1>
      <p class="mt-1 text-sm text-wc-text-secondary">Tips y demostraciones en video de tu coach.</p>
    </div>

    <!-- Search -->
    <div class="relative sm:max-w-sm">
      <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
      </svg>
      <input
        v-model="search"
        type="text"
        placeholder="Buscar video..."
        class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-10 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
      />
      <div v-if="loading" class="absolute right-3 top-1/2 -translate-y-1/2">
        <svg class="h-4 w-4 animate-spin text-wc-accent" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
      </div>
    </div>

    <!-- Inline player -->
    <Transition name="fade">
      <div v-if="playingVideo" id="video-player" class="rounded-xl border border-wc-accent/30 bg-wc-bg-secondary">
        <div class="flex items-center justify-between border-b border-wc-border px-5 py-4">
          <div>
            <h2 class="font-display text-xl tracking-wide text-wc-text">{{ playingVideo?.title }}</h2>
            <p v-if="playingVideo?.duration_sec" class="mt-0.5 flex items-center gap-1.5 text-xs text-wc-text-tertiary">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
              {{ formatDuration(playingVideo.duration_sec) }}
            </p>
          </div>
          <button @click="playingVideo = null" class="flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="p-5">
          <template v-if="youtubeEmbedUrl(playingVideo.video_url, true)">
            <div class="aspect-video overflow-hidden rounded-xl">
              <iframe
                :src="youtubeEmbedUrl(playingVideo.video_url, true)"
                :title="playingVideo.title"
                class="h-full w-full"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
              ></iframe>
            </div>
          </template>
          <template v-else-if="playingVideo.video_url">
            <div class="aspect-video overflow-hidden rounded-xl bg-wc-bg">
              <video :src="playingVideo.video_url" class="h-full w-full rounded-xl" controls autoplay preload="auto"></video>
            </div>
          </template>
          <template v-else>
            <div class="flex aspect-video items-center justify-center rounded-xl bg-wc-bg">
              <p class="text-sm text-wc-text-tertiary">Video no disponible.</p>
            </div>
          </template>
        </div>
      </div>
    </Transition>

    <!-- Loading skeleton -->
    <template v-if="loading && videos.length === 0">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="n in 6" :key="n" class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary">
          <div class="aspect-video rounded-t-xl bg-wc-bg-secondary"></div>
          <div class="p-4 space-y-2">
            <div class="h-4 w-3/4 rounded bg-wc-bg-secondary"></div>
            <div class="h-3 w-1/2 rounded bg-wc-bg-secondary"></div>
          </div>
        </div>
      </div>
    </template>

    <!-- Empty state -->
    <div v-else-if="!loading && videos.length === 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
      <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
      </svg>
      <p class="mt-3 text-sm font-medium text-wc-text-secondary">
        {{ search ? `Sin videos que coincidan con "${search}".` : 'Tu coach no ha publicado videos aun.' }}
      </p>
      <button v-if="search" @click="search = ''" class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-xs font-semibold text-white hover:bg-wc-accent/90">
        Limpiar busqueda
      </button>
    </div>

    <!-- Video grid -->
    <template v-else-if="videos.length > 0">
      <p class="text-xs text-wc-text-tertiary">
        {{ videos.length }} {{ videos.length === 1 ? 'video' : 'videos' }}
        <span v-if="search"> que coinciden con "<span class="text-wc-accent">{{ search }}</span>"</span>
      </p>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <button
          v-for="video in videos"
          :key="video.id"
          @click="togglePlay(video)"
          :class="playingVideo?.id === video.id ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40'"
          class="group cursor-pointer rounded-xl border text-left transition-all focus:outline-none focus:ring-2 focus:ring-wc-accent"
        >
          <!-- Thumbnail -->
          <div class="relative aspect-video overflow-hidden rounded-t-xl bg-wc-bg-secondary">
            <img
              v-if="video.thumbnail_url"
              :src="video.thumbnail_url"
              :alt="video.title"
              class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
              loading="lazy"
            />
            <div v-else class="flex h-full items-center justify-center">
              <svg class="h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
              </svg>
            </div>

            <!-- Play/Pause overlay -->
            <div
              class="absolute inset-0 flex items-center justify-center bg-black/30"
              :class="playingVideo?.id === video.id ? 'opacity-100' : 'opacity-0 transition-opacity group-hover:opacity-100'"
            >
              <div class="flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent shadow-lg">
                <svg v-if="playingVideo?.id === video.id" class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
                </svg>
                <svg v-else class="ml-0.5 h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M8 5v14l11-7z"/>
                </svg>
              </div>
            </div>

            <!-- Duration badge -->
            <span v-if="video.duration_sec" class="absolute bottom-2 right-2 rounded-md bg-black/70 px-1.5 py-0.5 font-data text-[11px] font-medium text-white">
              {{ formatDuration(video.duration_sec) }}
            </span>

            <!-- Playing badge -->
            <span v-if="playingVideo?.id === video.id" class="absolute left-2 top-2 flex items-center gap-1 rounded-full bg-wc-accent px-2 py-0.5 text-[10px] font-semibold text-white">
              <span class="relative flex h-1.5 w-1.5">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-75"></span>
                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-white"></span>
              </span>
              REPRODUCIENDO
            </span>
          </div>

          <!-- Info -->
          <div class="p-4">
            <h3 class="text-sm font-semibold leading-snug text-wc-text">{{ video.title }}</h3>
            <p v-if="video.duration_sec" class="mt-1 flex items-center gap-1 text-xs text-wc-text-tertiary">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
              {{ formatDuration(video.duration_sec) }}
            </p>
          </div>
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { useApi } from '../../composables/useApi';

const api = useApi();
const videos = ref([]);
const playingVideo = ref(null);
const search = ref('');
const loading = ref(false);

function youtubeEmbedUrl(url, autoplay = false) {
  if (!url) return null;
  const isYt = url.includes('youtube.com') || url.includes('youtu.be');
  if (!isYt) return null;
  let ytId = null;
  if (url.includes('youtu.be/')) {
    ytId = url.split('youtu.be/')[1]?.split('?')[0];
  } else if (url.includes('/embed/')) {
    ytId = url.split('/embed/')[1]?.split('?')[0];
  } else {
    const m = url.match(/[?&]v=([^&]+)/);
    ytId = m ? m[1] : null;
  }
  if (!ytId) return null;
  return `https://www.youtube.com/embed/${ytId}?rel=0&modestbranding=1${autoplay ? '&autoplay=1' : ''}`;
}

function formatDuration(secs) {
  const m = Math.floor(secs / 60);
  const s = secs % 60;
  return `${m}:${String(s).padStart(2, '0')}`;
}

function togglePlay(video) {
  playingVideo.value = playingVideo.value?.id === video.id ? null : video;
  if (playingVideo.value) {
    setTimeout(() => {
      document.getElementById('video-player')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
  }
}

let debounceTimer = null;
async function fetchVideos() {
  loading.value = true;
  try {
    const params = search.value ? `?search=${encodeURIComponent(search.value)}` : '';
    const response = await api.get(`/api/v/client/videos${params}`);
    videos.value = response.data.videos ?? [];
  } catch (e) {
    videos.value = [];
  } finally {
    loading.value = false;
  }
}

watch(search, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(fetchVideos, 300);
});

onMounted(fetchVideos);
onBeforeUnmount(() => clearTimeout(debounceTimer));
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
