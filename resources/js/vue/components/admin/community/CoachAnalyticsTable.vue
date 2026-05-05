<script setup>
import CoachSparkline from './CoachSparkline.vue';

defineProps({
    coaches: { type: Array, default: () => [] },
});
const emit = defineEmits(['drill-down']);

function format(num) {
    if (num === null || num === undefined) return '0';
    return num >= 1000 ? (num / 1000).toFixed(1) + 'k' : num;
}

function alertIcon(type) {
    return type === 'no_activity_7d' ? '🔥'
        : type === 'client_spam' ? '⚠️'
        : type === 'thread_conflict' ? '💥'
        : null;
}
</script>

<template>
  <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-wc-bg-tertiary text-xs uppercase tracking-widest text-wc-text-tertiary">
          <tr>
            <th class="text-left px-4 py-3">Coach</th>
            <th class="text-right px-3 py-3">Clientes</th>
            <th class="text-right px-3 py-3">Posts 30d</th>
            <th class="text-right px-3 py-3">Engag</th>
            <th class="text-right px-3 py-3">Resp p50</th>
            <th class="text-center px-3 py-3">30d</th>
            <th class="text-center px-3 py-3">Alert</th>
            <th class="text-right px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-wc-border">
          <tr
            v-for="c in coaches" :key="c.coach_id"
            class="hover:bg-wc-bg-tertiary/40 cursor-pointer transition-colors"
            @click="emit('drill-down', c.coach_id)"
          >
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-full bg-wc-accent/15 flex items-center justify-center overflow-hidden shrink-0">
                  <img v-if="c.avatar_url" :src="c.avatar_url" :alt="c.coach_name" class="h-full w-full object-cover" />
                  <span v-else class="text-xs font-semibold text-wc-accent">{{ (c.coach_name || '?').charAt(0) }}</span>
                </div>
                <span class="font-medium text-wc-text">{{ c.coach_name }}</span>
              </div>
            </td>
            <td class="text-right px-3 py-3 text-wc-text">{{ c.active_clients_count ?? c.posts_count ?? 0 }}</td>
            <td class="text-right px-3 py-3 text-wc-text">{{ format(c.total_posts_count ?? c.posts_count) }}</td>
            <td class="text-right px-3 py-3 text-wc-text">{{ Math.round((c.engagement_rate ?? 0) * 100) }}%</td>
            <td class="text-right px-3 py-3 text-wc-text-tertiary">{{ c.response_time_p50_min ?? '—' }}min</td>
            <td class="text-center px-3 py-3">
              <CoachSparkline :series="c.posts_per_day_30d || []" />
            </td>
            <td class="text-center px-3 py-3">
              <span v-if="c.alert" :title="c.alert" class="text-lg">{{ alertIcon(c.alert) }}</span>
              <span v-else class="text-wc-text-tertiary">—</span>
            </td>
            <td class="text-right px-4 py-3">
              <button class="text-wc-accent text-xs font-semibold hover:underline">Drill →</button>
            </td>
          </tr>
          <tr v-if="!coaches.length">
            <td colspan="8" class="px-4 py-12 text-center text-wc-text-tertiary text-sm">
              Sin coaches para mostrar este período.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
