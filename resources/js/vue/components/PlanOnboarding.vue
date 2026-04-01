<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '../stores/auth';

const props = defineProps({
    planType: { type: String, default: 'esencial' },
    clientName: { type: String, default: '' },
});

const emit = defineEmits(['completed']);
const authStore = useAuthStore();

const current = ref(0);

const slides = computed(() => buildSlides(props.planType, props.clientName));
const total = computed(() => slides.value.length);

function next() {
    if (current.value < total.value - 1) current.value++;
}
function prev() {
    if (current.value > 0) current.value--;
}

async function finish() {
    await fetch('/api/v/client/onboarding/complete', {
        method: 'POST',
        headers: { Authorization: `Bearer ${authStore.token}` },
    });
    emit('completed');
}

function buildSlides(planType, name) {
    const common = [
        {
            icon: 'sparkles',
            title: '¡Bienvenido a WellCore!',
            description: `Hola ${name}, tu coach ha creado un programa personalizado para ti. Aquí te explicamos cómo sacarle el máximo provecho.`,
            color: 'wc-accent',
        },
    ];

    const planMap = {
        esencial: [
            {
                icon: 'dumbbell', title: 'Tu Entrenamiento', color: 'blue-500',
                description: 'Tu plan incluye un programa de entrenamiento personalizado. Cada ejercicio tiene series, repeticiones y descansos específicos para ti.',
                features: ['Ejercicios con instrucciones detalladas', 'Registro de peso y repeticiones', 'Detección automática de records personales'],
            },
            {
                icon: 'habits', title: 'Hábitos Diarios', color: 'emerald-500',
                description: 'Agua, sueño, nutrición y entrenamiento — marca tus hábitos cada día para construir consistencia.',
                features: ['Seguimiento diario con rachas', 'Heatmap de 30 días', 'Tips de tu coach'],
            },
        ],
        metodo: [
            {
                icon: 'dumbbell', title: 'Tu Entrenamiento', color: 'blue-500',
                description: 'Tu programa de entrenamiento está diseñado con periodización para maximizar tus resultados. Cada semana tiene progresiones específicas.',
                features: ['Workout Player interactivo', 'Timer de descanso automático', 'Records personales con celebración'],
            },
            {
                icon: 'nutrition', title: 'Plan de Nutrición', color: 'emerald-500',
                description: 'Tu coach ha calculado tus macros ideales. Proteína, carbohidratos y grasas en las cantidades exactas para tu objetivo.',
                features: ['Calorías y macros personalizados', 'Plan de comidas detallado', 'Tracker de agua diario'],
            },
            {
                icon: 'habits', title: 'Hábitos + Suplementación', color: 'violet-500',
                description: 'Los hábitos son la base de la transformación. Tu protocolo de suplementación complementa tu nutrición.',
                features: ['5 hábitos diarios con rachas', 'Suplementos con horarios específicos', 'Adherencia semanal visible'],
            },
        ],
        elite: [
            {
                icon: 'dumbbell', title: 'Entrenamiento Elite', color: 'blue-500',
                description: 'Tu plan es el más completo. Incluye variaciones semanales y progresiones de carga para resultados de élite.',
                features: ['Progresiones semanales automáticas', 'Variaciones de ejercicios', 'Análisis de volumen de entrenamiento'],
            },
            {
                icon: 'nutrition', title: 'Nutrición Avanzada', color: 'emerald-500',
                description: 'Macros calculados científicamente con ajustes según tu progreso. Incluye plan de comidas y timing nutricional.',
                features: ['Macros personalizados', 'Timing nutricional', 'Ajustes por fase de entrenamiento'],
            },
            {
                icon: 'elite', title: '6 Áreas de Coaching', color: 'amber-500',
                description: 'Entrenamiento, nutrición, hábitos, suplementación, ciclo hormonal y bloodwork — coaching integral.',
                features: ['Seguimiento de ciclo hormonal', 'Análisis de bloodwork', 'Suplementación personalizada'],
            },
        ],
        rise: [
            {
                icon: 'fire', title: '30 Días de Transformación', color: 'orange-500',
                description: 'RISE es un programa intensivo de 30 días. Cada día cuenta. Tu coach te guía paso a paso.',
                features: ['Entrenamiento diario progresivo', 'Tips de nutrición', 'Tracking diario de progreso'],
            },
            {
                icon: 'chart', title: 'Mide Tu Progreso', color: 'cyan-500',
                description: 'Al inicio y al final tomarás medidas y fotos. La ciencia no miente — verás tu transformación en datos.',
                features: ['Medidas corporales día 1 y 30', 'Fotos de progreso', 'Reporte final de resultados'],
            },
        ],
        trial: [
            {
                icon: 'rocket', title: '3 Días de Muestra', color: 'violet-500',
                description: 'Tienes 3 días para experimentar el método WellCore. Si te gusta, imagina lo que lograrás en 12 semanas.',
                features: ['Entrenamiento de muestra', 'Tips de nutrición básicos', 'Acceso al dashboard completo'],
            },
        ],
    };

    const planSlides = planMap[planType] ?? [
        {
            icon: 'dumbbell', title: 'Tu Programa', color: 'blue-500',
            description: 'Tu coach está preparando un programa personalizado para ti.',
            features: ['Entrenamiento personalizado', 'Seguimiento de progreso'],
        },
    ];

    const finalSlide = [
        {
            icon: 'rocket', title: '¡Comienza Ahora!', color: 'wc-accent',
            description: 'Todo está listo. Tu coach está aquí, tu comunidad está aquí. Solo falta tu decisión.',
            cta: true,
        },
    ];

    return [...common, ...planSlides, ...finalSlide];
}

function iconColor(slide) {
    return slide.color.startsWith('wc-') ? 'text-wc-accent' : `text-${slide.color}`;
}
function iconBg(slide) {
    return slide.color.startsWith('wc-') ? 'bg-wc-accent/10' : `bg-${slide.color}/10`;
}
</script>

<template>
  <Transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div class="fixed inset-0 z-[70] flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
      <div class="relative w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-secondary shadow-2xl overflow-hidden">

        <!-- Close -->
        <button
          @click="finish"
          class="absolute top-4 right-4 z-10 text-wc-text-tertiary hover:text-wc-text transition-colors"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Progress bar -->
        <div class="h-1 bg-wc-bg-tertiary">
          <div
            class="h-full bg-wc-accent transition-all duration-500"
            :style="{ width: ((current + 1) / total * 100) + '%' }"
          ></div>
        </div>

        <!-- Slides -->
        <div class="p-6 sm:p-8">
          <template v-for="(slide, index) in slides" :key="index">
            <Transition
              enter-active-class="transition ease-out duration-300"
              enter-from-class="opacity-0 translate-x-4"
              enter-to-class="opacity-100 translate-x-0"
              mode="out-in"
            >
              <div v-if="current === index" class="flex flex-col items-center text-center">

                <!-- Icon -->
                <div :class="['mb-5 flex h-16 w-16 items-center justify-center rounded-2xl', iconBg(slide)]">
                  <!-- sparkles -->
                  <svg v-if="slide.icon === 'sparkles'" :class="['h-8 w-8', iconColor(slide)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                  </svg>
                  <!-- dumbbell -->
                  <svg v-else-if="slide.icon === 'dumbbell'" :class="['h-8 w-8', iconColor(slide)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-4.5 0a1.5 1.5 0 0 0-1.5 1.5v3a1.5 1.5 0 0 0 1.5 1.5h1.5a1.5 1.5 0 0 0 1.5-1.5v-3m12-4.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5m3 0a1.5 1.5 0 0 1-1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3m0-4.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3" />
                  </svg>
                  <!-- nutrition -->
                  <svg v-else-if="slide.icon === 'nutrition'" :class="['h-8 w-8', iconColor(slide)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                  </svg>
                  <!-- habits -->
                  <svg v-else-if="slide.icon === 'habits'" :class="['h-8 w-8', iconColor(slide)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                  <!-- elite -->
                  <svg v-else-if="slide.icon === 'elite'" :class="['h-8 w-8', iconColor(slide)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                  </svg>
                  <!-- fire -->
                  <svg v-else-if="slide.icon === 'fire'" :class="['h-8 w-8', iconColor(slide)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                  </svg>
                  <!-- chart -->
                  <svg v-else-if="slide.icon === 'chart'" :class="['h-8 w-8', iconColor(slide)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                  </svg>
                  <!-- rocket (default) -->
                  <svg v-else :class="['h-8 w-8', iconColor(slide)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                  </svg>
                </div>

                <!-- Title -->
                <h2 class="font-display text-2xl tracking-wide text-wc-text">{{ slide.title.toUpperCase() }}</h2>

                <!-- Description -->
                <p class="mt-3 text-sm text-wc-text-tertiary leading-relaxed max-w-sm">{{ slide.description }}</p>

                <!-- Features -->
                <div v-if="slide.features" class="mt-5 w-full max-w-xs space-y-2">
                  <div v-for="feature in slide.features" :key="feature" class="flex items-center gap-2 text-left">
                    <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    <span class="text-xs text-wc-text-secondary">{{ feature }}</span>
                  </div>
                </div>

                <!-- CTA (last slide) -->
                <button
                  v-if="slide.cta"
                  @click="finish"
                  class="mt-6 w-full max-w-xs rounded-xl bg-wc-accent px-6 py-3 font-display text-lg tracking-wider text-white shadow-lg shadow-wc-accent/20 transition-all hover:bg-wc-accent-hover active:scale-[0.98]"
                >
                  COMENZAR
                </button>
              </div>
            </Transition>
          </template>
        </div>

        <!-- Navigation -->
        <div class="flex items-center justify-between border-t border-wc-border px-6 py-4">
          <button
            v-if="current > 0"
            @click="prev"
            class="text-sm text-wc-text-secondary hover:text-wc-text transition-colors"
          >
            Anterior
          </button>
          <div v-else></div>

          <!-- Dots -->
          <div class="flex items-center gap-1.5">
            <div
              v-for="(_, i) in slides"
              :key="i"
              :class="[
                'h-1.5 rounded-full transition-all duration-300',
                current === i ? 'w-4 bg-wc-accent' : 'w-1.5 bg-wc-bg-tertiary',
              ]"
            ></div>
          </div>

          <button
            v-if="current < total - 1"
            @click="next"
            class="flex items-center gap-1 text-sm font-medium text-wc-accent hover:text-wc-accent-hover transition-colors"
          >
            Siguiente
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
          </button>
          <div v-else></div>
        </div>
      </div>
    </div>
  </Transition>
</template>
