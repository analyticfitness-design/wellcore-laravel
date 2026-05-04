<script setup>
import { computed } from 'vue';

const props = defineProps({
  data: { type: Object, required: true },
  delay: { type: [Number, String], default: 320 },
});
const emit = defineEmits(['chat']);

const coachInitial = computed(() => {
  if (props.data.coachInitials) return props.data.coachInitials.charAt(0);
  if (props.data.coachName) return props.data.coachName.charAt(0).toUpperCase();
  return 'C';
});

const coachRole = computed(() => props.data.coachRole || 'Tu coach');
</script>

<template>
  <section
    v-if="data.coachName"
    class="coach section grain wc-card-dashboard-coach"
    :style="{ animationDelay: typeof delay === 'number' ? delay + 'ms' : delay }"
  >
    <div class="coach-top">
      <div class="coach-av">{{ coachInitial }}</div>
      <div>
        <div class="coach-label">Tu coach</div>
        <div class="coach-name">{{ data.coachName }}</div>
        <div class="coach-role">{{ coachRole }}</div>
      </div>
    </div>
    <button class="coach-cta" @click="emit('chat')">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"></path>
      </svg>
      Enviar mensaje
    </button>
  </section>
</template>
