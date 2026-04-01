<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

const loading = ref(true);
const error = ref(null);
const data = ref(null);

async function fetchAnalytics() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/admin/chat-analytics');
        data.value = response.data;
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar analiticas de chat';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchAnalytics();
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">Chat Analytics</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Metricas de uso del chat AI</p>
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
        <button @click="fetchAnalytics" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white">Reintentar</button>
      </div>

      <!-- Content -->
      <div v-else-if="data" class="space-y-6">
        <!-- Stats -->
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Total mensajes</span>
            <p class="mt-2 font-data text-2xl font-bold text-wc-text">{{ data.totalMessages?.toLocaleString('es-CO') || 0 }}</p>
          </div>
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Mensajes hoy</span>
            <p class="mt-2 font-data text-2xl font-bold text-sky-500">{{ data.messagesToday || 0 }}</p>
          </div>
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Tiempo promedio resp.</span>
            <p class="mt-2 font-data text-2xl font-bold text-wc-text">{{ data.avgResponseTime || '0' }}s</p>
          </div>
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Usuarios activos</span>
            <p class="mt-2 font-data text-2xl font-bold text-emerald-500">{{ data.activeUsers || 0 }}</p>
          </div>
        </div>

        <!-- Message Volume Chart -->
        <div v-if="data.volumeChart && data.volumeChart.length" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">Volumen de Mensajes</h3>
          <div class="space-y-2">
            <div v-for="(item, idx) in data.volumeChart" :key="idx" class="flex items-center gap-3">
              <span class="w-16 text-xs font-medium text-wc-text-tertiary">{{ item.date || item.day }}</span>
              <div class="flex-1 rounded-full bg-wc-bg-secondary h-4 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-sky-600 to-sky-500 transition-all duration-500" :style="{ width: `${(item.count / (data.volumeChart.reduce((max, i) => Math.max(max, i.count), 1))) * 100}%` }"></div>
              </div>
              <span class="w-12 text-right text-xs font-data font-medium text-wc-text">{{ item.count }}</span>
            </div>
          </div>
        </div>

        <!-- Top Users -->
        <div v-if="data.topUsers && data.topUsers.length" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">Top Usuarios Chat</h3>
          <div class="divide-y divide-wc-border">
            <div v-for="(user, idx) in data.topUsers" :key="idx" class="flex items-center gap-3 py-3">
              <span class="flex h-6 w-6 items-center justify-center rounded-full bg-wc-bg-secondary text-xs font-bold text-wc-text-tertiary">{{ idx + 1 }}</span>
              <p class="flex-1 text-sm text-wc-text truncate">{{ user.name }}</p>
              <span class="font-data text-sm font-medium text-wc-text">{{ user.messageCount }} msgs</span>
            </div>
          </div>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
