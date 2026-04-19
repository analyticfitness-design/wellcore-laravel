<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import RiseLayout from '../../layouts/RiseLayout.vue';

const api = useApi();

const loading = ref(true);
const uploading = ref(false);
const error = ref(null);
const uploadSuccess = ref(false);
const uploadError = ref('');

// Gallery
const photosByDate = ref([]);
const firstDate = ref(null);
const latestDate = ref(null);

// Upload form
const uploadDate = ref(new Date().toISOString().split('T')[0]);
const photoFrente = ref(null);
const photoPerfil = ref(null);
const photoEspalda = ref(null);

// File input refs
const frenteInput = ref(null);
const perfilInput = ref(null);
const espaldaInput = ref(null);

// Preview URLs
const frentePreview = ref(null);
const perfilPreview = ref(null);
const espaldaPreview = ref(null);

function onFileSelect(type, event) {
    const file = event.target.files[0];
    if (!file) return;

    const url = URL.createObjectURL(file);
    if (type === 'frente') {
        photoFrente.value = file;
        frentePreview.value = url;
    } else if (type === 'perfil') {
        photoPerfil.value = file;
        perfilPreview.value = url;
    } else {
        photoEspalda.value = file;
        espaldaPreview.value = url;
    }
}

async function fetchPhotos() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/rise/photos');
        photosByDate.value = response.data.photosByDate || [];
        firstDate.value = response.data.firstDate || null;
        latestDate.value = response.data.latestDate || null;
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar fotos';
    } finally {
        loading.value = false;
    }
}

async function uploadPhotos() {
    if (!photoFrente.value && !photoPerfil.value && !photoEspalda.value) {
        uploadError.value = 'Selecciona al menos una foto para subir.';
        return;
    }

    uploading.value = true;
    uploadSuccess.value = false;
    uploadError.value = '';

    const formData = new FormData();
    formData.append('date', uploadDate.value);
    if (photoFrente.value) formData.append('photo_frente', photoFrente.value);
    if (photoPerfil.value) formData.append('photo_perfil', photoPerfil.value);
    if (photoEspalda.value) formData.append('photo_espalda', photoEspalda.value);

    try {
        await api.post('/api/v/rise/photos', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        uploadSuccess.value = true;
        resetUpload();
        await fetchPhotos();
        setTimeout(() => { uploadSuccess.value = false; }, 3000);
    } catch (err) {
        uploadError.value = err.response?.data?.message || 'Error al subir fotos';
    } finally {
        uploading.value = false;
    }
}

async function deletePhoto(photoId) {
    if (!confirm('Eliminar esta foto? Esta accion no se puede deshacer.')) return;
    try {
        await api.delete(`/api/v/rise/photos/${photoId}`);
        await fetchPhotos();
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al eliminar';
    }
}

function resetUpload() {
    photoFrente.value = null;
    photoPerfil.value = null;
    photoEspalda.value = null;
    frentePreview.value = null;
    perfilPreview.value = null;
    espaldaPreview.value = null;
}

onMounted(() => {
    fetchPhotos();
});
</script>

<template>
  <RiseLayout>
    <div v-if="loading" class="space-y-6">
      <div class="h-10 w-64 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      <div class="h-48 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Page header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">FOTOS DE PROGRESO</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Registra tu transformacion visual semana a semana.</p>
      </div>

      <!-- Upload form -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h2 class="font-display text-lg tracking-wide text-wc-text">Subir fotos nuevas</h2>

        <!-- Success -->
        <div v-if="uploadSuccess" class="mt-3 flex items-center gap-2 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3">
          <svg class="h-4 w-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
          </svg>
          <p class="text-sm text-emerald-400">Fotos guardadas correctamente.</p>
        </div>

        <!-- Error -->
        <div v-if="uploadError" class="mt-3 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
          {{ uploadError }}
        </div>

        <!-- Date picker -->
        <div class="mt-4">
          <label class="block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Fecha de las fotos</label>
          <input type="date" v-model="uploadDate"
            class="mt-1.5 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none [color-scheme:dark]">
        </div>

        <!-- Three photo slots -->
        <div class="mt-4 grid grid-cols-3 gap-3">
          <!-- Frente -->
          <div>
            <p class="mb-1.5 text-center text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Frente</p>
            <label class="group relative flex aspect-[3/4] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-wc-border bg-wc-bg-secondary transition-colors hover:border-wc-accent/50">
              <template v-if="frentePreview">
                <img :src="frentePreview" alt="Frente" class="h-full w-full object-cover">
                <div class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 transition-opacity group-hover:opacity-100">
                  <span class="text-xs font-medium text-white">Cambiar</span>
                </div>
              </template>
              <template v-else>
                <svg class="h-6 w-6 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span class="mt-1 text-[10px] text-wc-text-tertiary">Agregar</span>
              </template>
              <input type="file" class="hidden" accept="image/*" @change="onFileSelect('frente', $event)">
            </label>
          </div>

          <!-- Perfil -->
          <div>
            <p class="mb-1.5 text-center text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Perfil</p>
            <label class="group relative flex aspect-[3/4] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-wc-border bg-wc-bg-secondary transition-colors hover:border-wc-accent/50">
              <template v-if="perfilPreview">
                <img :src="perfilPreview" alt="Perfil" class="h-full w-full object-cover">
                <div class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 transition-opacity group-hover:opacity-100">
                  <span class="text-xs font-medium text-white">Cambiar</span>
                </div>
              </template>
              <template v-else>
                <svg class="h-6 w-6 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span class="mt-1 text-[10px] text-wc-text-tertiary">Agregar</span>
              </template>
              <input type="file" class="hidden" accept="image/*" @change="onFileSelect('perfil', $event)">
            </label>
          </div>

          <!-- Espalda -->
          <div>
            <p class="mb-1.5 text-center text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Espalda</p>
            <label class="group relative flex aspect-[3/4] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-wc-border bg-wc-bg-secondary transition-colors hover:border-wc-accent/50">
              <template v-if="espaldaPreview">
                <img :src="espaldaPreview" alt="Espalda" class="h-full w-full object-cover">
                <div class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 transition-opacity group-hover:opacity-100">
                  <span class="text-xs font-medium text-white">Cambiar</span>
                </div>
              </template>
              <template v-else>
                <svg class="h-6 w-6 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span class="mt-1 text-[10px] text-wc-text-tertiary">Agregar</span>
              </template>
              <input type="file" class="hidden" accept="image/*" @change="onFileSelect('espalda', $event)">
            </label>
          </div>
        </div>

        <!-- Save button -->
        <button @click="uploadPhotos" :disabled="uploading"
          class="mt-5 w-full rounded-full bg-wc-accent px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-wc-accent/20 transition-all hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60">
          {{ uploading ? 'Guardando...' : 'Guardar fotos' }}
        </button>
      </div>

      <!-- Comparison section -->
      <div v-if="firstDate && latestDate && firstDate !== latestDate" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h2 class="font-display text-lg tracking-wide text-wc-text">Comparacion de progreso</h2>
        <p class="mt-1 text-xs text-wc-text-tertiary">Semana 1 vs Actual</p>

        <div class="mt-4 grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <p class="text-center text-xs font-medium text-wc-text-secondary">{{ firstDate }}</p>
            <div class="aspect-[3/4] overflow-hidden rounded-lg border border-wc-border bg-wc-bg-secondary">
              <img v-if="photosByDate[photosByDate.length - 1]?.frente" :src="photosByDate[photosByDate.length - 1].frente_url || ('/uploads/photos/' + photosByDate[photosByDate.length - 1].frente)" alt="Foto inicial" class="h-full w-full object-cover" loading="lazy">
              <div v-else class="flex h-full w-full flex-col items-center justify-center text-wc-text-tertiary">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3v18m0-18h.257" />
                </svg>
                <span class="mt-1 text-xs">Sin foto</span>
              </div>
            </div>
          </div>

          <div class="space-y-2">
            <p class="text-center text-xs font-medium text-wc-text-secondary">{{ latestDate }}</p>
            <div class="aspect-[3/4] overflow-hidden rounded-lg border border-wc-border bg-wc-bg-secondary">
              <img v-if="photosByDate[0]?.frente" :src="photosByDate[0].frente_url || ('/uploads/photos/' + photosByDate[0].frente)" alt="Foto actual" class="h-full w-full object-cover" loading="lazy">
              <div v-else class="flex h-full w-full flex-col items-center justify-center text-wc-text-tertiary">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3v18m0-18h.257" />
                </svg>
                <span class="mt-1 text-xs">Sin foto</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Photo gallery by date -->
      <div v-for="group in photosByDate" :key="group.date" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div class="flex items-center justify-between">
          <h2 class="font-display text-base tracking-wide text-wc-text">{{ group.formatted }}</h2>
          <span class="rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-wc-accent">
            {{ [group.frente, group.perfil, group.espalda].filter(Boolean).length }}/3 fotos
          </span>
        </div>

        <div class="mt-4 grid grid-cols-3 gap-3">
          <div v-for="(tipo, label) in { frente: 'Frente', perfil: 'Perfil', espalda: 'Espalda' }" :key="tipo" class="space-y-1.5">
            <p class="text-center text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">{{ label }}</p>
            <div class="relative aspect-[3/4] overflow-hidden rounded-lg border border-wc-border bg-wc-bg-secondary">
              <img v-if="group[tipo]" :src="group[tipo + '_url'] || ('/uploads/photos/' + group[tipo])" :alt="label" class="h-full w-full object-cover" loading="lazy">
              <div v-else class="flex h-full w-full flex-col items-center justify-center text-wc-text-tertiary">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                <span class="mt-1 text-[10px]">Pendiente</span>
              </div>
            </div>
            <button v-if="group[tipo]" @click="deletePhoto(group[tipo + '_id'])"
              class="flex w-full items-center justify-center gap-1 rounded-lg border border-red-500/30 bg-red-500/10 py-1.5 text-[10px] font-medium text-red-400 transition-colors hover:bg-red-500/20 active:bg-red-500/30">
              <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
              </svg>
              Eliminar
            </button>
          </div>
        </div>
      </div>

      <!-- Empty gallery -->
      <div v-if="photosByDate.length === 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-wc-bg-secondary">
          <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18M3 3v18m0-18h.257" />
          </svg>
        </div>
        <h3 class="mt-4 font-display text-lg text-wc-text">Sin fotos de progreso</h3>
        <p class="mt-2 text-sm text-wc-text-tertiary">Aun no has subido fotos. Las fotos te ayudan a ver tu transformacion.</p>
      </div>
    </div>
  </RiseLayout>
</template>
