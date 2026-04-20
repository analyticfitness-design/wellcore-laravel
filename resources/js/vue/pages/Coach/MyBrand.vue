<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();

// ============================================================
// NEW BRAND API (P4) — GET/PUT /api/v/coach/brand
// ============================================================
const loading = ref(true);
const saving = ref(false);
const uploading = ref(false);
const uploadProgress = ref(0);
const toast = ref(null); // { type: 'success'|'error', message: string }
let toastTimer = null;

// Brand state (new API)
const nombreComercial = ref('');
const tagline = ref('');
const primaryColor = ref('#DC2626');
const logoUrl = ref('');
const logoUrlWebp = ref('');

// Validation errors from 422
const errors = ref({});

// File refs for upload
const fileInput = ref(null);
const dragging = ref(false);
const uploadError = ref('');

// ============================================================
// LEGACY PROFILE (preserved but hidden — TODO: migrate to CoachProfile.vue)
// ============================================================
const showLegacy = ref(false);
const slug = ref('');
const bio = ref('');
const colorSecondary = ref('#1F2937');
const instagram = ref('');
const tiktok = ref('');
const youtube = ref('');
const website = ref('');

const coachName = computed(() => localStorage.getItem('wc_user_name') || 'Coach');

const HEX_RE = /^#[0-9A-Fa-f]{6}$/;
const MAX_LOGO_BYTES = 10 * 1024 * 1024; // 10MB
const ALLOWED_MIMES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

const nombreCount = computed(() => nombreComercial.value?.length || 0);
const taglineCount = computed(() => tagline.value?.length || 0);

function showToast(type, message, ms = 3000) {
    toast.value = { type, message };
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { toast.value = null; }, ms);
}

async function loadBrand() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v/coach/brand');
        nombreComercial.value = data.nombre_comercial || '';
        tagline.value = data.tagline || '';
        primaryColor.value = data.primary_color || '#DC2626';
        logoUrl.value = data.logo_url || '';
        logoUrlWebp.value = data.logo_url_webp || '';
    } catch (e) {
        // silent — backend may be missing fields
    } finally {
        loading.value = false;
    }
}

function applyBrandResponse(brand) {
    if (!brand) return;
    if (brand.nombre_comercial !== undefined) nombreComercial.value = brand.nombre_comercial || '';
    if (brand.tagline !== undefined) tagline.value = brand.tagline || '';
    if (brand.primary_color !== undefined) primaryColor.value = brand.primary_color || '#DC2626';
    if (brand.logo_url !== undefined) logoUrl.value = brand.logo_url || '';
    if (brand.logo_url_webp !== undefined) logoUrlWebp.value = brand.logo_url_webp || '';
}

async function saveField(payload, successMsg = 'Guardado') {
    saving.value = true;
    errors.value = {};
    try {
        const { data } = await api.put('/api/v/coach/brand', payload);
        applyBrandResponse(data.brand || data);
        showToast('success', successMsg);
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors || {};
            const first = Object.values(errors.value)[0];
            showToast('error', Array.isArray(first) ? first[0] : 'Error de validacion', 4500);
        } else {
            showToast('error', 'No se pudo guardar', 4000);
        }
    } finally {
        saving.value = false;
    }
}

function onNombreBlur() {
    const v = (nombreComercial.value || '').trim();
    if (v.length > 150) {
        errors.value = { nombre_comercial: ['Maximo 150 caracteres'] };
        showToast('error', 'Nombre comercial: maximo 150 caracteres', 3500);
        return;
    }
    saveField({ nombre_comercial: v }, 'Nombre actualizado');
}

function onTaglineBlur() {
    const v = (tagline.value || '').trim();
    if (v.length > 250) {
        errors.value = { tagline: ['Maximo 250 caracteres'] };
        showToast('error', 'Tagline: maximo 250 caracteres', 3500);
        return;
    }
    saveField({ tagline: v }, 'Tagline actualizado');
}

function onColorBlur() {
    const v = primaryColor.value || '';
    if (!HEX_RE.test(v)) {
        errors.value = { primary_color: ['Color invalido (ej. #DC2626)'] };
        showToast('error', 'Color invalido. Usa formato #RRGGBB', 3500);
        return;
    }
    saveField({ primary_color: v }, 'Color actualizado');
}

// ============================================================
// LOGO UPLOAD
// ============================================================
function triggerFileDialog() {
    fileInput.value?.click();
}

function onFileSelect(e) {
    const file = e.target.files?.[0];
    if (!file) return;
    uploadLogo(file);
    // reset input so selecting same file again works
    e.target.value = '';
}

function onDrop(e) {
    dragging.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (!file) return;
    uploadLogo(file);
}

async function uploadLogo(file) {
    uploadError.value = '';

    // Client-side validation
    if (!ALLOWED_MIMES.includes(file.type)) {
        uploadError.value = 'Formato no permitido. Usa JPG, PNG o WebP.';
        showToast('error', uploadError.value, 4000);
        return;
    }
    if (file.size > MAX_LOGO_BYTES) {
        uploadError.value = `Archivo muy grande (${(file.size / 1024 / 1024).toFixed(1)}MB). Maximo 10MB.`;
        showToast('error', uploadError.value, 4000);
        return;
    }

    uploading.value = true;
    uploadProgress.value = 0;
    const formData = new FormData();
    formData.append('logo', file);

    try {
        const { data } = await api.post('/api/v/coach/brand/logo', formData, {
            onUploadProgress: (evt) => {
                if (evt.total) {
                    uploadProgress.value = Math.round((evt.loaded * 100) / evt.total);
                }
            },
        });
        applyBrandResponse(data.brand || data);
        showToast('success', 'Logo actualizado');
    } catch (err) {
        if (err.response?.status === 422) {
            const errs = err.response.data.errors || {};
            const msg = Object.values(errs)[0];
            uploadError.value = Array.isArray(msg) ? msg[0] : 'Error de validacion';
        } else {
            uploadError.value = 'No se pudo subir el logo';
        }
        showToast('error', uploadError.value, 4500);
    } finally {
        uploading.value = false;
        uploadProgress.value = 0;
    }
}

async function deleteLogo() {
    if (!confirm('Eliminar el logo actual?')) return;
    saving.value = true;
    try {
        await api.delete('/api/v/coach/brand/logo');
        logoUrl.value = '';
        logoUrlWebp.value = '';
        showToast('success', 'Logo eliminado');
    } catch (e) {
        showToast('error', 'No se pudo eliminar', 3500);
    } finally {
        saving.value = false;
    }
}

onMounted(loadBrand);

onBeforeUnmount(() => {
    clearTimeout(toastTimer);
});
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex items-center gap-4">
        <div class="flex h-14 w-14 items-center justify-center rounded-full" :style="{ backgroundColor: primaryColor + '20' }">
          <svg class="h-7 w-7" :style="{ color: primaryColor }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42" />
          </svg>
        </div>
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Mi Marca</h1>
          <p class="mt-0.5 text-sm text-wc-text-tertiary">{{ coachName }} -- Personaliza tu marca visible para clientes</p>
        </div>
      </div>

      <!-- Toast -->
      <Transition name="fade">
        <div
          v-if="toast"
          class="flex items-center gap-3 rounded-lg border px-4 py-3 text-sm"
          :class="toast.type === 'success'
            ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
            : 'border-red-500/30 bg-red-500/10 text-red-400'"
        >
          <svg v-if="toast.type === 'success'" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          <svg v-else class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
          </svg>
          {{ toast.message }}
        </div>
      </Transition>

      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <svg class="h-8 w-8 animate-spin text-wc-accent" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      </div>

      <template v-else>
        <!-- NEW BRAND SECTION -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">

          <!-- Controls -->
          <div class="space-y-5 lg:col-span-3">

            <!-- Logo uploader -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
              <div class="flex items-baseline justify-between">
                <h3 class="font-display text-lg tracking-wide text-wc-text">Logo de tu marca</h3>
                <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">JPG / PNG / WebP - max 10MB</span>
              </div>

              <div class="mt-4 flex items-start gap-4">
                <!-- Current logo thumb -->
                <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary overflow-hidden">
                  <picture v-if="logoUrl">
                    <source v-if="logoUrlWebp" :srcset="logoUrlWebp" type="image/webp" />
                    <img :src="logoUrl" alt="Logo" class="max-h-full max-w-full object-contain" />
                  </picture>
                  <svg v-else class="h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                  </svg>
                </div>

                <!-- Dropzone -->
                <div
                  @dragover.prevent="dragging = true"
                  @dragleave.prevent="dragging = false"
                  @drop.prevent="onDrop"
                  @click="triggerFileDialog"
                  :class="dragging ? 'border-wc-accent/60 bg-wc-accent/5' : 'border-wc-border hover:border-wc-accent/40'"
                  class="relative flex-1 cursor-pointer rounded-xl border-2 border-dashed p-5 text-center transition-colors"
                >
                  <input
                    ref="fileInput"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    @change="onFileSelect"
                    class="hidden"
                  />
                  <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                  </svg>
                  <p class="mt-2 text-sm text-wc-text-secondary">Arrastra o haz click para subir</p>
                  <p class="mt-0.5 text-[11px] text-wc-text-tertiary">Se generara version WebP automaticamente</p>

                  <!-- Progress -->
                  <div v-if="uploading && uploadProgress > 0" class="mt-3">
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-wc-bg">
                      <div class="h-full bg-wc-accent transition-all" :style="{ width: uploadProgress + '%' }"></div>
                    </div>
                    <p class="mt-1 text-[10px] text-wc-text-tertiary">{{ uploadProgress }}%</p>
                  </div>
                </div>
              </div>

              <div v-if="uploadError" class="mt-3 text-xs text-red-400">{{ uploadError }}</div>

              <div class="mt-4 flex items-center gap-2">
                <button
                  v-if="logoUrl"
                  @click="deleteLogo"
                  :disabled="saving"
                  class="inline-flex items-center gap-1.5 rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-1.5 text-xs font-medium text-red-400 hover:bg-red-500/20 transition-colors disabled:opacity-50"
                >
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                  </svg>
                  Eliminar logo
                </button>
              </div>
            </div>

            <!-- Color -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
              <h3 class="font-display text-lg tracking-wide text-wc-text">Color principal</h3>
              <p class="mt-1 text-xs text-wc-text-tertiary">Se usa como acento en el portal de tus clientes.</p>
              <div class="mt-4 flex items-center gap-3">
                <input
                  type="color"
                  v-model="primaryColor"
                  @blur="onColorBlur"
                  class="h-10 w-14 cursor-pointer rounded-lg border border-wc-border bg-wc-bg-secondary p-1"
                />
                <input
                  type="text"
                  v-model="primaryColor"
                  @blur="onColorBlur"
                  maxlength="7"
                  class="w-32 rounded-lg border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text font-mono focus:outline-none focus:ring-1"
                  :class="errors.primary_color ? 'border-red-500/50 focus:border-red-500 focus:ring-red-500/30' : 'border-wc-border focus:border-wc-accent focus:ring-wc-accent/30'"
                />
                <div class="h-10 w-10 rounded-lg border border-wc-border" :style="{ backgroundColor: primaryColor }"></div>
              </div>
              <p v-if="errors.primary_color" class="mt-1.5 text-xs text-red-400">{{ errors.primary_color[0] }}</p>
            </div>

            <!-- Nombre comercial -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
              <div class="flex items-baseline justify-between">
                <h3 class="font-display text-lg tracking-wide text-wc-text">Nombre comercial</h3>
                <span class="text-[10px] text-wc-text-tertiary" :class="nombreCount > 150 ? 'text-red-400' : ''">{{ nombreCount }}/150</span>
              </div>
              <input
                v-model="nombreComercial"
                @blur="onNombreBlur"
                type="text"
                maxlength="150"
                placeholder="Ej. Performance Coaching"
                class="mt-3 w-full rounded-lg border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:outline-none focus:ring-1"
                :class="errors.nombre_comercial ? 'border-red-500/50 focus:border-red-500 focus:ring-red-500/30' : 'border-wc-border focus:border-wc-accent focus:ring-wc-accent/30'"
              />
              <p v-if="errors.nombre_comercial" class="mt-1.5 text-xs text-red-400">{{ errors.nombre_comercial[0] }}</p>
            </div>

            <!-- Tagline -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
              <div class="flex items-baseline justify-between">
                <h3 class="font-display text-lg tracking-wide text-wc-text">Tagline</h3>
                <span class="text-[10px] text-wc-text-tertiary" :class="taglineCount > 250 ? 'text-red-400' : ''">{{ taglineCount }}/250</span>
              </div>
              <textarea
                v-model="tagline"
                @blur="onTaglineBlur"
                rows="3"
                maxlength="250"
                placeholder="Tu frase o propuesta de valor"
                class="mt-3 w-full rounded-lg border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:outline-none focus:ring-1"
                :class="errors.tagline ? 'border-red-500/50 focus:border-red-500 focus:ring-red-500/30' : 'border-wc-border focus:border-wc-accent focus:ring-wc-accent/30'"
              ></textarea>
              <p v-if="errors.tagline" class="mt-1.5 text-xs text-red-400">{{ errors.tagline[0] }}</p>
            </div>
          </div>

          <!-- Preview -->
          <div class="lg:col-span-2">
            <div class="sticky top-24 space-y-4">
              <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Preview - tus clientes veran</p>

              <!-- Preview card with side bar of primary color -->
              <div class="rounded-xl overflow-hidden border border-wc-border bg-wc-bg-tertiary flex">
                <div class="w-2 shrink-0" :style="{ backgroundColor: primaryColor }"></div>
                <div class="flex-1 p-5">
                  <div class="flex items-center gap-3">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary overflow-hidden">
                      <picture v-if="logoUrl">
                        <source v-if="logoUrlWebp" :srcset="logoUrlWebp" type="image/webp" />
                        <img :src="logoUrl" alt="" class="max-h-full max-w-full object-contain" />
                      </picture>
                      <span v-else class="font-display text-xl" :style="{ color: primaryColor }">{{ (nombreComercial || coachName || 'C').charAt(0).toUpperCase() }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                      <p class="font-display text-lg tracking-wide text-wc-text truncate">{{ nombreComercial || coachName }}</p>
                      <p v-if="tagline" class="mt-0.5 text-xs text-wc-text-secondary line-clamp-2">{{ tagline }}</p>
                    </div>
                  </div>
                  <div class="mt-4 rounded-lg border p-3" :style="{ borderColor: primaryColor + '40', backgroundColor: primaryColor + '0D' }">
                    <p class="text-[10px] font-semibold uppercase tracking-wider" :style="{ color: primaryColor }">Plan activo</p>
                    <p class="mt-0.5 text-sm text-wc-text">Transformacion 12 semanas</p>
                  </div>
                </div>
              </div>

              <p class="text-[11px] text-wc-text-tertiary leading-relaxed">
                El logo se muestra junto al logo de WellCore en el header del portal de tus clientes. El color principal se usa como acento en elementos destacados.
              </p>
            </div>
          </div>
        </div>

        <!-- LEGACY SECTION (collapsed) -->
        <div class="mt-10 rounded-xl border border-wc-border/50 bg-wc-bg-secondary">
          <button
            @click="showLegacy = !showLegacy"
            class="flex w-full items-center justify-between px-5 py-3 text-left"
          >
            <div>
              <p class="text-sm font-medium text-wc-text-secondary">Perfil publico (legacy)</p>
              <p class="text-[11px] text-wc-text-tertiary">Slug, bio y redes sociales -- se migrara a pagina de Perfil</p>
            </div>
            <svg class="h-5 w-5 text-wc-text-tertiary transition-transform" :class="showLegacy ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </button>

          <!-- TODO: migrar a CoachProfile.vue o a su propio endpoint /api/v/coach/profile -->
          <div v-show="showLegacy" class="border-t border-wc-border/50 p-5 space-y-4 opacity-70">
            <p class="rounded-lg border border-yellow-500/30 bg-yellow-500/10 px-3 py-2 text-xs text-yellow-500">
              Estos campos pertenecen a un endpoint anterior. No se guardan con la nueva API de marca.
            </p>

            <div>
              <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Slug</label>
              <div class="flex items-center">
                <span class="rounded-l-lg border border-r-0 border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text-tertiary">wellcore.co/coach/</span>
                <input v-model="slug" disabled type="text" class="flex-1 rounded-r-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text-tertiary" placeholder="mi-slug" />
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Bio publica</label>
              <textarea v-model="bio" disabled rows="3" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text-tertiary" placeholder="Tu bio"></textarea>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Instagram</label>
                <input v-model="instagram" disabled type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text-tertiary" placeholder="@username" />
              </div>
              <div>
                <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">TikTok</label>
                <input v-model="tiktok" disabled type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text-tertiary" placeholder="@username" />
              </div>
              <div>
                <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">YouTube</label>
                <input v-model="youtube" disabled type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text-tertiary" placeholder="URL" />
              </div>
              <div>
                <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Website</label>
                <input v-model="website" disabled type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text-tertiary" placeholder="https://..." />
              </div>
              <div>
                <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Color secundario</label>
                <input v-model="colorSecondary" disabled type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text-tertiary font-mono" />
              </div>
            </div>
          </div>
        </div>

      </template>
    </div>
  </CoachLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
