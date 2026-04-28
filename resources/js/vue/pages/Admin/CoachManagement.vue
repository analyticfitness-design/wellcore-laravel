<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminGreeting from '../../components/admin/dashboard/AdminGreeting.vue';
import AdminCoachKPIsHero from '../../components/admin/coaches/AdminCoachKPIsHero.vue';
import AdminCoachPodium from '../../components/admin/coaches/AdminCoachPodium.vue';
import AdminCoachFilters from '../../components/admin/coaches/AdminCoachFilters.vue';
import AdminCoachesTable from '../../components/admin/coaches/AdminCoachesTable.vue';
import AdminCoachCardMobile from '../../components/admin/coaches/AdminCoachCardMobile.vue';
import AdminCoachDetailDrawer from '../../components/admin/coaches/AdminCoachDetailDrawer.vue';
import AdminCoachImpersonateModal from '../../components/admin/coaches/AdminCoachImpersonateModal.vue';
import AdminCoachSuspendModal from '../../components/admin/coaches/AdminCoachSuspendModal.vue';
import AdminCoachEditModal from '../../components/admin/coaches/AdminCoachEditModal.vue';
import AdminCoachCreateModal from '../../components/admin/coaches/AdminCoachCreateModal.vue';
import AdminCoachResetModal from '../../components/admin/coaches/AdminCoachResetModal.vue';
import AdminCoachToast from '../../components/admin/coaches/AdminCoachToast.vue';
import { useAdminCoachListStore } from '../../stores/adminCoachList';
import { useAuthStore } from '../../stores/auth';

const list = useAdminCoachListStore();
const authStore = useAuthStore();

// Modales activos — solo uno puede estar abierto a la vez
const showCreate = ref(false);
const editTarget = ref(null);
const resetTarget = ref(null);
const suspendTarget = ref(null);
const impersonateTarget = ref(null);

// Toast
const toast = ref({ show: false, type: 'success', message: '' });
function showToast(message, type = 'success') {
    toast.value = { show: true, type, message };
}
function closeToast() {
    toast.value.show = false;
}

// ─── Lifecycle ────────────────────────────────────────────────────────
onMounted(() => {
    list.fetchAll();
});
onBeforeUnmount(() => {
    // Lista no tiene polling, pero por higiene
});

// ─── Action wiring ────────────────────────────────────────────────────
function onEdit(coach) {
    editTarget.value = coach;
}
function onReset(coach) {
    resetTarget.value = coach;
}
function onSuspend(coach) {
    suspendTarget.value = coach;
}
function onImpersonate(coach) {
    if (!canImpersonate(coach)) return;
    impersonateTarget.value = coach;
}

function canImpersonate(coach) {
    if (!coach) return false;
    if (coach.role === 'superadmin') return false;
    if (String(coach.id) === String(authStore.userId)) return false;
    return true;
}

function onCreateSuccess(payload) {
    showCreate.value = false;
    showToast(`Coach creado. Credenciales enviadas a ${payload.email}.`);
    list.refreshSilent();
}

function onEditSuccess() {
    editTarget.value = null;
    showToast('Cambios guardados.');
    list.refreshSilent();
}

function onResetSuccess(payload) {
    resetTarget.value = null;
    showToast(`Contrasena temporal enviada a ${payload.email}.`);
}

function onSuspendSuccess(coach) {
    suspendTarget.value = null;
    showToast(`Coach ${coach.name} desactivado.`, 'success');
    list.refreshSilent();
}

function onImpersonateSuccess() {
    impersonateTarget.value = null;
    // El componente hace window.location.href = '/coach' tras success
}

// ─── UI helpers ────────────────────────────────────────────────────────
const initialPaint = computed(() => list.loading && list.coaches.length === 0);
const refreshHint = computed(() => {
    const s = list.secondsSinceRefresh;
    if (s === null) return '';
    if (s < 10) return 'Actualizado ahora';
    if (s < 60) return `Hace ${s}s`;
    if (s < 3600) return `Hace ${Math.floor(s / 60)} min`;
    return 'Hace 1h+';
});
</script>

<template>
  <AdminLayout>
    <AdminGreeting :greeting="'Coaches'" :critical-alerts="0" />

    <!-- Sub-header con eyebrow + acciones -->
    <div class="page-meta">
      <span class="page-eyebrow">EQUIPO OPERATIVO</span>
      <div class="meta-actions">
        <span v-if="refreshHint" class="poll-hint">{{ refreshHint }}</span>
        <button class="btn-primary" type="button" @click="showCreate = true">
          <span aria-hidden="true">+</span>
          NUEVO COACH
        </button>
      </div>
    </div>

    <!-- KPIs hero -->
    <AdminCoachKPIsHero class="page-block" :kpis="list.kpis" />

    <!-- Podium top 3 + lista 4-5 -->
    <AdminCoachPodium class="page-block" :coaches="list.topMonth" />

    <!-- Filtros -->
    <AdminCoachFilters class="page-block" />

    <!-- Loading skeleton (first paint) -->
    <div v-if="initialPaint" class="page-loading page-block">
      <div class="page-loading-bar"></div>
      <div class="page-loading-grid">
        <div v-for="i in 4" :key="i" class="page-loading-card"></div>
      </div>
    </div>

    <!-- Error state -->
    <div v-else-if="list.error" class="error-card page-block">
      <span class="error-eyebrow">ERROR</span>
      <p class="error-msg">{{ list.error }}</p>
      <button class="btn-primary" type="button" @click="list.fetchAll()">Reintentar</button>
    </div>

    <!-- Empty state editorial -->
    <div v-else-if="list.coaches.length === 0" class="empty-card page-block">
      <div class="empty-num">—</div>
      <p class="empty-msg">
        "Sin coaches activos en este filtro. Buscas por nombre o cambias el estado del filtro?"
      </p>
      <button v-if="list.hasActiveFilters" class="empty-cta" type="button" @click="list.clearFilters()">
        VER TODOS →
      </button>
    </div>

    <!-- Lista: tabla desktop / cards mobile -->
    <template v-else>
      <AdminCoachesTable
        class="page-block hidden lg:block"
        @edit="onEdit"
        @reset="onReset"
        @suspend="onSuspend"
        @impersonate="onImpersonate"
      />

      <div class="page-block mobile-stack lg:hidden">
        <AdminCoachCardMobile
          v-for="c in list.sortedCoaches"
          :key="c.id"
          :coach="c"
          @edit="onEdit"
          @reset="onReset"
          @suspend="onSuspend"
          @impersonate="onImpersonate"
        />
      </div>
    </template>

    <!-- Singletons globales -->
    <AdminCoachDetailDrawer />
    <AdminCoachCreateModal
      :show="showCreate"
      @close="showCreate = false"
      @success="onCreateSuccess"
    />
    <AdminCoachEditModal
      :coach="editTarget"
      @close="editTarget = null"
      @success="onEditSuccess"
    />
    <AdminCoachResetModal
      :coach="resetTarget"
      @close="resetTarget = null"
      @success="onResetSuccess"
    />
    <AdminCoachSuspendModal
      :coach="suspendTarget"
      @close="suspendTarget = null"
      @success="onSuspendSuccess"
    />
    <AdminCoachImpersonateModal
      :coach="impersonateTarget"
      @close="impersonateTarget = null"
      @success="onImpersonateSuccess"
    />
    <AdminCoachToast
      :show="toast.show"
      :type="toast.type"
      :message="toast.message"
      @close="closeToast"
    />
  </AdminLayout>
</template>

<style scoped>
/* ── Sub-header ──────────────────────────────────────────────────────── */
.page-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    padding: 6px 0 14px;
}
.page-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.meta-actions {
    display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
}
.poll-hint {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.btn-primary {
    background: var(--color-wc-accent, #DC2626);
    border: 1px solid var(--color-wc-accent, #DC2626);
    color: #fff;
    border-radius: 10px;
    padding: 9px 16px;
    font-family: var(--font-sans);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.04em;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.15s var(--ease-out, ease);
}
.btn-primary:hover { background: #B91C1C; }
.btn-primary span[aria-hidden] {
    font-family: var(--font-display);
    font-size: 16px;
    line-height: 1;
}

.page-block { margin-bottom: 12px; }
@media (min-width: 1024px) {
    .page-block { margin-bottom: 20px; }
}

.mobile-stack {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* ── Loading skeleton ──────────────────────────────────────────────────── */
.page-loading {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.page-loading-bar {
    height: 36px;
    background: var(--color-wc-bg-tertiary);
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    animation: page-pulse 1.5s ease-in-out infinite;
}
.page-loading-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}
@media (min-width: 768px) {
    .page-loading-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1024px) {
    .page-loading-grid { grid-template-columns: repeat(4, 1fr); }
}
.page-loading-card {
    height: 124px;
    background: var(--color-wc-bg-tertiary);
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    animation: page-pulse 1.5s ease-in-out infinite;
}
@keyframes page-pulse {
    0%, 100% { opacity: 0.6; }
    50%      { opacity: 0.9; }
}

/* ── Error / empty cards ──────────────────────────────────────────────── */
.error-card {
    border-radius: 14px;
    border: 1px solid rgba(220, 38, 38, 0.22);
    background: rgba(220, 38, 38, 0.07);
    padding: 22px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.error-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--color-wc-red-text, #F87171);
}
.error-msg {
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--color-wc-text);
    margin: 0;
}

.empty-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 32px 18px 24px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--color-wc-bg-tertiary);
    letter-spacing: 0.1em;
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 13px;
    color: var(--color-wc-text-tertiary);
    line-height: 1.55;
    margin: 0 0 16px;
    max-width: 480px;
    text-wrap: balance;
}
.empty-cta {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.22em;
    color: var(--color-wc-text-secondary);
    background: transparent;
    border: none;
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

@media (prefers-reduced-motion: reduce) {
    .page-loading-bar,
    .page-loading-card { animation: none !important; }
}
</style>
