<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import PublicLayout from '../../layouts/PublicLayout.vue';

const route = useRoute();
const invitationCode = computed(() => route.params.code || null);
const isInvitation = computed(() => !!invitationCode.value);

const step = ref(0);
const isLoading = ref(false);
const success = ref(false);
const errorMessage = ref('');
const errors = ref({});

// Invitation state — initialize loading=true synchronously when in invitation mode
// to avoid a flash of the form before the resolve call kicks off in onMounted.
const invitationLoading = ref(!!route.params.code);
const invitationError = ref(null); // null | 'expired' | 'used' | 'invalid' | 'network'
const invitationErrorMessage = ref('');
const invitationData = ref(null); // { plan, plan_label, email_hint }

// Step 0 — Plan selection
const form = ref({
  plan: '',
  // Step 1 — Basic info
  nombre: '',
  apellido: '',
  email: '',
  whatsapp: '',
  edad: '',
  peso: '',
  estatura: '',
  genero: '',
  objetivo: '',
  ciudad: '',
  pais: 'Colombia',
  // Step 2 — Experience
  experiencia: '',
  dias_disponibles: '',
  equipamiento: '',
  coaching_previo: '',
  rutina_actual: '',
  // Step 3 — Preferences
  tipo_entrenamiento: '',
  duracion_sesion: '',
  horario: '',
  restricciones_ejercicio: '',
  // Step 4 — Injuries
  lesion: 'no',
  detalle_lesion: '',
  condiciones_medicas: '',
  medicamentos: '',
  // Step 5 — Nutrition
  dieta_actual: '',
  alergias: '',
  comidas_dia: '',
  experiencia_macros: '',
  alimentos_excluir: '',
  // Step 6 — Lifestyle
  horario_trabajo: '',
  comer_fuera: '',
  nivel_estres: '',
  horas_sueno: '',
  // Step 7 — Final
  como_conocio: '',
  notas: '',
  password: '',
  password_confirmation: '',
  terminos: false,
  // Invitation-only conditional fields
  horario_preferido: '',
  compromiso_30dias: false,
  objetivo_composicion: '',
  historial_medico: '',
  ciclo_hormonal: 'no',
  bloodwork_disponible: 'no',
});

const plans = [
  {
    id: 'esencial',
    name: 'Esencial',
    price: '$149,900',
    description: 'Plan de entrenamiento personalizado con seguimiento basico.',
    features: ['Plan de entrenamiento', 'Seguimiento semanal', 'Soporte por WhatsApp'],
  },
  {
    id: 'metodo',
    name: 'El Metodo',
    price: '$249,900',
    description: 'Entrenamiento + nutricion con coaching integral.',
    features: ['Todo de Esencial', 'Plan nutricional', 'Coaching quincenal', 'Acceso a comunidad'],
  },
  {
    id: 'elite',
    name: 'Elite',
    price: '$399,900',
    description: 'La experiencia completa con acompanamiento premium.',
    features: ['Todo de El Metodo', 'Coaching semanal 1:1', 'Ajustes ilimitados', 'Prioridad en soporte'],
  },
  {
    id: 'entreno_solo',
    name: 'Entreno',
    price: '$170,000',
    description: 'Solo plan de entrenamiento — coach humano ajusta cada mes.',
    features: ['Plan de entrenamiento personalizado', 'Coach humano con ajuste mensual', 'Acceso a plataforma (sin nutricion ni habitos)'],
  },
  {
    id: 'nutricion_solo',
    name: 'Nutricion',
    price: '$153,000',
    description: 'Solo plan nutricional — coach humano ajusta cada mes.',
    features: ['Plan nutricional con macros', '3 opciones por plato', 'Coach humano con ajuste mensual'],
  },
];

// Dynamic step structure.
// Public mode: 0=Plan, 1=Datos basicos, 2=Experiencia, 3=Preferencias, 4=Lesiones, 5=Nutricion, 6=Estilo de vida, 7=Final
// Invitation mode: skip Step 0; insert step "Avanzado" before Final if plan === 'elite'.
// We keep step numbers (0-7) intact for the existing v-show blocks, and use "stepOrder" to define
// which numbers are actually visible in this mode and in which order.
const stepOrder = computed(() => {
  if (!isInvitation.value) {
    // Public mode: planes vertical única omiten steps de la vertical no contratada.
    if (form.value.plan === 'entreno_solo') {
      return [0, 1, 2, 3, 4, 6, 7]; // skip 5 (Nutricion)
    }
    if (form.value.plan === 'nutricion_solo') {
      return [0, 1, 5, 6, 7]; // skip 2,3,4 (Experiencia, Preferencias, Lesiones)
    }
    return [0, 1, 2, 3, 4, 5, 6, 7];
  }
  const plan = invitationData.value?.plan;
  // Step 8 is the new "Avanzado" (Elite-only) step, inserted between Estilo de vida (6) and Final (7).
  if (plan === 'elite') {
    return [1, 2, 3, 4, 5, 6, 8, 7];
  }
  if (plan === 'entreno_solo') {
    return [1, 2, 3, 4, 6, 7];
  }
  if (plan === 'nutricion_solo') {
    return [1, 5, 6, 7];
  }
  return [1, 2, 3, 4, 5, 6, 7];
});

const totalSteps = computed(() => stepOrder.value.length);
const currentIndex = computed(() => stepOrder.value.indexOf(step.value));
const progressPercent = computed(() => {
  const idx = Math.max(0, currentIndex.value);
  const total = totalSteps.value;
  if (total <= 1) return 100;
  return Math.round((idx / (total - 1)) * 100);
});

const ALL_STEP_LABELS = {
  0: 'Plan',
  1: 'Datos basicos',
  2: 'Experiencia',
  3: 'Preferencias',
  4: 'Lesiones',
  5: 'Nutricion',
  6: 'Estilo de vida',
  7: 'Finalizar',
  8: 'Avanzado',
};
const currentStepLabel = computed(() => ALL_STEP_LABELS[step.value] ?? '');

function fieldError(field) {
  if (errors.value[field]) {
    return Array.isArray(errors.value[field]) ? errors.value[field][0] : errors.value[field];
  }
  return '';
}

function validateStep(s) {
  const e = {};
  const plan = isInvitation.value ? invitationData.value?.plan : form.value.plan;

  if (s === 0) {
    if (!form.value.plan) e.plan = 'Selecciona un plan.';
  } else if (s === 1) {
    if (!form.value.nombre) e.nombre = 'El nombre es obligatorio.';
    if (!form.value.apellido) e.apellido = 'El apellido es obligatorio.';
    if (!form.value.email) e.email = 'El email es obligatorio.';
    if (!form.value.whatsapp) e.whatsapp = 'El WhatsApp es obligatorio.';
    if (!form.value.edad) e.edad = 'La edad es obligatoria.';
    if (!form.value.peso) e.peso = 'El peso es obligatorio.';
    if (!form.value.estatura) e.estatura = 'La estatura es obligatoria.';
    if (!form.value.genero) e.genero = 'Selecciona tu genero.';
    if (!form.value.objetivo) e.objetivo = 'Selecciona tu objetivo.';
  } else if (s === 2) {
    if (!form.value.experiencia) e.experiencia = 'Selecciona tu experiencia.';
    if (!form.value.dias_disponibles) e.dias_disponibles = 'Selecciona los dias disponibles.';
    if (!form.value.equipamiento) e.equipamiento = 'Selecciona tu equipamiento.';
  } else if (s === 3) {
    if (!form.value.tipo_entrenamiento) e.tipo_entrenamiento = 'Selecciona el tipo de entrenamiento.';
    if (!form.value.duracion_sesion) e.duracion_sesion = 'Selecciona la duracion.';
    if (isInvitation.value && plan === 'presencial' && !form.value.horario_preferido) {
      e.horario_preferido = 'Selecciona un horario preferido.';
    }
  } else if (s === 4) {
    if (form.value.lesion === 'si' && !form.value.detalle_lesion) {
      e.detalle_lesion = 'Describe tu lesion.';
    }
  } else if (s === 8) {
    // Elite "Avanzado" step
    if (!form.value.objetivo_composicion) e.objetivo_composicion = 'Describe tu objetivo de composicion.';
    if (!form.value.historial_medico) e.historial_medico = 'Comparte tu historial medico.';
  } else if (s === 7) {
    if (!form.value.password) e.password = 'Crea una contrasena.';
    if (form.value.password && form.value.password.length < 8) e.password = 'Minimo 8 caracteres.';
    if (form.value.password !== form.value.password_confirmation) e.password_confirmation = 'Las contrasenas no coinciden.';
    if (!form.value.terminos) e.terminos = 'Debes aceptar los terminos.';
    if (isInvitation.value && plan === 'rise' && !form.value.compromiso_30dias) {
      e.compromiso_30dias = 'Debes aceptar el compromiso de 30 dias.';
    }
  }
  return e;
}

function nextStep() {
  const stepErrors = validateStep(step.value);
  if (Object.keys(stepErrors).length > 0) {
    errors.value = stepErrors;
    return;
  }
  errors.value = {};
  const order = stepOrder.value;
  const idx = order.indexOf(step.value);
  if (idx >= 0 && idx < order.length - 1) {
    step.value = order[idx + 1];
  }
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function prevStep() {
  errors.value = {};
  const order = stepOrder.value;
  const idx = order.indexOf(step.value);
  if (idx > 0) {
    step.value = order[idx - 1];
  }
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

const isFirstStep = computed(() => currentIndex.value <= 0);
const isLastStep = computed(() => currentIndex.value === totalSteps.value - 1);

function selectPlan(planId) {
  form.value.plan = planId;
  errors.value = {};
}

function scrollToFirstError() {
  setTimeout(() => {
    const el = document.querySelector('[data-error="true"]');
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }, 50);
}

async function submit() {
  const stepErrors = validateStep(step.value);
  if (Object.keys(stepErrors).length > 0) {
    errors.value = stepErrors;
    scrollToFirstError();
    return;
  }

  isLoading.value = true;
  errorMessage.value = '';
  errors.value = {};

  try {
    if (isInvitation.value) {
      await axios.post('/api/v/public/invitation-intake', {
        ...form.value,
        invitation_code: invitationCode.value,
      });
    } else {
      await axios.post('/api/v/public/inscription', form.value);
    }
    success.value = true;
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors;
      scrollToFirstError();
    } else if (err.response?.data?.message) {
      errorMessage.value = err.response.data.message;
    } else {
      errorMessage.value = 'Error de conexion. Intenta de nuevo.';
    }
  } finally {
    isLoading.value = false;
  }
}

async function resolveInvitation() {
  if (!isInvitation.value) return;
  invitationLoading.value = true;
  invitationError.value = null;
  try {
    const { data } = await axios.get(`/api/v/public/invitations/${invitationCode.value}`);
    if (data && data.valid) {
      invitationData.value = {
        plan: data.plan,
        plan_label: data.plan_label,
        email_hint: data.email_hint || '',
      };
      // Pre-fill plan from invitation; pre-fill email if provided.
      form.value.plan = data.plan || '';
      if (data.email_hint) form.value.email = data.email_hint;
      // Skip Step 0 (plan selection); start at Datos basicos.
      step.value = 1;
    } else {
      const status = data?.status || 'invalid';
      invitationError.value = ['expired', 'used', 'invalid'].includes(status) ? status : 'invalid';
      invitationErrorMessage.value = data?.message || '';
    }
  } catch (err) {
    if (err.response?.status === 404) {
      invitationError.value = 'invalid';
    } else if (err.response?.data?.status) {
      invitationError.value = err.response.data.status;
      invitationErrorMessage.value = err.response.data.message || '';
    } else {
      invitationError.value = 'network';
      invitationErrorMessage.value = 'No pudimos validar tu invitacion. Intenta de nuevo.';
    }
  } finally {
    invitationLoading.value = false;
  }
}

onMounted(() => {
  if (isInvitation.value) {
    resolveInvitation();
  }
});
</script>

<template>
  <PublicLayout>
    <div class="min-h-screen bg-wc-bg px-4 py-12 sm:py-16">
      <div class="mx-auto max-w-3xl">

        <!-- Invitation: validating -->
        <div v-if="isInvitation && invitationLoading" class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-12 shadow-xl text-center">
          <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-wc-accent/10">
            <svg class="h-8 w-8 animate-spin text-wc-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
          <h2 class="text-xl font-bold text-wc-text font-display">Validando invitacion...</h2>
          <p class="mt-2 text-sm text-wc-text-secondary">Un momento mientras verificamos tu codigo.</p>
        </div>

        <!-- Invitation: error -->
        <div v-else-if="isInvitation && invitationError" class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8 shadow-xl text-center">
          <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-500/10">
            <svg class="h-10 w-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-wc-text font-display">
            <template v-if="invitationError === 'expired'">Invitacion Expirada</template>
            <template v-else-if="invitationError === 'used'">Invitacion Ya Usada</template>
            <template v-else-if="invitationError === 'network'">Error de Conexion</template>
            <template v-else>Invitacion Invalida</template>
          </h2>
          <p class="mt-3 text-wc-text-secondary">
            <template v-if="invitationErrorMessage">{{ invitationErrorMessage }}</template>
            <template v-else-if="invitationError === 'expired'">Tu invitacion ha caducado. Contacta a tu coach para recibir una nueva.</template>
            <template v-else-if="invitationError === 'used'">Esta invitacion ya fue activada. Inicia sesion para acceder a tu cuenta.</template>
            <template v-else-if="invitationError === 'network'">No pudimos validar tu invitacion. Verifica tu conexion e intenta de nuevo.</template>
            <template v-else>El codigo de invitacion no es valido. Verifica el enlace que recibiste.</template>
          </p>
          <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="/" class="inline-flex rounded-full border border-wc-border bg-wc-bg-secondary px-6 py-3 font-semibold text-wc-text hover:bg-wc-bg-tertiary active:scale-[0.98]">
              Volver al inicio
            </a>
            <a v-if="invitationError === 'used'" href="/login" class="inline-flex rounded-full bg-wc-accent px-6 py-3 font-semibold text-white hover:bg-wc-accent-hover active:scale-[0.98]">
              Iniciar Sesion
            </a>
          </div>
        </div>

        <!-- Success state -->
        <div v-else-if="success" class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8 shadow-xl text-center">
          <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-500/10">
            <svg class="h-10 w-10 text-green-500 animate-[scale-in_0.4s_ease-out]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-wc-text font-display">
            {{ isInvitation ? 'Cuenta Activada' : 'Inscripcion Exitosa' }}
          </h2>
          <p class="mt-3 text-wc-text-secondary">
            {{ isInvitation
              ? 'Tu cuenta WellCore esta lista. Inicia sesion para empezar.'
              : 'Tu cuenta ha sido creada. Revisa tu email para los siguientes pasos.' }}
          </p>
          <a href="/login" class="mt-6 inline-flex rounded-full bg-wc-accent px-8 py-3 font-semibold text-white hover:bg-wc-accent-hover active:scale-[0.98]">
            Iniciar Sesion
          </a>
        </div>

        <!-- Form -->
        <div v-else>
          <!-- Header -->
          <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-wc-text font-display sm:text-4xl">
              {{ isInvitation ? 'Completa tu Registro' : 'Comienza tu Transformacion' }}
            </h1>
            <p class="mt-2 text-wc-text-secondary">
              {{ isInvitation
                ? 'Solo unos pasos mas para activar tu cuenta WellCore.'
                : 'Completa el formulario para crear tu plan personalizado' }}
            </p>
          </div>

          <!-- Invitation banner -->
          <div
            v-if="isInvitation && invitationData"
            class="mb-6 flex items-center gap-3 rounded-xl border border-wc-accent/30 bg-wc-accent/10 p-4"
          >
            <svg class="h-6 w-6 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
            </svg>
            <div>
              <p class="text-sm font-semibold text-wc-text">
                Has sido invitado al plan {{ invitationData.plan_label }}
              </p>
              <p class="text-xs text-wc-text-secondary">Completa tus datos para activar tu cuenta.</p>
            </div>
          </div>

          <!-- Progress bar -->
          <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs font-medium text-wc-text-secondary">Paso {{ currentIndex + 1 }} de {{ totalSteps }}</span>
              <span class="text-xs font-medium text-wc-accent">{{ currentStepLabel }}</span>
            </div>
            <div class="h-2 w-full rounded-full bg-wc-bg-secondary">
              <div
                class="h-2 rounded-full bg-wc-accent transition-all duration-500 ease-out"
                :style="{ width: progressPercent + '%' }"
              ></div>
            </div>
          </div>

          <!-- Error message -->
          <div v-if="errorMessage" class="mb-6 flex items-start gap-3 rounded-xl border border-red-500/30 bg-red-500/10 p-4">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <p class="text-sm text-red-400">{{ errorMessage }}</p>
          </div>

          <div class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8 shadow-xl">

            <!-- STEP 0: Plan selection (public mode only) -->
            <div v-if="!isInvitation" v-show="step === 0">
              <h2 class="mb-6 text-xl font-bold text-wc-text">Selecciona tu Plan</h2>
              <div class="grid gap-4 sm:grid-cols-3">
                <button
                  v-for="p in plans"
                  :key="p.id"
                  type="button"
                  @click="selectPlan(p.id)"
                  :class="[
                    'relative rounded-xl border-2 p-5 text-left transition-all hover:shadow-lg',
                    form.plan === p.id
                      ? 'border-wc-accent bg-wc-accent/5 shadow-md'
                      : 'border-wc-border bg-wc-bg-secondary hover:border-wc-text-tertiary',
                  ]"
                >
                  <div v-if="form.plan === p.id" class="absolute top-3 right-3">
                    <svg class="h-5 w-5 text-wc-accent" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                  </div>
                  <h3 class="text-lg font-bold text-wc-text font-display">{{ p.name }}</h3>
                  <p class="mt-1 text-xl font-bold text-wc-accent font-data">{{ p.price }}<span class="text-xs font-normal text-wc-text-secondary"> /mes</span></p>
                  <p class="mt-2 text-xs text-wc-text-secondary">{{ p.description }}</p>
                  <ul class="mt-3 space-y-1">
                    <li v-for="f in p.features" :key="f" class="flex items-center gap-1.5 text-xs text-wc-text-secondary">
                      <svg class="h-3.5 w-3.5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                      {{ f }}
                    </li>
                  </ul>
                </button>
              </div>
              <p v-if="fieldError('plan')" class="mt-2 text-xs text-red-500">{{ fieldError('plan') }}</p>
            </div>

            <!-- STEP 1: Basic info -->
            <div v-show="step === 1">
              <h2 class="mb-6 text-xl font-bold text-wc-text">Datos Basicos</h2>
              <div class="grid gap-5 sm:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Nombre *</label>
                  <input v-model="form.nombre" type="text" placeholder="Tu nombre" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                  <p v-if="fieldError('nombre')" class="mt-1 text-xs text-red-500">{{ fieldError('nombre') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Apellido *</label>
                  <input v-model="form.apellido" type="text" placeholder="Tu apellido" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                  <p v-if="fieldError('apellido')" class="mt-1 text-xs text-red-500">{{ fieldError('apellido') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Email *</label>
                  <input v-model="form.email" type="email" placeholder="tu@email.com" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                  <p v-if="fieldError('email')" class="mt-1 text-xs text-red-500">{{ fieldError('email') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">WhatsApp *</label>
                  <input v-model="form.whatsapp" type="tel" placeholder="+57 300 123 4567" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                  <p v-if="fieldError('whatsapp')" class="mt-1 text-xs text-red-500">{{ fieldError('whatsapp') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Edad *</label>
                  <input v-model="form.edad" type="number" placeholder="25" min="14" max="80" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                  <p v-if="fieldError('edad')" class="mt-1 text-xs text-red-500">{{ fieldError('edad') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Peso (kg) *</label>
                  <input v-model="form.peso" type="number" placeholder="70" step="0.1" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                  <p v-if="fieldError('peso')" class="mt-1 text-xs text-red-500">{{ fieldError('peso') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Estatura (cm) *</label>
                  <input v-model="form.estatura" type="number" placeholder="170" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                  <p v-if="fieldError('estatura')" class="mt-1 text-xs text-red-500">{{ fieldError('estatura') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Genero *</label>
                  <select v-model="form.genero" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="masculino">Masculino</option>
                    <option value="femenino">Femenino</option>
                    <option value="otro">Otro</option>
                  </select>
                  <p v-if="fieldError('genero')" class="mt-1 text-xs text-red-500">{{ fieldError('genero') }}</p>
                </div>
                <div class="sm:col-span-2">
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Objetivo principal *</label>
                  <select v-model="form.objetivo" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="perder_grasa">Perder grasa</option>
                    <option value="ganar_musculo">Ganar musculo</option>
                    <option value="recomposicion">Recomposicion corporal</option>
                    <option value="fuerza">Ganar fuerza</option>
                    <option value="salud">Mejorar salud general</option>
                    <option value="rendimiento">Rendimiento deportivo</option>
                  </select>
                  <p v-if="fieldError('objetivo')" class="mt-1 text-xs text-red-500">{{ fieldError('objetivo') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Ciudad</label>
                  <input v-model="form.ciudad" type="text" placeholder="Bogota" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Pais</label>
                  <input v-model="form.pais" type="text" placeholder="Colombia" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                </div>
              </div>
            </div>

            <!-- STEP 2: Experience -->
            <div v-show="step === 2">
              <h2 class="mb-6 text-xl font-bold text-wc-text">Tu Experiencia</h2>
              <div class="space-y-5">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Nivel de experiencia *</label>
                  <select v-model="form.experiencia" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="principiante">Principiante (0-6 meses)</option>
                    <option value="intermedio">Intermedio (6 meses - 2 anos)</option>
                    <option value="avanzado">Avanzado (2+ anos)</option>
                  </select>
                  <p v-if="fieldError('experiencia')" class="mt-1 text-xs text-red-500">{{ fieldError('experiencia') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Dias disponibles para entrenar *</label>
                  <select v-model="form.dias_disponibles" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="3">3 dias</option>
                    <option value="4">4 dias</option>
                    <option value="5">5 dias</option>
                    <option value="6">6 dias</option>
                  </select>
                  <p v-if="fieldError('dias_disponibles')" class="mt-1 text-xs text-red-500">{{ fieldError('dias_disponibles') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Equipamiento disponible *</label>
                  <select v-model="form.equipamiento" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="gimnasio_completo">Gimnasio completo</option>
                    <option value="gimnasio_basico">Gimnasio basico</option>
                    <option value="casa_equipamiento">Casa con equipamiento</option>
                    <option value="casa_sin_equipamiento">Casa sin equipamiento</option>
                  </select>
                  <p v-if="fieldError('equipamiento')" class="mt-1 text-xs text-red-500">{{ fieldError('equipamiento') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Has tenido coaching previo?</label>
                  <select v-model="form.coaching_previo" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="si">Si</option>
                    <option value="no">No</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Describe tu rutina actual</label>
                  <textarea v-model="form.rutina_actual" rows="3" placeholder="Ejemplo: Hago pesas 3 veces por semana y cardio los fines..." class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                </div>
              </div>
            </div>

            <!-- STEP 3: Preferences -->
            <div v-show="step === 3">
              <h2 class="mb-6 text-xl font-bold text-wc-text">Preferencias de Entrenamiento</h2>
              <div class="space-y-5">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Tipo de entrenamiento preferido *</label>
                  <select v-model="form.tipo_entrenamiento" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="pesas">Pesas / Fuerza</option>
                    <option value="funcional">Funcional</option>
                    <option value="hibrido">Hibrido (pesas + funcional)</option>
                    <option value="calistenia">Calistenia</option>
                    <option value="sin_preferencia">Sin preferencia</option>
                  </select>
                  <p v-if="fieldError('tipo_entrenamiento')" class="mt-1 text-xs text-red-500">{{ fieldError('tipo_entrenamiento') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Duracion por sesion *</label>
                  <select v-model="form.duracion_sesion" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="30-45">30 - 45 min</option>
                    <option value="45-60">45 - 60 min</option>
                    <option value="60-90">60 - 90 min</option>
                    <option value="90+">90+ min</option>
                  </select>
                  <p v-if="fieldError('duracion_sesion')" class="mt-1 text-xs text-red-500">{{ fieldError('duracion_sesion') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Horario preferido</label>
                  <select v-model="form.horario" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="manana">Manana (6am - 10am)</option>
                    <option value="mediodia">Mediodia (10am - 2pm)</option>
                    <option value="tarde">Tarde (2pm - 6pm)</option>
                    <option value="noche">Noche (6pm - 10pm)</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Restricciones de ejercicio</label>
                  <textarea v-model="form.restricciones_ejercicio" rows="3" placeholder="Ejercicios que no puedas o no quieras hacer..." class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                </div>
                <div v-if="isInvitation && invitationData?.plan === 'presencial'">
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Horario preferido para sesiones presenciales *</label>
                  <select v-model="form.horario_preferido" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="manana">Manana</option>
                    <option value="tarde">Tarde</option>
                    <option value="noche">Noche</option>
                  </select>
                  <p v-if="fieldError('horario_preferido')" class="mt-1 text-xs text-red-500">{{ fieldError('horario_preferido') }}</p>
                </div>
              </div>
            </div>

            <!-- STEP 4: Injuries -->
            <div v-show="step === 4">
              <h2 class="mb-6 text-xl font-bold text-wc-text">Lesiones y Salud</h2>
              <div class="space-y-5">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Tienes alguna lesion actual?</label>
                  <div class="flex gap-4 mt-2">
                    <label class="flex cursor-pointer items-center gap-2">
                      <input v-model="form.lesion" type="radio" value="si" class="h-4 w-4 border-wc-border text-wc-accent focus:ring-wc-accent/30">
                      <span class="text-sm text-wc-text">Si</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2">
                      <input v-model="form.lesion" type="radio" value="no" class="h-4 w-4 border-wc-border text-wc-accent focus:ring-wc-accent/30">
                      <span class="text-sm text-wc-text">No</span>
                    </label>
                  </div>
                </div>
                <div v-if="form.lesion === 'si'">
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Describe tu lesion *</label>
                  <textarea v-model="form.detalle_lesion" rows="3" placeholder="Tipo de lesion, zona afectada, tiempo..." class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                  <p v-if="fieldError('detalle_lesion')" class="mt-1 text-xs text-red-500">{{ fieldError('detalle_lesion') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Condiciones medicas</label>
                  <textarea v-model="form.condiciones_medicas" rows="3" placeholder="Diabetes, hipertension, asma, etc." class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Medicamentos actuales</label>
                  <textarea v-model="form.medicamentos" rows="2" placeholder="Medicamentos que tomes actualmente..." class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                </div>
              </div>
            </div>

            <!-- STEP 5: Nutrition -->
            <div v-show="step === 5">
              <h2 class="mb-6 text-xl font-bold text-wc-text">Nutricion</h2>
              <div class="space-y-5">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Dieta actual</label>
                  <select v-model="form.dieta_actual" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="sin_dieta">Sin dieta especifica</option>
                    <option value="equilibrada">Equilibrada</option>
                    <option value="alta_proteina">Alta en proteina</option>
                    <option value="vegetariana">Vegetariana</option>
                    <option value="vegana">Vegana</option>
                    <option value="keto">Keto / Low carb</option>
                    <option value="otra">Otra</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Alergias alimentarias</label>
                  <input v-model="form.alergias" type="text" placeholder="Lactosa, gluten, frutos secos..." class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Cuantas comidas al dia?</label>
                  <select v-model="form.comidas_dia" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="2">2 comidas</option>
                    <option value="3">3 comidas</option>
                    <option value="4">4 comidas</option>
                    <option value="5">5 comidas</option>
                    <option value="6+">6 o mas</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Experiencia con macros</label>
                  <select v-model="form.experiencia_macros" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="ninguna">Ninguna</option>
                    <option value="basica">Basica (se que son)</option>
                    <option value="intermedia">Intermedia (he contado macros)</option>
                    <option value="avanzada">Avanzada (cuento macros regularmente)</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Alimentos que quieras excluir</label>
                  <input v-model="form.alimentos_excluir" type="text" placeholder="Brocoli, higado, etc." class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                </div>
              </div>
            </div>

            <!-- STEP 6: Lifestyle -->
            <div v-show="step === 6">
              <h2 class="mb-6 text-xl font-bold text-wc-text">Estilo de Vida</h2>
              <div class="space-y-5">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Horario de trabajo</label>
                  <select v-model="form.horario_trabajo" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="oficina">Oficina (horario fijo)</option>
                    <option value="remoto">Remoto / Freelance</option>
                    <option value="turnos">Turnos rotativos</option>
                    <option value="estudiante">Estudiante</option>
                    <option value="independiente">Independiente</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Con que frecuencia comes fuera?</label>
                  <select v-model="form.comer_fuera" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="nunca">Casi nunca</option>
                    <option value="1-2">1-2 veces por semana</option>
                    <option value="3-4">3-4 veces por semana</option>
                    <option value="diario">Casi todos los dias</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Nivel de estres</label>
                  <select v-model="form.nivel_estres" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="bajo">Bajo</option>
                    <option value="moderado">Moderado</option>
                    <option value="alto">Alto</option>
                    <option value="muy_alto">Muy alto</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Horas de sueno por noche</label>
                  <select v-model="form.horas_sueno" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="menos_5">Menos de 5 horas</option>
                    <option value="5-6">5 - 6 horas</option>
                    <option value="6-7">6 - 7 horas</option>
                    <option value="7-8">7 - 8 horas</option>
                    <option value="8+">8+ horas</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- STEP 8: Advanced (Elite plan in invitation mode only) -->
            <div v-if="isInvitation && invitationData?.plan === 'elite'" v-show="step === 8">
              <h2 class="mb-6 text-xl font-bold text-wc-text">Informacion Avanzada</h2>
              <div class="space-y-5">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Tu objetivo especifico de composicion corporal *</label>
                  <textarea v-model="form.objetivo_composicion" rows="3" placeholder="Ej: bajar a 12% de grasa manteniendo masa muscular, recomposicion de 6 meses..." class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                  <p v-if="fieldError('objetivo_composicion')" class="mt-1 text-xs text-red-500">{{ fieldError('objetivo_composicion') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Historial medico relevante *</label>
                  <textarea v-model="form.historial_medico" rows="3" placeholder="Cirugias, condiciones cronicas, eventos cardiovasculares, etc." class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                  <p v-if="fieldError('historial_medico')" class="mt-1 text-xs text-red-500">{{ fieldError('historial_medico') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Has hecho ciclos hormonales en el ultimo ano?</label>
                  <div class="flex gap-4 mt-2">
                    <label class="flex cursor-pointer items-center gap-2">
                      <input v-model="form.ciclo_hormonal" type="radio" value="si" class="h-4 w-4 border-wc-border text-wc-accent focus:ring-wc-accent/30">
                      <span class="text-sm text-wc-text">Si</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2">
                      <input v-model="form.ciclo_hormonal" type="radio" value="no" class="h-4 w-4 border-wc-border text-wc-accent focus:ring-wc-accent/30">
                      <span class="text-sm text-wc-text">No</span>
                    </label>
                  </div>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Tienes bloodwork reciente disponible?</label>
                  <div class="flex gap-4 mt-2">
                    <label class="flex cursor-pointer items-center gap-2">
                      <input v-model="form.bloodwork_disponible" type="radio" value="si" class="h-4 w-4 border-wc-border text-wc-accent focus:ring-wc-accent/30">
                      <span class="text-sm text-wc-text">Si</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2">
                      <input v-model="form.bloodwork_disponible" type="radio" value="no" class="h-4 w-4 border-wc-border text-wc-accent focus:ring-wc-accent/30">
                      <span class="text-sm text-wc-text">No</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- STEP 7: Final -->
            <div v-show="step === 7">
              <h2 class="mb-6 text-xl font-bold text-wc-text">
                {{ isInvitation ? 'Finalizar Registro' : 'Finalizar Inscripcion' }}
              </h2>
              <div class="space-y-5">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Como nos conociste?</label>
                  <select v-model="form.como_conocio" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="instagram">Instagram</option>
                    <option value="youtube">YouTube</option>
                    <option value="google">Google</option>
                    <option value="referido">Referido por alguien</option>
                    <option value="otro">Otro</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Notas adicionales</label>
                  <textarea v-model="form.notas" rows="3" placeholder="Algo mas que quieras que sepamos..." class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                </div>
                <div
                  v-if="isInvitation && invitationData?.plan === 'rise'"
                  :data-error="!!fieldError('compromiso_30dias') || null"
                  class="rounded-lg border p-4"
                  :class="fieldError('compromiso_30dias') ? 'border-red-500 bg-red-500/5' : 'border-wc-border bg-wc-bg-secondary'"
                >
                  <label class="flex cursor-pointer items-start gap-3">
                    <input v-model="form.compromiso_30dias" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-wc-border bg-wc-bg-secondary text-wc-accent focus:ring-wc-accent/30">
                    <span class="text-sm text-wc-text-secondary">
                      Me comprometo a seguir el programa Rise por 30 dias sin excepciones.
                    </span>
                  </label>
                  <p v-if="fieldError('compromiso_30dias')" class="mt-2 text-xs font-medium text-red-500">{{ fieldError('compromiso_30dias') }}</p>
                </div>
                <div :data-error="!!fieldError('terminos') || null" class="rounded-lg border p-4" :class="fieldError('terminos') ? 'border-red-500 bg-red-500/5' : 'border-wc-border bg-wc-bg-secondary'">
                  <label class="flex cursor-pointer items-start gap-3">
                    <input v-model="form.terminos" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-wc-border bg-wc-bg-secondary text-wc-accent focus:ring-wc-accent/30">
                    <span class="text-sm text-wc-text-secondary">
                      Acepto los <a href="/terminos" target="_blank" class="text-wc-accent hover:underline">Terminos y Condiciones</a>
                      y la <a href="/privacidad" target="_blank" class="text-wc-accent hover:underline">Politica de Privacidad</a> *
                    </span>
                  </label>
                  <p v-if="fieldError('terminos')" class="mt-2 text-xs font-medium text-red-500">{{ fieldError('terminos') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Contrasena *</label>
                  <input v-model="form.password" type="password" placeholder="Minimo 8 caracteres" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" :class="fieldError('password') ? 'border-red-500' : ''">
                  <p v-if="fieldError('password')" :data-error="true" class="mt-1 text-xs text-red-500">{{ fieldError('password') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Confirmar contrasena *</label>
                  <input v-model="form.password_confirmation" type="password" placeholder="Repite tu contrasena" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" :class="fieldError('password_confirmation') ? 'border-red-500' : ''">
                  <p v-if="fieldError('password_confirmation')" :data-error="true" class="mt-1 text-xs text-red-500">{{ fieldError('password_confirmation') }}</p>
                </div>
              </div>
            </div>

            <!-- Navigation buttons -->
            <div class="mt-8 flex items-center justify-between">
              <button
                v-if="!isFirstStep"
                type="button"
                @click="prevStep"
                class="flex items-center gap-2 rounded-full border border-wc-border bg-wc-bg-secondary px-6 py-3 text-sm font-semibold text-wc-text hover:bg-wc-bg-tertiary active:scale-[0.98]"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                Atras
              </button>
              <div v-else></div>

              <button
                v-if="!isLastStep"
                type="button"
                @click="nextStep"
                class="flex items-center gap-2 rounded-full bg-wc-accent px-8 py-3 font-semibold text-white hover:bg-wc-accent-hover active:scale-[0.98]"
              >
                Siguiente
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
              </button>

              <button
                v-else
                type="button"
                @click="submit"
                :disabled="isLoading"
                class="flex items-center gap-2 rounded-full bg-wc-accent px-8 py-3 font-semibold text-white hover:bg-wc-accent-hover active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
              >
                <svg v-if="isLoading" class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isLoading ? 'Enviando...' : (isInvitation ? 'Activar Cuenta' : 'Completar Inscripcion') }}
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </PublicLayout>
</template>
