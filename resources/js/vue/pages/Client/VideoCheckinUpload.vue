<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-start justify-between">
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">VIDEO CHECK-IN</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">
          Sube tu video o foto de ejercicio para que tu coach analice tu técnica.
        </p>
      </div>

      <!-- Monthly usage badge -->
      <div
        v-if="!loading"
        :class="[
          'rounded-full px-3 py-1 text-xs font-semibold border',
          monthlyCount >= monthlyLimit
            ? 'bg-red-500/10 text-red-400 border-red-500/20'
            : monthlyCount >= monthlyLimit - 1
              ? 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20'
              : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
        ]"
      >
        {{ monthlyCount }}/{{ monthlyLimit }} este mes
      </div>
    </div>

    <!-- Success notification -->
    <Transition name="fade">
      <div
        v-if="showSuccess"
        class="flex items-center gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3"
      >
        <svg class="h-5 w-5 flex-shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <p class="text-sm font-medium text-emerald-400">
          Check-in enviado correctamente. Tu coach revisara pronto.
        </p>
      </div>
    </Transition>

    <!-- Upload limit warning -->
    <div
      v-if="!loading && monthlyCount >= monthlyLimit"
      class="rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3"
    >
      <p class="text-sm font-medium text-red-400">
        Has alcanzado el limite mensual de {{ monthlyLimit }} check-ins. Podras subir mas el proximo mes.
      </p>
    </div>

    <!-- Upload form card -->
    <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
      <h2 class="mb-4 font-display text-lg tracking-wide text-wc-text">NUEVO CHECK-IN</h2>

      <form @submit.prevent="submitCheckin" class="space-y-4">
        <!-- Drop zone -->
        <div
          @dragover.prevent="dragging = true"
          @dragleave.prevent="dragging = false"
          @drop.prevent="onDrop"
          :class="[
            'relative rounded-xl border-2 border-dashed p-6 text-center transition-colors duration-200',
            dragging
              ? 'border-wc-accent/60 bg-wc-accent/5'
              : selectedFile
                ? 'border-emerald-500/40 bg-emerald-500/5'
                : 'border-wc-border hover:border-wc-accent/40'
          ]"
        >
          <input
            type="file"
            ref="fileInputRef"
            accept="video/mp4,video/quicktime,video/webm,image/jpeg,image/png"
            @change="onFileSelect"
            class="absolute inset-0 cursor-pointer opacity-0"
          />

          <!-- Preview: image -->
          <div v-if="selectedFile && fileType === 'image'" class="flex flex-col items-center gap-3">
            <img
              :src="previewUrl"
              alt="Preview"
              class="max-h-48 rounded-lg object-contain"
            />
            <p class="text-xs text-wc-text-secondary">{{ selectedFile.name }}</p>
            <button
              type="button"
              @click.stop="clearFile"
              class="text-xs text-red-400 hover:text-red-300 transition-colors"
            >
              Quitar archivo
            </button>
          </div>

          <!-- Preview: video icon -->
          <div v-else-if="selectedFile && fileType === 'video'" class="flex flex-col items-center gap-3">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-wc-accent/10">
              <svg class="h-10 w-10 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.893L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                />
              </svg>
            </div>
            <p class="text-sm font-medium text-wc-text">{{ selectedFile.name }}</p>
            <p class="text-xs text-wc-text-tertiary">{{ formatFileSize(selectedFile.size) }}</p>
            <button
              type="button"
              @click.stop="clearFile"
              class="text-xs text-red-400 hover:text-red-300 transition-colors"
            >
              Quitar archivo
            </button>
          </div>

          <!-- Empty drop zone -->
          <div v-else class="flex flex-col items-center gap-3 py-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-wc-bg-tertiary">
              <svg class="h-7 w-7 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                />
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-wc-text">
                Arrastra tu archivo aqui o <span class="text-wc-accent">selecciona</span>
              </p>
              <p class="mt-1 text-xs text-wc-text-tertiary">MP4, MOV, WebM, JPG, PNG — max 100 MB</p>
            </div>
          </div>
        </div>

        <!-- Field error: file -->
        <p v-if="fieldErrors.media_file" class="text-xs text-red-400">
          {{ fieldErrors.media_file[0] }}
        </p>

        <!-- Exercise name -->
        <div>
          <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
            Ejercicio <span class="text-wc-accent">*</span>
          </label>
          <input
            v-model="exerciseName"
            type="text"
            placeholder="Ej: Sentadilla trasera, Peso muerto..."
            class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent/60 focus:outline-none focus:ring-1 focus:ring-wc-accent/30 transition-colors"
          />
          <p v-if="fieldErrors.exercise_name" class="mt-1 text-xs text-red-400">
            {{ fieldErrors.exercise_name[0] }}
          </p>
          <p v-else-if="formErrors.exerciseName" class="mt-1 text-xs text-red-400">
            {{ formErrors.exerciseName }}
          </p>
        </div>

        <!-- Notes -->
        <div>
          <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
            Notas (opcional)
          </label>
          <textarea
            v-model="notes"
            rows="3"
            placeholder="Contexto adicional, dudas especificas, sensaciones..."
            class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent/60 focus:outline-none focus:ring-1 focus:ring-wc-accent/30 transition-colors"
          />
          <p v-if="fieldErrors.notes" class="mt-1 text-xs text-red-400">
            {{ fieldErrors.notes[0] }}
          </p>
        </div>

        <!-- Submit button -->
        <button
          type="submit"
          :disabled="submitting || monthlyCount >= monthlyLimit"
          class="flex w-full items-center justify-center gap-2 rounded-lg bg-wc-accent px-6 py-3 font-display tracking-wider text-white transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
        >
          <svg v-if="submitting" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
          </svg>
          <span>{{ submitting ? 'ENVIANDO...' : 'ENVIAR CHECK-IN' }}</span>
        </button>
      </form>
    </div>

    <!-- Loading skeletons -->
    <template v-if="loading">
      <div v-for="n in 3" :key="n" class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary h-20" />
    </template>

    <!-- Historial -->
    <template v-else>
      <div class="rounded-xl border border-wc-border bg-wc-bg-secondary overflow-hidden">
        <div class="px-6 py-4 border-b border-wc-border">
          <h2 class="font-display text-lg tracking-wide text-wc-text">HISTORIAL</h2>
        </div>

        <!-- Empty state -->
        <div
          v-if="checkins.length === 0"
          class="px-6 py-12 text-center"
        >
          <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-wc-bg-tertiary">
            <svg class="h-7 w-7 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.893L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
              />
            </svg>
          </div>
          <p class="text-sm text-wc-text-secondary">Aun no tienes check-ins. Sube tu primero.</p>
        </div>

        <!-- Checkin list -->
        <div v-else class="divide-y divide-wc-border">
          <div v-for="checkin in checkins" :key="checkin.id">
            <!-- Accordion header -->
            <button
              type="button"
              @click="toggleExpanded(checkin.id)"
              class="flex w-full items-center gap-4 px-6 py-4 text-left hover:bg-wc-bg-tertiary/50 transition-colors"
              :class="{
                'border-l-2': true,
                'border-yellow-500': checkin.status === 'pending',
                'border-emerald-500': checkin.status === 'coach_reviewed',
                'border-blue-500': checkin.status === 'ai_reviewed',
              }"
            >
              <!-- Media type icon -->
              <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-wc-bg-tertiary">
                <svg v-if="checkin.media_type === 'video'" class="h-5 w-5 text-wc-text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.893L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                  />
                </svg>
                <svg v-else class="h-5 w-5 text-wc-text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
              </div>

              <!-- Info -->
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-wc-text">{{ checkin.exercise_name }}</p>
                <p class="text-xs text-wc-text-tertiary">{{ formatDate(checkin.created_at) }}</p>
              </div>

              <!-- Status badge -->
              <div class="flex items-center gap-2">
                <span
                  :class="statusBadgeClass(checkin.status)"
                  class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                >
                  {{ statusLabel(checkin.status) }}
                </span>
                <!-- Chevron -->
                <svg
                  class="h-4 w-4 text-wc-text-tertiary transition-transform duration-200"
                  :class="expandedId === checkin.id ? 'rotate-180' : ''"
                  fill="none" viewBox="0 0 24 24" stroke="currentColor"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </button>

            <!-- Accordion content — v-show to preserve video state -->
            <div v-show="expandedId === checkin.id" class="border-t border-wc-border bg-wc-bg-tertiary/30 px-6 py-5 space-y-4">
              <!-- Video media -->
              <video
                v-if="checkin.media_type === 'video'"
                :src="`/storage/${checkin.media_url}`"
                controls
                preload="metadata"
                class="w-full max-h-72 rounded-lg object-contain bg-black"
              />
              <!-- Image media -->
              <img
                v-else
                :src="`/storage/${checkin.media_url}`"
                :alt="checkin.exercise_name"
                class="max-h-72 w-full rounded-lg object-contain"
              />

              <!-- Notes -->
              <div v-if="checkin.notes" class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
                <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Tus notas</p>
                <p class="text-sm text-wc-text-secondary">{{ checkin.notes }}</p>
              </div>

              <!-- Coach response -->
              <div v-if="checkin.coach_response" class="rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-3">
                <div class="mb-1 flex items-center gap-1.5">
                  <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">
                    Coach
                  </span>
                  <span v-if="checkin.responded_at" class="text-[10px] text-wc-text-tertiary">
                    {{ formatDate(checkin.responded_at) }}
                  </span>
                </div>
                <p class="text-sm text-wc-text-secondary">{{ checkin.coach_response }}</p>
              </div>

              <!-- AI response -->
              <div v-if="checkin.ai_response" class="rounded-lg border border-blue-500/20 bg-blue-500/5 p-3">
                <div class="mb-1 flex items-center gap-1.5">
                  <span class="rounded-full bg-blue-500/10 px-2 py-0.5 text-[10px] font-semibold text-blue-400">
                    IA
                  </span>
                </div>
                <p class="text-sm text-wc-text-secondary">{{ checkin.ai_response }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useApi } from '../../composables/useApi';

// -------------------------------------------------------------------
// Types
// -------------------------------------------------------------------
interface VideoCheckin {
  id: number;
  media_type: 'video' | 'image';
  media_url: string;
  exercise_name: string;
  notes: string | null;
  status: 'pending' | 'coach_reviewed' | 'ai_reviewed';
  coach_response: string | null;
  ai_response: string | null;
  created_at: string;
  responded_at: string | null;
}

// -------------------------------------------------------------------
// Module-level non-reactive handles
// -------------------------------------------------------------------
let successTimer: ReturnType<typeof setTimeout> | null = null;

// -------------------------------------------------------------------
// State
// -------------------------------------------------------------------
const api = useApi();

const loading = ref(false);
const submitting = ref(false);
const showSuccess = ref(false);

const checkins = ref<VideoCheckin[]>([]);
const monthlyCount = ref(0);
const monthlyLimit = ref(4);

const dragging = ref(false);
const selectedFile = ref<File | null>(null);
const fileType = ref<'image' | 'video'>('video');
const previewUrl = ref<string | null>(null);

const exerciseName = ref('');
const notes = ref('');
const expandedId = ref<number | null>(null);

const fieldErrors = ref<Record<string, string[]>>({});
const formErrors = ref<{ exerciseName?: string }>({});

// Template ref — typed via useTemplateRef pattern with plain ref
const fileInputRef = ref<HTMLInputElement | null>(null);

// -------------------------------------------------------------------
// Fetch
// -------------------------------------------------------------------
async function fetchCheckins() {
  loading.value = true;
  try {
    const response = await api.get('/api/v/client/video-checkins');
    checkins.value = response.data.checkins ?? [];
    monthlyCount.value = response.data.monthly_count ?? 0;
    monthlyLimit.value = response.data.monthly_limit ?? 4;
  } catch {
    checkins.value = [];
  } finally {
    loading.value = false;
  }
}

// -------------------------------------------------------------------
// File handling
// -------------------------------------------------------------------
function detectFileType(file: File): 'image' | 'video' {
  const ext = file.name.split('.').pop()?.toLowerCase() ?? '';
  return ['jpg', 'jpeg', 'png'].includes(ext) ? 'image' : 'video';
}

function setFile(file: File) {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value);
  }
  selectedFile.value = file;
  fileType.value = detectFileType(file);
  previewUrl.value = URL.createObjectURL(file);
  fieldErrors.value.media_file = [];
}

function clearFile() {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = null;
  }
  selectedFile.value = null;
  if (fileInputRef.value) {
    fileInputRef.value.value = '';
  }
}

function onFileSelect(e: Event) {
  const input = e.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;
  setFile(file);
}

function onDrop(e: DragEvent) {
  dragging.value = false;
  const file = e.dataTransfer?.files[0];
  if (!file) return;
  setFile(file);
}

// -------------------------------------------------------------------
// Submit
// -------------------------------------------------------------------
function validateForm(): boolean {
  formErrors.value = {};
  if (!exerciseName.value.trim()) {
    formErrors.value.exerciseName = 'El nombre del ejercicio es obligatorio.';
    return false;
  }
  return true;
}

async function submitCheckin() {
  if (monthlyCount.value >= monthlyLimit.value) return;
  if (!validateForm()) return;

  fieldErrors.value = {};
  submitting.value = true;

  try {
    const formData = new FormData();
    if (selectedFile.value) {
      formData.append('media_file', selectedFile.value);
    }
    formData.append('exercise_name', exerciseName.value.trim());
    if (notes.value.trim()) {
      formData.append('notes', notes.value.trim());
    }

    await api.post('/api/v/client/video-checkin', formData);

    // Reset form
    exerciseName.value = '';
    notes.value = '';
    clearFile();

    // Show success, auto-dismiss after 5 s
    showSuccess.value = true;
    if (successTimer) clearTimeout(successTimer);
    successTimer = setTimeout(() => {
      showSuccess.value = false;
    }, 5000);

    // Refresh list
    await fetchCheckins();
  } catch (err: any) {
    if (err.response?.status === 422) {
      fieldErrors.value = err.response.data.errors ?? {};
    }
  } finally {
    submitting.value = false;
  }
}

// -------------------------------------------------------------------
// Accordion
// -------------------------------------------------------------------
function toggleExpanded(id: number) {
  expandedId.value = expandedId.value === id ? null : id;
}

// -------------------------------------------------------------------
// Helpers
// -------------------------------------------------------------------
function formatDate(iso: string): string {
  return new Date(iso).toLocaleDateString('es-CO', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

function formatFileSize(bytes: number): string {
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function statusLabel(status: VideoCheckin['status']): string {
  const labels: Record<VideoCheckin['status'], string> = {
    pending: 'Pendiente',
    coach_reviewed: 'Revisado',
    ai_reviewed: 'IA revisado',
  };
  return labels[status] ?? status;
}

function statusBadgeClass(status: VideoCheckin['status']): string {
  const classes: Record<VideoCheckin['status'], string> = {
    pending: 'bg-yellow-500/10 text-yellow-500',
    coach_reviewed: 'bg-emerald-500/10 text-emerald-500',
    ai_reviewed: 'bg-blue-500/10 text-blue-400',
  };
  return classes[status] ?? '';
}

// -------------------------------------------------------------------
// Lifecycle
// -------------------------------------------------------------------
onMounted(fetchCheckins);

onBeforeUnmount(() => {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
  if (successTimer) clearTimeout(successTimer);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
