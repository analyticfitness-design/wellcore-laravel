<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();

// State
const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const showSuccess = ref(false);

// Form data
const form = ref({
    name: '',
    email: '',
    city: '',
    birthDate: '',
    whatsapp: '',
    bio: '',
    peso: '',
    altura: '',
    objetivo: '',
    nivel: '',
    lugarEntreno: '',
    diasDisponibles: [],
    restricciones: '',
});
const formErrors = ref({});

const diasSemana = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];

// Fetch profile
async function fetchProfile() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/client/profile');
        const d = response.data;
        form.value = {
            name: d.name || '',
            email: d.email || '',
            city: d.city || '',
            birthDate: d.birthDate || '',
            whatsapp: d.whatsapp || '',
            bio: d.bio || '',
            peso: d.peso || '',
            altura: d.altura || '',
            objetivo: d.objetivo || '',
            nivel: d.nivel || '',
            lugarEntreno: d.lugarEntreno || '',
            diasDisponibles: d.diasDisponibles || [],
            restricciones: d.restricciones || '',
        };
        // Cache name for layout
        if (d.name) {
            localStorage.setItem('wc_user_name', d.name);
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar el perfil';
    } finally {
        loading.value = false;
    }
}

// Save profile
async function saveProfile() {
    saving.value = true;
    formErrors.value = {};
    try {
        await api.put('/api/v/client/profile', {
            name: form.value.name,
            email: form.value.email,
            city: form.value.city,
            birth_date: form.value.birthDate,
            whatsapp: form.value.whatsapp,
            bio: form.value.bio,
            peso: form.value.peso || null,
            altura: form.value.altura || null,
            objetivo: form.value.objetivo,
            nivel: form.value.nivel,
            lugar_entreno: form.value.lugarEntreno,
            dias_disponibles: form.value.diasDisponibles,
            restricciones: form.value.restricciones,
        });
        // Update cached name
        if (form.value.name) {
            localStorage.setItem('wc_user_name', form.value.name);
        }
        showSuccess.value = true;
        setTimeout(() => { showSuccess.value = false; }, 3000);
    } catch (err) {
        if (err.response?.status === 422) {
            formErrors.value = err.response.data.errors || {};
        } else {
            error.value = err.response?.data?.message || 'Error al guardar';
        }
    } finally {
        saving.value = false;
    }
}

function toggleDia(dia) {
    const val = dia.toLowerCase();
    const idx = form.value.diasDisponibles.indexOf(val);
    if (idx >= 0) {
        form.value.diasDisponibles.splice(idx, 1);
    } else {
        form.value.diasDisponibles.push(val);
    }
}

function isDiaSelected(dia) {
    return form.value.diasDisponibles.includes(dia.toLowerCase());
}

onMounted(() => {
    fetchProfile();
});
</script>

<template>
  <ClientLayout>
    <!-- Success Toast -->
    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-2"
    >
      <div
        v-if="showSuccess"
        class="fixed bottom-24 right-4 z-50 flex items-center gap-3 rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 shadow-lg backdrop-blur-sm lg:bottom-6 lg:right-6"
      >
        <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        <span class="text-sm font-medium text-green-400">Perfil actualizado correctamente</span>
      </div>
    </Transition>

    <!-- Header -->
    <div class="mb-8">
      <h1 class="font-display text-3xl tracking-wide text-wc-text">MI PERFIL</h1>
      <p class="mt-1 text-sm text-wc-text-secondary">Actualiza tu informacion personal y datos de entrenamiento</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid gap-8 lg:grid-cols-2">
      <div class="h-96 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      <div class="h-96 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error && !form.name" class="flex flex-col items-center justify-center py-20">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
        <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
      </div>
      <p class="mt-4 text-sm text-wc-text-secondary">{{ error }}</p>
      <button @click="fetchProfile" class="mt-4 rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover">
        Reintentar
      </button>
    </div>

    <!-- Form -->
    <form v-else @submit.prevent="saveProfile">
      <div class="grid gap-8 lg:grid-cols-2">

        <!-- Left Column: Personal Info -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
          <div class="mb-6 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
              <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
              </svg>
            </div>
            <div>
              <h2 class="font-display text-xl tracking-wide text-wc-text">DATOS PERSONALES</h2>
              <p class="text-sm text-wc-text-tertiary">Informacion basica de tu cuenta</p>
            </div>
          </div>

          <div class="space-y-4">
            <!-- Name -->
            <div>
              <label for="name" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Nombre completo</label>
              <input
                v-model="form.name"
                type="text"
                id="name"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                placeholder="Tu nombre"
              >
              <p v-if="formErrors.name" class="mt-1 text-xs text-red-500">{{ formErrors.name[0] }}</p>
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Email</label>
              <input
                v-model="form.email"
                type="email"
                id="email"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                placeholder="tu@email.com"
              >
              <p v-if="formErrors.email" class="mt-1 text-xs text-red-500">{{ formErrors.email[0] }}</p>
            </div>

            <!-- City -->
            <div>
              <label for="city" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Ciudad</label>
              <input
                v-model="form.city"
                type="text"
                id="city"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                placeholder="Tu ciudad"
              >
              <p v-if="formErrors.city" class="mt-1 text-xs text-red-500">{{ formErrors.city[0] }}</p>
            </div>

            <!-- Birth Date -->
            <div>
              <label for="birthDate" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Fecha de nacimiento</label>
              <input
                v-model="form.birthDate"
                type="date"
                id="birthDate"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
              >
              <p v-if="formErrors.birth_date" class="mt-1 text-xs text-red-500">{{ formErrors.birth_date[0] }}</p>
            </div>

            <!-- WhatsApp -->
            <div>
              <label for="whatsapp" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">WhatsApp</label>
              <input
                v-model="form.whatsapp"
                type="text"
                id="whatsapp"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                placeholder="+52 123 456 7890"
              >
              <p v-if="formErrors.whatsapp" class="mt-1 text-xs text-red-500">{{ formErrors.whatsapp[0] }}</p>
            </div>

            <!-- Bio -->
            <div>
              <label for="bio" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Bio</label>
              <textarea
                v-model="form.bio"
                id="bio"
                rows="3"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                placeholder="Cuentanos sobre ti..."
              ></textarea>
              <p v-if="formErrors.bio" class="mt-1 text-xs text-red-500">{{ formErrors.bio[0] }}</p>
            </div>
          </div>
        </div>

        <!-- Right Column: Fitness Info -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
          <div class="mb-6 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
              <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
              </svg>
            </div>
            <div>
              <h2 class="font-display text-xl tracking-wide text-wc-text">DATOS FITNESS</h2>
              <p class="text-sm text-wc-text-tertiary">Tu informacion de entrenamiento</p>
            </div>
          </div>

          <div class="space-y-4">
            <!-- Peso + Altura (inline) -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="peso" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Peso (kg)</label>
                <input
                  v-model="form.peso"
                  type="number"
                  step="0.1"
                  id="peso"
                  class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                  placeholder="75.0"
                >
                <p v-if="formErrors.peso" class="mt-1 text-xs text-red-500">{{ formErrors.peso[0] }}</p>
              </div>
              <div>
                <label for="altura" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Altura (cm)</label>
                <input
                  v-model="form.altura"
                  type="number"
                  step="0.1"
                  id="altura"
                  class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                  placeholder="175.0"
                >
                <p v-if="formErrors.altura" class="mt-1 text-xs text-red-500">{{ formErrors.altura[0] }}</p>
              </div>
            </div>

            <!-- Objetivo -->
            <div>
              <label for="objetivo" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Objetivo</label>
              <input
                v-model="form.objetivo"
                type="text"
                id="objetivo"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                placeholder="Ej: Perder grasa, ganar musculo..."
              >
              <p v-if="formErrors.objetivo" class="mt-1 text-xs text-red-500">{{ formErrors.objetivo[0] }}</p>
            </div>

            <!-- Nivel -->
            <div>
              <label for="nivel" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Nivel</label>
              <select
                v-model="form.nivel"
                id="nivel"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
              >
                <option value="">Selecciona tu nivel</option>
                <option value="principiante">Principiante</option>
                <option value="intermedio">Intermedio</option>
                <option value="avanzado">Avanzado</option>
              </select>
              <p v-if="formErrors.nivel" class="mt-1 text-xs text-red-500">{{ formErrors.nivel[0] }}</p>
            </div>

            <!-- Lugar de Entreno -->
            <div>
              <label for="lugarEntreno" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Lugar de entrenamiento</label>
              <select
                v-model="form.lugarEntreno"
                id="lugarEntreno"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
              >
                <option value="">Selecciona lugar</option>
                <option value="gym">Gimnasio</option>
                <option value="casa">Casa</option>
                <option value="ambos">Ambos</option>
              </select>
              <p v-if="formErrors.lugar_entreno" class="mt-1 text-xs text-red-500">{{ formErrors.lugar_entreno[0] }}</p>
            </div>

            <!-- Dias Disponibles -->
            <div>
              <label class="mb-2 block text-sm font-medium text-wc-text-secondary">Dias disponibles</label>
              <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                <label
                  v-for="dia in diasSemana"
                  :key="dia"
                  :class="[
                    'flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm transition-colors',
                    isDiaSelected(dia)
                      ? 'border-wc-accent/50 bg-wc-accent/10'
                      : 'border-wc-border bg-wc-bg-secondary hover:border-wc-accent/50'
                  ]"
                >
                  <input
                    type="checkbox"
                    :checked="isDiaSelected(dia)"
                    @change="toggleDia(dia)"
                    class="h-4 w-4 rounded border-wc-border bg-wc-bg-secondary text-wc-accent focus:ring-wc-accent/30"
                  >
                  <span class="text-wc-text-secondary">{{ dia }}</span>
                </label>
              </div>
            </div>

            <!-- Restricciones -->
            <div>
              <label for="restricciones" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Restricciones o lesiones</label>
              <textarea
                v-model="form.restricciones"
                id="restricciones"
                rows="3"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                placeholder="Ej: Lesion en rodilla derecha, alergia al gluten..."
              ></textarea>
              <p v-if="formErrors.restricciones" class="mt-1 text-xs text-red-500">{{ formErrors.restricciones[0] }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="mt-8 flex justify-end">
        <button
          type="submit"
          :disabled="saving"
          class="flex w-full items-center justify-center gap-2 rounded-xl bg-wc-accent px-8 py-3.5 text-sm font-semibold text-white transition-all hover:bg-wc-accent-hover active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto sm:py-3"
        >
          <svg v-if="saving" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span v-if="!saving">Guardar Cambios</span>
          <span v-else>Guardando...</span>
        </button>
      </div>
    </form>
  </ClientLayout>
</template>
