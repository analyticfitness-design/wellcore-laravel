<script setup>
import { ref } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

const generating = ref(false);
const error = ref(null);
const generatedPlan = ref(null);

const form = ref({
    methodology: 'hypertrophy',
    level: 'intermediate',
    daysPerWeek: 4,
    duration: 8,
    goal: '',
    equipment: 'full_gym',
    clientName: '',
    notes: '',
});

const methodologies = [
    { value: 'hypertrophy', label: 'Hipertrofia' },
    { value: 'strength', label: 'Fuerza' },
    { value: 'functional', label: 'Funcional' },
    { value: 'powerbuilding', label: 'Powerbuilding' },
    { value: 'fat_loss', label: 'Perdida de grasa' },
    { value: 'athletic', label: 'Atletico' },
];

const levels = [
    { value: 'beginner', label: 'Principiante' },
    { value: 'intermediate', label: 'Intermedio' },
    { value: 'advanced', label: 'Avanzado' },
];

const equipmentOptions = [
    { value: 'full_gym', label: 'Gym completo' },
    { value: 'home', label: 'Casa (basico)' },
    { value: 'minimal', label: 'Equipamiento minimo' },
    { value: 'bodyweight', label: 'Peso corporal' },
];

async function generatePlan() {
    generating.value = true;
    error.value = null;
    generatedPlan.value = null;
    try {
        const response = await api.post('/api/v/admin/ai-generator', form.value);
        generatedPlan.value = response.data;
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al generar plan';
    } finally {
        generating.value = false;
    }
}

function resetForm() {
    generatedPlan.value = null;
    error.value = null;
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">Generador IA de Planes</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Genera planes de entrenamiento personalizados con inteligencia artificial</p>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <!-- Form -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">Parametros del Plan</h3>
          <form @submit.prevent="generatePlan" class="space-y-4">
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Nombre del cliente (opcional)</label>
              <input v-model="form.clientName" type="text" placeholder="Para personalizar el plan" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Metodologia</label>
                <select v-model="form.methodology" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20">
                  <option v-for="m in methodologies" :key="m.value" :value="m.value">{{ m.label }}</option>
                </select>
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Nivel</label>
                <select v-model="form.level" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20">
                  <option v-for="l in levels" :key="l.value" :value="l.value">{{ l.label }}</option>
                </select>
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Dias por semana</label>
                <select v-model="form.daysPerWeek" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20">
                  <option :value="3">3 dias</option>
                  <option :value="4">4 dias</option>
                  <option :value="5">5 dias</option>
                  <option :value="6">6 dias</option>
                </select>
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Duracion (semanas)</label>
                <select v-model="form.duration" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20">
                  <option :value="4">4 semanas</option>
                  <option :value="8">8 semanas</option>
                  <option :value="12">12 semanas</option>
                  <option :value="16">16 semanas</option>
                </select>
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Equipamiento</label>
                <select v-model="form.equipment" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20">
                  <option v-for="e in equipmentOptions" :key="e.value" :value="e.value">{{ e.label }}</option>
                </select>
              </div>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Objetivo especifico</label>
              <textarea v-model="form.goal" rows="2" placeholder="Describir el objetivo del cliente..." class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"></textarea>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Notas adicionales</label>
              <textarea v-model="form.notes" rows="2" placeholder="Lesiones, preferencias, restricciones..." class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"></textarea>
            </div>
            <button type="submit" :disabled="generating" class="w-full rounded-lg bg-gradient-to-r from-red-600 to-red-500 px-4 py-2.5 text-sm font-medium text-white hover:from-red-700 hover:to-red-600 transition-colors disabled:opacity-50">
              <span v-if="generating" class="flex items-center justify-center gap-2">
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Generando con IA...
              </span>
              <span v-else class="flex items-center justify-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                </svg>
                Generar Plan
              </span>
            </button>
          </form>
        </div>

        <!-- Preview / Result -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">Vista Previa del Plan</h3>

          <!-- Error -->
          <div v-if="error" class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 p-4 text-center">
            <p class="text-sm text-wc-text">{{ error }}</p>
            <button @click="resetForm" class="mt-3 text-sm font-medium text-wc-accent hover:underline">Intentar de nuevo</button>
          </div>

          <!-- Generating -->
          <div v-else-if="generating" class="flex flex-col items-center justify-center py-16">
            <svg class="h-12 w-12 animate-spin text-wc-accent/30" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="mt-4 text-sm text-wc-text-tertiary">Generando plan personalizado...</p>
            <p class="mt-1 text-xs text-wc-text-tertiary/60">Esto puede tomar 15-30 segundos</p>
          </div>

          <!-- Generated Result -->
          <div v-else-if="generatedPlan" class="space-y-4">
            <div class="rounded-lg bg-emerald-500/10 border border-emerald-500/20 p-3">
              <p class="text-xs font-medium text-emerald-500">Plan generado exitosamente</p>
            </div>
            <div v-if="generatedPlan.summary" class="text-sm text-wc-text-secondary whitespace-pre-wrap">{{ generatedPlan.summary }}</div>
            <div v-if="generatedPlan.weeks" class="space-y-3">
              <div v-for="(week, idx) in generatedPlan.weeks" :key="idx" class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
                <p class="text-xs font-medium text-wc-text">Semana {{ idx + 1 }}</p>
                <div v-if="week.days" class="mt-2 space-y-1">
                  <div v-for="(day, dIdx) in week.days" :key="dIdx" class="text-xs text-wc-text-tertiary">
                    <span class="font-medium text-wc-text-secondary">Dia {{ dIdx + 1 }}:</span> {{ day.focus || day.name || '-' }}
                  </div>
                </div>
              </div>
            </div>
            <button @click="resetForm" class="w-full rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
              Generar otro plan
            </button>
          </div>

          <!-- Empty state -->
          <div v-else class="flex flex-col items-center justify-center py-16">
            <svg class="h-16 w-16 text-wc-text-tertiary/20" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456Z" />
            </svg>
            <p class="mt-4 text-sm text-wc-text-tertiary">Configura los parametros y genera un plan</p>
            <p class="mt-1 text-xs text-wc-text-tertiary/60">El plan se mostrara aqui</p>
          </div>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
