<script setup>
import { computed, watch } from 'vue';
import { RouterLink } from 'vue-router';
import { useHaptics } from '../../composables/useHaptics';
import { useCelebration } from '../../composables/useCelebration';

const props = defineProps({
    missions: { type: Array, default: () => [] },
});

const haptics = useHaptics();
const celebration = useCelebration();

// Mission route mapping (API returns route keys, we map to Vue routes)
const missionRouteMap = {
    training:  '/client/plan?tab=training',
    checkin:   '/client/checkin',
    weight:    '/client/metrics',
    nutrition: '/client/plan?tab=nutrition',
};

function getMissionRoute(mission) {
    return missionRouteMap[mission.key] || mission.route || '/client/dashboard';
}

function getMissionStatusClass(completed) {
    return completed
        ? 'border-emerald-500/30 bg-emerald-500/5 hover:bg-emerald-500/10'
        : 'border-wc-border bg-wc-bg-tertiary hover:bg-wc-bg-secondary';
}

function handleMissionTap() {
    // Tap feedback suave — respeta OS vibration setting.
    haptics.light();
}

const completedCount = computed(() => props.missions.filter(m => m.completed).length);

// Fase 8: celebrate al completar una misión que antes estaba pending.
// Comparamos snapshots del array y si una mission pasó pending → completed,
// disparamos haptic success + celebración visual (reusando BentoCelebration global).
let lastSnapshot = [];
watch(() => props.missions, (next) => {
    if (!Array.isArray(next)) return;
    if (lastSnapshot.length === 0) {
        lastSnapshot = next.map(m => ({ key: m.key, completed: !!m.completed }));
        return;
    }
    // Buscar transición pending → completed
    for (const mission of next) {
        const prev = lastSnapshot.find(p => p.key === mission.key);
        if (prev && !prev.completed && mission.completed) {
            haptics.pattern('success');
            if (celebration?.celebrate) {
                celebration.celebrate('mission-complete', {
                    title: '¡Misión completada!',
                    message: mission.title || 'Un paso más hacia tu objetivo',
                });
            }
            break;
        }
    }
    lastSnapshot = next.map(m => ({ key: m.key, completed: !!m.completed }));
}, { deep: true });
</script>

<template>
  <div v-if="missions && missions.length > 0">
    <div class="mb-3 flex items-center justify-between">
      <h2 class="text-lg font-semibold text-wc-text">Misiones diarias</h2>
      <span class="text-sm text-wc-text-secondary">
        {{ completedCount }}/{{ missions.length }} completadas
      </span>
    </div>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
      <RouterLink
        v-for="(mission, idx) in missions"
        :key="mission.key || idx"
        :to="getMissionRoute(mission)"
        @click="handleMissionTap"
        :class="[
          'group flex items-center gap-3 rounded-xl border p-4 transition-colors',
          getMissionStatusClass(mission.completed)
        ]"
      >
        <!-- Status icon -->
        <div :class="[
          'flex h-9 w-9 shrink-0 items-center justify-center rounded-full',
          mission.completed ? 'bg-emerald-500/15' : 'border-2 border-wc-border'
        ]">
          <!-- Completed checkmark -->
          <svg v-if="mission.completed" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
          </svg>
          <!-- Type-specific icons for incomplete missions -->
          <template v-else>
            <!-- Dumbbell icon for training -->
            <svg v-if="mission.key === 'training' || mission.icon === 'dumbbell'" class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
            <!-- Check-in icon -->
            <svg v-else-if="mission.key === 'checkin' || mission.icon === 'checkin'" class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <!-- Scale icon for weight -->
            <svg v-else-if="mission.key === 'weight' || mission.icon === 'scale'" class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.589-1.202L18.75 4.97Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.589-1.202L5.25 4.97Z" />
            </svg>
            <!-- Nutrition/book icon -->
            <svg v-else class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
            </svg>
          </template>
        </div>

        <!-- Text -->
        <div class="min-w-0 flex-1">
          <p :class="['text-base font-medium leading-tight', mission.completed ? 'text-emerald-600 dark:text-emerald-400' : 'text-wc-text']">
            {{ mission.title }}
          </p>
          <p class="mt-0.5 text-sm text-wc-text-secondary">
            {{ mission.completed ? 'Completada' : 'Pendiente' }}
          </p>
        </div>

        <!-- Arrow (incomplete only) -->
        <svg v-if="!mission.completed" class="h-4 w-4 shrink-0 text-wc-text-tertiary group-hover:text-wc-text transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
      </RouterLink>
    </div>
  </div>
</template>
