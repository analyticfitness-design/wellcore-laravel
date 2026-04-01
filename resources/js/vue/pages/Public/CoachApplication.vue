<script setup>
import { ref } from 'vue';
import axios from 'axios';
import PublicLayout from '../../layouts/PublicLayout.vue';

const isLoading = ref(false);
const success = ref(false);
const errorMessage = ref('');
const errors = ref({});

const form = ref({
  name: '',
  email: '',
  whatsapp: '',
  city: '',
  bio: '',
  experience: '',
  plan: '',
  current_clients: '',
  specializations: [],
  referral: '',
});

const specializationOptions = [
  'Fuerza',
  'Hipertrofia',
  'Perdida de grasa',
  'CrossFit',
  'Calistenia',
  'Running',
  'Yoga/Movilidad',
  'Adulto mayor',
  'Prenatal/Postnatal',
  'Rehabilitacion',
];

function fieldError(field) {
  if (errors.value[field]) {
    return Array.isArray(errors.value[field]) ? errors.value[field][0] : errors.value[field];
  }
  return '';
}

function toggleSpecialization(spec) {
  const idx = form.value.specializations.indexOf(spec);
  if (idx === -1) {
    form.value.specializations.push(spec);
  } else {
    form.value.specializations.splice(idx, 1);
  }
}

async function submit() {
  isLoading.value = true;
  errorMessage.value = '';
  errors.value = {};

  // Client-side validation
  const e = {};
  if (!form.value.name) e.name = 'El nombre es obligatorio.';
  if (!form.value.email) e.email = 'El email es obligatorio.';
  if (!form.value.whatsapp) e.whatsapp = 'El WhatsApp es obligatorio.';
  if (!form.value.bio) e.bio = 'Cuentanos sobre ti.';
  if (!form.value.experience) e.experience = 'Selecciona tu experiencia.';
  if (!form.value.plan) e.plan = 'Selecciona un tipo de plan.';

  if (Object.keys(e).length > 0) {
    errors.value = e;
    isLoading.value = false;
    return;
  }

  try {
    await axios.post('/api/v/public/coach-apply', form.value);
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
          <h2 class="text-2xl font-bold text-wc-text font-display">Aplicacion Enviada</h2>
          <p class="mt-3 text-wc-text-secondary">Gracias por tu interes en ser coach de WellCore. Revisaremos tu aplicacion y te contactaremos pronto.</p>
          <a href="/" class="mt-6 inline-flex rounded-full bg-wc-accent px-8 py-3 font-semibold text-white hover:bg-wc-accent-hover active:scale-[0.98]">
            Volver al Inicio
          </a>
        </div>

        <!-- Form -->
        <div v-else>
          <!-- Header -->
          <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-wc-text font-display sm:text-4xl">Aplica como Coach</h1>
            <p class="mt-2 text-wc-text-secondary">Unete al equipo de coaches de WellCore Fitness</p>
          </div>

          <!-- Error message -->
          <div v-if="errorMessage" class="mb-6 flex items-start gap-3 rounded-xl border border-red-500/30 bg-red-500/10 p-4">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <p class="text-sm text-red-400">{{ errorMessage }}</p>
          </div>

          <form @submit.prevent="submit" class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8 shadow-xl">
            <div class="space-y-5">
              <!-- Name -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Nombre completo *</label>
                <input v-model="form.name" type="text" placeholder="Tu nombre completo" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                <p v-if="fieldError('name')" class="mt-1 text-xs text-red-500">{{ fieldError('name') }}</p>
              </div>

              <!-- Email -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Email *</label>
                <input v-model="form.email" type="email" placeholder="tu@email.com" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                <p v-if="fieldError('email')" class="mt-1 text-xs text-red-500">{{ fieldError('email') }}</p>
              </div>

              <!-- WhatsApp + City -->
              <div class="grid gap-5 sm:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">WhatsApp *</label>
                  <input v-model="form.whatsapp" type="tel" placeholder="+57 300 123 4567" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                  <p v-if="fieldError('whatsapp')" class="mt-1 text-xs text-red-500">{{ fieldError('whatsapp') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Ciudad</label>
                  <input v-model="form.city" type="text" placeholder="Tu ciudad" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                </div>
              </div>

              <!-- Bio -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Cuentanos sobre ti *</label>
                <textarea v-model="form.bio" rows="4" placeholder="Tu formacion, experiencia, filosofia de coaching..." class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                <p v-if="fieldError('bio')" class="mt-1 text-xs text-red-500">{{ fieldError('bio') }}</p>
              </div>

              <!-- Experience + Plan -->
              <div class="grid gap-5 sm:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Anos de experiencia *</label>
                  <select v-model="form.experience" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="1-2">1 - 2 anos</option>
                    <option value="3-5">3 - 5 anos</option>
                    <option value="5-10">5 - 10 anos</option>
                    <option value="10+">10+ anos</option>
                  </select>
                  <p v-if="fieldError('experience')" class="mt-1 text-xs text-red-500">{{ fieldError('experience') }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Tipo de coaching *</label>
                  <select v-model="form.plan" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    <option value="" disabled>Seleccionar</option>
                    <option value="training">Entrenamiento</option>
                    <option value="nutrition">Nutricion</option>
                    <option value="both">Ambos</option>
                  </select>
                  <p v-if="fieldError('plan')" class="mt-1 text-xs text-red-500">{{ fieldError('plan') }}</p>
                </div>
              </div>

              <!-- Current clients -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Clientes actuales</label>
                <select v-model="form.current_clients" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                  <option value="" disabled>Seleccionar</option>
                  <option value="0">0</option>
                  <option value="1-5">1 - 5</option>
                  <option value="6-15">6 - 15</option>
                  <option value="16+">16+</option>
                </select>
              </div>

              <!-- Specializations -->
              <div>
                <label class="mb-3 block text-sm font-medium text-wc-text-secondary">Especializaciones</label>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                  <button
                    v-for="spec in specializationOptions"
                    :key="spec"
                    type="button"
                    @click="toggleSpecialization(spec)"
                    :class="[
                      'rounded-lg border px-3 py-2.5 text-left text-xs font-medium transition-all',
                      form.specializations.includes(spec)
                        ? 'border-wc-accent bg-wc-accent/10 text-wc-accent'
                        : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:border-wc-text-tertiary',
                    ]"
                  >
                    <span class="flex items-center gap-1.5">
                      <svg v-if="form.specializations.includes(spec)" class="h-3.5 w-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                      </svg>
                      <span v-else class="h-3.5 w-3.5 flex-shrink-0 rounded-full border border-current"></span>
                      {{ spec }}
                    </span>
                  </button>
                </div>
              </div>

              <!-- Referral -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Referido por</label>
                <input v-model="form.referral" type="text" placeholder="Nombre de quien te refirio (opcional)" class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
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
                {{ isLoading ? 'Enviando...' : 'Enviar Aplicacion' }}
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </PublicLayout>
</template>
