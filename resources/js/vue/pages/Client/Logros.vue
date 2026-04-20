<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import ClientLayout from '../../layouts/ClientLayout.vue';
import MedalHex from '../../components/MedalHex.vue';
import MedalUnlockCelebration from '../../components/MedalUnlockCelebration.vue';
import { useMedals } from '../../composables/useMedals';

const {
    medals,
    stats,
    loading,
    error,
    newMedal,
    unlockedCount,
    totalCount,
    fetchMedals,
    clearNewMedal,
} = useMedals();

// ── Tier filter ──────────────────────────────────────────────────────────────
const TIERS = [
    { key: 'todas', label: 'Todas' },
    { key: 'bronce', label: 'Bronce' },
    { key: 'plata', label: 'Plata' },
    { key: 'oro', label: 'Oro' },
    { key: 'platino', label: 'Platino' },
    { key: 'legendario', label: 'Legendario' },
];

const tierFilter = ref('todas');
const statusFilter = ref('todas'); // todas | logradas | progreso | bloqueadas

const filteredMedals = computed(() => {
    let list = medals.value;

    if (tierFilter.value !== 'todas') {
        list = list.filter((m) => m.tier === tierFilter.value);
    }

    if (statusFilter.value === 'logradas') {
        list = list.filter((m) => m.achieved);
    } else if (statusFilter.value === 'bloqueadas') {
        list = list.filter((m) => !m.achieved);
    } else if (statusFilter.value === 'progreso') {
        list = list.filter(
            (m) => !m.achieved && m.progress && m.progress.current > 0,
        );
    }

    return list;
});

// ── Detail bottom-sheet ──────────────────────────────────────────────────────
const selectedMedal = ref(null);

function openDetail(medal) {
    selectedMedal.value = medal;
}
function closeDetail() {
    selectedMedal.value = null;
}

function formatDate(iso) {
    if (!iso) return '';
    try {
        return new Date(iso).toLocaleDateString('es-CO', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    } catch (e) {
        return iso;
    }
}

function detailProgressPct(m) {
    if (!m?.progress?.target) return 0;
    return Math.min(100, Math.round((m.progress.current / m.progress.target) * 100));
}

// ── XP bar computed ──────────────────────────────────────────────────────────
const xpProgressPct = computed(() => {
    if (!stats.value) return 0;
    const { xpCurrentLevel, xpNextLevel } = stats.value;
    if (!xpNextLevel) return 100;
    return Math.max(0, Math.min(100, Math.round((xpCurrentLevel / xpNextLevel) * 100)));
});

// ── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(fetchMedals);

// Close the celebration overlay callback — and if the user was watching a
// detail sheet about that same medal, refresh its reference (pivot changed).
function onCelebrationClose() {
    clearNewMedal();
}
</script>

<template>
  <ClientLayout>
    <div class="logros-page space-y-6">

      <!-- Page header -->
      <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="font-display text-3xl uppercase tracking-wide text-wc-text sm:text-4xl">
            Logros
          </h1>
          <p class="mt-1 text-sm text-wc-text-secondary">
            <span v-if="!loading && stats">
              {{ unlockedCount }} de {{ totalCount }} medallas desbloqueadas
            </span>
            <span v-else-if="loading">Cargando medallas...</span>
            <span v-else>Tus medallas de constancia, fuerza y progreso.</span>
          </p>
        </div>

        <!-- Stats chips -->
        <div v-if="stats" class="flex flex-wrap items-center gap-2">
          <div class="rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2">
            <div class="font-display text-xl leading-none text-wc-accent">
              {{ stats.level }}
            </div>
            <div class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">
              Nivel
            </div>
          </div>
          <div class="rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2">
            <div class="font-data text-xl leading-none text-wc-text">
              {{ stats.totalXP?.toLocaleString() ?? 0 }}
            </div>
            <div class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">
              XP total
            </div>
          </div>
          <div class="rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2">
            <div class="font-data text-xl leading-none text-wc-text">
              {{ stats.streak }}
            </div>
            <div class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">
              Racha
            </div>
          </div>
        </div>
      </div>

      <!-- XP bar -->
      <div v-if="stats" class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
        <div class="mb-2 flex items-center justify-between text-xs">
          <span class="font-semibold uppercase tracking-wider text-wc-text-tertiary">
            Nivel {{ stats.level }}
          </span>
          <span class="font-data text-wc-text-secondary">
            {{ stats.xpCurrentLevel?.toLocaleString() ?? 0 }} / {{ stats.xpNextLevel?.toLocaleString() ?? 0 }} XP
          </span>
          <span class="font-semibold uppercase tracking-wider text-wc-text-tertiary">
            Nivel {{ stats.level + 1 }}
          </span>
        </div>
        <div class="h-2 w-full overflow-hidden rounded-full bg-wc-bg-tertiary">
          <div
            class="h-full rounded-full transition-all duration-700"
            :style="{
                width: xpProgressPct + '%',
                background: 'linear-gradient(90deg, #DC2626, #C4D92E)',
            }"
          ></div>
        </div>
      </div>

      <!-- Filters -->
      <div class="space-y-3">
        <!-- Status -->
        <div class="flex flex-wrap gap-2">
          <button
            v-for="opt in [
                { k: 'todas',       l: 'Todas' },
                { k: 'logradas',    l: 'Logradas' },
                { k: 'progreso',    l: 'En progreso' },
                { k: 'bloqueadas',  l: 'Bloqueadas' },
            ]"
            :key="opt.k"
            type="button"
            :class="[
                'rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wider transition-colors',
                statusFilter === opt.k
                    ? 'border-wc-accent bg-wc-accent/10 text-wc-accent'
                    : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text',
            ]"
            @click="statusFilter = opt.k"
          >
            {{ opt.l }}
          </button>
        </div>

        <!-- Tier -->
        <div class="flex flex-wrap gap-2">
          <button
            v-for="t in TIERS"
            :key="t.key"
            type="button"
            :class="[
                'rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-wider transition-colors',
                tierFilter === t.key
                    ? 'border-wc-accent bg-wc-accent/10 text-wc-accent'
                    : 'border-wc-border bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text',
            ]"
            @click="tierFilter = t.key"
          >
            {{ t.label }}
          </button>
        </div>
      </div>

      <!-- Loading -->
      <template v-if="loading">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
          <div
            v-for="n in 10"
            :key="n"
            class="h-48 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"
          ></div>
        </div>
      </template>

      <!-- Error -->
      <div
        v-else-if="error"
        class="rounded-xl border border-red-500/30 bg-red-500/10 p-6 text-center"
      >
        <p class="text-sm text-red-400">{{ error }}</p>
        <button
          type="button"
          class="mt-3 rounded-lg border border-red-500/30 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-red-400 hover:bg-red-500/10"
          @click="fetchMedals"
        >
          Reintentar
        </button>
      </div>

      <!-- Empty -->
      <div
        v-else-if="!filteredMedals.length"
        class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center"
      >
        <p class="text-sm text-wc-text-secondary">
          No hay medallas que coincidan con los filtros.
        </p>
      </div>

      <!-- Grid -->
      <div
        v-else
        class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5"
      >
        <MedalHex
          v-for="medal in filteredMedals"
          :key="medal.id"
          :medal="medal"
          :size="110"
          @click="openDetail"
        />
      </div>
    </div>

    <!-- Bottom-sheet detail -->
    <Transition name="fade">
      <div
        v-if="selectedMedal"
        class="fixed inset-0 z-50 bg-black/70"
        @click.self="closeDetail"
      ></div>
    </Transition>

    <Transition name="slide-up">
      <div
        v-if="selectedMedal"
        class="fixed inset-x-0 bottom-0 z-50 rounded-t-3xl border-t border-wc-border bg-wc-bg-secondary p-6 shadow-2xl sm:left-1/2 sm:right-auto sm:top-1/2 sm:w-full sm:max-w-md sm:-translate-x-1/2 sm:-translate-y-1/2 sm:rounded-3xl sm:border"
      >
        <div class="mx-auto mb-4 h-1 w-12 rounded-full bg-wc-border sm:hidden"></div>

        <div class="flex items-center justify-between gap-4">
          <div>
            <p
              class="text-[10px] font-semibold uppercase tracking-widest"
              :class="selectedMedal.achieved ? 'text-emerald-400' : 'text-wc-text-tertiary'"
            >
              {{ selectedMedal.achieved ? 'Logrado' : 'Bloqueado' }}
              · {{ selectedMedal.tier }}
            </p>
            <h3 class="mt-1 font-display text-2xl uppercase tracking-wide text-wc-text">
              {{ selectedMedal.name }}
            </h3>
          </div>
          <button
            type="button"
            class="rounded-full border border-wc-border p-2 text-wc-text-tertiary hover:text-wc-text"
            aria-label="Cerrar"
            @click="closeDetail"
          >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
        </div>

        <div class="my-4 flex justify-center">
          <MedalHex :medal="selectedMedal" :size="150" :interactive="false" />
        </div>

        <p class="text-sm text-wc-text-secondary">
          {{ selectedMedal.description }}
        </p>

        <div class="mt-4 space-y-3">
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-3">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">
              Requisito
            </p>
            <p class="mt-1 text-sm text-wc-text">{{ selectedMedal.requirement }}</p>
          </div>

          <!-- Progress for locked -->
          <div
            v-if="!selectedMedal.achieved && selectedMedal.progress"
            class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-3"
          >
            <div class="mb-2 flex items-center justify-between text-xs">
              <span class="font-semibold uppercase tracking-wider text-wc-text-tertiary">
                Progreso
              </span>
              <span class="font-data text-wc-text">
                {{ selectedMedal.progress.current }} / {{ selectedMedal.progress.target }}
              </span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-wc-bg">
              <div
                class="h-full rounded-full transition-all duration-700"
                :style="{
                    width: detailProgressPct(selectedMedal) + '%',
                    background: 'linear-gradient(90deg, #DC2626, #F59E0B)',
                }"
              ></div>
            </div>
          </div>

          <!-- Achieved metadata -->
          <div
            v-if="selectedMedal.achieved"
            class="rounded-xl border border-emerald-500/25 bg-emerald-500/5 p-3"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-emerald-400">
                  Desbloqueado
                </p>
                <p class="mt-0.5 text-sm text-wc-text">
                  {{ formatDate(selectedMedal.achievedAt) || '—' }}
                </p>
              </div>
              <div class="rounded-full bg-[#C4D92E] px-3 py-1 text-xs font-bold text-black">
                +{{ selectedMedal.xp }} XP
              </div>
            </div>
          </div>

          <!-- XP reward locked -->
          <div
            v-else
            class="flex items-center justify-between rounded-xl border border-wc-border bg-wc-bg-tertiary p-3"
          >
            <span class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">
              Recompensa
            </span>
            <div class="rounded-full bg-[#C4D92E] px-3 py-1 text-xs font-bold text-black">
              +{{ selectedMedal.xp }} XP
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Celebration overlay -->
    <MedalUnlockCelebration :medal="newMedal" @close="onCelebrationClose" />
  </ClientLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.25s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.slide-up-enter-active, .slide-up-leave-active {
    transition: transform 0.3s ease, opacity 0.3s ease;
}
.slide-up-enter-from, .slide-up-leave-to {
    transform: translateY(100%);
    opacity: 0;
}

@media (min-width: 640px) {
    .slide-up-enter-from, .slide-up-leave-to {
        transform: translate(-50%, calc(-50% + 40px));
        opacity: 0;
    }
}
</style>
