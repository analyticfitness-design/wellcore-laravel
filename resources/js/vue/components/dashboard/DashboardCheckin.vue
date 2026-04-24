<script setup>
import { RouterLink } from 'vue-router';
import { useHaptics } from '../../composables/useHaptics';

const props = defineProps({
    data: { type: Object, required: true },
    showCheckinTimer: { type: Boolean, default: false },
    checkinHours: { type: String, default: '00' },
    checkinMinutes: { type: String, default: '00' },
    checkinSeconds: { type: String, default: '00' },
});

const haptics = useHaptics();

function handleCheckinTap() {
    // Si el check-in está URGENT (pendiente), pattern más intenso para señalizar prioridad.
    // Si es regular countdown, tap suave.
    if ((props.data.daysUntilCheckin ?? 99) <= 0) {
        haptics.pattern('success');
    } else {
        haptics.light();
    }
}
</script>

<template>
  <RouterLink
    v-if="data.daysUntilCheckin !== undefined"
    to="/client/checkin"
    @click="handleCheckinTap"
    :class="[
      'group block rounded-xl border p-4 sm:p-5 transition-colors',
      data.daysUntilCheckin <= 0
        ? 'border-wc-accent/40 bg-wc-accent/10 hover:bg-wc-accent/15'
        : data.daysUntilCheckin <= 2
          ? 'border-amber-500/40 bg-amber-500/10 hover:bg-amber-500/15'
          : 'border-emerald-500/30 bg-emerald-500/5 hover:bg-emerald-500/10'
    ]"
  >
    <div class="flex items-center gap-4">
      <!-- Icon -->
      <div :class="[
        'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl',
        data.daysUntilCheckin <= 0 ? 'bg-wc-accent/20' : data.daysUntilCheckin <= 2 ? 'bg-amber-500/20' : 'bg-emerald-500/15'
      ]">
        <svg v-if="data.daysUntilCheckin <= 0" class="h-5 w-5 text-wc-accent animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        </svg>
        <svg v-else-if="data.daysUntilCheckin <= 2" class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <svg v-else class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
        </svg>
      </div>

      <!-- Text -->
      <div class="min-w-0 flex-1">
        <p v-if="data.daysUntilCheckin <= 0" class="text-sm font-semibold uppercase tracking-wide text-wc-accent">Check-in pendiente</p>
        <p v-else-if="data.daysUntilCheckin <= 2" class="text-sm font-semibold text-amber-600 dark:text-amber-400">
          Check-in en {{ data.daysUntilCheckin }} dia{{ data.daysUntilCheckin !== 1 ? 's' : '' }}
        </p>
        <p v-else class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
          Proximo check-in en {{ data.daysUntilCheckin }} dias
        </p>
        <p v-if="data.daysUntilCheckin <= 0" class="mt-0.5 text-sm text-wc-text-secondary">Tu check-in semanal esta listo. Envialo ahora.</p>
        <p v-else class="mt-0.5 text-sm text-wc-text-secondary capitalize">{{ data.nextCheckinDate || '' }}</p>
      </div>

      <!-- Live countdown timer (if < 24h) -->
      <div
        v-if="showCheckinTimer && data.daysUntilCheckin > 0"
        :class="[
          'cd-digits hidden items-center gap-1 font-data text-lg font-bold sm:flex',
          data.daysUntilCheckin <= 2 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400'
        ]"
      >
        <span>{{ checkinHours }}</span><span class="text-wc-text-tertiary">:</span>
        <span>{{ checkinMinutes }}</span><span class="text-wc-text-tertiary">:</span>
        <span>{{ checkinSeconds }}</span>
      </div>

      <!-- Arrow -->
      <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary group-hover:text-wc-text transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
      </svg>
    </div>
  </RouterLink>
</template>
