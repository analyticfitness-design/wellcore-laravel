<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const success = ref(false);

const settings = ref({
    platformName: 'WellCore Fitness',
    supportEmail: '',
    defaultPlan: 'premium',
    trialDays: 7,
    maintenanceMode: false,
    notificationsEnabled: true,
    autoAssignCoach: false,
});

async function fetchSettings() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/admin/settings');
        if (response.data) {
            settings.value = { ...settings.value, ...response.data };
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar configuracion';
    } finally {
        loading.value = false;
    }
}

async function saveSettings() {
    saving.value = true;
    error.value = null;
    success.value = false;
    try {
        await api.put('/api/v/admin/settings', settings.value);
        success.value = true;
        setTimeout(() => { success.value = false; }, 3000);
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al guardar';
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    fetchSettings();
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">Configuracion</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Ajustes generales de la plataforma</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-4">
        <div v-for="i in 4" :key="i" class="h-16 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      </div>

      <!-- Settings Form -->
      <form v-else @submit.prevent="saveSettings" class="space-y-6">

        <!-- Success -->
        <div v-if="success" class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-3">
          <p class="text-sm font-medium text-emerald-500">Configuracion guardada exitosamente</p>
        </div>

        <!-- Error -->
        <div v-if="error" class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 p-3">
          <p class="text-sm text-wc-text">{{ error }}</p>
        </div>

        <!-- General -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">General</h3>
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Nombre de la plataforma</label>
              <input v-model="settings.platformName" type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Email de soporte</label>
              <input v-model="settings.supportEmail" type="email" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Plan por defecto</label>
              <select v-model="settings.defaultPlan" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20">
                <option value="premium">Premium</option>
                <option value="basic">Basico</option>
                <option value="rise">RISE</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Dias de prueba</label>
              <input v-model.number="settings.trialDays" type="number" min="0" max="30" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20" />
            </div>
          </div>
        </div>

        <!-- Toggles -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">Opciones</h3>
          <div class="space-y-4">
            <label class="flex items-center justify-between cursor-pointer">
              <div>
                <p class="text-sm font-medium text-wc-text">Modo mantenimiento</p>
                <p class="text-xs text-wc-text-tertiary">Deshabilita el acceso de clientes temporalmente</p>
              </div>
              <div class="relative">
                <input v-model="settings.maintenanceMode" type="checkbox" class="sr-only peer" />
                <div class="h-6 w-11 rounded-full bg-wc-bg-secondary peer-checked:bg-wc-accent transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-transform peer-checked:after:translate-x-5"></div>
              </div>
            </label>
            <label class="flex items-center justify-between cursor-pointer">
              <div>
                <p class="text-sm font-medium text-wc-text">Notificaciones habilitadas</p>
                <p class="text-xs text-wc-text-tertiary">Enviar notificaciones push a los clientes</p>
              </div>
              <div class="relative">
                <input v-model="settings.notificationsEnabled" type="checkbox" class="sr-only peer" />
                <div class="h-6 w-11 rounded-full bg-wc-bg-secondary peer-checked:bg-wc-accent transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-transform peer-checked:after:translate-x-5"></div>
              </div>
            </label>
            <label class="flex items-center justify-between cursor-pointer">
              <div>
                <p class="text-sm font-medium text-wc-text">Auto-asignar coach</p>
                <p class="text-xs text-wc-text-tertiary">Asignar coach automaticamente a nuevos clientes</p>
              </div>
              <div class="relative">
                <input v-model="settings.autoAssignCoach" type="checkbox" class="sr-only peer" />
                <div class="h-6 w-11 rounded-full bg-wc-bg-secondary peer-checked:bg-wc-accent transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-transform peer-checked:after:translate-x-5"></div>
              </div>
            </label>
          </div>
        </div>

        <button type="submit" :disabled="saving" class="rounded-lg bg-wc-accent px-6 py-2.5 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50">
          {{ saving ? 'Guardando...' : 'Guardar configuracion' }}
        </button>
      </form>

    </div>
  </AdminLayout>
</template>
