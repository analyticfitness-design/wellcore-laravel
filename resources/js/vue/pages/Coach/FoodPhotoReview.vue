<script setup>
import { onMounted, ref, computed } from 'vue';
import CoachLayout from '../../layouts/CoachLayout.vue';
import { useApi } from '../../composables/useApi';

const api = useApi();

const loading = ref(true);
const photos = ref([]);
const allClients = ref([]);
const pendingCount = ref(0);
const showReviewed = ref(false);
const selectedClientId = ref('');
const noteMap = ref({});
const savingNote = ref({});
const reacting = ref({});

async function fetchPhotos() {
    loading.value = true;
    try {
        const params = {};
        if (showReviewed.value) params.reviewed = 1;
        if (selectedClientId.value) params.client_id = selectedClientId.value;
        const { data } = await api.get('/api/v/coach/food-photos', { params });
        photos.value = data.photos || [];
        allClients.value = data.all_clients || [];
        pendingCount.value = data.pending_count || 0;
        // Hidratar noteMap con notas existentes
        photos.value.forEach((p) => {
            if (noteMap.value[p.id] === undefined) noteMap.value[p.id] = p.coach_note || '';
        });
    } catch (err) {
        console.error('Failed to fetch food photos', err);
    } finally {
        loading.value = false;
    }
}

async function react(photoId, reaction) {
    reacting.value[photoId] = true;
    try {
        await api.post(`/api/v/coach/food-photos/${photoId}/react`, { reaction });
        await fetchPhotos();
    } catch (err) {
        console.error('React failed', err);
    } finally {
        reacting.value[photoId] = false;
    }
}

async function saveNote(photoId) {
    savingNote.value[photoId] = true;
    try {
        await api.patch(`/api/v/coach/food-photos/${photoId}/note`, {
            note: noteMap.value[photoId] || '',
        });
    } catch (err) {
        console.error('Save note failed', err);
    } finally {
        savingNote.value[photoId] = false;
    }
}

function toggleFilter() {
    showReviewed.value = !showReviewed.value;
    fetchPhotos();
}

onMounted(fetchPhotos);

const headerCount = computed(() => `${pendingCount.value} pendiente${pendingCount.value === 1 ? '' : 's'} de revisión`);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6 p-4 md:p-6">
      <!-- Header -->
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 class="font-display text-2xl tracking-wide text-wc-text">FOTOS DE COMIDA</h1>
          <p class="text-sm text-wc-text-secondary">{{ headerCount }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <select v-model="selectedClientId" @change="fetchPhotos"
                  class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text">
            <option value="">Todos los clientes</option>
            <option v-for="c in allClients" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <button @click="toggleFilter"
                  class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text transition hover:bg-wc-bg-tertiary">
            {{ showReviewed ? 'Ver Pendientes' : 'Ver Revisadas' }}
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-3">
        <div v-for="i in 3" :key="i" class="h-72 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      </div>

      <!-- Empty -->
      <div v-else-if="photos.length === 0"
           class="rounded-xl border border-wc-border bg-wc-bg-secondary p-10 text-center text-wc-text-secondary">
        {{ showReviewed ? 'No has revisado fotos aún.' : 'Sin fotos pendientes.' }}
      </div>

      <!-- Photos grid -->
      <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div v-for="photo in photos" :key="photo.id"
             class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary">
          <!-- Card header -->
          <div class="flex items-center gap-3 border-b border-wc-border p-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/10 text-sm font-bold text-wc-accent">
              {{ (photo.client_name || 'C').charAt(0) }}
            </div>
            <div class="min-w-0 flex-1">
              <p class="truncate font-medium text-wc-text">{{ photo.client_name }}</p>
              <p class="text-xs text-wc-text-tertiary">
                {{ photo.meal_name }} · {{ photo.photo_date }} · {{ photo.created_diff }}
              </p>
            </div>
          </div>

          <!-- Image -->
          <img :src="photo.photo_url" :alt="`Foto de ${photo.meal_name}`"
               class="h-64 w-full object-cover" loading="lazy">

          <!-- Actions -->
          <div class="space-y-3 p-4">
            <div v-if="photo.coach_seen" class="flex items-center gap-2 text-sm">
              <span v-if="photo.coach_reaction === 'bien'"
                    class="rounded-full bg-green-500/10 px-2 py-0.5 text-green-400">✅ Bien</span>
              <span v-else-if="photo.coach_reaction === 'mejorar'"
                    class="rounded-full bg-amber-500/10 px-2 py-0.5 text-amber-400">⚠️ Por mejorar</span>
              <span v-else class="rounded-full bg-wc-bg-tertiary px-2 py-0.5">Vista sin reacción</span>
            </div>
            <div v-else class="flex gap-2">
              <button @click="react(photo.id, 'bien')" :disabled="reacting[photo.id]"
                      class="flex-1 rounded-lg border border-green-500/30 bg-green-500/10 py-2 text-sm font-semibold text-green-400 transition hover:bg-green-500/20 disabled:opacity-50">
                ✅ Bien
              </button>
              <button @click="react(photo.id, 'mejorar')" :disabled="reacting[photo.id]"
                      class="flex-1 rounded-lg border border-amber-500/30 bg-amber-500/10 py-2 text-sm font-semibold text-amber-400 transition hover:bg-amber-500/20 disabled:opacity-50">
                ⚠️ Mejorar
              </button>
            </div>

            <textarea v-model="noteMap[photo.id]"
                      @blur="saveNote(photo.id)"
                      rows="2"
                      placeholder="Nota opcional para el cliente"
                      class="w-full rounded-lg border border-wc-border bg-wc-bg p-2 text-sm text-wc-text"></textarea>
            <p v-if="savingNote[photo.id]" class="text-xs text-wc-text-tertiary">Guardando...</p>
          </div>
        </div>
      </div>

      <p v-if="!loading && photos.length === 40" class="text-center text-xs text-wc-text-tertiary">
        Mostrando los 40 más recientes
      </p>
    </div>
  </CoachLayout>
</template>
