<template>
  <div class="space-y-6">
    <div>
      <h1 class="font-display text-3xl tracking-wide text-wc-text">ACADEMIA</h1>
      <p class="mt-1 text-sm text-wc-text-secondary">Contenido educativo seleccionado para potenciar tu entrenamiento.</p>
    </div>

    <!-- Search + category filter -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
      <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input
          v-model="search"
          type="text"
          placeholder="Buscar contenido..."
          class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
        />
        <div v-if="loading" class="absolute right-3 top-1/2 -translate-y-1/2">
          <svg class="h-4 w-4 animate-spin text-wc-accent" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
        </div>
      </div>
      <select
        v-if="categories.length > 0"
        v-model="categoryFilter"
        class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
      >
        <option value="">Todas las categorias</option>
        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
      </select>
    </div>

    <!-- Active filters -->
    <div v-if="search || categoryFilter" class="flex flex-wrap items-center gap-2">
      <span class="text-xs text-wc-text-tertiary">Filtrando por:</span>
      <span v-if="search" class="inline-flex items-center gap-1.5 rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-medium text-wc-accent">
        "{{ search }}"
        <button @click="search = ''" class="hover:text-wc-accent">
          <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>
        </button>
      </span>
      <span v-if="categoryFilter" class="inline-flex items-center gap-1.5 rounded-full border border-wc-border bg-wc-bg-tertiary px-3 py-1 text-xs font-medium text-wc-text-secondary">
        {{ categoryFilter }}
        <button @click="categoryFilter = ''" class="hover:text-wc-text">
          <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>
        </button>
      </span>
    </div>

    <!-- Two-column layout when content selected -->
    <div :class="selectedContent ? 'grid grid-cols-1 gap-6 lg:grid-cols-2' : ''">

      <!-- Content grid -->
      <div>
        <!-- Empty state -->
        <div v-if="!loading && contents.length === 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
          <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.966 8.966 0 0 0-6 2.292m0-14.25v14.25" />
          </svg>
          <p class="mt-3 text-sm font-medium text-wc-text-secondary">
            {{ search || categoryFilter ? 'Sin resultados para tu busqueda.' : 'No hay contenido disponible aun.' }}
          </p>
          <p v-if="search || categoryFilter" class="mt-1 text-xs text-wc-text-tertiary">Intenta cambiar los filtros o la busqueda.</p>
          <button v-if="search || categoryFilter" @click="search = ''; categoryFilter = ''" class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-xs font-semibold text-white hover:bg-wc-accent/90">
            Limpiar filtros
          </button>
        </div>

        <template v-else>
          <p class="mb-4 text-xs text-wc-text-tertiary">{{ contents.length }} {{ contents.length === 1 ? 'resultado' : 'resultados' }}</p>

          <div :class="['grid grid-cols-1 gap-4 sm:grid-cols-2', selectedContent ? 'lg:grid-cols-1' : 'lg:grid-cols-3']">
            <button
              v-for="content in contents"
              :key="content.id"
              @click="toggleContent(content)"
              :class="selectedContent?.id === content.id ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40'"
              class="group w-full cursor-pointer rounded-xl border text-left transition-all focus:outline-none focus:ring-2 focus:ring-wc-accent"
            >
              <!-- Thumbnail / type indicator -->
              <div class="relative aspect-video overflow-hidden rounded-t-xl bg-wc-bg-secondary">
                <img
                  v-if="content.thumbnail_url"
                  :src="content.thumbnail_url"
                  :alt="content.title"
                  class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                  loading="lazy"
                />
                <div v-else class="flex h-full items-center justify-center">
                  <svg v-if="content.content_type === 'video'" class="h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                  </svg>
                  <svg v-else class="h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.966 8.966 0 0 0-6 2.292m0-14.25v14.25" />
                  </svg>
                </div>

                <!-- Play overlay for video -->
                <div v-if="content.content_type === 'video'" class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 transition-opacity group-hover:opacity-100">
                  <div class="flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent/90 shadow-lg">
                    <svg class="ml-0.5 h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                  </div>
                </div>

                <!-- Content type badge -->
                <span
                  class="absolute left-2 top-2 rounded-full px-2 py-0.5 text-[10px] font-semibold"
                  :class="content.content_type === 'video' ? 'bg-purple-500/20 text-purple-300' : 'bg-blue-500/20 text-blue-300'"
                >{{ (content.content_type || 'articulo').toUpperCase() }}</span>

                <!-- Selected indicator -->
                <div v-if="selectedContent?.id === content.id" class="absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full bg-wc-accent">
                  <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                  </svg>
                </div>
              </div>

              <!-- Info -->
              <div class="p-4">
                <span v-if="content.category" class="text-[10px] font-semibold uppercase tracking-wider text-wc-accent">{{ content.category }}</span>
                <h3 class="mt-1 text-sm font-semibold leading-snug text-wc-text">{{ content.title }}</h3>
                <p v-if="content.description" class="mt-1.5 line-clamp-2 text-xs text-wc-text-tertiary">{{ content.description }}</p>
              </div>
            </button>
          </div>
        </template>
      </div>

      <!-- Content detail panel -->
      <Transition name="fade">
        <div
          v-if="selectedContent"
          class="sticky top-4 h-fit rounded-xl border border-wc-accent/30 bg-wc-bg-secondary"
        >
          <!-- Panel header -->
          <div class="flex items-start justify-between border-b border-wc-border px-5 py-4">
            <div class="flex-1 pr-4">
              <span v-if="selectedContent.category" class="text-[10px] font-semibold uppercase tracking-wider text-wc-accent">{{ selectedContent.category }}</span>
              <h2 class="mt-0.5 font-display text-xl tracking-wide text-wc-text">{{ selectedContent.title }}</h2>
            </div>
            <button @click="selectedContent = null" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-wc-text-secondary transition-colors hover:bg-wc-bg-tertiary hover:text-wc-text">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Panel body -->
          <div class="max-h-[75vh] space-y-5 overflow-y-auto px-5 py-5">

            <!-- Video embed -->
            <template v-if="selectedContent.content_type === 'video' && selectedContent.content_url">
              <div v-if="youtubeEmbedUrl(selectedContent.content_url)" class="aspect-video overflow-hidden rounded-xl">
                <iframe
                  :src="youtubeEmbedUrl(selectedContent.content_url)"
                  :title="selectedContent.title"
                  class="h-full w-full"
                  frameborder="0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowfullscreen
                ></iframe>
              </div>
              <div v-else class="aspect-video overflow-hidden rounded-xl bg-wc-bg">
                <video :src="selectedContent.content_url" class="h-full w-full rounded-xl" controls preload="metadata"></video>
              </div>
            </template>

            <!-- Thumbnail for non-video -->
            <img
              v-else-if="selectedContent.thumbnail_url"
              :src="selectedContent.thumbnail_url"
              :alt="selectedContent.title"
              class="w-full rounded-xl object-cover"
            />

            <!-- Description -->
            <p v-if="selectedContent.description" class="text-sm leading-relaxed text-wc-text-secondary">{{ selectedContent.description }}</p>

            <!-- Rich HTML body -->
            <div
              v-if="selectedContent.body_html"
              v-html="selectedContent.body_html"
              class="prose prose-sm max-w-none
                prose-headings:font-display prose-headings:text-wc-text prose-headings:tracking-wide
                prose-p:text-wc-text-secondary prose-p:leading-relaxed
                prose-strong:text-wc-text
                prose-a:text-wc-accent prose-a:no-underline hover:prose-a:underline
                prose-ul:text-wc-text-secondary prose-ol:text-wc-text-secondary
                prose-li:marker:text-wc-accent
                prose-blockquote:border-l-wc-accent prose-blockquote:text-wc-text-tertiary
                prose-code:text-wc-accent prose-code:bg-wc-bg-tertiary prose-code:rounded prose-code:px-1"
            ></div>

            <p v-else-if="!selectedContent.content_url && !selectedContent.body_html" class="text-sm italic text-wc-text-tertiary">Contenido no disponible aun.</p>

            <!-- External link -->
            <a
              v-if="selectedContent.content_type !== 'video' && selectedContent.content_url"
              :href="selectedContent.content_url"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent/90"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
              </svg>
              Ver contenido completo
            </a>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';

const api = useApi();
const contents = ref([]);
const categories = ref([]);
const selectedContent = ref(null);
const search = ref('');
const categoryFilter = ref('');
const loading = ref(false);

function youtubeEmbedUrl(url) {
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
  return ytId ? `https://www.youtube.com/embed/${ytId}?rel=0&modestbranding=1` : null;
}

function toggleContent(content) {
  selectedContent.value = selectedContent.value?.id === content.id ? null : content;
}

let debounceTimer = null;
async function fetchContents() {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (search.value) params.set('search', search.value);
    if (categoryFilter.value) params.set('category', categoryFilter.value);
    const qs = params.toString() ? `?${params.toString()}` : '';
    const response = await api.get(`/api/v/client/academia${qs}`);
    contents.value = response.data.contents ?? [];
    if (response.data.categories?.length) categories.value = response.data.categories;
  } catch (e) {
    contents.value = [];
  } finally {
    loading.value = false;
  }
}

watch([search, categoryFilter], () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(fetchContents, 300);
});

onMounted(fetchContents);
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
