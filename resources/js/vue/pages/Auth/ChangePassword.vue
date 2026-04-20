<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const currentPassword = ref('');
const newPassword = ref('');
const newPasswordConfirmation = ref('');
const submitting = ref(false);
const error = ref('');
const success = ref('');

const token = computed(() => localStorage.getItem('wc_token') || '');

const rules = computed(() => ({
  length: newPassword.value.length >= 10,
  upper: /[A-Z]/.test(newPassword.value),
  lower: /[a-z]/.test(newPassword.value),
  digit: /\d/.test(newPassword.value),
  symbol: /[^A-Za-z0-9]/.test(newPassword.value),
  match: newPassword.value.length > 0 && newPassword.value === newPasswordConfirmation.value,
}));

const allPass = computed(() =>
  Object.values(rules.value).every(Boolean)
);

async function submit() {
  if (!allPass.value) {
    error.value = 'La nueva contrasena no cumple la politica.';
    return;
  }
  submitting.value = true;
  error.value = '';
  success.value = '';
  try {
    await axios.post(
      '/api/v/auth/change-password',
      {
        current_password: currentPassword.value,
        new_password: newPassword.value,
        new_password_confirmation: newPasswordConfirmation.value,
      },
      {
        headers: { Authorization: `Bearer ${token.value}` },
      }
    );
    success.value = 'Contrasena actualizada. Redirigiendo...';
    localStorage.removeItem('wc_force_password_change');
    // Clear flag in store so router guard deja pasar (sino crea loop).
    if (authStore && 'forcePasswordChange' in authStore) {
      authStore.forcePasswordChange = false;
    }
    // Refresh /me to pull fresh state (must_change_password=0) before navegar.
    try { if (typeof authStore.refreshMe === 'function') await authStore.refreshMe(); } catch (_) {}
    setTimeout(() => {
      const stored = localStorage.getItem('wc_user_portal');
      const portal = stored || (authStore.userType === 'admin' ? '/admin' : '/client');
      // Use replace to avoid back-button returning to change-password.
      router.replace(portal);
    }, 600);
  } catch (e) {
    error.value =
      e?.response?.data?.message ||
      Object.values(e?.response?.data?.errors || {})[0]?.[0] ||
      'No se pudo actualizar la contrasena.';
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-wc-bg px-4">
    <div class="w-full max-w-md bg-wc-bg-secondary border border-wc-border rounded-2xl p-6 shadow-xl">
      <h1 class="text-2xl font-display text-wc-text mb-2">Cambiar Contrasena</h1>
      <p class="text-sm text-wc-text-secondary mb-6">
        Por seguridad, define una nueva contrasena antes de continuar.
      </p>

      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="block text-xs text-wc-text-secondary mb-1">Contrasena actual</label>
          <input
            v-model="currentPassword"
            type="password"
            required
            autocomplete="current-password"
            class="w-full px-3 py-2 rounded-lg bg-wc-bg border border-wc-border text-wc-text"
          />
        </div>

        <div>
          <label class="block text-xs text-wc-text-secondary mb-1">Nueva contrasena</label>
          <input
            v-model="newPassword"
            type="password"
            required
            autocomplete="new-password"
            class="w-full px-3 py-2 rounded-lg bg-wc-bg border border-wc-border text-wc-text"
          />
        </div>

        <div>
          <label class="block text-xs text-wc-text-secondary mb-1">Confirmar nueva contrasena</label>
          <input
            v-model="newPasswordConfirmation"
            type="password"
            required
            autocomplete="new-password"
            class="w-full px-3 py-2 rounded-lg bg-wc-bg border border-wc-border text-wc-text"
          />
        </div>

        <ul class="text-xs space-y-1">
          <li :class="rules.length ? 'text-green-400' : 'text-wc-text-secondary'">
            {{ rules.length ? '✓' : '•' }} Minimo 10 caracteres
          </li>
          <li :class="rules.upper ? 'text-green-400' : 'text-wc-text-secondary'">
            {{ rules.upper ? '✓' : '•' }} Una mayuscula
          </li>
          <li :class="rules.lower ? 'text-green-400' : 'text-wc-text-secondary'">
            {{ rules.lower ? '✓' : '•' }} Una minuscula
          </li>
          <li :class="rules.digit ? 'text-green-400' : 'text-wc-text-secondary'">
            {{ rules.digit ? '✓' : '•' }} Un numero
          </li>
          <li :class="rules.symbol ? 'text-green-400' : 'text-wc-text-secondary'">
            {{ rules.symbol ? '✓' : '•' }} Un simbolo (!@#$% etc.)
          </li>
          <li :class="rules.match ? 'text-green-400' : 'text-wc-text-secondary'">
            {{ rules.match ? '✓' : '•' }} Las contrasenas coinciden
          </li>
        </ul>

        <p v-if="error" class="text-sm text-red-400">{{ error }}</p>
        <p v-if="success" class="text-sm text-green-400">{{ success }}</p>

        <button
          type="submit"
          class="w-full py-3 rounded-lg bg-wc-accent text-white font-semibold disabled:opacity-50"
          :disabled="submitting || !allPass"
        >
          {{ submitting ? 'Guardando...' : 'Guardar contrasena' }}
        </button>
      </form>
    </div>
  </div>
</template>
