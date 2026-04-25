<script setup>
import { computed, defineAsyncComponent, ref, onErrorCaptured } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

// Catalog: maps slug -> dynamic import path
// IMPORTANT: every entry maps to a Vue page that the client/RISE user actually fills out.
const FORM_REGISTRY = {
    // ─── Cliente ─────────────────────────────────────────────────────────────
    'client/checkin':         () => import('../Client/CheckinForm.vue'),
    'client/metrics':         () => import('../Client/MetricsTracker.vue'),
    'client/profile':         () => import('../Client/ProfileEditor.vue'),
    'client/settings':        () => import('../Client/ClientSettings.vue'),
    'client/habits':          () => import('../Client/HabitTracker.vue'),
    'client/supplements':     () => import('../Client/SupplementTracker.vue'),
    'client/photos':          () => import('../Client/ProgressPhotos.vue'),
    'client/video-checkin':   () => import('../Client/VideoCheckinUpload.vue'),
    'client/tickets':         () => import('../Client/TicketSupport.vue'),

    // ─── RISE ────────────────────────────────────────────────────────────────
    'rise/habits':            () => import('../Rise/Habits.vue'),
    'rise/measurements':      () => import('../Rise/Measurements.vue'),
    'rise/photos':            () => import('../Rise/Photos.vue'),
    'rise/tracking':          () => import('../Rise/DailyTracking.vue'),
    'rise/profile':           () => import('../Rise/RiseProfile.vue'),

    // ─── Inscripción pública (asesorado pre-signup) ──────────────────────────
    'public/inscripcion':     () => import('../Public/InscriptionForm.vue'),
    'public/coach-apply':     () => import('../Public/CoachApplication.vue'),
    'public/rise-enroll':     () => import('../Public/RiseEnrollment.vue'),
    'public/presencial':      () => import('../Public/PresencialForm.vue'),
};

const slug = computed(() => `${route.params.area}/${route.params.form}`);

const error = ref(null);

const FormComponent = computed(() => {
    const loader = FORM_REGISTRY[slug.value];
    if (! loader) return null;
    return defineAsyncComponent({
        loader,
        delay: 0,
        timeout: 8000,
        onError(err, retry, fail, attempts) {
            if (attempts < 2) retry();
            else fail();
        },
    });
});

onErrorCaptured((err) => {
    error.value = err?.message || 'Error desconocido al renderizar el formulario.';
    return false; // stop propagation
});
</script>

<template>
  <div class="min-h-screen bg-wc-bg">
    <!-- Banner de modo vista previa -->
    <div class="sticky top-0 z-50 flex items-center gap-2 bg-amber-500/95 px-4 py-2 text-xs font-semibold text-amber-950 shadow-md backdrop-blur-sm">
      <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
      </svg>
      <span>VISTA PREVIA · Asi se vera para el asesorado · Las acciones no se ejecutan</span>
    </div>

    <!-- Form not found -->
    <div v-if="! FormComponent" class="flex min-h-[80vh] items-center justify-center px-6">
      <div class="max-w-md rounded-xl border border-wc-border bg-wc-bg-secondary p-8 text-center">
        <p class="font-display text-xl tracking-wide text-wc-text">FORMULARIO NO ENCONTRADO</p>
        <p class="mt-2 text-sm text-wc-text-secondary">No existe un preview registrado para <code class="rounded bg-wc-bg-tertiary px-1.5 py-0.5 font-mono text-xs">{{ slug }}</code>.</p>
      </div>
    </div>

    <!-- Render error -->
    <div v-else-if="error" class="flex min-h-[80vh] items-center justify-center px-6">
      <div class="max-w-md rounded-xl border border-red-500/40 bg-red-500/10 p-8 text-center">
        <p class="font-display text-xl tracking-wide text-red-400">ERROR AL RENDERIZAR</p>
        <p class="mt-2 text-sm text-red-300">{{ error }}</p>
      </div>
    </div>

    <!-- Form renders here -->
    <Suspense v-else>
      <component :is="FormComponent" />
      <template #fallback>
        <div class="flex min-h-[80vh] items-center justify-center">
          <div class="flex items-center gap-3 text-sm text-wc-text-secondary">
            <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            Cargando vista previa...
          </div>
        </div>
      </template>
    </Suspense>
  </div>
</template>
