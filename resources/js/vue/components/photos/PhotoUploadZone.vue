<script setup>
/**
 * PhotoUploadZone — single-angle dropzone with empty / preview / uploading
 * / error states. Stateless wrt upload logic — wires into usePhotoUpload via
 * props + emits so the parent owns the composable instance.
 *
 * Props:
 *   angle:       'frente' | 'perfil' | 'espalda'
 *   label:       string  display label (default by angle)
 *   file:        File | null
 *   previewUrl:  string | null
 *   error:       string  (already translated)
 *   uploading:   bool
 *   chips:       { lighting, framing } | null   (optional validation chips)
 *
 * Emits:
 *   select(event)       native input change
 *   drop(file)          file dropped
 *   remove
 *
 * NO HEIC dynamic import here — that lives in usePhotoUpload composable.
 * Parent decides whether to call setFile(angle, droppedFile) directly.
 */
import { computed, ref } from 'vue';
import PhotoValidationChips from './PhotoValidationChips.vue';
import PhotoFlashAlert from './PhotoFlashAlert.vue';

const props = defineProps({
  angle: { type: String, required: true },
  label: { type: String, default: '' },
  file: { type: Object, default: null }, // File instance
  previewUrl: { type: String, default: null },
  error: { type: String, default: '' },
  uploading: { type: Boolean, default: false },
  chips: { type: Object, default: null },
});

const emit = defineEmits(['select', 'drop', 'remove']);

const ANGLE_LABELS = { frente: 'Frente', perfil: 'Perfil', espalda: 'Espalda' };
const displayLabel = computed(() => props.label || ANGLE_LABELS[props.angle] || props.angle);

// HTML ref: el "REQ" cambia de color y label según estado
//   - empty:     "REQ" (gris) — todavía falta
//   - uploading: "SUBIENDO" (ámbar)
//   - has flash/error: "REVISAR" (ámbar)
//   - preview ok: "LISTA" (verde)
const reqState = computed(() => {
  if (props.uploading) {
    return { label: 'SUBIENDO', cls: 'text-amber-300' };
  }
  if (props.previewUrl && props.error) {
    return { label: 'REVISAR', cls: 'text-amber-300' };
  }
  if (props.previewUrl && props.chips?.lighting === 'low') {
    return { label: 'REVISAR', cls: 'text-amber-300' };
  }
  if (props.previewUrl) {
    return { label: 'LISTA', cls: 'text-emerald-400' };
  }
  return { label: 'REQ', cls: 'text-wc-text-tertiary' };
});

const inputRef = ref(null);
const dragging = ref(false);

function onInputChange(e) {
  emit('select', e);
}

function onDragOver(e) {
  e.preventDefault();
  dragging.value = true;
}
function onDragLeave(e) {
  e.preventDefault();
  dragging.value = false;
}
function onDrop(e) {
  e.preventDefault();
  dragging.value = false;
  const dropped = e.dataTransfer?.files?.[0];
  if (dropped) emit('drop', dropped);
}

function openPicker() {
  if (props.uploading) return;
  inputRef.value?.click();
}

const inputId = computed(() => `photo-upload-${props.angle}`);
</script>

<template>
  <div class="flex min-h-[200px] flex-col overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-wc-border px-4 py-3.5">
      <h4 class="font-display text-[14px] font-semibold uppercase tracking-[0.10em] text-wc-text">
        {{ displayLabel }}
      </h4>
      <span
        class="font-mono text-[10px] uppercase tracking-[0.12em] transition-colors"
        :class="reqState.cls"
      >{{ reqState.label }}</span>
    </div>

    <!-- Body -->
    <div
      class="relative flex flex-1 items-center justify-center"
      :class="[
        previewUrl ? 'p-0' : 'p-5',
        dragging ? 'bg-wc-accent/5 ring-2 ring-wc-accent/40' : '',
      ]"
      @dragover="onDragOver"
      @dragleave="onDragLeave"
      @drop="onDrop"
    >
      <!-- Empty -->
      <button
        v-if="!previewUrl && !uploading"
        type="button"
        class="flex w-full min-h-[44px] cursor-pointer items-center gap-3.5 rounded-xl text-left transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        :aria-label="`Subir foto de ${displayLabel}`"
        @click="openPicker"
      >
        <div class="flex h-[84px] w-16 shrink-0 items-center justify-center rounded-lg border border-dashed border-wc-border bg-[repeating-linear-gradient(45deg,_rgba(255,255,255,0.02)_0_8px,_rgba(255,255,255,0.04)_8px_16px)]">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 text-wc-text-tertiary opacity-50" aria-hidden="true">
            <rect x="3" y="6" width="18" height="14" rx="2" />
            <circle cx="12" cy="13" r="3.5" />
          </svg>
        </div>
        <div>
          <p class="text-[13px] font-medium text-wc-text">Arrastra o toma la foto</p>
          <small class="mt-1 block font-mono text-[10px] uppercase tracking-widest text-wc-text-tertiary">
            JPG · PNG · max 12MB
          </small>
        </div>
      </button>

      <!-- Preview -->
      <template v-if="previewUrl && !uploading">
        <div class="absolute inset-0">
          <img
            :src="previewUrl"
            :alt="`Preview ${displayLabel}`"
            class="absolute inset-0 h-full w-full object-cover"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-transparent to-transparent" aria-hidden="true"></div>

          <!-- Validation chips -->
          <div v-if="chips" class="absolute bottom-3 left-3">
            <PhotoValidationChips :chips="chips" />
          </div>

          <!-- Replace -->
          <div class="absolute right-3 top-3 flex gap-1.5">
            <button
              type="button"
              class="inline-flex min-h-[36px] items-center gap-1.5 rounded-full border border-white/20 bg-black/55 px-3 text-xs text-white backdrop-blur transition-colors hover:bg-black/75 focus:outline-none focus:ring-2 focus:ring-white/40"
              @click="openPicker"
              :aria-label="`Cambiar foto de ${displayLabel}`"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3 w-3" aria-hidden="true">
                <path d="M3 12a9 9 0 0 1 15-6.7L21 8M21 12a9 9 0 0 1-15 6.7L3 16" />
              </svg>
              Cambiar
            </button>
            <button
              type="button"
              class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/20 bg-black/55 text-white backdrop-blur transition-colors hover:bg-red-500/80 focus:outline-none focus:ring-2 focus:ring-white/40"
              @click="$emit('remove')"
              :aria-label="`Eliminar foto de ${displayLabel}`"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5" aria-hidden="true">
                <path d="M18 6 6 18M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </template>

      <!-- Uploading -->
      <div
        v-if="uploading"
        class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-wc-bg/65 backdrop-blur"
      >
        <div
          class="h-8 w-8 animate-spin rounded-full border-2 border-white/10"
          style="border-top-color: #EF4444"
          aria-hidden="true"
        ></div>
        <p class="text-xs text-wc-text">Subiendo {{ displayLabel.toLowerCase() }}...</p>
      </div>

      <!-- Hidden input -->
      <input
        ref="inputRef"
        :id="inputId"
        type="file"
        accept="image/*,.heic,.heif"
        class="absolute h-0 w-0 opacity-0"
        tabindex="-1"
        @change="onInputChange"
      />

      <!-- In-context flash alert (lighting warn etc) -->
      <div
        v-if="error"
        class="absolute inset-x-3 bottom-3 z-10"
      >
        <PhotoFlashAlert :message="error" icon="alert" />
      </div>
    </div>
  </div>
</template>
