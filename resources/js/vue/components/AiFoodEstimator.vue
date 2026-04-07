<template>
  <Teleport to="body">
    <Transition name="slide-up">
      <div v-if="show" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">

        <!-- Overlay -->
        <div
          class="absolute inset-0 bg-black/60 backdrop-blur-sm"
          @click="!isLoading && $emit('close')"
        />

        <!-- Panel -->
        <div class="relative w-full sm:max-w-md bg-wc-bg-secondary border border-wc-border rounded-t-2xl sm:rounded-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">

          <!-- Header -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <h2 class="font-display text-2xl tracking-wide text-wc-text">ANALIZAR COMIDA</h2>
              <span class="rounded-full bg-wc-accent/10 border border-wc-accent/30 px-2 py-0.5 text-[10px] font-bold tracking-wider text-wc-accent uppercase">IA · Haiku</span>
            </div>
            <button
              @click="$emit('close')"
              :disabled="isLoading"
              class="flex h-8 w-8 items-center justify-center rounded-lg border border-wc-border text-wc-text-tertiary hover:text-wc-text hover:border-wc-border/80 transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- ── INPUT STATE ── -->
          <template v-if="mode === 'input'">

            <!-- Photo area -->
            <div
              @click="triggerCamera"
              :class="imagePreview ? 'border-wc-accent/40 bg-wc-accent/5' : 'border-wc-border hover:border-wc-accent/50 bg-wc-bg-tertiary'"
              class="cursor-pointer rounded-xl border-2 border-dashed p-6 text-center transition-colors"
            >
              <img
                v-if="imagePreview"
                :src="imagePreview"
                class="mx-auto max-h-40 rounded-lg object-cover"
                alt="Preview de comida"
              />
              <div v-else class="flex flex-col items-center gap-2">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-wc-bg border border-wc-border">
                  <svg class="h-6 w-6 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"/>
                  </svg>
                </div>
                <p class="text-sm font-medium text-wc-text-secondary">Toca para tomar foto o cargar imagen</p>
                <p class="text-xs text-wc-text-tertiary">JPG, PNG — máx 5MB</p>
              </div>
            </div>

            <!-- Hidden file input — capture="environment" opens camera on mobile -->
            <input
              ref="fileInput"
              type="file"
              accept="image/*"
              capture="environment"
              class="hidden"
              @change="handleImage"
            />

            <!-- Remove image button if preview exists -->
            <button
              v-if="imagePreview"
              @click.stop="clearImage"
              class="w-full text-xs text-wc-text-tertiary hover:text-wc-text transition-colors"
            >
              Quitar imagen
            </button>

            <!-- Divider -->
            <div class="flex items-center gap-3">
              <div class="h-px flex-1 bg-wc-border"/>
              <span class="text-xs uppercase tracking-wider text-wc-text-tertiary">o describe</span>
              <div class="h-px flex-1 bg-wc-border"/>
            </div>

            <!-- Textarea -->
            <textarea
              v-model="description"
              placeholder="Ej: comí 2 arepas con queso, un jugo de naranja y un huevo frito..."
              rows="3"
              class="w-full resize-none rounded-xl border border-wc-border bg-wc-bg px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent/50 focus:outline-none focus:ring-2 focus:ring-wc-accent/20 transition-colors"
            />

            <!-- Analyze button -->
            <button
              @click="analyze"
              :disabled="!canAnalyze"
              class="w-full rounded-xl bg-wc-accent py-3 text-sm font-bold uppercase tracking-wider text-white transition-opacity disabled:opacity-40 hover:opacity-90"
            >
              Analizar con IA
            </button>
          </template>

          <!-- ── LOADING STATE ── -->
          <template v-else-if="mode === 'loading'">
            <div class="flex flex-col items-center justify-center gap-6 py-10">
              <!-- Spinner -->
              <div class="relative h-16 w-16">
                <svg class="h-full w-full animate-spin" viewBox="0 0 64 64" fill="none">
                  <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" class="text-wc-border"/>
                  <path d="M32 4a28 28 0 0 1 28 28" stroke="#DC2626" stroke-width="4" stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                  <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/>
                  </svg>
                </div>
              </div>
              <div class="text-center">
                <p class="font-display text-xl tracking-wide text-wc-text">Analizando tu comida</p>
                <p class="mt-1 text-sm text-wc-text-tertiary">{{ loadingDots }}</p>
              </div>
            </div>
          </template>

          <!-- ── RESULT STATE ── -->
          <template v-else-if="mode === 'result' && result">

            <!-- Detected description -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3">
              <p class="text-xs uppercase tracking-widest text-wc-text-tertiary mb-1">Detectado</p>
              <p class="text-sm font-medium text-wc-text">{{ result.descripcion }}</p>
            </div>

            <!-- Macro grid 2x2 -->
            <div class="grid grid-cols-2 gap-3">
              <!-- Calories -->
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
                <p class="font-data text-3xl font-bold text-wc-accent leading-none">{{ result.kcal }}</p>
                <p class="mt-1.5 text-[10px] uppercase tracking-widest text-wc-text-tertiary">Calorías</p>
              </div>
              <!-- Protein -->
              <div class="rounded-xl border border-blue-500/20 bg-blue-500/[0.06] p-4 text-center">
                <p class="font-data text-3xl font-bold text-blue-400 leading-none">{{ result.proteina_g }}<span class="text-base font-normal ml-0.5">g</span></p>
                <p class="mt-1.5 text-[10px] uppercase tracking-widest text-blue-400/70">Proteína</p>
              </div>
              <!-- Carbs -->
              <div class="rounded-xl border border-orange-500/20 bg-orange-500/[0.06] p-4 text-center">
                <p class="font-data text-3xl font-bold text-orange-400 leading-none">{{ result.carbohidratos_g }}<span class="text-base font-normal ml-0.5">g</span></p>
                <p class="mt-1.5 text-[10px] uppercase tracking-widest text-orange-400/70">Carbos</p>
              </div>
              <!-- Fat -->
              <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/[0.06] p-4 text-center">
                <p class="font-data text-3xl font-bold text-emerald-400 leading-none">{{ result.grasas_g }}<span class="text-base font-normal ml-0.5">g</span></p>
                <p class="mt-1.5 text-[10px] uppercase tracking-widest text-emerald-400/70">Grasas</p>
              </div>
            </div>

            <!-- Fits plan badge -->
            <div
              :class="result.encaja_en_plan
                ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400'
                : 'border-yellow-500/30 bg-yellow-500/10 text-yellow-400'"
              class="rounded-xl border px-4 py-3 text-sm font-semibold text-center"
            >
              {{ result.encaja_en_plan ? '✓ Encaja en tu plan de hoy' : '⚠ Ajusta las porciones' }}
            </div>

            <!-- AI coach comment -->
            <p class="text-xs italic text-wc-text-secondary text-center leading-relaxed">"{{ result.comentario }}"</p>

            <!-- Analyze another button -->
            <button
              @click="reset"
              class="w-full rounded-xl border border-wc-border py-2.5 text-sm text-wc-text-secondary hover:text-wc-text hover:border-wc-border/80 transition-colors"
            >
              Analizar otra comida
            </button>
          </template>

          <!-- ── ERROR STATE ── -->
          <template v-else-if="mode === 'error'">
            <div class="flex flex-col items-center justify-center gap-5 py-8">
              <div class="flex h-14 w-14 items-center justify-center rounded-full border border-red-500/30 bg-red-500/10">
                <svg class="h-7 w-7 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                </svg>
              </div>
              <div class="text-center">
                <p class="font-display text-xl tracking-wide text-wc-text">Error al analizar</p>
                <p class="mt-1 text-sm text-wc-text-secondary max-w-xs">{{ errorMsg }}</p>
              </div>
              <button
                @click="reset"
                class="rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-bold uppercase tracking-wider text-white hover:opacity-90 transition-opacity"
              >
                Intentar de nuevo
              </button>
            </div>
          </template>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import { useApi } from '../composables/useApi';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close']);

const api = useApi();

// State
const mode = ref('input');
const description = ref('');
const imageFile = ref(null);
const imagePreview = ref(null);
const result = ref(null);
const errorMsg = ref('');

// DOM ref
const fileInput = ref(null);

// Loading dots animation
const loadingDots = ref('Procesando imagen y texto...');
let dotsInterval = null;

const dotStates = [
  'Procesando imagen y texto...',
  'Identificando alimentos...',
  'Calculando macros...',
  'Consultando al coach IA...',
];
let dotsStep = 0;

const isLoading = computed(() => mode.value === 'loading');

const canAnalyze = computed(
  () => description.value.trim().length > 3 || imageFile.value !== null
);

// Start/stop dot animation when loading changes
watch(isLoading, (val) => {
  if (val) {
    dotsStep = 0;
    loadingDots.value = dotStates[0];
    dotsInterval = setInterval(() => {
      dotsStep = (dotsStep + 1) % dotStates.length;
      loadingDots.value = dotStates[dotsStep];
    }, 1500);
  } else {
    clearInterval(dotsInterval);
    dotsInterval = null;
  }
});

// Reset state when panel opens
watch(() => props.show, (val) => {
  if (val) reset();
});

function triggerCamera() {
  fileInput.value?.click();
}

function handleImage(e) {
  const file = e.target.files?.[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) {
    errorMsg.value = 'La imagen no puede pesar más de 5MB.';
    mode.value = 'error';
    return;
  }
  setFile(file);
}

function setFile(file) {
  if (imagePreview.value) URL.revokeObjectURL(imagePreview.value);
  imageFile.value = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
}

function clearImage() {
  imageFile.value = null;
  imagePreview.value = null;
  if (fileInput.value) fileInput.value.value = '';
}

async function analyze() {
  mode.value = 'loading';
  try {
    const formData = new FormData();
    if (imageFile.value) formData.append('image', imageFile.value);
    if (description.value.trim()) formData.append('description', description.value.trim());

    const res = await api.post('/api/v/client/ai-nutrition/estimate', formData);
    result.value = res.data;
    mode.value = 'result';
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Error al analizar. Intenta de nuevo.';
    mode.value = 'error';
  }
}

function reset() {
  mode.value = 'input';
  description.value = '';
  imageFile.value = null;
  imagePreview.value = null;
  result.value = null;
  errorMsg.value = '';
  if (fileInput.value) fileInput.value.value = '';
}

onBeforeUnmount(() => {
  clearInterval(dotsInterval);
  dotsInterval = null;
  if (imagePreview.value) URL.revokeObjectURL(imagePreview.value);
});
</script>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}

@media (min-width: 640px) {
  .slide-up-enter-from,
  .slide-up-leave-to {
    transform: translateY(0) scale(0.95);
    opacity: 0;
  }
}
</style>
