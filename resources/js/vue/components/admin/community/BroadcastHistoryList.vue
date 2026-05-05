<script setup>
import { ref } from 'vue';

defineProps({ history: { type: Array, default: () => [] } });
const expandedId = ref(null);

function toggle(id) {
    expandedId.value = expandedId.value === id ? null : id;
}

function timeAgo(iso) {
    if (!iso) return '';
    const diffMs = Date.now() - new Date(iso).getTime();
    const minutes = Math.floor(diffMs / 60000);
    if (minutes < 60) return `hace ${minutes}m`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `hace ${hours}h`;
    return `hace ${Math.floor(hours / 24)}d`;
}
</script>

<template>
  <div class="space-y-2">
    <h3 class="text-xs uppercase tracking-widest text-wc-text-tertiary mb-2">Historial</h3>
    <div v-if="!history.length" class="text-sm text-wc-text-tertiary text-center py-8">Sin broadcasts aún.</div>
    <article v-for="b in history" :key="b.id" class="rounded-xl border border-wc-border bg-wc-bg-secondary">
      <button @click="toggle(b.id)" class="w-full px-4 py-3 text-left">
        <div class="flex items-center justify-between gap-3">
          <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-wc-text truncate">{{ b.subject || (b.body || '').slice(0, 60) }}</p>
            <p class="text-xs text-wc-text-tertiary">
              {{ b.audience_type }} · {{ b.recipients_count }} recipientes
            </p>
          </div>
          <span class="text-xs text-wc-text-tertiary shrink-0">{{ timeAgo(b.sent_at) }}</span>
        </div>
      </button>
      <Transition enter-active-class="duration-200" enter-from-class="opacity-0 max-h-0" enter-to-class="opacity-100 max-h-96">
        <div v-if="expandedId === b.id" class="border-t border-wc-border px-4 py-3 text-sm text-wc-text-secondary whitespace-pre-wrap">
          <p>{{ b.body }}</p>
          <p class="text-xs text-wc-text-tertiary mt-2">Delivered: {{ b.delivered_count }}/{{ b.recipients_count }} · push: {{ b.push_enabled ? 'sí' : 'no' }}</p>
        </div>
      </Transition>
    </article>
  </div>
</template>
