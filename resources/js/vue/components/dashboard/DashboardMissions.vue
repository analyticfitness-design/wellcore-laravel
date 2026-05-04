<script setup>
import { computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useHaptics } from '../../composables/useHaptics';
import { useCelebration } from '../../composables/useCelebration';

const props = defineProps({
    missions: { type: Array, default: () => [] },
});

const router = useRouter();
const haptics = useHaptics();
const celebration = useCelebration();

// Mission route mapping (API returns route keys, we map to Vue routes)
const missionRouteMap = {
    training:  '/client/plan?tab=training',
    checkin:   '/client/checkin',
    weight:    '/client/metrics',
    nutrition: '/client/plan?tab=nutrition',
};

// XP rewards por mission key (frontend-only fallback)
const missionXpMap = {
    training:  50,
    checkin:   30,
    weight:    20,
    nutrition: 20,
};

function getMissionRoute(mission) {
    return missionRouteMap[mission.key] || mission.route || '/client/dashboard';
}

function getMissionXp(mission) {
    return missionXpMap[mission.key] ?? 20;
}

function handleMissionTap(mission) {
    haptics.light();
    router.push(getMissionRoute(mission));
}

const completedCount = computed(() => props.missions.filter(m => m.completed).length);
const totalCount = computed(() => props.missions.length);
const progressPct = computed(() => {
    if (!totalCount.value) return 0;
    return Math.round((completedCount.value / totalCount.value) * 100);
});

// Fase 8: celebrate al completar una misión que antes estaba pending.
let lastSnapshot = [];
watch(() => props.missions, (next) => {
    if (!Array.isArray(next)) return;
    if (lastSnapshot.length === 0) {
        lastSnapshot = next.map(m => ({ key: m.key, completed: !!m.completed }));
        return;
    }
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
  <section v-if="missions && missions.length > 0" class="card section wc-card-dashboard-missions" :style="{ animationDelay: '340ms' }">
    <div class="card-head">
      <div class="card-head-left">
        <span class="card-title">Misiones diarias</span>
      </div>
      <span class="card-meta tnum">{{ completedCount }} / {{ totalCount }} hoy</span>
    </div>
    <div class="missions-progress">
      <div class="mp-bar">
        <div class="mp-fill" :style="{ width: progressPct + '%' }"></div>
      </div>
      <span class="mp-text">{{ progressPct }}%</span>
    </div>
    <div class="missions-list">
      <div
        v-for="(mission, idx) in missions"
        :key="mission.key || idx"
        class="mission"
        :class="{ done: mission.completed }"
        tabindex="0"
        @click="handleMissionTap(mission)"
        @keydown.enter="handleMissionTap(mission)"
      >
        <span class="mission-check"></span>
        <div class="mission-body">
          <div class="mission-name">{{ mission.title }}</div>
          <div class="mission-status">
            {{ mission.completed ? 'Completada' : 'Pendiente' }} · +{{ getMissionXp(mission) }} XP
          </div>
        </div>
        <svg class="mission-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
      </div>
    </div>
  </section>
</template>
