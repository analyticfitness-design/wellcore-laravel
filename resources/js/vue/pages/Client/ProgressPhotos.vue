<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();

// --- State ---
const loading = ref(true);
const error = ref(null);
const photos = ref({});            // { 'YYYY-MM-DD': [ { id, photo_date, tipo, filename, url } ] }

// Upload form
const uploadDate = ref(new Date().toISOString().split('T')[0]);
const uploadFiles = ref({ frente: null, lado: null, espalda: null });
const uploadPreviews = ref({ frente: null, lado: null, espalda: null });
const uploading = ref(false);
const uploadError = ref(null);
const fieldErrors = ref({});       // per-field 422 errors

// Achievement overlay
const showSuccess = ref(false);
const showConfetti = ref(false);
let confettiTimer = null;

// Gallery accordion
const selectedDate = ref(null);

// Comparison mode
const compareMode = ref(false);
const compareDate1 = ref('');
const compareDate2 = ref('');

// Deleting
const deletingId = ref(null);

// --- Constants ---
const ANGLES = ['frente', 'lado', 'espalda'];
const ANGLE_LABELS = { frente: 'Frente', lado: 'Lado', espalda: 'Espalda' };

// Translate raw Laravel validation keys (e.g. "validation.required") to Spanish
const VALIDATION_MESSAGES = {
  'validation.required': 'Selecciona una foto.',
  'validation.image': 'El archivo debe ser una imagen.',
  'validation.mimes': 'Formato de imagen no soportado.',
  'validation.max': 'La imagen es demasiado grande.',
  'validation.file': 'Archivo invalido.',
  'validation.date': 'Fecha invalida.',
};
function translateMessage(msg) {
  if (!msg) return '';
  if (typeof msg !== 'string') return String(msg);
  if (VALIDATION_MESSAGES[msg]) return VALIDATION_MESSAGES[msg];
  // Fallback: any "validation.*" key becomes generic
  if (msg.startsWith('validation.')) return 'Campo invalido.';
  return msg;
}
function fieldErrorFor(angle) {
  const raw = fieldErrors.value?.[angle]?.[0] || fieldErrors.value?.photo?.[0];
  return translateMessage(raw);
}

const CONFETTI_PIECES = [
  { left: '8%',  bg: '#DC2626', dur: '2.8s', delay: '0.1s', round: false },
  { left: '22%', bg: '#F59E0B', dur: '3.2s', delay: '0.3s', round: true },
  { left: '38%', bg: '#10B981', dur: '2.5s', delay: '0s',   round: false },
  { left: '52%', bg: '#DC2626', dur: '3s',   delay: '0.5s', round: true },
  { left: '65%', bg: '#8B5CF6', dur: '2.7s', delay: '0.2s', round: false },
  { left: '78%', bg: '#F59E0B', dur: '3.4s', delay: '0.4s', round: true },
  { left: '90%', bg: '#10B981', dur: '2.6s', delay: '0.15s',round: false },
  { left: '45%', bg: '#8B5CF6', dur: '3.1s', delay: '0.6s', round: false },
];

// --- Computed ---
const sortedDates = computed(() => {
  return Object.keys(photos.value).sort((a, b) => b.localeCompare(a));
});

const availableDates = computed(() => sortedDates.value);

const hasUploads = computed(() => {
  return Object.values(uploadFiles.value).some(f => f !== null);
});

const uploadedCount = computed(() => {
  return Object.values(uploadFiles.value).filter(f => f !== null).length;
});

// --- Fetch ---
async function fetchPhotos() {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.get('/api/v/client/photos');
    photos.value = response.data.photos || {};
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar fotos';
  } finally {
    loading.value = false;
  }
}

// --- File handling ---
function onFileSelect(angle, event) {
  const file = event.target.files?.[0];
  if (!file) return;
  fieldErrors.value[angle] = null;
  uploadFiles.value[angle] = file;

  // Preview via object URL
  if (uploadPreviews.value[angle]) {
    URL.revokeObjectURL(uploadPreviews.value[angle]);
  }
  uploadPreviews.value[angle] = URL.createObjectURL(file);
}

function removeFile(angle) {
  uploadFiles.value[angle] = null;
  if (uploadPreviews.value[angle]) {
    URL.revokeObjectURL(uploadPreviews.value[angle]);
    uploadPreviews.value[angle] = null;
  }
}

// --- Upload ---
async function uploadPhotos() {
  if (!hasUploads.value) {
    uploadError.value = 'Selecciona al menos una foto para subir.';
    return;
  }

  uploading.value = true;
  uploadError.value = null;
  fieldErrors.value = {};

  try {
    // The API expects one photo at a time with: photo_date, tipo, photo
    const entries = Object.entries(uploadFiles.value).filter(([, file]) => file !== null);

    for (const [angle, file] of entries) {
      const formData = new FormData();
      formData.append('photo_date', uploadDate.value);
      formData.append('tipo', angle);
      formData.append('photo', file);

      await api.post('/api/v/client/photos', formData);
    }

    // Reset form
    for (const angle of ANGLES) {
      removeFile(angle);
    }

    // Show achievement overlay
    showSuccess.value = true;
    showConfetti.value = true;
    confettiTimer = setTimeout(() => { showConfetti.value = false; }, 4000);

    // Refresh gallery
    await fetchPhotos();

    // Auto-expand the uploaded date
    selectedDate.value = uploadDate.value;
  } catch (err) {
    if (err.response?.status === 422) {
      fieldErrors.value = err.response.data.errors || {};
      uploadError.value = Object.values(err.response.data.errors || {}).flat().map(translateMessage).join(' ');
    } else {
      uploadError.value = err.response?.data?.message || err.response?.data?.error || 'Error al subir fotos';
    }
  } finally {
    uploading.value = false;
  }
}

function dismissSuccess() {
  showSuccess.value = false;
  showConfetti.value = false;
}

// --- Gallery ---
function toggleDate(date) {
  selectedDate.value = selectedDate.value === date ? null : date;
}

function getPhotosForDate(date) {
  return photos.value[date] || [];
}

function getPhotoByAngle(datePhotos, angle) {
  return datePhotos.find(p => p.tipo === angle) || null;
}

function photoCount(date) {
  const dp = photos.value[date] || [];
  return dp.length;
}

// --- Delete ---
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

// --- Helpers ---
function formatDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr + 'T12:00:00');
  return d.toLocaleDateString('es-CO', { day: 'numeric', month: 'short', year: 'numeric' });
}

function confettiStyle(piece) {
  return {
    left: piece.left,
    background: piece.bg,
    borderRadius: piece.round ? '50%' : '0',
    animation: `wc-confetti-fall ${piece.dur} ease-in forwards ${piece.delay}`,
  };
}

// --- Cleanup ---
onBeforeUnmount(() => {
  clearTimeout(confettiTimer);
  for (const angle of ANGLES) {
    if (uploadPreviews.value[angle]) {
      URL.revokeObjectURL(uploadPreviews.value[angle]);
    }
  }
});

onMounted(fetchPhotos);
</script>

<template>
  <ClientLayout>
    <!-- ===== ACHIEVEMENT OVERLAY ===== -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showSuccess"
          class="fixed inset-0 z-50 flex items-center justify-center p-4"
          style="background: rgba(0,0,0,0.85)"
          @keydown.escape.window="dismissSuccess"
        >
          <!-- Confetti -->
          <div v-show="showConfetti" class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <div
              v-for="(piece, idx) in CONFETTI_PIECES"
              :key="idx"
              class="wc-confetti"
              :style="confettiStyle(piece)"
            ></div>
          </div>

          <!-- Card -->
          <Transition
            enter-active-class="transition ease-out duration-400"
            enter-from-class="opacity-0 scale-90"
            enter-to-class="opacity-100 scale-100"
          >
            <div
              v-if="showSuccess"
              class="relative w-full max-w-sm overflow-hidden rounded-2xl text-center"
              style="background: linear-gradient(160deg, #0C1015 0%, #131F2B 50%, #0C1015 100%)"
              role="dialog"
              aria-modal="true"
              aria-labelledby="photos-success-title"
            >
              <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(ellipse at 50% -5%, rgba(255,255,255,0.08) 0%, transparent 60%)" aria-hidden="true"></div>

              <div class="relative z-10 p-8">
                <span class="wc-emoji-bounce block text-6xl mb-4" aria-hidden="true">📸</span>

                <div class="mb-3 flex items-center justify-center gap-2">
                  <span class="font-display text-xl tracking-[0.25em] text-white/90">WELLCORE</span>
                  <span class="h-2 w-2 rounded-full bg-white/30" aria-hidden="true"></span>
                </div>

                <h2 id="photos-success-title" class="font-sans text-2xl font-bold text-white mb-2">Fotos guardadas!</h2>

                <div class="my-5 rounded-xl border border-white/10 bg-white/[0.06] px-5 py-4">
                  <p class="font-data text-3xl font-bold text-white">+{{ uploadedCount || 3 }}</p>
                  <p class="mt-0.5 text-xs text-white/50">angulos registrados</p>
                </div>

                <p class="mb-6 text-sm text-white/70">Tu progreso queda registrado. La constancia transforma!</p>

                <button
                  @click="dismissSuccess"
                  class="w-full rounded-xl bg-wc-accent px-6 py-3 font-display text-lg tracking-wider text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-black"
                >
                  LISTO!
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>
    <!-- ===== /ACHIEVEMENT OVERLAY ===== -->

    <!-- Loading Skeleton -->
    <div v-if="loading" class="space-y-6">
      <div class="space-y-2">
        <div class="h-9 w-56 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
        <div class="h-5 w-72 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      </div>
      <div class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
        <div class="mb-5 flex items-center gap-3">
          <div class="h-10 w-10 animate-pulse rounded-lg bg-wc-bg-secondary"></div>
          <div class="space-y-1.5">
            <div class="h-5 w-32 rounded bg-wc-bg-secondary"></div>
            <div class="h-3 w-48 rounded bg-wc-bg-secondary"></div>
          </div>
        </div>
        <div class="h-8 w-40 rounded-lg bg-wc-bg-secondary mb-4"></div>
        <div class="grid grid-cols-3 gap-4">
          <div v-for="n in 3" :key="n" class="aspect-[3/4] animate-pulse rounded-xl border-2 border-dashed border-wc-border bg-wc-bg-secondary"></div>
        </div>
      </div>
      <div v-for="n in 2" :key="'sk-' + n" class="h-16 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex flex-col items-center justify-center py-20">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
        <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
      </div>
      <h2 class="mt-4 font-display text-xl tracking-wide text-wc-text">ERROR AL CARGAR</h2>
      <p class="mt-2 text-sm text-wc-text-secondary">{{ error }}</p>
      <button
        @click="fetchPhotos"
        class="mt-6 rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg"
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
          <p class="mt-1 text-sm text-wc-text-secondary">Registra tu transformacion con fotos periodicas</p>
        </div>
        <button
          v-if="availableDates.length >= 2"
          @click="compareMode = !compareMode"
          class="rounded-lg border px-3 py-2 text-sm font-medium transition-colors"
          :class="compareMode ? 'bg-wc-accent text-white border-wc-accent' : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'"
        >
          {{ compareMode ? 'Cerrar comparacion' : 'Comparar' }}
        </button>
      </div>

      <!-- ===== Upload Section ===== -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
        <!-- Section header with icon -->
        <div class="mb-5 flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
            </svg>
          </div>
          <div>
            <h2 class="font-display text-xl tracking-wide text-wc-text">SUBIR FOTOS</h2>
            <p class="text-sm text-wc-text-tertiary">Sube fotos de frente, lado y espalda</p>
          </div>
        </div>

        <form @submit.prevent="uploadPhotos">
          <!-- Date Picker -->
          <div class="mb-5">
            <label for="uploadDate" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Fecha</label>
            <input
              v-model="uploadDate"
              type="date"
              id="uploadDate"
              class="w-full max-w-xs rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
            />
            <p v-if="fieldErrors.photo_date" class="mt-1 text-xs text-red-500">{{ translateMessage(fieldErrors.photo_date[0]) }}</p>
          </div>

          <!-- Upload Zones: 3-column grid matching blade layout -->
          <div class="grid gap-4 sm:grid-cols-3">
            <div v-for="angle in ANGLES" :key="angle">
              <label
                :for="'photo-' + angle"
                class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-8 text-center transition-colors"
                :class="uploadFiles[angle]
                  ? 'border-green-500/50 bg-green-500/5'
                  : 'border-wc-border bg-wc-bg-secondary hover:border-wc-accent/50'"
              >
                <!-- File selected state -->
                <template v-if="uploadFiles[angle]">
                  <!-- Preview thumbnail -->
                  <img
                    v-if="uploadPreviews[angle]"
                    :src="uploadPreviews[angle]"
                    :alt="ANGLE_LABELS[angle]"
                    class="mb-2 h-16 w-16 rounded-lg object-cover"
                  />
                  <svg v-else class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                  </svg>
                  <p class="mt-2 text-sm font-medium text-green-400">Foto seleccionada</p>
                  <button
                    type="button"
                    @click.prevent.stop="removeFile(angle)"
                    class="mt-1 text-xs text-wc-text-tertiary hover:text-red-400 transition-colors"
                  >
                    Cambiar
                  </button>
                </template>

                <!-- Empty state -->
                <template v-else>
                  <svg class="h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                  </svg>
                  <p class="mt-2 text-sm font-medium text-wc-text-secondary">{{ ANGLE_LABELS[angle] }}</p>
                  <p class="mt-1 text-sm text-wc-text-tertiary">Click para seleccionar</p>
                </template>

                <input
                  :id="'photo-' + angle"
                  type="file"
                  accept="image/*"
                  class="hidden"
                  @change="onFileSelect(angle, $event)"
                />
              </label>
              <p v-if="fieldErrorFor(angle)" class="mt-1 text-xs text-red-500">
                {{ fieldErrorFor(angle) }}
              </p>
            </div>
          </div>

          <!-- General upload error -->
          <p v-if="uploadError" class="mt-3 text-sm text-red-500">{{ uploadError }}</p>

          <!-- Upload Button (right-aligned, matching blade) -->
          <div class="mt-5 flex justify-end">
            <button
              type="submit"
              :disabled="uploading || !hasUploads"
              class="flex items-center gap-2 rounded-xl bg-wc-accent px-6 py-3 text-sm font-semibold text-white transition-all hover:bg-red-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
            >
              <!-- Spinner -->
              <svg v-if="uploading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <!-- Upload icon -->
              <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
              </svg>
              <span>{{ uploading ? 'Subiendo...' : 'Subir Fotos' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- ===== Comparison Mode ===== -->
      <div v-if="compareMode && availableDates.length >= 2" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">COMPARACION LADO A LADO</h3>
        <div class="mb-4 grid grid-cols-2 gap-3">
          <div>
            <label class="mb-1 block text-sm font-medium text-wc-text-secondary">Fecha 1</label>
            <select v-model="compareDate1" class="w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20">
              <option value="">Seleccionar...</option>
              <option v-for="date in availableDates" :key="date" :value="date">{{ formatDate(date) }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-wc-text-secondary">Fecha 2</label>
            <select v-model="compareDate2" class="w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20">
              <option value="">Seleccionar...</option>
              <option v-for="date in availableDates" :key="date" :value="date">{{ formatDate(date) }}</option>
            </select>
          </div>
        </div>

        <div v-if="compareDate1 && compareDate2" class="grid grid-cols-2 gap-4">
          <div v-for="cDate in [compareDate1, compareDate2]" :key="cDate">
            <p class="mb-2 text-center text-sm font-semibold text-wc-text-tertiary">{{ formatDate(cDate) }}</p>
            <div class="grid grid-cols-1 gap-2">
              <div v-for="angle in ANGLES" :key="angle">
                <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary">
                  <template v-if="getPhotoByAngle(getPhotosForDate(cDate), angle)">
                    <img
                      :src="getPhotoByAngle(getPhotosForDate(cDate), angle).url"
                      :alt="ANGLE_LABELS[angle]"
                      class="aspect-[3/4] w-full object-cover"
                      loading="lazy"
                    />
                  </template>
                  <template v-else>
                    <div class="flex aspect-[3/4] w-full flex-col items-center justify-center bg-wc-bg-secondary">
                      <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                      </svg>
                      <p class="mt-2 text-sm text-wc-text-tertiary">Sin foto</p>
                    </div>
                  </template>
                  <div class="px-3 py-2 text-center">
                    <span class="text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">{{ ANGLE_LABELS[angle] }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== Photo Gallery (Accordion style matching Blade) ===== -->
      <template v-if="sortedDates.length > 0">
        <div class="space-y-4">
          <div
            v-for="date in sortedDates"
            :key="date"
            class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary"
          >
            <!-- Date Header (clickable accordion toggle) -->
            <button
              @click="toggleDate(date)"
              class="flex w-full items-center justify-between px-5 py-4 text-left transition-colors hover:bg-wc-bg-secondary"
            >
              <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                <span class="font-display text-lg tracking-wide text-wc-text">{{ formatDate(date) }}</span>
                <span class="rounded-full bg-wc-bg-secondary px-2 py-0.5 text-xs text-wc-text-tertiary">
                  {{ photoCount(date) }} {{ photoCount(date) === 1 ? 'foto' : 'fotos' }}
                </span>
              </div>
              <svg
                class="h-5 w-5 text-wc-text-tertiary transition-transform duration-200"
                :class="{ 'rotate-180': selectedDate === date }"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
              </svg>
            </button>

            <!-- Photos Grid (expanded) - 3-column by angle -->
            <Transition
              enter-active-class="transition-all duration-200 ease-out"
              enter-from-class="opacity-0 max-h-0"
              enter-to-class="opacity-100 max-h-[800px]"
              leave-active-class="transition-all duration-200 ease-in"
              leave-from-class="opacity-100 max-h-[800px]"
              leave-to-class="opacity-0 max-h-0"
            >
              <div v-if="selectedDate === date" class="border-t border-wc-border px-5 py-4 overflow-hidden">
                <div class="grid gap-4 sm:grid-cols-3">
                  <div v-for="angle in ANGLES" :key="angle" class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary">
                    <!-- Photo exists -->
                    <template v-if="getPhotoByAngle(getPhotosForDate(date), angle)">
                      <div class="group relative">
                        <img
                          :src="getPhotoByAngle(getPhotosForDate(date), angle).url"
                          :alt="ANGLE_LABELS[angle]"
                          class="aspect-[3/4] w-full object-cover"
                          loading="lazy"
                        />
                        <!-- Delete button on hover -->
                        <button
                          @click="deletePhoto(getPhotoByAngle(getPhotosForDate(date), angle).id)"
                          :disabled="deletingId === getPhotoByAngle(getPhotosForDate(date), angle).id"
                          class="absolute right-1.5 top-1.5 flex h-7 w-7 items-center justify-center rounded-full bg-black/50 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-red-500"
                          aria-label="Eliminar foto"
                        >
                          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                          </svg>
                        </button>
                      </div>
                    </template>

                    <!-- Placeholder: no photo for this angle -->
                    <template v-else>
                      <div class="flex aspect-[3/4] w-full flex-col items-center justify-center bg-wc-bg-secondary">
                        <svg class="h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        <p class="mt-2 text-sm text-wc-text-tertiary">Sin foto</p>
                      </div>
                    </template>

                    <!-- Angle label below photo/placeholder -->
                    <div class="px-3 py-2 text-center">
                      <span class="text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">{{ ANGLE_LABELS[angle] }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </Transition>
          </div>
        </div>
      </template>

      <!-- Empty gallery -->
      <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <svg class="mx-auto h-16 w-16 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
        </svg>
        <h3 class="mt-4 font-display text-xl text-wc-text">SIN FOTOS AUN</h3>
        <p class="mt-2 text-sm text-wc-text-secondary">Sube tu primera foto de progreso para empezar a registrar tu transformacion.</p>
      </div>
    </div>
  </ClientLayout>
</template>

<style scoped>
@keyframes wc-confetti-fall {
  0%   { transform: translateY(-20px) rotate(0deg); opacity: 1; }
  100% { transform: translateY(110vh) rotate(720deg); opacity: 0; }
}
.wc-confetti {
  position: absolute;
  top: -10px;
  width: 10px;
  height: 10px;
}
@keyframes wc-emoji-bounce {
  0%, 100% { transform: scale(1) rotate(-3deg); }
  50%       { transform: scale(1.15) rotate(3deg); }
}
.wc-emoji-bounce {
  animation: wc-emoji-bounce 2s ease-in-out infinite;
  display: inline-block;
}
</style>
