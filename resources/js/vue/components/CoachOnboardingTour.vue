<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';

const emit = defineEmits(['done']);

const STEPS = [
  {
    selector: '[data-tour="dashboard"]',
    title: 'Tu Dashboard',
    body: 'Aqui ves el resumen de tu equipo cada dia: metricas, tickets pendientes y actividad reciente.',
  },
  {
    selector: '[data-tour="clients"]',
    title: 'Tus Clientes',
    body: 'Tus clientes asignados. Usalo para ver su progreso, historial y estado activo.',
  },
  {
    selector: '[data-tour="plan-tickets"]',
    title: 'Tickets de Plan',
    body: 'El corazon de tu trabajo. Aqui creas briefs que el equipo convierte en planes personalizados para tus clientes.',
  },
  {
    selector: '[data-tour="checkins"]',
    title: 'Check-ins',
    body: 'Revisa y responde los check-ins semanales de tus clientes. Mantener esto al dia genera confianza.',
  },
  {
    selector: '[data-tour="messages"]',
    title: 'Mensajes',
    body: 'Linea directa con tus clientes. Responde pronto para generar confianza y retencion.',
  },
];

const stepIndex = ref(0);
const spotlight = ref({ top: 0, left: 0, width: 0, height: 0, found: false });
const isMobile = ref(false);

const currentStep = computed(() => STEPS[stepIndex.value]);
const isLast = computed(() => stepIndex.value === STEPS.length - 1);

function measureSpotlight() {
  const el = document.querySelector(currentStep.value.selector);
  if (!el) {
    spotlight.value = { top: 0, left: 0, width: 0, height: 0, found: false };
    return;
  }
  const rect = el.getBoundingClientRect();
  const pad = 8;
  spotlight.value = {
    top: rect.top - pad,
    left: rect.left - pad,
    width: rect.width + pad * 2,
    height: rect.height + pad * 2,
    found: true,
  };
  // scroll into view if offscreen
  if (rect.top < 0 || rect.bottom > window.innerHeight) {
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
}

const tooltipStyle = computed(() => {
  const s = spotlight.value;
  if (isMobile.value || !s.found) {
    return null;
  }
  // Desktop: place tooltip to the right of spotlight, vertically aligned
  const top = Math.max(16, s.top);
  const left = s.left + s.width + 16;
  return {
    top: top + 'px',
    left: left + 'px',
    maxWidth: '320px',
  };
});

function next() {
  if (isLast.value) return finish();
  stepIndex.value++;
  nextTick(measureSpotlight);
}
function back() {
  if (stepIndex.value === 0) return;
  stepIndex.value--;
  nextTick(measureSpotlight);
}
function finish() {
  try { localStorage.setItem('coach_tour_completed', '1'); } catch {}
  emit('done');
}
function skip() {
  finish();
}

function onResize() {
  isMobile.value = window.innerWidth < 1024;
  measureSpotlight();
}

onMounted(async () => {
  isMobile.value = window.innerWidth < 1024;
  await nextTick();
  // wait a tick for layout
  setTimeout(measureSpotlight, 100);
  window.addEventListener('resize', onResize);
  window.addEventListener('scroll', measureSpotlight, true);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', onResize);
  window.removeEventListener('scroll', measureSpotlight, true);
});
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-[100] pointer-events-none">
      <!-- Dark overlay with SVG mask for spotlight hole -->
      <svg
        v-if="spotlight.found && !isMobile"
        class="absolute inset-0 w-full h-full pointer-events-auto"
        xmlns="http://www.w3.org/2000/svg"
      >
        <defs>
          <mask id="tour-mask">
            <rect width="100%" height="100%" fill="white" />
            <rect
              :x="spotlight.left"
              :y="spotlight.top"
              :width="spotlight.width"
              :height="spotlight.height"
              rx="12"
              ry="12"
              fill="black"
            />
          </mask>
        </defs>
        <rect width="100%" height="100%" fill="rgba(0,0,0,0.72)" mask="url(#tour-mask)" />
        <rect
          :x="spotlight.left"
          :y="spotlight.top"
          :width="spotlight.width"
          :height="spotlight.height"
          rx="12"
          ry="12"
          fill="none"
          stroke="#DC2626"
          stroke-width="2"
        />
      </svg>
      <!-- Fallback / mobile: full overlay -->
      <div v-else class="absolute inset-0 bg-black/75 pointer-events-auto"></div>

      <!-- Skip button -->
      <button
        @click="skip"
        class="absolute top-4 right-4 z-[102] pointer-events-auto rounded-lg border border-white/20 bg-black/60 px-3 py-1.5 text-xs font-semibold text-white/80 hover:bg-black/80 hover:text-white backdrop-blur"
      >
        Saltar tour
      </button>

      <!-- Tooltip card (desktop: next to spotlight; mobile: bottom full-width) -->
      <Transition name="fade" mode="out-in">
        <div
          :key="stepIndex"
          class="pointer-events-auto z-[101] rounded-xl border border-wc-border bg-wc-bg-secondary p-5 shadow-2xl"
          :class="isMobile
            ? 'fixed bottom-4 left-4 right-4'
            : 'absolute'"
          :style="!isMobile ? tooltipStyle : null"
        >
          <div class="flex items-center gap-2 mb-3">
            <span class="inline-flex h-6 items-center rounded-full bg-wc-accent/15 px-2 text-[11px] font-data font-bold text-wc-accent">
              {{ stepIndex + 1 }} de {{ STEPS.length }}
            </span>
            <!-- progress bar -->
            <div class="flex-1 h-1 rounded-full bg-wc-bg-tertiary overflow-hidden">
              <div
                class="h-full bg-wc-accent transition-all duration-300"
                :style="{ width: ((stepIndex + 1) / STEPS.length * 100) + '%' }"
              ></div>
            </div>
          </div>

          <h3 class="font-display text-xl tracking-wide text-wc-text">{{ currentStep.title }}</h3>
          <p class="mt-2 text-sm text-wc-text-secondary leading-relaxed">{{ currentStep.body }}</p>

          <div class="mt-5 flex items-center justify-between gap-2">
            <button
              v-if="stepIndex > 0"
              @click="back"
              class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-sm font-semibold text-wc-text-secondary hover:bg-wc-bg hover:text-wc-text transition"
            >
              Atras
            </button>
            <span v-else></span>
            <button
              @click="next"
              class="rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition"
            >
              {{ isLast ? 'Empezar' : 'Siguiente' }}
              <svg v-if="!isLast" class="inline h-3.5 w-3.5 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
              </svg>
            </button>
          </div>
        </div>
      </Transition>
    </div>
  </Teleport>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
