<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminGreeting from '../../components/admin/dashboard/AdminGreeting.vue';
import AdminAIBriefForm from '../../components/admin/ai-generator/AdminAIBriefForm.vue';
import AdminAIStreamingOutput from '../../components/admin/ai-generator/AdminAIStreamingOutput.vue';
import AdminAIPlanPreview from '../../components/admin/ai-generator/AdminAIPlanPreview.vue';
import AdminAIPlanActions from '../../components/admin/ai-generator/AdminAIPlanActions.vue';
import AdminAITemplatesPanel from '../../components/admin/ai-generator/AdminAITemplatesPanel.vue';
import AdminAIHistoryDrawer from '../../components/admin/ai-generator/AdminAIHistoryDrawer.vue';
import { useAIStream } from '../../composables/useAIStream';
import { useAdminAIGeneratorStore } from '../../stores/adminAIGenerator';

const store = useAdminAIGeneratorStore();
const stream = useAIStream();

const historyOpen = ref(false);

const greeting = computed(() => {
    const userName = localStorage.getItem('wc_user_name') || 'Admin';
    const hour = new Date().getHours();
    const part = hour < 12 ? 'Buenos días' : hour < 19 ? 'Buenas tardes' : 'Buenas noches';
    return `${part}, ${userName}`;
});

// When the stream finishes, mirror its result into the store so other panels react.
watch(
    () => stream.lastHistoryId.value,
    (id) => {
        if (id) {
            store.setCurrent({
                historyId: id,
                text: stream.accumulated.value,
                brief: { ...store.brief },
            });
            store.fetchHistory();
        }
    }
);

async function handleGenerate() {
    if (stream.isStreaming.value) return;
    // Reset visible output without losing the brief.
    stream.accumulated.value = '';
    store.setCurrent({ historyId: null, text: '', brief: { ...store.brief } });

    await stream.start(buildPayload(store.brief), {
        onChunk: (_, full) => { /* output reactivity is handled inside composable */ },
        onDone: () => { /* watcher above persists currentHistoryId */ },
    });
}

function buildPayload(b) {
    const p = {
        plan_type: b.plan_type,
        duration_weeks: b.duration_weeks,
    };
    if (b.methodology) p.methodology = b.methodology;
    if (b.frequency) p.frequency = b.frequency;
    if (b.experience_level) p.experience_level = b.experience_level;
    if (b.training_goal) p.training_goal = b.training_goal;
    if (b.injuries) p.injuries = b.injuries;
    if (b.preferences) p.preferences = b.preferences;
    if (b.calorie_target) p.calorie_target = b.calorie_target;
    if (b.meals_per_day) p.meals_per_day = b.meals_per_day;
    if (b.dietary_restrictions) p.dietary_restrictions = b.dietary_restrictions;
    if (b.habit_focus_areas?.length) p.habit_focus_areas = b.habit_focus_areas;
    if (b.target_client_id) p.target_client_id = b.target_client_id;
    return p;
}

function handleStop() {
    stream.abort();
}

function handleRetry() {
    handleGenerate();
}

function handleApproved() {
    // Approved flow: clear current state so next generation starts fresh.
    stream.accumulated.value = '';
    store.setCurrent({ historyId: null, text: '', brief: store.brief });
}

function handleDiscarded() {
    stream.accumulated.value = '';
    store.setCurrent({ historyId: null, text: '', brief: store.brief });
}

function handleHistoryLoad(row) {
    // When a history entry is loaded into "current" by the drawer, mirror its
    // text into the streaming output so the user can preview it.
    stream.accumulated.value = store.currentText;
    if (store.currentBrief) {
        store.setBrief(store.currentBrief);
    }
}

function handleTemplateLoad(t) {
    // Templates only carry plan_type + methodology. The store action already
    // applied them; nothing else to do here.
}

onBeforeUnmount(() => stream.abort());

// Output text shown in streaming card: live during stream, store text otherwise.
const displayText = computed(() => stream.accumulated.value || store.currentText);
const displayHistoryId = computed(() => stream.lastHistoryId.value || store.currentHistoryId);
const showPreview = computed(() => !stream.isStreaming.value && displayText.value.length > 200);
</script>

<template>
  <AdminLayout>
    <AdminGreeting
      :greeting="greeting"
      :critical-alerts="0"
      :pending-tickets="0"
      :review-tickets="0"
    />

    <!-- Page actions row -->
    <div class="ai-page-actions">
      <p class="ai-page-eyebrow">SISTEMA ASISTIDO · DRAFTS</p>
      <button type="button" class="ai-history-btn" @click="historyOpen = true">
        Historial ({{ store.history.length || '—' }})
      </button>
    </div>

    <div class="ai-grid">
      <!-- Left column: form + templates -->
      <div class="ai-col-left">
        <AdminAIBriefForm
          :is-streaming="stream.isStreaming.value"
          @generate="handleGenerate"
        />
        <AdminAITemplatesPanel @load-template="handleTemplateLoad" />
      </div>

      <!-- Right column: streaming + preview + actions -->
      <div class="ai-col-right">
        <AdminAIStreamingOutput
          :text="displayText"
          :is-streaming="stream.isStreaming.value"
          :error="stream.error.value"
          :duration-ms="stream.lastDurationMs.value"
          @stop="handleStop"
          @retry="handleRetry"
        />
        <AdminAIPlanPreview
          v-if="showPreview"
          :text="displayText"
        />
        <AdminAIPlanActions
          :text="displayText"
          :history-id="displayHistoryId"
          @approved="handleApproved"
          @discarded="handleDiscarded"
        />
      </div>
    </div>

    <AdminAIHistoryDrawer
      :open="historyOpen"
      @close="historyOpen = false"
      @load="handleHistoryLoad"
    />
  </AdminLayout>
</template>

<style scoped>
.ai-page-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 14px 0 12px;
    gap: 10px;
}
.ai-page-eyebrow {
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.22em;
    color: var(--color-wc-text-tertiary);
    text-transform: uppercase;
    margin: 0;
}
.ai-history-btn {
    height: 32px;
    padding: 0 14px;
    border-radius: 999px;
    border: 1px solid var(--color-wc-border);
    background: transparent;
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out), border-color 0.15s var(--ease-out), color 0.15s var(--ease-out);
}
.ai-history-btn:hover {
    border-color: var(--color-wc-border-2);
    color: var(--color-wc-text);
}

.ai-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
}
.ai-col-left,
.ai-col-right {
    display: flex;
    flex-direction: column;
    gap: 14px;
    min-width: 0;
}

@media (min-width: 1024px) {
    .ai-grid {
        grid-template-columns: minmax(0, 1fr) minmax(0, 2fr);
        gap: 18px;
        align-items: start;
    }
}
</style>
