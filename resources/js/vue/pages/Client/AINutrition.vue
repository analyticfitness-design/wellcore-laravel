<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();

// State
const loading = ref(false);
const error = ref(null);

// Upload
const selectedFile = ref(null);
const previewUrl = ref(null);
const analyzing = ref(false);

// Result
const analysisResult = ref(null);

// History
const historyLoading = ref(true);
const analysisHistory = ref([]);

// Handle file select
function onFileSelect(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    selectedFile.value = file;

    const reader = new FileReader();
    reader.onload = (e) => {
        previewUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);

    // Clear previous result
    analysisResult.value = null;
}

// Remove file
function removeFile() {
    selectedFile.value = null;
    previewUrl.value = null;
    analysisResult.value = null;
}

// Analyze photo
async function analyzePhoto() {
    if (!selectedFile.value) return;
    analyzing.value = true;
    error.value = null;
    analysisResult.value = null;

    try {
        const formData = new FormData();
        formData.append('image', selectedFile.value);

        const response = await api.post('/api/v/client/ai-nutrition/analyze', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        analysisResult.value = response.data.analysis || response.data;
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al analizar la imagen';
    } finally {
        analyzing.value = false;
    }
}

// Fetch history (optional)
async function fetchHistory() {
    historyLoading.value = true;
    try {
        const response = await api.get('/api/v/client/ai-nutrition');
        analysisHistory.value = response.data.history || [];
    } catch {
        // History is optional
    } finally {
        historyLoading.value = false;
    }
}

onMounted(() => {
    fetchHistory();
});

// Helpers
function getMacroColor(type) {
    const colors = {
        protein: 'bg-wc-accent text-wc-accent',
        carbs: 'bg-blue-500 text-blue-500',
        fat: 'bg-amber-500 text-amber-500',
    };
    return colors[type] || 'bg-wc-bg-secondary text-wc-text-secondary';
}

function getMacroLabel(type) {
    const labels = { protein: 'Proteina', carbs: 'Carbos', fat: 'Grasa' };
    return labels[type] || type;
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('es-CO', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
  <ClientLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">ANALISIS NUTRICIONAL IA</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Toma una foto de tu comida y obtendras un analisis nutricional con inteligencia artificial.</p>
      </div>

      <!-- Upload Section -->
      <div class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h3 class="mb-4 font-semibold text-wc-text">Analizar comida</h3>

        <!-- Photo preview -->
        <div v-if="previewUrl" class="mb-4">
          <div class="group relative mx-auto max-w-sm overflow-hidden rounded-xl border border-wc-accent/30">
            <img :src="previewUrl" alt="Comida a analizar" class="w-full object-cover" style="max-height: 300px;" />
            <button
              @click="removeFile"
              class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-black/60 text-white transition-opacity hover:bg-red-500"
              aria-label="Eliminar foto"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Upload area -->
        <label
          v-else
          class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-wc-border bg-wc-bg py-12 transition-colors hover:border-wc-accent/50"
        >
          <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
            <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
            </svg>
          </div>
          <p class="mt-3 text-sm font-medium text-wc-text">Toca para subir una foto</p>
          <p class="mt-1 text-xs text-wc-text-tertiary">JPG, PNG hasta 10MB</p>
          <input type="file" accept="image/*" class="hidden" @change="onFileSelect" />
        </label>

        <!-- Error -->
        <p v-if="error" class="mt-3 text-xs text-red-400">{{ error }}</p>

        <!-- Analyze button -->
        <button
          v-if="selectedFile && !analysisResult"
          @click="analyzePhoto"
          :disabled="analyzing"
          class="mt-4 w-full rounded-xl bg-wc-accent py-3 text-sm font-semibold text-white transition-colors hover:bg-wc-accent/90 disabled:opacity-50"
        >
          <span v-if="analyzing" class="flex items-center justify-center gap-2">
            <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Analizando con IA...
          </span>
          <span v-else class="flex items-center justify-center gap-2">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
            </svg>
            Analizar con IA
          </span>
        </button>
      </div>

      <!-- Analyzing State -->
      <div v-if="analyzing" class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8 text-center" aria-busy="true">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
          <svg class="h-8 w-8 animate-spin text-wc-accent" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
        </div>
        <h3 class="mt-4 font-display text-xl text-wc-text">ANALIZANDO...</h3>
        <p class="mt-2 text-sm text-wc-text-secondary">La IA esta procesando tu foto. Esto puede tomar unos segundos.</p>
        <div class="mx-auto mt-4 h-1 w-48 overflow-hidden rounded-full bg-wc-bg-secondary">
          <div class="h-full animate-pulse rounded-full bg-wc-accent" style="width: 60%"></div>
        </div>
      </div>

      <!-- Analysis Result -->
      <div v-if="analysisResult" class="space-y-4">
        <!-- Meal name -->
        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/5 p-5">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10">
              <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-medium uppercase tracking-wider text-emerald-400">Comida identificada</p>
              <h3 class="font-display text-xl tracking-wide text-wc-text">{{ analysisResult.meal_name || 'Comida analizada' }}</h3>
            </div>
          </div>
        </div>

        <!-- Calories -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center">
          <p class="text-[10px] font-medium uppercase tracking-widest text-wc-text-tertiary">Calorias estimadas</p>
          <p class="mt-2 font-data text-5xl font-bold text-wc-text">{{ analysisResult.calories || 0 }}</p>
          <p class="mt-1 text-sm text-wc-text-tertiary">kcal</p>
        </div>

        <!-- Macros -->
        <div class="grid grid-cols-3 gap-3">
          <div
            v-for="macro in ['protein', 'carbs', 'fat']"
            :key="macro"
            class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center"
          >
            <div class="mx-auto mb-2 h-1 w-8 rounded-full" :class="getMacroColor(macro).split(' ')[0]"></div>
            <p class="font-data text-2xl font-bold text-wc-text">{{ analysisResult[macro] || 0 }}<span class="text-sm font-normal">g</span></p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">{{ getMacroLabel(macro) }}</p>
          </div>
        </div>

        <!-- Notes & suggestions -->
        <div v-if="analysisResult.notes || analysisResult.suggestions" class="rounded-xl border border-wc-border bg-wc-bg-secondary p-5">
          <div class="flex items-start gap-3">
            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
              <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Notas de la IA</p>
              <p v-if="analysisResult.notes" class="mt-1 text-sm leading-relaxed text-wc-text-secondary">{{ analysisResult.notes }}</p>
              <p v-if="analysisResult.suggestions" class="mt-2 text-sm leading-relaxed text-wc-text-secondary">{{ analysisResult.suggestions }}</p>
            </div>
          </div>
        </div>

        <!-- New analysis button -->
        <button
          @click="removeFile"
          class="w-full rounded-xl border border-wc-border bg-wc-bg-tertiary py-3 text-sm font-medium text-wc-text-secondary transition-colors hover:text-wc-text"
        >
          Analizar otra comida
        </button>
      </div>

      <!-- History -->
      <div v-if="!historyLoading && analysisHistory.length > 0 && !analysisResult" class="space-y-3">
        <h3 class="font-display text-xl tracking-wide text-wc-text">HISTORIAL</h3>

        <div
          v-for="item in analysisHistory"
          :key="item.id"
          class="flex items-center gap-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-4"
        >
          <div v-if="item.image_url" class="h-16 w-16 shrink-0 overflow-hidden rounded-lg border border-wc-border">
            <img :src="item.image_url" alt="" class="h-full w-full object-cover" loading="lazy" />
          </div>
          <div v-else class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary">
            <svg class="h-6 w-6 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" />
            </svg>
          </div>
          <div class="min-w-0 flex-1">
            <h4 class="text-sm font-semibold text-wc-text">{{ item.meal_name || 'Comida analizada' }}</h4>
            <p class="text-xs text-wc-text-tertiary">{{ formatDate(item.created_at) }}</p>
          </div>
          <div class="shrink-0 text-right">
            <p class="font-data text-lg font-bold text-wc-text">{{ item.calories || 0 }}</p>
            <p class="text-[10px] text-wc-text-tertiary">kcal</p>
          </div>
        </div>
      </div>
    </div>
  </ClientLayout>
</template>
