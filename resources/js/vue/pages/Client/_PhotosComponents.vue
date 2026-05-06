<script setup>
/**
 * _PhotosComponents — DEV PAGE for visual review of the 18 photos/* components.
 * Mounted at /dev/photos-components.
 *
 * TODO remove en Fase 6 — la página final será ProgressPhotos.vue.
 *
 * No live API calls. Uses inline mock data so it can render without auth.
 */
import { ref, computed } from 'vue';

import PhotosHero from '../../components/photos/PhotosHero.vue';
import PhotoGuide from '../../components/photos/PhotoGuide.vue';
import AnglesGrid from '../../components/photos/AnglesGrid.vue';
import AngleSilhouette from '../../components/photos/AngleSilhouette.vue';
import TipsList from '../../components/photos/TipsList.vue';
import PhotoTimeline from '../../components/photos/PhotoTimeline.vue';
import TimelineSession from '../../components/photos/TimelineSession.vue';
import PhotoComparison from '../../components/photos/PhotoComparison.vue';
import CompareCell from '../../components/photos/CompareCell.vue';
import PhotoUploadZone from '../../components/photos/PhotoUploadZone.vue';
import PhotoValidationChips from '../../components/photos/PhotoValidationChips.vue';
import PhotoFlashAlert from '../../components/photos/PhotoFlashAlert.vue';
import UploadSessionBar from '../../components/photos/UploadSessionBar.vue';
import DateField from '../../components/photos/DateField.vue';
import CoachFeedbackBadge from '../../components/photos/CoachFeedbackBadge.vue';
import CoachFeedbackPanel from '../../components/photos/CoachFeedbackPanel.vue';
import PrivacyReassurance from '../../components/photos/PrivacyReassurance.vue';
import EmptyState from '../../components/photos/EmptyState.vue';

// ── Mock data ──────────────────────────────────────────────────────
const mockPhoto = (id, tipo, date) => ({
  id,
  tipo,
  photo_date: date,
  filename: `mock-${id}.jpg`,
  // Placeholder gradient image (data URI svg) — works without network
  url: `data:image/svg+xml;utf8,${encodeURIComponent(
    `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 400'>
      <defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'>
        <stop offset='0' stop-color='%23${tipo === 'frente' ? '3b3b40' : tipo === 'perfil' ? '2a2a2e' : '1f1f24'}'/>
        <stop offset='1' stop-color='%230a0a0c'/>
      </linearGradient></defs>
      <rect width='300' height='400' fill='url(%23g)'/>
      <text x='50%' y='50%' font-family='Oswald,sans-serif' font-size='28' fill='%23fafafa' opacity='0.7' text-anchor='middle' dominant-baseline='middle'>${tipo.toUpperCase()}</text>
      <text x='50%' y='60%' font-family='monospace' font-size='12' fill='%23fafafa' opacity='0.4' text-anchor='middle'>${date}</text>
    </svg>`
  )}`,
});

const mockSessions = [
  {
    date: '2026-04-12',
    photos: {
      frente: mockPhoto(7, 'frente', '2026-04-12'),
      perfil: mockPhoto(8, 'perfil', '2026-04-12'),
      espalda: mockPhoto(9, 'espalda', '2026-04-12'),
    },
    hasAll: true,
  },
  {
    date: '2026-04-05',
    photos: {
      frente: mockPhoto(4, 'frente', '2026-04-05'),
      perfil: mockPhoto(5, 'perfil', '2026-04-05'),
      espalda: mockPhoto(6, 'espalda', '2026-04-05'),
    },
    hasAll: true,
  },
  {
    date: '2026-03-22',
    photos: {
      frente: mockPhoto(2, 'frente', '2026-03-22'),
      perfil: mockPhoto(3, 'perfil', '2026-03-22'),
      espalda: null,
    },
    hasAll: false,
  },
  {
    date: '2026-03-06',
    photos: {
      frente: mockPhoto(1, 'frente', '2026-03-06'),
      perfil: null,
      espalda: null,
    },
    hasAll: false,
  },
];

const metaByDate = {
  '2026-04-12': { weekLabel: 'Semana 06 · Reciente', reviewStatus: 'pending', weight: 52.1, waist: 72 },
  '2026-04-05': { weekLabel: 'Semana 05', reviewStatus: 'reviewed', weight: 52.8, waist: 73 },
  '2026-03-22': { weekLabel: 'Semana 03', reviewStatus: 'notes', notesCount: 3, weight: 53.4, waist: 74.5 },
  '2026-03-06': { weekLabel: 'Semana 01 · Inicio', reviewStatus: 'reviewed', weight: 54.2, waist: 76 },
};

// Upload zone demo state (no real upload)
const demoFiles = ref({ frente: null, perfil: null, espalda: null });
const demoPreviews = ref({ frente: null, perfil: null, espalda: null });
function onDemoSelect(angle, e) {
  const f = e.target.files?.[0];
  if (!f) return;
  if (demoPreviews.value[angle]) URL.revokeObjectURL(demoPreviews.value[angle]);
  demoFiles.value[angle] = f;
  demoPreviews.value[angle] = URL.createObjectURL(f);
}
function onDemoRemove(angle) {
  if (demoPreviews.value[angle]) URL.revokeObjectURL(demoPreviews.value[angle]);
  demoFiles.value[angle] = null;
  demoPreviews.value[angle] = null;
}

const demoSelected = computed(() => Object.values(demoFiles.value).filter(Boolean).length);
const demoDate = ref('2026-05-06');

// Coach feedback panel demo
const fbOpen = ref(false);
const activePhoto = ref(mockSessions[0].photos.frente);

// Toggle panel
function openFbPanel(session, photo) {
  activePhoto.value = photo || session.photos.frente;
  fbOpen.value = true;
}
</script>

<template>
  <div class="min-h-screen bg-wc-bg p-6 text-wc-text">
    <header class="mx-auto mb-8 max-w-5xl">
      <h1 class="font-display text-3xl uppercase tracking-tight">Photos · Component Library</h1>
      <p class="mt-1 text-sm text-wc-text-secondary">
        Visual review de los 18 componentes Fase 1. Esta página NO se compone — cada bloque es standalone.
      </p>
      <p class="mt-2 text-xs text-wc-text-tertiary">TODO remove en Fase 6.</p>
    </header>

    <main class="mx-auto max-w-5xl space-y-12">

      <!-- 01 PhotosHero -->
      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">01 · PhotosHero</h2>
        <PhotosHero
          :session-count="4"
          :week-count="1"
          latest-date="2026-04-12"
          next-suggested="sugerida en 5 días — domingo 17 may"
        />
      </section>

      <!-- 02 PhotoGuide -->
      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">02 · PhotoGuide</h2>
        <PhotoGuide />
      </section>

      <!-- 03 AnglesGrid -->
      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">03 · AnglesGrid</h2>
        <AnglesGrid />
      </section>

      <!-- 04 AngleSilhouette -->
      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">04 · AngleSilhouette (3 variants)</h2>
        <div class="grid grid-cols-3 gap-3">
          <div v-for="variant in ['front','side','back']" :key="variant" class="flex aspect-square items-center justify-center rounded-xl border border-wc-border bg-wc-bg-tertiary">
            <AngleSilhouette :variant="variant" :label="variant" />
          </div>
        </div>
      </section>

      <!-- 05 TipsList -->
      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">05 · TipsList</h2>
        <TipsList />
      </section>

      <!-- 06 PhotoTimeline + 07 TimelineSession -->
      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">06 · PhotoTimeline (con TimelineSession)</h2>
        <PhotoTimeline
          :sessions="mockSessions"
          :meta-by-date="metaByDate"
          @open-feedback="openFbPanel($event)"
        />
      </section>

      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">07 · TimelineSession (single)</h2>
        <div class="max-w-xs">
          <TimelineSession
            :session="mockSessions[2]"
            week-label="Semana 03"
            review-status="notes"
            :notes-count="3"
            :meta="{ weight: 53.4, waist: 74.5 }"
          />
        </div>
      </section>

      <!-- 08 PhotoComparison + 09 CompareCell -->
      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">08 · PhotoComparison (incluye CompareCell)</h2>
        <PhotoComparison :sessions="mockSessions" />
      </section>

      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">09 · CompareCell (single)</h2>
        <div class="grid max-w-md grid-cols-2 gap-0 overflow-hidden rounded-xl border border-wc-border">
          <CompareCell :photo="mockSessions[3].photos.frente" date="2026-03-06" angle="frente" side="a" />
          <CompareCell :photo="mockSessions[0].photos.frente" date="2026-04-12" angle="frente" side="b" />
        </div>
      </section>

      <!-- 10 PhotoUploadZone -->
      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">10 · PhotoUploadZone (3 estados)</h2>
        <div class="grid gap-3 sm:grid-cols-3">
          <PhotoUploadZone
            angle="frente"
            :file="demoFiles.frente"
            :preview-url="demoPreviews.frente"
            @select="onDemoSelect('frente', $event)"
            @drop="(f) => onDemoSelect('frente', { target: { files: [f] } })"
            @remove="onDemoRemove('frente')"
            :chips="demoPreviews.frente ? { lighting: 'good', framing: 'good' } : null"
          />
          <PhotoUploadZone
            angle="perfil"
            :uploading="true"
          />
          <PhotoUploadZone
            angle="espalda"
            error="La luz parece baja, intenta acercarte a una ventana"
          />
        </div>
      </section>

      <!-- 11 PhotoValidationChips -->
      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">11 · PhotoValidationChips</h2>
        <div class="flex gap-3">
          <PhotoValidationChips :chips="{ lighting: 'good', framing: 'good' }" />
          <PhotoValidationChips :chips="{ lighting: 'low', framing: 'good' }" />
          <PhotoValidationChips :chips="{ lighting: 'good', framing: 'warn' }" />
        </div>
      </section>

      <!-- 12 PhotoFlashAlert -->
      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">12 · PhotoFlashAlert</h2>
        <div class="space-y-2">
          <PhotoFlashAlert message="La luz parece baja, intenta acercarte a una ventana" icon="lightbulb" />
          <PhotoFlashAlert message="La foto pesa más de 12MB. Reduce el tamaño." icon="alert" />
          <PhotoFlashAlert message="Tu próxima sesión está sugerida para el domingo." icon="info" />
        </div>
      </section>

      <!-- 13 UploadSessionBar -->
      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">13 · UploadSessionBar</h2>
        <UploadSessionBar
          v-model="demoDate"
          :selected="demoSelected"
          :total="3"
          @submit="alert('Submit (demo)')"
        />
      </section>

      <!-- 14 DateField -->
      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">14 · DateField (standalone)</h2>
        <div class="max-w-xs">
          <DateField v-model="demoDate" label="Fecha de la sesión" />
          <p class="mt-2 font-mono text-xs text-wc-text-tertiary">value: {{ demoDate }}</p>
        </div>
      </section>

      <!-- 15 CoachFeedbackBadge -->
      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">15 · CoachFeedbackBadge</h2>
        <div class="flex flex-wrap gap-2">
          <CoachFeedbackBadge status="reviewed" />
          <CoachFeedbackBadge status="pending" />
          <CoachFeedbackBadge status="notes" :count="3" />
          <CoachFeedbackBadge status="reviewed" compact />
        </div>
      </section>

      <!-- 16 CoachFeedbackPanel -->
      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">16 · CoachFeedbackPanel (toggle)</h2>
        <button
          type="button"
          class="rounded-xl bg-wc-accent px-4 py-2 font-display text-sm uppercase tracking-wider text-white"
          @click="openFbPanel(mockSessions[2], mockSessions[2].photos.frente)"
        >
          Abrir panel
        </button>
        <CoachFeedbackPanel
          :open="fbOpen"
          :session="mockSessions[2]"
          :active-photo="activePhoto"
          coach-name="Marina Pérez"
          summary="Juan, se nota la postura más erguida desde la semana 3."
          @close="fbOpen = false"
          @change-active="activePhoto = $event"
        />
      </section>

      <!-- 17 PrivacyReassurance -->
      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">17 · PrivacyReassurance</h2>
        <PrivacyReassurance class="mb-3" />
        <PrivacyReassurance compact />
      </section>

      <!-- 18 EmptyState -->
      <section>
        <h2 class="mb-3 font-mono text-xs uppercase tracking-widest text-wc-accent">18 · EmptyState</h2>
        <EmptyState
          @start="alert('Start')"
          @guide="alert('Guide')"
        />
      </section>

    </main>
  </div>
</template>
