<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const impersonating = ref(false);
const clientName    = ref('');
const ending        = ref(false);

function refreshState() {
  impersonating.value = localStorage.getItem('wc_impersonating_by_coach') === '1';
  clientName.value    = localStorage.getItem('wc_user_name') || 'Cliente';
}

async function endImpersonation() {
  if (ending.value) return;
  ending.value = true;
  const token = localStorage.getItem('wc_impersonating_token_key');
  const coachToken = localStorage.getItem('wc_token_backup');

  // Call backend to invalidate the client token issued for impersonation.
  try {
    if (token && coachToken) {
      await axios.post(
        '/api/v/coach/impersonate/end',
        { token },
        { headers: { Authorization: `Bearer ${coachToken}`, Accept: 'application/json' } }
      );
    }
  } catch {
    // Non-blocking — continue restoring the coach session anyway.
  }

  // Restore coach session from backups
  const backup = {
    token:     localStorage.getItem('wc_token_backup'),
    userType:  localStorage.getItem('wc_user_type_backup'),
    userId:    localStorage.getItem('wc_user_id_backup'),
    userName:  localStorage.getItem('wc_user_name_backup'),
    portal:    localStorage.getItem('wc_user_portal_backup'),
  };
  if (backup.token)    localStorage.setItem('wc_token', backup.token);
  if (backup.userType) localStorage.setItem('wc_user_type', backup.userType);
  if (backup.userId)   localStorage.setItem('wc_user_id', backup.userId);
  // Siempre restaurar userName (aunque sea vacio) para que no quede el del
  // cliente impersonado en el avatar del coach.
  localStorage.setItem('wc_user_name', backup.userName || '');
  if (backup.portal)   localStorage.setItem('wc_user_portal', backup.portal);

  // Cleanup impersonation keys
  ['wc_token_backup', 'wc_user_type_backup', 'wc_user_id_backup', 'wc_user_name_backup',
   'wc_user_portal_backup', 'wc_impersonating_by_coach', 'wc_impersonating_token_key']
    .forEach(k => localStorage.removeItem(k));

  // Hard redirect to reset Pinia store with the restored coach token.
  const clientId = localStorage.getItem('wc_impersonation_client_id');
  localStorage.removeItem('wc_impersonation_client_id');
  window.location.href = clientId ? `/coach/clients` : '/coach';
}

onMounted(() => {
  refreshState();
  // react to changes from other tabs (or late writes)
  window.addEventListener('storage', refreshState);
});
</script>

<template>
  <div v-if="impersonating"
       class="fixed top-0 left-0 right-0 z-[100] flex items-center justify-center gap-3 bg-wc-accent px-4 py-2 text-xs sm:text-sm font-medium text-white shadow-lg">
    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
    </svg>
    <span>Viendo como <strong>{{ clientName }}</strong></span>
    <button @click="endImpersonation" :disabled="ending"
            class="ml-2 inline-flex items-center gap-1 rounded-md bg-black/25 px-3 py-1 text-xs font-semibold hover:bg-black/40 transition-colors disabled:opacity-60">
      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
      </svg>
      {{ ending ? 'Volviendo...' : 'Volver al portal coach' }}
    </button>
  </div>
</template>
