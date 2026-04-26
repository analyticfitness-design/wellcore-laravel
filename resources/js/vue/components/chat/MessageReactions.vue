<template>
  <div class="relative group">
    <slot />

    <!-- Emoji bar trigger — only shown on hover when showTrigger is true -->
    <button
      v-if="showTrigger"
      @click.stop="expanded = !expanded"
      class="absolute -top-2 right-0 opacity-0 group-hover:opacity-100 transition-opacity rounded-full bg-wc-bg-tertiary p-1 shadow text-xs leading-none"
      aria-label="Reaccionar al mensaje"
    >
      😊
    </button>

    <!-- Emoji picker -->
    <Transition name="fade">
      <div
        v-if="expanded"
        class="absolute -top-10 right-0 flex gap-1 rounded-full bg-wc-bg-secondary border border-wc-border p-1 shadow-lg z-10"
        role="toolbar"
        aria-label="Seleccionar reaccion"
      >
        <button
          v-for="emoji in EMOJIS"
          :key="emoji"
          @click.stop="toggle(emoji)"
          class="rounded-full p-1 hover:bg-wc-bg-tertiary transition-colors text-base leading-none"
          :aria-label="`Reaccionar con ${emoji}`"
        >
          {{ emoji }}
        </button>
      </div>
    </Transition>

    <!-- Reactions count row -->
    <div v-if="reactionEntries.length" class="flex flex-wrap gap-1 mt-1">
      <button
        v-for="{ emoji, count } in reactionEntries"
        :key="emoji"
        @click.stop="toggle(emoji)"
        class="rounded-full bg-wc-bg-secondary border border-wc-border px-2 py-0.5 text-xs hover:bg-wc-bg-tertiary transition-colors leading-none"
        :aria-label="`${emoji} ${count} reacciones`"
      >
        {{ emoji }} {{ count }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useApi } from '@/composables/useApi';

const EMOJIS = ['🔥', '💪', '👏', '❤️', '😂', '👍'];

const props = defineProps<{
  messageId: number;
  reactions: Record<string, number>;
  showTrigger?: boolean;
}>();

const emit = defineEmits<{
  'update:reactions': [reactions: Record<string, number>];
}>();

const api = useApi();
const expanded = ref(false);

const reactionEntries = computed(() =>
  Object.entries(props.reactions ?? {})
    .filter(([, count]) => count > 0)
    .map(([emoji, count]) => ({ emoji, count }))
);

async function toggle(emoji: string) {
  expanded.value = false;
  try {
    const { data } = await api.post(
      `/api/v/client/chat/messages/${props.messageId}/react`,
      { emoji }
    );
    emit('update:reactions', data.counts ?? {});
  } catch {
    // Fail silently — reactions are non-critical
  }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
