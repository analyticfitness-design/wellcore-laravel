<script setup>
import { ref, computed, onMounted } from 'vue';
import ClientLayout from '../../layouts/ClientLayout.vue';
import MedalHex from '../../components/MedalHex.vue';
import LogrosHero from '../../components/logros/LogrosHero.vue';
import LogroBadgeCard from '../../components/logros/LogroBadgeCard.vue';
import WcStatCard from '../../components/ui/wellcore/WcStatCard.vue';
import { useMedals } from '../../composables/useMedals';

const {
  medals,
  stats,
  loading,
  error,
  unlockedCount,
  totalCount,
  fetchMedals,
} = useMedals();

// ── Filters ──────────────────────────────────────────────────────────────────
const STATUS_OPTS = [
  { k: 'todas',      l: 'Todas' },
  { k: 'logradas',   l: 'Logradas' },
  { k: 'progreso',   l: 'En progreso' },
  { k: 'bloqueadas', l: 'Bloqueadas' },
];

const TIERS = [
  { key: 'todas',      label: 'Todas' },
  { key: 'bronce',     label: 'Bronce' },
  { key: 'plata',      label: 'Plata' },
  { key: 'oro',        label: 'Oro' },
  { key: 'platino',    label: 'Platino' },
  { key: 'legendario', label: 'Legendario' },
];

const tierFilter   = ref('todas');
const statusFilter = ref('todas');

const filteredMedals = computed(() => {
  let list = medals.value;
  if (tierFilter.value !== 'todas') {
    list = list.filter(m => m.tier === tierFilter.value);
  }
  if (statusFilter.value === 'logradas') {
    list = list.filter(m => m.achieved);
  } else if (statusFilter.value === 'bloqueadas') {
    list = list.filter(m => !m.achieved);
  } else if (statusFilter.value === 'progreso') {
    list = list.filter(m => !m.achieved && m.progress && m.progress.current > 0);
  }
  return list;
});

// ── Stats computed ────────────────────────────────────────────────────────────
const xpProgressPct = computed(() => {
  if (!stats.value) return 0;
  const { xpCurrentLevel, xpNextLevel } = stats.value;
  if (!xpNextLevel) return 100;
  return Math.max(0, Math.min(100, Math.round((xpCurrentLevel / xpNextLevel) * 100)));
});

// ── Detail bottom-sheet ───────────────────────────────────────────────────────
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
      day:   '2-digit',
      month: 'short',
      year:  'numeric',
    });
  } catch {
    return iso;
  }
}

function detailProgressPct(m) {
  if (!m?.progress?.target) return 0;
  return Math.min(100, Math.round((m.progress.current / m.progress.target) * 100));
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(fetchMedals);
</script>

<template>
  <ClientLayout>
    <!-- Loading skeleton -->
    <div v-if="loading && !medals.length" class="wc-shell wc-shell--logros">
      <main class="scroll">
        <div class="hero grain section" style="min-height:140px; animation:none; opacity:.4; grid-column:span 12;"></div>
        <div class="stats-grid section" style="animation-delay:180ms; grid-column:span 12;">
          <div v-for="n in 4" :key="n" class="stat-card" style="opacity:.3; min-height:108px;"></div>
        </div>
      </main>
    </div>

    <!-- Error -->
    <div v-else-if="error && !loading" class="wc-shell wc-shell--logros">
      <main class="scroll">
        <section class="card section" style="text-align:center; padding:32px; grid-column:span 12;">
          <p style="color:var(--wc-accent); font-size:14px; margin-bottom:12px;">{{ error }}</p>
          <button class="btn-primary" style="margin:0 auto;" @click="fetchMedals">
            Reintentar
          </button>
        </section>
      </main>
    </div>

    <!-- Content -->
    <div v-else class="wc-shell wc-shell--logros">
      <main class="scroll">

        <!-- §1 Hero -->
        <LogrosHero v-if="stats" :stats="stats" />
        <section v-else class="hero grain section" :style="{ animationDelay: '40ms' }">
          <div class="hero-content">
            <div class="hero-greeting tight"><span class="name">Logros</span></div>
            <p class="hero-sub">Tus medallas de constancia, fuerza y progreso.</p>
          </div>
        </section>

        <!-- §2 Stats -->
        <div
          v-if="stats"
          class="stats-grid section wc-card-logros-stats"
          :style="{ animationDelay: '180ms' }"
        >
          <WcStatCard variant="purple" label="Nivel" :value="stats.level || 1" sub="Tu progresión" :xp-percent="xpProgressPct">
            <template #ghost>
              <svg class="stat-ghost" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
              </svg>
            </template>
          </WcStatCard>
          <WcStatCard variant="amber" label="Logros" :value="`${unlockedCount}/${totalCount}`" sub="desbloqueados">
            <template #ghost>
              <svg class="stat-ghost" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                <path d="M12 15C8.7 15 6 12.3 6 9V3h12v6c0 3.3-2.7 6-6 6z"/><path d="M8.5 15.5A5 5 0 0 0 7 19h10a5 5 0 0 0-1.5-3.5"/><line x1="12" y1="15" x2="12" y2="19"/>
              </svg>
            </template>
          </WcStatCard>
          <WcStatCard variant="red" label="XP total" :value="(stats.totalXP || 0).toLocaleString()" unit="XP" sub="acumulado">
            <template #ghost>
              <svg class="stat-ghost" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                <circle cx="12" cy="12" r="10"/><path d="m8 12 2.5 2.5L16 9"/>
              </svg>
            </template>
          </WcStatCard>
          <WcStatCard variant="green" label="Entrenamientos" :value="stats.totalWorkouts || 0" sub="completados">
            <template #ghost>
              <svg class="stat-ghost" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                <path d="M6 5v14M18 5v14M2 9h4M18 9h4M2 15h4M18 15h4M6 9h12M6 15h12"/>
              </svg>
            </template>
          </WcStatCard>
        </div>

        <!-- §3 Filtros -->
        <div class="card section wc-card-logros-filters" :style="{ animationDelay: '220ms' }">
          <!-- Status filter -->
          <div class="logros-filters">
            <button
              v-for="opt in STATUS_OPTS"
              :key="opt.k"
              type="button"
              :class="['logros-filter', statusFilter === opt.k ? 'is-active' : '']"
              @click="statusFilter = opt.k"
            >
              {{ opt.l }}
            </button>
          </div>
          <!-- Tier filter -->
          <div class="logros-filters" style="padding-top:0;">
            <button
              v-for="t in TIERS"
              :key="t.key"
              type="button"
              :class="['logros-filter', tierFilter === t.key ? 'is-active' : '']"
              @click="tierFilter = t.key"
            >
              {{ t.label }}
            </button>
          </div>
        </div>

        <!-- §4 Grid -->
        <div class="card section wc-card-logros-grid" :style="{ animationDelay: '260ms' }">
          <!-- Empty state -->
          <div v-if="!filteredMedals.length" class="empty">
            <div class="empty-art">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
              </svg>
            </div>
            <p class="empty-title">Sin resultados</p>
            <p class="empty-sub">No hay medallas que coincidan con los filtros.</p>
          </div>

          <!-- Medal grid -->
          <div v-else class="logros-grid">
            <LogroBadgeCard
              v-for="medal in filteredMedals"
              :key="medal.id"
              :medal="medal"
              @click="openDetail"
            />
          </div>
        </div>

      </main>

      <!-- ── Bottom-sheet overlay ─────────────────────────────────────────────── -->
      <Transition name="logros-fade">
        <div
          v-if="selectedMedal"
          class="logros-overlay"
          @click.self="closeDetail"
        ></div>
      </Transition>

      <Transition name="logros-slide">
        <div v-if="selectedMedal" class="logros-sheet">
          <div class="logros-sheet-handle"></div>

          <div class="logros-sheet-header">
            <div>
              <p
                class="logros-sheet-status"
                :style="{ color: selectedMedal.achieved ? 'var(--wc-green)' : 'var(--wc-text-3)' }"
              >
                {{ selectedMedal.achieved ? 'Logrado' : 'Bloqueado' }} · {{ selectedMedal.tier }}
              </p>
              <h3 class="logros-sheet-title">{{ selectedMedal.name }}</h3>
            </div>
            <button type="button" class="logros-sheet-close" aria-label="Cerrar" @click="closeDetail">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6L6 18M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <div class="logros-sheet-art">
            <MedalHex :medal="selectedMedal" :size="150" :interactive="false" />
          </div>

          <p class="logros-sheet-desc">{{ selectedMedal.description }}</p>

          <!-- Requisito -->
          <div class="logros-sheet-block">
            <p class="logros-sheet-block-label">Requisito</p>
            <p class="logros-sheet-block-value">{{ selectedMedal.requirement }}</p>
          </div>

          <!-- Progreso (bloqueado con progreso parcial) -->
          <div v-if="!selectedMedal.achieved && selectedMedal.progress" class="logros-sheet-block">
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <p class="logros-sheet-block-label" style="margin-bottom:0;">Progreso</p>
              <span style="font:600 13px/1 var(--fm); color:var(--wc-text);" class="tnum">
                {{ selectedMedal.progress.current }} / {{ selectedMedal.progress.target }}
              </span>
            </div>
            <div class="logros-sheet-progress-bar" style="margin-top:10px;">
              <div
                class="logros-sheet-progress-fill"
                :style="{ width: detailProgressPct(selectedMedal) + '%' }"
              ></div>
            </div>
          </div>

          <!-- Logrado: fecha + XP -->
          <div v-if="selectedMedal.achieved" class="logros-sheet-block" style="border-color:rgba(16,185,129,.25); background:rgba(16,185,129,.06);">
            <div style="display:flex; align-items:center; justify-content:space-between;">
              <div>
                <p class="logros-sheet-block-label" style="color:var(--wc-green);">Desbloqueado</p>
                <p class="logros-sheet-block-value">{{ formatDate(selectedMedal.achievedAt) || '—' }}</p>
              </div>
              <span class="logros-xp-badge">+{{ selectedMedal.xp }} XP</span>
            </div>
          </div>

          <!-- Bloqueado: recompensa -->
          <div v-else class="logros-sheet-block" style="display:flex; align-items:center; justify-content:space-between;">
            <p class="logros-sheet-block-label" style="margin-bottom:0;">Recompensa</p>
            <span class="logros-xp-badge">+{{ selectedMedal.xp }} XP</span>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Celebraciones están en ClientLayout.vue (globales) -->
  </ClientLayout>
</template>

<style scoped>
.logros-fade-enter-active,
.logros-fade-leave-active {
  transition: opacity 0.25s ease;
}
.logros-fade-enter-from,
.logros-fade-leave-to {
  opacity: 0;
}

.logros-slide-enter-active,
.logros-slide-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.logros-slide-enter-from,
.logros-slide-leave-to {
  transform: translateY(100%);
  opacity: 0;
}

@media (min-width: 640px) {
  .logros-slide-enter-from,
  .logros-slide-leave-to {
    transform: translate(-50%, calc(-50% + 40px));
    opacity: 0;
  }
}
</style>
