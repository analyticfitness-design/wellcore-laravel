<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

const loading = ref(true);
const error = ref(null);
const plans = ref([]);

async function fetchPlans() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/admin/plans');
        plans.value = response.data.plans || response.data.data || [];
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar planes';
    } finally {
        loading.value = false;
    }
}

function getPlanColor(type) {
    const map = {
        premium: 'from-red-600 to-red-500',
        basic: 'from-sky-600 to-sky-500',
        rise: 'from-amber-500 to-amber-400',
        presencial: 'from-emerald-600 to-emerald-500',
    };
    return map[type] || 'from-gray-600 to-gray-500';
}

onMounted(() => {
    fetchPlans();
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">Planes</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Templates de planes de entrenamiento</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="i in 6" :key="i" class="h-48 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
        <p class="text-sm text-wc-text">{{ error }}</p>
        <button @click="fetchPlans" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white">Reintentar</button>
      </div>

      <!-- Plans Grid -->
      <div v-else-if="plans.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="plan in plans"
          :key="plan.id"
          class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary"
        >
          <!-- Color Bar -->
          <div class="h-2 bg-gradient-to-r" :class="getPlanColor(plan.type)"></div>
          <div class="p-5">
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-display text-lg tracking-wide text-wc-text">{{ plan.name }}</h3>
              <span class="rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">{{ plan.type }}</span>
            </div>
            <p v-if="plan.description" class="mb-4 text-xs text-wc-text-tertiary line-clamp-2">{{ plan.description }}</p>
            <div class="grid grid-cols-2 gap-2">
              <div class="rounded-lg bg-wc-bg-secondary p-2 text-center">
                <p class="font-data text-lg font-bold text-wc-text">{{ plan.weeks || '-' }}</p>
                <p class="text-[10px] text-wc-text-tertiary">Semanas</p>
              </div>
              <div class="rounded-lg bg-wc-bg-secondary p-2 text-center">
                <p class="font-data text-lg font-bold text-wc-text">{{ plan.daysPerWeek || '-' }}</p>
                <p class="text-[10px] text-wc-text-tertiary">Dias/sem</p>
              </div>
              <div class="rounded-lg bg-wc-bg-secondary p-2 text-center">
                <p class="font-data text-lg font-bold text-wc-text">{{ plan.activeClients || 0 }}</p>
                <p class="text-[10px] text-wc-text-tertiary">Activos</p>
              </div>
              <div class="rounded-lg bg-wc-bg-secondary p-2 text-center">
                <p class="font-data text-lg font-bold text-wc-text capitalize">{{ plan.level || '-' }}</p>
                <p class="text-[10px] text-wc-text-tertiary">Nivel</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <svg class="mx-auto h-10 w-10 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
        </svg>
        <p class="mt-3 text-sm text-wc-text-tertiary">Sin planes configurados</p>
      </div>

    </div>
  </AdminLayout>
</template>
