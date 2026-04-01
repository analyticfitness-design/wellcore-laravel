<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();
const loading = ref(true);
const dateRange = ref('month');

const coachScore = ref(0);
const coachScoreLabel = ref('');
const avgResponseHours = ref(0);
const checkinReplyRate = ref(0);
const retentionRate = ref(0);
const avgBienestar = ref(0);
const totalCheckins = ref(0);
const totalMessages = ref(0);

const clientOverview = ref([]);
const slaBreakdown = ref({ within24h: 0, within48h: 0, over48h: 0 });
const bienestarTrend = ref([]);
const revenueStats = ref({ total: 0, monthly: 0, clients_paying: 0 });

async function loadAnalytics() {
    loading.value = true;
    try {
        const { data } = await api.get(`/api/v/coach/analytics?range=${dateRange.value}`);
        coachScore.value = data.coachScore || 0;
        coachScoreLabel.value = data.coachScoreLabel || '';
        avgResponseHours.value = data.avgResponseHours || 0;
        checkinReplyRate.value = data.checkinReplyRate || 0;
        retentionRate.value = data.retentionRate || 0;
        avgBienestar.value = data.avgBienestar || 0;
        totalCheckins.value = data.totalCheckins || 0;
        totalMessages.value = data.totalMessages || 0;
        clientOverview.value = data.clientOverview || [];
        slaBreakdown.value = data.slaBreakdown || slaBreakdown.value;
        bienestarTrend.value = data.bienestarTrend || [];
        revenueStats.value = data.revenueStats || revenueStats.value;
    } catch (e) {
        // silent
    } finally {
        loading.value = false;
    }
}

function switchRange(range) {
    dateRange.value = range;
    loadAnalytics();
}

onMounted(loadAnalytics);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Analytics</h1>
          <p class="mt-1 text-sm text-wc-text-tertiary">Rendimiento y metricas de tu equipo</p>
        </div>
        <div class="flex items-center gap-1 rounded-lg border border-wc-border bg-wc-bg-secondary p-1">
          <button
            v-for="r in [{ key: 'month', label: 'Mes' }, { key: 'quarter', label: 'Trimestre' }, { key: 'year', label: 'Ano' }, { key: 'all', label: 'Todo' }]"
            :key="r.key"
            @click="switchRange(r.key)"
            class="rounded-md px-3 py-1.5 text-xs font-medium transition-colors"
            :class="dateRange === r.key ? 'bg-wc-accent text-white shadow-sm' : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary'"
          >{{ r.label }}</button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex items-center gap-2 text-sm text-wc-text-tertiary">
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        Actualizando metricas...
      </div>

      <template v-else>

        <!-- Coach Score Hero -->
        <div v-if="coachScore > 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
              <div class="relative flex h-20 w-20 shrink-0 items-center justify-center">
                <svg class="h-20 w-20 -rotate-90" viewBox="0 0 80 80">
                  <circle cx="40" cy="40" r="34" fill="none" stroke="currentColor" stroke-width="6" class="text-wc-border" />
                  <circle cx="40" cy="40" r="34" fill="none" stroke-width="6"
                    :stroke-dasharray="`${coachScore * 2.136} 213.6`"
                    stroke-linecap="round"
                    :class="coachScore >= 75 ? 'text-emerald-500' : coachScore >= 50 ? 'text-amber-500' : 'text-red-500'" />
                </svg>
                <span class="absolute font-data text-xl font-bold text-wc-text">{{ coachScore }}</span>
              </div>
              <div>
                <h2 class="font-display text-lg tracking-wide text-wc-text">Coach Score</h2>
                <p class="text-sm font-semibold" :class="coachScore >= 75 ? 'text-emerald-500' : coachScore >= 50 ? 'text-amber-500' : 'text-red-500'">{{ coachScoreLabel }}</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">Puntuacion compuesta de rendimiento</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-x-6 gap-y-1.5 text-xs sm:grid-cols-3">
              <div class="flex items-center justify-between gap-2">
                <span class="text-wc-text-tertiary">Respuesta</span>
                <span class="font-data font-semibold" :class="avgResponseHours <= 24 ? 'text-emerald-500' : avgResponseHours <= 48 ? 'text-amber-500' : 'text-red-500'">{{ avgResponseHours }}h</span>
              </div>
              <div class="flex items-center justify-between gap-2">
                <span class="text-wc-text-tertiary">Reply Rate</span>
                <span class="font-data font-semibold text-wc-text">{{ checkinReplyRate }}%</span>
              </div>
              <div class="flex items-center justify-between gap-2">
                <span class="text-wc-text-tertiary">Retencion</span>
                <span class="font-data font-semibold text-wc-text">{{ retentionRate }}%</span>
              </div>
              <div class="flex items-center justify-between gap-2">
                <span class="text-wc-text-tertiary">Bienestar</span>
                <span class="font-data font-semibold text-wc-text">{{ avgBienestar }}/10</span>
              </div>
              <div class="flex items-center justify-between gap-2">
                <span class="text-wc-text-tertiary">Check-ins</span>
                <span class="font-data font-semibold text-wc-text">{{ totalCheckins }}</span>
              </div>
              <div class="flex items-center justify-between gap-2">
                <span class="text-wc-text-tertiary">Mensajes</span>
                <span class="font-data font-semibold text-wc-text">{{ totalMessages }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- SLA + Revenue -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
          <!-- SLA Breakdown -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h3 class="text-sm font-semibold text-wc-text mb-4">SLA de Respuesta</h3>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-xs text-wc-text-tertiary">Dentro de 24h</span>
                <span class="font-data text-sm font-semibold text-emerald-500">{{ slaBreakdown.within24h }}%</span>
              </div>
              <div class="h-2 w-full rounded-full bg-wc-bg-secondary">
                <div class="h-2 rounded-full bg-emerald-500" :style="{ width: slaBreakdown.within24h + '%' }"></div>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-xs text-wc-text-tertiary">24-48h</span>
                <span class="font-data text-sm font-semibold text-amber-500">{{ slaBreakdown.within48h }}%</span>
              </div>
              <div class="h-2 w-full rounded-full bg-wc-bg-secondary">
                <div class="h-2 rounded-full bg-amber-500" :style="{ width: slaBreakdown.within48h + '%' }"></div>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-xs text-wc-text-tertiary">Mas de 48h</span>
                <span class="font-data text-sm font-semibold text-red-500">{{ slaBreakdown.over48h }}%</span>
              </div>
              <div class="h-2 w-full rounded-full bg-wc-bg-secondary">
                <div class="h-2 rounded-full bg-red-500" :style="{ width: slaBreakdown.over48h + '%' }"></div>
              </div>
            </div>
          </div>

          <!-- Revenue -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h3 class="text-sm font-semibold text-wc-text mb-4">Revenue</h3>
            <div class="grid grid-cols-3 gap-4">
              <div class="text-center">
                <p class="font-data text-2xl font-bold text-wc-text">${{ revenueStats.total.toLocaleString() }}</p>
                <p class="text-[10px] text-wc-text-tertiary">Total</p>
              </div>
              <div class="text-center">
                <p class="font-data text-2xl font-bold text-emerald-500">${{ revenueStats.monthly.toLocaleString() }}</p>
                <p class="text-[10px] text-wc-text-tertiary">Mensual</p>
              </div>
              <div class="text-center">
                <p class="font-data text-2xl font-bold text-wc-text">{{ revenueStats.clients_paying }}</p>
                <p class="text-[10px] text-wc-text-tertiary">Clientes activos</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Client Overview Table -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
          <div class="px-5 py-3 border-b border-wc-border">
            <h3 class="text-sm font-semibold text-wc-text">Resumen de Clientes</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-wc-border bg-wc-bg-secondary/50">
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cliente</th>
                  <th class="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Bienestar</th>
                  <th class="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Check-ins</th>
                  <th class="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Adherencia</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-wc-border">
                <tr v-for="c in clientOverview" :key="c.id" class="hover:bg-wc-bg-secondary/30">
                  <td class="px-4 py-2.5 font-medium text-wc-text">{{ c.name }}</td>
                  <td class="px-4 py-2.5 text-center">
                    <span class="font-data font-semibold" :class="(c.bienestar || 0) >= 7 ? 'text-emerald-500' : (c.bienestar || 0) >= 4 ? 'text-amber-500' : 'text-red-500'">{{ c.bienestar || '-' }}</span>
                  </td>
                  <td class="px-4 py-2.5 text-center font-data text-wc-text">{{ c.checkins || 0 }}</td>
                  <td class="px-4 py-2.5 text-center font-data text-wc-text">{{ c.adherence || 0 }}%</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="clientOverview.length === 0" class="py-8 text-center text-sm text-wc-text-tertiary">Sin datos de clientes</div>
        </div>

      </template>
    </div>
  </CoachLayout>
</template>
