<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

const loading = ref(true);
const error = ref(null);
const coaches = ref([]);
const showForm = ref(false);
const saving = ref(false);
const editingId = ref(null);

const form = ref({
    name: '',
    email: '',
    phone: '',
    specialty: '',
    bio: '',
});

async function fetchCoaches() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/admin/coaches');
        coaches.value = response.data.coaches || response.data.data || [];
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar coaches';
    } finally {
        loading.value = false;
    }
}

function openAddForm() {
    editingId.value = null;
    form.value = { name: '', email: '', phone: '', specialty: '', bio: '' };
    showForm.value = true;
}

function openEditForm(coach) {
    editingId.value = coach.id;
    form.value = { name: coach.name, email: coach.email, phone: coach.phone || '', specialty: coach.specialty || '', bio: coach.bio || '' };
    showForm.value = true;
}

async function saveCoach() {
    saving.value = true;
    error.value = null;
    try {
        if (editingId.value) {
            await api.put(`/api/v/admin/coaches/${editingId.value}`, form.value);
        } else {
            await api.post('/api/v/admin/coaches', form.value);
        }
        showForm.value = false;
        editingId.value = null;
        form.value = { name: '', email: '', phone: '', specialty: '', bio: '' };
        fetchCoaches();
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al guardar coach';
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    fetchCoaches();
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text">Coaches</h1>
          <p class="mt-1 text-sm text-wc-text-tertiary">Gestiona el equipo de coaching</p>
        </div>
        <button @click="openAddForm" class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Agregar coach
        </button>
      </div>

      <!-- Form -->
      <div v-if="showForm" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">{{ editingId ? 'Editar Coach' : 'Nuevo Coach' }}</h3>
        <form @submit.prevent="saveCoach" class="space-y-4">
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Nombre</label>
              <input v-model="form.name" type="text" required class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Email</label>
              <input v-model="form.email" type="email" required class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Telefono</label>
              <input v-model="form.phone" type="tel" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Especialidad</label>
              <input v-model="form.specialty" type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20" placeholder="Fuerza, hipertrofia, funcional..." />
            </div>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Bio</label>
            <textarea v-model="form.bio" rows="3" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"></textarea>
          </div>
          <div class="flex gap-3">
            <button type="submit" :disabled="saving" class="rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50">
              {{ saving ? 'Guardando...' : (editingId ? 'Actualizar' : 'Crear coach') }}
            </button>
            <button type="button" @click="showForm = false" class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">Cancelar</button>
          </div>
        </form>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="i in 6" :key="i" class="h-40 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error && !coaches.length" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
        <p class="text-sm text-wc-text">{{ error }}</p>
        <button @click="fetchCoaches" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white">Reintentar</button>
      </div>

      <!-- Coach Cards -->
      <div v-else-if="coaches.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="coach in coaches"
          :key="coach.id"
          class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 transition-colors hover:bg-wc-bg-secondary"
        >
          <div class="flex items-center gap-3 mb-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent/10">
              <span class="text-lg font-semibold text-wc-accent">{{ (coach.name || 'C').charAt(0).toUpperCase() }}</span>
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-wc-text truncate">{{ coach.name }}</p>
              <p class="text-xs text-wc-text-tertiary truncate">{{ coach.email }}</p>
            </div>
          </div>
          <div class="space-y-2">
            <div v-if="coach.specialty" class="flex items-center gap-2">
              <span class="rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-medium text-wc-text-secondary">{{ coach.specialty }}</span>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div class="rounded-lg bg-wc-bg-secondary p-2 text-center">
                <p class="font-data text-lg font-bold text-wc-text">{{ coach.clientCount || 0 }}</p>
                <p class="text-[10px] text-wc-text-tertiary">Clientes</p>
              </div>
              <div class="rounded-lg bg-wc-bg-secondary p-2 text-center">
                <p class="font-data text-lg font-bold text-wc-text">{{ coach.avgAdherence || 0 }}%</p>
                <p class="text-[10px] text-wc-text-tertiary">Adherencia</p>
              </div>
            </div>
          </div>
          <button @click="openEditForm(coach)" class="mt-3 w-full rounded-lg border border-wc-border px-3 py-1.5 text-xs font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
            Editar
          </button>
        </div>
      </div>

      <!-- Empty -->
      <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <p class="text-sm text-wc-text-tertiary">Sin coaches registrados</p>
      </div>

    </div>
  </AdminLayout>
</template>
