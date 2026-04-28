<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { storeToRefs } from 'pinia';

import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminGreeting from '../../components/admin/dashboard/AdminGreeting.vue';
import AdminClientFilters from '../../components/admin/clients/AdminClientFilters.vue';
import AdminClientsTable from '../../components/admin/clients/AdminClientsTable.vue';
import AdminClientCardMobile from '../../components/admin/clients/AdminClientCardMobile.vue';
import AdminClientPagination from '../../components/admin/clients/AdminClientPagination.vue';

import { useAdminClientListStore } from '../../stores/adminClientList';
import { useApi } from '../../composables/useApi';

const api = useApi();
const store = useAdminClientListStore();
const { clients, loading, error, pagination, isSuperadmin, hasActiveFilters } = storeToRefs(store);

const TAGLINES = [
    'La consistencia es más valiosa que el talento.',
    'Cada cliente es una conversación con la disciplina.',
    'No hay clientes difíciles. Hay sistemas mal diseñados.',
    'El sistema funciona cuando el coach se compromete con el cliente.',
];
const tagline = TAGLINES[Math.floor(Math.random() * TAGLINES.length)];

// ─── Modales legacy (preservar funcionalidad existente) ────────────────
const showDeactivateModal = ref(false);
const deactivateTarget = ref(null);
const deactivating = ref(false);

const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const deleting = ref(false);

const toast = ref(null);
let toastTimer = null;

function showToast(type, message) {
    clearTimeout(toastTimer);
    toast.value = { type, message };
    toastTimer = setTimeout(() => { toast.value = null; }, 4000);
}

function onDeactivate(client) {
    deactivateTarget.value = client;
    showDeactivateModal.value = true;
}
function cancelDeactivate() {
    showDeactivateModal.value = false;
    deactivateTarget.value = null;
}
async function confirmDeactivate() {
    if (!deactivateTarget.value?.id) return;
    deactivating.value = true;
    try {
        await api.put(`/api/v/admin/clients/${deactivateTarget.value.id}`, { status: 'inactivo' });
        showToast('success', `Cliente "${deactivateTarget.value.name}" marcado como inactivo.`);
        cancelDeactivate();
        store.refreshSilent();
    } catch (err) {
        showToast('error', err.response?.data?.message || 'Error al desactivar cliente.');
    } finally {
        deactivating.value = false;
    }
}

function onDelete(client) {
    deleteTarget.value = client;
    showDeleteModal.value = true;
}

// ─── Impersonificación (superadmin → cliente directo) ──────────────────
const impersonating = ref(false);
async function onImpersonate(client) {
    if (!client?.id || impersonating.value) return;
    impersonating.value = true;
    try {
        const { data } = await api.post(`/api/v/coach/clients/${client.id}/impersonate`);
        // Backup actual sesión admin para restaurarla cuando cierre la impersonación.
        localStorage.setItem('wc_token_backup',     localStorage.getItem('wc_token') || '');
        localStorage.setItem('wc_user_type_backup', localStorage.getItem('wc_user_type') || '');
        localStorage.setItem('wc_user_id_backup',   localStorage.getItem('wc_user_id') || '');
        localStorage.setItem('wc_user_name_backup', localStorage.getItem('wc_user_name') || '');
        localStorage.setItem('wc_user_portal_backup', localStorage.getItem('wc_user_portal') || '/admin');
        // Swap a sesión cliente.
        localStorage.setItem('wc_token',      data.token);
        localStorage.setItem('wc_user_type',  'client');
        localStorage.setItem('wc_user_id',    String(data.client_id));
        localStorage.setItem('wc_user_name',  data.client_name || 'Cliente');
        localStorage.setItem('wc_user_portal', '/client');
        localStorage.setItem('wc_impersonating_by_coach', '1');
        localStorage.setItem('wc_impersonating_token_key', data.token);
        localStorage.setItem('wc_impersonation_client_id', String(data.client_id));
        // Hard redirect para que Pinia recargue con el nuevo token.
        window.location.href = data.redirect_url || '/client';
    } catch (err) {
        showToast('error', err.response?.data?.error || 'No se pudo impersonificar a este cliente.');
        impersonating.value = false;
    }
}
function cancelDelete() {
    showDeleteModal.value = false;
    deleteTarget.value = null;
}
async function confirmDelete() {
    if (!deleteTarget.value?.id) return;
    deleting.value = true;
    try {
        await api.delete(`/api/v/admin/clients/${deleteTarget.value.id}`);
        showToast('success', `Cliente "${deleteTarget.value.name}" eliminado permanentemente.`);
        cancelDelete();
        store.refreshSilent();
    } catch (err) {
        showToast('error', err.response?.data?.message || 'Error al eliminar cliente.');
    } finally {
        deleting.value = false;
    }
}

// ─── Export CSV ────────────────────────────────────────────────────────
const PLAN_LABEL = {
    metodo: 'Metodo', elite: 'Elite', esencial: 'Esencial',
    rise: 'Rise', presencial: 'Presencial', trial: 'Trial',
};
const STATUS_LABEL = {
    activo: 'Activo', pendiente: 'Pendiente', inactivo: 'Inactivo',
    suspendido: 'Suspendido', congelado: 'Congelado',
};

function exportCsv() {
    if (!clients.value.length) return;
    const headers = ['Nombre', 'Email', 'Codigo', 'Plan', 'Estado', 'Fecha inicio'];
    const rows = clients.value.map((c) => [
        c.name || '',
        c.email || '',
        c.client_code || '',
        PLAN_LABEL[c.plan?.value || c.plan] || c.plan || '',
        STATUS_LABEL[c.status?.value || c.status] || c.status || '',
        c.fecha_inicio || c.created_at || '',
    ]);
    const csv = [headers, ...rows]
        .map((row) => row.map((cell) => `"${String(cell).replace(/"/g, '""')}"`).join(','))
        .join('\n');
    const blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `clientes_wellcore_${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    URL.revokeObjectURL(url);
}

// ─── Greeting props ────────────────────────────────────────────────────
const greetingTotal = computed(() => pagination.value.total);

// ─── Lifecycle ─────────────────────────────────────────────────────────
onMounted(() => {
    store.hydrateFromUrl();
    store.fetchClients();
});

onBeforeUnmount(() => clearTimeout(toastTimer));
</script>

<template>
  <AdminLayout>
    <div class="clients-page">
      <!-- Header editorial -->
      <header class="page-head">
        <div class="head-text">
          <span class="eyebrow">CRM · CLIENTES</span>
          <h1 class="page-title">Clientes</h1>
          <p class="page-tagline">"{{ tagline }}"</p>
        </div>

        <div class="head-meta">
          <div class="meta-card">
            <span class="meta-label">TOTAL</span>
            <span class="meta-value">{{ greetingTotal }}</span>
          </div>
          <button
            type="button"
            class="meta-action"
            :disabled="!clients.length"
            :aria-disabled="!clients.length"
            @click="exportCsv"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
              <path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            EXPORT CSV
          </button>
        </div>
      </header>

      <!-- Filtros -->
      <AdminClientFilters />

      <!-- Toast -->
      <Teleport to="body">
        <Transition name="toast">
          <div v-if="toast" class="toast" :class="`toast--${toast.type}`" role="status">
            <span class="toast-msg">{{ toast.message }}</span>
            <button class="toast-close" type="button" @click="toast = null" aria-label="Cerrar notificación">×</button>
          </div>
        </Transition>
      </Teleport>

      <!-- Loading skeleton (solo primer fetch) -->
      <div v-if="loading && !clients.length" class="page-loading">
        <div class="skeleton-card" />
        <div class="skeleton-card" />
        <div class="skeleton-card" />
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="error-card">
        <span class="error-num">!</span>
        <p class="error-msg">{{ error }}</p>
        <button type="button" class="error-retry" @click="store.fetchClients()">REINTENTAR</button>
      </div>

      <!-- Empty state editorial -->
      <div v-else-if="!clients.length" class="empty-state">
        <div class="empty-num">—</div>
        <p class="empty-msg" v-if="hasActiveFilters">
          "Sin clientes que coincidan con los filtros.
          Probá quitando filtros o ampliando la búsqueda."
        </p>
        <p class="empty-msg" v-else>
          "Aún no hay clientes registrados en la plataforma.
          La lista se llenará a medida que el funnel de inscripción los traiga."
        </p>
        <button
          v-if="hasActiveFilters"
          type="button"
          class="empty-cta"
          @click="store.clearFilters()"
        >
          LIMPIAR FILTROS →
        </button>
      </div>

      <!-- Lista (desktop tabla / mobile cards) -->
      <template v-else>
        <div class="table-wrap">
          <AdminClientsTable
            @deactivate="onDeactivate"
            @delete="onDelete"
            @impersonate="onImpersonate"
          />
        </div>

        <div class="cards-wrap">
          <AdminClientCardMobile
            v-for="client in clients"
            :key="client.id"
            :client="client"
            @deactivate="onDeactivate"
            @delete="onDelete"
            @impersonate="onImpersonate"
          />
        </div>

        <AdminClientPagination />
      </template>

      <!-- Modal Desactivar -->
      <Teleport to="body">
        <Transition name="modal">
          <div
            v-if="showDeactivateModal"
            class="modal-shell"
            role="dialog"
            aria-modal="true"
            aria-labelledby="deact-title"
            @keydown.escape="cancelDeactivate"
          >
            <div class="modal-backdrop" @click="cancelDeactivate" />
            <div class="modal-card">
              <span class="modal-eyebrow">CAMBIO DE ESTADO</span>
              <h2 id="deact-title" class="modal-title">Desactivar cliente</h2>
              <p class="modal-body">
                Vas a marcar como <strong class="modal-em">inactivo</strong>
                a <strong class="modal-em">{{ deactivateTarget?.name }}</strong>.
                El cliente no podrá iniciar sesión, pero los datos se conservan.
                Reversible cambiando el estado manualmente.
              </p>
              <div class="modal-actions">
                <button type="button" class="modal-btn modal-btn--ghost" @click="cancelDeactivate">CANCELAR</button>
                <button
                  type="button"
                  class="modal-btn modal-btn--danger"
                  :disabled="deactivating"
                  @click="confirmDeactivate"
                >
                  {{ deactivating ? 'PROCESANDO...' : 'DESACTIVAR' }}
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>

      <!-- Modal Eliminar (Superadmin) -->
      <Teleport to="body">
        <Transition name="modal">
          <div
            v-if="showDeleteModal"
            class="modal-shell"
            role="dialog"
            aria-modal="true"
            aria-labelledby="del-title"
            @keydown.escape="cancelDelete"
          >
            <div class="modal-backdrop" @click="cancelDelete" />
            <div class="modal-card">
              <span class="modal-eyebrow modal-eyebrow--danger">ACCION IRREVERSIBLE</span>
              <h2 id="del-title" class="modal-title">Eliminar cliente</h2>
              <p class="modal-body">
                Esta operación borra a <strong class="modal-em">{{ deleteTarget?.name }}</strong>,
                sus tokens y planes asignados de la base de datos.
                <br /><strong class="modal-em">No se puede deshacer.</strong>
              </p>
              <div class="modal-actions">
                <button type="button" class="modal-btn modal-btn--ghost" @click="cancelDelete">CANCELAR</button>
                <button
                  type="button"
                  class="modal-btn modal-btn--danger"
                  :disabled="deleting"
                  @click="confirmDelete"
                >
                  {{ deleting ? 'BORRANDO...' : 'ELIMINAR' }}
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
.clients-page {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding-bottom: 20px;
}

/* ── Header editorial ──────────────────────────────────────────────── */
.page-head {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    align-items: flex-end;
    justify-content: space-between;
    padding: 4px 2px 6px;
}
.head-text {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}
.eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.page-title {
    font-family: var(--font-display, 'Bebas Neue', sans-serif);
    font-size: clamp(34px, 5vw, 52px);
    letter-spacing: 0.04em;
    color: var(--color-wc-text);
    line-height: 1;
    margin: 2px 0 0;
}
.page-tagline {
    font-family: var(--font-editorial, serif);
    font-style: italic;
    font-size: 13px;
    color: var(--color-wc-gold, #C8A769);
    margin: 0;
    max-width: 560px;
    text-wrap: balance;
}

.head-meta {
    display: flex;
    align-items: center;
    gap: 8px;
}
.meta-card {
    display: inline-flex;
    flex-direction: column;
    gap: 2px;
    padding: 8px 12px;
    border-radius: 10px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.6);
    min-width: 88px;
}
.meta-label {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.meta-value {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-feature-settings: 'tnum' 1;
    font-size: 22px;
    font-weight: 700;
    color: var(--color-wc-text);
    line-height: 1;
}
.meta-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0 12px;
    height: 38px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.6);
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.meta-action:hover:not(:disabled) {
    color: var(--color-wc-text);
    border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.12));
    background: rgba(255, 255, 255, 0.04);
}
.meta-action:disabled { opacity: 0.4; cursor: not-allowed; }

/* ── Tabla / cards toggle por viewport ─────────────────────────────── */
.table-wrap { display: none; }
.cards-wrap {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
@media (min-width: 1024px) {
    .table-wrap { display: block; }
    .cards-wrap { display: none; }
}

/* ── Loading / Error / Empty ───────────────────────────────────────── */
.page-loading {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.skeleton-card {
    height: 78px;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary, #181818);
    animation: page-pulse 1.5s ease-in-out infinite;
}
@keyframes page-pulse {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 0.9; }
}
@media (prefers-reduced-motion: reduce) {
    .skeleton-card { animation: none; opacity: 0.6; }
}

.error-card {
    border-radius: 14px;
    border: 1px solid rgba(220, 38, 38, 0.4);
    background: rgba(220, 38, 38, 0.06);
    padding: 24px;
    text-align: center;
    color: var(--color-wc-red-text, #F87171);
}
.error-num {
    display: block;
    font-family: var(--font-display);
    font-size: 56px;
    line-height: 1;
}
.error-msg { margin: 8px 0 16px; font-size: 13px; }
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

.empty-state {
    padding: 32px 8px 24px;
    text-align: center;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.5);
}
.empty-num {
    font-family: var(--font-display);
    font-size: 64px;
    color: var(--color-wc-bg-tertiary);
    letter-spacing: 0.1em;
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial, serif);
    font-style: italic;
    font-size: 13px;
    color: var(--color-wc-text-tertiary);
    line-height: 1.55;
    margin: 0 auto 16px;
    max-width: 460px;
    text-wrap: balance;
}
.empty-cta {
    background: transparent;
    border: none;
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    border-bottom: 1px solid var(--color-wc-border);
    padding-bottom: 4px;
    cursor: pointer;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.empty-cta:hover {
    color: var(--color-wc-text);
    border-bottom-color: var(--color-wc-accent, #DC2626);
}

/* ── Toast ─────────────────────────────────────────────────────────── */
.toast {
    position: fixed;
    top: 16px;
    right: 16px;
    z-index: 200;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid;
    backdrop-filter: blur(8px);
    font-family: var(--font-sans);
    font-size: 13px;
    max-width: 360px;
}
.toast--success {
    background: rgba(16, 185, 129, 0.12);
    border-color: rgba(16, 185, 129, 0.3);
    color: var(--color-wc-green-text, #34D399);
}
.toast--error {
    background: rgba(220, 38, 38, 0.12);
    border-color: rgba(220, 38, 38, 0.3);
    color: var(--color-wc-red-text, #F87171);
}
.toast-msg { flex: 1; min-width: 0; }
.toast-close {
    background: transparent;
    border: none;
    color: inherit;
    opacity: 0.7;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
}
.toast-close:hover { opacity: 1; }
.toast-enter-active,
.toast-leave-active { transition: opacity 0.18s var(--ease-out, ease), transform 0.18s var(--ease-out, ease); }
.toast-enter-from,
.toast-leave-to { opacity: 0; transform: translateY(-6px); }

/* ── Modales ───────────────────────────────────────────────────────── */
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
    max-width: 440px;
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
.modal-eyebrow--danger { color: var(--color-wc-red-text, #F87171); }
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
.modal-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    padding-top: 4px;
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
.modal-btn--danger {
    background: var(--color-wc-accent, #DC2626);
    border-color: var(--color-wc-accent, #DC2626);
    color: #fff;
}
.modal-btn--danger:hover:not(:disabled) { background: #B91C1C; border-color: #B91C1C; }
.modal-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.modal-enter-active,
.modal-leave-active { transition: opacity 0.18s var(--ease-out, ease); }
.modal-enter-from,
.modal-leave-to { opacity: 0; }
.modal-enter-active .modal-card,
.modal-leave-active .modal-card { transition: transform 0.18s var(--ease-out, ease); }
.modal-enter-from .modal-card,
.modal-leave-to .modal-card { transform: scale(0.97); }

@media (max-width: 640px) {
    .head-meta { width: 100%; justify-content: space-between; }
    .meta-card { flex: 1; }
}
</style>
