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

    <!-- MY MACROS TODAY — sticky -->
    <div v-if="myMacros && myMacros.goals" class="sticky top-0 z-20 -mx-4 px-4 py-3 sm:mx-0 sm:px-0 sm:py-0">
      <div class="rounded-2xl border border-wc-accent/20 bg-wc-bg-tertiary p-4 backdrop-blur-md">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-accent/10">
              <Sparkles :size="16" :stroke-width="2" class="text-wc-accent" />
            </div>
            <div>
              <p class="text-[10px] font-bold uppercase tracking-widest text-wc-accent">Mis Macros Hoy</p>
              <p class="text-xs text-wc-text-tertiary">Encuentra recetas que encajen en tu plan</p>
            </div>
          </div>
          <div v-if="myMacros.swaps_today && myMacros.swaps_today.length" class="flex items-center gap-1.5 rounded-full border border-wc-accent/30 bg-wc-accent/10 px-2.5 py-1">
            <RefreshCw :size="11" :stroke-width="2.5" class="text-wc-accent" />
            <span class="text-[10px] font-bold uppercase tracking-wider text-wc-accent">{{ myMacros.swaps_today.length }} swap{{ myMacros.swaps_today.length !== 1 ? 's' : '' }} hoy</span>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
          <div>
            <div class="flex items-baseline justify-between">
              <span class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Kcal</span>
              <span class="font-data text-xs font-bold tabular-nums text-wc-text">{{ myMacros.current_total.calories }}/{{ myMacros.goals.calories }}</span>
            </div>
            <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
              <div class="h-full rounded-full bg-wc-accent transition-all" :style="{ width: Math.min(100, (myMacros.current_total.calories / myMacros.goals.calories) * 100) + '%' }"></div>
            </div>
          </div>
          <div>
            <div class="flex items-baseline justify-between">
              <span class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Proteína</span>
              <span class="font-data text-xs font-bold tabular-nums text-wc-text">{{ myMacros.current_total.protein }}/{{ myMacros.goals.protein }}g</span>
            </div>
            <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
              <div class="h-full rounded-full bg-red-500 transition-all" :style="{ width: Math.min(100, (myMacros.current_total.protein / myMacros.goals.protein) * 100) + '%' }"></div>
            </div>
          </div>
          <div>
            <div class="flex items-baseline justify-between">
              <span class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Carbs</span>
              <span class="font-data text-xs font-bold tabular-nums text-wc-text">{{ myMacros.current_total.carbs }}/{{ myMacros.goals.carbs }}g</span>
            </div>
            <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
              <div class="h-full rounded-full bg-blue-500 transition-all" :style="{ width: Math.min(100, (myMacros.current_total.carbs / myMacros.goals.carbs) * 100) + '%' }"></div>
            </div>
          </div>
          <div>
            <div class="flex items-baseline justify-between">
              <span class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Grasas</span>
              <span class="font-data text-xs font-bold tabular-nums text-wc-text">{{ myMacros.current_total.fat }}/{{ myMacros.goals.fat }}g</span>
            </div>
            <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
              <div class="h-full rounded-full bg-amber-500 transition-all" :style="{ width: Math.min(100, (myMacros.current_total.fat / myMacros.goals.fat) * 100) + '%' }"></div>
            </div>
          </div>
        </div>

        <div v-if="myMacros.swaps_today && myMacros.swaps_today.length" class="mt-3 flex flex-wrap gap-2 border-t border-wc-border pt-3">
          <div v-for="swap in myMacros.swaps_today" :key="swap.id" class="flex items-center gap-2 rounded-full border border-wc-border bg-wc-bg-secondary px-3 py-1">
            <span class="text-[10px] text-wc-text-tertiary">{{ swap.original_meal_name }} →</span>
            <span class="text-[10px] font-bold text-wc-text truncate max-w-[120px]">{{ swap.recipe_name }}</span>
            <button @click="undoSwap(swap.id)" class="text-wc-text-tertiary hover:text-wc-accent transition-colors" aria-label="Deshacer">
              <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>
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
        <!-- Hover glow -->
        <div class="pointer-events-none absolute -right-12 -top-12 h-32 w-32 rounded-full bg-wc-accent/0 blur-2xl transition-all group-hover:bg-wc-accent/10"></div>

        <!-- Thumbnail area -->
        <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-wc-bg-secondary via-wc-bg-tertiary to-wc-bg-secondary">
          <!-- Subtle dot pattern -->
          <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle, currentColor 1px, transparent 1px); background-size: 20px 20px;"></div>

          <!-- Center emoji (recipe-specific 3D emoji) -->
          <div class="absolute inset-0 flex items-center justify-center">
            <span class="text-6xl transition-transform group-hover:scale-110">{{ r.emoji }}</span>
          </div>

          <!-- Time badge -->
          <div class="absolute left-3 top-3 flex items-center gap-1 rounded-full border border-wc-border bg-black/60 px-2.5 py-1 backdrop-blur-sm">
            <Clock :size="11" :stroke-width="2.5" class="text-wc-text-secondary" />
            <span class="text-[10px] font-bold uppercase tracking-wider text-wc-text">{{ r.prepTime }} min</span>
          </div>

          <!-- Meal badge -->
          <div class="absolute right-3 top-3 rounded-full border border-wc-accent/40 bg-wc-accent/20 px-2.5 py-1 backdrop-blur-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-wc-accent">{{ r.meal }}</span>
          </div>

          <!-- Best meal match badge -->
          <div v-if="myMacros && getBestMealMatch(r)" class="absolute bottom-3 left-3 right-3 z-10">
            <div
              class="flex items-center justify-center gap-1.5 rounded-full border px-2.5 py-1 backdrop-blur-md"
              :class="{
                'border-emerald-500/40 bg-emerald-500/20': getRecipeCompatibility(r, getBestMealMatch(r)) === 'good',
                'border-amber-500/40 bg-amber-500/20': getRecipeCompatibility(r, getBestMealMatch(r)) === 'warn',
              }"
            >
              <CheckCircle2 v-if="getRecipeCompatibility(r, getBestMealMatch(r)) === 'good'" :size="11" :stroke-width="2.5" class="text-emerald-400" />
              <AlertTriangle v-else :size="11" :stroke-width="2.5" class="text-amber-400" />
              <span class="text-[9px] font-bold uppercase tracking-wider"
                :class="{
                  'text-emerald-400': getRecipeCompatibility(r, getBestMealMatch(r)) === 'good',
                  'text-amber-400': getRecipeCompatibility(r, getBestMealMatch(r)) === 'warn',
                }"
              >Cabe en {{ getBestMealMatch(r) }}</span>
            </div>
          </div>
        </div>

        <!-- Card body -->
        <div class="relative p-4">
          <h3 class="font-display text-base tracking-wide text-wc-text uppercase line-clamp-2 min-h-[2.5rem]">
            {{ r.name }}
          </h3>

          <!-- Macros row -->
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

          <!-- Goal tag -->
          <div class="mt-2 flex flex-wrap gap-1">
            <span
              class="inline-flex items-center gap-1 rounded-full border border-wc-border bg-wc-bg-secondary px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-wc-text-tertiary"
            >
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
            <!-- Modal header -->
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

            <!-- Modal body -->
            <div class="max-h-[70vh] overflow-y-auto px-6 py-5 space-y-5">
              <p class="text-sm text-wc-text-secondary">{{ selectedRecipe.description }}</p>

              <!-- Macro cards -->
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

              <!-- Meta tags -->
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

              <!-- Ingredients -->
              <div>
                <h4 class="font-display text-sm uppercase tracking-wide text-wc-text">Ingredientes</h4>
                <ul class="mt-3 space-y-2">
                  <li v-for="(ing, i) in selectedRecipe.ingredients" :key="i" class="flex items-center gap-2.5 text-sm text-wc-text-secondary">
                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-wc-accent"></span>
                    {{ ing }}
                  </li>
                </ul>
              </div>

              <!-- Steps -->
              <div>
                <h4 class="font-display text-sm uppercase tracking-wide text-wc-text">Preparacion</h4>
                <ol class="mt-3 space-y-3">
                  <li v-for="(step, i) in selectedRecipe.steps" :key="i" class="flex gap-3 text-sm text-wc-text-secondary">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-wc-accent/30 bg-wc-accent/10 font-data text-[11px] font-bold text-wc-accent tabular-nums">{{ i + 1 }}</span>
                    <span class="pt-0.5">{{ step }}</span>
                  </li>
                </ol>
              </div>

              <!-- Impact section -->
              <div v-if="myMacros && myMacros.meals" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
                <div class="mb-3 flex items-center gap-2">
                  <RefreshCw :size="16" :stroke-width="2" class="text-wc-accent" />
                  <h3 class="font-display text-sm uppercase tracking-wide text-wc-text">Reemplazar una comida</h3>
                </div>
                <p class="mb-3 text-xs text-wc-text-tertiary">Aplica esta receta en lugar de una de tus comidas del día. Tus macros se ajustarán automáticamente.</p>
                <div class="space-y-2">
                  <button
                    v-for="meal in myMacros.meals"
                    :key="meal.name"
                    @click="applySwap(selectedRecipe, meal.name)"
                    :disabled="applyingSwap || meal.swapped"
                    class="flex w-full items-center justify-between gap-3 rounded-xl border bg-wc-bg-secondary px-4 py-3 text-left transition-all hover:border-wc-accent/40 disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="{
                      'border-emerald-500/40': getRecipeCompatibility(selectedRecipe, meal.name) === 'good',
                      'border-amber-500/40': getRecipeCompatibility(selectedRecipe, meal.name) === 'warn',
                      'border-wc-border': getRecipeCompatibility(selectedRecipe, meal.name) === 'bad' || getRecipeCompatibility(selectedRecipe, meal.name) === 'no-data',
                    }"
                  >
                    <div class="flex items-center gap-3">
                      <CheckCircle2 v-if="getRecipeCompatibility(selectedRecipe, meal.name) === 'good'" :size="18" :stroke-width="2" class="text-emerald-400" />
                      <AlertTriangle v-else-if="getRecipeCompatibility(selectedRecipe, meal.name) === 'warn'" :size="18" :stroke-width="2" class="text-amber-400" />
                      <Info v-else :size="18" :stroke-width="2" class="text-wc-text-tertiary" />
                      <div>
                        <p class="text-sm font-bold text-wc-text">Reemplazar {{ meal.name }}</p>
                        <p class="text-[10px] text-wc-text-tertiary">
                          Original: {{ meal.calories }}kcal · P{{ meal.protein }} C{{ meal.carbs }} G{{ meal.fat }}
                        </p>
                      </div>
                    </div>
                    <div class="text-right">
                      <p v-if="meal.swapped" class="text-[10px] font-bold uppercase text-wc-accent">Ya cambiada</p>
                      <p v-else class="text-[10px] font-bold uppercase tracking-wider"
                        :class="{
                          'text-emerald-400': getRecipeCompatibility(selectedRecipe, meal.name) === 'good',
                          'text-amber-400': getRecipeCompatibility(selectedRecipe, meal.name) === 'warn',
                          'text-wc-text-tertiary': getRecipeCompatibility(selectedRecipe, meal.name) === 'bad' || getRecipeCompatibility(selectedRecipe, meal.name) === 'no-data',
                        }"
                      >Aplicar →</p>
                    </div>
                  </button>
                </div>
                <div v-if="swapResult === 'success'" class="mt-3 flex items-center gap-2 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2">
                  <CheckCircle2 :size="14" :stroke-width="2.5" class="text-emerald-400" />
                  <span class="text-xs font-semibold text-emerald-400">¡Receta aplicada! Tus macros se actualizaron.</span>
                </div>
                <div v-else-if="swapResult === 'error'" class="mt-3 flex items-center gap-2 rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-2">
                  <AlertTriangle :size="14" :stroke-width="2.5" class="text-red-400" />
                  <span class="text-xs font-semibold text-red-400">Error al aplicar el swap. Intenta de nuevo.</span>
                </div>
              </div>

              <!-- Coach tip -->
              <div v-if="selectedRecipe.coachTip" class="flex gap-3 rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-4">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-accent/30 bg-wc-accent/10">
                  <Lightbulb :size="16" :stroke-width="2" class="text-wc-accent" />
                </div>
                <div>
                  <p class="text-[10px] font-bold uppercase tracking-widest text-wc-accent">Tip del Coach</p>
                  <p class="mt-1 text-xs text-wc-text-secondary">{{ selectedRecipe.coachTip }}</p>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import {
  ChefHat, Search, Clock, Flame, Dumbbell, Scale, Zap,
  LayoutGrid, Coffee, UtensilsCrossed, Soup, Cookie,
  Salad, Apple, X, Utensils, Lightbulb,
  Sparkles, RefreshCw, AlertTriangle, CheckCircle2, Info
} from 'lucide-vue-next';
import { useApi } from '../../composables/useApi';

const api = useApi();
const myMacros = ref(null);
const loadingMacros = ref(false);
const applyingSwap = ref(false);
const swapResult = ref(null);

async function loadMyMacros() {
  loadingMacros.value = true;
  try {
    const r = await api.get('/api/v/client/nutrition/macros-today');
    myMacros.value = r.data;
  } catch (e) {
    myMacros.value = null;
  } finally {
    loadingMacros.value = false;
  }
}

function getRecipeCompatibility(recipe, mealName) {
  if (!myMacros.value || !myMacros.value.meals) return 'no-data';
  const meal = myMacros.value.meals.find(m => m.name.toLowerCase().includes(mealName.toLowerCase()));
  if (!meal) return 'no-data';
  const r = recipe.macros || {};
  const calDiff = Math.abs((r.cal || r.calories || 0) - meal.calories);
  const protDiff = Math.abs((r.protein || 0) - meal.protein);
  const carbDiff = Math.abs((r.carbs || 0) - meal.carbs);
  const fatDiff = Math.abs((r.fat || 0) - meal.fat);
  const tolerance = meal.calories * 0.15;
  if (calDiff <= tolerance && protDiff <= 10 && carbDiff <= 20 && fatDiff <= 8) return 'good';
  if (calDiff <= tolerance * 2) return 'warn';
  return 'bad';
}

function getBestMealMatch(recipe) {
  if (!myMacros.value || !myMacros.value.meals) return null;
  const compatibilities = myMacros.value.meals.map(m => ({
    meal: m,
    score: getRecipeCompatibility(recipe, m.name),
  }));
  const good = compatibilities.find(c => c.score === 'good');
  if (good) return good.meal.name;
  const warn = compatibilities.find(c => c.score === 'warn');
  if (warn) return warn.meal.name;
  return null;
}

async function applySwap(recipe, mealName) {
  if (applyingSwap.value) return;
  applyingSwap.value = true;
  swapResult.value = null;
  try {
    const meal = myMacros.value.meals.find(m => m.name === mealName);
    await api.post('/api/v/client/nutrition/swap', {
      recipe_id: recipe.id,
      recipe_name: recipe.name,
      original_meal_name: mealName,
      recipe_macros: {
        calories: recipe.macros.cal || recipe.macros.calories || 0,
        protein: recipe.macros.protein || 0,
        carbs: recipe.macros.carbs || 0,
        fat: recipe.macros.fat || 0,
      },
      original_macros: {
        calories: meal.calories,
        protein: meal.protein,
        carbs: meal.carbs,
        fat: meal.fat,
      },
    });
    swapResult.value = 'success';
    await loadMyMacros();
    setTimeout(() => { swapResult.value = null; }, 3000);
  } catch (e) {
    swapResult.value = 'error';
    setTimeout(() => { swapResult.value = null; }, 3000);
  } finally {
    applyingSwap.value = false;
  }
}

async function undoSwap(swapId) {
  try {
    await api.delete(`/api/v/client/nutrition/swap/${swapId}`);
    await loadMyMacros();
  } catch (e) {}
}

onMounted(() => {
  loadMyMacros();
});

const GOALS = [
  { id: 'perder_grasa', label: 'Perder grasa', icon: '🔥' },
  { id: 'ganar_musculo', label: 'Ganar musculo', icon: '💪' },
  { id: 'mantenimiento', label: 'Mantenimiento', icon: '⚖️' },
  { id: 'energia', label: 'Energia', icon: '⚡' },
];

const RECIPES = [
  // DESAYUNOS
  { id: 1, name: 'Bowl de Avena Proteica', emoji: '🥣', meal: 'Desayuno', goal: 'ganar_musculo', prepTime: 10, servings: '1 porcion', description: 'Bowl de avena con whey protein, frutas y semillas. Alto en proteina para empezar el dia con energia sostenida.', macros: { cal: 420, protein: 35, carbs: 48, fat: 12 }, ingredients: ['60g avena en hojuelas', '1 scoop whey protein vainilla', '200ml leche de almendras', '1/2 banano en rodajas', '10g semillas de chia', '5 fresas picadas', '10g mantequilla de mani'], steps: ['Cocina la avena con la leche de almendras a fuego medio 3-4 min.', 'Retira del fuego y mezcla el whey protein.', 'Sirve en un bowl y agrega banano, fresas y semillas.', 'Termina con un chorrito de mantequilla de mani.'], tags: ['Alta proteina', 'Fibra'], coachTip: 'Prepara la avena la noche anterior en la nevera (overnight oats) para ganar tiempo en la manana.' },
  { id: 2, name: 'Huevos Revueltos con Espinaca', emoji: '🥚', meal: 'Desayuno', goal: 'perder_grasa', prepTime: 10, servings: '1 porcion', description: 'Huevos revueltos con espinaca y tomate. Bajo en carbos, alto en proteina para deficit calorico.', macros: { cal: 280, protein: 24, carbs: 6, fat: 18 }, ingredients: ['3 huevos enteros', '1 taza de espinaca fresca', '1/2 tomate picado', '1 cdta aceite de oliva', 'Sal y pimienta al gusto', 'Oregano opcional'], steps: ['Calienta el aceite en sarten a fuego medio.', 'Saltea la espinaca hasta que se reduzca (1 min).', 'Agrega el tomate y cocina 1 min mas.', 'Vierte los huevos batidos y revuelve hasta coccion deseada.'], tags: ['Low carb', 'Keto friendly'], coachTip: 'Si estas en deficit, usa 2 huevos enteros + 2 claras para reducir 60 kcal sin perder volumen.' },
  { id: 3, name: 'Pancakes de Banano', emoji: '🥞', meal: 'Desayuno', goal: 'energia', prepTime: 15, servings: '2 porciones', description: 'Pancakes esponjosos de banano y avena. Perfectos como pre-entreno por su carga glucemica moderada.', macros: { cal: 350, protein: 18, carbs: 52, fat: 8 }, ingredients: ['1 banano maduro', '2 huevos', '40g avena en hojuelas', '1/2 scoop whey protein', '1 cdta polvo para hornear', 'Canela al gusto', 'Miel o fruta para servir'], steps: ['Licua banano, huevos, avena, protein y polvo para hornear.', 'Calienta sarten antiadherente a fuego medio-bajo.', 'Vierte porciones de mezcla y cocina 2 min por lado.', 'Sirve con miel o fruta fresca.'], tags: ['Pre-entreno', 'Sin gluten opcion'], coachTip: 'Comelos 60-90 min antes de entrenar para tener energia sostenida durante la sesion.' },
  { id: 4, name: 'Yogurt Griego con Granola', emoji: '🫐', meal: 'Desayuno', goal: 'mantenimiento', prepTime: 5, servings: '1 porcion', description: 'Yogurt griego natural con granola casera y arandanos. Balance perfecto de macros.', macros: { cal: 320, protein: 22, carbs: 35, fat: 10 }, ingredients: ['200g yogurt griego natural', '30g granola sin azucar', '50g arandanos frescos', '5g semillas de linaza', '1 cdta miel de abejas'], steps: ['Coloca el yogurt en un bowl.', 'Agrega la granola y los arandanos.', 'Espolvorea las semillas de linaza.', 'Termina con un toque de miel.'], tags: ['Rapido', 'Probioticos'], coachTip: 'El yogurt griego tiene el doble de proteina que el regular. Siempre elige la version natural sin azucar.' },
  // ALMUERZOS
  { id: 5, name: 'Pollo a la Plancha con Arroz', emoji: '🍗', meal: 'Almuerzo', goal: 'ganar_musculo', prepTime: 25, servings: '1 porcion', description: 'Clasico del fitness: pechuga a la plancha con arroz integral y vegetales. Simple, efectivo, comprobado.', macros: { cal: 520, protein: 45, carbs: 55, fat: 10 }, ingredients: ['180g pechuga de pollo', '100g arroz integral (peso crudo)', '1 taza brocoli', '1/2 zanahoria rallada', '1 cdta aceite de oliva', 'Limon, ajo, sal, pimienta'], steps: ['Cocina el arroz integral segun instrucciones del paquete.', 'Sazona el pollo con ajo, limon, sal y pimienta.', 'Cocina en sarten caliente con aceite 5-6 min por lado.', 'Cocina el brocoli al vapor 4 min.', 'Sirve todo junto con zanahoria rallada.'], tags: ['Meal prep', 'Clasico fitness'], coachTip: 'Prepara 4-5 porciones el domingo para tener almuerzo listo toda la semana (meal prep).' },
  { id: 6, name: 'Ensalada de Atun Mediterranea', emoji: '🥗', meal: 'Almuerzo', goal: 'perder_grasa', prepTime: 10, servings: '1 porcion', description: 'Ensalada fresca con atun, aceitunas y vegetales. Baja en calorias, alta en proteina y grasas saludables.', macros: { cal: 310, protein: 32, carbs: 12, fat: 16 }, ingredients: ['1 lata atun en agua (160g)', '2 tazas lechuga mixta', '1/2 pepino en rodajas', '10 tomates cherry', '5 aceitunas negras', '1/4 cebolla morada', '1 cda aceite de oliva', 'Jugo de 1/2 limon'], steps: ['Lava y pica todos los vegetales.', 'Escurre el atun y desmenuzalo.', 'Mezcla todo en un bowl grande.', 'Adreza con aceite de oliva y limon.'], tags: ['Sin coccion', 'Rapido'], coachTip: 'El atun en agua tiene la mitad de calorias que en aceite. Para deficit calorico siempre elige en agua.' },
  { id: 7, name: 'Bowl de Quinoa con Salmon', emoji: '🐟', meal: 'Almuerzo', goal: 'mantenimiento', prepTime: 30, servings: '1 porcion', description: 'Bowl nutritivo con salmon, quinoa y aguacate. Rico en omega-3 y proteina completa.', macros: { cal: 530, protein: 38, carbs: 42, fat: 22 }, ingredients: ['150g filete de salmon', '80g quinoa (peso crudo)', '1/2 aguacate', '1 taza espinaca', '1/2 pepino en cubos', '1 cda salsa de soya', 'Sesamo y limon'], steps: ['Cocina la quinoa en agua con sal por 15 min.', 'Sazona el salmon y cocina en sarten 4 min por lado.', 'Monta el bowl: quinoa base, espinaca, pepino.', 'Coloca el salmon encima con aguacate en laminas.', 'Termina con soya, sesamo y limon.'], tags: ['Omega-3', 'Grasas saludables'], coachTip: 'La quinoa es uno de los pocos granos con proteina completa (todos los aminoacidos esenciales).' },
  { id: 8, name: 'Wrap de Pollo y Vegetales', emoji: '🌯', meal: 'Almuerzo', goal: 'energia', prepTime: 15, servings: '1 porcion', description: 'Wrap integral con pollo desmenuzado y vegetales frescos. Facil de llevar y comer en cualquier parte.', macros: { cal: 410, protein: 35, carbs: 38, fat: 14 }, ingredients: ['1 tortilla integral grande', '150g pollo desmenuzado', '1/4 aguacate', '1/2 tomate en rodajas', '1/4 taza zanahoria rallada', 'Lechuga', '1 cda hummus'], steps: ['Unta el hummus en la tortilla.', 'Coloca la lechuga como base.', 'Agrega el pollo, tomate, zanahoria y aguacate.', 'Enrolla firme doblando los extremos.', 'Corta a la mitad para servir.'], tags: ['Portable', 'Para llevar'], coachTip: 'Perfecto para comer en la oficina. Preparalo en la manana y envuelvelo en papel aluminio.' },
  // CENAS
  { id: 9, name: 'Salmon al Horno con Vegetales', emoji: '🐟', meal: 'Cena', goal: 'perder_grasa', prepTime: 30, servings: '1 porcion', description: 'Salmon al horno con especias y vegetales asados. Alto en omega-3 y bajo en carbos para la noche.', macros: { cal: 380, protein: 36, carbs: 14, fat: 20 }, ingredients: ['180g filete de salmon', '1 taza esparragos', '1/2 pimenton rojo', '1/2 calabacin en rodajas', '1 cda aceite de oliva', 'Ajo en polvo, paprika, sal'], steps: ['Precalienta el horno a 200C.', 'Coloca salmon y vegetales en bandeja con aceite y especias.', 'Hornea 18-22 min hasta que el salmon este cocido.', 'Sirve directamente de la bandeja.'], tags: ['Low carb', 'Omega-3', 'Una bandeja'], coachTip: 'Las grasas del salmon no te engordan — los omega-3 son antiinflamatorios y mejoran la recuperacion muscular.' },
  { id: 10, name: 'Pechuga Rellena de Espinaca', emoji: '🍗', meal: 'Cena', goal: 'ganar_musculo', prepTime: 35, servings: '1 porcion', description: 'Pechuga de pollo rellena con espinaca y queso. Elegante, alta en proteina y sorprendentemente facil.', macros: { cal: 420, protein: 48, carbs: 5, fat: 22 }, ingredients: ['200g pechuga de pollo', '1 taza espinaca', '30g queso mozzarella', '1 diente de ajo picado', '1 cdta aceite de oliva', 'Sal, pimienta, paprika'], steps: ['Corta la pechuga por la mitad horizontalmente sin separar completamente.', 'Saltea el ajo y la espinaca hasta que se reduzca.', 'Rellena la pechuga con espinaca y queso.', 'Cierra con palillos y sazona por fuera.', 'Cocina en sarten 6 min por lado a fuego medio.'], tags: ['Alta proteina', 'Low carb'], coachTip: '48g de proteina en una sola comida. Ideal como cena post-entrenamiento nocturno.' },
  { id: 11, name: 'Sopa de Lentejas', emoji: '🍲', meal: 'Cena', goal: 'mantenimiento', prepTime: 40, servings: '3 porciones', description: 'Sopa reconfortante de lentejas con vegetales. Rica en fibra, hierro y proteina vegetal.', macros: { cal: 340, protein: 22, carbs: 48, fat: 6 }, ingredients: ['200g lentejas secas', '1 zanahoria picada', '1 papa pequena en cubos', '1/2 cebolla picada', '2 dientes de ajo', '1 tomate rallado', 'Comino, cilantro, sal'], steps: ['Remoja las lentejas 30 min y escurre.', 'Sofrie cebolla y ajo hasta dorar.', 'Agrega tomate, zanahoria y papa.', 'Anade las lentejas con 4 tazas de agua.', 'Cocina a fuego medio 25-30 min hasta que las lentejas esten tiernas.', 'Sazona con comino, cilantro y sal.'], tags: ['Vegetariano', 'Fibra', 'Batch cooking'], coachTip: 'Las lentejas son la proteina vegetal mas economica del mercado. 200g secas rinden 3 porciones completas.' },
  { id: 12, name: 'Tacos de Carne Magra', emoji: '🌮', meal: 'Cena', goal: 'energia', prepTime: 20, servings: '2 porciones', description: 'Tacos con carne molida magra 95/5 y toppings frescos. Comida divertida sin sacrificar los macros.', macros: { cal: 440, protein: 36, carbs: 34, fat: 18 }, ingredients: ['200g carne molida 95% magra', '4 tortillas de maiz', '1/2 cebolla picada', '1 tomate picado', 'Cilantro fresco', '1/2 aguacate', 'Limon, sal, comino'], steps: ['Cocina la carne con cebolla, comino y sal hasta dorar.', 'Calienta las tortillas en sarten seco.', 'Arma los tacos con carne, tomate, cilantro y aguacate.', 'Termina con un chorrito de limon.'], tags: ['Divertido', 'Social'], coachTip: 'La carne 95/5 tiene la mitad de grasa que la 80/20 pero la misma proteina. Vale la diferencia de precio.' },
  // SNACKS
  { id: 13, name: 'Batido Post-Entreno', emoji: '🥤', meal: 'Snack', goal: 'ganar_musculo', prepTime: 5, servings: '1 porcion', description: 'Batido rapido de whey protein con banano y avena. Ventana anabolica: tomar dentro de 30 min post-entreno.', macros: { cal: 380, protein: 35, carbs: 45, fat: 6 }, ingredients: ['1 scoop whey protein chocolate', '1 banano congelado', '30g avena', '250ml leche de almendras', '5 cubos de hielo'], steps: ['Agrega todos los ingredientes a la licuadora.', 'Licua a velocidad alta por 30 segundos.', 'Sirve inmediatamente.'], tags: ['Post-entreno', 'Rapido'], coachTip: 'El banano congelado le da textura de milkshake. Congela bananos maduros cortados en zip-lock para tenerlos siempre listos.' },
  { id: 14, name: 'Bolitas de Energia', emoji: '🟤', meal: 'Snack', goal: 'energia', prepTime: 15, servings: '10 bolitas', description: 'Bolitas de avena, mantequilla de mani y chocolate. Sin hornear, perfectas como pre-entreno rapido.', macros: { cal: 95, protein: 4, carbs: 10, fat: 5 }, ingredients: ['100g avena', '60g mantequilla de mani', '40g miel', '20g chips de chocolate oscuro', '1 cda semillas de chia', '1 cdta extracto de vainilla'], steps: ['Mezcla todos los ingredientes en un bowl.', 'Refrigera la mezcla 15 min para que sea mas facil de moldear.', 'Forma bolitas del tamano de una nuez con las manos.', 'Guarda en contenedor hermetico en la nevera.'], tags: ['Sin horno', 'Batch', 'Portable'], coachTip: 'Duran 7 dias en la nevera. Prepara un batch el domingo y lleva 2 al gym como pre-entreno.' },
  { id: 15, name: 'Manzana con Mantequilla de Mani', emoji: '🍎', meal: 'Snack', goal: 'mantenimiento', prepTime: 3, servings: '1 porcion', description: 'El snack mas simple y efectivo del fitness. Carbos de la fruta + grasas saludables + proteina.', macros: { cal: 250, protein: 7, carbs: 30, fat: 14 }, ingredients: ['1 manzana mediana', '1.5 cdas mantequilla de mani natural'], steps: ['Corta la manzana en laminas.', 'Unta cada lamina con mantequilla de mani.', 'Listo.'], tags: ['3 minutos', 'Sin coccion'], coachTip: 'Elige mantequilla de mani que solo tenga un ingrediente: mani. Evita las que agregan aceites y azucares.' },
  { id: 16, name: 'Palitos de Vegetales con Hummus', emoji: '🥕', meal: 'Snack', goal: 'perder_grasa', prepTime: 5, servings: '1 porcion', description: 'Vegetales crudos con hummus casero. Volumen alto, calorias bajas. Perfecto para deficit sin pasar hambre.', macros: { cal: 180, protein: 8, carbs: 22, fat: 8 }, ingredients: ['1 zanahoria en palitos', '1 pepino en palitos', '1/2 pimenton en tiras', '3 tallos de apio', '60g hummus'], steps: ['Corta todos los vegetales en palitos.', 'Sirve con el hummus al centro.', 'Sumerge y disfruta.'], tags: ['Volumen', 'Bajo en calorias'], coachTip: 'Puedes comer una bandeja entera por 180 kcal. Cuando sientas ansiedad, este snack te salva el deficit.' },
  // EXTRAS
  { id: 17, name: 'Arepa Fitness de Pollo', emoji: '🫓', meal: 'Desayuno', goal: 'ganar_musculo', prepTime: 20, servings: '1 porcion', description: 'Arepa de maiz rellena de pollo desmenuzado, aguacate y queso. Adaptacion fitness del clasico LATAM.', macros: { cal: 450, protein: 38, carbs: 40, fat: 16 }, ingredients: ['80g harina de maiz precocida', '120g pollo desmenuzado', '1/4 aguacate', '20g queso rallado', 'Sal al gusto'], steps: ['Amasa la harina con agua tibia y sal hasta consistencia suave.', 'Forma la arepa y cocina en sarten 4 min por lado.', 'Abre y rellena con pollo, aguacate y queso.'], tags: ['LATAM', 'Clasico adaptado'], coachTip: 'La harina de maiz precocida tiene menos procesamiento que la harina de trigo. Buena fuente de carbos complejos.' },
  { id: 18, name: 'Bowl de Arroz con Carne y Frijoles', emoji: '🍚', meal: 'Almuerzo', goal: 'ganar_musculo', prepTime: 30, servings: '1 porcion', description: 'Bowl completo con arroz, carne molida sazonada y frijoles negros. Proteina de dos fuentes, carbos complejos.', macros: { cal: 560, protein: 42, carbs: 58, fat: 16 }, ingredients: ['100g arroz integral', '150g carne molida magra', '80g frijoles negros cocidos', '1/2 tomate picado', 'Cilantro fresco', '1/2 limon', 'Comino, ajo, sal'], steps: ['Cocina el arroz integral.', 'Saltea la carne con ajo, comino y sal hasta dorar.', 'Calienta los frijoles con un poco de su liquido.', 'Monta el bowl: arroz, carne, frijoles, tomate y cilantro.', 'Exprime limon encima.'], tags: ['LATAM', 'Proteina completa', 'Meal prep'], coachTip: 'Arroz + frijoles = proteina completa. Los aminoacidos que le faltan al arroz, los tiene el frijol y viceversa.' },
  { id: 19, name: 'Tortilla Espanola Fit', emoji: '🥚', meal: 'Cena', goal: 'mantenimiento', prepTime: 25, servings: '2 porciones', description: 'Version fitness de la tortilla espanola. Menos aceite, misma satisfaccion. Perfecta para cenar ligero.', macros: { cal: 320, protein: 26, carbs: 22, fat: 14 }, ingredients: ['4 huevos', '1 papa mediana en laminas finas', '1/2 cebolla en laminas', '1 cda aceite de oliva', 'Sal al gusto'], steps: ['Cocina las papas y cebolla en sarten con aceite a fuego bajo 10 min.', 'Bate los huevos con sal.', 'Mezcla las papas con los huevos batidos.', 'Vierte en sarten y cocina a fuego bajo 5 min.', 'Voltea con ayuda de un plato y cocina 3 min mas.'], tags: ['Clasico adaptado', 'Economico'], coachTip: 'La version fit usa 1 cda de aceite vs las 3-4 de la receta tradicional. Misma textura, menos grasa.' },
  { id: 20, name: 'Smoothie Verde Detox', emoji: '🥬', meal: 'Snack', goal: 'perder_grasa', prepTime: 5, servings: '1 porcion', description: 'Smoothie verde con espinaca, pepino y jengibre. Bajo en calorias, alto en micronutrientes y antiinflamatorio.', macros: { cal: 120, protein: 3, carbs: 24, fat: 2 }, ingredients: ['1 taza espinaca', '1/2 pepino', '1 manzana verde', '1 cm jengibre fresco', '1/2 limon (jugo)', '200ml agua fria'], steps: ['Agrega todos los ingredientes a la licuadora.', 'Licua hasta obtener consistencia suave.', 'Sirve con hielo si deseas.'], tags: ['Detox', 'Micronutrientes', 'Antiinflamatorio'], coachTip: 'No lo uses como reemplazo de comida. Es un complemento — tus comidas principales deben tener proteina solida.' },
];

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
