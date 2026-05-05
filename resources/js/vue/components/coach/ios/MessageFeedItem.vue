<script setup>
import AvatarConic from './AvatarConic.vue';
import PRBadge from './PRBadge.vue';

defineProps({
  clientName: { type: String, required: true },
  clientInitial: { type: String, default: '' },
  avatarTone: {
    type: String,
    default: 'gold',
    validator: v => ['accent', 'gold', 'purple'].includes(v),
  },
  imageUrl: { type: String, default: '' },
  timeAgo: { type: String, default: '' },
  body: { type: String, default: '' },
  isPR: { type: Boolean, default: false },
  isUnread: { type: Boolean, default: false },
});

const emit = defineEmits(['click']);
</script>

<template>
  <button
    class="msg-row w-full flex items-start gap-2.5 p-3 px-4 cursor-pointer transition relative text-left active:bg-[var(--s2)] hover:bg-[var(--s2)]"
    style="transition-duration: var(--t-tap);"
    @click="emit('click')"
  >
    <AvatarConic
      :initial="clientInitial || clientName.charAt(0).toUpperCase()"
      :tone="avatarTone"
      :image-url="imageUrl"
      size="sm"
    />

    <div class="flex-1 min-w-0">
      <div class="flex items-baseline justify-between gap-1.5 mb-0.5">
        <span class="text-[13px] font-semibold text-wc-text truncate">{{ clientName }}</span>
        <span v-if="timeAgo" class="text-[10px] text-[var(--color-wc-text-3)] flex-shrink-0">{{ timeAgo }}</span>
      </div>
      <p class="text-[12px] text-[var(--color-wc-text-2)] leading-[1.4] truncate">
        <PRBadge v-if="isPR" />
        {{ body }}
      </p>
    </div>

    <span
      v-if="isUnread"
      class="w-2 h-2 rounded-full bg-wc-accent flex-shrink-0 mt-1.5"
      aria-label="Sin leer"
    />
  </button>
</template>
