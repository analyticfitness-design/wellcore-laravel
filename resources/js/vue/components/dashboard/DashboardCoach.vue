<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useHaptics } from '../../composables/useHaptics';

const props = defineProps({
    data: { type: Object, required: true },
});

const router = useRouter();
const haptics = useHaptics();

const coachInitial = computed(() => {
    if (props.data.coachInitials) return props.data.coachInitials.charAt(0);
    if (props.data.coachName) return props.data.coachName.charAt(0).toUpperCase();
    return 'C';
});

const coachRole = computed(() => {
    return props.data.coachRole || 'Tu coach';
});

function goToChat() {
    haptics.light();
    router.push('/client/chat');
}
</script>

<template>
  <section v-if="data.coachName" class="coach section grain wc-card-dashboard-coach" :style="{ animationDelay: '320ms' }">
    <div class="coach-av">{{ coachInitial }}</div>
    <div class="coach-body">
      <div class="coach-label">Tu coach</div>
      <div class="coach-name">{{ data.coachName }}</div>
      <div class="coach-role">{{ coachRole }}</div>
    </div>
    <button class="coach-cta" @click="goToChat">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"></path>
      </svg>
      Enviar mensaje
    </button>
  </section>
</template>
