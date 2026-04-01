<script setup>
import { ref } from 'vue';
import axios from 'axios';
import PublicLayout from '../../layouts/PublicLayout.vue';

const isLoading = ref(false);
const success = ref(false);
const errorMessage = ref('');
const errors = ref({});

const form = ref({
  nombre: '',
  apellido: '',
  email: '',
  whatsapp: '',
  edad: '',
  ciudad: 'Bogota',
  objetivo: '',
  experiencia: '',
  horario: '',
  dias_disponibles: '',
  lesion: 'no',
  detalle_lesion: '',
});

function fieldError(field) {
  if (errors.value[field]) {
    return Array.isArray(errors.value[field]) ? errors.value[field][0] : errors.value[field];
  }
  return '';
}

async function submit() {
  isLoading.value = true;
  errorMessage.value = '';
  errors.value = {};

  // Client-side validation
  const e = {};
  if (!form.value.nombre) e.nombre = 'El nombre es obligatorio.';
  if (!form.value.apellido) e.apellido = 'El apellido es obligatorio.';
  if (!form.value.email) e.email = 'El email es obligatorio.';
  if (!form.value.whatsapp) e.whatsapp = 'El WhatsApp es obligatorio.';
  if (!form.value.edad) e.edad = 'La edad es obligatoria.';
  if (!form.value.objetivo) e.objetivo = 'Selecciona tu objetivo.';
  if (!form.value.experiencia) e.experiencia = 'Selecciona tu experiencia.';
  if (!form.value.horario) e.horario = 'Selecciona tu horario preferido.';
  if (!form.value.dias_disponibles) e.dias_disponibles = 'Selecciona los dias disponibles.';
  if (form.value.lesion === 'si' && !form.value.detalle_lesion) {
    e.detalle_lesion = 'Describe tu lesion.';
  }

  if (Object.keys(e).length > 0) {
    errors.value = e;
    isLoading.value = false;
    return;
  }

  try {
    await axios.post('/api/v/public/presencial', form.value);
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
          <h2 class="text-2xl font-bold text-wc-text font-display">Inscripcion Enviada</h2>
          <p class="mt-3 text-wc-text-secondary">Hemos recibido tu inscripcion para entrenamiento presencial. Te contactaremos por WhatsApp para coordinar tu primera sesion.</p>
          <a href="/" class="mt-6 inline-flex rounded-full bg-wc-accent px-8 py-3 font-semibold text-white hover:bg-wc-accent-hover active:scale-[0.98]">
            Volver al Inicio
          </a>
        </div>

        <!-- Form -->
        <div v-else>
          <!-- Header -->
          <div class="mb-8 text-center">
            <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-wc-accent/10 px-4 py-1.5">
              <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
              <span class="text-xs font-semibold text-wc-accent uppercase tracking-wide">Presencial</span>
            </div>
            <h1 class="text-3xl font-bold text-wc-text font-display sm:text-4xl">Entrenamiento Presencial</h1>
            <p class="mt-2 text-wc-text-secondary">Sesiones personalizadas cara a cara en Bogota</p>
          </div>

          <!-- Error message -->
          <div v-if="errorMessage" class="mb-6 flex items-start gap-3 rounded-xl border border-red-500/30 bg-red-500/10 p-4">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <p class="text-sm text-red-400">{{ errorMessage }}</p>
          </div>

          <form @submit.prevent="submit" class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8 shadow-xl">
            <div class="grid gap-5 sm:grid-cols-2">
              <!-- Nombre -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Nombre *</label>
                <input v-model="form.nombre" type="text" placeholder="Tu nombre" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                <p v-if="fieldError('nombre')" class="mt-1 text-xs text-red-500">{{ fieldError('nombre') }}</p>
              </div>

              <!-- Apellido -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Apellido *</label>
                <input v-model="form.apellido" type="text" placeholder="Tu apellido" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                <p v-if="fieldError('apellido')" class="mt-1 text-xs text-red-500">{{ fieldError('apellido') }}</p>
              </div>

              <!-- Email -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Email *</label>
                <input v-model="form.email" type="email" placeholder="tu@email.com" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                <p v-if="fieldError('email')" class="mt-1 text-xs text-red-500">{{ fieldError('email') }}</p>
              </div>

              <!-- WhatsApp -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">WhatsApp *</label>
                <input v-model="form.whatsapp" type="tel" placeholder="+57 300 123 4567" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                <p v-if="fieldError('whatsapp')" class="mt-1 text-xs text-red-500">{{ fieldError('whatsapp') }}</p>
              </div>

              <!-- Edad -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Edad *</label>
                <input v-model="form.edad" type="number" placeholder="25" min="14" max="80" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                <p v-if="fieldError('edad')" class="mt-1 text-xs text-red-500">{{ fieldError('edad') }}</p>
              </div>

              <!-- Ciudad -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Ciudad</label>
                <input v-model="form.ciudad" type="text" placeholder="Bogota" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>

              <!-- Objetivo -->
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

              <!-- Experiencia -->
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

              <!-- Horario -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Horario preferido *</label>
                <select v-model="form.horario" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                  <option value="" disabled>Seleccionar</option>
                  <option value="5:00-7:00">5:00 AM - 7:00 AM</option>
                  <option value="7:00-9:00">7:00 AM - 9:00 AM</option>
                  <option value="9:00-11:00">9:00 AM - 11:00 AM</option>
                  <option value="11:00-13:00">11:00 AM - 1:00 PM</option>
                  <option value="14:00-16:00">2:00 PM - 4:00 PM</option>
                  <option value="16:00-18:00">4:00 PM - 6:00 PM</option>
                  <option value="18:00-20:00">6:00 PM - 8:00 PM</option>
                  <option value="20:00-22:00">8:00 PM - 10:00 PM</option>
                </select>
                <p v-if="fieldError('horario')" class="mt-1 text-xs text-red-500">{{ fieldError('horario') }}</p>
              </div>

              <!-- Dias disponibles -->
              <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Dias disponibles por semana *</label>
                <div class="flex gap-3 mt-2">
                  <label
                    v-for="d in ['3', '4', '5']"
                    :key="d"
                    :class="[
                      'flex h-12 w-16 cursor-pointer items-center justify-center rounded-lg border-2 text-sm font-semibold transition-all',
                      form.dias_disponibles === d
                        ? 'border-wc-accent bg-wc-accent/10 text-wc-accent'
                        : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:border-wc-text-tertiary',
                    ]"
                  >
                    <input v-model="form.dias_disponibles" type="radio" :value="d" class="sr-only">
                    {{ d }} dias
                  </label>
                </div>
                <p v-if="fieldError('dias_disponibles')" class="mt-1 text-xs text-red-500">{{ fieldError('dias_disponibles') }}</p>
              </div>

              <!-- Lesion -->
              <div class="sm:col-span-2">
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

              <!-- Detalle lesion -->
              <div v-if="form.lesion === 'si'" class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Describe tu lesion *</label>
                <textarea v-model="form.detalle_lesion" rows="3" placeholder="Tipo de lesion, zona afectada, tiempo..." class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                <p v-if="fieldError('detalle_lesion')" class="mt-1 text-xs text-red-500">{{ fieldError('detalle_lesion') }}</p>
              </div>
            </div>

            <!-- Submit -->
            <div class="mt-8">
              <button
                type="submit"
                :disabled="isLoading"
                class="flex w-full items-center justify-center gap-2 rounded-full bg-wc-accent py-3 font-semibold text-white hover:bg-wc-accent-hover active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
              >
                <svg v-if="isLoading" class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isLoading ? 'Enviando...' : 'Enviar Inscripcion' }}
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </PublicLayout>
</template>
