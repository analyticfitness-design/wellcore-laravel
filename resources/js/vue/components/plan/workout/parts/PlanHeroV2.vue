<template>
  <section class="hero" data-testid="plan-hero-v2">
    <div class="hero-pad">
      <div class="hero-eyebrow">
        {{ t('client_plan.v2_hero_eyebrow', { current: currentWeek, total: totalWeeks }) }}
      </div>
      <h1 class="hero-title">
        {{ t('client_plan.v2_hero_title_prefix') }}<br><span class="accent">{{ planTypeUpper }}</span>
      </h1>
      <div class="hero-meta-row">
        <span>{{ totalWeeks }} {{ totalWeeks === 1 ? t('client_plan.v2_hero_weeks_one') : t('client_plan.v2_hero_weeks_other') }}</span>
        <template v-if="volLabel"><span class="dot"></span><span>{{ volLabel }}</span></template>
        <template v-if="diasSemana"><span class="dot"></span><span>{{ t('client_plan.v2_hero_days_per_week', { n: diasSemana }) }}</span></template>
      </div>

      <div class="macro-bar-wrap">
        <div class="macro-bar" :style="{ gridTemplateColumns: `repeat(${totalWeeks}, 1fr)` }">
          <div
            v-for="i in totalWeeks"
            :key="i"
            class="macro-tick"
            :class="weekClass(i)"
          >
            <span class="tk-num">{{ String(i).padStart(2, '0') }}</span>
          </div>
        </div>
        <div class="macro-legend">
          <span><span class="sw sw-acc"></span>{{ t('client_plan.v2_hero_legend_today') }}</span>
          <span><span class="sw sw-future"></span>{{ t('client_plan.v2_hero_legend_upcoming') }}</span>
          <span v-if="hasDeloadWeek"><span class="sw sw-deload"></span>{{ t('client_plan.v2_hero_legend_deload') }}</span>
        </div>
      </div>
    </div>

    <div v-if="hasStats" class="hero-stats">
      <div v-if="stats.volumen" class="hstat">
        <div class="k">{{ t('client_plan.v2_hero_stat_volume') }}</div>
        <div class="v">{{ stats.volumen.value }} <small>{{ stats.volumen.label }}</small></div>
      </div>
      <div v-if="stats.frecuencia" class="hstat">
        <div class="k">{{ t('client_plan.v2_hero_stat_frequency') }}</div>
        <div class="v">{{ stats.frecuencia.value }} <small>{{ stats.frecuencia.label }}</small></div>
      </div>
      <div v-if="stats.rir" class="hstat">
        <div class="k">{{ t('client_plan.v2_hero_stat_rir') }}</div>
        <div class="v">{{ stats.rir.value }} <small>{{ stats.rir.label }}</small></div>
      </div>
    </div>
  </section>
</template>

<script setup>
// PlanHeroV2 — hero cinematográfico con macro-tick bar (semanas) + stats grid.
// CSS lines 211-321 del HTML V2.1.
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  planType: { type: String, default: '' },          // ej "elite"
  currentWeek: { type: Number, default: 1 },
  totalWeeks: { type: Number, default: 4 },
  diasSemana: { type: Number, default: null },
  volumenLabel: { type: String, default: '' },      // "Vol. alto" derivado server
  totalSeriesSemana: { type: Number, default: null },
  rirObjetivo: { type: String, default: '' },
  semanas: { type: Array, default: () => [] },
});

const planTypeUpper = computed(() => {
  const v = (props.planType || '').toString().trim().toUpperCase();
  return v || t('client_plan.v2_hero_default_plan_label');
});

const volLabel = computed(() => (props.volumenLabel || '').toUpperCase());

const hasDeloadWeek = computed(() => {
  return (props.semanas || []).some((s) => {
    const f = String(s?.fase || '').toLowerCase();
    return f === 'deload' || f === 'descarga';
  });
});

function weekClass(weekNumber) {
  // weekNumber 1-based
  const sem = (props.semanas || [])[weekNumber - 1];
  const fase = String(sem?.fase || '').toLowerCase();
  if (weekNumber === props.currentWeek) return 'active';
  if (weekNumber < props.currentWeek) return 'done';
  if (fase === 'deload' || fase === 'descarga') return 'deload';
  return 'future';
}

const stats = computed(() => {
  const out = {};
  if (props.totalSeriesSemana) {
    out.volumen = { value: props.totalSeriesSemana, label: t('client_plan.v2_hero_stat_volume_label') };
  }
  if (props.diasSemana) {
    out.frecuencia = {
      value: props.diasSemana,
      label: props.diasSemana === 1
        ? t('client_plan.v2_hero_stat_frequency_day_one')
        : t('client_plan.v2_hero_stat_frequency_day_other'),
    };
  }
  if (props.rirObjetivo) {
    out.rir = { value: props.rirObjetivo, label: t('client_plan.v2_hero_stat_rir_label') };
  }
  return out;
});

const hasStats = computed(() => Object.keys(stats.value).length > 0);
</script>

<style scoped>
.hero {
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  background:
    radial-gradient(ellipse 70% 80% at 100% 0%, rgba(220,38,38,0.32), transparent 60%),
    radial-gradient(ellipse 60% 60% at 0% 100%, rgba(127,29,29,0.30), transparent 70%),
    linear-gradient(180deg, #1a0707 0%, #0c0606 100%);
  border: 1px solid rgba(220,38,38,0.20);
  isolation: isolate;
}
.hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: repeating-linear-gradient(90deg, rgba(255,255,255,0.04) 0 1px, transparent 1px 80px);
  opacity: 0.7;
  pointer-events: none;
  mix-blend-mode: overlay;
}
.hero::after {
  content: '';
  position: absolute;
  inset: auto 0 0 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, #EF4444 40%, #EF4444 60%, transparent);
  opacity: 0.6;
}
.hero-pad {
  padding: 22px 18px 18px;
}
.hero-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-family: 'JetBrains Mono', ui-monospace, monospace;
  font-size: 11px;
  letter-spacing: 0.20em;
  text-transform: uppercase;
  color: #EF4444;
}
.hero-eyebrow::before {
  content: '';
  width: 6px;
  height: 6px;
  background: #DC2626;
  border-radius: 999px;
  box-shadow: 0 0 10px #EF4444;
}
.hero-title {
  font-family: 'Oswald', Impact, sans-serif;
  font-weight: 700;
  letter-spacing: 0.02em;
  line-height: 0.92;
  text-transform: uppercase;
  color: #FAFAFA;
  text-shadow: 0 2px 30px rgba(220,38,38,0.40);
  font-size: 64px;
  margin: 12px 0 14px;
}
.hero-title .accent {
  color: transparent;
  -webkit-text-stroke: 1.5px rgba(250,250,250,0.30);
}
.hero-meta-row {
  display: flex;
  align-items: center;
  gap: 10px;
  font-family: 'JetBrains Mono', ui-monospace, monospace;
  font-size: 11px;
  letter-spacing: 0.10em;
  color: rgba(250,250,250,0.56);
  flex-wrap: wrap;
}
.hero-meta-row .dot {
  width: 3px;
  height: 3px;
  background: rgba(250,250,250,0.40);
  border-radius: 999px;
  flex-shrink: 0;
}

.macro-bar-wrap {
  margin-top: 18px;
}
.macro-bar {
  position: relative;
  height: 28px;
  display: grid;
  gap: 3px;
}
.macro-tick {
  position: relative;
  background: rgba(255,255,255,0.06);
  border-radius: 3px;
  overflow: hidden;
}
.macro-tick.done { background: #DC2626; }
.macro-tick.active {
  background: linear-gradient(180deg, #EF4444, #DC2626);
  box-shadow: 0 0 0 1px rgba(255,255,255,0.30) inset, 0 0 12px rgba(239,68,68,0.6);
}
.macro-tick.future { background: rgba(255,255,255,0.06); }
.macro-tick.deload {
  background: repeating-linear-gradient(45deg, rgba(255,255,255,0.10) 0 4px, rgba(255,255,255,0.04) 4px 8px);
}
.macro-tick .tk-num {
  position: absolute;
  bottom: 4px;
  left: 0;
  right: 0;
  text-align: center;
  font-family: 'JetBrains Mono', ui-monospace, monospace;
  font-size: 9px;
  color: rgba(0,0,0,0.55);
  font-weight: 600;
}
.macro-tick.future .tk-num,
.macro-tick.deload .tk-num {
  color: rgba(250,250,250,0.40);
}
.macro-tick.active .tk-num {
  color: rgba(255,255,255,0.95);
}
.macro-tick.done .tk-num {
  color: rgba(255,255,255,0.85);
}

.macro-legend {
  display: flex;
  gap: 14px;
  margin-top: 10px;
  font-family: 'JetBrains Mono', ui-monospace, monospace;
  font-size: 10px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: rgba(250,250,250,0.40);
  flex-wrap: wrap;
}
.macro-legend span {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.macro-legend .sw {
  width: 10px;
  height: 10px;
  border-radius: 2px;
}
.sw-acc { background: #EF4444; }
.sw-future { background: rgba(255,255,255,0.10); }
.sw-deload {
  background: repeating-linear-gradient(45deg, rgba(255,255,255,0.30) 0 3px, rgba(255,255,255,0.10) 3px 6px);
}

.hero-stats {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  border-top: 1px solid rgba(255,255,255,0.06);
}
.hstat {
  padding: 14px 16px;
  border-right: 1px solid rgba(255,255,255,0.06);
}
.hstat:last-child { border-right: 0; }
.hstat .k {
  font-family: 'JetBrains Mono', ui-monospace, monospace;
  font-size: 10px;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: rgba(250,250,250,0.40);
}
.hstat .v {
  font-family: 'Oswald', Impact, sans-serif;
  font-weight: 600;
  font-size: 22px;
  letter-spacing: 0.02em;
  color: #FAFAFA;
  margin-top: 2px;
}
.hstat .v small {
  font-family: 'JetBrains Mono', ui-monospace, monospace;
  font-size: 11px;
  color: rgba(250,250,250,0.56);
  margin-left: 4px;
  font-weight: 400;
}

@media (max-width: 600px) {
  .hero-title { font-size: 42px; }
  .hero-pad { padding: 22px 18px 18px; }
  .hstat { padding: 12px 14px; }
  .hstat .v { font-size: 18px; }
}
</style>
