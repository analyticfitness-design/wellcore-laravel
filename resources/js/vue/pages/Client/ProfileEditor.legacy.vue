<script setup>
import { ref, computed, onBeforeUnmount, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useApi } from '../../composables/useApi';
import { useToast } from '../../composables/useToast';
import ClientLayout from '../../layouts/ClientLayout.vue';

const { t } = useI18n();
const api = useApi();
const toast = useToast();

// State
const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const showSuccess = ref(false);
const completion = ref({ score: 0, missing: [] });

// Avatar state
const avatarUrl = ref(null);
const avatarPreview = ref(null);
const avatarFile = ref(null);
const uploadingAvatar = ref(false);
let avatarObjectUrl = null;

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

// Día display labels (las claves internas se mantienen en español lowercase para
// no romper el contrato con el backend `dias_disponibles`).
const diasSemana = computed(() => [
    { key: 'lunes',     label: t('client_account.profile_day_monday_short') },
    { key: 'martes',    label: t('client_account.profile_day_tuesday_short') },
    { key: 'miercoles', label: t('client_account.profile_day_wednesday_short') },
    { key: 'jueves',    label: t('client_account.profile_day_thursday_short') },
    { key: 'viernes',   label: t('client_account.profile_day_friday_short') },
    { key: 'sabado',    label: t('client_account.profile_day_saturday_short') },
    { key: 'domingo',   label: t('client_account.profile_day_sunday_short') },
]);

// Completitud
const completionColor = computed(() => {
    if (completion.value.score >= 80) return '#10B981';
    if (completion.value.score >= 50) return '#F59E0B';
    return '#DC2626';
});

function getInitials(name) {
    if (!name) return '?';
    return name.split(' ').slice(0, 2).map(w => w[0] || '').join('').toUpperCase() || '?';
}

// Avatar
function onAvatarChange(e) {
    const file = e.target.files?.[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
        toast.warn(t('client_account.profile_avatar_too_large'));
        return;
    }
    avatarFile.value = file;
    if (avatarObjectUrl) URL.revokeObjectURL(avatarObjectUrl);
    avatarObjectUrl = URL.createObjectURL(file);
    avatarPreview.value = avatarObjectUrl;
}

async function uploadAvatar() {
    if (!avatarFile.value || uploadingAvatar.value) return;
    uploadingAvatar.value = true;
    try {
        const formData = new FormData();
        formData.append('avatar', avatarFile.value);
        const res = await api.post('/api/v/client/avatar', formData);
        avatarUrl.value = res.data.avatar_url;
        avatarFile.value = null;
        toast.success(t('client_account.profile_avatar_success'));
        // Refresh completion
        await fetchCompletion();
    } catch (err) {
        toast.apiError(err, t('client_account.profile_avatar_error'));
    } finally {
        uploadingAvatar.value = false;
    }
}

async function fetchCompletion() {
    try {
        const res = await api.get('/api/v/client/profile');
        completion.value = res.data.completion ?? { score: 0, missing: [] };
    } catch { /* silent */ }
}

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
        avatarUrl.value = d.avatarUrl || null;
        completion.value = d.completion ?? { score: 0, missing: [] };
        if (d.name) localStorage.setItem('wc_user_name', d.name);
    } catch (err) {
        error.value = err.response?.data?.message || t('client_account.settings_profile_load_error');
    } finally {
        loading.value = false;
    }
}

// Save profile
async function saveProfile() {
    if (saving.value) return;
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
        if (form.value.name) localStorage.setItem('wc_user_name', form.value.name);
        showSuccess.value = true;
        setTimeout(() => { showSuccess.value = false; }, 3000);
        await fetchCompletion();
    } catch (err) {
        if (err.response?.status === 422) {
            formErrors.value = err.response.data.errors || {};
        } else {
            toast.apiError(err, t('client_account.settings_profile_save_error'));
        }
    } finally {
        saving.value = false;
    }
}

function toggleDia(key) {
    const idx = form.value.diasDisponibles.indexOf(key);
    if (idx >= 0) {
        form.value.diasDisponibles.splice(idx, 1);
    } else {
        form.value.diasDisponibles.push(key);
    }
}

function isDiaSelected(key) {
    return form.value.diasDisponibles.includes(key);
}

onMounted(fetchProfile);

onBeforeUnmount(() => {
    if (avatarObjectUrl) URL.revokeObjectURL(avatarObjectUrl);
});
</script>

<template>
  <ClientLayout>
    <div class="wc-shell wc-shell--profile">
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
        <span class="text-sm font-medium text-green-400">{{ t('client_account.profile_saved') }}</span>
      </div>
    </Transition>

    <!-- Header -->
    <div class="mb-6">
      <h1 class="font-display text-3xl tracking-wide text-wc-text">{{ t('client_account.profile_title_legacy') }}</h1>
      <p class="mt-1 text-sm text-wc-text-secondary">{{ t('client_account.profile_subtitle_legacy') }}</p>
    </div>

    <!-- Avatar + Completitud (no skeleton: se muestran siempre) -->
    <div v-if="!loading" class="mb-8 rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
      <div class="flex flex-col items-center gap-5 sm:flex-row sm:items-start">

        <!-- Avatar upload -->
        <div class="flex flex-col items-center gap-3 shrink-0">
          <div class="relative">
            <div class="h-24 w-24 overflow-hidden rounded-full border-2 border-wc-border bg-wc-bg-secondary flex items-center justify-center">
              <img
                v-if="avatarPreview || avatarUrl"
                :src="avatarPreview || avatarUrl"
                :alt="t('client_account.profile_avatar_alt')"
                class="h-full w-full object-cover"
              />
              <span v-else class="font-display text-3xl text-wc-accent">{{ getInitials(form.name) }}</span>
            </div>
            <!-- Badge si tiene foto -->
            <span v-if="avatarUrl && !avatarPreview"
              class="absolute -bottom-0.5 -right-0.5 flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-white text-xs ring-2 ring-wc-bg-tertiary">
              ✓
            </span>
          </div>

          <!-- File input oculto -->
          <input
            type="file"
            id="avatar-input"
            accept="image/jpeg,image/jpg,image/png,image/webp"
            class="sr-only"
            @change="onAvatarChange"
          >

          <div class="flex flex-col items-center gap-2">
            <label
              for="avatar-input"
              class="cursor-pointer rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text-secondary transition-colors hover:border-wc-accent/40 hover:text-wc-text"
            >
              {{ avatarUrl ? t('client_account.profile_change_avatar') : t('client_account.profile_avatar_upload') }}
            </label>
            <button
              v-if="avatarFile"
              type="button"
              @click="uploadAvatar"
              :disabled="uploadingAvatar"
              class="flex items-center gap-1.5 rounded-lg bg-wc-accent px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-wc-accent-hover disabled:opacity-60"
            >
              <svg v-if="uploadingAvatar" class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ uploadingAvatar ? t('client_account.profile_avatar_uploading') : t('client_account.profile_avatar_save') }}
            </button>
          </div>
          <p class="text-center text-[10px] text-wc-text-tertiary">{{ t('client_account.profile_avatar_hint') }}<br>{{ t('client_account.profile_avatar_hint_community') }}</p>
        </div>

        <!-- Barra de completitud -->
        <div class="flex-1 min-w-0 w-full">
          <div class="mb-2 flex items-center justify-between gap-2">
            <p class="text-sm font-semibold text-wc-text">{{ t('client_account.profile_completion_label') }}</p>
            <span class="font-data text-lg font-bold" :style="{ color: completionColor }">{{ completion.score }}%</span>
          </div>

          <!-- Barra -->
          <div class="mb-3 h-2.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
            <div
              class="h-full rounded-full transition-all duration-700"
              :style="{ width: completion.score + '%', background: completionColor }"
            ></div>
          </div>

          <!-- Mensaje según score -->
          <p v-if="completion.score >= 80" class="mb-3 text-xs text-emerald-400 font-medium">
            {{ t('client_account.profile_completion_complete_legacy') }}
          </p>
          <p v-else class="mb-3 text-xs text-wc-text-tertiary">
            {{ t('client_account.profile_completion_missing_intro_legacy') }}
          </p>

          <!-- Tags de campos faltantes -->
          <div v-if="completion.missing.length" class="flex flex-wrap gap-2">
            <span
              v-for="m in completion.missing"
              :key="m.key"
              class="inline-flex items-center gap-1 rounded-full border border-wc-border bg-wc-bg-secondary px-2.5 py-1 text-xs text-wc-text-secondary"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-wc-accent/60"></span>
              {{ m.label }}
              <span class="text-wc-text-tertiary">+{{ m.points }}pts</span>
            </span>
          </div>
        </div>
      </div>
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
      <button @click="fetchProfile" class="wc-btn-primary mt-4">
        {{ t('client_account.profile_retry') }}
      </button>
    </div>

    <!-- Form -->
    <form v-else @submit.prevent="saveProfile">
      <fieldset :disabled="saving" class="contents">
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
              <h2 class="text-lg font-semibold text-wc-text">{{ t('client_account.profile_section_personal_title') }}</h2>
              <p class="text-sm text-wc-text-tertiary">{{ t('client_account.profile_section_personal_sub_legacy') }}</p>
            </div>
          </div>

          <div class="space-y-4">
            <!-- Name -->
            <div>
              <label for="name" class="mb-1.5 block text-sm font-medium text-wc-text">{{ t('client_account.profile_field_name') }}</label>
              <input
                v-model="form.name"
                type="text"
                id="name"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                :placeholder="t('client_account.profile_field_name_placeholder')"
              >
              <p v-if="formErrors.name" class="mt-1 text-xs text-red-500">{{ formErrors.name[0] }}</p>
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="mb-1.5 block text-sm font-medium text-wc-text">{{ t('client_account.profile_field_email') }}</label>
              <input
                v-model="form.email"
                type="email"
                id="email"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                :placeholder="t('client_account.profile_field_email_placeholder')"
              >
              <p v-if="formErrors.email" class="mt-1 text-xs text-red-500">{{ formErrors.email[0] }}</p>
            </div>

            <!-- City -->
            <div>
              <label for="city" class="mb-1.5 block text-sm font-medium text-wc-text">{{ t('client_account.profile_field_city') }}</label>
              <input
                v-model="form.city"
                type="text"
                id="city"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                :placeholder="t('client_account.profile_field_city_placeholder')"
              >
              <p v-if="formErrors.city" class="mt-1 text-xs text-red-500">{{ formErrors.city[0] }}</p>
            </div>

            <!-- Birth Date -->
            <div>
              <label for="birthDate" class="mb-1.5 block text-sm font-medium text-wc-text">{{ t('client_account.profile_field_birthdate') }}</label>
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
              <label for="whatsapp" class="mb-1.5 block text-sm font-medium text-wc-text">{{ t('client_account.profile_field_whatsapp') }}</label>
              <input
                v-model="form.whatsapp"
                type="text"
                id="whatsapp"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                :placeholder="t('client_account.profile_field_whatsapp_placeholder_legacy')"
              >
              <p v-if="formErrors.whatsapp" class="mt-1 text-xs text-red-500">{{ formErrors.whatsapp[0] }}</p>
            </div>

            <!-- Bio -->
            <div>
              <div class="mb-1.5 flex items-center justify-between gap-2">
                <label for="bio" class="text-sm font-medium text-wc-text">
                  {{ t('client_account.profile_field_bio') }}
                  <span class="ml-1 text-xs font-normal text-wc-text-tertiary">{{ t('client_account.profile_field_bio_hint') }}</span>
                </label>
                <span class="font-data text-xs tabular-nums" :class="(form.bio || '').length > 140 ? 'text-amber-400' : 'text-wc-text-tertiary'">
                  {{ (form.bio || '').length }}/160
                </span>
              </div>
              <textarea
                v-model="form.bio"
                id="bio"
                rows="3"
                maxlength="160"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                :placeholder="t('client_account.profile_field_bio_placeholder_legacy')"
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
              <h2 class="text-lg font-semibold text-wc-text">{{ t('client_account.profile_section_fitness_title_legacy') }}</h2>
              <p class="text-sm text-wc-text-tertiary">{{ t('client_account.profile_section_fitness_sub_legacy') }}</p>
            </div>
          </div>

          <div class="space-y-4">
            <!-- Peso + Altura (inline) -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="peso" class="mb-1.5 block text-sm font-medium text-wc-text">{{ t('client_account.profile_field_weight_legacy') }}</label>
                <input
                  v-model.number="form.peso"
                  type="number"
                  step="0.1"
                  id="peso"
                  class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                  :placeholder="t('client_account.profile_field_weight_placeholder')"
                >
                <p v-if="formErrors.peso" class="mt-1 text-xs text-red-500">{{ formErrors.peso[0] }}</p>
              </div>
              <div>
                <label for="altura" class="mb-1.5 block text-sm font-medium text-wc-text">{{ t('client_account.profile_field_height_legacy') }}</label>
                <input
                  v-model.number="form.altura"
                  type="number"
                  step="0.1"
                  id="altura"
                  class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                  :placeholder="t('client_account.profile_field_height_placeholder_legacy')"
                >
                <p v-if="formErrors.altura" class="mt-1 text-xs text-red-500">{{ formErrors.altura[0] }}</p>
              </div>
            </div>

            <!-- Objetivo -->
            <div>
              <label for="objetivo" class="mb-1.5 block text-sm font-medium text-wc-text">{{ t('client_account.profile_field_goal') }}</label>
              <input
                v-model="form.objetivo"
                type="text"
                id="objetivo"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                :placeholder="t('client_account.profile_field_goal_placeholder_legacy')"
              >
              <p v-if="formErrors.objetivo" class="mt-1 text-xs text-red-500">{{ formErrors.objetivo[0] }}</p>
            </div>

            <!-- Nivel -->
            <div>
              <label for="nivel" class="mb-1.5 block text-sm font-medium text-wc-text">{{ t('client_account.profile_field_level') }}</label>
              <select
                v-model="form.nivel"
                id="nivel"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
              >
                <option value="">{{ t('client_account.profile_field_level_placeholder') }}</option>
                <option value="principiante">{{ t('client_account.profile_field_level_beginner') }}</option>
                <option value="intermedio">{{ t('client_account.profile_field_level_intermediate') }}</option>
                <option value="avanzado">{{ t('client_account.profile_field_level_advanced') }}</option>
              </select>
              <p v-if="formErrors.nivel" class="mt-1 text-xs text-red-500">{{ formErrors.nivel[0] }}</p>
            </div>

            <!-- Lugar de Entreno -->
            <div>
              <label for="lugarEntreno" class="mb-1.5 block text-sm font-medium text-wc-text">{{ t('client_account.profile_field_place') }}</label>
              <select
                v-model="form.lugarEntreno"
                id="lugarEntreno"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
              >
                <option value="">{{ t('client_account.profile_field_place_placeholder_legacy') }}</option>
                <option value="gym">{{ t('client_account.profile_field_place_gym') }}</option>
                <option value="casa">{{ t('client_account.profile_field_place_home') }}</option>
                <option value="ambos">{{ t('client_account.profile_field_place_both') }}</option>
              </select>
              <p v-if="formErrors.lugar_entreno" class="mt-1 text-xs text-red-500">{{ formErrors.lugar_entreno[0] }}</p>
            </div>

            <!-- Dias Disponibles -->
            <fieldset>
              <legend class="sr-only">{{ t('client_account.profile_field_days_sr') }}</legend>
              <p class="mb-2 text-sm font-medium text-wc-text-secondary" aria-hidden="true">{{ t('client_account.profile_field_days') }}</p>
              <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                <label
                  v-for="dia in diasSemana"
                  :key="dia.key"
                  :class="[
                    'flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm transition-colors',
                    isDiaSelected(dia.key)
                      ? 'border-wc-accent/50 bg-wc-accent/10'
                      : 'border-wc-border bg-wc-bg-secondary hover:border-wc-accent/50'
                  ]"
                >
                  <input
                    type="checkbox"
                    :checked="isDiaSelected(dia.key)"
                    @change="toggleDia(dia.key)"
                    class="h-4 w-4 rounded border-wc-border bg-wc-bg-secondary text-wc-accent focus:ring-wc-accent/30"
                  >
                  <span class="text-wc-text-secondary">{{ dia.label }}</span>
                </label>
              </div>
            </fieldset>

            <!-- Restricciones -->
            <div>
              <label for="restricciones" class="mb-1.5 block text-sm font-medium text-wc-text">{{ t('client_account.profile_field_restrictions') }}</label>
              <textarea
                v-model="form.restricciones"
                id="restricciones"
                rows="3"
                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                :placeholder="t('client_account.profile_field_restrictions_placeholder_legacy')"
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
          class="wc-btn-primary w-full justify-center sm:w-auto disabled:cursor-not-allowed disabled:opacity-60"
        >
          <svg v-if="saving" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span v-if="!saving">{{ t('client_account.profile_save_legacy') }}</span>
          <span v-else>{{ t('client_account.profile_saving_legacy') }}</span>
        </button>
      </div>
      </fieldset>
    </form>
    </div>
  </ClientLayout>
</template>
