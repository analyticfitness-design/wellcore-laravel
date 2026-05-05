<script setup>
import { ref, onMounted } from 'vue';
import { useAdminClientDetailStore } from '../../../stores/adminClientDetail';
import { useApi } from '../../../composables/useApi';

const store = useAdminClientDetailStore();
const api   = useApi();

const loading   = ref(false);
const error     = ref(null);
const intake    = ref(null);
const showJson  = ref(false);
const copied    = ref(false);

async function fetchIntake() {
    if (!store.clientId) return;
    loading.value = true;
    error.value   = null;
    try {
        const { data } = await api.get(`/api/v/admin/clients/${store.clientId}/intake`);
        intake.value = data;
    } catch (e) {
        error.value = e?.response?.data?.message || 'No se pudo cargar el intake.';
    } finally {
        loading.value = false;
    }
}

async function copyJson() {
    if (!intake.value?.rawJson) return;
    try {
        await navigator.clipboard.writeText(JSON.stringify(intake.value.rawJson, null, 2));
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    } catch {
        /* clipboard API no disponible */
    }
}

onMounted(fetchIntake);
</script>

<template>
  <div class="intake-panel">

    <!-- Loading -->
    <div v-if="loading" class="skeleton-stack">
      <div class="skeleton sk-lg" />
      <div class="skeleton sk-md" />
      <div class="skeleton sk-md" />
    </div>

    <!-- Error -->
    <div v-else-if="error" class="err-card">
      <span class="err-icon">!</span>
      <p class="err-msg">{{ error }}</p>
      <button type="button" class="btn-retry" @click="fetchIntake">REINTENTAR</button>
    </div>

    <!-- Sin intake -->
    <div v-else-if="intake && !intake.hasIntake" class="empty-card">
      <span class="empty-glyph">—</span>
      <p class="empty-msg">Este cliente aún no ha completado el formulario de intake.</p>
    </div>

    <!-- Intake con datos -->
    <template v-else-if="intake">

      <!-- Meta pill -->
      <div class="meta-row">
        <span class="plan-pill">{{ (intake.plan || '').toUpperCase() }}</span>
        <span class="meta-date" v-if="intake.submittedAt">
          {{ new Date(intake.submittedAt).toLocaleDateString('es-CO', { day:'2-digit', month:'short', year:'numeric' }) }}
        </span>
      </div>

      <!-- Secciones Q&A -->
      <article
        v-for="section in intake.sections"
        :key="section.key"
        class="section-card"
      >
        <header class="section-head">
          <span class="section-icon">{{ section.icon }}</span>
          <span class="section-title">{{ section.title.toUpperCase() }}</span>
        </header>

        <dl class="qa-list">
          <template v-for="field in section.fields" :key="field.label">
            <dt class="qa-label">{{ field.label }}</dt>
            <dd class="qa-value">
              <template v-if="field.value === null || field.value === '' || field.value === undefined">
                <span class="qa-empty">—</span>
              </template>
              <template v-else>
                {{ field.value }}{{ field.unit ? ' ' + field.unit : '' }}
              </template>
            </dd>
          </template>
        </dl>
      </article>

      <!-- JSON block para Claude Code -->
      <div class="json-block">
        <div class="json-head">
          <button type="button" class="json-toggle" @click="showJson = !showJson">
            <span class="json-toggle-icon">{{ showJson ? '▾' : '▸' }}</span>
            <span class="json-toggle-label">JSON PARA CLAUDE CODE</span>
          </button>
          <button
            type="button"
            class="btn-copy"
            :class="{ 'btn-copy--ok': copied }"
            @click="copyJson"
          >
            {{ copied ? 'COPIADO ✓' : 'COPIAR' }}
          </button>
        </div>

        <Transition name="json-expand">
          <pre v-if="showJson" class="json-pre"><code>{{ JSON.stringify(intake.rawJson, null, 2) }}</code></pre>
        </Transition>
      </div>

    </template>

  </div>
</template>

<style scoped>
.intake-panel { display: flex; flex-direction: column; gap: 12px; }

/* ── Skeletons ──────────────────────────────────────────────────────── */
.skeleton-stack { display: flex; flex-direction: column; gap: 10px; }
.skeleton {
    border-radius: var(--r-md, 16px);
    background: var(--c-surface-2);
    animation: pulse 1.5s ease-in-out infinite;
}
.sk-lg  { height: 140px; }
.sk-md  { height: 80px; }
@keyframes pulse { 0%,100%{opacity:.55} 50%{opacity:.85} }
@media (prefers-reduced-motion:reduce) { .skeleton { animation: none; opacity: .55; } }

/* ── Error / Empty ──────────────────────────────────────────────────── */
.err-card, .empty-card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: var(--c-surface-2);
    padding: 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    text-align: center;
}
.err-icon {
    font-family: var(--font-display);
    font-size: 36px;
    color: #F87171;
    line-height: 1;
}
.err-msg, .empty-msg {
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--c-text-2);
    margin: 0;
}
.empty-glyph {
    font-family: var(--font-display);
    font-size: 36px;
    color: var(--c-text-3);
    line-height: 1;
}
.btn-retry {
    background: transparent;
    border: 1px solid var(--c-border);
    color: #F87171;
    padding: 6px 14px;
    border-radius: var(--r-sm, 12px);
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    cursor: pointer;
}
.btn-retry:hover { background: rgba(255,255,255,.04); }

/* ── Meta row ───────────────────────────────────────────────────────── */
.meta-row { display: flex; align-items: center; gap: 8px; }
.plan-pill {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    padding: 3px 8px;
    border-radius: var(--r-pill, 999px);
    background: rgba(220,38,38,.12);
    color: var(--c-accent, #DC2626);
    border: 1px solid rgba(220,38,38,.25);
}
.meta-date {
    font-family: var(--font-sans);
    font-size: 11px;
    color: var(--c-text-3);
}

/* ── Section card ───────────────────────────────────────────────────── */
.section-card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: var(--c-surface-2);
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.section-head { display: flex; align-items: center; gap: 8px; }
.section-icon { font-size: 16px; line-height: 1; }
.section-title {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    color: var(--c-text-3);
}

/* ── Q&A list ───────────────────────────────────────────────────────── */
.qa-list {
    display: grid;
    grid-template-columns: minmax(140px, 1fr) 2fr;
    column-gap: 12px;
    row-gap: 8px;
    margin: 0;
    padding: 0;
}
.qa-label {
    font-family: var(--font-sans);
    font-size: 11px;
    color: var(--c-text-3);
    padding-top: 1px;
}
.qa-value {
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--c-text);
    font-weight: 500;
    word-break: break-word;
    margin: 0;
}
.qa-empty { color: var(--c-text-3); font-weight: 400; }

/* ── JSON block ─────────────────────────────────────────────────────── */
.json-block {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: var(--c-surface-2);
    overflow: hidden;
}
.json-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border-bottom: 1px solid var(--c-border);
    gap: 8px;
}
.json-toggle {
    display: flex;
    align-items: center;
    gap: 6px;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
}
.json-toggle-icon {
    font-family: monospace;
    font-size: 12px;
    color: var(--c-text-3);
}
.json-toggle-label {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.8px;
    color: var(--c-text-3);
}
.json-toggle:hover .json-toggle-label,
.json-toggle:hover .json-toggle-icon { color: var(--c-text-2); }

.btn-copy {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    padding: 4px 10px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: transparent;
    color: var(--c-text-3);
    cursor: pointer;
    transition: color .12s, background .12s, border-color .12s;
}
.btn-copy:hover { color: var(--c-text); background: rgba(255,255,255,.04); }
.btn-copy--ok {
    color: #4ADE80;
    border-color: rgba(74,222,128,.3);
    background: rgba(74,222,128,.06);
}

.json-pre {
    margin: 0;
    padding: 14px;
    font-family: var(--font-mono, 'JetBrains Mono', monospace);
    font-size: 11px;
    line-height: 1.6;
    color: var(--c-text-2);
    overflow-x: auto;
    white-space: pre;
    max-height: 480px;
    overflow-y: auto;
}
.json-pre::-webkit-scrollbar { width: 4px; height: 4px; }
.json-pre::-webkit-scrollbar-track { background: transparent; }
.json-pre::-webkit-scrollbar-thumb { background: var(--c-border); border-radius: 2px; }

.json-expand-enter-active,
.json-expand-leave-active { transition: opacity .15s var(--ease-out, ease); }
.json-expand-enter-from,
.json-expand-leave-to { opacity: 0; }
</style>
