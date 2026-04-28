<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminGreeting from '../../components/admin/dashboard/AdminGreeting.vue';
import AdminToolsCatalog from '../../components/admin/tools/AdminToolsCatalog.vue';
import AdminToolRunModal from '../../components/admin/tools/AdminToolRunModal.vue';
import AdminToolHistoryDrawer from '../../components/admin/tools/AdminToolHistoryDrawer.vue';
import { useAdminToolsStore } from '../../stores/adminTools';

const store         = useAdminToolsStore();
const activeTool    = ref(null);   // tool object being run (or null = modal closed)
const historyOpen   = ref(false);

onMounted(async () => {
  await store.fetchCatalog();
});

function openRun(tool) {
  activeTool.value = tool;
}
function closeRun() {
  activeTool.value = null;
}

async function openHistory() {
  historyOpen.value = true;
  await store.fetchHistory();
}
function closeHistory() {
  historyOpen.value = false;
}
</script>

<template>
  <AdminLayout>
    <!-- Greeting -->
    <AdminGreeting
      greeting="Herramientas de sistema"
      :critical-alerts="0"
      :pending-tickets="0"
      :review-tickets="0"
    />

    <!-- Page header row -->
    <div class="tools-page-header">
      <div>
        <p class="tools-page-eyebrow">PANEL BREAK-GLASS</p>
        <p class="tools-page-hint">Utilidades de uso ocasional. Cada ejecucion queda registrada en el audit log.</p>
      </div>
      <button class="tools-history-btn" @click="openHistory" aria-label="Ver historial de ejecuciones">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        HISTORIAL
      </button>
    </div>

    <!-- Catalog -->
    <AdminToolsCatalog @run="openRun" />

    <!-- Run modal -->
    <AdminToolRunModal :tool="activeTool" @close="closeRun" />

    <!-- History drawer -->
    <AdminToolHistoryDrawer :open="historyOpen" @close="closeHistory" />
  </AdminLayout>
</template>

<style scoped>
.tools-page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.tools-page-eyebrow {
  font-family: var(--font-mono);
  font-size: 9px;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
  margin-bottom: 4px;
}
.tools-page-hint {
  font-family: var(--font-sans);
  font-size: 12px;
  color: var(--color-wc-text-secondary);
  line-height: 1.5;
  max-width: 480px;
}
.tools-history-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  height: 34px;
  padding: 0 14px;
  border-radius: 8px;
  font-family: var(--font-mono);
  font-size: 9px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--color-wc-text-secondary);
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--color-wc-border);
  cursor: pointer;
  transition: background 0.12s, border-color 0.12s, color 0.12s;
  flex-shrink: 0;
}
.tools-history-btn svg { width: 14px; height: 14px; }
.tools-history-btn:hover {
  background: rgba(255,255,255,0.06);
  border-color: var(--color-wc-border-2);
  color: var(--color-wc-text);
}
</style>
