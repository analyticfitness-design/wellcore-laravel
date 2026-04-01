<script setup>
import { ref, computed, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();

// State
const loading = ref(true);
const error = ref(null);
const photoGroups = ref([]);

// Upload form
const uploadDate = ref(new Date().toISOString().split('T')[0]);
const uploadFiles = ref({ frente: null, lado: null, espalda: null });
const uploadPreviews = ref({ frente: null, lado: null, espalda: null });
const uploading = ref(false);
const uploadError = ref(null);
const showSuccess = ref(false);

// Comparison
const compareMode = ref(false);
const compareDate1 = ref('');
const compareDate2 = ref('');

// Deleting
const deletingId = ref(null);

// Fetch photos
async function fetchPhotos() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/client/photos');
        const d = response.data;
        photoGroups.value = d.photos || d.groups || [];
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar fotos';
    } finally {
        loading.value = false;
    }
}

// Handle file select
function onFileSelect(angle, event) {
    const file = event.target.files?.[0];
    if (!file) return;
    uploadFiles.value[angle] = file;

    // Preview
    const reader = new FileReader();
    reader.onload = (e) => {
        uploadPreviews.value[angle] = e.target.result;
    };
    reader.readAsDataURL(file);
}

// Remove file
function removeFile(angle) {
    uploadFiles.value[angle] = null;
    uploadPreviews.value[angle] = null;
}

// Upload photos
async function uploadPhotos() {
    const hasFiles = Object.values(uploadFiles.value).some(f => f !== null);
    if (!hasFiles) return;

    uploading.value = true;
    uploadError.value = null;

    try {
        const formData = new FormData();
        formData.append('date', uploadDate.value);
        for (const [angle, file] of Object.entries(uploadFiles.value)) {
            if (file) {
                formData.append(angle, file);
            }
        }

        await api.post('/api/v/client/photos', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        // Reset form
        uploadFiles.value = { frente: null, lado: null, espalda: null };
        uploadPreviews.value = { frente: null, lado: null, espalda: null };
        showSuccess.value = true;
        setTimeout(() => { showSuccess.value = false; }, 3000);

        // Refresh data
        await fetchPhotos();
    } catch (err) {
        uploadError.value = err.response?.data?.message || 'Error al subir fotos';
    } finally {
        uploading.value = false;
    }
}

// Delete photo
async function deletePhoto(photoId) {
    if (!confirm('Seguro que deseas eliminar esta foto?')) return;
    deletingId.value = photoId;
    try {
        await api.delete(`/api/v/client/photos/${photoId}`);
        await fetchPhotos();
    } catch {
        // Fail silently
    } finally {
        deletingId.value = null;
    }
}

// Available dates for comparison
const availableDates = computed(() => {
    return photoGroups.value.map(g => g.date).filter(Boolean);
});

// Get photos for a comparison date
function getPhotosForDate(date) {
    return photoGroups.value.find(g => g.date === date)?.photos || [];
}

// Helpers
function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr + 'T12:00:00');
    return d.toLocaleDateString('es-CO', { day: 'numeric', month: 'long', year: 'numeric' });
}

const angleLabels = { frente: 'Frente', lado: 'Lado', espalda: 'Espalda' };

const hasUploads = computed(() => {
    return Object.values(uploadFiles.value).some(f => f !== null);
});

onMounted(() => {
    fetchPhotos();
});
</script>

<template>
  <ClientLayout>
    <!-- Loading Skeleton -->
    <div v-if="loading" class="space-y-6">
      <div class="space-y-2">
        <div class="h-9 w-56 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
        <div class="h-5 w-72 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      </div>
      <div class="h-64 animate-pulse rounded-2xl border border-wc-border bg-wc-bg-tertiary"></div>
      <div class="grid grid-cols-3 gap-3">
        <div v-for="i in 3" :key="i" class="aspect-[3/4] animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex flex-col items-center justify-center py-20">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
        <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
      </div>
      <h2 class="mt-4 font-display text-xl tracking-wide text-wc-text">Error al cargar</h2>
      <p class="mt-2 text-sm text-wc-text-secondary">{{ error }}</p>
      <button
        @click="fetchPhotos"
        class="mt-6 rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg"
      >
        Reintentar
      </button>
    </div>

    <!-- Content -->
    <div v-else class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text">FOTOS DE PROGRESO</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">Documenta tu transformacion con fotos periodicas</p>
        </div>
        <button
          v-if="availableDates.length >= 2"
          @click="compareMode = !compareMode"
          class="rounded-lg border border-wc-border px-3 py-2 text-sm font-medium transition-colors"
          :class="compareMode ? 'bg-wc-accent text-white border-wc-accent' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'"
        >
          {{ compareMode ? 'Cerrar comparacion' : 'Comparar' }}
        </button>
      </div>

      <!-- Success alert -->
      <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 -translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showSuccess" class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-center">
          <svg class="mx-auto h-8 w-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          <p class="mt-2 text-sm font-semibold text-emerald-400">Fotos guardadas exitosamente!</p>
        </div>
      </Transition>

      <!-- Upload Form -->
      <div class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h3 class="mb-4 font-semibold text-wc-text">Subir fotos</h3>

        <!-- Date selector -->
        <div class="mb-4">
          <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Fecha</label>
          <input
            v-model="uploadDate"
            type="date"
            class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent/50 focus:outline-none sm:w-48"
          />
        </div>

        <!-- Photo upload slots -->
        <div class="mb-4 grid grid-cols-3 gap-3">
          <div v-for="angle in ['frente', 'lado', 'espalda']" :key="angle">
            <label class="mb-1 block text-center text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
              {{ angleLabels[angle] }}
            </label>

            <!-- Preview -->
            <div v-if="uploadPreviews[angle]" class="group relative aspect-[3/4] overflow-hidden rounded-xl border border-wc-accent/30 bg-wc-bg">
              <img :src="uploadPreviews[angle]" :alt="`Preview ${angle}`" class="h-full w-full object-cover" />
              <button
                @click="removeFile(angle)"
                class="absolute right-1.5 top-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-black/60 text-white opacity-0 transition-opacity group-hover:opacity-100"
                aria-label="Eliminar"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Upload slot -->
            <label
              v-else
              class="flex aspect-[3/4] cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-wc-border bg-wc-bg transition-colors hover:border-wc-accent/50"
            >
              <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
              </svg>
              <span class="mt-1 text-[10px] text-wc-text-tertiary">Toca para subir</span>
              <input type="file" accept="image/*" class="hidden" @change="onFileSelect(angle, $event)" />
            </label>
          </div>
        </div>

        <!-- Upload error -->
        <p v-if="uploadError" class="mb-3 text-xs text-red-400">{{ uploadError }}</p>

        <!-- Upload button -->
        <button
          @click="uploadPhotos"
          :disabled="uploading || !hasUploads"
          class="w-full rounded-xl bg-wc-accent py-3 text-sm font-semibold text-white transition-colors hover:bg-wc-accent/90 disabled:opacity-50"
        >
          <span v-if="uploading" class="flex items-center justify-center gap-2">
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Subiendo...
          </span>
          <span v-else>Guardar fotos</span>
        </button>
      </div>

      <!-- Comparison Mode -->
      <div v-if="compareMode && availableDates.length >= 2" class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h3 class="mb-4 font-semibold text-wc-text">Comparacion lado a lado</h3>
        <div class="mb-4 grid grid-cols-2 gap-3">
          <div>
            <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Fecha 1</label>
            <select v-model="compareDate1" class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:outline-none">
              <option value="">Seleccionar...</option>
              <option v-for="date in availableDates" :key="date" :value="date">{{ formatDate(date) }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Fecha 2</label>
            <select v-model="compareDate2" class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:outline-none">
              <option value="">Seleccionar...</option>
              <option v-for="date in availableDates" :key="date" :value="date">{{ formatDate(date) }}</option>
            </select>
          </div>
        </div>

        <div v-if="compareDate1 && compareDate2" class="grid grid-cols-2 gap-4">
          <div>
            <p class="mb-2 text-center text-xs font-semibold text-wc-text-tertiary">{{ formatDate(compareDate1) }}</p>
            <div class="grid grid-cols-1 gap-2">
              <div v-for="photo in getPhotosForDate(compareDate1)" :key="photo.id" class="aspect-[3/4] overflow-hidden rounded-xl border border-wc-border">
                <img :src="photo.url" :alt="photo.angle" class="h-full w-full object-cover" />
              </div>
            </div>
          </div>
          <div>
            <p class="mb-2 text-center text-xs font-semibold text-wc-text-tertiary">{{ formatDate(compareDate2) }}</p>
            <div class="grid grid-cols-1 gap-2">
              <div v-for="photo in getPhotosForDate(compareDate2)" :key="photo.id" class="aspect-[3/4] overflow-hidden rounded-xl border border-wc-border">
                <img :src="photo.url" :alt="photo.angle" class="h-full w-full object-cover" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Photo Gallery -->
      <div v-if="photoGroups.length > 0">
        <h3 class="mb-3 font-display text-xl tracking-wide text-wc-text">GALERIA</h3>

        <div v-for="group in photoGroups" :key="group.date" class="mb-6">
          <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ formatDate(group.date) }}</p>
          <div class="grid grid-cols-3 gap-2">
            <div
              v-for="photo in group.photos"
              :key="photo.id"
              class="group relative aspect-[3/4] overflow-hidden rounded-xl border border-wc-border bg-wc-bg"
            >
              <img :src="photo.url" :alt="photo.angle" class="h-full w-full object-cover" loading="lazy" />
              <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                <span class="text-[10px] font-medium uppercase text-white/80">{{ photo.angle }}</span>
              </div>
              <button
                @click="deletePhoto(photo.id)"
                :disabled="deletingId === photo.id"
                class="absolute right-1.5 top-1.5 flex h-7 w-7 items-center justify-center rounded-full bg-black/50 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-red-500"
                aria-label="Eliminar foto"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty gallery -->
      <div v-else class="rounded-2xl border border-dashed border-wc-border bg-wc-bg-tertiary/50 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
        </svg>
        <h3 class="mt-4 font-display text-xl text-wc-text">SIN FOTOS AUN</h3>
        <p class="mx-auto mt-2 max-w-xs text-sm text-wc-text-secondary">Sube tu primera foto de progreso para documentar tu transformacion.</p>
      </div>
    </div>
  </ClientLayout>
</template>
