<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import { useAdminProofsStore } from '../../../stores/adminProofs';
import { useToast } from '../../../composables/useToast';
import { formatCOP, formatRelativeTime } from '../../../composables/useFormat';
import AdminProofImageZoom from './AdminProofImageZoom.vue';
import AdminProofRejectModal from './AdminProofRejectModal.vue';

const store = useAdminProofsStore();
const toast = useToast();

const showApproveConfirm = ref(false);
const showRejectModal = ref(false);
const showZoom = ref(false);
const submitting = ref(false);

const proof = computed(() => store.selectedProof);
const open = computed(() => !!proof.value);

const isPending = computed(() => proof.value?.status === 'pendiente');
const isImage = computed(() => proof.value?.fileMime?.startsWith('image/'));
const isPdf = computed(() => proof.value?.fileMime === 'application/pdf');

const STATUS_LABEL = {
    pendiente: 'Pendiente',
    aprobado:  'Aprobado',
    rechazado: 'Rechazado',
    expirado:  'Expirado',
};
const STATUS_PILL_CLASS = {
    pendiente: 'pill--warn',
    aprobado:  'pill--success',
    rechazado: 'pill--urgent',
    expirado:  'pill--info',
};

function planLabel(plan) {
    if (!plan) return '—';
    const map = { rise: 'Plan Rise', metodo: 'Plan Método', ascenso: 'Plan Ascenso', elite: 'Plan Élite' };
    return map[plan] || plan;
}

function methodLabel(method) {
    if (!method) return '—';
    return method.charAt(0).toUpperCase() + method.slice(1);
}

function close() {
    if (submitting.value) return;
    showApproveConfirm.value = false;
    showRejectModal.value = false;
    showZoom.value = false;
    store.closeDrawer();
}

function openFileNewTab() {
    if (store.fileUrl) window.open(store.fileUrl, '_blank', 'noopener,noreferrer');
}

function openZoom() {
    if (!isImage.value || !store.fileUrl) return;
    showZoom.value = true;
}

function requestApprove() {
    showApproveConfirm.value = true;
    showRejectModal.value = false;
}

function cancelApprove() {
    showApproveConfirm.value = false;
}

async function confirmApprove() {
    if (!proof.value || submitting.value) return;
    submitting.value = true;
    const result = await store.approveProof(proof.value.id);
    submitting.value = false;
    showApproveConfirm.value = false;

    if (result.ok) {
        toast.success('Comprobante aprobado. Cliente activado.');
        // Cierra automáticamente para volver a la cola
        setTimeout(close, 800);
    } else if (result.reason === 'already_reviewed') {
        toast.info(result.message);
        close();
    } else if (result.reason === 'in_flight') {
        // Silencioso: ya hay una acción en vuelo
    } else {
        toast.error(result.message || 'Error al aprobar.');
    }
}

function requestReject() {
    showRejectModal.value = true;
    showApproveConfirm.value = false;
}

async function confirmReject(reviewNote) {
    if (!proof.value || submitting.value) return;
    submitting.value = true;
    const result = await store.rejectProof(proof.value.id, reviewNote);
    submitting.value = false;

    if (result.ok) {
        toast.success('Comprobante rechazado. Cliente notificado.');
        showRejectModal.value = false;
        setTimeout(close, 800);
    } else if (result.reason === 'already_reviewed') {
        toast.info(result.message);
        showRejectModal.value = false;
        close();
    } else if (result.reason === 'note_too_short' || result.reason === 'validation') {
        toast.error(result.message);
    } else if (result.reason === 'in_flight') {
        // silencioso
    } else {
        toast.error(result.message || 'Error al rechazar.');
    }
}

function onKeydown(e) {
    if (!open.value) return;
    if (e.key === 'Escape' && !showZoom.value && !showRejectModal.value) close();
}

watch(open, (v) => {
    if (v) {
        document.addEventListener('keydown', onKeydown);
        document.body.style.overflow = 'hidden';
    } else {
        document.removeEventListener('keydown', onKeydown);
        document.body.style.overflow = '';
        showApproveConfirm.value = false;
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
  <Teleport to="body">
    <Transition name="drawer-fade">
      <div v-if="open" class="drawer-backdrop" @click.self="close" role="dialog" aria-modal="true" aria-label="Revisión de comprobante"></div>
    </Transition>

    <Transition name="drawer-slide">
      <aside v-if="open && proof" class="drawer-panel">
        <!-- Header sticky -->
        <header class="drawer-header">
          <div class="drawer-header-text">
            <p class="drawer-eyebrow">COMPROBANTE #{{ proof.id }}</p>
            <h2 class="drawer-title">{{ proof.clientName || 'Sin nombre' }}</h2>
            <p class="drawer-sub">
              <span class="pill" :class="STATUS_PILL_CLASS[proof.status] || 'pill--info'">
                {{ STATUS_LABEL[proof.status] || proof.status }}
              </span>
              <span class="drawer-time">Enviado {{ formatRelativeTime(proof.submittedAt) }}</span>
            </p>
          </div>
          <button class="drawer-close" @click="close" :disabled="submitting" aria-label="Cerrar revisión">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
          </button>
        </header>

        <!-- Body scroll -->
        <div class="drawer-body">
          <!-- Imagen / PDF -->
          <section class="drawer-image-section">
            <p class="drawer-section-label">COMPROBANTE</p>
            <div class="drawer-image-frame">
              <div v-if="store.fileLoading" class="drawer-image-loading">
                <svg viewBox="0 0 24 24" class="drawer-image-spin" aria-hidden="true">
                  <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2.5" fill="none" opacity="0.25"/>
                  <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                </svg>
              </div>

              <div v-else-if="!store.fileUrl" class="drawer-image-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
                  <rect x="3" y="3" width="18" height="18" rx="2"/>
                  <path d="m4 19 6-7 5 6 3-4 2 3"/>
                </svg>
                <p>No se pudo cargar el archivo.</p>
              </div>

              <button
                v-else-if="isPdf"
                type="button"
                class="drawer-pdf"
                @click="openFileNewTab"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
                  <rect x="4" y="3" width="16" height="18" rx="2"/>
                  <path d="M9 17V7m3 10V7m3 10V7"/>
                </svg>
                <span>Ver PDF en pestaña aparte →</span>
              </button>

              <button
                v-else-if="isImage"
                type="button"
                class="drawer-image-btn"
                @click="openZoom"
                :aria-label="`Ampliar comprobante de ${proof.clientName}`"
              >
                <img :src="store.fileUrl" :alt="`Comprobante ${proof.clientName}`" class="drawer-image" />
                <span class="drawer-image-zoom-hint">CLIC PARA AMPLIAR</span>
              </button>

              <button
                v-else
                type="button"
                class="drawer-pdf"
                @click="openFileNewTab"
              >
                <span>Ver archivo →</span>
              </button>
            </div>
          </section>

          <!-- Datos esperados -->
          <section class="drawer-data">
            <p class="drawer-section-label">DATOS ESPERADOS</p>
            <div class="drawer-data-grid">
              <div class="drawer-data-cell">
                <span class="drawer-data-label">CLIENTE</span>
                <span class="drawer-data-value">{{ proof.clientName || '—' }}</span>
              </div>
              <div class="drawer-data-cell">
                <span class="drawer-data-label">EMAIL</span>
                <span class="drawer-data-value drawer-data-value--email">{{ proof.clientEmail || '—' }}</span>
              </div>
              <div class="drawer-data-cell drawer-data-cell--accent">
                <span class="drawer-data-label">MONTO</span>
                <span class="drawer-data-value drawer-data-value--big">{{ formatCOP(proof.amount) }}</span>
                <span class="drawer-data-meta">{{ proof.currency || 'COP' }}</span>
              </div>
              <div class="drawer-data-cell">
                <span class="drawer-data-label">PLAN</span>
                <span class="drawer-data-value">{{ planLabel(proof.plan) }}</span>
              </div>
              <div class="drawer-data-cell">
                <span class="drawer-data-label">MÉTODO</span>
                <span class="drawer-data-value">{{ methodLabel(proof.paymentMethod) }}</span>
              </div>
              <div class="drawer-data-cell">
                <span class="drawer-data-label">COACH</span>
                <span class="drawer-data-value">{{ proof.coach?.name || '—' }}</span>
              </div>
            </div>
          </section>

          <!-- Nota del coach -->
          <section v-if="proof.coachNote" class="drawer-note drawer-note--coach">
            <p class="drawer-section-label">NOTA DEL COACH</p>
            <p class="drawer-note-text">{{ proof.coachNote }}</p>
          </section>

          <!-- Razón rechazo (si existe) -->
          <section v-if="proof.reviewNote" class="drawer-note drawer-note--reject">
            <p class="drawer-section-label drawer-section-label--danger">RAZÓN DEL RECHAZO</p>
            <p class="drawer-note-text">{{ proof.reviewNote }}</p>
          </section>

          <!-- Audit timeline -->
          <section class="drawer-timeline">
            <p class="drawer-section-label">HISTÓRICO</p>
            <ol class="timeline-list">
              <li class="timeline-item">
                <span class="timeline-dot timeline-dot--neutral"></span>
                <div class="timeline-content">
                  <span class="timeline-title">Comprobante recibido</span>
                  <span class="timeline-meta">{{ formatRelativeTime(proof.submittedAt) }} · {{ proof.coach?.name || 'coach' }}</span>
                </div>
              </li>
              <li v-if="proof.reviewedAt" class="timeline-item">
                <span
                  class="timeline-dot"
                  :class="proof.status === 'aprobado' ? 'timeline-dot--success' : 'timeline-dot--danger'"
                ></span>
                <div class="timeline-content">
                  <span class="timeline-title">
                    {{ proof.status === 'aprobado' ? 'Aprobado' : 'Rechazado' }}
                  </span>
                  <span class="timeline-meta">
                    {{ formatRelativeTime(proof.reviewedAt) }}<template v-if="proof.reviewer?.name"> · {{ proof.reviewer.name }}</template>
                  </span>
                </div>
              </li>
              <li v-else-if="proof.expiresAt" class="timeline-item timeline-item--future">
                <span class="timeline-dot timeline-dot--future"></span>
                <div class="timeline-content">
                  <span class="timeline-title">Expira</span>
                  <span class="timeline-meta">{{ formatRelativeTime(proof.expiresAt) }}</span>
                </div>
              </li>
            </ol>
          </section>
        </div>

        <!-- Footer sticky con acciones -->
        <footer v-if="isPending" class="drawer-actions">
          <Transition name="fade" mode="out-in">
            <!-- Confirmación de aprobación inline -->
            <div v-if="showApproveConfirm" key="confirm" class="approve-confirm">
              <div class="approve-confirm-head">
                <p class="approve-confirm-title">CONFIRMAR APROBACIÓN</p>
                <p class="approve-confirm-msg">
                  El cliente <strong>{{ proof.clientName }}</strong> será activado en el plan <strong>{{ planLabel(proof.plan) }}</strong> por <strong>{{ formatCOP(proof.amount) }}</strong>. Esta acción es irreversible.
                </p>
              </div>
              <div class="approve-confirm-buttons">
                <button class="btn btn--secondary" @click="cancelApprove" :disabled="submitting">
                  Cancelar
                </button>
                <button class="btn btn--approve" @click="confirmApprove" :disabled="submitting">
                  <svg v-if="submitting" class="btn-spinner" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round"/></svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 5 5L20 7"/></svg>
                  {{ submitting ? 'Aprobando...' : 'Activar cliente' }}
                </button>
              </div>
            </div>

            <!-- Botones primarios -->
            <div v-else key="actions" class="actions-row">
              <button class="btn btn--reject" @click="requestReject" :disabled="submitting">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                Rechazar
              </button>
              <button class="btn btn--approve" @click="requestApprove" :disabled="submitting">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 5 5L20 7"/></svg>
                Aprobar
              </button>
            </div>
          </Transition>
        </footer>

        <footer v-else class="drawer-actions drawer-actions--readonly">
          <p class="drawer-readonly-msg">
            Este comprobante ya fue revisado. La revisión es definitiva.
          </p>
        </footer>
      </aside>
    </Transition>

    <!-- Modal de zoom imagen -->
    <AdminProofImageZoom
      :open="showZoom"
      :src="store.fileUrl"
      :alt="`Comprobante ${proof?.clientName || ''}`"
      @close="showZoom = false"
    />

    <!-- Modal de razón de rechazo -->
    <AdminProofRejectModal
      :open="showRejectModal"
      :proof="proof"
      :submitting="submitting"
      @close="showRejectModal = false"
      @confirm="confirmReject"
    />
  </Teleport>
</template>

<style scoped>
/* ── Backdrop + panel ──────────────────────────────────────────────────── */
.drawer-backdrop {
    position: fixed;
    inset: 0;
    z-index: 60;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
}
.drawer-panel {
    position: fixed;
    right: 0;
    top: 0;
    bottom: 0;
    z-index: 70;
    width: 100%;
    background: var(--c-surface);
    border-left: 1px solid var(--c-border);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: -16px 0 48px rgba(0, 0, 0, 0.5);
}
@media (min-width: 768px) {
    .drawer-panel { width: min(540px, 92vw); }
}
@media (min-width: 1280px) {
    .drawer-panel { width: 580px; }
}

/* ── Header ────────────────────────────────────────────────────────────── */
.drawer-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 18px 20px 14px;
    border-bottom: 1px solid var(--c-border);
    flex-shrink: 0;
}
.drawer-header-text { min-width: 0; }
.drawer-eyebrow {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
    margin: 0 0 4px;
}
.drawer-title {
    font-family: var(--font-display);
    font-size: 22px;
    letter-spacing: 0.06em;
    color: var(--c-text);
    margin: 0 0 8px;
    line-height: 1.05;
    text-transform: uppercase;
}
.drawer-sub {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    flex-wrap: wrap;
}
.drawer-time {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.drawer-close {
    width: var(--tap-comfort, 48px);
    height: var(--tap-comfort, 48px);
    border-radius: var(--r-sm, 12px);
    background: transparent;
    border: 1px solid var(--c-border);
    color: var(--c-text-2);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.drawer-close:hover { border-color: rgba(255, 255, 255, 0.12); color: var(--c-text); }
.drawer-close svg { width: 16px; height: 16px; }

/* ── Body ──────────────────────────────────────────────────────────────── */
.drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 18px 20px 24px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.drawer-section-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
    margin: 0 0 10px;
}
.drawer-section-label--danger { color: #F87171; }

/* Imagen */
.drawer-image-frame {
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: var(--c-surface-2);
    overflow: hidden;
    min-height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.drawer-image-btn {
    background: none;
    border: none;
    padding: 0;
    width: 100%;
    cursor: zoom-in;
    position: relative;
    display: block;
}
.drawer-image {
    width: 100%;
    max-height: 360px;
    object-fit: contain;
    display: block;
}
.drawer-image-zoom-hint {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.65);
    color: #fff;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.4px;
    padding: 5px 10px;
    border-radius: var(--r-pill, 999px);
    border: 1px solid rgba(255, 255, 255, 0.18);
}

.drawer-image-loading {
    color: var(--c-text-3);
}
.drawer-image-spin {
    width: 28px; height: 28px;
    animation: dspin 0.8s linear infinite;
}
@keyframes dspin { to { transform: rotate(360deg); } }
.drawer-image-empty {
    text-align: center;
    color: var(--c-text-3);
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 13px;
    padding: 24px;
}
.drawer-image-empty svg { width: 32px; height: 32px; margin: 0 auto 8px; display: block; }

.drawer-pdf {
    width: 100%;
    padding: 32px 20px;
    background: rgba(96, 165, 250, 0.06);
    border: none;
    color: #60A5FA;
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    transition: background 0.15s var(--ease-out, ease);
}
.drawer-pdf:hover { background: rgba(96, 165, 250, 0.12); }
.drawer-pdf svg { width: 36px; height: 36px; }

/* Datos */
.drawer-data-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}
.drawer-data-cell {
    padding: 10px 12px;
    border-radius: var(--r-sm, 12px);
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--c-border);
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
}
.drawer-data-cell--accent {
    grid-column: span 2;
    background: rgba(220, 38, 38, 0.05);
    border-color: rgba(220, 38, 38, 0.18);
}
.drawer-data-label {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.drawer-data-value {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    color: var(--c-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    text-transform: capitalize;
}
.drawer-data-value--email { text-transform: lowercase; font-size: 12px; font-weight: 500; }
.drawer-data-value--big {
    font-family: var(--font-display);
    font-size: 26px;
    color: #F87171;
    letter-spacing: 0.04em;
    line-height: 1;
}
.drawer-data-meta {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.4px;
    color: var(--c-text-3);
}

/* Notas */
.drawer-note {
    padding: 12px 14px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255, 255, 255, 0.02);
}
.drawer-note--coach { background: rgba(96, 165, 250, 0.05); border-color: rgba(96, 165, 250, 0.16); }
.drawer-note--reject { background: rgba(220, 38, 38, 0.06); border-color: rgba(220, 38, 38, 0.18); }
.drawer-note-text {
    margin: 0;
    font-family: var(--font-sans);
    font-size: 13px;
    line-height: 1.55;
    color: var(--c-text-2);
}

/* Timeline */
.timeline-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; }
.timeline-item {
    display: grid;
    grid-template-columns: 14px 1fr;
    gap: 10px;
    align-items: flex-start;
}
.timeline-item--future { opacity: 0.6; }
.timeline-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-top: 5px;
    background: var(--c-text-3);
}
.timeline-dot--neutral { background: var(--c-text-3); }
.timeline-dot--success { background: #34D399; }
.timeline-dot--danger  { background: #F87171; }
.timeline-dot--future  { background: #FCD34D; }
.timeline-content { display: flex; flex-direction: column; gap: 1px; }
.timeline-title { font-family: var(--font-sans); font-size: 13px; color: var(--c-text); font-weight: 500; }
.timeline-meta {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.2px;
    color: var(--c-text-3);
    text-transform: uppercase;
}

/* ── Footer actions ────────────────────────────────────────────────────── */
.drawer-actions {
    flex-shrink: 0;
    border-top: 1px solid var(--c-border);
    padding: 14px 20px;
    background: rgba(10, 10, 10, 0.85);
    backdrop-filter: blur(8px);
}
.drawer-actions--readonly {
    text-align: center;
}
.drawer-readonly-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: var(--c-text-3);
    margin: 0;
}

.actions-row { display: flex; gap: 10px; }
.btn {
    flex: 1;
    min-height: var(--tap-comfort, 48px);
    padding: 0 16px;
    border-radius: var(--r-sm, 12px);
    font-family: var(--font-sans);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: 1px solid transparent;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.btn svg { width: 16px; height: 16px; }
.btn--secondary {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--c-border);
    color: var(--c-text);
}
.btn--secondary:hover:not(:disabled) { border-color: rgba(255, 255, 255, 0.12); }
.btn--reject {
    background: rgba(220, 38, 38, 0.08);
    border: 1px solid rgba(220, 38, 38, 0.28);
    color: #F87171;
}
.btn--reject:hover:not(:disabled) { background: rgba(220, 38, 38, 0.16); border-color: rgba(220, 38, 38, 0.45); }
.btn--approve {
    background: rgba(16, 185, 129, 0.12);
    border: 1px solid rgba(16, 185, 129, 0.32);
    color: #34D399;
}
.btn--approve:hover:not(:disabled) { background: rgba(16, 185, 129, 0.22); border-color: rgba(16, 185, 129, 0.5); }
.btn:disabled { opacity: 0.45; cursor: not-allowed; }
.btn-spinner { width: 14px; height: 14px; animation: dspin 0.8s linear infinite; color: currentColor; }

/* Approve confirm card */
.approve-confirm {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.approve-confirm-head { display: flex; flex-direction: column; gap: 6px; }
.approve-confirm-title {
    font-family: var(--font-display);
    font-size: 14px;
    letter-spacing: 0.14em;
    color: #34D399;
    margin: 0;
}
.approve-confirm-msg {
    margin: 0;
    font-family: var(--font-sans);
    font-size: 13px;
    line-height: 1.55;
    color: var(--c-text-2);
}
.approve-confirm-msg strong { color: var(--c-text); font-weight: 600; }
.approve-confirm-buttons { display: flex; gap: 10px; }

/* Pills */
.pill {
    display: inline-block;
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: var(--r-pill, 999px);
}
.pill--urgent  { background: var(--c-accent-dim); color: #F87171; }
.pill--warn    { background: rgba(245, 158, 11, 0.1); color: #FCD34D; }
.pill--success { background: rgba(16, 185, 129, 0.1); color: #34D399; }
.pill--info    { background: rgba(59, 130, 246, 0.1); color: #60A5FA; }

/* Transitions */
.drawer-fade-enter-active, .drawer-fade-leave-active { transition: opacity 0.22s var(--ease-out, ease); }
.drawer-fade-enter-from, .drawer-fade-leave-to { opacity: 0; }
.drawer-slide-enter-active, .drawer-slide-leave-active { transition: transform 0.28s var(--ease-out, ease); }
.drawer-slide-enter-from, .drawer-slide-leave-to { transform: translateX(100%); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.18s var(--ease-out, ease); }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .drawer-fade-enter-active, .drawer-fade-leave-active,
    .drawer-slide-enter-active, .drawer-slide-leave-active,
    .fade-enter-active, .fade-leave-active { transition: none !important; }
    .btn-spinner, .drawer-image-spin { animation: none !important; }
}
</style>
