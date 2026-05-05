<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useBroadcast } from '../../../composables/useBroadcast';
import { useToast } from '../../../composables/useToast';
import { useHaptics } from '../../../composables/useHaptics';
import BroadcastPreviewBar from '../../../components/admin/community/BroadcastPreviewBar.vue';
import BroadcastHistoryList from '../../../components/admin/community/BroadcastHistoryList.vue';

const broadcast = useBroadcast();
const toast = useToast();
const haptics = useHaptics();

const showConfirmStep = ref(false);

const charLimit = 2000;
const subjectLimit = 255;
const charCount = computed(() => broadcast.body.value.length);
const charOver = computed(() => charCount.value > charLimit);
const subjectOver = computed(() => broadcast.subject.value.length > subjectLimit);

const canSend = computed(() => {
    return !broadcast.sending.value
        && broadcast.body.value.trim().length > 0
        && !charOver.value
        && !subjectOver.value;
});

const PLAN_OPTIONS = ['rise', 'metodo', 'elite', 'esencial', 'presencial'];
const STATUS_OPTIONS = ['activo', 'inactivo', 'pendiente'];

watch(() => [broadcast.audience.value, JSON.stringify(broadcast.segment.value)], () => {
    broadcast.previewCount();
});

async function attemptSend() {
    if (!canSend.value) return;
    if ((broadcast.recipientCount.value || 0) > 50) {
        showConfirmStep.value = true;
        return;
    }
    await doSend();
}

async function doSend() {
    showConfirmStep.value = false;
    try {
        const res = await broadcast.send();
        haptics.success();
        toast.success(`Broadcast enviado a ${res?.recipients_count ?? broadcast.recipientCount.value} recipientes.`);
        broadcast.fetchHistory();
    } catch (err) {
        haptics.error();
        toast.apiError(err, 'No pudimos enviar el broadcast.');
    }
}

function togglePlan(plan) {
    if (!broadcast.segment.value.plan) {
        broadcast.segment.value.plan = [plan];
        return;
    }
    const idx = broadcast.segment.value.plan.indexOf(plan);
    if (idx >= 0) {
        broadcast.segment.value.plan = broadcast.segment.value.plan.filter(p => p !== plan);
        if (!broadcast.segment.value.plan.length) broadcast.segment.value.plan = null;
    } else {
        broadcast.segment.value.plan = [...broadcast.segment.value.plan, plan];
    }
}

function isPlanSelected(plan) {
    return broadcast.segment.value.plan?.includes(plan);
}

onMounted(() => {
    broadcast.previewCount();
    broadcast.fetchHistory();
});
</script>

<template>
  <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-5">
    <div class="space-y-4">
      <div v-if="!showConfirmStep" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5 space-y-4">
        <h3 class="font-display text-xl text-wc-text">Composer</h3>

        <div>
          <label class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary block mb-2">
            Audiencia
          </label>
          <div class="grid grid-cols-3 rounded-lg border border-wc-border overflow-hidden">
            <button @click="broadcast.audience.value = 'clients'"
              :class="broadcast.audience.value === 'clients' ? 'bg-wc-accent text-white' : 'bg-wc-bg text-wc-text-secondary'"
              class="py-2 text-sm font-semibold">Clientes</button>
            <button @click="broadcast.audience.value = 'coaches'"
              :class="broadcast.audience.value === 'coaches' ? 'bg-wc-accent text-white' : 'bg-wc-bg text-wc-text-secondary'"
              class="py-2 text-sm font-semibold">Coaches</button>
            <button @click="broadcast.audience.value = 'all_communities'"
              :class="broadcast.audience.value === 'all_communities' ? 'bg-wc-accent text-white' : 'bg-wc-bg text-wc-text-secondary'"
              class="py-2 text-sm font-semibold">All</button>
          </div>
        </div>

        <div v-if="broadcast.audience.value === 'clients'" class="space-y-3">
          <div>
            <label class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary block mb-2">
              Plan
            </label>
            <div class="flex flex-wrap gap-2">
              <button v-for="p in PLAN_OPTIONS" :key="p" @click="togglePlan(p)"
                :class="isPlanSelected(p) ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary'"
                class="rounded-full px-3 py-1 text-xs font-semibold">{{ p }}</button>
            </div>
          </div>
        </div>

        <div>
          <label class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary block mb-2">
            Asunto (opcional)
          </label>
          <input v-model="broadcast.subject.value" :maxlength="subjectLimit"
            class="w-full rounded-lg border border-wc-border bg-wc-bg p-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" />
        </div>

        <div>
          <label class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary block mb-2">
            Mensaje
          </label>
          <textarea v-model="broadcast.body.value" :maxlength="charLimit" rows="6"
            placeholder="Escribe tu mensaje al equipo..."
            class="w-full rounded-lg border border-wc-border bg-wc-bg p-3 text-sm text-wc-text resize-none focus:border-wc-accent focus:outline-none"></textarea>
          <div class="mt-1 flex justify-end text-[11px]" :class="charOver ? 'text-rose-500' : 'text-wc-text-tertiary'">
            {{ charCount }} / {{ charLimit }}
          </div>
        </div>

        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" v-model="broadcast.pushEnabled.value" class="accent-wc-accent" />
          <span class="text-sm text-wc-text-secondary">Enviar también como push notification</span>
        </label>

        <BroadcastPreviewBar :count="broadcast.recipientCount.value" :sending="broadcast.sending.value" :can-send="canSend" @send="attemptSend" />
      </div>

      <div v-else class="rounded-2xl border border-amber-500/30 bg-amber-500/5 p-6 text-center space-y-3">
        <div class="text-4xl">⚠️</div>
        <h3 class="font-display text-2xl text-wc-text">¿Confirmar envío?</h3>
        <p class="text-sm text-wc-text-secondary">
          Vas a enviar a <strong class="text-wc-text">{{ broadcast.recipientCount.value }}</strong> recipientes.
        </p>
        <p v-if="broadcast.pushEnabled.value" class="text-xs text-wc-text-tertiary">Push notification activado.</p>
        <div class="flex gap-3 justify-center">
          <button @click="showConfirmStep = false" class="rounded-full border border-wc-border text-wc-text-secondary px-5 py-2">
            Cancelar
          </button>
          <button @click="doSend" :disabled="broadcast.sending.value" class="rounded-full bg-wc-accent text-white px-5 py-2 font-semibold disabled:opacity-50">
            Sí, enviar
          </button>
        </div>
      </div>
    </div>

    <div>
      <BroadcastHistoryList :history="broadcast.history.value" />
    </div>
  </div>
</template>
