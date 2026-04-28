<script setup>
import { computed } from 'vue';
import { formatNumber } from '@/composables/useFormat';

const props = defineProps({
    form: { type: Object, required: true },
});

const tagClass = computed(() => ({
    'Inscripcion': 'form-tag--amber',
    'Cliente':     'form-tag--sky',
    'RISE':        'form-tag--violet',
}[props.form.tag] ?? 'form-tag--default'));

const weekDelta = computed(() => {
    const m = props.form.metrics;
    if (!m || m.last_week === 0) return null;
    return Math.round(((m.this_week - m.last_week) / m.last_week) * 100);
});
</script>

<template>
  <section class="form-stats" aria-label="Métricas del formulario seleccionado">
    <!-- Header -->
    <div class="form-stats__header">
      <span class="form-tag" :class="tagClass">{{ form.tag }}</span>
      <h2 class="form-stats__name">{{ form.name.toUpperCase() }}</h2>
      <p class="form-stats__desc">{{ form.description }}</p>
    </div>

    <!-- KPIs -->
    <div v-if="form.has_submissions && form.metrics" class="form-stats__kpis">
      <div class="kpi-box">
        <span class="kpi-label">TOTAL</span>
        <span class="kpi-val">{{ formatNumber(form.metrics.total) }}</span>
      </div>
      <div class="kpi-box">
        <span class="kpi-label">ESTA SEMANA</span>
        <span class="kpi-val">{{ formatNumber(form.metrics.this_week) }}</span>
        <span
          v-if="weekDelta !== null"
          class="kpi-delta"
          :class="weekDelta >= 0 ? 'kpi-delta--up' : 'kpi-delta--down'"
        >
          {{ weekDelta >= 0 ? '+' : '' }}{{ weekDelta }}% vs ant.
        </span>
      </div>
      <div class="kpi-box">
        <span class="kpi-label">SEMANA PASADA</span>
        <span class="kpi-val">{{ formatNumber(form.metrics.last_week) }}</span>
      </div>
    </div>

    <!-- No submissions empty state -->
    <div v-else class="form-stats__no-sub">
      <div class="empty-num" aria-hidden="true">—</div>
      <p class="empty-msg">"Este formulario actualiza datos existentes y no registra submissions independientes."</p>
      <a
        :href="`/admin/forms-preview/${form.area}/${form.slug}`"
        target="_blank"
        class="empty-cta"
        rel="noopener"
      >VER PREVIEW →</a>
    </div>
  </section>
</template>

<style scoped>
.form-stats {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    overflow: hidden;
}

.form-stats__header {
    padding: 16px 16px 14px;
    border-bottom: 1px solid var(--color-wc-border);
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-tag {
    display: inline-flex;
    align-self: flex-start;
    align-items: center;
    padding: 2px 8px;
    border-radius: 99px;
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    font-weight: 500;
    border: 1px solid transparent;
}
.form-tag--amber { background: var(--color-wc-amber-soft); color: var(--color-wc-amber-text); border-color: rgba(245,158,11,0.2); }
.form-tag--sky   { background: var(--color-wc-blue-soft);  color: var(--color-wc-blue-text);  border-color: rgba(59,130,246,0.2); }
.form-tag--violet{ background: rgba(139,92,246,0.1);       color: #a78bfa;                    border-color: rgba(139,92,246,0.2); }
.form-tag--default{ background: rgba(255,255,255,0.05); color: var(--color-wc-text-secondary); }

.form-stats__name {
    font-family: var(--font-display);
    font-size: 20px;
    letter-spacing: 0.04em;
    color: var(--color-wc-text);
    margin: 0;
    line-height: 1.1;
}
.form-stats__desc {
    font-family: var(--font-sans);
    font-size: 11px;
    line-height: 1.5;
    color: var(--color-wc-text-secondary);
    margin: 0;
}

.form-stats__kpis {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
}
.kpi-box {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 14px 16px;
    border-right: 1px solid var(--color-wc-border);
}
.kpi-box:last-child { border-right: none; }

.kpi-label {
    font-family: var(--font-mono);
    font-size: 8px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    line-height: 1;
}
.kpi-val {
    font-family: var(--font-data);
    font-size: 22px;
    font-feature-settings: 'tnum' 1;
    color: var(--color-wc-text);
    line-height: 1;
}
.kpi-delta {
    font-family: var(--font-mono);
    font-size: 8px;
    letter-spacing: 0.12em;
}
.kpi-delta--up   { color: var(--color-wc-green-text); }
.kpi-delta--down { color: var(--color-wc-red-text); }

.form-stats__no-sub {
    padding: 28px 16px 20px;
    text-align: center;
}
.empty-num {
    font-family: var(--font-display);
    font-size: 40px;
    color: var(--color-wc-bg-tertiary);
    letter-spacing: 0.1em;
    line-height: 1;
    margin-bottom: 10px;
    user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial);
    font-style: italic;
    font-size: 11px;
    color: var(--color-wc-text-tertiary);
    line-height: 1.55;
    margin: 0 0 14px;
    text-wrap: balance;
}
.empty-cta {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.22em;
    color: var(--color-wc-text-secondary);
    text-decoration: none;
    text-transform: uppercase;
    border-bottom: 1px solid var(--color-wc-border);
    padding-bottom: 4px;
    transition: color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
}
.empty-cta:hover {
    color: var(--color-wc-text);
    border-bottom-color: var(--color-wc-accent);
}
</style>
