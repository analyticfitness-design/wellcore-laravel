<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

interface StatsOverlay {
  volume_kg?: number;
  series?: number;
  ejercicios?: number;
  duracion_min?: number;
  day_name?: string;
}

interface Props {
  pulsoType: string;
  caption?: string;
  stats?: StatsOverlay | null;
  clientName?: string;
  compact?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  compact: false,
});

const { t } = useI18n();

const typeConfig = computed<Record<string, { emoji: string; label: string; gradient: string }>>(() => ({
  entrenamiento: { emoji: '🔥', label: t('client_social.statcard_training'),    gradient: 'from-red-950 via-zinc-900 to-black' },
  pr:            { emoji: '🏆', label: t('client_social.statcard_pr'),          gradient: 'from-yellow-900 via-zinc-900 to-black' },
  nutricion:     { emoji: '🥗', label: t('client_social.statcard_nutrition'),   gradient: 'from-green-950 via-zinc-900 to-black' },
  recuperacion:  { emoji: '😴', label: t('client_social.statcard_recovery'),    gradient: 'from-blue-950 via-zinc-900 to-black' },
  logro:         { emoji: '🏅', label: t('client_social.statcard_achievement'), gradient: 'from-purple-950 via-zinc-900 to-black' },
  libre:         { emoji: '📸', label: t('client_social.statcard_moment'),      gradient: 'from-zinc-800 via-zinc-900 to-black' },
}));

const config = computed(() => typeConfig.value[props.pulsoType] ?? typeConfig.value.libre);
</script>

<template>
  <div
    :class="[
      'relative flex flex-col items-center justify-center overflow-hidden bg-gradient-to-br text-white',
      config.gradient,
      compact ? 'h-full w-full rounded-2xl' : 'min-h-[340px] w-full rounded-2xl p-6',
    ]"
  >
    <!-- Compact: solo emoji + tipo -->
    <template v-if="compact">
      <span class="text-2xl">{{ config.emoji }}</span>
      <span class="mt-1 text-[9px] font-bold uppercase tracking-widest text-white/80">{{ config.label }}</span>
    </template>

    <!-- Full view: stats + caption -->
    <template v-else>
      <p class="mb-1 text-xs font-bold uppercase tracking-[0.3em] text-white/50">{{ config.label }}</p>
      <span class="mb-4 text-5xl">{{ config.emoji }}</span>

      <div v-if="stats && Object.values(stats).some(Boolean)" class="mb-4 grid w-full grid-cols-2 gap-2">
        <div v-if="stats.volume_kg" class="flex flex-col items-center rounded-xl bg-white/10 px-3 py-2 backdrop-blur-sm">
          <span class="text-xl font-black text-wc-accent">{{ stats.volume_kg.toLocaleString('es') }}</span>
          <span class="text-[10px] font-semibold uppercase tracking-widest text-white/60">{{ t('client_social.statcard_kg_volume') }}</span>
        </div>
        <div v-if="stats.series" class="flex flex-col items-center rounded-xl bg-white/10 px-3 py-2 backdrop-blur-sm">
          <span class="text-xl font-black text-wc-accent">{{ stats.series }}</span>
          <span class="text-[10px] font-semibold uppercase tracking-widest text-white/60">{{ t('client_social.statcard_sets') }}</span>
        </div>
        <div v-if="stats.ejercicios" class="flex flex-col items-center rounded-xl bg-white/10 px-3 py-2 backdrop-blur-sm">
          <span class="text-xl font-black text-wc-accent">{{ stats.ejercicios }}</span>
          <span class="text-[10px] font-semibold uppercase tracking-widest text-white/60">{{ t('client_social.statcard_exercises') }}</span>
        </div>
        <div v-if="stats.duracion_min" class="flex flex-col items-center rounded-xl bg-white/10 px-3 py-2 backdrop-blur-sm">
          <span class="text-xl font-black text-wc-accent">{{ stats.duracion_min }}</span>
          <span class="text-[10px] font-semibold uppercase tracking-widest text-white/60">{{ t('client_social.statcard_minutes') }}</span>
        </div>
      </div>

      <p v-if="stats?.day_name" class="mb-2 text-center text-sm font-semibold text-white/80">
        {{ stats.day_name }}
      </p>
      <p v-if="caption" class="text-center text-sm italic text-white/70">"{{ caption }}"</p>
      <p v-if="clientName" class="mt-4 text-xs text-white/40">— {{ clientName }}</p>
    </template>
  </div>
</template>
