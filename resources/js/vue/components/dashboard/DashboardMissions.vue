<script setup>
import { computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useHaptics } from '../../composables/useHaptics';
import { useCelebration } from '../../composables/useCelebration';

const { t } = useI18n();

const props = defineProps({
    missions: { type: Array, default: () => [] },
    peerCounts: { type: Object, default: () => ({}) },
    // shape: { [mission.key]: number } — peers del grupo haciendo la misma mision
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
                    title: t('client_home.missions_celebration_title'),
                    message: mission.title || t('client_home.missions_celebration_subtitle'),
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
        <span class="card-title">{{ t('client_home.missions_title') }}</span>
      </div>
      <span class="card-meta tnum">{{ t('client_home.missions_completed_today', { done: completedCount, total: totalCount }) }}</span>
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
          <div class="mission-name">
            {{ mission.title }}
            <span v-if="peerCounts[mission.key] > 0" class="ms-peer-pill">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5Z"></path>
              </svg>
              <span class="tnum">{{ peerCounts[mission.key] }}</span>
              {{ t('client_home.missions_with_you') }}
            </span>
          </div>
          <div class="mission-status">
            {{ mission.completed ? t('client_home.missions_done') : t('client_home.missions_pending') }} · +{{ getMissionXp(mission) }} XP
          </div>
        </div>
        <svg class="mission-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Peer count pill — sigue el mismo pattern visual de .chip-accent en wc-shell.css */
.ms-peer-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 9px 3px 7px;
    border-radius: var(--r-pill);
    background: rgba(220, 38, 38, 0.12);
    color: #FCA5A5;
    font: 600 10px/1 var(--fs);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    box-shadow:
        inset 0 0 0 1px rgba(220, 38, 38, 0.30),
        inset 0 1px 0 rgba(255, 255, 255, 0.05);
    margin-left: var(--s8);
    vertical-align: middle;
}
.ms-peer-pill svg {
    color: var(--wc-accent);
    flex-shrink: 0;
}
</style>
