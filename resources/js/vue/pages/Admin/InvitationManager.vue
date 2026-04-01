<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

const loading = ref(true);
const error = ref(null);
const invitations = ref([]);
const showForm = ref(false);
const sending = ref(false);
const copiedId = ref(null);

const form = ref({
    email: '',
    plan: 'premium',
    coach_id: '',
    message: '',
});

const coaches = ref([]);

async function fetchInvitations() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/admin/invitations');
        invitations.value = response.data.invitations || response.data.data || [];
        if (response.data.coaches) coaches.value = response.data.coaches;
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar invitaciones';
    } finally {
        loading.value = false;
    }
}

async function createInvitation() {
    sending.value = true;
    error.value = null;
    try {
        await api.post('/api/v/admin/invitations', form.value);
        form.value = { email: '', plan: 'premium', coach_id: '', message: '' };
        showForm.value = false;
        fetchInvitations();
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al crear invitacion';
    } finally {
        sending.value = false;
    }
}

function copyLink(invitation) {
    const link = invitation.link || `${window.location.origin}/inscripcion?code=${invitation.code}`;
    navigator.clipboard.writeText(link).then(() => {
        copiedId.value = invitation.id;
        setTimeout(() => { copiedId.value = null; }, 2000);
    });
}

function getStatusColor(status) {
    const map = {
        active: 'bg-emerald-500/10 text-emerald-500',
        pending: 'bg-amber-500/10 text-amber-500',
        used: 'bg-sky-500/10 text-sky-500',
        expired: 'bg-gray-500/10 text-gray-400',
    };
    return map[status] || 'bg-gray-500/10 text-gray-400';
}

onMounted(() => {
    fetchInvitations();
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text">Invitaciones</h1>
          <p class="mt-1 text-sm text-wc-text-tertiary">Crea y gestiona invitaciones de plan</p>
        </div>
        <button
          @click="showForm = !showForm"
          class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Nueva invitacion
        </button>
      </div>

      <!-- Create Form -->
      <div v-if="showForm" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">Crear Invitacion</h3>
        <form @submit.prevent="createInvitation" class="space-y-4">
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Email del invitado</label>
              <input v-model="form.email" type="email" required placeholder="correo@ejemplo.com" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Plan</label>
              <select v-model="form.plan" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20">
                <option value="premium">Premium</option>
                <option value="basic">Basico</option>
                <option value="rise">RISE</option>
                <option value="presencial">Presencial</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Coach asignado</label>
              <select v-model="form.coach_id" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20">
                <option value="">Sin asignar</option>
                <option v-for="coach in coaches" :key="coach.id" :value="coach.id">{{ coach.name }}</option>
              </select>
            </div>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Mensaje personalizado (opcional)</label>
            <textarea v-model="form.message" rows="2" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20" placeholder="Mensaje adicional..."></textarea>
          </div>
          <div class="flex gap-3">
            <button type="submit" :disabled="sending" class="rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50">
              {{ sending ? 'Enviando...' : 'Enviar invitacion' }}
            </button>
            <button type="button" @click="showForm = false" class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
              Cancelar
            </button>
          </div>
        </form>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-3">
        <div v-for="i in 5" :key="i" class="h-16 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error && !invitations.length" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
        <p class="text-sm text-wc-text">{{ error }}</p>
        <button @click="fetchInvitations" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white">Reintentar</button>
      </div>

      <!-- Invitations List -->
      <div v-else-if="invitations.length" class="space-y-2">
        <div
          v-for="inv in invitations"
          :key="inv.id"
          class="flex flex-col gap-3 rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:flex-row sm:items-center"
        >
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-wc-text truncate">{{ inv.email }}</p>
            <div class="mt-1 flex flex-wrap items-center gap-2">
              <span class="rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-medium text-wc-text capitalize">{{ inv.plan }}</span>
              <span class="rounded-full px-2 py-0.5 text-[10px] font-medium capitalize" :class="getStatusColor(inv.status)">{{ inv.status }}</span>
              <span v-if="inv.coachName" class="text-[10px] text-wc-text-tertiary">Coach: {{ inv.coachName }}</span>
            </div>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <span class="text-xs text-wc-text-tertiary hidden sm:inline">{{ inv.date || inv.created_at }}</span>
            <button
              @click="copyLink(inv)"
              class="inline-flex items-center gap-1 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors"
            >
              <svg v-if="copiedId !== inv.id" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
              </svg>
              <svg v-else class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
              </svg>
              {{ copiedId === inv.id ? 'Copiado' : 'Copiar link' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <svg class="mx-auto h-10 w-10 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
        </svg>
        <p class="mt-3 text-sm text-wc-text-tertiary">Sin invitaciones creadas</p>
      </div>

    </div>
  </AdminLayout>
</template>
