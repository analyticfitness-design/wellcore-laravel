<script setup>
/**
 * WcErrorState.vue — Pantalla de error reusable con Reintentar + Cerrar sesion.
 *
 * Uso:
 *   <WcErrorState :message="error" @retry="fetchData" />
 */
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';

defineProps({
    title:      { type: String, default: 'Error al cargar' },
    message:    { type: String, default: '' },
    retryLabel: { type: String, default: 'Reintentar' },
    hideLogout: { type: Boolean, default: false },
});

defineEmits(['retry']);

const authStore = useAuthStore();
const loggingOut = ref(false);

async function handleLogout() {
    if (loggingOut.value) return;
    loggingOut.value = true;

    try {
        [
            'wc_token_backup', 'wc_user_type_backup', 'wc_user_id_backup',
            'wc_user_name_backup', 'wc_user_portal_backup',
            'wc_impersonating_by_coach', 'wc_impersonating_token_key',
            'wc_impersonation_client_id', 'wc_impersonation_expires_at',
        ].forEach((k) => localStorage.removeItem(k));
    } catch { /* noop */ }

    try {
        await authStore.logout();
    } catch { /* noop */ }

    window.location.href = '/login';
}
</script>

<template>
  <div class="wc-err">
    <div class="wc-err-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
      </svg>
    </div>
    <h2 class="wc-err-title">{{ title }}</h2>
    <p v-if="message" class="wc-err-msg">{{ message }}</p>
    <div class="wc-err-actions">
      <button type="button" class="wc-err-btn wc-err-btn--primary" @click="$emit('retry')">
        {{ retryLabel }}
      </button>
      <button
        v-if="!hideLogout"
        type="button"
        class="wc-err-btn wc-err-btn--ghost"
        :disabled="loggingOut"
        @click="handleLogout"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        <span>{{ loggingOut ? 'Cerrando...' : 'Cerrar sesion' }}</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.wc-err {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px 40px;
  text-align: center;
  min-height: 60vh;
}
.wc-err-icon {
  width: 64px; height: 64px;
  border-radius: 18px;
  background: rgba(220, 38, 38, 0.10);
  display: grid;
  place-items: center;
  color: var(--color-wc-accent, #DC2626);
  margin-bottom: 18px;
}
.wc-err-icon svg { width: 32px; height: 32px; }
.wc-err-title {
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 22px;
  letter-spacing: 0.02em;
  color: var(--color-wc-text);
  margin: 0;
}
.wc-err-msg {
  margin: 8px 0 0;
  font-size: 14px;
  color: var(--color-wc-text-secondary);
  max-width: 320px;
  line-height: 1.5;
}
.wc-err-actions {
  margin-top: 24px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  width: 100%;
  max-width: 280px;
}
.wc-err-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  height: 48px;
  padding: 0 20px;
  border-radius: 14px;
  font-family: var(--font-sans);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s var(--ease-out, cubic-bezier(.22, 1, .36, 1));
  border: 1px solid transparent;
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
}
.wc-err-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.wc-err-btn--primary {
  background: var(--color-wc-accent, #DC2626);
  color: white;
  box-shadow: 0 6px 20px -4px rgba(220, 38, 38, 0.4);
}
.wc-err-btn--primary:hover:not(:disabled) {
  background: var(--color-wc-accent-hover, #B91C1C);
  transform: translateY(-1px);
  box-shadow: 0 10px 26px -4px rgba(220, 38, 38, 0.5);
}
.wc-err-btn--ghost {
  background: transparent;
  border-color: var(--color-wc-border);
  color: var(--color-wc-text-secondary);
}
.wc-err-btn--ghost:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.04);
  color: var(--color-wc-text);
  border-color: var(--color-wc-border-strong, rgba(255, 255, 255, 0.14));
}
.wc-err-btn--ghost svg { width: 16px; height: 16px; }
</style>
