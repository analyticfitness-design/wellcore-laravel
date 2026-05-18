<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

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

const STATUS_CLASSES = {
    sent:         'bg-zinc-700 text-zinc-300',
    opened:       'bg-blue-900/50 text-blue-300',
    link_clicked: 'bg-amber-900/50 text-amber-300',
    paid:         'bg-green-900/50 text-green-300',
    expired:      'bg-red-900/50 text-red-300',
    cancelled:    'bg-zinc-800 text-zinc-500',
    failed:       'bg-red-950 text-red-400',
};

const STATUS_KEYS = {
    sent:         'badge_sent',
    opened:       'badge_opened',
    link_clicked: 'badge_link_clicked',
    paid:         'badge_paid',
    expired:      'badge_expired',
    cancelled:    'badge_cancelled',
    failed:       'badge_failed',
};

const config = computed(() => ({
    cls: STATUS_CLASSES[props.status] ?? 'bg-zinc-700 text-zinc-300',
    label: STATUS_KEYS[props.status] ? t(`coach_growth.invitations.${STATUS_KEYS[props.status]}`) : props.status,
}));
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
    :class="config.cls"
  >
    {{ config.label }}
  </span>
</template>
