<script setup>
/**
 * ExerciseCard.vue — Wrapper que compone Head + Media + LastSessionStrip + sets + voice + rest btn.
 *
 * Estados: 'active' (expanded en curso), 'upcoming' (compact pre/queue), 'done' (collapsed).
 * Maneja superset/circuito block label.
 *
 * GIFs/Media (UI premium, fiel a V1 + mejorado):
 * - upcoming: hero card con GIF 96px + gradient overlay + numero grande + play badge
 * - active: botón "Ver ejercicio" expande GIF cinematic + play overlay → YouTube embed
 * - done: thumb mini 48px con check verde overlay + stats en línea
 */
import { computed, ref } from 'vue';
import ExerciseCardHead from './ExerciseCardHead.vue';
import LastSessionStrip from './LastSessionStrip.vue';
import SetRow from './SetRow.vue';
import VoiceCTA from './VoiceCTA.vue';
import { getEmbedUrl } from '../../composables/useExerciseMedia';

// Track imgs que fallaron — usado para mostrar fallback
const brokenImgs = ref(new Set());
function onImgError(key) {
    brokenImgs.value.add(key);
}

const props = defineProps({
    exerciseIndex:     { type: Number, required: true },
    exercise:          { type: Object, required: true },
    sets:              { type: Array, default: () => [] },
    weightUnit:        { type: String, default: 'kg' },
    state:             { type: String, default: 'upcoming' },
    blockType:         { type: String, default: 'normal' },
    isFirstInBlock:    { type: Boolean, default: false },
    isVariationActive: { type: Boolean, default: false },
    notesExpanded:     { type: Boolean, default: false },
    showActiveMedia:   { type: Boolean, default: false },
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
function exMuscle(ex)    { return ex?.musculo || ex?.muscle_group || null; }

function displayImage(ex) {
    if (props.isVariationActive && ex?.variacion?.gif_url) return ex.variacion.gif_url;
    return exImageUrl(ex);
}
function displayName(ex) {
    if (props.isVariationActive && ex?.variacion?.nombre) return ex.variacion.nombre;
    return exName(ex);
}

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

const hasMedia        = computed(() => !!exImageUrl(props.exercise) || !!exVideoUrl(props.exercise));
const hasNotes        = computed(() => !!exNotas(props.exercise));
const hasVariation    = computed(() => !!props.exercise?.variacion?.nombre);
const isCardio        = computed(() => exIsCardio(props.exercise));
const thumbnailUrl    = computed(() => displayImage(props.exercise));
const videoEmbedUrl   = computed(() => getEmbedUrl(exVideoUrl(props.exercise)));
const hasYoutubeVideo = computed(() => !!videoEmbedUrl.value);

const youtubePlaying = ref(false);
function startYoutubePlay() {
    if (hasYoutubeVideo.value) youtubePlaying.value = true;
}
function stopYoutube() {
    youtubePlaying.value = false;
}
function onToggleActiveMedia() {
    if (props.showActiveMedia) youtubePlaying.value = false;
    emit('toggle-active-media');
}

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

const exerciseCounter = computed(() => String(props.exerciseIndex + 1).padStart(2, '0'));

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
function onVoiceStart() { emit('voice-start', props.exerciseIndex); }
function onVoiceStop()  { emit('voice-stop'); }
function onVoiceConfirm() { emit('voice-confirm'); }
function onVoiceCancel()  { emit('voice-cancel'); }
function onOpenMediaModal() { emit('open-media'); }
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

    <!-- ════════════════ DONE — compact + thumb mini ════════════════ -->
    <div v-if="state === 'done'" class="done-summary" @click="onOpenMediaModal" role="button" tabindex="0" @keydown.enter="onOpenMediaModal">
      <div class="done-thumb-wrap">
        <img
          v-if="thumbnailUrl && !brokenImgs.has('done')"
          :src="thumbnailUrl"
          :alt="displayName(exercise)"
          class="done-thumb"
          loading="lazy"
          @error="onImgError('done')"
        />
        <div v-else class="done-thumb done-thumb--fallback">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4l3 3M18 20l-3-3M14 4h4v4M10 20H6v-4"/></svg>
        </div>
        <div class="done-check-overlay">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
      </div>
      <div class="done-body">
        <div class="done-top-line">
          <span class="done-counter">{{ exerciseCounter }}</span>
          <span class="done-status">Completado</span>
        </div>
        <div class="done-name">{{ displayName(exercise) }}</div>
        <div class="done-stats">
          <span class="done-stat"><strong>{{ sets.length }}</strong> sets</span>
          <span class="done-stat-sep" aria-hidden="true">·</span>
          <span class="done-stat"><strong>{{ totalReps }}</strong> reps</span>
          <template v-if="maxWeight > 0">
            <span class="done-stat-sep" aria-hidden="true">·</span>
            <span class="done-stat done-stat--accent"><strong>{{ maxWeight }}</strong>{{ weightUnit }}</span>
          </template>
        </div>
      </div>
    </div>

    <!-- ════════════════ UPCOMING — hero card con GIF 96px ════════════════ -->
    <article
      v-else-if="state === 'upcoming'"
      class="upcoming-row"
      @click="onOpenMediaModal"
      role="button"
      tabindex="0"
      @keydown.enter="onOpenMediaModal"
    >
      <!-- Thumbnail GIF column con gradient overlay -->
      <div class="thumb-col">
        <img
          v-if="thumbnailUrl && !brokenImgs.has('upcoming')"
          :src="thumbnailUrl"
          :alt="displayName(exercise)"
          class="thumb-img"
          loading="lazy"
          @error="onImgError('upcoming')"
        />
        <div v-else class="thumb-fallback">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 4l3 3M18 20l-3-3M14 4h4v4M10 20H6v-4M21 3l-7 7M3 21l7-7"/>
          </svg>
        </div>
        <!-- Gradient overlay para legibilidad del número -->
        <div class="thumb-gradient" aria-hidden="true"></div>
        <!-- Número grande del ejercicio -->
        <span class="thumb-num">{{ exerciseCounter }}</span>
        <!-- Badge play si tiene video -->
        <span v-if="exVideoUrl(exercise)" class="thumb-play" aria-label="Tiene video">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
        </span>
        <!-- Badge variación activa -->
        <span v-if="isVariationActive && exercise.variacion" class="thumb-variation" aria-label="Usando variación">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>
          </svg>
        </span>
      </div>

      <div class="up-body">
        <div class="up-name">{{ displayName(exercise) }}</div>
        <div class="up-meta">
          <span class="up-meta-item">
            <span class="up-meta-v">
              <template v-if="isCardio">{{ exercise.repeticiones || exercise.reps || '—' }}</template>
              <template v-else>{{ exercise.series || exercise.sets || '—' }}×{{ exercise.repeticiones || exercise.reps || '—' }}</template>
            </span>
          </span>
          <span class="up-meta-sep" aria-hidden="true"></span>
          <span class="up-meta-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            {{ exDescanso(exercise) }}
          </span>
        </div>
        <span v-if="exMuscle(exercise)" class="up-muscle">{{ exMuscle(exercise) }}</span>
      </div>

      <span class="up-chev" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="9 18 15 12 9 6"/>
        </svg>
      </span>
    </article>

    <!-- ════════════════ ACTIVE — full ════════════════ -->
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
        @media-open="onOpenMediaModal"
        @variation-toggle="$emit('variation-toggle')"
      />

      <!-- Coach notes -->
      <div v-if="notesExpanded && hasNotes" class="notes-block">
        <span class="notes-label">Nota del coach</span>
        <p>{{ exNotas(exercise) }}</p>
      </div>

      <!-- Media section: toggle button + inline GIF/YouTube cinematic -->
      <div v-if="hasMedia" class="media-section">
        <button
          type="button"
          class="media-toggle-btn"
          :class="{ 'media-toggle-btn--active': showActiveMedia }"
          @click="onToggleActiveMedia"
        >
          <span class="media-toggle-icon">
            <svg v-if="!showActiveMedia" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><line x1="6" y1="12" x2="18" y2="12"/></svg>
          </span>
          <span>{{ showActiveMedia ? 'Ocultar demostración' : 'Ver demostración' }}</span>
          <span class="media-toggle-hint" v-if="!showActiveMedia && hasYoutubeVideo">GIF + Video</span>
          <span class="media-toggle-hint" v-else-if="!showActiveMedia">GIF</span>
        </button>

        <Transition name="media-fade">
          <div
            v-if="showActiveMedia"
            class="active-media"
            :class="{ 'active-media--variation': isVariationActive && exercise.variacion }"
          >
            <!-- YouTube embed (cuando se hizo click play) -->
            <div v-if="youtubePlaying && hasYoutubeVideo" class="youtube-embed">
              <iframe
                :src="videoEmbedUrl + '&autoplay=1'"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen
              ></iframe>
              <button type="button" class="yt-close" @click="stopYoutube" aria-label="Cerrar video y volver al GIF">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
              </button>
            </div>

            <!-- GIF cinemático con play overlay -->
            <div
              v-else-if="thumbnailUrl && !brokenImgs.has('active')"
              class="gif-wrap"
              :class="{ 'gif-wrap--clickable': hasYoutubeVideo }"
              @click="startYoutubePlay"
            >
              <img
                :src="thumbnailUrl"
                :alt="displayName(exercise)"
                class="gif-img"
                loading="lazy"
                @error="onImgError('active')"
              />
              <!-- Edge gradients para look cinematográfico -->
              <div class="gif-edge-top" aria-hidden="true"></div>
              <div class="gif-edge-bottom" aria-hidden="true"></div>
              <!-- Play overlay si tiene YouTube video -->
              <div v-if="hasYoutubeVideo" class="gif-overlay">
                <div class="gif-play-btn">
                  <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                </div>
                <span class="gif-overlay-label">Reproducir video</span>
              </div>
              <!-- Variación badge inline -->
              <span v-if="isVariationActive && exercise.variacion" class="gif-variation-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>
                </svg>
                Variación
              </span>
            </div>

            <!-- Solo video sin GIF -->
            <div v-else-if="exVideoUrl(exercise)" class="gif-wrap gif-wrap--video-only" @click="onOpenMediaModal">
              <div class="gif-play-btn gif-play-btn--lg">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
              </div>
              <span class="gif-overlay-label">Ver video</span>
            </div>
          </div>
        </Transition>
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

      <!-- Voice CTA + manual rest btn -->
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
/* ═══════════════ Container base ═══════════════ */
.ex-card {
  background: var(--color-wc-bg-tertiary);
  border: 1px solid var(--color-wc-border);
  border-radius: 16px;
  overflow: hidden;
  position: relative;
  transition: border-color 0.3s var(--ease-out), transform 0.2s var(--ease-out);
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
.ex-card--upcoming { padding: 0; }
.ex-card--upcoming:hover {
  border-color: var(--color-wc-border-strong);
  transform: translateY(-1px);
}
.ex-card--done {
  opacity: 0.85;
  border-color: rgba(16,185,129,0.20);
  background: linear-gradient(180deg, rgba(16,185,129,0.04), var(--color-wc-bg-tertiary));
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

/* ═══════════════ DONE state ═══════════════ */
.done-summary {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 16px;
  cursor: pointer;
  position: relative;
}
.done-summary:hover { background: rgba(16,185,129,0.04); }
.done-summary:focus-visible { outline: 2px solid #10B981; outline-offset: -2px; }

.done-thumb-wrap {
  position: relative;
  width: 52px;
  height: 52px;
  flex-shrink: 0;
  border-radius: 12px;
  overflow: hidden;
  background: var(--color-wc-bg-secondary);
  border: 1px solid rgba(16,185,129,0.24);
}
.done-thumb {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  filter: grayscale(0.4) brightness(0.7);
}
.done-thumb--fallback {
  display: grid;
  place-items: center;
  color: rgba(16,185,129,0.6);
}
.done-thumb--fallback svg { width: 22px; height: 22px; }

.done-check-overlay {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  background: rgba(16,185,129,0.4);
  color: white;
  pointer-events: none;
}
.done-check-overlay svg { width: 22px; height: 22px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); }

.done-body { flex: 1; min-width: 0; }
.done-top-line {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 2px;
}
.done-counter {
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 700;
  background: rgba(16,185,129,0.16);
  color: #10B981;
  width: 22px;
  height: 22px;
  border-radius: 6px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}
.done-status {
  font-family: var(--font-display);
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: #10B981;
}

.done-name {
  font-family: var(--font-display);
  font-weight: 500;
  font-size: 15px;
  line-height: 1.2;
  text-transform: uppercase;
  color: var(--color-wc-text-secondary);
  letter-spacing: 0.01em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.done-stats {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 4px;
  font-size: 12px;
  color: var(--color-wc-text-tertiary);
  font-variant-numeric: tabular-nums;
  flex-wrap: wrap;
}
.done-stat strong { color: var(--color-wc-text-secondary); font-weight: 600; }
.done-stat--accent strong { color: #10B981; }
.done-stat-sep { color: var(--color-wc-text-tertiary); opacity: 0.5; }

/* ═══════════════ UPCOMING state ═══════════════ */
.upcoming-row {
  display: flex;
  align-items: stretch;
  cursor: pointer;
  background: var(--color-wc-bg-tertiary);
  position: relative;
  min-height: 96px;
}
.upcoming-row:focus-visible { outline: 2px solid var(--color-wc-accent); outline-offset: -2px; }

.thumb-col {
  position: relative;
  width: 96px;
  flex-shrink: 0;
  background: var(--color-wc-bg-secondary);
  overflow: hidden;
}
.thumb-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.4s var(--ease-out);
}
.upcoming-row:hover .thumb-img { transform: scale(1.05); }
.thumb-fallback {
  width: 100%;
  height: 100%;
  display: grid;
  place-items: center;
  background: linear-gradient(135deg, var(--color-wc-bg-secondary), var(--color-wc-bg));
  color: var(--color-wc-text-tertiary);
  opacity: 0.5;
}
.thumb-fallback svg { width: 32px; height: 32px; }

.thumb-gradient {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(180deg, transparent 0%, transparent 50%, rgba(0,0,0,0.55) 100%),
    linear-gradient(90deg, rgba(0,0,0,0.20) 0%, transparent 50%);
  pointer-events: none;
}

.thumb-num {
  position: absolute;
  bottom: 8px;
  left: 8px;
  display: grid;
  place-items: center;
  min-width: 28px;
  height: 24px;
  padding: 0 6px;
  background: var(--color-wc-accent, #DC2626);
  border: 1px solid rgba(255,255,255,0.18);
  border-radius: 8px;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 13px;
  color: white;
  line-height: 1;
  letter-spacing: 0.04em;
  box-shadow: 0 2px 8px -2px rgba(220,38,38,0.6);
}

.thumb-play {
  position: absolute;
  top: 8px;
  right: 8px;
  display: grid;
  place-items: center;
  width: 22px;
  height: 22px;
  background: rgba(220,38,38,0.95);
  border: 1px solid rgba(255,255,255,0.18);
  border-radius: 999px;
  color: white;
  box-shadow: 0 2px 6px rgba(0,0,0,0.4);
}
.thumb-play svg { width: 10px; height: 10px; margin-left: 1px; }

.thumb-variation {
  position: absolute;
  top: 8px;
  left: 8px;
  display: grid;
  place-items: center;
  width: 22px;
  height: 22px;
  background: rgba(167,139,250,0.95);
  border-radius: 6px;
  color: #1a1230;
  box-shadow: 0 2px 6px rgba(0,0,0,0.4);
}
.thumb-variation svg { width: 11px; height: 11px; }

.up-body {
  flex: 1;
  min-width: 0;
  padding: 14px 12px 12px 14px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 4px;
}
.up-name {
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 15px;
  line-height: 1.2;
  text-transform: uppercase;
  letter-spacing: 0.01em;
  color: var(--color-wc-text);
  text-wrap: balance;
  word-break: break-word;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.up-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: var(--color-wc-text-tertiary);
  font-variant-numeric: tabular-nums;
  flex-wrap: wrap;
}
.up-meta-item {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.up-meta-item svg { width: 12px; height: 12px; opacity: 0.6; }
.up-meta-v {
  font-family: var(--font-display);
  font-weight: 600;
  color: var(--color-wc-text-secondary);
  font-size: 13px;
}
.up-meta-sep {
  width: 3px;
  height: 3px;
  border-radius: 999px;
  background: var(--color-wc-text-tertiary);
  opacity: 0.4;
}

.up-muscle {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 999px;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--color-wc-border);
  font-size: 10px;
  font-weight: 500;
  color: var(--color-wc-text-tertiary);
  letter-spacing: 0.04em;
  text-transform: uppercase;
  align-self: flex-start;
  margin-top: 2px;
}

.up-chev {
  display: grid;
  place-items: center;
  padding: 0 14px;
  color: var(--color-wc-text-tertiary);
  flex-shrink: 0;
  transition: color 0.15s, transform 0.2s var(--ease-out);
}
.upcoming-row:hover .up-chev { color: var(--color-wc-accent-glow); transform: translateX(2px); }
.up-chev svg { width: 18px; height: 18px; }

/* ═══════════════ ACTIVE state ═══════════════ */
.notes-block {
  margin: 0 16px 12px;
  padding: 12px 14px;
  border-radius: 12px;
  background:
    linear-gradient(135deg, rgba(245,158,11,0.06), rgba(245,158,11,0.02)),
    rgba(255,255,255,0.02);
  border: 1px solid rgba(245,158,11,0.18);
  position: relative;
}
@media (min-width: 1024px) { .notes-block { margin: 0 22px 14px; } }
.notes-label {
  font-family: var(--font-display);
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: #F59E0B;
  display: block;
  margin-bottom: 6px;
}
.notes-block p {
  margin: 0;
  font-size: 13px;
  line-height: 1.55;
  color: var(--color-wc-text-secondary);
}

/* ═══ Media section premium ═══ */
.media-section {
  margin: 0 16px 12px;
}
@media (min-width: 1024px) { .media-section { margin: 0 22px 14px; } }

.media-toggle-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  border-radius: 12px;
  background:
    linear-gradient(135deg, rgba(220,38,38,0.06), transparent 80%),
    rgba(255,255,255,0.02);
  border: 1px solid var(--color-wc-border);
  color: var(--color-wc-text-secondary);
  font-family: var(--font-display);
  font-size: 12px;
  font-weight: 500;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
  min-height: 40px;
  transition: all 0.15s var(--ease-out);
  position: relative;
}
.media-toggle-btn:hover {
  color: var(--color-wc-text);
  border-color: rgba(220,38,38,0.30);
  background:
    linear-gradient(135deg, rgba(220,38,38,0.10), transparent 80%),
    rgba(255,255,255,0.04);
}
.media-toggle-btn--active {
  background: rgba(220,38,38,0.12);
  border-color: rgba(220,38,38,0.30);
  color: var(--color-wc-accent-glow, #EF4444);
}

.media-toggle-icon {
  display: grid;
  place-items: center;
  width: 18px;
  height: 18px;
  border-radius: 999px;
  background: var(--color-wc-accent, #DC2626);
  color: white;
  flex-shrink: 0;
}
.media-toggle-icon svg { width: 10px; height: 10px; }
.media-toggle-icon svg path[d*="V"] { transform: translateX(0.5px); }

.media-toggle-hint {
  margin-left: auto;
  padding: 2px 8px;
  border-radius: 999px;
  background: rgba(255,255,255,0.04);
  font-size: 9px;
  font-weight: 600;
  color: var(--color-wc-text-tertiary);
  letter-spacing: 0.14em;
}

.active-media {
  margin-top: 12px;
  position: relative;
  border-radius: 16px;
  overflow: hidden;
  background: #050507;
  border: 1px solid var(--color-wc-border-strong);
  box-shadow: 0 12px 32px -12px rgba(0,0,0,0.6);
}
.active-media--variation {
  border-color: rgba(167,139,250,0.40);
  box-shadow: 0 0 0 1px rgba(167,139,250,0.20), 0 12px 32px -12px rgba(167,139,250,0.30);
}

.youtube-embed {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 9;
  background: black;
}
.youtube-embed iframe {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  border: 0;
}
.yt-close {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 10;
  width: 36px;
  height: 36px;
  border-radius: 999px;
  background: rgba(0,0,0,0.7);
  border: 1px solid rgba(255,255,255,0.20);
  color: white;
  display: grid;
  place-items: center;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  transition: all 0.15s var(--ease-out);
}
.yt-close:hover { background: rgba(0,0,0,0.9); transform: scale(1.05); }
.yt-close svg { width: 16px; height: 16px; }

.gif-wrap {
  position: relative;
  width: 100%;
  display: block;
  background: var(--color-wc-bg);
  min-height: 180px;
  overflow: hidden;
}
.gif-wrap--clickable { cursor: pointer; }

.gif-img {
  width: 100%;
  max-height: 360px;
  object-fit: contain;
  display: block;
  background: #050507;
}

/* Edge gradients cinematográficos */
.gif-edge-top, .gif-edge-bottom {
  position: absolute;
  left: 0;
  right: 0;
  height: 32px;
  pointer-events: none;
}
.gif-edge-top {
  top: 0;
  background: linear-gradient(180deg, rgba(0,0,0,0.5), transparent);
}
.gif-edge-bottom {
  bottom: 0;
  background: linear-gradient(0deg, rgba(0,0,0,0.6), transparent);
}

.gif-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  background: rgba(0,0,0,0.30);
  transition: background 0.25s var(--ease-out);
  pointer-events: none;
}
.gif-wrap--clickable:hover .gif-overlay {
  background: rgba(0,0,0,0.45);
}
.gif-wrap--clickable:hover .gif-play-btn {
  transform: scale(1.10);
  box-shadow: 0 12px 40px -4px rgba(220,38,38,0.7);
}

.gif-play-btn {
  width: 64px;
  height: 64px;
  border-radius: 999px;
  background: linear-gradient(135deg, #DC2626, #991B1B);
  display: grid;
  place-items: center;
  color: white;
  box-shadow: 0 8px 32px -4px rgba(220,38,38,0.55), inset 0 1px 0 rgba(255,255,255,0.2);
  transition: all 0.25s var(--ease-out);
}
.gif-play-btn svg { width: 26px; height: 26px; margin-left: 4px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); }
.gif-play-btn--lg { width: 72px; height: 72px; }
.gif-play-btn--lg svg { width: 30px; height: 30px; }

.gif-overlay-label {
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.20em;
  text-transform: uppercase;
  color: white;
  text-shadow: 0 2px 12px rgba(0,0,0,0.8);
  padding: 4px 12px;
  background: rgba(0,0,0,0.30);
  border-radius: 999px;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

.gif-variation-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px;
  background: rgba(167,139,250,0.95);
  border-radius: 999px;
  color: #1a1230;
  font-family: var(--font-display);
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  z-index: 5;
}
.gif-variation-badge svg { width: 11px; height: 11px; }

.gif-wrap--video-only {
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 14px;
  padding: 56px 20px;
  background:
    radial-gradient(circle at 50% 50%, rgba(220,38,38,0.10), transparent 70%),
    linear-gradient(180deg, var(--color-wc-bg-secondary), #050507);
  min-height: 200px;
}
.gif-wrap--video-only .gif-overlay-label {
  color: var(--color-wc-text);
  background: transparent;
  text-shadow: none;
  backdrop-filter: none;
}

/* Transición fluida */
.media-fade-enter-active, .media-fade-leave-active {
  transition: opacity 0.25s var(--ease-out), max-height 0.35s var(--ease-out);
  overflow: hidden;
}
.media-fade-enter-from, .media-fade-leave-to {
  opacity: 0;
  max-height: 0;
  margin-top: 0;
}
.media-fade-enter-to, .media-fade-leave-from {
  opacity: 1;
  max-height: 500px;
}

/* ═══ Strip + sets + footer ═══ */
.strip-wrap { padding: 0 16px; margin-top: 6px; }
@media (min-width: 1024px) { .strip-wrap { padding: 0 22px; } }

.sets {
  margin-top: 12px;
  padding: 0 10px 10px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
@media (min-width: 1024px) { .sets { padding: 0 16px 14px; } }

.ex-footer {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 0 10px 12px;
}
@media (min-width: 1024px) { .ex-footer { padding: 0 16px 14px; } }

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
  -webkit-tap-highlight-color: transparent;
}
.rest-manual:hover { background: rgba(255,255,255,0.04); color: var(--color-wc-text); }
.rest-manual svg { width: 14px; height: 14px; }

/* ═══════════════ Mobile responsive <380px ═══════════════ */
@media (max-width: 380px) {
  .upcoming-row { min-height: 84px; }
  .thumb-col { width: 84px; }
  .thumb-num { min-width: 24px; height: 22px; font-size: 12px; bottom: 6px; left: 6px; padding: 0 4px; }
  .thumb-play, .thumb-variation { width: 18px; height: 18px; top: 6px; }
  .thumb-play { right: 6px; }
  .thumb-variation { left: 6px; }
  .thumb-play svg, .thumb-variation svg { width: 9px; height: 9px; }
  .up-body { padding: 10px 8px 10px 12px; }
  .up-name { font-size: 14px; }
  .up-meta { font-size: 11px; gap: 6px; }
  .up-meta-v { font-size: 12px; }
  .up-chev { padding: 0 10px; }
  .up-chev svg { width: 16px; height: 16px; }

  .done-summary { padding: 10px 12px; gap: 10px; }
  .done-thumb-wrap { width: 44px; height: 44px; }
  .done-thumb--fallback svg, .done-check-overlay svg { width: 18px; height: 18px; }
  .done-name { font-size: 13px; }
  .done-stats { font-size: 11px; }

  .media-section { margin: 0 14px 12px; }
  .media-toggle-btn { padding: 9px 12px; font-size: 11px; min-height: 36px; }
  .media-toggle-icon { width: 16px; height: 16px; }
  .gif-img { max-height: 260px; }
  .gif-play-btn { width: 56px; height: 56px; }
  .gif-play-btn svg { width: 22px; height: 22px; }
  .gif-overlay-label { font-size: 10px; letter-spacing: 0.16em; }
  .yt-close { width: 32px; height: 32px; top: 8px; right: 8px; }

  .notes-block { margin: 0 14px 12px; padding: 12px; }
}

@media (prefers-reduced-motion: reduce) {
  .ex-card, .upcoming-row .thumb-img, .up-chev,
  .gif-overlay, .gif-play-btn, .yt-close { transition: none !important; }
  .upcoming-row:hover .thumb-img { transform: none; }
}
</style>
