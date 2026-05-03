<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();
const route = useRoute();

const profile = ref(null);
const loading = ref(true);
const toggling = ref(false);
const fetchError = ref(false);

async function fetchProfile() {
  loading.value = true;
  fetchError.value = false;
  try {
    const response = await api.get(`/api/v/profile/${route.params.id}`);
    profile.value = response.data;
  } catch (err) {
    fetchError.value = true;
    profile.value = null;
  } finally {
    loading.value = false;
  }
}

async function toggleFollow() {
  if (!profile.value || toggling.value) return;
  toggling.value = true;
  try {
    if (profile.value.is_following) {
      await api.delete(`/api/v/profile/${profile.value.id}/follow`);
      profile.value.is_following = false;
      profile.value.follower_count = Math.max(0, (profile.value.follower_count || 1) - 1);
    } else {
      await api.post(`/api/v/profile/${profile.value.id}/follow`);
      profile.value.is_following = true;
      profile.value.follower_count = (profile.value.follower_count || 0) + 1;
    }
  } catch (err) {
    // Silently revert — optimistic update not applied so nothing to undo
  } finally {
    toggling.value = false;
  }
}

function initials(name) {
  if (!name) return '?';
  return name.split(' ').slice(0, 2).map(w => w[0] || '').join('').toUpperCase() || '?';
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  try {
    return new Date(dateStr).toLocaleDateString('es-CO', { day: 'numeric', month: 'long', year: 'numeric' });
  } catch {
    return dateStr;
  }
}

onMounted(fetchProfile);
</script>

<template>
  <ClientLayout>
    <!-- Loading skeleton -->
    <div v-if="loading" class="space-y-6 animate-pulse">
      <div class="flex items-center gap-4">
        <div class="h-20 w-20 shrink-0 rounded-full bg-wc-bg-tertiary"></div>
        <div class="flex-1 space-y-2">
          <div class="h-8 w-48 rounded-lg bg-wc-bg-tertiary"></div>
          <div class="h-4 w-32 rounded-lg bg-wc-bg-tertiary"></div>
        </div>
        <div class="h-9 w-24 shrink-0 rounded-xl bg-wc-bg-tertiary"></div>
      </div>
      <div class="grid grid-cols-3 gap-3">
        <div v-for="n in 3" :key="n" class="h-20 rounded-xl bg-wc-bg-tertiary"></div>
      </div>
      <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
        <div v-for="n in 8" :key="n" class="h-16 rounded-xl bg-wc-bg-tertiary"></div>
      </div>
    </div>

    <!-- Error / 403 state -->
    <div v-else-if="fetchError" class="flex flex-col items-center justify-center py-16 text-center gap-3">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
        <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
        </svg>
      </div>
      <p class="font-display text-xl tracking-wide text-wc-text">PERFIL NO DISPONIBLE</p>
      <p class="text-sm text-wc-text-secondary">Este perfil no existe o pertenece a otra comunidad.</p>
      <RouterLink
        to="/client/community"
        class="mt-2 rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white hover:bg-wc-accent/90 transition-colors"
      >
        Volver a la comunidad
      </RouterLink>
    </div>

    <!-- Profile content -->
    <div v-else-if="profile" class="space-y-6 pb-10">

      <!-- Hero -->
      <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
        <div class="flex flex-wrap items-center gap-3 sm:gap-4">
          <!-- Avatar -->
          <div class="h-20 w-20 shrink-0 rounded-full bg-wc-accent/20 flex items-center justify-center border-2 border-wc-accent/30 overflow-hidden">
            <img
              v-if="profile.avatar"
              :src="profile.avatar"
              :alt="profile.name"
              class="h-full w-full rounded-full object-cover"
            />
            <span v-else class="font-display text-2xl text-wc-accent">{{ initials(profile.name) }}</span>
          </div>

          <!-- Name + date + city + bio -->
          <div class="min-w-0 flex-1 basis-1/2">
            <h1 class="font-display text-3xl tracking-wide truncate text-wc-text">{{ profile.name }}</h1>
            <div class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-0.5">
              <p v-if="profile.started_at" class="text-sm text-wc-text-secondary">
                Desde {{ formatDate(profile.started_at) }}
              </p>
              <p v-if="profile.city" class="flex items-center gap-1 text-sm text-wc-text-tertiary">
                <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                {{ profile.city }}
              </p>
            </div>
            <p v-if="profile.bio" class="mt-2 text-sm leading-relaxed text-wc-text-secondary">
              {{ profile.bio }}
            </p>
          </div>

          <!-- Follow button -->
          <button
            @click="toggleFollow"
            :disabled="toggling"
            class="group relative shrink-0 overflow-hidden rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200 disabled:opacity-60"
            :class="profile.is_following
              ? 'border border-wc-border text-wc-text hover:border-red-500 hover:text-red-400'
              : 'bg-wc-accent text-white hover:bg-red-700 active:scale-95'"
          >
            <span v-if="toggling" class="inline-block h-3 w-3 rounded-full border-2 border-current border-t-transparent animate-spin align-middle"></span>
            <template v-else>
              <span v-if="profile.is_following" class="group-hover:hidden">Siguiendo ✓</span>
              <span v-if="profile.is_following" class="hidden group-hover:inline">Dejar de seguir</span>
              <span v-if="!profile.is_following">Seguir</span>
            </template>
          </button>
        </div>
      </div>

      <!-- Stats grid -->
      <div class="grid grid-cols-3 gap-3">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-3 text-center">
          <p class="font-data text-2xl font-bold text-wc-accent">{{ profile.streak_days ?? 0 }}</p>
          <p class="text-xs text-wc-text-secondary mt-0.5">Racha</p>
        </div>
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-3 text-center">
          <p class="font-data text-2xl font-bold text-wc-text">L{{ profile.level ?? 1 }}</p>
          <p class="text-xs text-wc-text-secondary mt-0.5">Nivel</p>
        </div>
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-3 text-center">
          <p class="font-data text-2xl font-bold text-wc-text">{{ profile.follower_count ?? 0 }}</p>
          <p class="text-xs text-wc-text-secondary mt-0.5">Seguidores</p>
        </div>
      </div>

      <!-- XP bar (if available) -->
      <div v-if="profile.xp_total != null" class="rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3 flex items-center gap-3">
        <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
        </svg>
        <div class="flex-1 min-w-0">
          <p class="text-xs text-wc-text-secondary">XP Total</p>
          <p class="font-data text-lg font-bold text-wc-text leading-tight">{{ profile.xp_total.toLocaleString('es-CO') }}</p>
        </div>
      </div>

      <!-- Medals -->
      <div v-if="profile.medals && profile.medals.length > 0">
        <h2 class="mb-3 wc-caption">Medallas</h2>
        <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-6">
          <div
            v-for="medal in profile.medals"
            :key="medal.name"
            :title="`${medal.name}${medal.achieved_at ? ' · ' + formatDate(medal.achieved_at) : ''}`"
            class="flex flex-col items-center gap-1 rounded-xl border border-wc-border bg-wc-bg-tertiary p-2 text-center hover:border-wc-accent/40 transition-colors"
          >
            <span class="text-2xl leading-none">{{ medal.icon }}</span>
            <span class="text-[10px] font-semibold leading-tight line-clamp-2 text-wc-text-secondary">{{ medal.name }}</span>
          </div>
        </div>
      </div>

      <!-- Empty medals -->
      <div v-else class="rounded-xl border border-dashed border-wc-border py-8 text-center space-y-2">
        <span class="text-3xl leading-none">&#127885;</span>
        <p class="text-sm text-wc-text/40">Aún no tiene medallas desbloqueadas.</p>
      </div>

    </div>
  </ClientLayout>
</template>
