<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import RiseLayout from '../../layouts/RiseLayout.vue';

const api = useApi();
const router = useRouter();

const loading = ref(true);
const error = ref(null);
const data = ref(null);
const activeTab = ref('training');
const expandedWeeks = ref(new Set());

const tabs = [
    { key: 'training',   label: 'Entrenamiento' },
    { key: 'nutrition',  label: 'Nutricion' },
    { key: 'habits',     label: 'Habitos' },
];

const dayTypeBadge = {
    empuje:  'bg-orange-500/10 text-orange-400',
    jale:    'bg-blue-500/10 text-blue-400',
    piernas: 'bg-violet-500/10 text-violet-400',
    full:    'bg-emerald-500/10 text-emerald-400',
    cardio:  'bg-sky-500/10 text-sky-400',
};

function dayTypeCss(tipo) {
    return dayTypeBadge[(tipo || '').toLowerCase()] ?? 'bg-wc-bg-secondary text-wc-text-tertiary';
}

function toggleWeek(weekNum) {
    if (expandedWeeks.value.has(weekNum)) {
        expandedWeeks.value.delete(weekNum);
    } else {
        expandedWeeks.value.add(weekNum);
    }
    // trigger reactivity on Set mutation
    expandedWeeks.value = new Set(expandedWeeks.value);
}

function goWorkout(weekIdx, dayIdx) {
    router.push({ name: 'rise-workout', params: { day: dayIdx + 1 } });
}

async function fetchProgram() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/rise/program');
        data.value = response.data;
        // auto-expand the current week
        if (response.data.currentWeek) {
            expandedWeeks.value = new Set([response.data.currentWeek]);
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar el programa';
    } finally {
        loading.value = false;
    }
}

onMounted(fetchProgram);
</script>

<template>
  <RiseLayout>

    <!-- Loading -->
    <div v-if="loading" class="space-y-6">
      <div class="h-10 w-48 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      <div class="h-40 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      <div class="h-64 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
      <p class="text-sm font-medium text-wc-text">{{ error }}</p>
      <button
        @click="fetchProgram"
        class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:opacity-90"
      >
        Reintentar
      </button>
    </div>

    <!-- Main content -->
    <div v-else-if="data" class="space-y-6">

      <!-- Page header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">MI PROGRAMA</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Tu plan RISE personalizado de {{ data.totalWeeks || 12 }} semanas.</p>
      </div>

      <!-- No program state -->
      <div v-if="!data.hasProgram" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
        <p class="text-sm font-medium text-wc-text">No tienes un programa RISE activo.</p>
        <p class="mt-1 text-xs text-wc-text-tertiary">Contacta a tu coach para activar tu programa.</p>
      </div>

      <template v-else>

        <!-- Overview card -->
        <div class="relative overflow-hidden rounded-xl border border-wc-accent/20 bg-gradient-to-br from-wc-accent/[0.08] via-amber-400/[0.04] to-transparent p-5 sm:p-6">
          <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-wc-accent/[0.06]"></div>
          <div class="pointer-events-none absolute -right-3 -top-3 h-16 w-16 rounded-full bg-wc-accent/10"></div>

          <div class="relative">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-wc-accent/15 to-amber-400/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-wc-accent">
                    <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Programa RISE
                  </span>
                  <span v-if="data.status === 'active'" class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-emerald-400">Activo</span>
                </div>

                <p class="mt-2 text-sm text-wc-text-secondary">
                  <template v-if="data.startDate && data.endDate">{{ data.startDate }} — {{ data.endDate }}</template>
                  <template v-else>Plan en curso</template>
                </p>

                <div class="mt-3 flex flex-wrap gap-2">
                  <span v-if="data.experienceLevel" class="inline-flex items-center rounded-full bg-wc-bg-secondary px-2.5 py-1 text-xs font-medium text-wc-text-secondary">
                    {{ data.experienceLevel }}
                  </span>
                  <span v-if="data.trainingLocation" class="inline-flex items-center rounded-full bg-wc-bg-secondary px-2.5 py-1 text-xs font-medium text-wc-text-secondary">
                    {{ data.trainingLocation }}
                  </span>
                  <span v-if="data.trainingPlan?.frecuencia" class="inline-flex items-center rounded-full bg-wc-accent/10 px-2.5 py-1 text-xs font-medium text-wc-accent">
                    {{ data.trainingPlan.frecuencia }}
                  </span>
                </div>
              </div>

              <!-- Week counter -->
              <div class="flex shrink-0 flex-col items-end">
                <div class="flex items-baseline gap-1">
                  <span class="font-data text-4xl font-bold tabular-nums text-wc-accent">{{ data.currentWeek }}</span>
                  <span class="text-sm text-wc-text-tertiary">/ {{ data.totalWeeks }}</span>
                </div>
                <p class="text-[11px] uppercase tracking-wider text-wc-text-tertiary">Semana actual</p>
              </div>
            </div>

            <!-- Progress bar -->
            <div class="mt-5">
              <div class="mb-1.5 flex items-center justify-between text-[11px] text-wc-text-tertiary">
                <span>Progreso del programa</span>
                <span class="font-data font-semibold text-wc-accent">{{ Math.round(data.progressPct || 0) }}%</span>
              </div>
              <div class="h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                <div
                  class="h-full rounded-full bg-gradient-to-r from-wc-accent to-amber-400 transition-all duration-700"
                  :style="{ width: (data.progressPct || 0) + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-1 rounded-lg bg-wc-bg-secondary p-1">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            :class="[
              'flex-1 rounded-md px-4 py-2 text-sm font-medium transition-colors',
              activeTab === tab.key
                ? 'bg-wc-accent text-white shadow-sm'
                : 'text-wc-text-secondary hover:text-wc-text'
            ]"
          >
            {{ tab.label }}
          </button>
        </div>

        <!-- ─── Tab: Entrenamiento ─── -->
        <div v-if="activeTab === 'training'" class="space-y-3">

          <!-- Plan objective banner -->
          <div v-if="data.trainingPlan?.objetivo" class="rounded-xl border border-wc-border bg-wc-bg-secondary px-5 py-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Objetivo del plan</p>
            <p class="mt-1 text-sm text-wc-text-secondary">{{ data.trainingPlan.objetivo }}</p>
          </div>

          <!-- Weeks accordion -->
          <div
            v-for="semana in (data.trainingPlan?.semanas || [])"
            :key="semana.semana"
            class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary"
          >
            <!-- Week header -->
            <button
              @click="toggleWeek(semana.semana)"
              class="flex w-full items-center justify-between px-5 py-4 text-left transition-colors hover:bg-wc-bg-secondary/50"
            >
              <div class="flex items-center gap-3">
                <div
                  :class="[
                    'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-sm font-bold',
                    semana.semana === data.currentWeek
                      ? 'bg-wc-accent/15 text-wc-accent'
                      : 'bg-wc-bg-secondary text-wc-text-secondary'
                  ]"
                >
                  {{ semana.semana }}
                </div>
                <div>
                  <div class="flex items-center gap-2">
                    <p class="text-sm font-medium text-wc-text">Semana {{ semana.semana }}</p>
                    <span
                      v-if="semana.semana === data.currentWeek"
                      class="rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-wc-accent"
                    >Semana actual</span>
                    <span
                      v-if="semana.fase"
                      class="rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-medium text-wc-text-tertiary"
                    >{{ semana.fase }}</span>
                  </div>
                  <p v-if="semana.descripcion" class="mt-0.5 text-xs text-wc-text-tertiary">{{ semana.descripcion }}</p>
                </div>
              </div>
              <svg
                :class="['h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform', expandedWeeks.has(semana.semana) ? 'rotate-180' : '']"
                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
              </svg>
            </button>

            <!-- Week expanded content -->
            <div v-show="expandedWeeks.has(semana.semana)" class="border-t border-wc-border px-5 py-4 space-y-4">
              <div
                v-for="(dia, diaIdx) in (semana.dias || [])"
                :key="diaIdx"
                class="rounded-xl border border-wc-border bg-wc-bg-secondary overflow-hidden"
              >
                <!-- Day header -->
                <div class="flex items-start justify-between gap-3 px-4 py-3">
                  <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                      <p class="text-sm font-semibold text-wc-text">{{ dia.nombre }}</p>
                      <span
                        v-if="dia.tipo"
                        :class="['rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide', dayTypeCss(dia.tipo)]"
                      >{{ dia.tipo }}</span>
                    </div>
                    <p v-if="dia.duracion" class="mt-0.5 text-xs text-wc-text-tertiary">{{ dia.duracion }}</p>
                  </div>
                  <button
                    @click="goWorkout(semana.semana - 1, diaIdx)"
                    class="shrink-0 rounded-lg bg-wc-accent px-3 py-1.5 text-xs font-semibold text-white transition-opacity hover:opacity-90 whitespace-nowrap"
                  >
                    Entrenar este dia
                  </button>
                </div>

                <!-- Warmup -->
                <div v-if="dia.calentamiento" class="border-t border-wc-border/60 bg-wc-bg-tertiary/50 px-4 py-2">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Calentamiento</p>
                  <p class="mt-0.5 text-xs text-wc-text-secondary">{{ dia.calentamiento }}</p>
                </div>

                <!-- Exercises -->
                <div v-if="dia.ejercicios && dia.ejercicios.length > 0" class="border-t border-wc-border/60 px-4 py-3 space-y-2">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Ejercicios</p>
                  <div
                    v-for="(ej, ejIdx) in dia.ejercicios"
                    :key="ejIdx"
                    class="flex items-start gap-3"
                  >
                    <div v-if="ej.gif_url || ej.gif_filename" class="relative h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-wc-bg-secondary">
                      <img
                        :src="ej.gif_url || ('/media/gif/' + (ej.gif_filename || '').replace('.gif',''))"
                        :alt="ej.nombre"
                        class="h-full w-full object-cover"
                        loading="lazy"
                      />
                      <span class="absolute bottom-0 right-0 flex h-4 w-4 items-center justify-center rounded-tl-md bg-wc-accent/80 font-data text-[8px] font-bold text-white">{{ ejIdx + 1 }}</span>
                    </div>
                    <span v-else class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-wc-accent/10 font-data text-[10px] font-bold text-wc-accent">
                      {{ ejIdx + 1 }}
                    </span>
                    <div class="min-w-0 flex-1">
                      <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-medium text-wc-text">{{ ej.nombre }}</p>
                        <span class="rounded-full bg-wc-bg-tertiary px-2 py-0.5 font-data text-[10px] font-semibold text-wc-text-secondary">
                          {{ ej.series }}x{{ ej.repeticiones }}
                        </span>
                        <span
                          v-if="ej.descanso"
                          class="rounded-full bg-wc-bg-tertiary px-2 py-0.5 font-data text-[10px] text-wc-text-tertiary"
                        >{{ ej.descanso }}</span>
                      </div>
                      <p v-if="ej.notas" class="mt-0.5 text-[11px] text-wc-text-tertiary">{{ ej.notas }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <p v-if="!semana.dias || semana.dias.length === 0" class="py-4 text-center text-sm text-wc-text-tertiary">
                Sin dias configurados para esta semana.
              </p>
            </div>
          </div>

          <p v-if="!data.trainingPlan?.semanas || data.trainingPlan.semanas.length === 0" class="py-8 text-center text-sm text-wc-text-tertiary">
            El plan de entrenamiento no tiene semanas definidas aun.
          </p>
        </div>

        <!-- ─── Tab: Nutricion ─── -->
        <div v-else-if="activeTab === 'nutrition'" class="space-y-4">
          <template v-if="data.nutritionPlan">

            <!-- Calorias totales (card destacada) -->
            <div class="relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary px-5 py-4">
              <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-wc-accent/[0.06]"></div>
              <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Calorias diarias</p>
              <div class="mt-1 flex items-baseline gap-2">
                <span class="font-data text-3xl font-bold tabular-nums text-wc-accent">
                  {{
                    data.nutritionPlan.calorias_diarias ||
                    (
                      (data.nutritionPlan.proteina_g || 0) * 4 +
                      (data.nutritionPlan.carbohidratos_g || 0) * 4 +
                      (data.nutritionPlan.grasas_g || 0) * 9
                    )
                  }}
                </span>
                <span class="text-sm text-wc-text-tertiary">kcal / dia</span>
              </div>
            </div>

            <!-- Macros con barras de progreso -->
            <div class="space-y-3">
              <!-- Proteina -->
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
                <div class="flex items-center justify-between">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Proteina</p>
                  <div class="flex items-baseline gap-1">
                    <span class="font-data text-xl font-bold tabular-nums text-wc-text">{{ data.nutritionPlan.proteina_g }}</span>
                    <span class="text-xs text-wc-text-tertiary">g</span>
                    <span class="ml-2 rounded-full bg-wc-accent/10 px-2 py-0.5 font-data text-[10px] font-semibold text-wc-accent">
                      {{
                        Math.round(
                          ((data.nutritionPlan.proteina_g || 0) * 4) /
                          Math.max(1,
                            data.nutritionPlan.calorias_diarias ||
                            ((data.nutritionPlan.proteina_g || 0) * 4 + (data.nutritionPlan.carbohidratos_g || 0) * 4 + (data.nutritionPlan.grasas_g || 0) * 9)
                          ) * 100
                        )
                      }}%
                    </span>
                  </div>
                </div>
                <div class="mt-2.5 h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                  <div
                    class="h-full rounded-full bg-wc-accent transition-all duration-700"
                    :style="{
                      width: Math.round(
                        ((data.nutritionPlan.proteina_g || 0) * 4) /
                        Math.max(1,
                          data.nutritionPlan.calorias_diarias ||
                          ((data.nutritionPlan.proteina_g || 0) * 4 + (data.nutritionPlan.carbohidratos_g || 0) * 4 + (data.nutritionPlan.grasas_g || 0) * 9)
                        ) * 100
                      ) + '%'
                    }"
                  ></div>
                </div>
              </div>

              <!-- Carbos -->
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
                <div class="flex items-center justify-between">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Carbohidratos</p>
                  <div class="flex items-baseline gap-1">
                    <span class="font-data text-xl font-bold tabular-nums text-wc-text">{{ data.nutritionPlan.carbohidratos_g }}</span>
                    <span class="text-xs text-wc-text-tertiary">g</span>
                    <span class="ml-2 rounded-full bg-blue-400/10 px-2 py-0.5 font-data text-[10px] font-semibold text-blue-400">
                      {{
                        Math.round(
                          ((data.nutritionPlan.carbohidratos_g || 0) * 4) /
                          Math.max(1,
                            data.nutritionPlan.calorias_diarias ||
                            ((data.nutritionPlan.proteina_g || 0) * 4 + (data.nutritionPlan.carbohidratos_g || 0) * 4 + (data.nutritionPlan.grasas_g || 0) * 9)
                          ) * 100
                        )
                      }}%
                    </span>
                  </div>
                </div>
                <div class="mt-2.5 h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                  <div
                    class="h-full rounded-full bg-blue-400 transition-all duration-700"
                    :style="{
                      width: Math.round(
                        ((data.nutritionPlan.carbohidratos_g || 0) * 4) /
                        Math.max(1,
                          data.nutritionPlan.calorias_diarias ||
                          ((data.nutritionPlan.proteina_g || 0) * 4 + (data.nutritionPlan.carbohidratos_g || 0) * 4 + (data.nutritionPlan.grasas_g || 0) * 9)
                        ) * 100
                      ) + '%'
                    }"
                  ></div>
                </div>
              </div>

              <!-- Grasas -->
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
                <div class="flex items-center justify-between">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Grasas</p>
                  <div class="flex items-baseline gap-1">
                    <span class="font-data text-xl font-bold tabular-nums text-wc-text">{{ data.nutritionPlan.grasas_g }}</span>
                    <span class="text-xs text-wc-text-tertiary">g</span>
                    <span class="ml-2 rounded-full bg-amber-400/10 px-2 py-0.5 font-data text-[10px] font-semibold text-amber-400">
                      {{
                        Math.round(
                          ((data.nutritionPlan.grasas_g || 0) * 9) /
                          Math.max(1,
                            data.nutritionPlan.calorias_diarias ||
                            ((data.nutritionPlan.proteina_g || 0) * 4 + (data.nutritionPlan.carbohidratos_g || 0) * 4 + (data.nutritionPlan.grasas_g || 0) * 9)
                          ) * 100
                        )
                      }}%
                    </span>
                  </div>
                </div>
                <div class="mt-2.5 h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                  <div
                    class="h-full rounded-full bg-amber-400 transition-all duration-700"
                    :style="{
                      width: Math.round(
                        ((data.nutritionPlan.grasas_g || 0) * 9) /
                        Math.max(1,
                          data.nutritionPlan.calorias_diarias ||
                          ((data.nutritionPlan.proteina_g || 0) * 4 + (data.nutritionPlan.carbohidratos_g || 0) * 4 + (data.nutritionPlan.grasas_g || 0) * 9)
                        ) * 100
                      ) + '%'
                    }"
                  ></div>
                </div>
              </div>
            </div>

            <!-- Objective -->
            <div v-if="data.nutritionPlan.objetivo" class="rounded-xl border border-wc-border bg-wc-bg-secondary px-5 py-4">
              <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Objetivo nutricional</p>
              <p class="mt-1 text-sm text-wc-text-secondary">{{ data.nutritionPlan.objetivo }}</p>
            </div>

            <!-- Tips -->
            <div v-if="data.nutritionPlan.tips && data.nutritionPlan.tips.length > 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
              <p class="mb-3 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Consejos</p>
              <ul class="space-y-2">
                <li v-for="(tip, idx) in data.nutritionPlan.tips" :key="idx" class="flex items-start gap-2.5">
                  <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                  </svg>
                  <span class="text-sm text-wc-text-secondary">{{ tip }}</span>
                </li>
              </ul>
            </div>

            <!-- Suggested meals -->
            <div v-if="data.nutritionPlan.comidas_sugeridas && data.nutritionPlan.comidas_sugeridas.length > 0" class="space-y-3">
              <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Comidas sugeridas</p>
              <div
                v-for="(comida, idx) in data.nutritionPlan.comidas_sugeridas"
                :key="idx"
                class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4"
              >
                <p class="mb-2 text-sm font-semibold text-wc-text">{{ comida.nombre }}</p>
                <ul class="space-y-1.5">
                  <li v-for="(opcion, oidx) in (comida.opciones || [])" :key="oidx" class="flex items-start gap-2">
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-wc-accent/60"></span>
                    <span class="text-sm text-wc-text-secondary">{{ opcion }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </template>

          <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
            <p class="text-sm text-wc-text-tertiary">Sin plan de nutricion definido.</p>
          </div>
        </div>

        <!-- ─── Tab: Habitos ─── -->
        <div v-else-if="activeTab === 'habits'" class="space-y-3">
          <template v-if="data.habitsPlan && data.habitsPlan.length > 0">
            <div
              v-for="(habit, idx) in data.habitsPlan"
              :key="idx"
              class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5"
            >
              <div class="flex items-start gap-3">
                <div
                  :class="[
                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-lg',
                    idx % 3 === 0 ? 'bg-emerald-500/15' : idx % 3 === 1 ? 'bg-wc-accent/15' : 'bg-violet-500/15'
                  ]"
                >
                  <svg
                    :class="['h-5 w-5', idx % 3 === 0 ? 'text-emerald-400' : idx % 3 === 1 ? 'text-wc-accent' : 'text-violet-400']"
                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-wc-text">{{ habit.nombre }}</p>
                    <span v-if="habit.frecuencia" class="rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-medium text-wc-text-tertiary">
                      {{ habit.frecuencia }}
                    </span>
                  </div>
                  <p v-if="habit.descripcion" class="mt-1 text-sm text-wc-text-secondary">{{ habit.descripcion }}</p>
                  <div v-if="habit.razon" class="mt-2 flex items-start gap-2 rounded-lg bg-wc-bg-secondary px-3 py-2">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                    </svg>
                    <p class="text-xs text-wc-text-tertiary">{{ habit.razon }}</p>
                  </div>
                </div>
              </div>
            </div>
          </template>

          <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
            <p class="text-sm text-wc-text-tertiary">Sin habitos definidos en tu programa aun.</p>
          </div>
        </div>

      </template>
    </div>

  </RiseLayout>
</template>
