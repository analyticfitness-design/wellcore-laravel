<script setup>
/**
 * ExerciseCard.vue — Wrapper que compone Head + LastSessionStrip + sets + voice + rest btn.
 *
 * Estados: 'active' (expanded en curso), 'upcoming' (compact pre/queue), 'done' (collapsed).
 * Maneja superset/circuito block label.
 */
import { computed } from 'vue';
import ExerciseCardHead from './ExerciseCardHead.vue';
import LastSessionStrip from './LastSessionStrip.vue';
import SetRow from './SetRow.vue';
import VoiceCTA from './VoiceCTA.vue';

const props = defineProps({
  exerciseIndex:     { type: Number, required: true },
  exercise:          { type: Object, required: true },
  sets:              { type: Array, default: () => [] },
  weightUnit:        { type: String, default: 'kg' },
  state:             { type: String, default: 'upcoming' }, // 'active'|'upcoming'|'done'
  blockType:         { type: String, default: 'normal' },
  isFirstInBlock:    { type: Boolean, default: false },
  isVariationActive: { type: Boolean, default: false },
  notesExpanded:     { type: Boolean, default: false },
  showActiveMedia:   { type: Boolean, default: false },
  // Voice
  voiceEngine:       { type: Boolean, default: false },
  voiceListening:    { type: Boolean, default: false },
  voiceIsProcessing: { type: Boolean, default: false },
  voiceConfirmation: { type: Object, default: null },
  voiceError:        { type: String, default: '' },
  voiceExIndex:      { type: [Number, null], default: null },
});

const emit = defineEmits([
  'toggle-set', 'complete-cardio-set', 'uncomplete-set', 'update-set-field',
  'open-media', 'toggle-active-media',
  'start-rest', 'voice-start', 'voice-stop', 'voice-confirm', 'voice-cancel',
  'notes-toggle', 'variation-toggle',
]);

// ── Field accessors ──
function exName(ex)      { return ex?.nombre || ex?.name || ex?.ejercicio || 'Ejercicio'; }
function exNotas(ex)     { return ex?.notas || ex?.notes || null; }
function exImageUrl(ex)  { return ex?.image_url || ex?.gif_url || ex?.imagen || ex?.thumbnail_url || null; }
function exVideoUrl(ex)  { return ex?.video_url || ex?.video || null; }
function exIsCardio(ex)  { return !!ex?.is_cardio; }
function exDescanso(ex)  { return ex?.descanso || ex?.rest || ex?.rest_seconds || '90s'; }

function parseRestSeconds(rest) {
  if (!rest) return 90;
  const str = String(rest).trim().toLowerCase();
  let m = str.match(/^(\d+)\s*s(eg)?$/i); if (m) return parseInt(m[1]);
  m = str.match(/^(\d+)\s*min$/i);         if (m) return parseInt(m[1]) * 60;
  m = str.match(/^(\d+):(\d{2})$/);         if (m) return parseInt(m[1]) * 60 + parseInt(m[2]);
  if (!isNaN(str)) return parseInt(str);
  m = str.match(/(\d+)/);
  if (m) {
    const n = parseInt(m[1]);
    return str.includes('min') ? n * 60 : n;
  }
  return 90;
}

const hasMedia = computed(() => !!exImageUrl(props.exercise) || !!exVideoUrl(props.exercise));
const hasNotes = computed(() => !!exNotas(props.exercise));
const hasVariation = computed(() => !!props.exercise?.variacion?.nombre);
const isCardio = computed(() => exIsCardio(props.exercise));

// Last session: prefer extended payload, fallback to legacy fields
const lastSessionData = computed(() => {
  const ex = props.exercise;
  if (ex.last_session) return ex.last_session;
  if (ex.last_weight !== undefined && ex.last_weight !== null) {
    return {
      weight: ex.last_weight,
      reps: ex.last_reps || 0,
      days_ago: 0,
      delta_kg: 0,
      session_id: null,
    };
  }
  return null;
});

const totalReps = computed(() =>
  (props.sets || []).reduce((sum, s) => sum + (parseInt(s.reps) || 0), 0)
);
const maxWeight = computed(() =>
  (props.sets || []).reduce((max, s) => Math.max(max, parseFloat(s.weight) || 0), 0)
);

// Determine which set is "active" (first non-completed in active exercise)
function setState(sIdx) {
  const set = props.sets[sIdx];
  if (!set) return 'pending';
  if (set.completed) return 'completed';
  if (props.state !== 'active') return 'pending';
  const firstIncomplete = props.sets.findIndex(s => !s.completed);
  return sIdx === firstIncomplete ? 'active' : 'pending';
}

const isVoiceActiveHere = computed(() =>
  props.voiceListening && props.voiceExIndex === props.exerciseIndex
);

const showVoiceConfirmation = computed(() =>
  !!props.voiceConfirmation && props.voiceExIndex === props.exerciseIndex
);

const showVoiceError = computed(() =>
  !!props.voiceError && props.voiceExIndex === props.exerciseIndex
);

const blockLabel = computed(() => {
  if (props.blockType === 'superset') return 'SUPERSET';
  if (props.blockType === 'circuito') return 'CIRCUITO';
  return null;
});

// ── Handlers ──
function onUpdateField(sIdx, field, value) {
  emit('update-set-field', { exIndex: props.exerciseIndex, sIdx, field, value });
}
function onSetComplete(sIdx) {
  if (isCardio.value) {
    emit('complete-cardio-set', { exIndex: props.exerciseIndex, sIdx });
  } else {
    emit('toggle-set', { exIndex: props.exerciseIndex, sIdx, type: 'complete' });
  }
}
function onSetUncomplete(sIdx) {
  emit('uncomplete-set', { exIndex: props.exerciseIndex, sIdx });
}
function onRestClick() {
  emit('start-rest', parseRestSeconds(exDescanso(props.exercise)));
}
function onVoiceStart() {
  emit('voice-start', props.exerciseIndex);
}
function onVoiceStop() {
  emit('voice-stop');
}
function onVoiceConfirm() {
  emit('voice-confirm');
}
function onVoiceCancel() {
  emit('voice-cancel');
}

// Resolve YouTube embed thumbnail
function resolveThumbnail(ex) {
  const img = exImageUrl(ex);
  if (img) return img;
  const v = exVideoUrl(ex);
  if (v) {
    const m = v.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|\/embed\/)([a-zA-Z0-9_-]{11})/);
    if (m) return `https://img.youtube.com/vi/${m[1]}/mqdefault.jpg`;
  }
  return null;
}
</script>

<template>
  <article
    class="ex-card"
    :class="['ex-card--' + state, { 'ex-card--in-block': isFirstInBlock }]"
    :data-state="state"
  >
    <!-- Block label -->
    <div v-if="isFirstInBlock && blockLabel" class="block-label">
      <span>{{ blockLabel }}</span>
    </div>

    <!-- DONE state — compact summary -->
    <div v-if="state === 'done'" class="done-summary" @click="$emit('toggle-active-media')">
      <div class="num-tile">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </div>
      <div class="done-body">
        <div class="done-name">{{ exName(exercise) }}</div>
        <div class="done-sub">{{ sets.length }} sets · {{ totalReps }} reps · {{ maxWeight }}{{ weightUnit }}</div>
      </div>
    </div>

    <!-- UPCOMING — compact -->
    <div v-else-if="state === 'upcoming'" class="upcoming-row">
      <div class="num-tile-up">{{ String(exerciseIndex + 1).padStart(2, '0') }}</div>
      <div class="up-body">
        <div class="up-name">{{ exName(exercise) }}</div>
        <div class="up-sub">{{ exercise.series || exercise.sets || '—' }}×{{ exercise.repeticiones || exercise.reps || '—' }} · {{ exercise.descanso || '90s' }}</div>
      </div>
      <span class="up-chev">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="9 18 15 12 9 6"/>
        </svg>
      </span>
    </div>

    <!-- ACTIVE state — full -->
    <template v-else>
      <ExerciseCardHead
        :exercise-index="exerciseIndex"
        :exercise="exercise"
        :is-variation-active="isVariationActive"
        :state="state"
        :has-notes="hasNotes"
        :has-media="hasMedia"
        :has-variation="hasVariation"
        :notes-expanded="notesExpanded"
        @notes-toggle="$emit('notes-toggle')"
        @media-open="$emit('open-media')"
        @variation-toggle="$emit('variation-toggle')"
      />

      <!-- Coach notes (collapsible) -->
      <div v-if="notesExpanded && hasNotes" class="notes-block">
        {{ exNotas(exercise) }}
      </div>

      <!-- Active inline media -->
      <div v-if="showActiveMedia && resolveThumbnail(exercise)" class="active-media">
        <img :src="resolveThumbnail(exercise)" :alt="exName(exercise)" loading="lazy" />
        <button v-if="exVideoUrl(exercise)" type="button" class="media-play" @click="$emit('open-media')">
          <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20"/></svg>
        </button>
      </div>

      <!-- Last session strip -->
      <div v-if="lastSessionData" class="strip-wrap">
        <LastSessionStrip
          :weight="lastSessionData.weight"
          :reps="lastSessionData.reps"
          :days-ago="lastSessionData.days_ago || 0"
          :weight-delta="lastSessionData.delta_kg || 0"
          :weight-unit="weightUnit"
        />
      </div>

      <!-- Set rows -->
      <div class="sets">
        <SetRow
          v-for="(set, sIdx) in sets"
          :key="`s-${exerciseIndex}-${sIdx}`"
          :set-index="sIdx"
          :set-number="sIdx + 1"
          :state="setState(sIdx)"
          :weight="set.weight"
          :reps="set.reps"
          :target-weight="set.target_weight"
          :target-reps="set.target_reps"
          :weight-unit="weightUnit"
          :is-pr="set.is_pr"
          :is-cardio="isCardio"
          :duration="set.duration"
          :speed="set.speed"
          :incline="set.incline"
          :disabled="set._saving"
          @update:weight="(v) => onUpdateField(sIdx, 'weight', v)"
          @update:reps="(v) => onUpdateField(sIdx, 'reps', v)"
          @update:duration="(v) => onUpdateField(sIdx, 'duration', v)"
          @update:speed="(v) => onUpdateField(sIdx, 'speed', v)"
          @update:incline="(v) => onUpdateField(sIdx, 'incline', v)"
          @complete="onSetComplete(sIdx)"
          @uncomplete="onSetUncomplete(sIdx)"
        />
      </div>

      <!-- Voice CTA + manual rest btn (strength only) -->
      <div v-if="!isCardio && voiceEngine" class="ex-footer">
        <VoiceCTA
          :listening="isVoiceActiveHere"
          :is-processing="voiceIsProcessing"
          :confirmation="showVoiceConfirmation ? voiceConfirmation : null"
          :error="showVoiceError ? voiceError : ''"
          :weight-unit="weightUnit"
          @start="onVoiceStart"
          @stop="onVoiceStop"
          @confirm="onVoiceConfirm"
          @cancel="onVoiceCancel"
        />
        <button type="button" class="rest-manual" @click="onRestClick">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/>
          </svg>
          <span>Iniciar descanso ({{ exDescanso(exercise) }})</span>
        </button>
      </div>
      <!-- Cardio: solo botón rest (no voice) -->
      <div v-else-if="isCardio" class="ex-footer">
        <button type="button" class="rest-manual" @click="onRestClick">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/>
          </svg>
          <span>Iniciar descanso ({{ exDescanso(exercise) }})</span>
        </button>
      </div>
    </template>
  </article>
</template>

<style scoped>
.ex-card {
  background: var(--color-wc-bg-tertiary);
  border: 1px solid var(--color-wc-border);
  border-radius: 20px;
  overflow: hidden;
  position: relative;
  transition: border-color 0.3s var(--ease-out);
}
.ex-card--active {
  background:
    linear-gradient(180deg, rgba(220,38,38,0.06), transparent 40%),
    var(--color-wc-bg-tertiary);
  border-color: rgba(239,68,68,0.30);
  box-shadow:
    0 1px 0 rgba(255,255,255,0.04) inset,
    0 20px 60px -30px rgba(220,38,38,0.45);
}

.block-label {
  padding: 8px 16px;
  background: rgba(220,38,38,0.10);
  border-bottom: 1px solid rgba(220,38,38,0.20);
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--color-wc-accent-glow, #EF4444);
}

/* DONE state */
.done-summary {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 18px;
  cursor: pointer;
  opacity: 0.85;
}
.done-summary .num-tile {
  width: 44px; height: 44px;
  border-radius: 12px;
  background: rgba(16,185,129,0.10);
  border: 1px solid rgba(16,185,129,0.22);
  color: #10B981;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}
.done-summary .num-tile svg { width: 22px; height: 22px; }
.done-name {
  font-family: var(--font-display);
  font-weight: 500;
  font-size: 15px;
  text-transform: uppercase;
  color: var(--color-wc-text-secondary);
  letter-spacing: 0.01em;
}
.done-sub {
  font-size: 12px;
  color: var(--color-wc-text-tertiary);
  margin-top: 4px;
  font-variant-numeric: tabular-nums;
}

/* UPCOMING state */
.upcoming-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
  cursor: pointer;
}
.num-tile-up {
  width: 44px; height: 44px;
  border-radius: 12px;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--color-wc-border);
  display: grid;
  place-items: center;
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 18px;
  color: var(--color-wc-text-secondary);
  flex-shrink: 0;
}
.up-body { flex: 1; min-width: 0; }
.up-name {
  font-family: var(--font-display);
  font-weight: 500;
  font-size: 16px;
  line-height: 1.15;
  text-transform: uppercase;
  letter-spacing: 0.01em;
  color: var(--color-wc-text);
}
.up-sub {
  font-size: 12px;
  color: var(--color-wc-text-secondary);
  margin-top: 4px;
  font-variant-numeric: tabular-nums;
}
.up-chev { color: var(--color-wc-text-tertiary); flex-shrink: 0; }
.up-chev svg { width: 18px; height: 18px; }

/* ACTIVE state */
.notes-block {
  margin: 0 20px 12px;
  padding: 12px 14px;
  border-radius: 12px;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--color-wc-border);
  color: var(--color-wc-text-secondary);
  font-size: 13px;
  line-height: 1.5;
}
@media (min-width: 1024px) { .notes-block { margin: 0 28px 14px; } }

.active-media {
  margin: 0 20px 12px;
  position: relative;
  border-radius: 14px;
  overflow: hidden;
  background: var(--color-wc-bg-secondary);
  aspect-ratio: 16 / 10;
}
@media (min-width: 1024px) { .active-media { margin: 0 28px 14px; } }
.active-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
.media-play {
  position: absolute;
  inset: 0;
  margin: auto;
  width: 56px; height: 56px;
  border-radius: 999px;
  background: rgba(220,38,38,0.9);
  color: white;
  border: none;
  display: grid;
  place-items: center;
  cursor: pointer;
}
.media-play svg { width: 22px; height: 22px; margin-left: 3px; }

.strip-wrap { padding: 0 20px; margin-top: 6px; }
@media (min-width: 1024px) { .strip-wrap { padding: 0 28px; } }

.sets {
  margin-top: 16px;
  padding: 0 12px 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
@media (min-width: 1024px) { .sets { padding: 0 20px 16px; } }

.ex-footer {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 0 12px 14px;
}
@media (min-width: 1024px) { .ex-footer { padding: 0 20px 16px; } }

.rest-manual {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  height: 48px;
  padding: 0 16px;
  border-radius: 14px;
  background: transparent;
  border: 1px solid var(--color-wc-border);
  color: var(--color-wc-text-secondary);
  font-family: var(--font-display);
  font-size: 12px;
  font-weight: 500;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  cursor: pointer;
}
.rest-manual:hover { background: rgba(255,255,255,0.04); color: var(--color-wc-text); }
.rest-manual svg { width: 14px; height: 14px; }
</style>
