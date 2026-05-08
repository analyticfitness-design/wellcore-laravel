<template>
  <!-- Overlay backdrop -->
  <Teleport to="body">
    <Transition name="grocery-fade">
      <div
        v-if="open"
        class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm"
        @click="$emit('close')"
      />
    </Transition>

    <!-- Drawer bottom-sheet -->
    <Transition name="grocery-slide">
      <div
        v-if="open"
        class="fixed bottom-0 left-0 right-0 z-50 flex max-h-[90dvh] flex-col rounded-t-2xl border-t border-wc-border bg-wc-bg-secondary shadow-2xl"
      >
        <!-- Drag handle -->
        <div class="flex justify-center pt-3 pb-1 shrink-0">
          <div class="h-1 w-10 rounded-full bg-wc-border"></div>
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-3 shrink-0 border-b border-wc-border">
          <div class="flex items-center gap-2.5">
            <ShoppingCart :size="18" class="text-wc-accent shrink-0" />
            <h2 class="font-display text-sm tracking-widest uppercase text-wc-text">
              Lista de mercado
            </h2>
          </div>
          <button
            type="button"
            aria-label="Cerrar lista de mercado"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-tertiary hover:text-wc-text hover:bg-wc-bg-tertiary transition-colors"
            @click="$emit('close')"
          >
            <X :size="18" />
          </button>
        </div>

        <!-- Toggle semanal / por comida -->
        <div class="px-5 pt-4 pb-3 shrink-0">
          <div class="flex rounded-lg border border-wc-border bg-wc-bg-tertiary p-1 gap-1">
            <button
              type="button"
              class="flex-1 rounded-md py-2 text-xs font-semibold transition-colors"
              :class="view === 'category'
                ? 'bg-wc-accent text-white shadow-sm'
                : 'text-wc-text-secondary hover:text-wc-text'"
              @click="view = 'category'"
            >
              Semanal
            </button>
            <button
              type="button"
              class="flex-1 rounded-md py-2 text-xs font-semibold transition-colors"
              :class="view === 'meal'
                ? 'bg-wc-accent text-white shadow-sm'
                : 'text-wc-text-secondary hover:text-wc-text'"
              @click="view = 'meal'"
            >
              Por comida
            </button>
          </div>
          <p class="mt-2 text-[10px] text-wc-text-tertiary text-center">
            {{ view === 'category' ? 'Todo lo que necesitas mercar para tu semana' : 'Filtra por comida para mercar día a día' }}
          </p>
        </div>

        <!-- Selector de opción A / B / C -->
        <div v-if="availableOptions.length > 0" class="px-5 pb-3 shrink-0 border-b border-wc-border">
          <p class="font-display text-[9px] tracking-[0.2em] uppercase text-wc-text-tertiary mb-2.5">
            Elige tu opción esta semana
          </p>
          <div class="flex gap-2">
            <button
              v-for="opt in availableOptions"
              :key="opt"
              type="button"
              class="rounded-full border px-5 py-1.5 text-xs font-bold tracking-wide transition-all"
              :class="optionBtnClass(opt)"
              @click="activeOption = opt"
            >
              Opción {{ opt.toUpperCase() }}
            </button>
          </div>
        </div>

        <!-- Contenido scrollable -->
        <div class="overflow-y-auto flex-1 px-5 pb-6">

          <!-- Empty state -->
          <div
            v-if="isEmpty"
            class="py-12 text-center"
          >
            <p class="text-sm text-wc-text-tertiary">Tu coach está preparando el plan de nutrición.</p>
          </div>

          <!-- Vista: Semanal (por categoría) -->
          <template v-else-if="view === 'category'">
            <div
              v-for="group in byCategory"
              :key="group.key"
              class="mb-5"
            >
              <div class="mb-2 flex items-center gap-2">
                <span class="text-base leading-none">{{ group.emoji }}</span>
                <p class="font-display text-[10px] tracking-[0.18em] uppercase text-wc-text-secondary">
                  {{ group.label }}
                </p>
              </div>
              <ul class="rounded-xl border border-wc-border bg-wc-bg-tertiary divide-y divide-wc-border/50">
                <li
                  v-for="(item, idx) in group.items"
                  :key="group.key + '-' + idx"
                  class="grid grid-cols-[auto_1fr] gap-x-3 px-4 py-2.5"
                >
                  <span class="font-data text-[11px] text-wc-text-tertiary tabular-nums whitespace-nowrap text-right min-w-[56px] pt-0.5">
                    {{ item.qty || '·' }}
                  </span>
                  <div class="min-w-0">
                    <p class="text-sm text-wc-text leading-snug">{{ item.name }}</p>
                  </div>
                </li>
              </ul>
            </div>
          </template>

          <!-- Vista: Por comida -->
          <template v-else>
            <!-- Selector de comida -->
            <div class="flex gap-2 overflow-x-auto pb-3 -mx-5 px-5 no-scrollbar">
              <button
                v-for="(meal, idx) in byMeal"
                :key="idx"
                type="button"
                class="shrink-0 rounded-full border px-4 py-2 text-xs font-semibold transition-colors whitespace-nowrap"
                :class="selectedMealIdx === idx
                  ? 'border-wc-accent bg-wc-accent/10 text-wc-accent'
                  : 'border-wc-border text-wc-text-secondary hover:text-wc-text'"
                @click="selectedMealIdx = idx"
              >
                {{ meal.label }}
              </button>
            </div>

            <!-- Alimentos de la comida seleccionada -->
            <div v-if="selectedMeal">
              <div class="mb-3 flex items-baseline gap-2">
                <p class="font-display text-sm tracking-wide text-wc-text uppercase">{{ selectedMeal.label }}</p>
                <p v-if="selectedMeal.hora" class="font-data text-[11px] text-wc-text-tertiary">{{ selectedMeal.hora }}</p>
              </div>
              <ul class="rounded-xl border border-wc-border bg-wc-bg-tertiary divide-y divide-wc-border/50">
                <li
                  v-for="(item, idx) in selectedMeal.items"
                  :key="'meal-' + idx"
                  class="grid grid-cols-[auto_1fr] gap-x-3 px-4 py-2.5"
                >
                  <span class="font-data text-[11px] text-wc-text-tertiary tabular-nums whitespace-nowrap text-right min-w-[56px] pt-0.5">
                    {{ item.qty || '·' }}
                  </span>
                  <p class="text-sm text-wc-text leading-snug">{{ item.name }}</p>
                </li>
              </ul>
            </div>
          </template>

          <!-- Guía de compra — siempre visible al final -->
          <div class="mt-6 rounded-xl border border-wc-border/60 bg-wc-bg-tertiary/60 p-4">
            <p class="font-display text-[10px] tracking-[0.2em] uppercase text-wc-text-secondary mb-3">
              📋 Guía de compra
            </p>
            <ul class="space-y-2.5">
              <li
                v-for="(tip, i) in SHOPPING_TIPS"
                :key="i"
                class="flex items-start gap-2.5"
              >
                <span class="mt-0.5 h-1.5 w-1.5 shrink-0 rounded-full bg-wc-accent/60"></span>
                <p class="text-xs leading-relaxed text-wc-text-secondary">{{ tip }}</p>
              </li>
            </ul>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { ShoppingCart, X } from 'lucide-vue-next';
import { useGroceryList } from '@/composables/useGroceryList';

const props = defineProps({
  open: { type: Boolean, required: true },
  nutritionPlan: { type: Object, default: null },
});

defineEmits(['close']);

const view = ref('category');
const selectedMealIdx = ref(0);
const activeOption = ref(null);

const nutritionPlanRef = computed(() => props.nutritionPlan);
const { byCategory, byMeal, availableOptions } = useGroceryList(nutritionPlanRef, activeOption);

const isEmpty = computed(() => byCategory.value.length === 0 && byMeal.value.length === 0);

const selectedMeal = computed(() => byMeal.value[selectedMealIdx.value] ?? null);

const OPTION_COLORS = {
  a: { active: 'bg-wc-accent border-wc-accent text-white shadow-sm', inactive: 'border-red-600/50 text-red-400 hover:border-red-500' },
  b: { active: 'bg-blue-500 border-blue-500 text-white shadow-sm', inactive: 'border-blue-600/50 text-blue-400 hover:border-blue-400' },
  c: { active: 'bg-amber-500 border-amber-500 text-white shadow-sm', inactive: 'border-amber-600/50 text-amber-400 hover:border-amber-400' },
};
function optionBtnClass(opt) {
  const c = OPTION_COLORS[opt] ?? OPTION_COLORS.a;
  return activeOption.value === opt ? c.active : c.inactive;
}

// Reset al abrir
watch(() => props.open, (val) => {
  if (val) {
    view.value = 'category';
    selectedMealIdx.value = 0;
    activeOption.value = availableOptions.value[0] ?? null;
  }
});

watch(byMeal, (meals) => {
  if (selectedMealIdx.value >= meals.length) {
    selectedMealIdx.value = 0;
  }
});

// Auto-inicializar opción cuando carga el plan
watch(availableOptions, (opts) => {
  if (opts.length > 0 && !opts.includes(activeOption.value)) {
    activeOption.value = opts[0];
  }
}, { immediate: true });


const SHOPPING_TIPS = [
  'Compra proteínas frescas el mismo día que las consumas o máximo 2 días antes y guárdalas refrigeradas.',
  'Para carnes: elige cortes de color rojizo brillante, sin olor fuerte y sin líquido oscuro en el empaque.',
  'Para vegetales: busca que estén firmes, con color vivo y sin manchas oscuras ni humedad excesiva.',
  'Lee las etiquetas: el primer ingrediente es el más abundante. Evita azúcar en los primeros 3 ingredientes de productos empacados.',
  'Prefiere productos con lista de ingredientes corta: entre menos procesado, mejor calidad nutricional.',
  'Organiza tu carrito siguiendo el orden de secciones de esta lista para hacer el mercado más eficiente.',
];
</script>

<style scoped>
.grocery-fade-enter-active,
.grocery-fade-leave-active {
  transition: opacity 0.25s ease;
}
.grocery-fade-enter-from,
.grocery-fade-leave-to {
  opacity: 0;
}

.grocery-slide-enter-active,
.grocery-slide-leave-active {
  transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
}
.grocery-slide-enter-from,
.grocery-slide-leave-to {
  transform: translateY(100%);
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
