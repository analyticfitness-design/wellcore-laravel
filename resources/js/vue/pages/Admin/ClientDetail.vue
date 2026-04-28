<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useRoute } from 'vue-router';
import { storeToRefs } from 'pinia';

import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminClientHeader from '../../components/admin/clients/AdminClientHeader.vue';
import AdminClientTabs from '../../components/admin/clients/AdminClientTabs.vue';
import AdminClientResumenPanel from '../../components/admin/clients/AdminClientResumenPanel.vue';
import AdminClientPlanPanel from '../../components/admin/clients/AdminClientPlanPanel.vue';
import AdminClientCheckinsList from '../../components/admin/clients/AdminClientCheckinsList.vue';
import AdminClientPaymentsList from '../../components/admin/clients/AdminClientPaymentsList.vue';
import AdminClientCommunicationPanel from '../../components/admin/clients/AdminClientCommunicationPanel.vue';
import AdminClientNotesPanel from '../../components/admin/clients/AdminClientNotesPanel.vue';
import AdminClientQuickActions from '../../components/admin/clients/AdminClientQuickActions.vue';

import { useAdminClientDetailStore } from '../../stores/adminClientDetail';

const route = useRoute();
const store = useAdminClientDetailStore();
const { client, loading, error, activeTab, coaches } = storeToRefs(store);

// ─── Modales ────────────────────────────────────────────────────────
const showEditModal = ref(false);
const editForm = ref({});
function openEdit() {
    editForm.value = {
        // El backend acepta status, plan, coach_id por PUT — bio/email
        // requieren un endpoint separado que aún no existe. Limitamos
        // por ahora a lo que SÍ se puede guardar.
        status: typeof client.value?.status === 'string' ? client.value.status : (client.value?.status?.value || ''),
        plan: typeof client.value?.plan === 'string' ? client.value.plan : (client.value?.plan?.value || ''),
    };
    showEditModal.value = true;
}
function cancelEdit() { showEditModal.value = false; }
async function saveEdit() {
    const payload = {};
    if (editForm.value.status) payload.status = editForm.value.status;
    if (editForm.value.plan) payload.plan = editForm.value.plan;
    const ok = await store.updateField(payload);
    if (ok) showEditModal.value = false;
}

const showCoachModal = ref(false);
const selectedCoachId = ref(null);
const assignPlanType = ref('entrenamiento');
function openCoachModal() {
    selectedCoachId.value = null;
    assignPlanType.value = 'entrenamiento';
    showCoachModal.value = true;
}
function cancelCoach() { showCoachModal.value = false; }
async function confirmCoach() {
    if (!selectedCoachId.value) return;
    const ok = await store.assignCoach({
        coachId: selectedCoachId.value,
        planType: assignPlanType.value,
    });
    if (ok) showCoachModal.value = false;
}

// Lista coaches load-balanceada (menos clientes primero) — pista editorial
const coachesByLoad = computed(() => store.coachesByLoad);

// ─── Lifecycle ───────────────────────────────────────────────────────
function loadFromRoute() {
    const id = Number(route.params.id);
    if (id) {
        store.hydrateFromUrl();
        store.openClient(id);
    }
}

watch(() => route.params.id, () => {
    loadFromRoute();
}, { immediate: false });

onMounted(loadFromRoute);

onBeforeUnmount(() => {
    store.close();
});
</script>

<template>
  <AdminLayout>
    <div class="client-detail-page">
      <!-- Header -->
      <AdminClientHeader :client="client" />

      <!-- Loading / Error / Content -->
      <div v-if="loading && !client" class="page-loading">
        <div class="skeleton skeleton--large" />
        <div class="skeleton skeleton--med" />
        <div class="skeleton skeleton--med" />
      </div>

      <div v-else-if="error" class="error-card">
        <span class="error-glyph">!</span>
        <p class="error-msg">{{ error }}</p>
        <button type="button" class="error-retry" @click="loadFromRoute">REINTENTAR</button>
      </div>

      <div v-else-if="client" class="content-grid">
        <main class="main-col">
          <AdminClientTabs />

          <div class="panel-wrap">
            <KeepAlive>
              <component
                :is="{
                  resumen: AdminClientResumenPanel,
                  plan: AdminClientPlanPanel,
                  checkins: AdminClientCheckinsList,
                  pagos: AdminClientPaymentsList,
                  comunicacion: AdminClientCommunicationPanel,
                  notas: AdminClientNotesPanel,
                }[activeTab]"
                :key="activeTab"
                :client="client"
                role="tabpanel"
                :id="`panel-${activeTab}`"
                :aria-labelledby="`tab-${activeTab}`"
              />
            </KeepAlive>
          </div>
        </main>

        <aside class="side-col">
          <AdminClientQuickActions
            :client="client"
            @edit="openEdit"
            @assign-coach="openCoachModal"
          />
        </aside>
      </div>

      <!-- Modal Edit (status/plan via PUT) -->
      <Teleport to="body">
        <Transition name="modal">
          <div
            v-if="showEditModal"
            class="modal-shell"
            role="dialog"
            aria-modal="true"
            aria-labelledby="edit-title"
            @keydown.escape="cancelEdit"
          >
            <div class="modal-backdrop" @click="cancelEdit" />
            <div class="modal-card">
              <span class="modal-eyebrow">EDICION RAPIDA</span>
              <h2 id="edit-title" class="modal-title">Editar cliente</h2>
              <p class="modal-body">
                Cambios visibles inmediatamente.
                <br /><span class="modal-em">Email y teléfono</span> requieren rol Superadmin
                con un endpoint pendiente de backend.
              </p>

              <div class="modal-form">
                <label class="modal-label" for="edit-status">ESTADO</label>
                <select id="edit-status" v-model="editForm.status" class="modal-select">
                  <option v-for="opt in store.statusOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label.toUpperCase() }}
                  </option>
                </select>

                <label class="modal-label" for="edit-plan">PLAN</label>
                <select id="edit-plan" v-model="editForm.plan" class="modal-select">
                  <option v-for="opt in store.planOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label.toUpperCase() }}
                  </option>
                </select>
              </div>

              <div class="modal-actions">
                <button type="button" class="modal-btn modal-btn--ghost" @click="cancelEdit">CANCELAR</button>
                <button type="button" class="modal-btn modal-btn--accent" @click="saveEdit">GUARDAR</button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>

      <!-- Modal Asignar Coach -->
      <Teleport to="body">
        <Transition name="modal">
          <div
            v-if="showCoachModal"
            class="modal-shell"
            role="dialog"
            aria-modal="true"
            aria-labelledby="coach-title"
            @keydown.escape="cancelCoach"
          >
            <div class="modal-backdrop" @click="cancelCoach" />
            <div class="modal-card">
              <span class="modal-eyebrow">REASIGNACION</span>
              <h2 id="coach-title" class="modal-title">Asignar coach</h2>
              <p class="modal-body">
                Lista ordenada por carga ascendente — menos clientes primero.
                <br />La acción crea o reemplaza el plan activo del tipo seleccionado.
              </p>

              <div class="modal-form">
                <label class="modal-label" for="coach-select">COACH</label>
                <select id="coach-select" v-model="selectedCoachId" class="modal-select">
                  <option :value="null">— SELECCIONAR —</option>
                  <option v-for="c in coachesByLoad" :key="c.id" :value="c.id">
                    {{ c.name }}
                  </option>
                </select>

                <label class="modal-label" for="coach-plan-type">TIPO DE PLAN</label>
                <select id="coach-plan-type" v-model="assignPlanType" class="modal-select">
                  <option value="entrenamiento">ENTRENAMIENTO</option>
                  <option value="nutricion">NUTRICION</option>
                  <option value="suplementacion">SUPLEMENTACION</option>
                  <option value="habitos">HABITOS</option>
                </select>
              </div>

              <div class="modal-actions">
                <button type="button" class="modal-btn modal-btn--ghost" @click="cancelCoach">CANCELAR</button>
                <button
                  type="button"
                  class="modal-btn modal-btn--accent"
                  :disabled="!selectedCoachId || store.savingCoach"
                  @click="confirmCoach"
                >
                  {{ store.savingCoach ? 'GUARDANDO...' : 'ASIGNAR' }}
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>
    </div>
  </AdminLayout>
</template>

<style scoped>
.client-detail-page {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding-bottom: 24px;
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
}
@media (min-width: 1024px) {
    .content-grid {
        grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
        gap: 18px;
        align-items: start;
    }
}

.main-col {
    display: flex;
    flex-direction: column;
    gap: 14px;
    min-width: 0;
}
.side-col {
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-width: 0;
}
@media (min-width: 1024px) {
    .side-col {
        position: sticky;
        top: calc(var(--admin-topbar-h, 64px) + 12px);
    }
}

.panel-wrap { min-height: 200px; }

/* ── Loading / Error ──────────────────────────────────────────────── */
.page-loading {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.skeleton {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary, #181818);
    animation: page-pulse 1.5s ease-in-out infinite;
}
.skeleton--large { height: 132px; }
.skeleton--med   { height: 96px; }
@keyframes page-pulse {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 0.9; }
}
@media (prefers-reduced-motion: reduce) {
    .skeleton { animation: none; opacity: 0.6; }
}

.error-card {
    border-radius: 14px;
    border: 1px solid rgba(220, 38, 38, 0.4);
    background: rgba(220, 38, 38, 0.06);
    padding: 24px;
    text-align: center;
    color: var(--color-wc-red-text, #F87171);
    display: flex;
    flex-direction: column;
    gap: 12px;
    align-items: center;
}
.error-glyph {
    font-family: var(--font-display);
    font-size: 40px;
    line-height: 1;
}
.error-msg {
    margin: 0;
    font-family: var(--font-sans);
    font-size: 13px;
}
.error-retry {
    background: transparent;
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-red-text, #F87171);
    padding: 8px 16px;
    border-radius: 8px;
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.2em;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease);
}
.error-retry:hover { background: rgba(255, 255, 255, 0.04); }

/* ── Modales ──────────────────────────────────────────────────────── */
.modal-shell {
    position: fixed;
    inset: 0;
    z-index: 180;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
}
.modal-card {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 460px;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary, #181818);
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.modal-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.modal-title {
    font-family: var(--font-display, sans-serif);
    font-size: 28px;
    letter-spacing: 0.04em;
    color: var(--color-wc-text);
    margin: 0;
    line-height: 1.05;
}
.modal-body {
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--color-wc-text-secondary);
    line-height: 1.55;
    margin: 0;
}
.modal-em { color: var(--color-wc-text); font-weight: 600; }

.modal-form {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 4px 0;
}
.modal-label {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    margin-top: 4px;
}
.modal-select {
    height: 36px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: rgba(255, 255, 255, 0.03);
    color: var(--color-wc-text);
    font-family: var(--font-sans);
    font-size: 13px;
    padding: 0 10px;
}
.modal-select:focus {
    outline: none;
    border-color: var(--color-wc-accent, #DC2626);
}

.modal-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    padding-top: 8px;
}
.modal-btn {
    padding: 0 16px;
    height: 36px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: transparent;
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.modal-btn--ghost:hover { background: rgba(255, 255, 255, 0.04); color: var(--color-wc-text); }
.modal-btn--accent {
    background: var(--color-wc-accent, #DC2626);
    border-color: var(--color-wc-accent, #DC2626);
    color: #fff;
}
.modal-btn--accent:hover:not(:disabled) { background: #B91C1C; border-color: #B91C1C; }
.modal-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.modal-enter-active, .modal-leave-active { transition: opacity 0.18s var(--ease-out, ease); }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active .modal-card, .modal-leave-active .modal-card { transition: transform 0.18s var(--ease-out, ease); }
.modal-enter-from .modal-card, .modal-leave-to .modal-card { transform: scale(0.97); }
</style>
