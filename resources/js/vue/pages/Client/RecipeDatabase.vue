<template>
  <div class="space-y-6">
    <!-- Premium compact header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-wc-accent/30 bg-wc-accent/10">
          <ChefHat :size="20" :stroke-width="1.75" class="text-wc-accent" />
        </div>
        <div>
          <p class="text-[10px] font-bold uppercase tracking-widest text-wc-accent">Cocina Saludable</p>
          <h1 class="font-display text-2xl tracking-wide text-wc-text uppercase">Recetas</h1>
        </div>
      </div>
      <div class="flex items-center gap-1.5 rounded-full border border-wc-border bg-wc-bg-tertiary px-3 py-1.5">
        <span class="font-data text-sm font-bold text-wc-accent tabular-nums">{{ filteredRecipes.length }}</span>
        <span class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">recetas</span>
      </div>
    </div>

    <!-- Search + filters -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
      <div class="relative flex-1">
        <Search :size="16" :stroke-width="2" class="absolute left-3 top-1/2 -translate-y-1/2 text-wc-text-tertiary" />
        <input
          v-model="search"
          type="text"
          placeholder="Buscar receta o ingrediente..."
          class="w-full rounded-xl border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary transition-colors focus:border-wc-accent focus:outline-none"
        />
      </div>
      <select v-model="mealFilter" class="rounded-xl border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text transition-colors focus:border-wc-accent focus:outline-none">
        <option value="all">Toda comida</option>
        <option value="desayuno">Desayuno</option>
        <option value="almuerzo">Almuerzo</option>
        <option value="cena">Cena</option>
        <option value="snack">Snack</option>
      </select>
      <select v-model="timeFilter" class="rounded-xl border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text transition-colors focus:border-wc-accent focus:outline-none">
        <option value="all">Cualquier tiempo</option>
        <option value="15">15 min o menos</option>
        <option value="30">30 min o menos</option>
        <option value="60">60 min o menos</option>
      </select>
    </div>

    <!-- Goal filter pills -->
    <div class="flex flex-wrap gap-2">
      <button
        @click="goalFilter = 'all'"
        :class="[
          'flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition-all',
          goalFilter === 'all'
            ? 'border-wc-accent bg-wc-accent text-white shadow-lg shadow-wc-accent/20'
            : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:border-wc-accent/40 hover:text-wc-text'
        ]"
      >
        <LayoutGrid :size="16" :stroke-width="2" />
        <span class="uppercase tracking-wide">Todas</span>
        <span class="rounded-full bg-black/20 px-1.5 py-0.5 text-[10px] font-bold tabular-nums">{{ RECIPES.length }}</span>
      </button>
      <button
        v-for="g in GOALS"
        :key="g.id"
        @click="goalFilter = g.id"
        :class="[
          'flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition-all',
          goalFilter === g.id
            ? 'border-wc-accent bg-wc-accent text-white shadow-lg shadow-wc-accent/20'
            : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:border-wc-accent/40 hover:text-wc-text'
        ]"
      >
        <component :is="getGoalIcon(g.id)" :size="16" :stroke-width="2" />
        <span class="uppercase tracking-wide">{{ g.label }}</span>
        <span class="rounded-full bg-black/20 px-1.5 py-0.5 text-[10px] font-bold tabular-nums">{{ RECIPES.filter(r => r.goal === g.id).length }}</span>
      </button>
    </div>

    <!-- Recipe grid -->
    <div v-if="filteredRecipes.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="r in filteredRecipes"
        :key="r.id"
        @click="selectedRecipe = r"
        class="group relative cursor-pointer overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary wc-lift transition-all hover:border-wc-accent/40"
      >
        <div class="pointer-events-none absolute -right-12 -top-12 h-32 w-32 rounded-full bg-wc-accent/0 blur-2xl transition-all group-hover:bg-wc-accent/10"></div>

        <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-wc-bg-secondary via-wc-bg-tertiary to-wc-bg-secondary">
          <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle, currentColor 1px, transparent 1px); background-size: 20px 20px;"></div>
          <div class="absolute inset-0 flex items-center justify-center">
            <span class="text-6xl transition-transform group-hover:scale-110">{{ r.emoji }}</span>
          </div>
          <div class="absolute left-3 top-3 flex items-center gap-1 rounded-full border border-wc-border bg-black/60 px-2.5 py-1 backdrop-blur-sm">
            <Clock :size="11" :stroke-width="2.5" class="text-wc-text-secondary" />
            <span class="text-[10px] font-bold uppercase tracking-wider text-wc-text">{{ r.prepTime }} min</span>
          </div>
          <div class="absolute right-3 top-3 rounded-full border border-wc-accent/40 bg-wc-accent/20 px-2.5 py-1 backdrop-blur-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-wc-accent">{{ r.meal }}</span>
          </div>
        </div>

        <div class="relative p-4">
          <h3 class="font-display text-base tracking-wide text-wc-text uppercase line-clamp-2 min-h-[2.5rem]">
            {{ r.name }}
          </h3>

          <div class="mt-3 flex items-center gap-2">
            <div class="flex items-center gap-1">
              <Flame :size="13" :stroke-width="2" class="text-wc-accent" />
              <span class="font-data text-sm font-bold tabular-nums text-wc-text">{{ r.macros.cal }}</span>
              <span class="text-[10px] text-wc-text-tertiary">kcal</span>
            </div>
            <span class="h-3 w-px bg-wc-border"></span>
            <div class="flex items-center gap-1">
              <span class="font-data text-xs font-bold text-red-400">P</span>
              <span class="font-data text-sm font-bold tabular-nums text-wc-text">{{ r.macros.protein }}g</span>
            </div>
            <div class="flex items-center gap-1">
              <span class="font-data text-xs font-bold text-blue-400">C</span>
              <span class="font-data text-sm font-bold tabular-nums text-wc-text">{{ r.macros.carbs }}g</span>
            </div>
            <div class="flex items-center gap-1">
              <span class="font-data text-xs font-bold text-amber-400">G</span>
              <span class="font-data text-sm font-bold tabular-nums text-wc-text">{{ r.macros.fat }}g</span>
            </div>
          </div>

          <div class="mt-2 flex flex-wrap gap-1">
            <span class="inline-flex items-center gap-1 rounded-full border border-wc-border bg-wc-bg-secondary px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-wc-text-tertiary">
              <component :is="getGoalIcon(r.goal)" :size="9" :stroke-width="2.5" />
              {{ GOALS.find(g => g.id === r.goal)?.label || r.goal }}
            </span>
            <span
              v-for="tag in (r.tags || []).slice(0, 2)"
              :key="tag"
              class="inline-flex items-center rounded-full border border-wc-border bg-wc-bg-secondary px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-wc-text-tertiary"
            >
              {{ tag }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
      <ChefHat :size="48" :stroke-width="1.5" class="mx-auto mb-3 text-wc-text-tertiary/40" />
      <h3 class="font-display text-lg uppercase tracking-wide text-wc-text">Sin resultados</h3>
      <p class="mt-2 text-sm text-wc-text-tertiary">Intenta cambiar los filtros o el termino de busqueda.</p>
    </div>

    <!-- Recipe detail modal -->
    <Transition name="fade">
      <div
        v-if="selectedRecipe"
        @click.self="selectedRecipe = null"
        @keydown.escape="selectedRecipe = null"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
      >
        <Transition name="scale">
          <div v-if="selectedRecipe" class="w-full max-w-2xl overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-secondary shadow-2xl">
            <div class="flex items-center justify-between border-b border-wc-border px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-wc-accent/30 bg-wc-accent/10">
                  <component :is="getMealIcon(selectedRecipe.meal)" :size="22" :stroke-width="1.75" class="text-wc-accent" />
                </div>
                <div>
                  <p class="text-[10px] font-bold uppercase tracking-widest text-wc-accent">{{ selectedRecipe.meal }}</p>
                  <h2 class="font-display text-xl tracking-wide text-wc-text uppercase">{{ selectedRecipe.name }}</h2>
                </div>
              </div>
              <button
                @click="selectedRecipe = null"
                class="btn-press flex h-9 w-9 items-center justify-center rounded-xl border border-wc-border bg-wc-bg-tertiary text-wc-text-secondary transition-colors hover:border-wc-accent/40 hover:text-wc-text"
              >
                <X :size="18" :stroke-width="2" />
              </button>
            </div>

            <div class="max-h-[70vh] overflow-y-auto px-6 py-5 space-y-5">
              <p class="text-sm text-wc-text-secondary">{{ selectedRecipe.description }}</p>

              <div class="grid grid-cols-4 gap-3">
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                  <Flame :size="14" :stroke-width="2" class="mx-auto mb-1 text-wc-accent" />
                  <p class="font-data text-xl font-bold tabular-nums text-wc-text">{{ selectedRecipe.macros.cal }}</p>
                  <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">kcal</p>
                </div>
                <div class="rounded-xl border border-red-500/20 bg-red-500/5 p-3 text-center">
                  <p class="font-data text-[10px] font-bold text-red-400">P</p>
                  <p class="font-data text-xl font-bold tabular-nums text-wc-text">{{ selectedRecipe.macros.protein }}g</p>
                  <p class="text-[10px] uppercase tracking-wider text-red-400/60">Proteina</p>
                </div>
                <div class="rounded-xl border border-blue-500/20 bg-blue-500/5 p-3 text-center">
                  <p class="font-data text-[10px] font-bold text-blue-400">C</p>
                  <p class="font-data text-xl font-bold tabular-nums text-wc-text">{{ selectedRecipe.macros.carbs }}g</p>
                  <p class="text-[10px] uppercase tracking-wider text-blue-400/60">Carbos</p>
                </div>
                <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-3 text-center">
                  <p class="font-data text-[10px] font-bold text-amber-400">G</p>
                  <p class="font-data text-xl font-bold tabular-nums text-wc-text">{{ selectedRecipe.macros.fat }}g</p>
                  <p class="text-[10px] uppercase tracking-wider text-amber-400/60">Grasa</p>
                </div>
              </div>

              <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-1 rounded-full border border-wc-border bg-wc-bg-tertiary px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-wc-text-secondary">
                  <Clock :size="11" :stroke-width="2.5" /> {{ selectedRecipe.prepTime }} min
                </span>
                <span class="inline-flex items-center gap-1 rounded-full border border-wc-border bg-wc-bg-tertiary px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-wc-text-secondary">
                  <Utensils :size="11" :stroke-width="2.5" /> {{ selectedRecipe.servings }}
                </span>
                <span class="inline-flex items-center gap-1 rounded-full border border-wc-accent/30 bg-wc-accent/10 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-wc-accent">
                  <component :is="getGoalIcon(selectedRecipe.goal)" :size="11" :stroke-width="2.5" />
                  {{ GOALS.find(g => g.id === selectedRecipe.goal)?.label || selectedRecipe.goal }}
                </span>
                <span
                  v-for="tag in selectedRecipe.tags"
                  :key="tag"
                  class="rounded-full border border-wc-border bg-wc-bg-tertiary px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-wc-text-tertiary"
                >{{ tag }}</span>
              </div>

              <div>
                <h4 class="font-display text-sm uppercase tracking-wide text-wc-text">Ingredientes</h4>
                <ul class="mt-3 space-y-2">
                  <li v-for="(ing, i) in selectedRecipe.ingredients" :key="i" class="flex items-center gap-2.5 text-sm text-wc-text-secondary">
                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-wc-accent"></span>
                    {{ ing }}
                  </li>
                </ul>
              </div>

              <div>
                <h4 class="font-display text-sm uppercase tracking-wide text-wc-text">Preparacion</h4>
                <ol class="mt-3 space-y-3">
                  <li v-for="(step, i) in selectedRecipe.steps" :key="i" class="flex gap-3 text-sm text-wc-text-secondary">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-wc-accent/30 bg-wc-accent/10 font-data text-[11px] font-bold text-wc-accent tabular-nums">{{ i + 1 }}</span>
                    <span class="pt-0.5">{{ step }}</span>
                  </li>
                </ol>
              </div>

              <div v-if="selectedRecipe.coachTip" class="flex gap-3 rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-4">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-accent/30 bg-wc-accent/10">
                  <Lightbulb :size="16" :stroke-width="2" class="text-wc-accent" />
                </div>
                <div>
                  <p class="text-[10px] font-bold uppercase tracking-widest text-wc-accent">Tip del Coach</p>
                  <p class="mt-1 text-xs text-wc-text-secondary">{{ selectedRecipe.coachTip }}</p>
                </div>
              </div>

              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-xs text-wc-text-tertiary">
                  ¿Quieres reemplazar una comida de tu plan por esta receta? Ve a
                  <span class="font-bold text-wc-accent">Nutricion</span> y usa el boton
                  <span class="font-bold text-wc-text">"Cambiar por receta"</span> sobre cualquier comida del dia.
                </p>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import {
  ChefHat, Search, Clock, Flame, Dumbbell, Scale, Zap,
  LayoutGrid, Coffee, UtensilsCrossed, Soup, Cookie,
  Salad, Apple, X, Utensils, Lightbulb,
} from 'lucide-vue-next';
import { RECIPES, GOALS } from '../../data/recipes';

const search = ref('');
const mealFilter = ref('all');
const timeFilter = ref('all');
const goalFilter = ref('all');
const selectedRecipe = ref(null);

function getMealIcon(meal) {
  const m = (meal || '').toLowerCase();
  if (m.includes('desayuno')) return Coffee;
  if (m.includes('almuerzo')) return UtensilsCrossed;
  if (m.includes('cena')) return Soup;
  if (m.includes('snack') || m.includes('merienda')) return Cookie;
  return Salad;
}

function getGoalIcon(goal) {
  const g = (goal || '').toLowerCase();
  if (g.includes('grasa') || g.includes('fat')) return Flame;
  if (g.includes('musculo') || g.includes('muscle') || g.includes('ganar')) return Dumbbell;
  if (g.includes('mantenim')) return Scale;
  if (g.includes('energ')) return Zap;
  return Apple;
}

const filteredRecipes = computed(() => {
  return RECIPES.filter(r => {
    if (goalFilter.value !== 'all' && r.goal !== goalFilter.value) return false;
    if (mealFilter.value !== 'all' && r.meal.toLowerCase() !== mealFilter.value) return false;
    if (timeFilter.value !== 'all' && r.prepTime > parseInt(timeFilter.value)) return false;
    if (search.value.length > 1) {
      const s = search.value.toLowerCase();
      const inName = r.name.toLowerCase().includes(s);
      const inIngredients = r.ingredients.some(ing => ing.toLowerCase().includes(s));
      const inDescription = r.description.toLowerCase().includes(s);
      if (!inName && !inIngredients && !inDescription) return false;
    }
    return true;
  });
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
.scale-enter-active,
.scale-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.scale-enter-from,
.scale-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
