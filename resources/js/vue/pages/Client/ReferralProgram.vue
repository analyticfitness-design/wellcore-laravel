<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();

// State
const loading = ref(true);
const error = ref(null);
const referralLink = ref('');
const stats = ref({ total: 0, registered: 0, active: 0 });
const history = ref([]);
const copied = ref(false);

// Invite form
const inviteEmail = ref('');
const inviteError = ref(null);
const inviteSending = ref(false);
const showSuccess = ref(false);
const successMessage = ref('');

// Fetch referral data
async function fetchReferrals() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/client/referrals');
        const d = response.data;
        referralLink.value = d.referral_link || '';
        stats.value = d.stats || { total: 0, registered: 0, active: 0 };
        history.value = d.history || [];
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar los referidos';
    } finally {
        loading.value = false;
    }
}

// Copy link
async function copyLink() {
    try {
        await navigator.clipboard.writeText(referralLink.value);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2500);
    } catch {
        // Fallback
        const textarea = document.createElement('textarea');
        textarea.value = referralLink.value;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2500);
    }
}

// Send invite
async function sendInvite() {
    if (!inviteEmail.value.trim()) return;
    inviteSending.value = true;
    inviteError.value = null;

    try {
        const response = await api.post('/api/v/client/referrals/invite', {
            email: inviteEmail.value.trim(),
        });
        inviteEmail.value = '';
        showSuccess.value = true;
        successMessage.value = response.data.message || 'Invitacion enviada exitosamente';
        setTimeout(() => { showSuccess.value = false; }, 4000);
        // Refresh data
        await fetchReferrals();
    } catch (err) {
        inviteError.value = err.response?.data?.message || 'Error al enviar la invitacion';
    } finally {
        inviteSending.value = false;
    }
}

function dismissSuccess() {
    showSuccess.value = false;
}

// WhatsApp share
function shareWhatsApp() {
    const text = encodeURIComponent(
        `Hola! Te invito a unirte a WellCore Fitness, la plataforma de entrenamiento personalizado. Usa mi link: ${referralLink.value}`
    );
    window.open(`https://wa.me/?text=${text}`, '_blank');
}

// Helpers
function formatDate(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('es-CO', { day: 'numeric', month: 'short', year: 'numeric' });
}

function getStatusBadge(status) {
    const map = {
        pending: { label: 'Pendiente', class: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20' },
        registered: { label: 'Registrado', class: 'bg-blue-500/10 text-blue-400 border-blue-500/20' },
        active: { label: 'Activo', class: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' },
    };
    return map[status] || map.pending;
}

onMounted(() => {
    fetchReferrals();
});
</script>

<template>
  <ClientLayout>

    <!-- Loading Skeleton -->
    <div v-if="loading" class="mx-auto max-w-4xl space-y-6 sm:space-y-8">
      <div class="h-48 animate-pulse rounded-2xl bg-wc-bg-tertiary"></div>
      <div class="grid grid-cols-3 gap-3 sm:gap-4">
        <div v-for="i in 3" :key="i" class="h-24 animate-pulse rounded-2xl bg-wc-bg-tertiary sm:h-28"></div>
      </div>
      <div class="h-36 animate-pulse rounded-2xl bg-wc-bg-tertiary"></div>
      <div class="h-24 animate-pulse rounded-2xl bg-wc-bg-tertiary"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="mx-auto flex max-w-4xl flex-col items-center justify-center py-24">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10 ring-1 ring-wc-accent/20">
        <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
      </div>
      <h2 class="mt-5 font-display text-2xl tracking-wide text-wc-text">ERROR AL CARGAR</h2>
      <p class="mt-2 text-sm text-wc-text-secondary">{{ error }}</p>
      <button
        @click="fetchReferrals"
        class="mt-6 rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-opacity hover:opacity-90"
      >
        Reintentar
      </button>
    </div>

    <!-- Content -->
    <div v-else class="mx-auto max-w-4xl space-y-6 sm:space-y-8">

      <!-- Hero Section -->
      <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-secondary">
        <!-- Decorative top-left red bar -->
        <div class="absolute left-0 top-0 h-full w-1 bg-wc-accent"></div>
        <!-- Decorative background gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-transparent pointer-events-none"></div>
        <!-- Decorative icon background -->
        <div class="absolute -right-6 -top-6 opacity-[0.04]">
          <svg class="h-52 w-52 text-wc-text" fill="currentColor" viewBox="0 0 24 24">
            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
          </svg>
        </div>
        <div class="relative px-5 py-6 sm:px-7 sm:py-8">
          <span class="inline-block rounded-full bg-wc-accent/10 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-wc-accent ring-1 ring-wc-accent/20">
            Gana recompensas
          </span>
          <h1 class="mt-3 font-display text-3xl tracking-wide text-wc-text leading-none sm:text-4xl">
            PROGRAMA DE<br>
            <span class="text-wc-accent">REFERIDOS</span>
          </h1>
          <p class="mt-3 max-w-md text-sm text-wc-text-secondary leading-relaxed">
            Invita a tus amigos a WellCore Fitness y gana beneficios exclusivos por cada persona que se una a la comunidad.
          </p>
        </div>
      </div>

      <!-- Success Notice -->
      <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 -translate-y-3"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-3"
      >
        <div v-if="showSuccess" class="flex items-center justify-between rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3">
          <div class="flex items-center gap-3">
            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-500/20">
              <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
              </svg>
            </div>
            <span class="text-sm font-medium text-emerald-400">{{ successMessage }}</span>
          </div>
          <button @click="dismissSuccess" class="text-emerald-400/50 transition-colors hover:text-emerald-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </Transition>

      <!-- Stats Cards -->
      <div class="grid grid-cols-3 gap-3 sm:gap-4">
        <!-- Total -->
        <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-secondary pt-1">
          <div class="absolute top-0 left-0 right-0 h-0.5 bg-wc-accent rounded-t-2xl"></div>
          <div class="p-3 text-center sm:p-5">
            <p class="font-data text-2xl font-bold text-wc-accent sm:text-4xl">{{ stats.total }}</p>
            <p class="mt-1.5 text-[9px] font-semibold uppercase tracking-widest text-wc-text-tertiary sm:mt-2 sm:text-[10px]">Total referidos</p>
          </div>
        </div>
        <!-- Registered -->
        <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-secondary pt-1">
          <div class="absolute top-0 left-0 right-0 h-0.5 bg-blue-500 rounded-t-2xl"></div>
          <div class="p-3 text-center sm:p-5">
            <p class="font-data text-2xl font-bold text-blue-400 sm:text-4xl">{{ stats.registered }}</p>
            <p class="mt-1.5 text-[9px] font-semibold uppercase tracking-widest text-wc-text-tertiary sm:mt-2 sm:text-[10px]">Registrados</p>
          </div>
        </div>
        <!-- Active -->
        <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-secondary pt-1">
          <div class="absolute top-0 left-0 right-0 h-0.5 bg-emerald-500 rounded-t-2xl"></div>
          <div class="p-3 text-center sm:p-5">
            <p class="font-data text-2xl font-bold text-emerald-400 sm:text-4xl">{{ stats.active }}</p>
            <p class="mt-1.5 text-[9px] font-semibold uppercase tracking-widest text-wc-text-tertiary sm:mt-2 sm:text-[10px]">Activos</p>
          </div>
        </div>
      </div>

      <!-- Referral Link Card -->
      <div class="rounded-2xl border border-wc-accent/30 bg-wc-bg-secondary shadow-lg shadow-wc-accent/5">
        <!-- Card Header -->
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-wc-border px-4 py-3 sm:px-6 sm:py-4">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-wc-accent/10 ring-1 ring-wc-accent/20">
              <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
              </svg>
            </div>
            <div>
              <h2 class="text-sm font-semibold text-wc-text">Tu link de referido</h2>
              <p class="text-[11px] text-wc-text-tertiary">Comparte este enlace con tus amigos</p>
            </div>
          </div>
          <span class="rounded-full bg-wc-accent px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-white">
            Tu link exclusivo
          </span>
        </div>

        <!-- Link display + Actions -->
        <div class="space-y-4 p-4 sm:p-6">
          <div class="flex items-center gap-2 rounded-xl border border-wc-border bg-wc-bg px-4 py-3">
            <svg class="h-3.5 w-3.5 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
            </svg>
            <span class="flex-1 truncate font-mono text-xs text-wc-text-secondary">{{ referralLink }}</span>
          </div>

          <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:gap-3">
            <!-- Copy button -->
            <button
              @click="copyLink"
              :class="copied
                ? 'border-emerald-500/50 bg-emerald-500/10 text-emerald-400'
                : 'border-wc-accent/40 text-wc-accent hover:bg-wc-accent/5'"
              class="flex w-full items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-semibold transition-all duration-200 sm:w-auto sm:justify-start"
            >
              <Transition name="swap" mode="out-in">
                <svg v-if="!copied" key="copy" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                </svg>
                <svg v-else key="check" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
              </Transition>
              <span>{{ copied ? 'Copiado' : 'Copiar link' }}</span>
            </button>

            <!-- WhatsApp button -->
            <button
              @click="shareWhatsApp"
              class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#25D366] px-5 py-2.5 text-sm font-semibold text-white transition-opacity hover:opacity-90 sm:w-auto sm:justify-start"
            >
              <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
              </svg>
              Compartir por WhatsApp
            </button>
          </div>
        </div>
      </div>

      <!-- Invite by Email -->
      <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-4 sm:p-6">
        <div class="mb-4 flex items-center gap-2">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-bg-tertiary">
            <svg class="h-4 w-4 text-wc-text-secondary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-wc-text">Invitar por correo electronico</h3>
            <p class="text-[11px] text-wc-text-tertiary">Envia una invitacion directa a la bandeja de entrada</p>
          </div>
        </div>
        <form @submit.prevent="sendInvite" class="flex flex-col gap-3 sm:flex-row sm:items-start">
          <div class="flex-1">
            <input
              v-model="inviteEmail"
              type="email"
              placeholder="correo@ejemplo.com"
              :class="inviteError ? 'border-red-500/50 focus:border-red-500/70 focus:ring-red-500/20' : 'border-wc-border focus:border-wc-accent/50 focus:ring-wc-accent/20'"
              class="w-full rounded-xl border bg-wc-bg px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary transition-colors focus:outline-none focus:ring-2"
            />
            <p v-if="inviteError" class="mt-1.5 flex items-center gap-1 text-xs text-red-400">
              <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
              </svg>
              {{ inviteError }}
            </p>
          </div>
          <button
            type="submit"
            :disabled="inviteSending || !inviteEmail.trim()"
            class="flex w-full items-center justify-center gap-2 rounded-xl bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white transition-opacity hover:opacity-90 disabled:opacity-40 sm:w-auto"
          >
            <svg v-if="!inviteSending" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
            </svg>
            <svg v-else class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span>{{ inviteSending ? 'Enviando...' : 'Invitar' }}</span>
          </button>
        </form>
      </div>

      <!-- Referral History -->
      <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary overflow-hidden">
        <!-- Section header -->
        <div class="flex items-center justify-between border-b border-wc-border px-4 py-3 sm:px-6 sm:py-4">
          <h3 class="text-xs font-bold uppercase tracking-widest text-wc-text-tertiary">Historial de referidos</h3>
          <span v-if="history.length > 0" class="rounded-full bg-wc-bg-tertiary px-2.5 py-0.5 text-[10px] font-bold text-wc-text-secondary">
            {{ history.length }}
          </span>
        </div>

        <!-- List -->
        <div v-if="history.length > 0" class="divide-y divide-wc-border/40">
          <div
            v-for="ref_item in history"
            :key="ref_item.id || ref_item.email"
            class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 transition-colors hover:bg-wc-bg-tertiary/30 sm:flex-nowrap sm:gap-0 sm:px-6 sm:py-4"
          >
            <div class="flex items-center gap-3 sm:gap-4">
              <!-- Avatar -->
              <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-bg-tertiary ring-1 ring-wc-border">
                <span class="font-display text-sm tracking-wide text-wc-text">
                  {{ (ref_item.name || ref_item.email || 'U').charAt(0).toUpperCase() }}
                </span>
              </div>
              <div>
                <p class="text-sm font-semibold text-wc-text">{{ ref_item.name || ref_item.email }}</p>
                <p class="mt-0.5 text-[11px] text-wc-text-tertiary">{{ formatDate(ref_item.created_at) }}</p>
              </div>
            </div>
            <span
              class="rounded-full border px-3 py-1 text-[10px] font-bold uppercase tracking-wider"
              :class="getStatusBadge(ref_item.status).class"
            >
              {{ getStatusBadge(ref_item.status).label }}
            </span>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else class="px-6 py-16 text-center">
          <!-- SVG illustration -->
          <svg class="mx-auto h-20 w-20 text-wc-text-tertiary/30" fill="none" viewBox="0 0 80 80">
            <circle cx="40" cy="40" r="38" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 4"/>
            <circle cx="28" cy="30" r="8" stroke="currentColor" stroke-width="1.5"/>
            <circle cx="52" cy="30" r="8" stroke="currentColor" stroke-width="1.5"/>
            <path d="M12 58c0-8.837 7.163-16 16-16h24c8.837 0 16 7.163 16 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <path d="M40 20v8M36 24h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
          <h3 class="mt-5 font-display text-2xl tracking-wide text-wc-text">SIN REFERIDOS AUN</h3>
          <p class="mx-auto mt-2 max-w-xs text-sm text-wc-text-secondary leading-relaxed">
            Comparte tu link exclusivo con amigos y comienza a ganar recompensas increibles.
          </p>
          <button
            @click="copyLink"
            class="mt-6 inline-flex items-center gap-2 rounded-xl border border-wc-accent/40 px-5 py-2.5 text-sm font-semibold text-wc-accent transition-colors hover:bg-wc-accent/5"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
            </svg>
            Copiar mi link
          </button>
        </div>
      </div>

    </div>
  </ClientLayout>
</template>

<style scoped>
.swap-enter-active,
.swap-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.swap-enter-from {
  opacity: 0;
  transform: scale(0.7);
}
.swap-leave-to {
  opacity: 0;
  transform: scale(0.7);
}
</style>
