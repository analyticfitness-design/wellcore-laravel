<script setup>
import { ref, onMounted } from 'vue';
import { useCoachCommunity } from '../../../composables/useCoachCommunity';
import AvatarConic from '../../../components/coach/ios/AvatarConic.vue';
import EmptyState from '../../../components/coach/ios/EmptyState.vue';

const { fetchPulsos, loading, error } = useCoachCommunity();
const pulsos = ref([]);
const activePulsoId = ref(null);

async function load() {
    pulsos.value = await fetchPulsos();
}

onMounted(() => load());
</script>

<template>
  <div class="anim-entry anim-entry-2 space-y-4">
    <div v-if="loading && !pulsos.length" class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3">
      <div v-for="i in 8" :key="i" class="aspect-square rounded-2xl bg-wc-bg-tertiary animate-pulse"></div>
    </div>
    <div v-else-if="error" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center text-sm">{{ error }}</div>
    <EmptyState
      v-else-if="!pulsos.length"
      kind="activity"
      title="Sin pulsos activos"
      subtitle="Los pulsos duran 24-48h. Cuando un cliente suba uno, aparecerá aquí en orden de prioridad."
    />
    <div v-else class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3">
      <button
        v-for="p in pulsos" :key="p.id"
        @click="activePulsoId = p.id"
        class="aspect-square rounded-[14px] overflow-hidden focus:ring-2 focus:ring-wc-accent transition-all hover:scale-105 border border-[var(--b1)] flex flex-col items-center justify-center p-2"
        style="background: var(--s2); box-shadow: var(--shadow-card-ios);"
      >
        <AvatarConic
          :initial="(p.client_name || '?').charAt(0).toUpperCase()"
          :image-url="p.media_url || p.thumbnail_url || ''"
          tone="accent"
          size="lg"
        />
        <span class="text-[10px] text-wc-text-tertiary mt-1 truncate w-full">{{ p.client_name || 'Cliente' }}</span>
      </button>
    </div>
  </div>
</template>
