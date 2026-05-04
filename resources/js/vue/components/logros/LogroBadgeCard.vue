<script setup>
defineProps({
  medal: { type: Object, required: true },
});

defineEmits(['click']);
</script>

<template>
  <div
    :class="['logro-card', medal.achieved ? 'is-unlocked' : 'is-locked']"
    tabindex="0"
    role="button"
    :aria-label="medal.name"
    @click="$emit('click', medal)"
    @keydown.enter="$emit('click', medal)"
  >
    <div class="logro-art">
      <svg v-if="medal.achieved" width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2 L15 9 L22 10 L17 15 L18.5 22 L12 18.5 L5.5 22 L7 15 L2 10 L9 9 Z"/>
      </svg>
      <svg v-else width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <rect x="5" y="11" width="14" height="10" rx="2"/>
        <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
      </svg>
    </div>
    <div class="logro-body">
      <div class="logro-title">{{ medal.name }}</div>
      <div class="logro-sub">{{ medal.description }}</div>
    </div>
    <div v-if="medal.achieved" class="logro-xp tnum">+{{ medal.xp }} XP</div>
  </div>
</template>
