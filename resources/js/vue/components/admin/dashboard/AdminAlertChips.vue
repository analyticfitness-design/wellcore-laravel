<script setup>
import { computed } from 'vue';
import WcAdminAlertChip from '../../ui/wellcore-admin/WcAdminAlertChip.vue';

const props = defineProps({
  alerts: { type: Array, default: () => [] },
  pendingTickets: { type: Number, default: 0 },
  reviewTickets: { type: Number, default: 0 },
});

// Si llegan alerts del API, las usamos; sino caemos a derivado de tickets.
const items = computed(() => {
  if (props.alerts && props.alerts.length) {
    return props.alerts.slice(0, 2).map((a, i) => ({
      variant: i === 0 ? 'amber' : 'blue',
      label: a.label || a.title || (i === 0 ? 'Tickets pend.' : 'En revisión'),
      value: a.value ?? a.count ?? 0,
      sub: a.sub || a.detail || '',
      icon: a.icon || (i === 0 ? 'warning' : 'clock'),
    }));
  }
  return [
    { variant: 'amber', label: 'Tickets pend.', value: props.pendingTickets, sub: props.pendingTickets > 0 ? `${props.pendingTickets} pend.` : '', icon: 'warning' },
    { variant: 'blue',  label: 'En revisión',  value: props.reviewTickets,  sub: props.reviewTickets > 0 ? 'check-in' : '',  icon: 'clock' },
  ];
});
</script>

<template>
  <section class="alerts">
    <WcAdminAlertChip
      v-for="(a, idx) in items"
      :key="idx"
      :variant="a.variant"
      :label="a.label"
      :value="a.value"
      :sub="a.sub"
    >
      <template #icon>
        <svg v-if="a.icon === 'warning'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
          <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
          <path d="M12 9v4"></path>
          <path d="M12 17h.01"></path>
        </svg>
        <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
      </template>
    </WcAdminAlertChip>
  </section>
</template>
