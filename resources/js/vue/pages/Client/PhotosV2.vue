<script setup>
/**
 * PhotosV2 — Página redesign Fase 2 de /client/photos.
 *
 * Compone los 18 sub-componentes de Fase 1 + composables (usePhotoUpload,
 * useProgressPhotos, usePhotoComparison, useCoachFeedback, usePhotoValidation)
 * y reproduce 100% del comportamiento de ProgressPhotos.legacy.vue con la
 * nueva UI editorial v2.1.
 *
 * Preservado del legacy:
 *  - Achievement overlay (Teleport + Transition + 8 confetti color-coded 4s)
 *  - fetchMedals() post-upload-success
 *  - localStorage('wc_photo_guide') (delegado a PhotoGuide)
 *  - Inline delete confirm (NO native confirm())
 *  - useToast().apiError() para errores DELETE
 *  - HEIC dynamic import (dentro de usePhotoUpload)
 *  - Cleanup onBeforeUnmount (timer + object URLs delegados al composable)
 *
 * Activación: feature flag `photos_v2` (override local con
 * localStorage.wc_force_photos_v2='1'). El switch vive en ProgressPhotos.vue.
 */
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import ClientLayout from '../../layouts/ClientLayout.vue';
import { useMedals } from '../../composables/useMedals';
import { useToast } from '../../composables/useToast';
import { useApi } from '../../composables/useApi';
import { localDateStr } from '../../composables/useDate';

import PhotosHero from '../../components/photos/PhotosHero.vue';
import PrivacyReassurance from '../../components/photos/PrivacyReassurance.vue';
import PhotoGuide from '../../components/photos/PhotoGuide.vue';
import UploadSessionBar from '../../components/photos/UploadSessionBar.vue';
import PhotoUploadZone from '../../components/photos/PhotoUploadZone.vue';
import EmptyState from '../../components/photos/EmptyState.vue';
import PhotoTimeline from '../../components/photos/PhotoTimeline.vue';
import PhotoComparison from '../../components/photos/PhotoComparison.vue';
import CoachFeedbackPanel from '../../components/photos/CoachFeedbackPanel.vue';

import { usePhotoUpload } from '../../composables/usePhotoUpload';
import { useProgressPhotos } from '../../composables/useProgressPhotos';
import { usePhotoComparison } from '../../composables/usePhotoComparison';
import { computeChips } from '../../composables/usePhotoValidation';

// --- Servicios externos ---
const api = useApi();
const toast = useToast();
const { fetchMedals } = useMedals();

// --- Composables de dominio ---
const upload = usePhotoUpload();
const photosStore = useProgressPhotos();
const compare = usePhotoComparison();

// --- State local ---
const ANGLES = ['frente', 'perfil', 'espalda'];
const uploadDate = ref(localDateStr());
const compareMode = ref(false);

// Validation chips por ángulo (heurística client-side: luma)
const chipsByAngle = ref({ frente: null, perfil: null, espalda: null });

// Coach feedback panel
const feedbackOpen = ref(false);
const feedbackSession = ref(null);
const feedbackPhoto = ref(null);

// Achievement overlay (preservado verbatim del legacy)
const showSuccess = ref(false);
const showConfetti = ref(false);
let confettiTimer = null;

const CONFETTI_PIECES = [
  { left: '8%',  bg: '#DC2626', dur: '2.8s', delay: '0.1s', round: false },
  { left: '22%', bg: '#F59E0B', dur: '3.2s', delay: '0.3s', round: true  },
  { left: '38%', bg: '#10B981', dur: '2.5s', delay: '0s',   round: false },
  { left: '52%', bg: '#DC2626', dur: '3s',   delay: '0.5s', round: true  },
  { left: '65%', bg: '#8B5CF6', dur: '2.7s', delay: '0.2s', round: false },
  { left: '78%', bg: '#F59E0B', dur: '3.4s', delay: '0.4s', round: true  },
  { left: '90%', bg: '#10B981', dur: '2.6s', delay: '0.15s',round: false },
  { left: '45%', bg: '#8B5CF6', dur: '3.1s', delay: '0.6s', round: false },
];

// Inline delete confirm (NO native confirm())
const pendingDeletePhoto = ref(null); // photo object or null
const deletingId = ref(null);

// --- Refs UI ---
const uploadSectionRef = ref(null);
const guideSectionRef = ref(null);
const guideRef = ref(null); // expone .open() para forzar apertura desde EmptyState

// --- Computed ---
const sortedDates = computed(() => photosStore.sortedDates.value);
const sessions = computed(() => photosStore.sessions.value);
const latestSession = computed(() => photosStore.latestSession.value);
const weekCount = computed(() => photosStore.weekCount.value);
const sessionCount = computed(() => sortedDates.value.length);

// Próxima sesión sugerida (cada 7 días desde la última)
const nextSuggested = computed(() => {
  const last = latestSession.value?.date;
  if (!last) return '';
  const d = new Date(last + 'T12:00:00');
  if (isNaN(d.getTime())) return '';
  d.setDate(d.getDate() + 7);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const diffDays = Math.ceil((d.getTime() - today.getTime()) / (24 * 3600 * 1000));
  const DOW = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
  const MES = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
  const human = `${DOW[d.getDay()]} ${String(d.getDate()).padStart(2,'0')} ${MES[d.getMonth()]}`;
  if (diffDays <= 0) return `hoy — ${human}`;
  if (diffDays === 1) return `mañana — ${human}`;
  return `en ${diffDays} días — ${human}`;
});

// --- Validation chips (recalcular cuando cambia un archivo) ---
watch(
  () => ANGLES.map((a) => upload.uploadFiles[a]),
  async (files) => {
    for (let i = 0; i < ANGLES.length; i++) {
      const angle = ANGLES[i];
      const file = files[i];
      if (!file) {
        chipsByAngle.value[angle] = null;
        continue;
      }
      // Solo recalcular si cambió la referencia
      try {
        chipsByAngle.value[angle] = await computeChips(file);
      } catch {
        chipsByAngle.value[angle] = { lighting: 'good', framing: 'good' };
      }
    }
  },
  { deep: false }
);

// --- Handlers de upload zone ---
function onZoneSelect(angle, event) {
  upload.onFileSelect(angle, event);
}
function onZoneDrop(angle, file) {
  upload.setFile(angle, file);
}
function onZoneRemove(angle) {
  upload.removeFile(angle);
  chipsByAngle.value[angle] = null;
}

// --- Submit upload ---
async function onUploadSubmit() {
  const ok = await upload.uploadAll(uploadDate.value);
  if (!ok) return;

  // Achievement overlay
  showSuccess.value = true;
  showConfetti.value = true;
  clearTimeout(confettiTimer);
  confettiTimer = setTimeout(() => { showConfetti.value = false; }, 4000);

  // Detecta medallas / level-up post-upload
  fetchMedals().catch(() => {});

  // Refresh gallery + reset chips
  await photosStore.refetch();
  chipsByAngle.value = { frente: null, perfil: null, espalda: null };
}

function dismissSuccess() {
  showSuccess.value = false;
  showConfetti.value = false;
}

function confettiStyle(piece) {
  return {
    left: piece.left,
    background: piece.bg,
    borderRadius: piece.round ? '50%' : '0',
    animation: `wc-confetti-fall ${piece.dur} ease-in forwards ${piece.delay}`,
  };
}

// --- Coach feedback panel ---
function openFeedback(session, photo = null) {
  feedbackSession.value = session;
  feedbackPhoto.value = photo
    || session?.photos?.frente
    || session?.photos?.perfil
    || session?.photos?.espalda
    || null;
  feedbackOpen.value = true;
}
function onTimelineSelect(photo) {
  // Cuando hacen click en un thumb, buscamos la sesión que lo contiene
  const sess = sessions.value.find((s) => Object.values(s.photos).some((p) => p?.id === photo?.id));
  openFeedback(sess, photo);
}
function onTimelineOpenFeedback(session) {
  openFeedback(session, null);
}
function onPanelChangeActive(photo) {
  feedbackPhoto.value = photo;
}
function closeFeedback() {
  feedbackOpen.value = false;
}

// --- Delete (inline confirm) ---
function requestDeletePhoto(photo) {
  pendingDeletePhoto.value = photo;
}
function cancelDeletePhoto() {
  pendingDeletePhoto.value = null;
}
async function confirmDeletePhoto() {
  const photo = pendingDeletePhoto.value;
  if (!photo) return;
  pendingDeletePhoto.value = null;
  deletingId.value = photo.id;
  try {
    await api.delete(`/api/v/client/photos/${photo.id}`);
    // Si la foto borrada estaba en el panel abierto, ciérralo o recarga foto activa
    if (feedbackPhoto.value?.id === photo.id) {
      feedbackOpen.value = false;
      feedbackPhoto.value = null;
      feedbackSession.value = null;
    }
    await photosStore.refetch();
  } catch (err) {
    toast.apiError(err, 'No pudimos eliminar la foto. Intenta de nuevo.');
  } finally {
    deletingId.value = null;
  }
}

// --- Empty state CTAs ---
function scrollToUpload() {
  uploadSectionRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
function scrollToGuide() {
  // Forzar apertura de la guía si está colapsada (sin esto el scroll va al
  // header pero el cliente no ve los ángulos sin un click extra).
  guideRef.value?.open?.();
  // Defer scroll para que la animación de expansión arranque primero
  requestAnimationFrame(() => {
    guideSectionRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
}

// --- Cleanup ---
onBeforeUnmount(() => {
  clearTimeout(confettiTimer);
  // El composable usePhotoUpload ya revoca object URLs en su propio onBeforeUnmount
});

onMounted(() => {
  photosStore.refetch();
});
</script>

<template>
  <ClientLayout>
    <div class="wc-shell wc-shell--photos">

      <!-- ===== ACHIEVEMENT OVERLAY (Teleport + 8 confetti color-coded) ===== -->
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
            <div v-show="showConfetti" class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
              <div
                v-for="(piece, idx) in CONFETTI_PIECES"
                :key="idx"
                class="wc-confetti"
                :style="confettiStyle(piece)"
              ></div>
            </div>

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
                  <h2 id="photos-success-title" class="font-sans text-2xl font-bold text-white mb-2">Sesión guardada!</h2>
                  <div class="my-5 rounded-xl border border-white/10 bg-white/[0.06] px-5 py-4">
                    <p class="font-data text-3xl font-bold text-white">+{{ upload.uploadedCount() || 3 }}</p>
                    <p class="mt-0.5 text-xs text-white/50">ángulos registrados</p>
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

      <!-- ===== INLINE DELETE CONFIRM (modal, NO native confirm) ===== -->
      <Teleport to="body">
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div
            v-if="pendingDeletePhoto"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(0,0,0,0.7)"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-confirm-title"
            @click.self="cancelDeletePhoto"
            @keydown.escape.window="cancelDeletePhoto"
          >
            <div class="w-full max-w-sm overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
              <h3 id="delete-confirm-title" class="font-display text-lg uppercase tracking-wider text-wc-text">
                ¿Eliminar foto?
              </h3>
              <p class="mt-2 text-sm text-wc-text-secondary">
                Esta acción no se puede deshacer. La foto se borra para siempre.
              </p>
              <div class="mt-5 flex gap-2">
                <button
                  type="button"
                  class="flex-1 min-h-[44px] rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 text-sm font-semibold text-wc-text transition-colors hover:border-wc-accent/40"
                  @click="cancelDeletePhoto"
                >
                  Cancelar
                </button>
                <button
                  type="button"
                  class="flex-1 min-h-[44px] rounded-xl bg-red-600 px-4 text-sm font-semibold text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/50"
                  @click="confirmDeletePhoto"
                >
                  Eliminar
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>
      <!-- ===== /DELETE CONFIRM ===== -->

      <!-- ===== Loading skeleton ===== -->
      <div v-if="photosStore.loading.value && !sortedDates.length" class="space-y-6">
        <div class="space-y-2">
          <div class="h-4 w-24 animate-pulse rounded bg-wc-bg-tertiary"></div>
          <div class="h-12 w-72 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
          <div class="h-5 w-96 max-w-full animate-pulse rounded bg-wc-bg-tertiary"></div>
        </div>
        <div class="grid grid-cols-3 gap-2 rounded-2xl border border-wc-border bg-wc-bg-tertiary p-4">
          <div v-for="n in 3" :key="n" class="h-12 animate-pulse rounded bg-wc-bg-secondary"></div>
        </div>
        <div class="grid gap-3 sm:grid-cols-3">
          <div v-for="n in 3" :key="'z-'+n" class="h-48 animate-pulse rounded-2xl border border-wc-border bg-wc-bg-tertiary"></div>
        </div>
      </div>

      <!-- ===== Error state ===== -->
      <div v-else-if="photosStore.error.value" class="flex flex-col items-center justify-center py-20 text-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
          <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
          </svg>
        </div>
        <h2 class="mt-4 font-display text-xl tracking-wide text-wc-text">ERROR AL CARGAR</h2>
        <p class="mt-2 text-sm text-wc-text-secondary">{{ photosStore.error.value }}</p>
        <button
          @click="photosStore.refetch()"
          class="mt-6 inline-flex min-h-[44px] items-center justify-center gap-2 rounded-xl bg-wc-accent px-5 text-sm font-semibold text-white transition-colors hover:bg-red-700"
        >
          Reintentar
        </button>
      </div>

      <!-- ===== Content ===== -->
      <div v-else class="space-y-6">

        <!-- Hero -->
        <PhotosHero
          :session-count="sessionCount"
          :week-count="weekCount"
          :latest-date="latestSession?.date || ''"
          :next-suggested="nextSuggested"
        />

        <!-- Privacy reassurance -->
        <PrivacyReassurance />

        <!-- Guía de fotos -->
        <div ref="guideSectionRef">
          <PhotoGuide ref="guideRef" :default-open="true" :genero="photosStore.genero.value" />
        </div>

        <!-- ===== Upload section ===== -->
        <section ref="uploadSectionRef" class="space-y-4">
          <UploadSessionBar
            v-model="uploadDate"
            :selected="upload.uploadedCount()"
            :total="3"
            :uploading="upload.uploading.value"
            @submit="onUploadSubmit"
          />

          <div class="grid gap-3 sm:grid-cols-3">
            <PhotoUploadZone
              v-for="angle in ANGLES"
              :key="angle"
              :angle="angle"
              :file="upload.uploadFiles[angle]"
              :preview-url="upload.uploadPreviews[angle]"
              :error="upload.fieldErrorFor(angle)"
              :uploading="upload.uploading.value"
              :chips="chipsByAngle[angle]"
              @select="(e) => onZoneSelect(angle, e)"
              @drop="(f) => onZoneDrop(angle, f)"
              @remove="onZoneRemove(angle)"
            />
          </div>

          <!-- Error general del upload -->
          <p
            v-if="upload.uploadError.value"
            class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300"
            role="alert"
          >
            {{ upload.uploadError.value }}
          </p>
        </section>

        <!-- ===== Empty state OR (Comparison + Timeline) ===== -->
        <EmptyState
          v-if="!sortedDates.length"
          @start="scrollToUpload"
          @guide="scrollToGuide"
        />

        <template v-else>
          <!-- Toggle compare mode -->
          <div class="flex items-center justify-between">
            <h2 class="font-display text-lg uppercase tracking-wider text-wc-text">
              Tu historia
            </h2>
            <button
              v-if="sortedDates.length >= 2"
              type="button"
              class="inline-flex min-h-[40px] items-center gap-2 rounded-xl border px-3 text-xs font-semibold uppercase tracking-wider transition-colors"
              :class="compareMode
                ? 'border-wc-accent bg-wc-accent text-white'
                : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'"
              @click="compareMode = !compareMode"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5" aria-hidden="true">
                <path d="M8 3v18M16 3v18M3 8h5M16 8h5M3 16h5M16 16h5" />
              </svg>
              {{ compareMode ? 'Cerrar comparación' : 'Comparar' }}
            </button>
          </div>

          <PhotoComparison
            v-if="compareMode"
            :sessions="sessions"
          />

          <PhotoTimeline
            v-else
            :sessions="sessions"
            @select="onTimelineSelect"
            @open-feedback="onTimelineOpenFeedback"
          />
        </template>
      </div>

      <!-- Coach feedback lateral panel -->
      <CoachFeedbackPanel
        :open="feedbackOpen"
        :session="feedbackSession"
        :active-photo="feedbackPhoto"
        coach-name="Marina Pérez"
        @close="closeFeedback"
        @change-active="onPanelChangeActive"
        @delete-photo="requestDeletePhoto"
      />
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
