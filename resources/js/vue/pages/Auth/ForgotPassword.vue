<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../../stores/auth';
import PublicLayout from '../../layouts/PublicLayout.vue';

const authStore = useAuthStore();

const email = ref('');
const sent = ref(false);
const errorMsg = ref('');
const isLoading = ref(false);
const validationErrors = ref({});

async function sendReset() {
    errorMsg.value = '';
    validationErrors.value = {};

    if (!email.value) {
        validationErrors.value.email = 'Ingresa tu email.';
        return;
    }

    isLoading.value = true;

    try {
        await authStore.forgotPassword(email.value);
        sent.value = true;
    } catch (err) {
        if (err.response?.status === 429) {
            errorMsg.value = err.response.data.message;
        } else if (err.response?.data?.errors?.email) {
            validationErrors.value.email = err.response.data.errors.email[0];
        } else if (err.response?.data?.message) {
            errorMsg.value = err.response.data.message;
        } else {
            errorMsg.value = 'Error de conexion. Intenta de nuevo.';
        }
    } finally {
        isLoading.value = false;
    }
}
</script>

<template>
  <PublicLayout>
    <div class="flex min-h-screen items-center justify-center bg-wc-bg px-4 py-12">
      <div class="w-full max-w-md">
        <div class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8 shadow-xl sm:p-10">

          <!-- Logo -->
          <div class="mb-8 text-center">
            <img src="/images/logo-dark.png" class="h-10 mx-auto dark:hidden" alt="WellCore">
            <img src="/images/logo-light.png" class="hidden h-10 mx-auto dark:block" alt="WellCore">
          </div>

          <!-- Success State -->
          <template v-if="sent">
            <div class="text-center">
              <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-500/10">
                <svg class="h-7 w-7 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
              </div>
              <h2 class="mt-5 text-2xl font-bold text-wc-text">Email Enviado</h2>
              <p class="mt-4 text-sm text-wc-text-secondary">
                Si existe una cuenta con <span class="font-semibold text-wc-text">{{ email }}</span>, recibiras un enlace para restablecer tu contrasena.
              </p>
              <p class="mt-2 text-xs text-wc-text-tertiary">Revisa tu carpeta de spam si no lo ves en unos minutos.</p>
              <p class="mt-1 text-xs text-wc-text-tertiary">El enlace expira en 1 hora.</p>
            </div>
            <div class="mt-8 text-center">
              <RouterLink to="/login" class="inline-flex items-center gap-2 text-sm text-wc-accent hover:underline">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                Volver a Iniciar Sesion
              </RouterLink>
            </div>
          </template>

          <!-- Form State -->
          <template v-else>
            <div class="text-center">
              <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-wc-accent/10">
                <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
              </div>
              <h2 class="mt-5 text-2xl font-bold text-wc-text">Recuperar Contrasena</h2>
              <p class="mt-2 text-sm text-wc-text-secondary">Ingresa tu email y te enviaremos un enlace para restablecer tu contrasena.</p>
            </div>

            <!-- Rate limit / error -->
            <div v-if="errorMsg" class="mt-6 rounded-lg border border-red-500/20 bg-red-500/10 px-4 py-3">
              <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <p class="text-sm text-red-400">{{ errorMsg }}</p>
              </div>
            </div>

            <form @submit.prevent="sendReset" class="mt-8 space-y-5">
              <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Email</label>
                <input
                  type="email"
                  id="email"
                  v-model="email"
                  placeholder="tu@email.com"
                  autofocus
                  class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary transition-colors focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                >
                <p v-if="validationErrors.email" class="mt-1 text-xs text-red-500">{{ validationErrors.email }}</p>
              </div>

              <button
                type="submit"
                :disabled="isLoading"
                class="flex w-full items-center justify-center rounded-full bg-wc-accent py-3 font-semibold text-white transition-all hover:bg-wc-accent-hover active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
              >
                <span v-if="!isLoading">Enviar enlace de recuperacion</span>
                <span v-else class="flex items-center gap-2">
                  <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Enviando...
                </span>
              </button>
            </form>

            <div class="mt-8 text-center">
              <RouterLink to="/login" class="inline-flex items-center gap-2 text-sm text-wc-accent hover:underline">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                Volver a Iniciar Sesion
              </RouterLink>
            </div>
          </template>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>
