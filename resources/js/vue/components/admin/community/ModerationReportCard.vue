<script setup>
const props = defineProps({ report: { type: Object, required: true } });
const emit = defineEmits(['action']);

function timeAgo(iso) {
    if (!iso) return '';
    const diffMs = Date.now() - new Date(iso).getTime();
    return Math.round(diffMs / 3600000);
}
</script>

<template>
  <article class="rounded-xl border bg-wc-bg-secondary p-4" :class="report.report_count >= 3 ? 'border-rose-500/40' : 'border-wc-border'">
    <header class="flex items-center justify-between gap-3 mb-2">
      <div class="min-w-0 flex-1">
        <p class="text-sm font-semibold text-wc-text truncate">
          ⚠️ {{ report.report_count }} reporte{{ report.report_count > 1 ? 's' : '' }} · {{ report.post_author_name }}
        </p>
        <p class="text-xs text-wc-text-tertiary">Coach: {{ report.coach_name }} · {{ report.reason }}</p>
      </div>
      <span class="text-xs text-wc-text-tertiary shrink-0">hace {{ timeAgo(report.created_at) }}h</span>
    </header>
    <p class="text-sm text-wc-text-secondary italic line-clamp-2 mb-3">"{{ report.post_excerpt }}"</p>
    <div v-if="report.reason_detail" class="text-xs text-wc-text-tertiary mb-3">{{ report.reason_detail }}</div>
    <div class="flex flex-wrap items-center gap-2">
      <button @click="emit('action', { id: report.report_id, action: 'dismiss' })" class="rounded-full bg-wc-bg-tertiary px-3 py-1 text-xs font-semibold text-wc-text-secondary hover:bg-wc-bg">
        Dismiss
      </button>
      <button @click="emit('action', { id: report.report_id, action: 'hide' })" class="rounded-full bg-amber-500/15 px-3 py-1 text-xs font-semibold text-amber-500 hover:bg-amber-500/25">
        Hide post
      </button>
      <button @click="emit('action', { id: report.report_id, action: 'warn' })" class="rounded-full bg-blue-500/15 px-3 py-1 text-xs font-semibold text-blue-500 hover:bg-blue-500/25">
        Warn
      </button>
      <button @click="emit('action', { id: report.report_id, action: 'ban_client' })" class="rounded-full bg-rose-500/15 px-3 py-1 text-xs font-semibold text-rose-500 hover:bg-rose-500/25">
        Ban cliente
      </button>
    </div>
  </article>
</template>
