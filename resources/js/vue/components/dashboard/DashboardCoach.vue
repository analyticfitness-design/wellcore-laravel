<script setup>
import { RouterLink } from 'vue-router';
import { useHaptics } from '../../composables/useHaptics';
import WcAvatar from '../ui/wellcore/WcAvatar.vue';

defineProps({
    data: { type: Object, required: true },
});

const haptics = useHaptics();
</script>

<template>
  <div v-if="data.coachName" class="coach-card flex items-center gap-4 rounded-xl border bg-wc-bg-tertiary p-4">
    <!-- DS v1: WcAvatar con conic ring (toma la inicial del coachName auto) -->
    <WcAvatar :name="data.coachName" size="md" />
    <div class="min-w-0 flex-1">
      <p class="wc-caption">Tu coach</p>
      <p class="truncate text-base font-semibold text-wc-text">{{ data.coachName }}</p>
    </div>
    <RouterLink
      to="/client/chat"
      @click="haptics.light()"
      class="wc-btn-primary"
      style="border-radius: var(--radius-wc-pill);"
    >
      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
      </svg>
      Enviar mensaje
    </RouterLink>
  </div>
</template>
