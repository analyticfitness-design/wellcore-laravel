<script setup>
import { ref, computed, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();
const loading = ref(false);
const dateRange = ref('30');

const pixelStatus = ref({
    is_active: false,
    pixel_id: '',
    capi_configured: false,
    test_mode: false,
});

const funnelData = ref({
    total_visits: 0,
    utm_visits: 0,
    utm_percentage: 0,
    inscriptions: 0,
    inscription_rate: 0,
    payments: 0,
    revenue: 0,
});

const campaignBreakdown = ref([]);
const sourceBreakdown = ref([]);
const deviceBreakdown = ref([]);
const recentConversions = ref([]);
const topLandingPages = ref([]);

const maxCampaignVisits = computed(() => {
    if (!campaignBreakdown.value.length) return 1;
    return Math.max(...campaignBreakdown.value.map(c => c.visits)) || 1;
});

const totalSourceVisits = computed(() =>
    sourceBreakdown.value.reduce((sum, s) => sum + s.visits, 0)
);

const totalDeviceVisits = computed(() =>
    deviceBreakdown.value.reduce((sum, d) => sum + d.count, 0)
);

const SOURCE_COLORS = {
    facebook:  'bg-blue-500',
    instagram: 'bg-pink-500',
    google:    'bg-red-500',
    tiktok:    'bg-cyan-500',
    youtube:   'bg-red-600',
    twitter:   'bg-sky-500',
    linkedin:  'bg-blue-700',
    email:     'bg-amber-500',
    whatsapp:  'bg-emerald-500',
};

const DEVICE_CONFIG = {
    mobile: {
        label: 'Movil',
        colorClass: 'text-sky-500 bg-sky-500/10',
        path: 'M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3',
    },
    desktop: {
        label: 'Escritorio',
        colorClass: 'text-violet-500 bg-violet-500/10',
        path: 'M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25Z',
    },
    tablet: {
        label: 'Tablet',
        colorClass: 'text-amber-500 bg-amber-500/10',
        path: 'M10.5 19.5h3M6.75 2.25h10.5a2.25 2.25 0 0 1 2.25 2.25v15a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25v-15a2.25 2.25 0 0 1 2.25-2.25Z',
    },
};

function sourceBarColor(name) {
    return SOURCE_COLORS[name.toLowerCase()] ?? 'bg-violet-500';
}

function sourcePercentage(visits) {
    return totalSourceVisits.value > 0
        ? Math.round((visits / totalSourceVisits.value) * 100 * 10) / 10
        : 0;
}

function devicePercentage(count) {
    return totalDeviceVisits.value > 0
        ? Math.round((count / totalDeviceVisits.value) * 100 * 10) / 10
        : 0;
}

function deviceConfig(key) {
    return DEVICE_CONFIG[key.toLowerCase()] ?? {
        label: key.charAt(0).toUpperCase() + key.slice(1),
        colorClass: 'text-wc-text-tertiary bg-wc-bg-secondary',
        path: DEVICE_CONFIG.desktop.path,
    };
}

function conversionRateBadgeClass(rate) {
    if (rate >= 5) return 'bg-emerald-500/10 text-emerald-500';
    if (rate >= 2) return 'bg-amber-500/10 text-amber-500';
    return 'bg-wc-bg-secondary text-wc-text-tertiary';
}

function campaignBarWidth(visits) {
    return maxCampaignVisits.value > 0
        ? Math.round((visits / maxCampaignVisits.value) * 100)
        : 0;
}

function formatNumber(n) {
    return Number(n ?? 0).toLocaleString('es-CO');
}

function formatRevenue(n) {
    return Number(n ?? 0).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

function truncateUrl(url, max = 60) {
    if (!url) return '';
    return url.length > max ? url.slice(0, max) + '...' : url;
}

async function fetchData() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v/admin/campaigns', {
            params: { date_range: dateRange.value },
        });
        pixelStatus.value         = data.pixel_status        ?? pixelStatus.value;
        funnelData.value          = data.funnel_data          ?? funnelData.value;
        campaignBreakdown.value   = data.campaign_breakdown   ?? [];
        sourceBreakdown.value     = data.source_breakdown     ?? [];
        deviceBreakdown.value     = data.device_breakdown     ?? [];
        recentConversions.value   = data.recent_conversions   ?? [];
        topLandingPages.value     = data.top_landing_pages    ?? [];
    } catch (e) {
        campaignBreakdown.value = [];
        sourceBreakdown.value   = [];
        deviceBreakdown.value   = [];
        recentConversions.value = [];
        topLandingPages.value   = [];
    } finally {
        loading.value = false;
    }
}

function setDateRange(value) {
    dateRange.value = value;
    fetchData();
}

onMounted(fetchData);
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header + date range toggle -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Campaign Tracker</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">Meta Pixel &amp; UTM Attribution</p>
        </div>
        <div class="flex items-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary p-1">
          <button
            v-for="[value, label] in [['7','7d'],['14','14d'],['30','30d'],['90','90d']]"
            :key="value"
            @click="setDateRange(value)"
            class="rounded-md px-3 py-1.5 text-xs font-semibold transition-all duration-200"
            :class="dateRange === value
              ? 'bg-wc-accent text-white shadow-sm'
              : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary'"
          >{{ label }}</button>
        </div>
      </div>

      <!-- Pixel status bar -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
        <div class="flex flex-wrap items-center gap-6">

          <!-- Pixel -->
          <div class="flex items-center gap-2.5">
            <span class="relative flex h-2.5 w-2.5">
              <template v-if="pixelStatus.is_active">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
              </template>
              <span v-else class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
            </span>
            <div>
              <span class="text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Pixel</span>
              <p class="font-data text-sm text-wc-text">
                <span v-if="pixelStatus.is_active" class="text-emerald-500">Activo</span>
                <span v-else class="text-red-500">No configurado</span>
                <span v-if="pixelStatus.is_active && pixelStatus.pixel_id" class="ml-1 text-wc-text-secondary">&middot; {{ pixelStatus.pixel_id }}</span>
              </p>
            </div>
          </div>

          <div class="hidden sm:block h-8 w-px bg-wc-border"></div>

          <!-- Conversions API -->
          <div class="flex items-center gap-2.5">
            <span class="relative flex h-2.5 w-2.5">
              <template v-if="pixelStatus.capi_configured">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
              </template>
              <span v-else class="relative inline-flex h-2.5 w-2.5 rounded-full bg-amber-500"></span>
            </span>
            <div>
              <span class="text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Conversions API</span>
              <p class="font-data text-sm" :class="pixelStatus.capi_configured ? 'text-emerald-500' : 'text-amber-500'">
                {{ pixelStatus.capi_configured ? 'Configurada' : 'Sin configurar' }}
              </p>
            </div>
          </div>

          <div class="hidden sm:block h-8 w-px bg-wc-border"></div>

          <!-- Test mode -->
          <div class="flex items-center gap-2.5">
            <span class="relative flex h-2.5 w-2.5">
              <span class="relative inline-flex h-2.5 w-2.5 rounded-full" :class="pixelStatus.test_mode ? 'bg-amber-500' : 'bg-wc-text-secondary/30'"></span>
            </span>
            <div>
              <span class="text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Modo Test</span>
              <p class="font-data text-sm" :class="pixelStatus.test_mode ? 'text-amber-500' : 'text-wc-text-secondary'">
                {{ pixelStatus.test_mode ? 'Activo' : 'Inactivo' }}
              </p>
            </div>
          </div>

        </div>
      </div>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
          <div v-for="n in 4" :key="n" class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary h-28"></div>
        </div>
        <div v-for="n in 3" :key="'sk' + n" class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary h-48"></div>
      </template>

      <template v-else>

        <!-- Funnel stat cards -->
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">

          <!-- Visitas totales -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 transition-transform hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
              <span class="text-xs font-medium uppercase tracking-wider text-wc-text-secondary">Visitas totales</span>
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10">
                <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ formatNumber(funnelData.total_visits) }}</p>
            <p class="mt-0.5 text-xs text-wc-text-secondary">ultimos {{ dateRange }}d</p>
          </div>

          <!-- Visitas UTM -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 transition-transform hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
              <span class="text-xs font-medium uppercase tracking-wider text-wc-text-secondary">Visitas UTM</span>
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/10">
                <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                </svg>
              </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ formatNumber(funnelData.utm_visits) }}</p>
            <p class="mt-0.5 text-xs text-wc-text-secondary">{{ funnelData.utm_percentage }}% del total</p>
          </div>

          <!-- Inscripciones -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 transition-transform hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
              <span class="text-xs font-medium uppercase tracking-wider text-wc-text-secondary">Inscripciones</span>
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                </svg>
              </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ formatNumber(funnelData.inscriptions) }}</p>
            <p class="mt-0.5 text-xs text-wc-text-secondary">{{ funnelData.inscription_rate }}% conv. de UTM</p>
          </div>

          <!-- Pagos -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 transition-transform hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
              <span class="text-xs font-medium uppercase tracking-wider text-wc-text-secondary">Pagos</span>
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-500/10">
                <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                </svg>
              </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ formatNumber(funnelData.payments) }}</p>
            <p class="mt-0.5 text-xs text-wc-text-secondary">${{ formatRevenue(funnelData.revenue) }} COP</p>
          </div>

        </div>

        <!-- Campaign breakdown table -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h2 class="font-display text-xl tracking-wide text-wc-text mb-4">Campanas</h2>

          <div v-if="campaignBreakdown.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
            <svg class="h-12 w-12 text-wc-text-secondary/30 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
            </svg>
            <p class="text-sm text-wc-text-secondary">Sin datos de campanas en este periodo</p>
            <p class="mt-1 text-xs text-wc-text-secondary/60">Los datos apareceran cuando tus campanas generen trafico con parametros UTM</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left">
              <thead>
                <tr class="border-b border-wc-border">
                  <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Campana</th>
                  <th class="pb-3 text-right text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Visitas</th>
                  <th class="hidden pb-3 text-right text-xs font-semibold uppercase tracking-wider text-wc-text-secondary sm:table-cell">Inscripciones</th>
                  <th class="hidden pb-3 text-right text-xs font-semibold uppercase tracking-wider text-wc-text-secondary sm:table-cell">Pagos</th>
                  <th class="hidden pb-3 text-right text-xs font-semibold uppercase tracking-wider text-wc-text-secondary md:table-cell">Revenue</th>
                  <th class="pb-3 text-right text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Conv.</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-wc-border/50">
                <tr
                  v-for="campaign in campaignBreakdown"
                  :key="campaign.name"
                  class="group transition-colors hover:bg-wc-bg-secondary/50"
                >
                  <td class="py-3 pr-4">
                    <div class="flex flex-col gap-1.5">
                      <span class="max-w-[200px] truncate text-sm font-medium text-wc-text" :title="campaign.name">
                        {{ campaign.name }}
                      </span>
                      <div class="h-1 w-full overflow-hidden rounded-full bg-wc-border/30">
                        <div
                          class="h-full rounded-full bg-wc-accent/60 transition-all duration-500"
                          :style="{ width: campaignBarWidth(campaign.visits) + '%' }"
                        ></div>
                      </div>
                    </div>
                  </td>
                  <td class="py-3 text-right font-data text-sm text-wc-text">{{ formatNumber(campaign.visits) }}</td>
                  <td class="hidden py-3 text-right font-data text-sm text-emerald-500 sm:table-cell">{{ campaign.inscriptions }}</td>
                  <td class="hidden py-3 text-right font-data text-sm text-sky-500 sm:table-cell">{{ campaign.payments }}</td>
                  <td class="hidden py-3 text-right font-data text-sm text-wc-text md:table-cell">${{ formatRevenue(campaign.revenue) }}</td>
                  <td class="py-3 text-right">
                    <span
                      class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                      :class="conversionRateBadgeClass(campaign.conversion_rate)"
                    >{{ campaign.conversion_rate }}%</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Source + Device -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

          <!-- Source breakdown -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="font-display text-xl tracking-wide text-wc-text mb-4">Fuentes de Trafico</h2>

            <div v-if="sourceBreakdown.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
              <svg class="h-10 w-10 text-wc-text-secondary/30 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
              </svg>
              <p class="text-sm text-wc-text-secondary">Sin datos de fuentes UTM</p>
            </div>

            <div v-else class="space-y-3">
              <div v-for="source in sourceBreakdown" :key="source.name">
                <div class="mb-1 flex items-center justify-between">
                  <span class="text-sm font-medium capitalize text-wc-text">{{ source.name }}</span>
                  <div class="flex items-center gap-3">
                    <span class="font-data text-xs text-wc-text-secondary">{{ source.conversions }} conv.</span>
                    <span class="font-data text-sm font-semibold text-wc-text">{{ formatNumber(source.visits) }}</span>
                  </div>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-wc-border/30">
                  <div
                    class="h-full rounded-full transition-all duration-500"
                    :class="sourceBarColor(source.name)"
                    :style="{ width: sourcePercentage(source.visits) + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Device breakdown -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="font-display text-xl tracking-wide text-wc-text mb-4">Dispositivos</h2>

            <div v-if="deviceBreakdown.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
              <svg class="h-10 w-10 text-wc-text-secondary/30 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
              </svg>
              <p class="text-sm text-wc-text-secondary">Sin datos de dispositivos</p>
            </div>

            <div v-else class="space-y-4">
              <div v-for="device in deviceBreakdown" :key="device.device" class="flex items-center gap-3">
                <div
                  class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                  :class="deviceConfig(device.device).colorClass"
                >
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="deviceConfig(device.device).path" />
                  </svg>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="mb-1 flex items-center justify-between">
                    <span class="text-sm font-medium text-wc-text">{{ deviceConfig(device.device).label }}</span>
                    <span class="font-data text-sm font-semibold text-wc-text">
                      {{ formatNumber(device.count) }}
                      <span class="text-xs font-normal text-wc-text-secondary">({{ devicePercentage(device.count) }}%)</span>
                    </span>
                  </div>
                  <div class="h-2 w-full overflow-hidden rounded-full bg-wc-border/30">
                    <div
                      class="h-full rounded-full bg-wc-accent/60 transition-all duration-500"
                      :style="{ width: devicePercentage(device.count) + '%' }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Recent conversions -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h2 class="font-display text-xl tracking-wide text-wc-text mb-4">Conversiones Recientes</h2>

          <div v-if="recentConversions.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
            <svg class="h-10 w-10 text-wc-text-secondary/30 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
            </svg>
            <p class="text-sm text-wc-text-secondary">Sin conversiones recientes</p>
            <p class="mt-1 text-xs text-wc-text-secondary/60">Las conversiones apareceran cuando los visitantes completen inscripciones o pagos</p>
          </div>

          <div v-else class="space-y-2">
            <div
              v-for="(conversion, i) in recentConversions"
              :key="i"
              class="flex items-center gap-3 rounded-lg bg-wc-bg-secondary/50 px-4 py-3 transition-colors hover:bg-wc-bg-secondary"
            >
              <!-- Type badge -->
              <span
                v-if="conversion.conversion_type === 'payment'"
                class="inline-flex shrink-0 items-center rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-500"
              >
                <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                </svg>
                Pago
              </span>
              <span
                v-else
                class="inline-flex shrink-0 items-center rounded-full bg-sky-500/10 px-2.5 py-1 text-xs font-semibold text-sky-500"
              >
                <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                Lead
              </span>

              <!-- Campaign / source info -->
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                  <span v-if="conversion.utm_campaign" class="truncate text-sm font-medium text-wc-text">
                    {{ conversion.utm_campaign }}
                  </span>
                  <span v-else class="text-sm italic text-wc-text-secondary">Sin campana</span>
                </div>
                <div class="mt-0.5 flex items-center gap-2">
                  <span v-if="conversion.utm_source" class="text-xs capitalize text-wc-text-secondary">{{ conversion.utm_source }}</span>
                  <span v-if="conversion.utm_medium" class="text-xs text-wc-text-secondary">&middot; {{ conversion.utm_medium }}</span>
                  <span v-if="conversion.device_type" class="text-xs text-wc-text-secondary">&middot; {{ conversion.device_type }}</span>
                </div>
              </div>

              <!-- Time ago -->
              <span class="shrink-0 font-data text-xs text-wc-text-secondary">{{ conversion.time_ago }}</span>
            </div>
          </div>
        </div>

        <!-- Top landing pages -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h2 class="font-display text-xl tracking-wide text-wc-text mb-4">Top Landing Pages</h2>

          <div v-if="topLandingPages.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
            <svg class="h-10 w-10 text-wc-text-secondary/30 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
            </svg>
            <p class="text-sm text-wc-text-secondary">Sin datos de landing pages</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left">
              <thead>
                <tr class="border-b border-wc-border">
                  <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">URL</th>
                  <th class="pb-3 text-right text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Visitas</th>
                  <th class="pb-3 text-right text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Conversiones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-wc-border/50">
                <tr
                  v-for="page in topLandingPages"
                  :key="page.url"
                  class="group transition-colors hover:bg-wc-bg-secondary/50"
                >
                  <td class="py-3 pr-4">
                    <span
                      class="block max-w-[400px] truncate font-mono text-sm text-wc-text"
                      :title="page.url"
                    >{{ truncateUrl(page.url) }}</span>
                  </td>
                  <td class="py-3 text-right font-data text-sm text-wc-text">{{ formatNumber(page.visits) }}</td>
                  <td class="py-3 text-right">
                    <span
                      v-if="page.conversions > 0"
                      class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 font-data text-xs font-semibold text-emerald-500"
                    >{{ page.conversions }}</span>
                    <span v-else class="font-data text-xs text-wc-text-secondary">0</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </template>

    </div>
  </AdminLayout>
</template>
