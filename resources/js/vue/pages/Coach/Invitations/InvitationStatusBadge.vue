<script setup>
/**
 * @typedef {'sent'|'opened'|'link_clicked'|'paid'|'expired'|'cancelled'|'failed'} InvitationStatus
 */

const props = defineProps({
    /** @type {InvitationStatus} */
    status: {
        type: String,
        required: true,
    },
});

const STATUS_CONFIG = {
    sent:         { label: 'Enviada',       classes: 'bg-zinc-700 text-zinc-300' },
    opened:       { label: 'Abierta',       classes: 'bg-blue-900/50 text-blue-300' },
    link_clicked: { label: 'Link visitado', classes: 'bg-amber-900/50 text-amber-300' },
    paid:         { label: 'Pagada',        classes: 'bg-green-900/50 text-green-300' },
    expired:      { label: 'Expirada',      classes: 'bg-red-900/50 text-red-300' },
    cancelled:    { label: 'Cancelada',     classes: 'bg-zinc-800 text-zinc-500' },
    failed:       { label: 'Fallida',       classes: 'bg-red-950 text-red-400' },
};
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
    :class="STATUS_CONFIG[status]?.classes ?? 'bg-zinc-700 text-zinc-300'"
  >
    {{ STATUS_CONFIG[status]?.label ?? status }}
  </span>
</template>
