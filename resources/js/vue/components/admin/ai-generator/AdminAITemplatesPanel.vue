<script setup>
import { onMounted } from 'vue';
import { useAdminAIGeneratorStore } from '../../../stores/adminAIGenerator';

const emit = defineEmits(['load-template']);
const store = useAdminAIGeneratorStore();

onMounted(() => {
    if (!store.templates.length) store.fetchTemplates();
});

function loadTemplate(t) {
    store.setBrief({
        plan_type: t.plan_type,
        methodology: t.methodology || '',
    });
    emit('load-template', t);
}

function fmtDate(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    return d.toLocaleDateString('es-CO', { day: '2-digit', month: 'short' });
}
</script>

<template>
  <section class="tpl-card">
    <header class="tpl-head">
      <div>
        <p class="tpl-eyebrow">PLANTILLAS</p>
        <h2 class="tpl-title">Briefs ganadores</h2>
      </div>
      <button
        type="button"
        class="tpl-refresh"
        @click="store.fetchTemplates()"
        :disabled="store.loadingTemplates"
        aria-label="Refrescar plantillas"
      >Refrescar</button>
    </header>

    <p class="tpl-tagline">"Los planes que pasaron revisión se vuelven plantilla. Reúsalos."</p>

    <div v-if="store.loadingTemplates && !store.templates.length" class="tpl-skeleton">
      <div v-for="i in 4" :key="i" class="tpl-skeleton-row"></div>
    </div>

    <div v-else-if="!store.templates.length" class="tpl-empty">
      <div class="tpl-empty-num">—</div>
      <p class="tpl-empty-msg">"Cuando apruebes tu primer plan asistido, aparecerá aquí como plantilla reutilizable."</p>
    </div>

    <ul v-else class="tpl-list">
      <li v-for="t in store.templates" :key="t.id" class="tpl-item">
        <button type="button" class="tpl-item-btn" @click="loadTemplate(t)">
          <span class="tpl-item-name">{{ t.name }}</span>
          <span class="tpl-item-meta">
            <span class="tpl-tag">{{ t.plan_type }}</span>
            <span v-if="t.methodology" class="tpl-tag tpl-tag--muted">{{ t.methodology }}</span>
            <span v-if="t.is_public" class="tpl-tag tpl-tag--gold">Público</span>
            <span class="tpl-date">{{ fmtDate(t.created_at) }}</span>
          </span>
        </button>
      </li>
    </ul>
  </section>
</template>

<style scoped>
.tpl-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    position: relative;
    z-index: 1;
}
.tpl-head { display: flex; justify-content: space-between; align-items: flex-start; }
.tpl-eyebrow {
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.22em;
    color: var(--color-wc-text-tertiary);
    text-transform: uppercase;
    margin: 0 0 3px;
}
.tpl-title {
    font-family: var(--font-display);
    font-size: 18px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--color-wc-text);
    margin: 0;
}
.tpl-refresh {
    height: 26px;
    padding: 0 11px;
    border-radius: 999px;
    border: 1px solid var(--color-wc-border);
    background: transparent;
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    transition: border-color 0.15s var(--ease-out), color 0.15s var(--ease-out);
}
.tpl-refresh:hover:not(:disabled) { border-color: var(--color-wc-border-2); color: var(--color-wc-text); }
.tpl-refresh:disabled { opacity: 0.5; }

.tpl-tagline {
    font-family: var(--font-editorial);
    font-style: italic;
    font-size: 11.5px;
    color: var(--color-wc-gold);
    margin: 0;
    line-height: 1.5;
}

.tpl-skeleton { display: flex; flex-direction: column; gap: 8px; }
.tpl-skeleton-row {
    height: 44px;
    border-radius: 8px;
    background: var(--color-wc-bg-tertiary);
    border: 1px solid var(--color-wc-border);
    animation: tpl-pulse 1.5s ease-in-out infinite;
}
@keyframes tpl-pulse { 0%,100% { opacity: 0.5; } 50% { opacity: 0.85; } }

.tpl-empty { padding: 16px 6px 8px; text-align: center; }
.tpl-empty-num {
    font-family: var(--font-display);
    font-size: 48px;
    color: var(--color-wc-bg-tertiary);
    letter-spacing: 0.1em;
    line-height: 1;
    user-select: none;
    margin-bottom: 10px;
}
.tpl-empty-msg {
    font-family: var(--font-editorial);
    font-style: italic;
    font-size: 12px;
    color: var(--color-wc-text-tertiary);
    line-height: 1.55;
    margin: 0;
}

.tpl-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px; max-height: 260px; overflow-y: auto; }
.tpl-item-btn {
    width: 100%;
    text-align: left;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 8px;
    padding: 8px 10px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 3px;
    transition: background 0.12s var(--ease-out), border-color 0.12s var(--ease-out);
}
.tpl-item-btn:hover { background: rgba(255, 255, 255, 0.03); border-color: var(--color-wc-border); }
.tpl-item-name {
    font-family: var(--font-sans);
    font-size: 13px;
    color: var(--color-wc-text);
    line-height: 1.3;
}
.tpl-item-meta { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
.tpl-tag {
    font-family: var(--font-mono);
    font-size: 8.5px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text);
    background: var(--color-wc-red-soft);
    border-radius: 4px;
    padding: 2px 6px;
}
.tpl-tag--muted {
    color: var(--color-wc-text-tertiary);
    background: rgba(255, 255, 255, 0.04);
}
.tpl-tag--gold {
    color: #2a1e08;
    background: var(--color-wc-gold);
}
.tpl-date {
    font-family: var(--font-mono);
    font-size: 9px;
    color: var(--color-wc-text-tertiary);
    letter-spacing: 0.06em;
}
</style>
