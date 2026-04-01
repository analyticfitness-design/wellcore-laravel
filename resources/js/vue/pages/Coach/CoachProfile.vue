<script setup>
import { ref, onMounted, computed } from 'vue';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();
const loading = ref(true);
const saving = ref(false);
const success = ref(false);
const activeTab = ref('profile');

// Profile fields
const bio = ref('');
const city = ref('');
const experience = ref('');
const specialties = ref([]);
const instagram = ref('');
const tiktok = ref('');
const youtube = ref('');

// Revenue data
const revenue = ref({ total: 0, monthly: 0, referrals: 0, clients_paying: 0 });

const coachName = computed(() => localStorage.getItem('wc_user_name') || 'Coach');
const coachInitial = computed(() => (coachName.value || 'C').charAt(0).toUpperCase());

async function loadProfile() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v/coach/profile');
        bio.value = data.bio || '';
        city.value = data.city || '';
        experience.value = data.experience || '';
        specialties.value = data.specialties || [];
        instagram.value = data.instagram || '';
        tiktok.value = data.tiktok || '';
        youtube.value = data.youtube || '';
        revenue.value = data.revenue || revenue.value;
    } catch (e) {
        // silent
    } finally {
        loading.value = false;
    }
}

async function saveProfile() {
    saving.value = true;
    success.value = false;
    try {
        await api.put('/api/v/coach/profile', {
            bio: bio.value,
            city: city.value,
            experience: experience.value,
            specialties: specialties.value,
            instagram: instagram.value,
            tiktok: tiktok.value,
            youtube: youtube.value,
        });
        success.value = true;
        setTimeout(() => { success.value = false; }, 3500);
    } catch (e) {
        // silent
    } finally {
        saving.value = false;
    }
}

onMounted(loadProfile);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex items-center gap-4">
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-wc-accent/20">
          <span class="font-display text-2xl text-wc-accent">{{ coachInitial }}</span>
        </div>
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Mi Perfil</h1>
          <p class="mt-0.5 text-sm text-wc-text-tertiary">{{ coachName }} -- Gestiona tu perfil y revenue</p>
        </div>
      </div>

      <!-- Success toast -->
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="success" class="flex items-center gap-3 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          Perfil actualizado
        </div>
      </Transition>

      <!-- Tab bar -->
      <div class="flex items-center gap-1 border-b border-wc-border">
        <button
          v-for="tab in [{ key: 'profile', label: 'Perfil' }, { key: 'revenue', label: 'Revenue' }]"
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

        <!-- PROFILE TAB -->
        <div v-if="activeTab === 'profile'" class="grid grid-cols-1 gap-6 lg:grid-cols-5">
          <div class="space-y-5 lg:col-span-3">
            <!-- Bio -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
              <h3 class="font-display text-lg tracking-wide text-wc-text">Informacion basica</h3>
              <div class="mt-4 space-y-4">
                <div>
                  <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Bio</label>
                  <textarea v-model="bio" rows="3" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30" placeholder="Describe tu experiencia y enfoque como coach..."></textarea>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Ciudad</label>
                    <input v-model="city" type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30" placeholder="Bogota, CO" />
                  </div>
                  <div>
                    <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Experiencia</label>
                    <input v-model="experience" type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30" placeholder="5 anos" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Social links -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
              <h3 class="font-display text-lg tracking-wide text-wc-text">Redes sociales</h3>
              <div class="mt-4 space-y-4">
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
                  <input v-model="youtube" type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30" placeholder="URL del canal" />
                </div>
              </div>
            </div>

            <!-- Save -->
            <button
              @click="saveProfile"
              :disabled="saving"
              class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-5 py-2.5 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50"
            >
              <svg v-if="saving" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              {{ saving ? 'Guardando...' : 'Guardar cambios' }}
            </button>
          </div>

          <!-- Preview card -->
          <div class="lg:col-span-2">
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 sticky top-24">
              <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary mb-3">Preview</p>
              <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent/20">
                  <span class="font-display text-xl text-wc-accent">{{ coachInitial }}</span>
                </div>
                <div>
                  <p class="text-sm font-semibold text-wc-text">{{ coachName }}</p>
                  <p class="text-xs text-wc-text-tertiary">{{ city || 'Sin ciudad' }} -- {{ experience || 'N/A' }} exp.</p>
                </div>
              </div>
              <p v-if="bio" class="mt-3 text-xs text-wc-text-secondary leading-relaxed">{{ bio }}</p>
              <div v-if="instagram || tiktok || youtube" class="mt-3 flex items-center gap-2">
                <span v-if="instagram" class="text-[10px] text-wc-accent">IG: {{ instagram }}</span>
                <span v-if="tiktok" class="text-[10px] text-wc-text-tertiary">TK: {{ tiktok }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- REVENUE TAB -->
        <div v-if="activeTab === 'revenue'" class="space-y-6">
          <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center">
              <p class="font-data text-3xl font-bold text-wc-text">${{ revenue.total.toLocaleString() }}</p>
              <p class="mt-1 text-xs text-wc-text-tertiary">Revenue total</p>
            </div>
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center">
              <p class="font-data text-3xl font-bold text-emerald-500">${{ revenue.monthly.toLocaleString() }}</p>
              <p class="mt-1 text-xs text-wc-text-tertiary">Este mes</p>
            </div>
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center">
              <p class="font-data text-3xl font-bold text-wc-text">{{ revenue.clients_paying }}</p>
              <p class="mt-1 text-xs text-wc-text-tertiary">Clientes activos</p>
            </div>
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center">
              <p class="font-data text-3xl font-bold text-violet-500">{{ revenue.referrals }}</p>
              <p class="mt-1 text-xs text-wc-text-tertiary">Referidos</p>
            </div>
          </div>
        </div>

      </template>
    </div>
  </CoachLayout>
</template>
