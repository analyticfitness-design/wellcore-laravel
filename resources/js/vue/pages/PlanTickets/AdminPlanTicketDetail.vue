<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminPlanTicketCommentsThread from '../../components/admin/plan-tickets/AdminPlanTicketCommentsThread.vue';
import AdminPlanTicketApproveModal from '../../components/admin/plan-tickets/AdminPlanTicketApproveModal.vue';
import AdminPlanTicketRejectModal from '../../components/admin/plan-tickets/AdminPlanTicketRejectModal.vue';
import { useAdminPlanTicketDetailStore } from '../../stores/adminPlanTicketDetail';
import { useAdminPlanTicketsListStore } from '../../stores/adminPlanTicketsList';

const route = useRoute();
const router = useRouter();
const store = useAdminPlanTicketDetailStore();
const listStore = useAdminPlanTicketsListStore();

const ticketId = computed(() => Number(route.params.id));

const TABS_BASE = [
    { key: 'cliente',         label: 'Cliente' },
    { key: 'entrenamiento',   label: 'Entrenamiento' },
    { key: 'nutricion',       label: 'Nutricion' },
    { key: 'habitos',         label: 'Habitos' },
    { key: 'suplementacion',  label: 'Suplementacion' },
    { key: 'comments',        label: 'Comentarios' },
];

const TABS_ELITE_EXTRA = { key: 'ciclo', label: 'Ciclo' };

const tabs = computed(() => {
    if (store.ticket?.plan_type === 'elite' && store.ticket?.plan_ciclo) {
        // Insertar Ciclo antes de Comments
        const t = [...TABS_BASE];
        t.splice(5, 0, TABS_ELITE_EXTRA);
        return t;
    }
    return TABS_BASE;
});

const activeTab = ref('cliente');
const tablistRef = ref(null);

function setTab(key) {
    activeTab.value = key;
    nextTick(() => {
        const el = tablistRef.value?.querySelector(`[data-tab="${key}"]`);
        if (el && typeof el.scrollIntoView === 'function') {
            try { el.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' }); } catch {}
        }
    });
}

// ─── modals ─────────────────────────────────────────────────────────
const approveOpen = ref(false);
const rejectOpen = ref(false);
const actionError = ref('');

function openApprove() { actionError.value = ''; approveOpen.value = true; }
function openReject()  { actionError.value = ''; rejectOpen.value = true; }

async function confirmApprove({ adminNotes, generatedPlanIds, forceCompleteWithoutPlans }) {
    actionError.value = '';
    try {
        const fresh = await store.approve({ adminNotes, generatedPlanIds, forceCompleteWithoutPlans });
        approveOpen.value = false;
        if (fresh) listStore.applyTicketUpdate(fresh);
    } catch (err) {
        actionError.value = err.response?.data?.error || err.response?.data?.message || 'No se pudo aprobar el ticket.';
    }
}

async function confirmReject({ rejectionCode, adminNotes }) {
    actionError.value = '';
    try {
        const fresh = await store.reject({ rejectionCode, adminNotes });
        rejectOpen.value = false;
        if (fresh) listStore.applyTicketUpdate(fresh);
    } catch (err) {
        actionError.value = err.response?.data?.error || err.response?.data?.message || 'No se pudo rechazar el ticket.';
    }
}

async function postComment(body) {
    try {
        await store.addComment(body);
    } catch (err) {
        // El componente CommentsThread maneja su error; aqui solo no rompemos.
        console.warn('[plan-ticket-detail] add comment failed', err);
    }
}

// ─── status helpers ─────────────────────────────────────────────────
const STATUS_LABEL = {
    pendiente:    'Pendiente',
    en_revision:  'En revision',
    completado:   'Aprobado',
    rechazado:    'Rechazado',
    borrador:     'Borrador',
};
const STATUS_VARIANT = {
    pendiente:   'amber',
    en_revision: 'blue',
    completado:  'green',
    rechazado:   'red',
    borrador:    'muted',
};
const PLAN_LABEL = {
    esencial: 'Esencial', metodo: 'Metodo', elite: 'Elite',
    rise: 'Rise', presencial: 'Presencial', trial: 'Trial',
};

const statusLabel = computed(() => STATUS_LABEL[store.ticket?.status] ?? '—');
const statusVariant = computed(() => STATUS_VARIANT[store.ticket?.status] ?? 'muted');
const planLabel = computed(() => PLAN_LABEL[store.ticket?.plan_type] ?? '—');

function formatLong(iso) {
    if (!iso) return '—';
    try {
        return new Date(iso).toLocaleString('es-CO', {
            day: 'numeric', month: 'short', year: 'numeric',
            hour: '2-digit', minute: '2-digit',
        });
    } catch { return '—'; }
}

// ─── adaptive section render ─────────────────────────────────────────
// Convierte cualquier valor en una representacion estable, sin asumir schema.
function isPrimitive(v) {
    return v === null || v === undefined || typeof v === 'string' || typeof v === 'number' || typeof v === 'boolean';
}
function humanKey(k) {
    if (typeof k !== 'string') return String(k);
    return k.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}
function renderableEntries(obj) {
    if (!obj || typeof obj !== 'object') return [];
    return Object.entries(obj).filter(([, v]) => v !== null && v !== undefined && v !== '');
}

// ─── export JSON (para motor v2) ─────────────────────────────────────
const exportLoading = ref(false);
const exportFeedback = ref('');
let exportFeedbackTimer = null;

function flashExportFeedback(msg, ms = 2400) {
    exportFeedback.value = msg;
    if (exportFeedbackTimer) clearTimeout(exportFeedbackTimer);
    exportFeedbackTimer = setTimeout(() => { exportFeedback.value = ''; }, ms);
}

async function copyExportJson() {
    if (exportLoading.value) return;
    exportLoading.value = true;
    try {
        const data = await store.fetchExport('full');
        const text = JSON.stringify(data, null, 2);
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(text);
        } else {
            // Fallback navegadores antiguos / contextos sin permiso
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
        }
        flashExportFeedback('JSON copiado al portapapeles');
    } catch (err) {
        flashExportFeedback(err.response?.data?.error || 'No se pudo copiar el JSON');
    } finally {
        exportLoading.value = false;
    }
}

async function downloadExportJson() {
    if (exportLoading.value) return;
    exportLoading.value = true;
    try {
        const data = await store.fetchExport('full');
        const text = JSON.stringify(data, null, 2);
        const blob = new Blob([text], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        const safeName = (store.ticket?.client_name || 'cliente').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        a.href = url;
        a.download = `plan-ticket-${store.ticket?.id ?? 'x'}-${safeName}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        flashExportFeedback('JSON descargado');
    } catch (err) {
        flashExportFeedback(err.response?.data?.error || 'No se pudo descargar el JSON');
    } finally {
        exportLoading.value = false;
    }
}

// ─── lifecycle ───────────────────────────────────────────────────────
onMounted(async () => {
    if (Number.isFinite(ticketId.value)) {
        await store.loadAll(ticketId.value);
        store.startCommentsPolling(60000);
    }
});

watch(ticketId, async (id) => {
    if (Number.isFinite(id)) {
        await store.loadAll(id);
    }
});

onBeforeUnmount(() => {
    store.stopCommentsPolling();
    store.$resetState();
    if (exportFeedbackTimer) clearTimeout(exportFeedbackTimer);
});
</script>

<template>
    <AdminLayout>
        <div class="pt-detail-page">
            <p class="pt-detail-eyebrow">
                <RouterLink :to="{ name: 'admin-plan-tickets' }" class="pt-detail-back">
                    <span aria-hidden="true">←</span> COLA DE TICKETS
                </RouterLink>
                <span class="pt-detail-eyebrow-sep" aria-hidden="true">/</span>
                <span>TICKET #{{ ticketId }}</span>
            </p>

            <!-- Loading -->
            <div v-if="store.loading && !store.ticket" class="pt-detail-loading" aria-live="polite">
                <div class="pt-detail-skeleton pt-detail-skeleton--lg"></div>
                <div class="pt-detail-skeleton"></div>
                <div class="pt-detail-skeleton pt-detail-skeleton--block"></div>
            </div>

            <!-- Error -->
            <div v-else-if="store.error && !store.ticket" class="pt-detail-error" role="alert">
                <p class="pt-detail-error-msg">{{ store.error }}</p>
                <RouterLink :to="{ name: 'admin-plan-tickets' }" class="pt-detail-error-link">
                    Volver a la cola →
                </RouterLink>
            </div>

            <!-- Detail body -->
            <template v-else-if="store.ticket">
                <!-- Header card -->
                <header class="pt-detail-header">
                    <div class="pt-detail-header-main">
                        <h1 class="pt-detail-title">{{ store.ticket.client_name || 'Cliente sin nombre' }}</h1>
                        <p class="pt-detail-tagline">
                            "{{ store.ticket.coach_name || `Coach #${store.ticket.coach_id}` }} entrego este plan
                            para revision. Tu decision lo activa o lo regresa con guidance."
                        </p>
                        <div class="pt-detail-tags">
                            <span class="pt-detail-tag pt-detail-tag--plan" :class="`pt-detail-tag--plan-${store.ticket.plan_type}`">{{ planLabel }}</span>
                            <span class="pt-detail-tag pt-detail-tag--status" :class="`pt-detail-tag--status-${statusVariant}`">{{ statusLabel }}</span>
                            <span v-if="store.ticket.category" class="pt-detail-tag pt-detail-tag--category">{{ humanKey(store.ticket.category) }}</span>
                        </div>
                    </div>
                    <dl class="pt-detail-meta">
                        <div class="pt-detail-meta-row">
                            <dt>ENTREGADO</dt>
                            <dd>{{ formatLong(store.ticket.submitted_at) }}</dd>
                        </div>
                        <div v-if="store.ticket.reviewed_at" class="pt-detail-meta-row">
                            <dt>EN REVISION DESDE</dt>
                            <dd>{{ formatLong(store.ticket.reviewed_at) }}</dd>
                        </div>
                        <div v-if="store.ticket.completed_at" class="pt-detail-meta-row">
                            <dt>APROBADO</dt>
                            <dd>{{ formatLong(store.ticket.completed_at) }}</dd>
                        </div>
                        <div v-if="store.ticket.rejected_at" class="pt-detail-meta-row">
                            <dt>RECHAZADO</dt>
                            <dd>{{ formatLong(store.ticket.rejected_at) }}</dd>
                        </div>
                        <div v-if="store.ticket.deadline_at" class="pt-detail-meta-row">
                            <dt>DEADLINE</dt>
                            <dd>{{ formatLong(store.ticket.deadline_at) }}</dd>
                        </div>

                        <div class="pt-detail-export">
                            <span class="pt-detail-export-label">EXPORT JSON · MOTOR V2</span>
                            <div class="pt-detail-export-actions">
                                <button
                                    type="button"
                                    class="pt-export-btn"
                                    :disabled="exportLoading"
                                    title="Copia el JSON completo al portapapeles (cliente, profile, intake, planes previos, check-ins, coach brief, instrucciones)"
                                    @click="copyExportJson"
                                >
                                    {{ exportLoading ? '…' : 'Copiar' }}
                                </button>
                                <button
                                    type="button"
                                    class="pt-export-btn pt-export-btn--primary"
                                    :disabled="exportLoading"
                                    title="Descarga el JSON completo como archivo .json"
                                    @click="downloadExportJson"
                                >
                                    {{ exportLoading ? '…' : 'Descargar .json' }}
                                </button>
                            </div>
                            <p v-if="exportFeedback" class="pt-detail-export-feedback" role="status" aria-live="polite">
                                {{ exportFeedback }}
                            </p>
                        </div>
                    </dl>
                </header>

                <!-- Tabs -->
                <nav ref="tablistRef" class="pt-detail-tabs" role="tablist" aria-label="Secciones del ticket">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        role="tab"
                        :aria-selected="activeTab === tab.key"
                        :data-tab="tab.key"
                        class="pt-detail-tab"
                        :class="{ 'pt-detail-tab--active': activeTab === tab.key }"
                        @click="setTab(tab.key)"
                    >
                        {{ tab.label }}
                    </button>
                </nav>

                <!-- Tab panels -->
                <section class="pt-detail-panel" :data-tab-active="activeTab" role="tabpanel">
                    <!-- CLIENTE -->
                    <div v-if="activeTab === 'cliente'" class="pt-section">
                        <div class="pt-section-head">
                            <span class="pt-section-eyebrow">DATOS GENERALES DEL CLIENTE</span>
                        </div>
                        <div v-if="renderableEntries(store.ticket.datos_generales).length === 0" class="pt-empty">
                            <p class="pt-empty-msg">"El coach no incluyo datos generales en este ticket."</p>
                        </div>
                        <dl v-else class="pt-defs">
                            <div v-for="[k, v] in renderableEntries(store.ticket.datos_generales)" :key="k" class="pt-def">
                                <dt class="pt-def-label">{{ humanKey(k) }}</dt>
                                <dd class="pt-def-value">
                                    <template v-if="isPrimitive(v)">{{ v }}</template>
                                    <pre v-else class="pt-def-pre">{{ JSON.stringify(v, null, 2) }}</pre>
                                </dd>
                            </div>
                        </dl>

                        <div v-if="store.ticket.notas_coach" class="pt-coach-notes">
                            <span class="pt-section-eyebrow">NOTAS DEL COACH</span>
                            <p class="pt-coach-notes-body">{{ store.ticket.notas_coach }}</p>
                        </div>
                    </div>

                    <!-- ENTRENAMIENTO -->
                    <div v-else-if="activeTab === 'entrenamiento'" class="pt-section">
                        <div class="pt-section-head"><span class="pt-section-eyebrow">PLAN DE ENTRENAMIENTO</span></div>
                        <div v-if="renderableEntries(store.ticket.plan_entrenamiento).length === 0" class="pt-empty">
                            <p class="pt-empty-msg">"Sin contenido de entrenamiento. El coach posiblemente lo dejo vacio."</p>
                        </div>
                        <dl v-else class="pt-defs">
                            <div v-for="[k, v] in renderableEntries(store.ticket.plan_entrenamiento)" :key="k" class="pt-def">
                                <dt class="pt-def-label">{{ humanKey(k) }}</dt>
                                <dd class="pt-def-value">
                                    <template v-if="isPrimitive(v)">{{ v }}</template>
                                    <pre v-else class="pt-def-pre">{{ JSON.stringify(v, null, 2) }}</pre>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- NUTRICION -->
                    <div v-else-if="activeTab === 'nutricion'" class="pt-section">
                        <div class="pt-section-head"><span class="pt-section-eyebrow">PLAN NUTRICIONAL</span></div>
                        <div v-if="renderableEntries(store.ticket.plan_nutricional).length === 0" class="pt-empty">
                            <p class="pt-empty-msg">"Sin contenido nutricional."</p>
                        </div>
                        <dl v-else class="pt-defs">
                            <div v-for="[k, v] in renderableEntries(store.ticket.plan_nutricional)" :key="k" class="pt-def">
                                <dt class="pt-def-label">{{ humanKey(k) }}</dt>
                                <dd class="pt-def-value">
                                    <template v-if="isPrimitive(v)">{{ v }}</template>
                                    <pre v-else class="pt-def-pre">{{ JSON.stringify(v, null, 2) }}</pre>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- HABITOS -->
                    <div v-else-if="activeTab === 'habitos'" class="pt-section">
                        <div class="pt-section-head"><span class="pt-section-eyebrow">PLAN DE HABITOS</span></div>
                        <div v-if="renderableEntries(store.ticket.plan_habitos).length === 0" class="pt-empty">
                            <p class="pt-empty-msg">"Sin habitos prescritos en este ticket."</p>
                        </div>
                        <dl v-else class="pt-defs">
                            <div v-for="[k, v] in renderableEntries(store.ticket.plan_habitos)" :key="k" class="pt-def">
                                <dt class="pt-def-label">{{ humanKey(k) }}</dt>
                                <dd class="pt-def-value">
                                    <template v-if="isPrimitive(v)">{{ v }}</template>
                                    <pre v-else class="pt-def-pre">{{ JSON.stringify(v, null, 2) }}</pre>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- SUPLEMENTACION -->
                    <div v-else-if="activeTab === 'suplementacion'" class="pt-section">
                        <div class="pt-section-head"><span class="pt-section-eyebrow">PROTOCOLO DE SUPLEMENTACION</span></div>
                        <div v-if="renderableEntries(store.ticket.plan_suplementacion).length === 0" class="pt-empty">
                            <p class="pt-empty-msg">"Sin protocolo de suplementacion en este ticket."</p>
                        </div>
                        <dl v-else class="pt-defs">
                            <div v-for="[k, v] in renderableEntries(store.ticket.plan_suplementacion)" :key="k" class="pt-def">
                                <dt class="pt-def-label">{{ humanKey(k) }}</dt>
                                <dd class="pt-def-value">
                                    <template v-if="isPrimitive(v)">{{ v }}</template>
                                    <pre v-else class="pt-def-pre">{{ JSON.stringify(v, null, 2) }}</pre>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- CICLO (Elite only) -->
                    <div v-else-if="activeTab === 'ciclo'" class="pt-section">
                        <div class="pt-section-head"><span class="pt-section-eyebrow">CICLO ELITE</span></div>
                        <div v-if="renderableEntries(store.ticket.plan_ciclo).length === 0" class="pt-empty">
                            <p class="pt-empty-msg">"Sin ciclo prescrito."</p>
                        </div>
                        <dl v-else class="pt-defs">
                            <div v-for="[k, v] in renderableEntries(store.ticket.plan_ciclo)" :key="k" class="pt-def">
                                <dt class="pt-def-label">{{ humanKey(k) }}</dt>
                                <dd class="pt-def-value">
                                    <template v-if="isPrimitive(v)">{{ v }}</template>
                                    <pre v-else class="pt-def-pre">{{ JSON.stringify(v, null, 2) }}</pre>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- COMMENTS -->
                    <div v-else-if="activeTab === 'comments'" class="pt-section">
                        <AdminPlanTicketCommentsThread
                            :comments="store.comments"
                            :loading="store.loadingComments"
                            :error="store.commentsError"
                            :submitting="store.actionInFlight === 'comment'"
                            @post="postComment"
                        />
                    </div>
                </section>

                <!-- Action footer -->
                <footer
                    v-if="store.canTakeAction"
                    class="pt-detail-footer"
                >
                    <button
                        type="button"
                        class="pt-action-btn pt-action-btn--reject"
                        :disabled="!!store.actionInFlight"
                        @click="openReject"
                    >
                        Devolver al coach
                    </button>
                    <button
                        type="button"
                        class="pt-action-btn pt-action-btn--approve"
                        :disabled="!!store.actionInFlight"
                        @click="openApprove"
                    >
                        Aprobar y activar plan
                    </button>
                </footer>
                <footer v-else class="pt-detail-footer pt-detail-footer--readonly">
                    <p class="pt-detail-readonly-msg">
                        "Este ticket ya esta {{ statusLabel.toLowerCase() }}. Solo lectura."
                    </p>
                </footer>
            </template>
        </div>

        <!-- Modals -->
        <AdminPlanTicketApproveModal
            :open="approveOpen"
            :ticket="store.ticket"
            :submitting="store.actionInFlight === 'approve'"
            :error="actionError"
            @close="approveOpen = false"
            @confirm="confirmApprove"
        />
        <AdminPlanTicketRejectModal
            :open="rejectOpen"
            :ticket="store.ticket"
            :submitting="store.actionInFlight === 'reject'"
            :error="actionError"
            @close="rejectOpen = false"
            @confirm="confirmReject"
        />
    </AdminLayout>
</template>

<style scoped>
.pt-detail-page {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding-top: 8px;
    padding-bottom: 80px;   /* hueco para el footer sticky */
    min-width: 0;
}

.pt-detail-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    margin: 0;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.pt-detail-back {
    color: var(--color-wc-text-secondary);
    text-decoration: none;
    border-bottom: 1px solid var(--color-wc-border);
    padding-bottom: 1px;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.pt-detail-back:hover { color: var(--color-wc-text); border-bottom-color: var(--color-wc-accent, #DC2626); }
.pt-detail-eyebrow-sep { opacity: 0.4; }

/* ─── header ─── */
.pt-detail-header {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
    padding: 18px 18px 16px;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
}
@media (min-width: 1024px) {
    .pt-detail-header { grid-template-columns: 1fr 320px; gap: 24px; padding: 22px; }
}
.pt-detail-header-main { display: flex; flex-direction: column; gap: 8px; min-width: 0; }
.pt-detail-title {
    font-family: var(--font-display);
    font-size: 32px;
    letter-spacing: 0.04em;
    color: var(--color-wc-text);
    margin: 0;
    line-height: 1;
    text-transform: uppercase;
}
@media (min-width: 1024px) { .pt-detail-title { font-size: 44px; } }

.pt-detail-tagline {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 12.5px;
    line-height: 1.55;
    color: var(--color-wc-gold, #C8A769);
    margin: 0;
    text-wrap: balance;
    max-width: 64ch;
}

.pt-detail-tags { display: flex; flex-wrap: wrap; gap: 8px; }
.pt-detail-tag {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    padding: 4px 9px;
    border-radius: 4px;
    border: 1px solid transparent;
}
.pt-detail-tag--plan-esencial   { background: rgba(59, 130, 246, 0.10);  color: var(--color-wc-blue-text, #60A5FA);  border-color: rgba(59, 130, 246, 0.20); }
.pt-detail-tag--plan-metodo     { background: rgba(245, 158, 11, 0.10);  color: var(--color-wc-amber-text, #FCD34D); border-color: rgba(245, 158, 11, 0.20); }
.pt-detail-tag--plan-elite      { background: rgba(220, 38, 38, 0.10);   color: var(--color-wc-red-text, #F87171);   border-color: rgba(220, 38, 38, 0.20); }
.pt-detail-tag--plan-rise       { background: rgba(200, 167, 105, 0.10); color: var(--color-wc-gold, #C8A769);       border-color: rgba(200, 167, 105, 0.22); }
.pt-detail-tag--plan-presencial,
.pt-detail-tag--plan-trial      { background: rgba(255, 255, 255, 0.04); color: var(--color-wc-text-secondary);      border-color: var(--color-wc-border); }

.pt-detail-tag--status-amber  { background: rgba(245, 158, 11, 0.10); color: var(--color-wc-amber-text); border-color: rgba(245, 158, 11, 0.20); }
.pt-detail-tag--status-blue   { background: rgba(59, 130, 246, 0.10); color: var(--color-wc-blue-text);  border-color: rgba(59, 130, 246, 0.20); }
.pt-detail-tag--status-green  { background: rgba(16, 185, 129, 0.10); color: var(--color-wc-green-text); border-color: rgba(16, 185, 129, 0.20); }
.pt-detail-tag--status-red    { background: rgba(220, 38, 38, 0.10);  color: var(--color-wc-red-text);   border-color: rgba(220, 38, 38, 0.20); }
.pt-detail-tag--status-muted  { background: rgba(255,255,255,0.03);   color: var(--color-wc-text-tertiary); border-color: var(--color-wc-border); }
.pt-detail-tag--category      { background: rgba(255, 255, 255, 0.04); color: var(--color-wc-text-secondary); border-color: var(--color-wc-border); }

.pt-detail-meta {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin: 0;
    padding: 0;
}
.pt-detail-meta-row {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 8px;
    align-items: baseline;
    padding: 4px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}
.pt-detail-meta-row:last-child { border-bottom: none; }
.pt-detail-meta-row dt {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.20em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    margin: 0;
}
.pt-detail-meta-row dd {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-feature-settings: 'tnum' 1;
    font-size: 12px;
    color: var(--color-wc-text);
    margin: 0;
    text-align: right;
}

/* ─── export JSON (motor v2) ─── */
.pt-detail-export {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 10px;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}
.pt-detail-export-label {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-gold, #C8A769);
}
.pt-detail-export-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.pt-export-btn {
    flex: 1 1 auto;
    min-width: 110px;
    height: 32px;
    padding: 0 12px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: rgba(255, 255, 255, 0.03);
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono, monospace);
    font-size: 10px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.pt-export-btn:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.06);
    color: var(--color-wc-text);
    border-color: var(--color-wc-text-tertiary);
}
.pt-export-btn--primary {
    background: rgba(200, 167, 105, 0.08);
    color: var(--color-wc-gold, #C8A769);
    border-color: rgba(200, 167, 105, 0.30);
}
.pt-export-btn--primary:hover:not(:disabled) {
    background: rgba(200, 167, 105, 0.14);
    border-color: var(--color-wc-gold, #C8A769);
    color: var(--color-wc-gold, #C8A769);
}
.pt-export-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.pt-detail-export-feedback {
    margin: 0;
    font-family: var(--font-mono, monospace);
    font-size: 10px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--color-wc-green-text, #34D399);
}

/* ─── tabs ─── */
.pt-detail-tabs {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    scrollbar-width: none;
    padding: 4px 2px;
    margin: 0 -2px;
    position: sticky;
    top: 0;
    z-index: 10;
    background: linear-gradient(180deg, rgba(10,10,10,0.94) 0%, rgba(10,10,10,0.85) 100%);
    backdrop-filter: blur(6px);
    border-bottom: 1px solid var(--color-wc-border);
}
.pt-detail-tabs::-webkit-scrollbar { display: none; }

.pt-detail-tab {
    flex: 0 0 auto;
    height: 36px;
    padding: 0 14px;
    border-radius: 8px;
    border: 1px solid transparent;
    background: transparent;
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono, monospace);
    font-size: 9.5px;
    letter-spacing: 0.20em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.pt-detail-tab:hover { color: var(--color-wc-text); }
.pt-detail-tab--active {
    background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.10));
    border-color: var(--color-wc-accent, #DC2626);
    color: var(--color-wc-red-text, #F87171);
}

/* ─── panel ─── */
.pt-detail-panel {
    min-width: 0;
}

.pt-section {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 18px;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.55);
    min-width: 0;
}
.pt-section-head {
    padding-bottom: 10px;
    border-bottom: 1px solid var(--color-wc-border);
}
.pt-section-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}

.pt-empty {
    padding: 22px 8px;
    border-radius: 10px;
    border: 1px dashed var(--color-wc-border);
    background: transparent;
    text-align: center;
}
.pt-empty-msg {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 12px;
    line-height: 1.55;
    color: var(--color-wc-text-tertiary);
    margin: 0;
    text-wrap: balance;
    max-width: 36ch;
    display: inline-block;
}

.pt-defs {
    margin: 0;
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}
@media (min-width: 1024px) { .pt-defs { grid-template-columns: 1fr 1fr; } }
.pt-def {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid var(--color-wc-border);
    background: rgba(10, 10, 10, 0.55);
    min-width: 0;
}
.pt-def-label {
    font-family: var(--font-mono, monospace);
    font-size: 8.5px;
    letter-spacing: 0.20em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.pt-def-value {
    font-family: var(--font-sans);
    font-size: 13px;
    line-height: 1.55;
    color: var(--color-wc-text);
    margin: 0;
    word-break: break-word;
}
.pt-def-pre {
    font-family: var(--font-mono, monospace);
    font-size: 11px;
    line-height: 1.5;
    margin: 0;
    padding: 8px 10px;
    background: rgba(0, 0, 0, 0.30);
    border: 1px solid var(--color-wc-border);
    border-radius: 6px;
    color: var(--color-wc-text-secondary);
    overflow-x: auto;
    max-height: 420px;
    white-space: pre-wrap;
    word-break: break-word;
}

.pt-coach-notes {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid rgba(200, 167, 105, 0.20);
    background: rgba(200, 167, 105, 0.04);
}
.pt-coach-notes-body {
    font-family: var(--font-sans);
    font-size: 13px;
    line-height: 1.6;
    color: var(--color-wc-text);
    margin: 0;
    white-space: pre-wrap;
}

/* ─── footer ─── */
.pt-detail-footer {
    position: sticky;
    bottom: 0;
    display: flex;
    gap: 10px;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(10, 10, 10, 0.92);
    backdrop-filter: blur(6px);
    z-index: 5;
    margin-top: 4px;
}
.pt-detail-footer--readonly { justify-content: center; }
.pt-detail-readonly-msg {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 12px;
    color: var(--color-wc-text-tertiary);
    margin: 0;
    text-align: center;
}
.pt-action-btn {
    flex: 1;
    height: 44px;
    padding: 0 18px;
    border-radius: 10px;
    border: 1px solid transparent;
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.pt-action-btn--reject {
    background: transparent;
    color: var(--color-wc-red-text, #F87171);
    border-color: rgba(220, 38, 38, 0.4);
}
.pt-action-btn--reject:hover:not(:disabled) {
    background: rgba(220, 38, 38, 0.10);
    border-color: var(--color-wc-accent, #DC2626);
}
.pt-action-btn--approve {
    background: var(--color-wc-green-text, #34D399);
    color: #04221A;
}
.pt-action-btn--approve:hover:not(:disabled) { filter: brightness(1.08); }
.pt-action-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* ─── loading + error ─── */
.pt-detail-loading {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.pt-detail-skeleton {
    height: 56px;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary, #181818);
    animation: pt-detail-pulse 1.5s ease-in-out infinite;
}
.pt-detail-skeleton--lg { height: 120px; }
.pt-detail-skeleton--block { height: 280px; }
@keyframes pt-detail-pulse {
    0%, 100% { opacity: 0.6; }
    50%      { opacity: 0.9; }
}

.pt-detail-error {
    padding: 22px;
    border-radius: 14px;
    border: 1px solid rgba(220, 38, 38, 0.20);
    background: rgba(220, 38, 38, 0.07);
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.pt-detail-error-msg {
    font-family: var(--font-sans);
    font-size: 13px;
    line-height: 1.55;
    color: var(--color-wc-red-text, #F87171);
    margin: 0;
}
.pt-detail-error-link {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-secondary);
    text-decoration: none;
    align-self: flex-start;
    border-bottom: 1px solid var(--color-wc-border);
    padding-bottom: 2px;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.pt-detail-error-link:hover { color: var(--color-wc-text); border-bottom-color: var(--color-wc-accent); }

@media (prefers-reduced-motion: reduce) {
    .pt-detail-skeleton,
    .pt-detail-tab { animation: none !important; transition: none !important; }
    .pt-detail-tabs { backdrop-filter: none; }
}
</style>
