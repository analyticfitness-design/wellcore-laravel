<script setup lang="ts">
import { ref, computed, onBeforeUnmount } from 'vue';
import { useApi } from '../../../composables/useApi';
import { useToast } from '../../../composables/useToast';
import PulsoStatCard from './PulsoStatCard.vue';

interface StatsOverlay {
  volume_kg?: number;
  series?: number;
  ejercicios?: number;
  duracion_min?: number;
  day_name?: string;
}

const props = withDefaults(defineProps<{
  prefillType?: string;
  prefillStats?: StatsOverlay | null;
  prefillSessionId?: number | null;
}>(), {
  prefillType: 'libre',
  prefillStats: null,
  prefillSessionId: null,
});

const emit = defineEmits<{
  close: [];
  created: [id: number];
}>();

const api = useApi();
const toast = useToast();

// State
const pulsoType = ref<string>(props.prefillType);
const caption = ref<string>('');
const mediaFile = ref<File | null>(null);
const mediaPreviewUrl = ref<string | null>(null);
const uploading = ref<boolean>(false);

// Static config — outside reactive scope
const PULSO_TYPES = [
  { value: 'entrenamiento', emoji: '🔥', label: 'Entrenamiento' },
  { value: 'pr',            emoji: '🏆', label: 'Nuevo PR'      },
  { value: 'nutricion',     emoji: '🥗', label: 'Nutrición'     },
  { value: 'recuperacion',  emoji: '😴', label: 'Recuperación'  },
  { value: 'logro',         emoji: '🏅', label: 'Logro'         },
  { value: 'libre',         emoji: '📸', label: 'Libre'         },
];

const ALLOWED_MIMES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'video/mp4', 'video/quicktime'];
const MAX_BYTES = 30 * 1024 * 1024; // 30 MB

// Computed
const isVideo = computed(() => {
  if (!mediaFile.value) return false;
  return ['video/mp4', 'video/quicktime'].includes(mediaFile.value.type);
});

const hasStats = computed(() => {
  if (!props.prefillStats) return false;
  return Object.values(props.prefillStats).some(Boolean);
});

// File handling
function handleFileSelect(e: Event) {
  const input = e.target as HTMLInputElement;
  const file = input.files?.[0] ?? null;
  if (!file) return;
  // Reset so same file can be selected again
  input.value = '';
  applyFile(file);
}

function onDrop(e: DragEvent) {
  dragging.value = false;
  const file = e.dataTransfer?.files?.[0] ?? null;
  if (!file) return;
  applyFile(file);
}

function applyFile(file: File) {
  if (!ALLOWED_MIMES.includes(file.type)) {
    toast.error('Formato no válido. Usa JPG, PNG, WebP o MP4/MOV.');
    return;
  }
  if (file.size > MAX_BYTES) {
    toast.error('El archivo excede el límite de 30 MB.');
    return;
  }
  // Revoke previous object URL before creating a new one
  if (mediaPreviewUrl.value) {
    URL.revokeObjectURL(mediaPreviewUrl.value);
    mediaPreviewUrl.value = null;
  }
  mediaFile.value = file;
  mediaPreviewUrl.value = URL.createObjectURL(file);
}

function removeMedia() {
  if (mediaPreviewUrl.value) {
    URL.revokeObjectURL(mediaPreviewUrl.value);
    mediaPreviewUrl.value = null;
  }
  mediaFile.value = null;
}

onBeforeUnmount(() => {
  if (mediaPreviewUrl.value) {
    URL.revokeObjectURL(mediaPreviewUrl.value);
  }
});

// Drag state — module-level would be fine but reactive needed for template binding
const dragging = ref(false);

// Submit
async function submit() {
  if (uploading.value) return;
  uploading.value = true;

  try {
    const formData = new FormData();
    formData.append('pulso_type', pulsoType.value);
    formData.append('caption', caption.value.trim());

    if (mediaFile.value) {
      formData.append('media', mediaFile.value);
    }

    if (props.prefillSessionId) {
      formData.append('workout_session_id', String(props.prefillSessionId));
      formData.append('is_auto_generated', '1');
    }

    if (props.prefillStats && hasStats.value) {
      for (const [key, value] of Object.entries(props.prefillStats)) {
        if (value !== undefined && value !== null) {
          formData.append(`stats_overlay[${key}]`, String(value));
        }
      }
    }

    const response = await api.post('/api/v/client/pulsos', formData);
    const id: number = response.data?.pulso?.id ?? response.data?.id;

    toast.success('Pulso publicado. Disponible por 24 horas.');
    emit('created', id);
  } catch (err: any) {
    toast.apiError(err, 'No pudimos publicar tu pulso. Intenta de nuevo.');
  } finally {
    uploading.value = false;
  }
}
</script>

<template>
  <!-- Backdrop -->
  <Transition name="fade">
    <div
      class="fixed inset-0 z-50 flex items-end justify-center bg-black/70 backdrop-blur-sm sm:items-center"
      @click.self="emit('close')"
    >
      <!-- Bottom-sheet panel -->
      <Transition name="slide-up">
        <div
          class="relative w-full max-w-lg overflow-hidden rounded-t-2xl border border-wc-border bg-wc-bg-secondary sm:rounded-2xl"
          style="max-height: 92dvh; overflow-y: auto;"
        >
          <!-- Handle bar (mobile) -->
          <div class="flex justify-center pt-3 sm:hidden">
            <div class="h-1 w-10 rounded-full bg-wc-border"></div>
          </div>

          <!-- Header -->
          <div class="flex items-center justify-between px-5 pb-2 pt-4">
            <h2 class="font-display text-2xl tracking-wide text-wc-text">NUEVO PULSO</h2>
            <button
              type="button"
              class="rounded-lg p-1.5 text-wc-text-tertiary transition-colors hover:bg-wc-bg-tertiary hover:text-wc-text"
              @click="emit('close')"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="space-y-4 px-5 pb-6 pt-2">

            <!-- Type selector -->
            <div>
              <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">Tipo de momento</p>
              <div class="grid grid-cols-3 gap-2">
                <button
                  v-for="t in PULSO_TYPES"
                  :key="t.value"
                  type="button"
                  :class="[
                    'flex flex-col items-center gap-1 rounded-xl border px-2 py-3 text-xs font-semibold transition-all',
                    pulsoType === t.value
                      ? 'border-wc-accent bg-wc-accent/10 text-wc-accent'
                      : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:border-wc-accent/40 hover:text-wc-text',
                  ]"
                  @click="pulsoType = t.value"
                >
                  <span class="text-xl">{{ t.emoji }}</span>
                  <span class="leading-tight">{{ t.label }}</span>
                </button>
              </div>
            </div>

            <!-- Preview area -->
            <div v-if="mediaPreviewUrl || hasStats" class="overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary">
              <!-- Video preview -->
              <div v-if="mediaPreviewUrl && isVideo" class="relative">
                <video
                  :src="mediaPreviewUrl"
                  class="max-h-56 w-full object-cover"
                  controls
                  muted
                  playsinline
                ></video>
                <button
                  type="button"
                  class="absolute right-2 top-2 rounded-full bg-black/60 p-1.5 text-white transition-opacity hover:bg-black/80"
                  @click="removeMedia"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <!-- Image preview -->
              <div v-else-if="mediaPreviewUrl && !isVideo" class="relative">
                <img
                  :src="mediaPreviewUrl"
                  class="max-h-56 w-full object-cover"
                  alt="Vista previa"
                />
                <button
                  type="button"
                  class="absolute right-2 top-2 rounded-full bg-black/60 p-1.5 text-white transition-opacity hover:bg-black/80"
                  @click="removeMedia"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <!-- Stats card preview (no media selected, but prefillStats present) -->
              <div v-if="!mediaPreviewUrl && hasStats" class="p-3">
                <PulsoStatCard
                  :pulso-type="pulsoType"
                  :caption="caption || undefined"
                  :stats="prefillStats"
                />
              </div>

              <!-- Stats overlay below media -->
              <div v-if="mediaPreviewUrl && hasStats" class="border-t border-wc-border p-3">
                <PulsoStatCard
                  :pulso-type="pulsoType"
                  :caption="caption || undefined"
                  :stats="prefillStats"
                  :compact="true"
                />
              </div>
            </div>

            <!-- File drop zone (shown when no media selected) -->
            <div v-if="!mediaPreviewUrl">
              <div
                :class="[
                  'relative rounded-xl border-2 border-dashed p-6 text-center transition-colors',
                  dragging
                    ? 'border-wc-accent/60 bg-wc-accent/5'
                    : 'border-wc-border hover:border-wc-accent/40',
                ]"
                @dragover.prevent="dragging = true"
                @dragleave.prevent="dragging = false"
                @drop.prevent="onDrop"
              >
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime"
                  class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                  @change="handleFileSelect"
                />
                <svg class="mx-auto mb-2 h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                <p class="text-sm font-medium text-wc-text-secondary">Arrastra una foto o video</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">JPG, PNG, WebP, MP4, MOV — max 30 MB</p>
              </div>
            </div>

            <!-- Caption textarea -->
            <div>
              <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">
                Descripcion (opcional)
              </label>
              <textarea
                v-model="caption"
                rows="3"
                maxlength="280"
                placeholder="Cuenta algo sobre este momento..."
                class="w-full resize-none rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary outline-none transition-colors focus:border-wc-accent/60 focus:ring-1 focus:ring-wc-accent/30"
              ></textarea>
              <p class="mt-1 text-right text-[10px] text-wc-text-tertiary">{{ caption.length }}/280</p>
            </div>

            <!-- Submit button -->
            <button
              type="button"
              :disabled="uploading"
              class="flex w-full items-center justify-center gap-2 rounded-xl bg-wc-accent px-6 py-3.5 text-sm font-bold uppercase tracking-widest text-white transition-all hover:bg-wc-accent/90 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
              @click="submit"
            >
              <template v-if="uploading">
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Publicando...
              </template>
              <template v-else>
                <span>⚡</span>
                Publicar Pulso
              </template>
            </button>

          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}

@media (min-width: 640px) {
  .slide-up-enter-from,
  .slide-up-leave-to {
    transform: translateY(1rem) scale(0.97);
    opacity: 0;
  }
}
</style>
