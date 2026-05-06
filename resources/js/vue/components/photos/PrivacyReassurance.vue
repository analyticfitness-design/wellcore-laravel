<script setup>
/**
 * PrivacyReassurance — strip with lock icon + headline + 3 trust chips.
 * Mandatory anywhere body photos appear.
 *
 * Props:
 *   coachName: string   (default 'Marina Pérez')
 *   compact:   bool     dense single-line variant for hero/sticky areas
 *
 * Voice: tuteo neutro, NO mention of AI/ML/Claude. Coach is human.
 */
defineProps({
  coachName: { type: String, default: 'Marina Pérez' },
  compact: { type: Boolean, default: false },
});
</script>

<template>
  <div
    class="rounded-xl border border-wc-border bg-wc-bg-tertiary"
    :class="compact ? 'flex items-center gap-3 px-3 py-2.5' : 'flex items-start gap-3 p-4'"
  >
    <div
      class="flex shrink-0 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary"
      :class="compact ? 'h-8 w-8' : 'h-10 w-10'"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true">
        <rect x="4" y="11" width="16" height="10" rx="2" />
        <path d="M8 11V7a4 4 0 0 1 8 0v4" />
      </svg>
    </div>

    <div class="min-w-0 flex-1">
      <h4
        class="font-display uppercase tracking-wider text-wc-text"
        :class="compact ? 'text-[11px]' : 'text-xs'"
      >
        Solo {{ coachName }} ve estas fotos
      </h4>
      <p
        v-if="!compact"
        class="mt-1 text-xs leading-snug text-wc-text-secondary"
      >
        Privadas, encriptadas, tuyas. Tu coach las usa para guiarte mejor — nadie más tiene acceso.
      </p>
      <div
        class="flex flex-wrap gap-1.5"
        :class="compact ? '' : 'mt-2'"
      >
        <span class="inline-flex items-center gap-1 rounded-full border border-wc-border bg-wc-bg-secondary px-2 py-0.5 text-[10px] text-wc-text-secondary">
          {{ coachName }}
        </span>
        <span class="inline-flex items-center gap-1 rounded-full border border-wc-border bg-wc-bg-secondary px-2 py-0.5 text-[10px] text-wc-text-secondary">
          AES-256 en reposo
        </span>
        <a
          href="/privacy"
          class="inline-flex items-center gap-1 rounded-full border border-wc-border bg-wc-bg-secondary px-2 py-0.5 text-[10px] text-wc-text-secondary transition-colors hover:text-wc-text"
        >
          Política de privacidad →
        </a>
      </div>
    </div>
  </div>
</template>
