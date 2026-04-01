<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();
const loading = ref(true);
const activeTab = ref('pods');
const success = ref('');

// Pods
const pods = ref([]);
// Availability
const availability = ref([]);
// Audio
const audioLibrary = ref([]);
// Video check-ins
const videoCheckins = ref([]);

async function loadFeatures() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v/coach/features');
        pods.value = data.pods || [];
        availability.value = data.availability || [];
        audioLibrary.value = data.audioLibrary || [];
        videoCheckins.value = data.videoCheckins || [];
    } catch (e) {
        // silent
    } finally {
        loading.value = false;
    }
}

onMounted(loadFeatures);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-2xl sm:text-3xl tracking-wide text-wc-text">HERRAMIENTAS DEL COACH</h1>
        <p class="text-sm text-wc-text-secondary mt-1">Pods, disponibilidad, audios y revision de video check-ins</p>
      </div>

      <!-- Success toast -->
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="success" class="flex items-center gap-3 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          {{ success }}
        </div>
      </Transition>

      <!-- Tab bar -->
      <div class="flex gap-1 rounded-xl bg-wc-bg-secondary border border-wc-border p-1 overflow-x-auto">
        <button
          v-for="tab in [{ key: 'pods', label: 'Pods' }, { key: 'availability', label: 'Disponibilidad' }, { key: 'audio', label: 'Audios' }, { key: 'video_checkins', label: 'Video Check-ins' }]"
          :key="tab.key"
          @click="activeTab = tab.key"
          class="flex-1 min-w-[80px] sm:min-w-[120px] rounded-lg px-2 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-medium transition-all whitespace-nowrap"
          :class="activeTab === tab.key ? 'bg-wc-accent text-white shadow-lg shadow-wc-accent/25' : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary'"
        >{{ tab.label }}</button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <svg class="h-8 w-8 animate-spin text-wc-accent" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      </div>

      <template v-else>

        <!-- PODS TAB -->
        <template v-if="activeTab === 'pods'">
          <div v-if="pods.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="pod in pods" :key="pod.id" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-wc-text">{{ pod.name }}</h3>
                <span class="text-xs text-wc-text-tertiary">{{ pod.member_count }}/{{ pod.max_members }}</span>
              </div>
              <p v-if="pod.description" class="mt-2 text-xs text-wc-text-secondary">{{ pod.description }}</p>
              <div class="mt-3 flex -space-x-1">
                <div v-for="member in (pod.members || []).slice(0, 5)" :key="member.id" class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-wc-bg-tertiary bg-wc-accent/15 text-[10px] font-semibold text-wc-accent">
                  {{ (member.name || 'M').charAt(0) }}
                </div>
                <div v-if="(pod.members || []).length > 5" class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-wc-bg-tertiary bg-wc-bg-secondary text-[10px] font-semibold text-wc-text-tertiary">
                  +{{ (pod.members || []).length - 5 }}
                </div>
              </div>
            </div>
          </div>
          <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
            <p class="text-sm text-wc-text-tertiary">Sin pods de accountability</p>
          </div>
        </template>

        <!-- AVAILABILITY TAB -->
        <template v-if="activeTab === 'availability'">
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h3 class="text-sm font-semibold text-wc-text mb-4">Horarios de disponibilidad</h3>
            <div v-if="availability.length > 0" class="space-y-2">
              <div v-for="slot in availability" :key="slot.id" class="flex items-center justify-between rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3">
                <div>
                  <p class="text-sm font-medium text-wc-text">{{ slot.day }}</p>
                  <p class="text-xs text-wc-text-tertiary">{{ slot.start_time }} - {{ slot.end_time }}</p>
                </div>
                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="slot.available ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500'">
                  {{ slot.available ? 'Disponible' : 'No disponible' }}
                </span>
              </div>
            </div>
            <div v-else class="py-8 text-center text-sm text-wc-text-tertiary">Sin horarios configurados</div>
          </div>
        </template>

        <!-- AUDIO TAB -->
        <template v-if="activeTab === 'audio'">
          <div v-if="audioLibrary.length > 0" class="space-y-3">
            <div v-for="audio in audioLibrary" :key="audio.id" class="flex items-center gap-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-violet-500/10">
                <svg class="h-5 w-5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />
                </svg>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-wc-text">{{ audio.title }}</p>
                <p class="text-xs text-wc-text-tertiary">{{ audio.duration }} -- {{ audio.category }}</p>
              </div>
            </div>
          </div>
          <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
            <p class="text-sm text-wc-text-tertiary">Sin audios en la biblioteca</p>
          </div>
        </template>

        <!-- VIDEO CHECK-INS TAB -->
        <template v-if="activeTab === 'video_checkins'">
          <div v-if="videoCheckins.length > 0" class="space-y-3">
            <div v-for="vc in videoCheckins" :key="vc.id" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4" :class="{ 'border-l-2 border-l-orange-500': vc.status === 'pending' }">
              <div class="flex items-center gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
                  <span class="text-sm font-semibold text-wc-accent">{{ (vc.client_name || 'C').charAt(0) }}</span>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center gap-2">
                    <p class="text-sm font-medium text-wc-text">{{ vc.client_name }}</p>
                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="vc.status === 'pending' ? 'bg-orange-500/10 text-orange-500' : 'bg-emerald-500/10 text-emerald-500'">
                      {{ vc.status === 'pending' ? 'Pendiente' : 'Revisado' }}
                    </span>
                  </div>
                  <p class="text-xs text-wc-text-tertiary">{{ vc.exercise_name }} -- {{ vc.created_at }}</p>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
            <p class="text-sm text-wc-text-tertiary">Sin video check-ins</p>
          </div>
        </template>

      </template>
    </div>
  </CoachLayout>
</template>
