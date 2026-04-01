<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();
const loading = ref(true);
const activeModule = ref('guides');

const guides = ref([]);
const protocols = ref([]);
const videos = ref([]);
const articles = ref([]);
const tools = ref([]);
const academy = ref([]);

const expandedGuide = ref(null);

const modules = [
    { key: 'guides', label: 'Guias', icon: 'book-open' },
    { key: 'protocols', label: 'Protocolos', icon: 'clipboard' },
    { key: 'videos', label: 'Videos', icon: 'play-circle' },
    { key: 'articles', label: 'Articulos', icon: 'newspaper' },
    { key: 'tools', label: 'Herramientas', icon: 'wrench' },
    { key: 'academy', label: 'Academia', icon: 'academic-cap' },
];

function currentItems() {
    switch (activeModule.value) {
        case 'guides': return guides.value;
        case 'protocols': return protocols.value;
        case 'videos': return videos.value;
        case 'articles': return articles.value;
        case 'tools': return tools.value;
        case 'academy': return academy.value;
        default: return [];
    }
}

async function loadResources() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v/coach/resources');
        guides.value = data.guides || [];
        protocols.value = data.protocols || [];
        videos.value = data.videos || [];
        articles.value = data.articles || [];
        tools.value = data.tools || [];
        academy.value = data.academy || [];
    } catch (e) {
        // silent
    } finally {
        loading.value = false;
    }
}

onMounted(loadResources);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-2xl tracking-wide text-wc-text sm:text-3xl">RECURSOS DEL COACH</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Guias, protocolos, herramientas y contenido educativo</p>
      </div>

      <!-- Layout: sidebar nav + content -->
      <div class="flex flex-col gap-6 lg:flex-row">

        <!-- Sidebar navigation -->
        <nav class="w-full shrink-0 lg:w-56">
          <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-2 space-y-0.5">
            <button
              v-for="mod in modules"
              :key="mod.key"
              @click="activeModule = mod.key"
              class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
              :class="activeModule === mod.key ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text'"
            >
              <!-- book-open -->
              <svg v-if="mod.icon === 'book-open'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
              </svg>
              <!-- clipboard -->
              <svg v-else-if="mod.icon === 'clipboard'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
              </svg>
              <!-- play-circle -->
              <svg v-else-if="mod.icon === 'play-circle'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" />
              </svg>
              <!-- newspaper -->
              <svg v-else-if="mod.icon === 'newspaper'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
              </svg>
              <!-- wrench -->
              <svg v-else-if="mod.icon === 'wrench'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.048.58.024 1.194-.14 1.743" />
              </svg>
              <!-- academic-cap -->
              <svg v-else-if="mod.icon === 'academic-cap'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
              </svg>
              {{ mod.label }}
            </button>
          </div>
        </nav>

        <!-- Content area -->
        <div class="min-w-0 flex-1">

          <!-- Loading -->
          <div v-if="loading" class="flex items-center justify-center py-12">
            <svg class="h-8 w-8 animate-spin text-wc-accent" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
          </div>

          <template v-else>
            <!-- Content items -->
            <div v-if="currentItems().length > 0" class="space-y-3">
              <div
                v-for="item in currentItems()"
                :key="item.id"
                class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden"
              >
                <button
                  v-if="activeModule === 'guides'"
                  @click="expandedGuide = expandedGuide === item.id ? null : item.id"
                  class="flex w-full items-center gap-4 p-4 text-left hover:bg-wc-bg-secondary/50 transition-colors"
                >
                  <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-sky-500/10">
                    <svg class="h-5 w-5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-wc-text">{{ item.title }}</p>
                    <p class="text-xs text-wc-text-tertiary">{{ item.category || item.description || '' }}</p>
                  </div>
                  <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform" :class="{ 'rotate-180': expandedGuide === item.id }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                  </svg>
                </button>

                <div v-else class="flex items-center gap-4 p-4">
                  <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg" :class="activeModule === 'videos' ? 'bg-violet-500/10' : activeModule === 'academy' ? 'bg-amber-500/10' : 'bg-emerald-500/10'">
                    <svg class="h-5 w-5" :class="activeModule === 'videos' ? 'text-violet-500' : activeModule === 'academy' ? 'text-amber-500' : 'text-emerald-500'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-wc-text">{{ item.title }}</p>
                    <p class="text-xs text-wc-text-tertiary">{{ item.description || item.category || '' }}</p>
                  </div>
                  <a v-if="item.url" :href="item.url" target="_blank" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-accent transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                  </a>
                </div>

                <!-- Expanded guide content -->
                <div v-if="activeModule === 'guides' && expandedGuide === item.id" class="border-t border-wc-border bg-wc-bg-secondary/30 p-4">
                  <p class="text-sm text-wc-text-secondary leading-relaxed whitespace-pre-line">{{ item.content || 'Sin contenido detallado disponible.' }}</p>
                </div>
              </div>
            </div>

            <!-- Empty state -->
            <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
              <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
              </svg>
              <p class="mt-3 text-sm font-medium text-wc-text">Sin recursos disponibles</p>
              <p class="mt-1 text-xs text-wc-text-tertiary">El contenido se agregara proximamente</p>
            </div>
          </template>
        </div>
      </div>
    </div>
  </CoachLayout>
</template>
