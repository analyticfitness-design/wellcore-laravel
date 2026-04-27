<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { adminMarketingApi } from '../../../api/adminMarketing';
import AdminLayout from '../../../layouts/AdminLayout.vue';

const route = useRoute();
const router = useRouter();

// Router param is :coachId (see router/index.js)
const coachId = Number(route.params.coachId);

const profile = ref(null);
const isLoading = ref(true);
const isSaving = ref(false);
const saveError = ref(null);
const saveSuccess = ref(false);

// Fields to exclude from the generic editor (metadata)
const SKIP_FIELDS = new Set(['id', 'coach_id', 'completed_at', 'is_complete', 'created_at', 'updated_at']);

// Fields that should render as textarea (object/array or long text)
function isComplexField(val) {
    return val !== null && (typeof val === 'object' || Array.isArray(val));
}

// All editable fields derived from profile
const editableFields = computed(() => {
    if (!profile.value) return [];
    return Object.keys(profile.value).filter((k) => !SKIP_FIELDS.has(k));
});

function valueToString(val) {
    if (isComplexField(val)) return JSON.stringify(val, null, 2);
    return val ?? '';
}

function updateField(key, rawValue) {
    const current = profile.value[key];
    if (isComplexField(current)) {
        try {
            profile.value[key] = JSON.parse(rawValue);
        } catch {
            // keep raw; user is mid-edit
        }
    } else {
        profile.value[key] = rawValue;
    }
}

let successTimer = null;

async function save() {
    isSaving.value = true;
    saveError.value = null;
    saveSuccess.value = false;
    clearTimeout(successTimer);
    try {
        profile.value = await adminMarketingApi.updateCoachProfile(coachId, profile.value);
        saveSuccess.value = true;
        successTimer = setTimeout(() => { saveSuccess.value = false; }, 3000);
    } catch (e) {
        if (e?.response?.status === 422) {
            const errors = e.response.data?.errors ?? {};
            saveError.value = Object.values(errors).flat().join(' — ');
        } else {
            saveError.value = e?.response?.data?.message ?? e?.message ?? 'Error al guardar';
        }
    } finally {
        isSaving.value = false;
    }
}

onMounted(async () => {
    try {
        profile.value = await adminMarketingApi.getCoachProfile(coachId);
    } catch (e) {
        profile.value = null;
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
  <AdminLayout>
    <div class="mx-auto max-w-3xl px-6 py-10 space-y-8">

      <!-- Back link -->
      <div>
        <RouterLink
          :to="{ name: 'admin-marketing-queue' }"
          class="inline-flex items-center gap-1.5 font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary hover:text-wc-text transition-colors"
        >
          <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
          Volver a la cola
        </RouterLink>
      </div>

      <!-- Header -->
      <div>
        <p class="font-mono text-[10px] uppercase tracking-[0.2em] text-wc-text-tertiary">WC · ADMIN / COACH #{{ coachId }} / PERFIL MARKETING</p>
        <h1 class="mt-2 font-display text-4xl uppercase tracking-tight text-wc-text">Editar perfil del coach</h1>
        <p class="mt-2 text-sm text-wc-text-secondary">Perfil de marca y voz que alimenta el generador de drops semanales.</p>
      </div>

      <!-- Loading skeletons -->
      <div v-if="isLoading" class="space-y-4">
        <div v-for="n in 5" :key="n" class="h-14 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      </div>

      <!-- Form -->
      <form v-else-if="profile" @submit.prevent="save" class="space-y-6">

        <!-- Metadata bar (read-only) -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 flex flex-wrap gap-4">
          <span class="font-mono text-[10px] uppercase tracking-wide text-wc-text-tertiary">
            Perfil ID: <span class="text-wc-text">{{ profile.id }}</span>
          </span>
          <span class="font-mono text-[10px] uppercase tracking-wide text-wc-text-tertiary">
            Completo:
            <span :class="profile.is_complete ? 'text-emerald-400' : 'text-amber-400'">
              {{ profile.is_complete ? 'Si' : 'No' }}
            </span>
          </span>
          <span v-if="profile.completed_at" class="font-mono text-[10px] uppercase tracking-wide text-wc-text-tertiary">
            Completado: <span class="text-wc-text">{{ new Date(profile.completed_at).toLocaleDateString('es') }}</span>
          </span>
        </div>

        <!-- Editable fields -->
        <div
          v-for="key in editableFields"
          :key="key"
          class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4"
        >
          <label class="block">
            <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">{{ key.replace(/_/g, ' ') }}</span>
            <!-- Complex (object/array): JSON textarea -->
            <textarea
              v-if="isComplexField(profile[key])"
              :value="valueToString(profile[key])"
              @input="e => updateField(key, e.target.value)"
              rows="5"
              spellcheck="false"
              class="mt-2 w-full resize-y rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 font-mono text-xs leading-relaxed text-wc-text focus:border-wc-accent focus:outline-none"
            />
            <!-- Long string heuristic: textarea -->
            <textarea
              v-else-if="typeof profile[key] === 'string' && profile[key].length > 80"
              :value="profile[key]"
              @input="e => updateField(key, e.target.value)"
              rows="3"
              class="mt-2 w-full resize-y rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 font-sans text-sm text-wc-text focus:border-wc-accent focus:outline-none"
            />
            <!-- Default: single-line input -->
            <input
              v-else
              :value="profile[key] ?? ''"
              @input="e => updateField(key, e.target.value)"
              class="mt-2 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 font-sans text-sm text-wc-text focus:border-wc-accent focus:outline-none"
            />
          </label>
        </div>

        <!-- Submit row -->
        <div class="flex flex-wrap items-center gap-4">
          <button
            type="submit"
            :disabled="isSaving"
            class="rounded-lg bg-wc-accent px-6 py-3 font-display text-sm uppercase tracking-wide text-white transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
          >
            {{ isSaving ? 'Guardando...' : 'Guardar perfil' }}
          </button>
          <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
          >
            <span v-if="saveSuccess" class="font-mono text-xs text-emerald-400">Perfil guardado correctamente.</span>
            <span v-else-if="saveError" class="font-mono text-xs text-red-400">{{ saveError }}</span>
          </Transition>
        </div>

      </form>

      <!-- Not found -->
      <div v-else class="py-16 text-center">
        <p class="font-mono text-xs uppercase tracking-[0.15em] text-wc-text-tertiary">Perfil de marketing no encontrado para coach #{{ coachId }}.</p>
        <RouterLink
          :to="{ name: 'admin-marketing-queue' }"
          class="mt-4 inline-block font-mono text-xs text-wc-accent hover:underline"
        >
          Volver a la cola
        </RouterLink>
      </div>

    </div>
  </AdminLayout>
</template>
