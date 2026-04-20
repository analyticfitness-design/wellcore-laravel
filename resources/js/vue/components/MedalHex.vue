<script setup>
import { computed } from 'vue';

const props = defineProps({
    medal: { type: Object, required: true },
    size: { type: Number, default: 110 }, // px
    interactive: { type: Boolean, default: true },
});

const emit = defineEmits(['click']);

const TIER_EMOJI = {
    bronce: 'B3',
    plata: 'AG',
    oro: 'AU',
    platino: 'PT',
    legendario: 'LG',
};

const TIER_LABEL = {
    bronce: 'Bronce',
    plata: 'Plata',
    oro: 'Oro',
    platino: 'Platino',
    legendario: 'Legendario',
};

const stripeBackground = computed(() => {
    const colors = props.medal.stripeColors;
    if (!Array.isArray(colors) || colors.length < 3) return null;
    const [c1, c2, c3] = colors;
    return `repeating-linear-gradient(
        148deg,
        ${c1} 0px, ${c1} 22px,
        #141416 22px, #141416 25px,
        ${c2} 25px, ${c2} 47px,
        #141416 47px, #141416 50px,
        ${c3} 50px, ${c3} 72px,
        #141416 72px, #141416 75px
    )`;
});

const progressPct = computed(() => {
    const p = props.medal.progress;
    if (!p || !p.target) return 0;
    return Math.min(100, Math.round((p.current / p.target) * 100));
});

const tierClass = computed(() => `tier-${props.medal.tier}`);

function handleClick() {
    if (props.interactive) emit('click', props.medal);
}
</script>

<template>
  <button
    type="button"
    class="medal-card group"
    :class="[
      tierClass,
      medal.achieved ? 'is-achieved' : 'is-locked',
      medal.tier === 'legendario' ? 'is-legendario' : '',
      !interactive ? 'pointer-events-none' : '',
    ]"
    @click="handleClick"
  >
    <!-- Hex wrapper -->
    <div
      class="hex-wrap"
      :style="{ width: size + 'px', height: size + 'px' }"
    >
      <!-- Legendary orbit particles (only for legendarios + achieved) -->
      <template v-if="medal.achieved && medal.tier === 'legendario'">
        <span class="orbit orbit-1"></span>
        <span class="orbit orbit-2"></span>
        <span class="orbit orbit-3"></span>
      </template>

      <!-- Hex body -->
      <div class="hex" :class="{ 'hex-locked': !medal.achieved }">
        <!-- Stripes (only if achieved) -->
        <div
          v-if="medal.achieved && stripeBackground"
          class="hex-stripes"
          :style="{ background: stripeBackground }"
        ></div>

        <!-- Top highlight overlay (metal shine) -->
        <div v-if="medal.achieved" class="hex-overlay-top"></div>

        <!-- Shimmer sweep (only if achieved) -->
        <div v-if="medal.achieved" class="hex-shimmer"></div>

        <!-- Icon / label inside hex -->
        <span
          class="hex-icon"
          :class="{ 'hex-icon-locked': !medal.achieved }"
        >{{ medal.iconLabel || '★' }}</span>

        <!-- Tier badge (top-left) -->
        <span class="tier-badge" :title="TIER_LABEL[medal.tier]">
          {{ TIER_EMOJI[medal.tier] }}
        </span>

        <!-- Achieved check (top-right) -->
        <span v-if="medal.achieved" class="achieved-check" aria-label="Logrado">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12" />
          </svg>
        </span>
      </div>

      <!-- XP pill at bottom-right overlap -->
      <span class="xp-pill">{{ medal.xp }} XP</span>
    </div>

    <!-- Name -->
    <div class="medal-name">{{ medal.name }}</div>

    <!-- Progress bar (locked + has progress) -->
    <div v-if="!medal.achieved && medal.progress" class="progress-wrap">
      <div class="progress-bar">
        <div
          class="progress-fill"
          :style="{ width: progressPct + '%' }"
        ></div>
      </div>
      <span class="progress-label">
        {{ medal.progress.current }}/{{ medal.progress.target }}
      </span>
    </div>

    <!-- Tooltip on hover for locked -->
    <div
      v-if="!medal.achieved"
      class="locked-tooltip"
    >
      {{ medal.requirement }}
    </div>
  </button>
</template>

<style scoped>
/* ── Tier tokens ─────────────────────────────────────────────────────────── */
.tier-bronce     { --metal: #8B5A2B; --glow: transparent;             --shimmer-op: 0.30; }
.tier-plata      { --metal: #C0C0C0; --glow: rgba(192,192,192,0.30);  --shimmer-op: 0.60; }
.tier-oro        { --metal: #FFD700; --glow: rgba(255,215,0,0.45);    --shimmer-op: 0.90; }
.tier-platino    { --metal: #00FFFF; --glow: rgba(0,255,255,0.55);    --shimmer-op: 1.00; }
.tier-legendario { --metal: #DC2626; --glow: rgba(220,38,38,0.75);    --shimmer-op: 1.00; }

/* ── Card shell ──────────────────────────────────────────────────────────── */
.medal-card {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 16px 10px 14px;
    border-radius: 14px;
    background: #111113;
    border: 1px solid rgba(255,255,255,0.06);
    text-align: center;
    cursor: pointer;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    color: #FAFAFA;
    font-family: 'Raleway', 'Inter', system-ui, sans-serif;
}
.medal-card:hover {
    transform: translateY(-2px);
    border-color: rgba(220, 38, 38, 0.35);
}
.medal-card.is-legendario {
    border-color: rgba(220, 38, 38, 0.4);
    box-shadow: 0 0 32px rgba(220, 38, 38, 0.18);
}

/* ── Hex wrap / shape ────────────────────────────────────────────────────── */
.hex-wrap {
    position: relative;
    display: grid;
    place-items: center;
}
.hex {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
    clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
    background: #1C1C1E;
    box-shadow:
        inset 0 2px 8px rgba(255,255,255,0.10),
        inset 0 -2px 8px rgba(0,0,0,0.55),
        0 0 24px var(--glow);
}
.hex-locked {
    background: #1a1a1a;
    box-shadow:
        inset 0 0 0 1px rgba(220,38,38,0.18),
        inset 0 2px 4px rgba(0,0,0,0.6);
}

.hex-stripes {
    position: absolute;
    inset: 0;
    z-index: 1;
}
.hex-overlay-top {
    position: absolute;
    inset: 0;
    z-index: 2;
    background: linear-gradient(180deg, rgba(255,255,255,0.22) 0%, transparent 55%);
    pointer-events: none;
}

/* Shimmer barrido diagonal */
.hex-shimmer {
    position: absolute;
    inset: 0;
    z-index: 3;
    pointer-events: none;
    overflow: hidden;
}
.hex-shimmer::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -80%;
    width: 60%;
    height: 200%;
    background: linear-gradient(
        45deg,
        transparent 0%,
        rgba(255,255,255,0) 40%,
        rgba(255,255,255,0.55) 50%,
        rgba(255,255,255,0) 60%,
        transparent 100%
    );
    opacity: var(--shimmer-op, 0.6);
    transform: translateX(-100%) rotate(8deg);
    animation: hex-shimmer-sweep 3.2s ease-in-out infinite;
}
@keyframes hex-shimmer-sweep {
    0%   { transform: translateX(-100%) rotate(8deg); }
    60%  { transform: translateX(320%) rotate(8deg); }
    100% { transform: translateX(320%) rotate(8deg); }
}

/* Icon */
.hex-icon {
    position: absolute;
    inset: 0;
    display: grid;
    place-items: center;
    z-index: 4;
    font-family: 'Oswald', 'Bebas Neue', 'Barlow', sans-serif;
    font-weight: 700;
    font-size: 26px;
    letter-spacing: 0.04em;
    color: #FAFAFA;
    text-shadow: 0 1px 4px rgba(0,0,0,0.75);
}
.hex-icon-locked {
    color: transparent;
    -webkit-text-stroke: 1.5px rgba(180, 180, 180, 0.25);
    opacity: 0.55;
}
.medal-card.is-locked:hover .hex-icon-locked {
    opacity: 0.8;
    -webkit-text-stroke-color: rgba(220, 38, 38, 0.55);
}

/* Tier & check badges */
.tier-badge {
    position: absolute;
    top: 6px;
    left: 6px;
    z-index: 5;
    font-family: 'Oswald', 'Bebas Neue', sans-serif;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: var(--metal);
    background: rgba(0,0,0,0.55);
    padding: 2px 5px;
    border-radius: 3px;
    border: 1px solid rgba(255,255,255,0.08);
    filter: drop-shadow(0 1px 2px rgba(0,0,0,0.6));
    text-transform: uppercase;
}
.achieved-check {
    position: absolute;
    top: 6px;
    right: 6px;
    z-index: 5;
    width: 18px;
    height: 18px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: #DC2626;
    color: #FFFFFF;
    box-shadow: 0 2px 6px rgba(0,0,0,0.5);
}
.achieved-check svg {
    width: 11px;
    height: 11px;
}

/* Pulse red for legendarios */
.is-legendario.is-achieved .hex {
    animation: pulse-red 2.4s ease-in-out infinite;
}
@keyframes pulse-red {
    0%, 100% { box-shadow:
        inset 0 2px 8px rgba(255,255,255,0.10),
        inset 0 -2px 8px rgba(0,0,0,0.55),
        0 0 20px rgba(220,38,38,0.55); }
    50%      { box-shadow:
        inset 0 2px 8px rgba(255,255,255,0.10),
        inset 0 -2px 8px rgba(0,0,0,0.55),
        0 0 36px rgba(220,38,38,0.95); }
}

/* Orbit particles (legendario only) */
.orbit {
    position: absolute;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #DC2626;
    box-shadow: 0 0 8px rgba(220,38,38,0.9);
    top: 50%;
    left: 50%;
    margin: -3px 0 0 -3px;
    z-index: 6;
    pointer-events: none;
}
.orbit-1 { animation: orbit-spin 3.2s linear infinite; }
.orbit-2 { animation: orbit-spin 3.2s linear infinite 1.07s; }
.orbit-3 { animation: orbit-spin 3.2s linear infinite 2.13s; }
@keyframes orbit-spin {
    from { transform: rotate(0deg)   translateX(65px) rotate(0deg); }
    to   { transform: rotate(360deg) translateX(65px) rotate(-360deg); }
}

/* XP pill */
.xp-pill {
    position: absolute;
    bottom: -6px;
    right: -4px;
    z-index: 7;
    background: #C4D92E;
    color: #000000;
    font-family: 'JetBrains Mono', ui-monospace, monospace;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 100px;
    letter-spacing: 0.02em;
    box-shadow: 0 2px 8px rgba(0,0,0,0.6);
    border: 1px solid rgba(0,0,0,0.3);
}

/* Name */
.medal-name {
    font-family: 'Oswald', 'Bebas Neue', sans-serif;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    line-height: 1.2;
    padding: 0 4px;
    min-height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.is-locked .medal-name {
    color: rgba(250,250,250,0.55);
}

/* Progress */
.progress-wrap {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
    padding: 0 2px;
}
.progress-bar {
    width: 100%;
    height: 4px;
    background: rgba(255,255,255,0.08);
    border-radius: 2px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #DC2626, #F59E0B);
    border-radius: 2px;
    transition: width 0.6s ease;
}
.progress-label {
    font-family: 'JetBrains Mono', ui-monospace, monospace;
    font-size: 9px;
    color: rgba(250,250,250,0.45);
    letter-spacing: 0.02em;
}

/* Locked tooltip (hover only, desktop) */
.locked-tooltip {
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translate(-50%, 100%);
    background: #18181B;
    border: 1px solid rgba(220,38,38,0.3);
    color: #FAFAFA;
    font-size: 10px;
    font-family: 'Raleway', sans-serif;
    padding: 6px 10px;
    border-radius: 8px;
    white-space: nowrap;
    max-width: 220px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
    z-index: 20;
    box-shadow: 0 8px 24px rgba(0,0,0,0.6);
}
.medal-card.is-locked:hover .locked-tooltip {
    opacity: 1;
}

/* Reduced motion — respetar siempre */
@media (prefers-reduced-motion: reduce) {
    .hex-shimmer::before,
    .orbit-1, .orbit-2, .orbit-3,
    .is-legendario.is-achieved .hex {
        animation: none !important;
    }
    .progress-fill {
        transition: none;
    }
}
</style>
