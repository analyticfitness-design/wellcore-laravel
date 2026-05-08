<script setup>
import { onMounted, reactive, ref } from 'vue';
import ClientLayout from '../../layouts/ClientLayout.vue';
import { useFoodTracking } from '../../composables/useFoodTracking';
import { useVoiceTranscription } from '../../composables/useVoiceTranscription';

const food = useFoodTracking();
const fileInputs = ref({});
const voice = useVoiceTranscription({ lang: 'es-CO' });

// Per-meal local state: pending file (pre-upload) and editable note
const noteDrafts = reactive({});           // mealIndex -> texto borrador antes de subir
const pendingFile = reactive({});          // mealIndex -> File seleccionado pero no subido
const pendingPreview = reactive({});       // mealIndex -> object URL para preview
const savingNote = reactive({});           // photoId -> bool (debouncer post-upload)
const recordingFor = ref(null);            // mealIndex actualmente grabando ('pre-{idx}' o photoId)
const uploadErrors = reactive({});         // mealIndex -> mensaje de error de upload

function getMealColor(nombre) {
    const n = (nombre || '').toLowerCase();
    if (n.includes('desayuno')) return 'bg-amber-500/10 text-amber-400';
    if (n.includes('pre-entreno') || n.includes('pre ')) return 'bg-green-500/10 text-green-400';
    if (n.includes('almuerzo') || n.includes('post')) return 'bg-blue-500/10 text-blue-400';
    if (n.includes('cena')) return 'bg-indigo-500/10 text-indigo-400';
    if (n.includes('snack') || n.includes('merienda')) return 'bg-pink-500/10 text-pink-400';
    return 'bg-wc-accent/10 text-wc-accent';
}

function triggerUpload(mealIndex) {
    const inputRef = fileInputs.value[mealIndex];
    if (inputRef) inputRef.click();
}

function onFileSelected(e, meal) {
    const file = e.target.files?.[0];
    if (!file) return;
    // Save file locally and show preview — el upload sucede al confirmar
    if (pendingPreview[meal.index]) {
        try { URL.revokeObjectURL(pendingPreview[meal.index]); } catch (_) {}
    }
    pendingFile[meal.index] = file;
    pendingPreview[meal.index] = URL.createObjectURL(file);
    if (noteDrafts[meal.index] === undefined) noteDrafts[meal.index] = '';
    e.target.value = '';
}

async function confirmUpload(meal) {
    const file = pendingFile[meal.index];
    if (!file) return;
    delete uploadErrors[meal.index];
    try {
        await food.uploadPhoto(file, meal.nombre, meal.index, noteDrafts[meal.index] || '');
        // limpiar locales tras éxito
        try { URL.revokeObjectURL(pendingPreview[meal.index]); } catch (_) {}
        delete pendingFile[meal.index];
        delete pendingPreview[meal.index];
        delete noteDrafts[meal.index];
        delete uploadErrors[meal.index];
    } catch (err) {
        uploadErrors[meal.index] = err.response?.data?.message || 'Error al subir la foto. Intenta de nuevo.';
    }
}

function cancelUpload(mealIndex) {
    if (pendingPreview[mealIndex]) {
        try { URL.revokeObjectURL(pendingPreview[mealIndex]); } catch (_) {}
    }
    delete pendingFile[mealIndex];
    delete pendingPreview[mealIndex];
}

const deleteError = ref('');
async function removePhoto(meal) {
    if (!meal.photo || meal.photo.coach_seen) return;
    if (!confirm('¿Eliminar esta foto?')) return;
    deleteError.value = '';
    try {
        await food.deletePhoto(meal.photo.id);
    } catch (err) {
        deleteError.value = 'No se pudo eliminar la foto. Intenta de nuevo.';
        setTimeout(() => { deleteError.value = ''; }, 4000);
    }
}

const noteError = ref('');
let _saveNoteTimer = null;
function onPostNoteInput(photo, value) {
    photo.client_note = value;
    if (_saveNoteTimer) clearTimeout(_saveNoteTimer);
    _saveNoteTimer = setTimeout(async () => {
        savingNote[photo.id] = true;
        noteError.value = '';
        try {
            await food.updateNote(photo.id, value);
        } catch (err) {
            noteError.value = 'No se pudo guardar la nota. Intenta de nuevo.';
            setTimeout(() => { noteError.value = ''; }, 4000);
        } finally {
            savingNote[photo.id] = false;
        }
    }, 600);
}

async function startDictating(target) {
    // target: { kind: 'pre', index } o { kind: 'post', photoId, mealIndex }
    if (!voice.supported.value) {
        alert(voice.error.value || 'Tu navegador no soporta dictado por voz');
        return;
    }
    recordingFor.value = target.kind === 'pre' ? `pre-${target.index}` : `post-${target.photoId}`;
    try {
        const text = await voice.start({ continuous: false, interim: true });
        if (text) {
            if (target.kind === 'pre') {
                const prev = (noteDrafts[target.index] || '').trim();
                noteDrafts[target.index] = prev ? `${prev} ${text}` : text;
            } else {
                const meal = food.meals.value.find((m) => m.photo?.id === target.photoId);
                if (meal?.photo) {
                    const prev = (meal.photo.client_note || '').trim();
                    const next = prev ? `${prev} ${text}` : text;
                    onPostNoteInput(meal.photo, next);
                }
            }
        }
    } catch (err) {
        console.error('Voice failed', err);
    } finally {
        recordingFor.value = null;
    }
}

function stopDictating() {
    voice.stop();
}

onMounted(() => {
    food.fetchToday();
    food.fetchHistory();
});
</script>

<template>
  <ClientLayout>
    <div v-if="food.loading.value" class="space-y-6">
      <div class="space-y-2">
        <div class="h-9 w-48 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
        <div class="h-5 w-72 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      </div>
      <div v-for="i in 3" :key="i" class="h-32 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
    </div>

    <div v-else-if="food.error.value"
         class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
      <p class="text-sm text-wc-text-secondary">{{ food.error.value }}</p>
      <button @click="food.fetchToday()"
              class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:bg-wc-accent-hover">
        Reintentar
      </button>
    </div>

    <div v-else class="space-y-6">
      <!-- Error toasts (delete / note save) -->
      <Transition name="fade">
        <div v-if="deleteError || noteError"
             class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2 text-sm text-red-400">
          {{ deleteError || noteError }}
        </div>
      </Transition>

      <div class="flex items-end justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text">MI ALIMENTACIÓN</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">Documenta cada comida y tu coach la revisa</p>
        </div>
        <div class="flex flex-col items-end gap-1">
          <span v-if="food.streakDays.value > 0"
                class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-wc-accent/15 to-amber-400/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-wc-accent">
            🔥 {{ food.streakDays.value }} días seguidos
          </span>
          <span class="rounded-full bg-amber-500/10 px-2 py-0.5 text-xs font-bold text-amber-400">
            +{{ food.xpToday.value }} XP hoy
          </span>
        </div>
      </div>

      <div v-if="!food.hasNutritionPlan.value"
           class="rounded-xl border border-wc-border bg-wc-bg-tertiary/50 px-4 py-3 text-sm text-wc-text-secondary">
        <span class="text-wc-accent">ℹ</span> Aún no tienes un plan de nutrición personalizado. Mientras tu coach lo arma, puedes documentar tus comidas con las categorías generales de abajo.
      </div>

      <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-5">
        <div class="flex items-center justify-between">
          <p class="text-sm font-medium text-wc-text-secondary">
            Hoy llevas {{ food.completedToday.value }} de {{ food.totalToday.value }} comidas documentadas
          </p>
          <span class="font-data text-2xl font-bold tabular-nums text-wc-text">{{ food.completionPct.value }}%</span>
        </div>
        <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-wc-bg-tertiary">
          <div class="h-full rounded-full bg-wc-accent transition-all duration-500"
               :style="{ width: food.completionPct.value + '%' }"></div>
        </div>
        <p v-if="food.bonusEarnedToday.value" class="mt-2 text-xs font-semibold text-amber-400">
          🎉 Bonus diario completo (+30 XP)
        </p>
      </div>

      <div v-if="food.meals.value.length > 0" class="space-y-3">
        <div
          v-for="meal in food.meals.value"
          :key="meal.index"
          class="overflow-hidden rounded-xl border bg-wc-bg-secondary transition"
          :class="meal.photo ? 'border-green-500/30' : 'border-wc-border'"
        >
          <div class="flex items-center gap-3 p-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg" :class="getMealColor(meal.nombre)">
              <span class="font-data text-sm font-bold">{{ meal.index + 1 }}</span>
            </div>
            <div class="min-w-0 flex-1">
              <p class="truncate font-display text-sm tracking-wide text-wc-text">{{ (meal.nombre || 'Comida').toUpperCase() }}</p>
              <p v-if="meal.calorias > 0" class="text-[11px] text-wc-text-tertiary">
                {{ meal.calorias }} kcal
              </p>
            </div>
            <span v-if="meal.photo?.xp_awarded"
                  class="rounded-full bg-amber-500/10 px-2 py-0.5 text-xs font-bold text-amber-400">+15 XP</span>
          </div>

          <!-- Foto ya subida -->
          <div v-if="meal.photo" class="relative group">
            <img :src="meal.photo.photo_url" :alt="`Foto de ${meal.nombre}`"
                 class="w-full max-h-72 object-cover">
            <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
              <button v-if="!meal.photo.coach_seen"
                      @click="triggerUpload(meal.index)"
                      class="rounded-lg bg-white/90 px-3 py-1.5 text-xs font-semibold text-black hover:bg-white">
                Reemplazar
              </button>
              <button v-if="!meal.photo.coach_seen"
                      @click="removePhoto(meal)"
                      class="rounded-lg bg-red-500/90 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-500">
                Eliminar
              </button>
            </div>
            <div v-if="meal.photo.coach_seen"
                 class="absolute right-3 top-3 rounded-full px-2 py-1 text-xs font-bold backdrop-blur"
                 :class="meal.photo.coach_reaction === 'bien'
                   ? 'bg-green-500/90 text-white'
                   : meal.photo.coach_reaction === 'mejorar'
                   ? 'bg-amber-500/90 text-white'
                   : 'bg-black/60 text-white'">
              {{ meal.photo.coach_reaction === 'bien' ? '✅ Bien' : meal.photo.coach_reaction === 'mejorar' ? '⚠️ Mejorar' : '👁 Vista' }}
            </div>
          </div>

          <!-- Nota editable post-upload con dictado -->
          <div v-if="meal.photo && !meal.photo.coach_seen"
               class="border-t border-wc-border bg-wc-bg px-4 py-3">
            <div class="mb-1 flex items-center justify-between">
              <label class="text-[11px] uppercase tracking-wider text-wc-text-tertiary">Tu descripción</label>
              <span v-if="savingNote[meal.photo.id]" class="text-[10px] text-wc-text-tertiary">Guardando...</span>
            </div>
            <div class="relative">
              <textarea
                :value="meal.photo.client_note || ''"
                @input="onPostNoteInput(meal.photo, $event.target.value)"
                rows="2"
                placeholder="Describe lo que comiste o dicta con voz"
                class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-secondary p-2 pr-10 text-sm text-wc-text"
              ></textarea>
              <button v-if="voice.supported.value"
                      @click="recordingFor === `post-${meal.photo.id}` ? stopDictating() : startDictating({ kind: 'post', photoId: meal.photo.id, mealIndex: meal.index })"
                      type="button"
                      :title="recordingFor === `post-${meal.photo.id}` ? 'Detener dictado' : 'Dictar por voz'"
                      class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full transition"
                      :class="recordingFor === `post-${meal.photo.id}` ? 'bg-red-500 text-white animate-pulse' : 'bg-wc-accent/10 text-wc-accent hover:bg-wc-accent/20'">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Nota fija si coach ya revisó (sin edicion) -->
          <div v-else-if="meal.photo?.client_note"
               class="border-t border-wc-border bg-wc-bg px-4 py-3">
            <p class="text-[11px] uppercase tracking-wider text-wc-text-tertiary">Tu descripción</p>
            <p class="mt-1 text-sm text-wc-text-secondary">{{ meal.photo.client_note }}</p>
          </div>

          <!-- Preview pre-upload con textarea + dictado + confirmar/cancelar -->
          <div v-if="!meal.photo && pendingFile[meal.index]" class="border-t border-wc-border">
            <div class="relative">
              <img :src="pendingPreview[meal.index]" :alt="`Preview ${meal.nombre}`"
                   class="w-full max-h-72 object-cover">
              <span class="absolute left-3 top-3 rounded-full bg-amber-500/90 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                Pendiente
              </span>
            </div>
            <div class="space-y-3 p-4">
              <div>
                <div class="mb-1 flex items-center justify-between">
                  <label class="text-[11px] uppercase tracking-wider text-wc-text-tertiary">¿Qué comiste? (opcional)</label>
                  <span v-if="recordingFor === `pre-${meal.index}`" class="flex items-center gap-1 text-[10px] font-bold text-red-400">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-red-500"></span>
                    Grabando...
                  </span>
                </div>
                <div class="relative">
                  <textarea
                    v-model="noteDrafts[meal.index]"
                    rows="2"
                    placeholder="Ej: huevos revueltos con avena, café sin azúcar"
                    class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-secondary p-2 pr-10 text-sm text-wc-text"
                  ></textarea>
                  <button v-if="voice.supported.value"
                          @click="recordingFor === `pre-${meal.index}` ? stopDictating() : startDictating({ kind: 'pre', index: meal.index })"
                          type="button"
                          :title="recordingFor === `pre-${meal.index}` ? 'Detener dictado' : 'Dictar por voz'"
                          class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full transition"
                          :class="recordingFor === `pre-${meal.index}` ? 'bg-red-500 text-white animate-pulse' : 'bg-wc-accent/10 text-wc-accent hover:bg-wc-accent/20'">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z" />
                    </svg>
                  </button>
                </div>
                <p v-if="!voice.supported.value" class="mt-1 text-[10px] text-wc-text-tertiary">
                  Tu navegador no soporta dictado por voz. Usa el teclado.
                </p>
              </div>

              <div class="flex gap-2">
                <button @click="cancelUpload(meal.index)"
                        :disabled="food.uploadingIndex.value === meal.index"
                        class="flex-1 rounded-lg border border-wc-border bg-wc-bg-secondary py-2 text-sm font-medium text-wc-text-secondary transition hover:bg-wc-bg-tertiary disabled:opacity-50">
                  Cancelar
                </button>
                <button @click="confirmUpload(meal)"
                        :disabled="food.uploadingIndex.value === meal.index"
                        class="flex-1 rounded-lg bg-wc-accent py-2 text-sm font-semibold text-white transition hover:bg-wc-accent-hover disabled:opacity-50">
                  {{ food.uploadingIndex.value === meal.index ? 'Subiendo...' : 'Confirmar foto' }}
                </button>
              </div>
              <p v-if="uploadErrors[meal.index]" class="text-xs text-red-400 mt-1">{{ uploadErrors[meal.index] }}</p>
            </div>
          </div>

          <!-- Botón Subir foto (sin pending y sin photo) -->
          <button v-else-if="!meal.photo"
                  @click="triggerUpload(meal.index)"
                  :disabled="food.uploadingIndex.value === meal.index"
                  class="flex w-full items-center justify-center gap-2 border-t border-dashed border-wc-border bg-wc-accent/5 py-3 text-sm font-medium text-wc-accent transition hover:bg-wc-accent/10 disabled:opacity-50">
            <svg v-if="food.uploadingIndex.value === meal.index" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
            </svg>
            {{ food.uploadingIndex.value === meal.index ? 'Subiendo...' : 'Subir foto' }}
          </button>

          <div v-if="meal.photo?.coach_note"
               class="border-t border-wc-border bg-wc-bg-tertiary px-4 py-3">
            <p class="text-xs uppercase tracking-wider text-wc-text-tertiary">Nota del coach</p>
            <p class="mt-1 text-sm text-wc-text-secondary">{{ meal.photo.coach_note }}</p>
          </div>

          <input
            type="file"
            accept="image/*"
            style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;opacity:0;pointer-events:none"
            :ref="(el) => fileInputs[meal.index] = el"
            @change="(e) => onFileSelected(e, meal)"
          />
        </div>
      </div>
    </div>
  </ClientLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
