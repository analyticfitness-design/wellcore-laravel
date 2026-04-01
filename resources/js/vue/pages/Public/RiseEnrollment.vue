<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import PublicLayout from '../../layouts/PublicLayout.vue';

const step = ref(1);
const totalSteps = 3;
const isLoading = ref(false);
const success = ref(false);
const errorMessage = ref('');
const errors = ref({});

const form = ref({
  // Step 1 — Personal data
  nombre: '',
  apellido: '',
  email: '',
  whatsapp: '',
  edad: '',
  peso: '',
  estatura: '',
  genero: '',
  ciudad: '',
  pais: 'Colombia',
  // Step 2 — Goals
  objetivo: '',
  experiencia: '',
  ubicacion_entrenamiento: '',
  dias_disponibles: '',
  lesion: 'no',
  detalle_lesion: '',
  motivacion: '',
  // Step 3 — Payment
  payment_reference: '',
});

const riseFeatures = [
  '12 semanas de entrenamiento progresivo',
  'Plan de nutricion personalizado',
  'Coaching semanal grupal',
  'Acceso a comunidad RISE',
  'Seguimiento de habitos diario',
  'Videos demostrativos de ejercicios',
  'Soporte por WhatsApp',
  'Certificado de finalizacion',
];

function fieldError(field) {
  if (errors.value[field]) {
    return Array.isArray(errors.value[field]) ? errors.value[field][0] : errors.value[field];
  }
  return '';
}

function validateStep(s) {
  const e = {};
  if (s === 1) {
    if (!form.value.nombre) e.nombre = 'El nombre es obligatorio.';
    if (!form.value.apellido) e.apellido = 'El apellido es obligatorio.';
    if (!form.value.email) e.email = 'El email es obligatorio.';
    if (!form.value.whatsapp) e.whatsapp = 'El WhatsApp es obligatorio.';
    if (!form.value.edad) e.edad = 'La edad es obligatoria.';
    if (!form.value.peso) e.peso = 'El peso es obligatorio.';
    if (!form.value.estatura) e.estatura = 'La estatura es obligatoria.';
    if (!form.value.genero) e.genero = 'Selecciona tu genero.';
  } else if (s === 2) {
    if (!form.value.objetivo) e.objetivo = 'Describe tu objetivo.';
    if (!form.value.experiencia) e.experiencia = 'Selecciona tu experiencia.';
    if (!form.value.ubicacion_entrenamiento) e.ubicacion_entrenamiento = 'Selecciona donde entrenas.';
    if (!form.value.dias_disponibles) e.dias_disponibles = 'Selecciona los dias disponibles.';
    if (form.value.lesion === 'si' && !form.value.detalle_lesion) {
      e.detalle_lesion = 'Describe tu lesion.';
    }
  } else if (s === 3) {
    if (!form.value.payment_reference) e.payment_reference = 'Ingresa la referencia de pago.';
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
  step.value = Math.min(step.value + 1, totalSteps);
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function prevStep() {
  errors.value = {};
  step.value = Math.max(step.value - 1, 1);
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function submit() {
  const stepErrors = validateStep(step.value);
  if (Object.keys(stepErrors).length > 0) {
    errors.value = stepErrors;
    return;
  }

  isLoading.value = true;
  errorMessage.value = '';
  errors.value = {};

  try {
    await axios.post('/api/v/public/rise-enroll', form.value);
    success.value = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors;
    } else if (err.response?.data?.message) {
      errorMessage.value = err.response.data.message;
    } else {
      errorMessage.value = 'Error de conexion. Intenta de nuevo.';
    }
  } finally {
    isLoading.value = false;
  }
}
</script>

<template>
  <PublicLayout>
    <div class="min-h-screen bg-wc-bg px-4 py-12 sm:py-16">
      <div class="mx-auto max-w-2xl">

        <!-- Success state -->
        <div v-if="success" class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8 shadow-xl text-center">
          <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-500/10">
            <svg class="h-10 w-10 text-green-500 animate-[scale-in_0.4s_ease-out]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-wc-text font-display">Inscripcion RISE Confirmada</h2>
          <p class="mt-3 text-wc-text-secondary">Tu inscripcion al programa RISE ha sido recibida. Verificaremos tu pago y te enviaremos los accesos por email.</p>
          <a href="/" class="mt-6 inline-flex rounded-full bg-wc-accent px-8 py-3 font-semibold text-white hover:bg-wc-accent-hover active:scale-[0.98]">
            Volver al Inicio
          </a>
        </div>

        <!-- Form -->
        <div v-else>
          <!-- Header -->
          <div class="mb-8 text-center">
            <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-wc-accent/10 px-4 py-1.5">
              <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z"/></svg>
              <span class="text-xs font-semibold text-wc-accent uppercase tracking-wide">Programa RISE</span>
            </div>
            <h1 class="text-3xl font-bold text-wc-text font-display sm:text-4xl">Inscripcion RISE</h1>
            <p class="mt-2 text-wc-text-secondary">12 semanas para transformar tu cuerpo y mente</p>
          </div>

          <!-- Step indicator -->
          <div class="mb-8 flex items-center justify-center gap-3">
            <template v-for="s in totalSteps" :key="s">
              <div
                :class="[
                  'flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold transition-all',
                  s < step ? 'bg-green-500 text-white' :
                  s === step ? 'bg-wc-accent text-white shadow-lg shadow-wc-accent/25' :
                  'bg-wc-bg-secondary text-wc-text-tertiary border border-wc-border',
                ]"
              >
                <svg v-if="s < step" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                <span v-else>{{ s }}</span>
              </div>
              <div v-if="s < totalSteps" :class="['h-0.5 w-10 rounded-full transition-all', s < step ? 'bg-green-500' : 'bg-wc-border']"></div>
            </template>
          </div>

          <!-- Error message -->
          <div v-if="errorMessage" class="mb-6 flex items-start gap-3 rounded-xl border border-red-500/30 bg-red-500/10 p-4">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <p class="text-sm text-red-400">{{ errorMessage }}</p>
          </div>

          <div class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8 shadow-xl">

            <!-- STEP 1: Personal data -->
            <div v-show="step === 1">
              <h2 class="mb-6 text-xl font-bold text-wc-text">Datos Personales</h2>
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

            <!-- STEP 2: Goals -->
            <div v-show="step === 2">
              <h2 class="mb-6 text-xl font-bold text-wc-text">Tus Objetivos</h2>
              <div class="space-y-5">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Cual es tu objetivo principal? *</label>
                  <textarea v-model="form.objetivo" rows="3" placeholder="Describe que quieres lograr con el programa RISE..." class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                  <p v-if="fieldError('objetivo')" class="mt-1 text-xs text-red-500">{{ fieldError('objetivo') }}</p>
                </div>
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
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Donde entrenas? *</label>
                  <select v-model="form.ubicacion_entrenamiento" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="gimnasio">Gimnasio</option>
                    <option value="casa">Casa</option>
                    <option value="aire_libre">Aire libre</option>
                    <option value="mixto">Mixto</option>
                  </select>
                  <p v-if="fieldError('ubicacion_entrenamiento')" class="mt-1 text-xs text-red-500">{{ fieldError('ubicacion_entrenamiento') }}</p>
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
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Tienes alguna lesion?</label>
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
                  <textarea v-model="form.detalle_lesion" rows="3" placeholder="Tipo de lesion, zona afectada..." class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                  <p v-if="fieldError('detalle_lesion')" class="mt-1 text-xs text-red-500">{{ fieldError('detalle_lesion') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Que te motiva a unirte a RISE?</label>
                  <textarea v-model="form.motivacion" rows="3" placeholder="Tu motivacion principal..." class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                </div>
              </div>
            </div>

            <!-- STEP 3: Payment -->
            <div v-show="step === 3">
              <h2 class="mb-6 text-xl font-bold text-wc-text">Pago e Inscripcion</h2>

              <!-- Price card -->
              <div class="mb-6 rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-6">
                <div class="flex items-baseline justify-between">
                  <div>
                    <p class="text-sm font-medium text-wc-text-secondary">Programa RISE - 12 semanas</p>
                    <p class="mt-1 text-3xl font-bold text-wc-text font-data">$99,900 <span class="text-base font-normal text-wc-text-secondary">COP</span></p>
                  </div>
                  <div class="flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent/10">
                    <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"/></svg>
                  </div>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                  <div v-for="feature in riseFeatures" :key="feature" class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    <span class="text-xs text-wc-text-secondary">{{ feature }}</span>
                  </div>
                </div>
              </div>

              <!-- Payment reference -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Referencia de pago *</label>
                <input v-model="form.payment_reference" type="text" placeholder="Ingresa el numero de referencia de tu pago" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                <p v-if="fieldError('payment_reference')" class="mt-1 text-xs text-red-500">{{ fieldError('payment_reference') }}</p>
                <p class="mt-2 text-xs text-wc-text-tertiary">Realiza tu pago y coloca aqui la referencia. Verificaremos el pago y activaremos tu acceso.</p>
              </div>
            </div>

            <!-- Navigation buttons -->
            <div class="mt-8 flex items-center justify-between">
              <button
                v-if="step > 1"
                type="button"
                @click="prevStep"
                class="flex items-center gap-2 rounded-full border border-wc-border bg-wc-bg-secondary px-6 py-3 text-sm font-semibold text-wc-text hover:bg-wc-bg-tertiary active:scale-[0.98]"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                Atras
              </button>
              <div v-else></div>

              <button
                v-if="step < totalSteps"
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
                {{ isLoading ? 'Procesando...' : 'Completar Inscripcion' }}
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </PublicLayout>
</template>
