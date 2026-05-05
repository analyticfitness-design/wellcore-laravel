<script setup>
import { onMounted, ref } from 'vue';
import RiseLayout from '../../layouts/RiseLayout.vue';
import { useFoodTracking } from '../../composables/useFoodTracking';

const food = useFoodTracking();
const fileInputs = ref({});

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

async function onFileSelected(e, meal) {
    const file = e.target.files?.[0];
    if (!file) return;
    try {
        await food.uploadPhoto(file, meal.nombre, meal.index);
    } catch (err) {
        console.error('Upload failed', err);
    } finally {
        e.target.value = '';
    }
}

async function removePhoto(meal) {
    if (!meal.photo || meal.photo.coach_seen) return;
    if (!confirm('¿Eliminar esta foto?')) return;
    try {
        await food.deletePhoto(meal.photo.id);
    } catch (err) {
        console.error('Delete failed', err);
    }
}

onMounted(() => {
    food.fetchToday();
    food.fetchHistory();
});
</script>

<template>
  <RiseLayout>
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

    <div v-else-if="!food.hasNutritionPlan.value"
         class="rounded-2xl border border-dashed border-wc-border bg-wc-bg-tertiary/50 p-16 text-center">
      <h3 class="font-display text-2xl tracking-wide text-wc-text">SIN PLAN DE NUTRICIÓN</h3>
      <p class="mt-2 text-sm text-wc-text-secondary">Tu coach está armando tu plan. Pronto podrás documentar tus comidas.</p>
    </div>

    <div v-else class="space-y-6">
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

          <button v-else
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
            accept="image/jpeg,image/jpg,image/png,image/webp"
            class="hidden"
            :ref="(el) => fileInputs[meal.index] = el"
            @change="(e) => onFileSelected(e, meal)"
          />
        </div>
      </div>
    </div>
  </RiseLayout>
</template>
