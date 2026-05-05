<script setup>
import { computed, watch, onUnmounted } from 'vue';
import { useAdminCoachDetailStore } from '../../../stores/adminCoachDetail';

const store = useAdminCoachDetailStore();

const open = computed(() => store.isOpen);
const view = computed(() => store.view || {});

const TABS = [
    { id: 'resumen', label: 'RESUMEN' },
    { id: 'clientes', label: 'CLIENTES' },
    { id: 'activity', label: 'ACTIVITY' },
    { id: 'pagos', label: 'PAGOS' },
];

watch(open, (val) => {
    if (val) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
    store.stopPolling();
});

function close() {
    store.close();
}

function avatarInitial(name) {
    return (name || '?').trim().charAt(0).toUpperCase() || '?';
}

const refreshHint = computed(() => {
    const s = store.secondsSinceRefresh;
    if (s === null) return '';
    if (s < 10) return 'Actualizado ahora';
    if (s < 60) return `Hace ${s}s`;
    return `Hace ${Math.floor(s / 60)} min`;
});

const specsList = computed(() => {
    const raw = view.value?.specializations;
    if (!raw) return [];
    if (Array.isArray(raw)) return raw.filter(Boolean);
    if (typeof raw === 'string') return raw.split(',').map((s) => s.trim()).filter(Boolean);
    return [];
});
</script>

<template>
  <Teleport to="body">
    <Transition name="drawer-fade">
      <div v-if="open" class="drawer-backdrop" aria-hidden="true" @click="close"></div>
    </Transition>

    <Transition name="drawer-slide">
      <aside v-if="open" class="drawer-panel" role="dialog" aria-label="Detalle del coach">
        <!-- Head -->
        <header class="drawer-head">
          <div class="head-left">
            <span class="avatar">{{ avatarInitial(view.name) }}</span>
            <div class="head-text">
              <span class="eyebrow">PERFIL DE COACH</span>
              <h2 class="title">{{ view.name || 'Cargando…' }}</h2>
              <span class="handle">@{{ view.username || '—' }}</span>
            </div>
          </div>
          <button class="head-close" type="button" aria-label="Cerrar" @click="close">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
              <path d="M6 6l12 12M6 18L18 6" stroke-linecap="round" />
            </svg>
          </button>
        </header>

        <!-- Tabs -->
        <nav class="drawer-tabs" role="tablist">
          <button
            v-for="tab in TABS"
            :key="tab.id"
            type="button"
            role="tab"
            :aria-selected="store.activeTab === tab.id"
            class="drawer-tab"
            :class="{ 'drawer-tab--active': store.activeTab === tab.id }"
            @click="store.setTab(tab.id)"
          >{{ tab.label }}</button>
        </nav>

        <!-- Body -->
        <div class="drawer-body">
          <!-- ── Resumen ────────────────────────────────────────────────────── -->
          <section v-if="store.activeTab === 'resumen'" class="tab-section">
            <div class="metrics-grid">
              <div class="metric">
                <span class="metric-label">CLIENTES ACTIVOS</span>
                <span class="metric-value-data">{{ view.client_count ?? 0 }}</span>
              </div>
              <div class="metric">
                <span class="metric-label">ROL</span>
                <span class="metric-value-mono">{{ (view.role_label || view.role || '—').toUpperCase() }}</span>
              </div>
              <div class="metric">
                <span class="metric-label">ALTA</span>
                <span class="metric-value-mono">{{ view.created_at || '—' }}</span>
              </div>
            </div>

            <div class="block">
              <span class="block-label">PERFIL</span>
              <p v-if="view.bio" class="bio">{{ view.bio }}</p>
              <p v-else class="bio bio--empty">"Sin biografia. La pagina publica del coach se ve sin contexto."</p>

              <div v-if="specsList.length" class="specs">
                <span
                  v-for="spec in specsList"
                  :key="spec"
                  class="spec-pill"
                >{{ spec.toUpperCase() }}</span>
              </div>
            </div>

            <div class="block">
              <span class="block-label">CONTACTO</span>
              <p class="meta-line">
                <span class="meta-label">EMAIL</span>
                <span class="meta-val">{{ view.email || '—' }}</span>
              </p>
              <p class="meta-line">
                <span class="meta-label">WHATSAPP</span>
                <span class="meta-val mono">{{ view.whatsapp || '—' }}</span>
              </p>
              <p class="meta-line">
                <span class="meta-label">CIUDAD</span>
                <span class="meta-val">{{ view.city || '—' }}</span>
              </p>
              <p v-if="view.referral_code" class="meta-line">
                <span class="meta-label">CODIGO REF.</span>
                <span class="meta-val mono">{{ view.referral_code }}</span>
              </p>
            </div>

            <div class="block visibility-block">
              <span class="block-label">VISIBILIDAD PUBLICA</span>
              <div class="visibility-row">
                <button
                  type="button"
                  role="switch"
                  :aria-checked="view.public_visible ? 'true' : 'false'"
                  :disabled="!view.has_profile"
                  class="toggle-track"
                  :class="{ on: view.public_visible, disabled: !view.has_profile }"
                  @click="store.toggleVisibility()"
                >
                  <span class="toggle-thumb"></span>
                </button>
                <span class="visibility-label">
                  {{ view.public_visible ? 'Visible en página de coaches' : 'Oculto en página de coaches' }}
                </span>
              </div>
              <p v-if="!view.has_profile" class="visibility-hint">
                Requiere perfil completo para activar
              </p>
            </div>

            <p v-if="refreshHint" class="poll-hint">{{ refreshHint }}</p>
          </section>

          <!-- ── Clientes ──────────────────────────────────────────────────── -->
          <section v-else-if="store.activeTab === 'clientes'" class="tab-section">
            <div class="empty-block">
              <div class="empty-num">{{ view.client_count ?? 0 }}</div>
              <p class="empty-msg">
                "Hay {{ view.client_count ?? 0 }} cliente{{ view.client_count === 1 ? '' : 's' }} asignado{{ view.client_count === 1 ? '' : 's' }} a este coach.
                La lista detallada y el balance de carga llegan en la proxima fase."
              </p>
            </div>
          </section>

          <!-- ── Activity ──────────────────────────────────────────────────── -->
          <section v-else-if="store.activeTab === 'activity'" class="tab-section">
            <div class="empty-block">
              <div class="empty-num">—</div>
              <p class="empty-msg">
                "Sin timeline de actividad disponible todavia. Cuando se conecte el feed de eventos del coach, apareceran aqui las acciones por orden cronologico."
              </p>
            </div>
          </section>

          <!-- ── Pagos ─────────────────────────────────────────────────────── -->
          <section v-else-if="store.activeTab === 'pagos'" class="tab-section">
            <div class="empty-block">
              <div class="empty-num">—</div>
              <p class="empty-msg">
                "El reporte de comisiones y MRR generado por este coach se construye en la proxima fase del modulo financiero."
              </p>
            </div>
          </section>
        </div>
      </aside>
    </Transition>
  </Teleport>
</template>

<style scoped>
.drawer-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(6px);
    z-index: 80;
}
.drawer-panel {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    max-width: 480px;
    background: var(--c-surface);
    border-left: 1px solid var(--c-border);
    z-index: 90;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
@media (min-width: 768px) {
    .drawer-panel { max-width: 520px; }
}

/* ── Head ────────────────────────────────────────────────────────────── */
.drawer-head {
    padding: 18px 18px 14px;
    border-bottom: 1px solid var(--c-border);
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.head-left {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}
.avatar {
    width: 44px;
    height: 44px;
    flex-shrink: 0;
    border-radius: 50%;
    background: rgba(220, 38, 38, 0.12);
    border: 1px solid rgba(220, 38, 38, 0.3);
    color: #F87171;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-size: 18px;
    letter-spacing: 0.04em;
}
.head-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}
.eyebrow {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.title {
    font-family: var(--font-display);
    font-size: 22px;
    letter-spacing: 0.04em;
    color: var(--c-text);
    margin: 0;
    line-height: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.handle {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 1.2px;
    color: var(--c-text-3);
}
.head-close {
    width: var(--tap-comfort, 48px);
    height: var(--tap-comfort, 48px);
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: transparent;
    color: var(--c-text-2);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
    flex-shrink: 0;
}
.head-close:hover {
    background: rgba(255, 255, 255, 0.04);
    color: var(--c-text);
}

/* ── Tabs ────────────────────────────────────────────────────────────── */
.drawer-tabs {
    display: flex;
    border-bottom: 1px solid var(--c-border);
    padding: 0 18px;
    gap: 4px;
    overflow-x: auto;
    scrollbar-width: none;
    flex-shrink: 0;
}
.drawer-tabs::-webkit-scrollbar { display: none; }

.drawer-tab {
    background: transparent;
    border: none;
    color: var(--c-text-3);
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    padding: 12px 14px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
    flex-shrink: 0;
}
.drawer-tab:hover { color: var(--c-text-2); }
.drawer-tab--active {
    color: #F87171;
    border-bottom-color: var(--c-accent);
}

/* ── Body ────────────────────────────────────────────────────────────── */
.drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 18px;
}
.tab-section {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}
.metric {
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255, 255, 255, 0.02);
    padding: 10px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.metric-label {
    font-family: var(--font-display);
    font-size: 7px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.metric-value-data {
    font-family: var(--font-display);
    font-feature-settings: 'tnum' 1;
    font-size: 22px;
    font-weight: 700;
    color: var(--c-text);
    line-height: 1;
}
.metric-value-mono {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 0.12em;
    color: var(--c-text-2);
}

.block {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.block-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.bio {
    font-family: var(--font-sans);
    font-size: 13px;
    line-height: 1.55;
    color: var(--c-text-2);
    margin: 0;
}
.bio--empty {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    color: var(--c-text-3);
}
.specs {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 4px;
}
.spec-pill {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 0.18em;
    background: rgba(220, 38, 38, 0.08);
    color: #F87171;
    border: 1px solid rgba(220, 38, 38, 0.22);
    padding: 4px 8px;
    border-radius: var(--r-pill, 999px);
}

.meta-line {
    display: flex;
    align-items: baseline;
    gap: 10px;
    margin: 0;
    padding: 6px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}
.meta-line:last-child { border-bottom: none; }
.meta-label {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 0.18em;
    color: var(--c-text-3);
    flex-shrink: 0;
    width: 100px;
}
.meta-val {
    flex: 1;
    font-size: 12px;
    color: var(--c-text-2);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.meta-val.mono {
    font-family: var(--font-display);
    font-size: 11px;
    letter-spacing: 0.1em;
}

.poll-hint {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--c-text-3);
    text-align: right;
    opacity: 0.5;
    margin: 0;
}

/* ── Empty blocks por tab ─────────────────────────────────────────────── */
.empty-block {
    text-align: center;
    padding: 24px 12px;
    border-radius: var(--r-sm, 12px);
    border: 1px dashed var(--c-border);
    background: rgba(255, 255, 255, 0.02);
}
.empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--c-surface-2);
    letter-spacing: 0.1em;
    line-height: 1;
    margin-bottom: 12px;
}
.empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: var(--c-text-3);
    line-height: 1.55;
    margin: 0;
    text-wrap: balance;
}

/* ── Visibility toggle ───────────────────────────────────────────────── */
.visibility-block {
    padding: 12px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255, 255, 255, 0.02);
}
.visibility-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.toggle-track {
    width: 40px;
    height: 22px;
    border-radius: 11px;
    background: var(--c-border);
    border: none;
    transition: background 0.2s;
    cursor: pointer;
    position: relative;
    flex-shrink: 0;
    padding: 0;
}
.toggle-track.on {
    background: #22C55E;
}
.toggle-track.disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
.toggle-thumb {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: white;
    transition: left 0.2s;
    pointer-events: none;
}
.toggle-track.on .toggle-thumb {
    left: 21px;
}
.visibility-label {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--c-text-2);
}
.visibility-hint {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--c-text-3);
    margin: 6px 0 0;
}

/* ── Transitions ─────────────────────────────────────────────────────── */
.drawer-fade-enter-active,
.drawer-fade-leave-active { transition: opacity 0.2s var(--ease-out, ease); }
.drawer-fade-enter-from,
.drawer-fade-leave-to { opacity: 0; }

.drawer-slide-enter-active,
.drawer-slide-leave-active { transition: transform 0.28s var(--ease-out, ease); }
.drawer-slide-enter-from,
.drawer-slide-leave-to { transform: translateX(100%); }

@media (prefers-reduced-motion: reduce) {
    .drawer-fade-enter-active,
    .drawer-fade-leave-active,
    .drawer-slide-enter-active,
    .drawer-slide-leave-active { transition: none !important; }
}
</style>
