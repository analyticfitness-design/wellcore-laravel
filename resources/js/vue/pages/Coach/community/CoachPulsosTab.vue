<script setup>
import { ref, onMounted } from 'vue';
import { useCoachCommunity } from '../../../composables/useCoachCommunity';

const { fetchPulsos, loading, error } = useCoachCommunity();
const pulsos = ref([]);
const activePulsoId = ref(null);

async function load() {
    pulsos.value = await fetchPulsos();
}

onMounted(() => load());
</script>

<template>
  <div class="space-y-4">
    <div v-if="loading && !pulsos.length" class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3">
      <div v-for="i in 8" :key="i" class="aspect-square rounded-2xl bg-wc-bg-tertiary animate-pulse"></div>
    </div>
    <div v-else-if="error" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center text-sm">{{ error }}</div>
    <div v-else-if="!pulsos.length" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-12 text-center">
      <p class="font-display text-lg text-wc-text">Sin pulsos activos</p>
      <p class="text-sm text-wc-text-tertiary mt-2 max-w-md mx-auto">
        Los pulsos duran 24-48h. Cuando un cliente suba uno, aparecerá aquí en orden de prioridad.
      </p>
    </div>
    <div v-else class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3">
      <button
        v-for="p in pulsos" :key="p.id"
        @click="activePulsoId = p.id"
        class="aspect-square rounded-2xl overflow-hidden focus:ring-2 focus:ring-wc-accent transition-all hover:scale-105 bg-wc-bg-secondary border border-wc-border flex flex-col items-center justify-center p-2"
      >
        <div class="w-14 h-14 rounded-full bg-wc-accent/15 flex items-center justify-center text-wc-accent overflow-hidden">
          <img v-if="p.media_url || p.thumbnail_url" :src="p.media_url || p.thumbnail_url" alt="" class="w-full h-full object-cover" />
          <span v-else class="text-lg font-semibold">{{ (p.client_name || '?').charAt(0) }}</span>
        </div>
        <span class="text-[10px] text-wc-text-tertiary mt-1 truncate w-full">{{ p.client_name || 'Cliente' }}</span>
      </button>
    </div>
  </div>
</template>
