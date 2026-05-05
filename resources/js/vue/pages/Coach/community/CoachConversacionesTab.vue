<script setup>
import { ref, computed, onMounted } from 'vue';
import { useCoachCommunity } from '../../../composables/useCoachCommunity';

const { fetchThreads, loading, error } = useCoachCommunity();
const threads = ref([]);
const FILTERS = [
    { key: 'all',         label: 'Todos' },
    { key: 'unanswered',  label: 'Sin respuesta de coach' },
    { key: 'large',       label: '+50 comentarios' },
    { key: 'conflicted',  label: 'Conflictos' },
];
const activeFilter = ref('all');

const filtered = computed(() => {
    if (activeFilter.value === 'all') return threads.value;
    if (activeFilter.value === 'unanswered') return threads.value.filter(t => !t.has_coach_reply);
    if (activeFilter.value === 'large') return threads.value.filter(t => t.thread_size >= 50);
    if (activeFilter.value === 'conflicted') return threads.value.filter(t => t.is_conflicted);
    return threads.value;
});

async function load() {
    const data = await fetchThreads({ sinceDays: 7, page: 1, perPage: 30 });
    if (data) threads.value = data.data || [];
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
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
      <button
        v-for="f in FILTERS" :key="f.key"
        @click="activeFilter = f.key"
        :class="activeFilter === f.key ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary'"
        class="shrink-0 rounded-full px-4 py-1.5 text-xs font-semibold transition-colors"
      >{{ f.label }}</button>
    </div>

    <div v-if="loading && !threads.length" class="space-y-3">
      <div v-for="i in 4" :key="i" class="h-20 rounded-xl border border-wc-border bg-wc-bg-secondary animate-pulse"></div>
    </div>
    <div v-else-if="error" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center text-sm text-wc-text">
      {{ error }}
    </div>
    <div v-else-if="!filtered.length" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-12 text-center">
      <p class="text-wc-text font-display text-lg">Sin conversaciones recientes</p>
      <p class="text-sm text-wc-text-tertiary mt-2">Anímalos a interactuar con un mensaje al equipo.</p>
    </div>
    <div v-else class="space-y-2">
      <article
        v-for="thread in filtered" :key="thread.post_id"
        class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4 hover:border-wc-accent/30 transition-colors cursor-pointer"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-wc-text truncate">{{ thread.post_author_name }}</p>
            <p class="text-sm text-wc-text-secondary truncate">"{{ thread.post_excerpt }}"</p>
            <div class="mt-2 flex items-center gap-3 text-xs text-wc-text-tertiary">
              <span>{{ thread.thread_size }} comentarios</span>
              <span>·</span>
              <span>{{ thread.participants_count }} participantes</span>
              <span>·</span>
              <span>{{ timeAgo(thread.last_activity_at) }}</span>
            </div>
          </div>
          <div class="shrink-0 flex flex-col items-end gap-1">
            <span v-if="thread.has_coach_reply" class="rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 text-[10px] font-semibold">
              Respondiste
            </span>
            <span v-else class="rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 px-2 py-0.5 text-[10px] font-semibold">
              ⚠️ Sin respuesta
            </span>
            <span v-if="thread.is_conflicted" class="rounded-full bg-rose-500/10 text-rose-600 dark:text-rose-400 px-2 py-0.5 text-[10px] font-semibold">
              Atención
            </span>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>
