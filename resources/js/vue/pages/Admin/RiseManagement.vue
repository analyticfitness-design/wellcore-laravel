<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

const loading = ref(true);
const error = ref(null);
const data = ref(null);

async function fetchRise() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/admin/rise');
        data.value = response.data;
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar datos RISE';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchRise();
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <div class="flex items-center gap-3">
          <h1 class="font-display text-3xl tracking-wide text-wc-text">Programa RISE</h1>
          <span class="rounded-full bg-gradient-to-r from-amber-500/15 to-amber-400/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-amber-500">12 Semanas</span>
        </div>
        <p class="mt-1 text-sm text-wc-text-tertiary">Gestion del programa de transformacion RISE</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-4">
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
          <div v-for="i in 4" :key="i" class="h-28 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
        </div>
        <div class="h-64 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
        <p class="text-sm text-wc-text">{{ error }}</p>
        <button @click="fetchRise" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white">Reintentar</button>
      </div>

      <!-- Content -->
      <div v-else-if="data" class="space-y-6">
        <!-- Stats -->
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Participantes activos</span>
            <p class="mt-2 font-data text-2xl font-bold text-wc-text">{{ data.activeParticipants || 0 }}</p>
          </div>
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Completados</span>
            <p class="mt-2 font-data text-2xl font-bold text-emerald-500">{{ data.completedPrograms || 0 }}</p>
          </div>
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Adherencia promedio</span>
            <p class="mt-2 font-data text-2xl font-bold text-wc-text">{{ data.avgAdherence || 0 }}%</p>
          </div>
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Ingresos RISE</span>
            <p class="mt-2 font-data text-2xl font-bold text-amber-500">${{ (data.revenue || 0).toLocaleString('es-CO') }}</p>
          </div>
        </div>

        <!-- Active Programs -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">Programas Activos</h3>
          <div v-if="data.programs && data.programs.length" class="divide-y divide-wc-border">
            <div v-for="(program, idx) in data.programs" :key="idx" class="flex items-center gap-4 py-3">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-500/10">
                <span class="text-sm font-semibold text-amber-500">{{ (program.clientName || 'U').charAt(0).toUpperCase() }}</span>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-wc-text truncate">{{ program.clientName }}</p>
                <p class="text-xs text-wc-text-tertiary">Semana {{ program.currentWeek }} de {{ program.totalWeeks || 12 }}</p>
              </div>
              <div class="flex items-center gap-2">
                <div class="hidden sm:block">
                  <div class="h-1.5 w-20 overflow-hidden rounded-full bg-wc-bg-secondary">
                    <div class="h-full rounded-full bg-amber-500 transition-all" :style="{ width: `${(program.currentWeek / (program.totalWeeks || 12)) * 100}%` }"></div>
                  </div>
                </div>
                <span class="text-xs font-data text-wc-text-tertiary">{{ program.adherence || 0 }}%</span>
              </div>
            </div>
          </div>
          <div v-else class="py-8 text-center">
            <p class="text-sm text-wc-text-tertiary">Sin programas activos</p>
          </div>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
