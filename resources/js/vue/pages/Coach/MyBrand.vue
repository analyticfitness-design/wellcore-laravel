<script setup>
import { ref, onMounted, computed } from 'vue';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();
const loading = ref(true);
const saving = ref(false);
const success = ref(false);
const activeTab = ref('brand');

// Brand fields
const slug = ref('');
const bio = ref('');
const colorPrimary = ref('#DC2626');
const colorSecondary = ref('#1F2937');
const logoUrl = ref('');
const instagram = ref('');
const tiktok = ref('');
const youtube = ref('');
const website = ref('');

const coachName = computed(() => localStorage.getItem('wc_user_name') || 'Coach');

async function loadBrand() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v/coach/brand');
        slug.value = data.slug || '';
        bio.value = data.bio || '';
        colorPrimary.value = data.color_primary || '#DC2626';
        colorSecondary.value = data.color_secondary || '#1F2937';
        logoUrl.value = data.logo_url || '';
        instagram.value = data.instagram || '';
        tiktok.value = data.tiktok || '';
        youtube.value = data.youtube || '';
        website.value = data.website || '';
    } catch (e) {
        // silent
    } finally {
        loading.value = false;
    }
}

async function saveBrand() {
    saving.value = true;
    success.value = false;
    try {
        await api.put('/api/v/coach/brand', {
            slug: slug.value,
            bio: bio.value,
            color_primary: colorPrimary.value,
            color_secondary: colorSecondary.value,
            instagram: instagram.value,
            tiktok: tiktok.value,
            youtube: youtube.value,
            website: website.value,
        });
        success.value = true;
        setTimeout(() => { success.value = false; }, 3500);
    } catch (e) {
        // silent
    } finally {
        saving.value = false;
    }
}

onMounted(loadBrand);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex items-center gap-4">
        <div class="flex h-14 w-14 items-center justify-center rounded-full" :style="{ backgroundColor: colorPrimary + '20' }">
          <svg class="h-7 w-7" :style="{ color: colorPrimary }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42" />
          </svg>
        </div>
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Mi Marca</h1>
          <p class="mt-0.5 text-sm text-wc-text-tertiary">{{ coachName }} -- Personaliza tu marca y pagina publica</p>
        </div>
      </div>

      <!-- Success toast -->
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="success" class="flex items-center gap-3 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          Marca actualizada
        </div>
      </Transition>

      <!-- Tab bar -->
      <div class="flex items-center gap-1 border-b border-wc-border">
        <button
          v-for="tab in [{ key: 'brand', label: 'Marca' }, { key: 'preview', label: 'Preview' }]"
          :key="tab.key"
          @click="activeTab = tab.key"
          class="relative px-4 py-2.5 text-sm font-medium transition-colors"
          :class="activeTab === tab.key ? 'text-wc-accent' : 'text-wc-text-tertiary hover:text-wc-text'"
        >
          {{ tab.label }}
          <span v-if="activeTab === tab.key" class="absolute bottom-0 left-0 h-0.5 w-full bg-wc-accent"></span>
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <svg class="h-8 w-8 animate-spin text-wc-accent" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      </div>

      <template v-else>
        <!-- BRAND TAB -->
        <div v-if="activeTab === 'brand'" class="grid grid-cols-1 gap-6 lg:grid-cols-5">
          <div class="space-y-5 lg:col-span-3">

            <!-- Visual Identity -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
              <h3 class="font-display text-lg tracking-wide text-wc-text">Identidad Visual</h3>
              <div class="mt-4 space-y-4">
                <div>
                  <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Color Principal</label>
                  <div class="flex items-center gap-3">
                    <input type="color" v-model="colorPrimary" class="h-10 w-14 cursor-pointer rounded-lg border border-wc-border bg-wc-bg-secondary p-1" />
                    <input type="text" v-model="colorPrimary" class="w-32 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text font-mono focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30" />
                  </div>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Color Secundario</label>
                  <div class="flex items-center gap-3">
                    <input type="color" v-model="colorSecondary" class="h-10 w-14 cursor-pointer rounded-lg border border-wc-border bg-wc-bg-secondary p-1" />
                    <input type="text" v-model="colorSecondary" class="w-32 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text font-mono focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30" />
                  </div>
                </div>
              </div>
            </div>

            <!-- URL Slug -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
              <h3 class="font-display text-lg tracking-wide text-wc-text">URL Publica</h3>
              <div class="mt-4">
                <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Slug</label>
                <div class="flex items-center gap-0">
                  <span class="rounded-l-lg border border-r-0 border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text-tertiary">wellcore.co/coach/</span>
                  <input v-model="slug" type="text" class="flex-1 rounded-r-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30" placeholder="mi-slug" />
                </div>
              </div>
            </div>

            <!-- Bio & Social -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
              <h3 class="font-display text-lg tracking-wide text-wc-text">Bio y redes</h3>
              <div class="mt-4 space-y-4">
                <div>
                  <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Bio publica</label>
                  <textarea v-model="bio" rows="3" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30" placeholder="Tu bio para la pagina publica"></textarea>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Instagram</label>
                    <input v-model="instagram" type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30" placeholder="@username" />
                  </div>
                  <div>
                    <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">TikTok</label>
                    <input v-model="tiktok" type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30" placeholder="@username" />
                  </div>
                  <div>
                    <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">YouTube</label>
                    <input v-model="youtube" type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30" placeholder="URL" />
                  </div>
                  <div>
                    <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Website</label>
                    <input v-model="website" type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30" placeholder="https://..." />
                  </div>
                </div>
              </div>
            </div>

            <button
              @click="saveBrand"
              :disabled="saving"
              class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-5 py-2.5 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50"
            >{{ saving ? 'Guardando...' : 'Guardar marca' }}</button>
          </div>

          <!-- Preview card -->
          <div class="lg:col-span-2">
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 sticky top-24">
              <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary mb-3">Preview de tarjeta</p>
              <div class="rounded-xl overflow-hidden border border-wc-border">
                <div class="h-20" :style="{ backgroundColor: colorPrimary }"></div>
                <div class="p-4 -mt-6">
                  <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-white" :style="{ backgroundColor: colorPrimary }">
                    <span class="font-display text-lg text-white">{{ (coachName || 'C').charAt(0) }}</span>
                  </div>
                  <p class="mt-2 text-sm font-semibold text-wc-text">{{ coachName }}</p>
                  <p v-if="bio" class="mt-1 text-xs text-wc-text-tertiary line-clamp-2">{{ bio }}</p>
                  <p v-if="slug" class="mt-2 text-[10px] text-wc-text-tertiary">wellcore.co/coach/{{ slug }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- PREVIEW TAB -->
        <div v-if="activeTab === 'preview'" class="flex flex-col items-center py-8">
          <div class="w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-tertiary overflow-hidden shadow-lg">
            <div class="h-32" :style="{ background: `linear-gradient(135deg, ${colorPrimary}, ${colorSecondary})` }"></div>
            <div class="px-6 pb-6 -mt-10">
              <div class="flex h-20 w-20 items-center justify-center rounded-full border-4 border-wc-bg-tertiary" :style="{ backgroundColor: colorPrimary }">
                <span class="font-display text-3xl text-white">{{ (coachName || 'C').charAt(0) }}</span>
              </div>
              <h2 class="mt-3 font-display text-2xl tracking-wide text-wc-text">{{ coachName }}</h2>
              <p v-if="bio" class="mt-2 text-sm text-wc-text-secondary leading-relaxed">{{ bio }}</p>
              <div v-if="instagram || tiktok || youtube || website" class="mt-4 flex flex-wrap gap-2">
                <a v-if="instagram" :href="`https://instagram.com/${instagram.replace('@','')}`" class="inline-flex items-center gap-1 rounded-full border border-wc-border px-3 py-1 text-xs text-wc-text-secondary hover:text-wc-text transition-colors">IG {{ instagram }}</a>
                <a v-if="tiktok" :href="`https://tiktok.com/${tiktok.replace('@','')}`" class="inline-flex items-center gap-1 rounded-full border border-wc-border px-3 py-1 text-xs text-wc-text-secondary hover:text-wc-text transition-colors">TK {{ tiktok }}</a>
                <a v-if="youtube" :href="youtube" class="inline-flex items-center gap-1 rounded-full border border-wc-border px-3 py-1 text-xs text-wc-text-secondary hover:text-wc-text transition-colors">YouTube</a>
              </div>
            </div>
          </div>
        </div>

      </template>
    </div>
  </CoachLayout>
</template>
