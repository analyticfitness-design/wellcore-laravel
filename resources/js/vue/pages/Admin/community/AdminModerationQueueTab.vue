<script setup>
import { ref, computed, onMounted } from 'vue';
import { useModerationQueue } from '../../../composables/useModerationQueue';
import { useToast } from '../../../composables/useToast';
import ModerationReportCard from '../../../components/admin/community/ModerationReportCard.vue';
import ModerationActionDialog from '../../../components/admin/community/ModerationActionDialog.vue';

const moderation = useModerationQueue();
const toast = useToast();

const dialogOpen = ref(false);
const dialogAction = ref('');
const dialogReport = ref(null);

const activeFilter = ref('all');
const FILTERS = [
    { key: 'all', label: 'Todos' },
    { key: 'multi', label: 'Multi-reporte' },
    { key: 'recent', label: 'Recientes' },
];

const reports = computed(() => moderation.queue.value?.data || []);

const filtered = computed(() => {
    if (activeFilter.value === 'multi') return reports.value.filter(r => r.report_count >= 3);
    if (activeFilter.value === 'recent') {
        return [...reports.value].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    }
    return reports.value;
});

function handleAction({ id, action }) {
    if (action === 'dismiss') {
        moderation.dismissReport(id)
            .then(() => toast.success('Reporte descartado.'))
            .catch((err) => toast.apiError(err, 'No pudimos descartar.'));
        return;
    }
    dialogAction.value = action;
    dialogReport.value = reports.value.find(r => r.report_id === id);
    dialogOpen.value = true;
}

async function onConfirm({ reason }) {
    try {
        await moderation.actionReport(dialogReport.value.report_id, dialogAction.value, reason);
        toast.success(`Acción ${dialogAction.value} aplicada.`);
        dialogOpen.value = false;
    } catch (err) {
        toast.apiError(err, 'No pudimos aplicar la acción.');
    }
}

function onCancel() {
    dialogOpen.value = false;
}

onMounted(() => moderation.fetchQueue());
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
      <button v-for="f in FILTERS" :key="f.key" @click="activeFilter = f.key"
        :class="activeFilter === f.key ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary'"
        class="shrink-0 rounded-full px-4 py-1.5 text-xs font-semibold">{{ f.label }}</button>
    </div>

    <div v-if="moderation.loading.value && !reports.length" class="space-y-3">
      <div v-for="i in 4" :key="i" class="h-32 rounded-xl bg-wc-bg-tertiary animate-pulse"></div>
    </div>
    <div v-else-if="moderation.error.value" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center text-sm">{{ moderation.error.value }}</div>
    <div v-else-if="!filtered.length" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-12 text-center">
      <p class="font-display text-lg text-wc-text">Cola limpia</p>
      <p class="text-sm text-wc-text-tertiary mt-2">Sin reportes pendientes. La comunidad está saludable.</p>
    </div>
    <div v-else class="space-y-3">
      <ModerationReportCard v-for="r in filtered" :key="r.report_id" :report="r" @action="handleAction" />
    </div>

    <ModerationActionDialog
      :open="dialogOpen"
      :action="dialogAction"
      :report="dialogReport"
      @confirm="onConfirm"
      @cancel="onCancel"
    />
  </div>
</template>
