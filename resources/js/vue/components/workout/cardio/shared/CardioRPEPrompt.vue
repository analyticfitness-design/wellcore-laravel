<script setup>
/**
 * CardioRPEPrompt.vue
 *
 * Modal post-cardio que pide al cliente reportar RPE (1-10) y notas opcionales.
 * Es el sustituto del pulsómetro: proxy de intensidad subjetivo y validado en literatura (Borg scale).
 *
 * Props:
 *   - show: Boolean (controla visibilidad)
 *   - cardioType: string (para mostrar contexto al cliente)
 *   - expectedRpe: string ("4-5") guía del coach
 *
 * Emits:
 *   - @submit({rpe, notes})
 *   - @skip       cuando el cliente cierra sin reportar
 */
import { ref, watch } from 'vue';

const props = defineProps({
    show:        { type: Boolean, default: false },
    cardioType:  { type: String, default: 'free' },
    expectedRpe: { type: String, default: '' },
});

const emit = defineEmits(['submit', 'skip']);

const rpe = ref(null);
const notes = ref('');

watch(() => props.show, (v) => {
    if (v) { rpe.value = null; notes.value = ''; }
});

const scaleLabels = [
    { value: 1,  emoji: '😌', label: 'Muy fácil',       hint: 'Como caminar' },
    { value: 2,  emoji: '🙂', label: 'Fácil',           hint: 'Podía hablar largo' },
    { value: 3,  emoji: '🙂', label: 'Cómodo',          hint: 'Conversación normal' },
    { value: 4,  emoji: '😐', label: 'Moderado',        hint: 'Frases cortas' },
    { value: 5,  emoji: '😐', label: 'Algo difícil',    hint: 'Respiración notable' },
    { value: 6,  emoji: '😤', label: 'Difícil',         hint: 'Pocas palabras' },
    { value: 7,  emoji: '😤', label: 'Muy difícil',     hint: 'Sin hablar' },
    { value: 8,  emoji: '🥵', label: 'Casi máximo',     hint: 'Cerca del límite' },
    { value: 9,  emoji: '🥵', label: 'Máximo',          hint: 'Casi no podía más' },
    { value: 10, emoji: '🥵', label: 'Imposible',       hint: 'No podía continuar' },
];

function submit() {
    if (rpe.value === null) return;
    emit('submit', { rpe: rpe.value, notes: notes.value.trim() || null });
}

function skip() {
    emit('skip');
}
</script>

<template>
  <Teleport to="body">
    <div v-if="show" class="rpe-prompt-overlay" @click.self="skip">
      <div class="rpe-prompt" role="dialog" aria-labelledby="rpe-title">
        <header class="rpe-prompt__header">
          <h3 id="rpe-title" class="rpe-prompt__title">¿Cómo te sentiste?</h3>
          <p class="rpe-prompt__sub">Calificá la intensidad real del cardio del 1 al 10.</p>
          <p v-if="expectedRpe" class="rpe-prompt__hint">Tu coach esperaba RPE {{ expectedRpe }}.</p>
        </header>

        <div class="rpe-prompt__scale">
          <button
            v-for="opt in scaleLabels"
            :key="opt.value"
            type="button"
            class="rpe-prompt__opt"
            :class="{ 'rpe-prompt__opt--active': rpe === opt.value }"
            @click="rpe = opt.value"
          >
            <span class="rpe-prompt__opt-emoji">{{ opt.emoji }}</span>
            <span class="rpe-prompt__opt-val">{{ opt.value }}</span>
            <span class="rpe-prompt__opt-label">{{ opt.label }}</span>
            <span class="rpe-prompt__opt-hint">{{ opt.hint }}</span>
          </button>
        </div>

        <textarea
          v-model="notes"
          class="rpe-prompt__notes"
          placeholder="Notas opcionales (cómo te sentiste, qué te costó, qué cambiaste)…"
          rows="2"
        ></textarea>

        <footer class="rpe-prompt__footer">
          <button type="button" class="rpe-prompt__btn rpe-prompt__btn--ghost" @click="skip">
            Saltar
          </button>
          <button
            type="button"
            class="rpe-prompt__btn rpe-prompt__btn--primary"
            :disabled="rpe === null"
            @click="submit"
          >
            Guardar
          </button>
        </footer>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.rpe-prompt-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  align-items: flex-end;
  justify-content: center;
  z-index: 1000;
  padding: 16px;
}
@media (min-width: 640px) {
  .rpe-prompt-overlay { align-items: center; }
}
.rpe-prompt {
  background: rgb(15, 23, 42);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px 16px 0 0;
  padding: 20px;
  width: 100%;
  max-width: 480px;
  max-height: 90vh;
  overflow-y: auto;
}
@media (min-width: 640px) {
  .rpe-prompt { border-radius: 16px; }
}
.rpe-prompt__header { margin-bottom: 16px; }
.rpe-prompt__title {
  font-family: 'Oswald', system-ui, sans-serif;
  font-size: 24px;
  font-weight: 700;
  color: white;
  margin: 0 0 4px 0;
}
.rpe-prompt__sub {
  font-size: 14px;
  color: rgb(156, 163, 175);
  margin: 0 0 4px 0;
}
.rpe-prompt__hint {
  font-size: 12px;
  color: rgb(220, 38, 38);
  margin: 0;
}
.rpe-prompt__scale {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 6px;
  margin-bottom: 12px;
}
@media (min-width: 480px) {
  .rpe-prompt__scale { grid-template-columns: repeat(10, 1fr); }
}
.rpe-prompt__opt {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  padding: 8px 4px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.03);
  cursor: pointer;
  min-height: 64px;
  color: rgb(229, 231, 235);
  transition: all 0.15s;
}
.rpe-prompt__opt:hover { background: rgba(255, 255, 255, 0.08); }
.rpe-prompt__opt--active {
  background: rgba(220, 38, 38, 0.2);
  border-color: rgb(220, 38, 38);
}
.rpe-prompt__opt-emoji { font-size: 18px; }
.rpe-prompt__opt-val   { font-weight: 700; font-size: 14px; }
.rpe-prompt__opt-label { font-size: 9px; text-align: center; opacity: 0.8; }
.rpe-prompt__opt-hint  { font-size: 8px; text-align: center; opacity: 0.5; display: none; }
@media (min-width: 480px) {
  .rpe-prompt__opt-hint { display: block; }
}
.rpe-prompt__notes {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.03);
  color: rgb(229, 231, 235);
  font-size: 14px;
  resize: vertical;
  margin-bottom: 12px;
}
.rpe-prompt__footer {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}
.rpe-prompt__btn {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  border: 1px solid rgba(255, 255, 255, 0.15);
  background: rgba(255, 255, 255, 0.05);
  color: rgb(229, 231, 235);
  cursor: pointer;
  min-height: 44px;
}
.rpe-prompt__btn--primary {
  background: rgb(220, 38, 38);
  border-color: rgb(220, 38, 38);
  color: white;
}
.rpe-prompt__btn--primary:disabled {
  background: rgba(220, 38, 38, 0.3);
  cursor: not-allowed;
}
.rpe-prompt__btn--ghost {
  background: transparent;
  color: rgb(156, 163, 175);
}
</style>
