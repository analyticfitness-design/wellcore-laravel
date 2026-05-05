<script setup>
const props = defineProps({
    client: { type: Object, required: true },
});

const emit = defineEmits(['quick-message']);

function openQuickMessage() {
    emit('quick-message', props.client);
}
</script>

<template>
  <div class="flex items-center gap-3 rounded-xl border border-rose-500/20 bg-rose-500/5 px-4 py-3">
    <div class="h-9 w-9 rounded-full bg-rose-500/15 flex items-center justify-center text-rose-500 shrink-0">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
      </svg>
    </div>
    <div class="flex-1 min-w-0">
      <p class="text-sm font-semibold text-wc-text truncate">{{ client.name || client.client_name }}</p>
      <p class="text-xs text-rose-500 dark:text-rose-400">
        <span v-if="client.days_inactive">{{ client.days_inactive }}d sin actividad</span>
        <span v-else>Sin actividad reciente</span>
      </p>
    </div>
    <button
      @click="openQuickMessage"
      class="shrink-0 rounded-full bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 px-3 py-1.5 text-xs font-semibold transition-colors"
    >
      Mensaje
    </button>
  </div>
</template>
