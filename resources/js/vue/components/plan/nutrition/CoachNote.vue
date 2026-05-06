<template>
  <article
    class="relative rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 sm:p-6"
  >
    <!-- Decorative quote glyph (Oswald renders the curly quote with editorial weight) -->
    <span
      aria-hidden="true"
      class="pointer-events-none absolute -top-2 -left-1 select-none font-display text-6xl leading-none text-wc-accent/20"
    >&#8220;</span>

    <!-- Note body -->
    <p
      class="relative pl-4 text-base italic leading-relaxed text-wc-text sm:pl-6"
    >
      {{ note }}
    </p>

    <!-- Footer: avatar + name + role + timestamp -->
    <footer class="mt-5 flex items-center gap-3 pl-4 sm:pl-6">
      <!-- Avatar: photo with graceful fallback to initials -->
      <img
        v-if="showPhoto"
        :src="coachAvatar"
        :alt="coachName"
        loading="lazy"
        decoding="async"
        @error="onAvatarError"
        class="h-9 w-9 flex-shrink-0 rounded-full object-cover ring-1 ring-wc-border"
      />
      <div
        v-else
        aria-hidden="true"
        class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-wc-accent/10 font-display text-sm tracking-wider text-wc-accent"
      >
        {{ initials }}
      </div>

      <div class="min-w-0">
        <p class="font-display text-sm uppercase tracking-wide text-wc-text">
          {{ coachName }}
        </p>
        <p class="text-xs text-wc-text-tertiary">
          <span>{{ coachRole }}</span>
          <template v-if="timestamp">
            <span class="mx-1">&middot;</span>
            <span>{{ timestamp }}</span>
          </template>
        </p>
      </div>
    </footer>
  </article>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  note: { type: String, required: true },
  coachName: { type: String, default: 'Tu coach' },
  coachRole: { type: String, default: 'Coach de nutrición' },
  coachAvatar: { type: String, default: null },
  timestamp: { type: String, default: null },
});

const avatarFailed = ref(false);

// Reset failure flag if the parent swaps the URL.
watch(
  () => props.coachAvatar,
  () => {
    avatarFailed.value = false;
  },
);

function onAvatarError() {
  avatarFailed.value = true;
}

const showPhoto = computed(
  () => Boolean(props.coachAvatar) && !avatarFailed.value,
);

const initials = computed(() => {
  const name = (props.coachName || '').trim();
  if (!name) return 'C';
  const parts = name.split(/\s+/).filter(Boolean);
  const letters = parts.slice(0, 2).map((p) => p.charAt(0).toUpperCase());
  return letters.join('') || 'C';
});
</script>
