<script setup lang="ts">
import { ref, onBeforeUnmount } from 'vue';
import { usePaymentProofs } from '../../composables/usePaymentProofs';

const emit = defineEmits<{ (e: 'submitted'): void }>();

const { submitting, submitProof } = usePaymentProofs();

// --- File state (module-level mutable, not reactive) ---
let previewObjectUrl: string | null = null;

const selectedFile = ref<File | null>(null);
const previewUrl = ref<string | null>(null);
const isPdf = ref(false);
const dragging = ref(false);

// --- Form fields ---
const clientName = ref('');
const clientEmail = ref('');
const plan = ref('');
const amount = ref<string>('');
const paymentMethod = ref('');
const coachNote = ref('');

// --- Error state ---
const fieldErrors = ref<Record<string, string[]>>({});
const globalError = ref<string | null>(null);
const successMsg = ref<string | null>(null);

const PLAN_OPTIONS = [
    { value: 'rise', label: 'RISE' },
    { value: 'esencial', label: 'Esencial' },
    { value: 'metodo', label: 'Metodo' },
    { value: 'elite', label: 'Elite' },
    { value: 'entreno_solo', label: 'Entreno Solo' },
    { value: 'nutricion_solo', label: 'Nutricion Solo' },
    { value: 'presencial', label: 'Presencial' },
] as const;

const METHOD_OPTIONS = [
    { value: 'transferencia', label: 'Transferencia' },
    { value: 'efectivo', label: 'Efectivo' },
    { value: 'nequi', label: 'Nequi' },
    { value: 'otro', label: 'Otro' },
] as const;

const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'application/pdf'];
const MAX_BYTES = 10 * 1024 * 1024; // 10 MB

function revokePreview() {
    if (previewObjectUrl) {
        URL.revokeObjectURL(previewObjectUrl);
        previewObjectUrl = null;
    }
}

function setFile(file: File) {
    if (!ACCEPTED_TYPES.includes(file.type)) {
        globalError.value = 'Solo se permiten archivos JPG, PNG o PDF.';
        return;
    }
    if (file.size > MAX_BYTES) {
        globalError.value = 'El archivo excede el limite de 10 MB.';
        return;
    }
    globalError.value = null;
    revokePreview();
    selectedFile.value = file;
    isPdf.value = file.type === 'application/pdf';
    if (!isPdf.value) {
        previewObjectUrl = URL.createObjectURL(file);
        previewUrl.value = previewObjectUrl;
    } else {
        previewUrl.value = null;
    }
}

function onFileChange(e: Event) {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0];
    if (file) setFile(file);
    // reset input so same file can be re-selected after clearing
    input.value = '';
}

function onDrop(e: DragEvent) {
    dragging.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file) setFile(file);
}

function clearFile() {
    revokePreview();
    selectedFile.value = null;
    previewUrl.value = null;
    isPdf.value = false;
}

function resetForm() {
    clearFile();
    clientName.value = '';
    clientEmail.value = '';
    plan.value = '';
    amount.value = '';
    paymentMethod.value = '';
    coachNote.value = '';
    fieldErrors.value = {};
    globalError.value = null;
}

async function handleSubmit() {
    fieldErrors.value = {};
    globalError.value = null;
    successMsg.value = null;

    if (!selectedFile.value) {
        globalError.value = 'Selecciona un archivo de comprobante.';
        return;
    }
    if (!clientName.value.trim()) {
        fieldErrors.value.client_name = ['El nombre del cliente es obligatorio.'];
        return;
    }
    if (!clientEmail.value.trim()) {
        fieldErrors.value.client_email = ['El email del cliente es obligatorio.'];
        return;
    }
    if (!plan.value) {
        fieldErrors.value.plan = ['Selecciona un plan.'];
        return;
    }

    const fd = new FormData();
    fd.append('file', selectedFile.value);
    fd.append('client_name', clientName.value.trim());
    fd.append('client_email', clientEmail.value.trim());
    fd.append('plan', plan.value);
    if (amount.value) fd.append('amount', amount.value);
    if (paymentMethod.value) fd.append('payment_method', paymentMethod.value);
    if (coachNote.value.trim()) fd.append('coach_note', coachNote.value.trim());

    try {
        await submitProof(fd);
        successMsg.value = 'Comprobante enviado correctamente.';
        resetForm();
        emit('submitted');
    } catch (err: any) {
        if (err?.response?.status === 409) {
            const code = err.response.data?.errorCode;
            globalError.value = code === 'DUPLICATE_FILE'
                ? 'Este archivo ya fue subido y esta pendiente de revision.'
                : 'Ya existe un comprobante pendiente para este cliente. Espera a que sea revisado antes de subir otro.';
        } else if (err?.response?.status === 422) {
            fieldErrors.value = err.response.data.errors ?? {};
            globalError.value = 'Revisa los campos marcados.';
        } else {
            globalError.value = 'Ocurrio un error al subir el comprobante. Intenta de nuevo.';
        }
    }
}

onBeforeUnmount(revokePreview);
</script>

<template>
  <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 space-y-5">
    <div>
      <h3 class="font-display text-lg tracking-wide text-wc-text">Subir comprobante de pago</h3>
      <p class="mt-0.5 text-xs text-wc-text-tertiary">JPG, PNG o PDF — maximo 10 MB</p>
    </div>

    <!-- Success toast -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="successMsg"
        class="flex items-center gap-3 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400"
      >
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        {{ successMsg }}
      </div>
    </Transition>

    <!-- Global error -->
    <div
      v-if="globalError"
      class="flex items-start gap-3 rounded-lg border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-400"
    >
      <svg class="h-4 w-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
      </svg>
      {{ globalError }}
    </div>

    <!-- Dropzone -->
    <div
      @dragover.prevent="dragging = true"
      @dragleave.prevent="dragging = false"
      @drop.prevent="onDrop"
      :class="dragging ? 'border-wc-accent/60 bg-wc-accent/5' : 'border-wc-border hover:border-wc-accent/40'"
      class="relative rounded-xl border-2 border-dashed p-6 text-center transition-colors cursor-pointer"
    >
      <!-- Hidden file input covers the dropzone for click-to-browse -->
      <input
        type="file"
        accept=".jpg,.jpeg,.png,.pdf"
        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
        @change="onFileChange"
      />

      <!-- No file selected -->
      <div v-if="!selectedFile" class="pointer-events-none space-y-2">
        <svg class="mx-auto h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
        </svg>
        <p class="text-sm text-wc-text-secondary">Arrastra aqui o <span class="text-wc-accent font-medium">selecciona</span></p>
        <p class="text-xs text-wc-text-tertiary">JPG, PNG, PDF — max 10 MB</p>
      </div>

      <!-- Image preview -->
      <div v-else-if="!isPdf" class="pointer-events-none">
        <img :src="previewUrl!" alt="Preview" class="mx-auto max-h-40 rounded-lg object-contain" />
        <p class="mt-2 text-xs text-wc-text-tertiary truncate">{{ selectedFile.name }}</p>
      </div>

      <!-- PDF indicator -->
      <div v-else class="pointer-events-none space-y-2">
        <svg class="mx-auto h-10 w-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
        </svg>
        <p class="text-sm font-medium text-wc-text truncate">{{ selectedFile.name }}</p>
        <p class="text-xs text-wc-text-tertiary">Archivo PDF seleccionado</p>
      </div>
    </div>

    <!-- Clear file button -->
    <button
      v-if="selectedFile"
      type="button"
      @click="clearFile"
      class="text-xs text-wc-text-tertiary hover:text-red-400 transition-colors"
    >
      Quitar archivo
    </button>

    <!-- Form fields -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <!-- Client name -->
      <div>
        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
          Nombre del cliente <span class="text-wc-accent">*</span>
        </label>
        <input
          v-model="clientName"
          type="text"
          placeholder="Juan Perez"
          class="w-full rounded-lg border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30 transition-colors"
          :class="fieldErrors.client_name ? 'border-red-500/60' : 'border-wc-border'"
        />
        <p v-if="fieldErrors.client_name" class="mt-1 text-xs text-red-400">{{ fieldErrors.client_name[0] }}</p>
      </div>

      <!-- Client email -->
      <div>
        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
          Email del cliente <span class="text-wc-accent">*</span>
        </label>
        <input
          v-model="clientEmail"
          type="email"
          placeholder="juan@email.com"
          class="w-full rounded-lg border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30 transition-colors"
          :class="fieldErrors.client_email ? 'border-red-500/60' : 'border-wc-border'"
        />
        <p v-if="fieldErrors.client_email" class="mt-1 text-xs text-red-400">{{ fieldErrors.client_email[0] }}</p>
      </div>

      <!-- Plan -->
      <div>
        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
          Plan <span class="text-wc-accent">*</span>
        </label>
        <select
          v-model="plan"
          class="w-full rounded-lg border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30 transition-colors"
          :class="fieldErrors.plan ? 'border-red-500/60' : 'border-wc-border'"
        >
          <option value="" disabled>Selecciona plan...</option>
          <option v-for="opt in PLAN_OPTIONS" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
        <p v-if="fieldErrors.plan" class="mt-1 text-xs text-red-400">{{ fieldErrors.plan[0] }}</p>
      </div>

      <!-- Amount -->
      <div>
        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Monto</label>
        <input
          v-model="amount"
          type="number"
          min="0"
          step="0.01"
          placeholder="0 (opcional)"
          class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30 transition-colors"
        />
      </div>

      <!-- Payment method -->
      <div>
        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Metodo de pago</label>
        <select
          v-model="paymentMethod"
          class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30 transition-colors"
        >
          <option value="">Sin especificar</option>
          <option v-for="opt in METHOD_OPTIONS" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
      </div>
    </div>

    <!-- Coach note -->
    <div>
      <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
        Nota interna
        <span class="ml-1 text-wc-text-tertiary normal-case font-normal">({{ coachNote.length }}/1000)</span>
      </label>
      <textarea
        v-model="coachNote"
        maxlength="1000"
        rows="3"
        placeholder="Contexto adicional para el equipo... (opcional)"
        class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30 transition-colors resize-none"
      ></textarea>
    </div>

    <!-- Submit -->
    <button
      type="button"
      @click="handleSubmit"
      :disabled="submitting"
      class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-5 py-2.5 text-sm font-medium text-white hover:bg-red-700 transition-colors disabled:opacity-50"
    >
      <svg v-if="submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
      </svg>
      <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
      </svg>
      {{ submitting ? 'Subiendo...' : 'Subir comprobante' }}
    </button>
  </div>
</template>
