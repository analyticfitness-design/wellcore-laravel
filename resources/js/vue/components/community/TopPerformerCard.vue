<script setup>
import { useRouter } from 'vue-router';

const props = defineProps({
    performer: { type: Object, required: true },
    rank: { type: Number, default: 0 },
});

const router = useRouter();
const medals = ['🥇', '🥈', '🥉']; // gold, silver, bronze

function openClient() {
    router.push(`/coach/clients?focus=${props.performer.client_id}`);
}
</script>

<template>
  <button
    type="button"
    @click="openClient"
    class="w-full flex items-center gap-3 rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 hover:border-wc-accent/40 hover:bg-wc-bg-tertiary/50 transition-all text-left"
  >
    <span v-if="rank >= 1 && rank <= 3" class="text-2xl">{{ medals[rank - 1] }}</span>
    <div class="h-10 w-10 rounded-full bg-wc-accent/15 flex items-center justify-center overflow-hidden">
      <img v-if="performer.avatar_url" :src="performer.avatar_url" :alt="performer.client_name" class="h-full w-full object-cover" />
      <span v-else class="text-sm font-semibold text-wc-accent">
        {{ (performer.client_name || '?').charAt(0) }}
      </span>
    </div>
    <div class="flex-1 min-w-0">
      <p class="font-semibold text-wc-text truncate">{{ performer.client_name || `Cliente ${performer.client_id}` }}</p>
      <p class="text-xs text-wc-text-tertiary truncate">
        <template v-if="performer.metric">{{ performer.metric }}</template>
        <template v-else-if="performer.workout_count !== undefined">{{ performer.workout_count }} entrenamientos</template>
      </p>
    </div>
    <svg class="h-4 w-4 text-wc-text-tertiary shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
    </svg>
  </button>
</template>
