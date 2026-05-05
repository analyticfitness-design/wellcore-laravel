<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAdminCommunity } from '../../../composables/useAdminCommunity';

const { fetchCommunityFeed, loading, error } = useAdminCommunity();
const events = ref([]);
const page = ref(1);
const filterType = ref(null); // null | 'community' | 'achievement' | 'pr'

async function load() {
    const data = await fetchCommunityFeed({ type: filterType.value, page: page.value });
    if (data) {
        events.value = data.events || data.data || [];
    }
}

function timeAgo(iso) {
    if (!iso) return '';
    const diffMs = Date.now() - new Date(iso).getTime();
    const minutes = Math.floor(diffMs / 60000);
    if (minutes < 60) return `hace ${minutes}m`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `hace ${hours}h`;
    return `hace ${Math.floor(hours / 24)}d`;
}

onMounted(() => load());
</script>

<template>
  <div class="space-y-4">
    <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4 text-sm text-wc-text-tertiary">
      Live feed de la comunidad cross-coach. Filtros y selector de coach disponibles en página completa
      <RouterLink to="/admin/feed" class="text-wc-accent hover:underline">/admin/feed</RouterLink>.
    </div>

    <div v-if="loading && !events.length" class="space-y-2">
      <div v-for="i in 5" :key="i" class="h-16 rounded-xl bg-wc-bg-tertiary animate-pulse"></div>
    </div>
    <div v-else-if="error" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center text-sm">{{ error }}</div>
    <div v-else-if="!events.length" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-12 text-center">
      <p class="font-display text-lg text-wc-text">Sin eventos recientes</p>
      <p class="text-sm text-wc-text-tertiary mt-2">El feed se actualizará en tiempo real cuando los clientes interactúen.</p>
    </div>
    <div v-else class="space-y-2">
      <article v-for="(event, idx) in events" :key="idx" class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-wc-text">{{ event.title || event.type || 'Evento' }}</p>
            <p v-if="event.description" class="text-sm text-wc-text-secondary truncate">{{ event.description }}</p>
          </div>
          <span class="text-xs text-wc-text-tertiary shrink-0">{{ timeAgo(event.created_at) }}</span>
        </div>
      </article>
    </div>
  </div>
</template>
