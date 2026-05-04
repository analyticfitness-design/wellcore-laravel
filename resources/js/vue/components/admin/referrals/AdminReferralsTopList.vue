<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';

const props = defineProps({
  referidores: { type: Array, default: () => [] },
});

const isEmpty  = computed(() => !props.referidores?.length);
const podium   = computed(() => (props.referidores ?? []).slice(0, 3));
const restList = computed(() => (props.referidores ?? []).slice(3, 5));

const podiumColor = (idx) => ['gold', 'silver', 'bronze'][idx] ?? 'neutral';
</script>

<template>
  <section class="top-refs" aria-labelledby="top-refs-title">
    <header class="refs-header">
      <h2 id="top-refs-title" class="refs-title">TOP REFERIDORES</h2>
      <span class="refs-period">PERÍODO</span>
    </header>

    <!-- Empty state -->
    <div v-if="isEmpty" class="refs-empty">
      <div class="refs-empty-num" aria-hidden="true">—</div>
      <p class="refs-empty-msg">
        "Sin referidos convertidos en el período. El ranking se construye con cada pago calificado."
      </p>
      <RouterLink to="/admin/clients" class="refs-empty-cta">
        VER CLIENTES <span aria-hidden="true">→</span>
      </RouterLink>
    </div>

    <!-- Podium + rest -->
    <div v-else class="refs-content">
      <div class="podium" role="list" aria-label="Podio top 3 referidores">
        <article
          v-for="(ref, idx) in podium"
          :key="ref.referrer_id ?? idx"
          class="podium-card"
          :class="`podium-card--${podiumColor(idx)}`"
          role="listitem"
        >
          <div class="podium-rank" aria-label="Posición">#{{ idx + 1 }}</div>
          <div class="podium-name">{{ ref.name }}</div>
          <div class="podium-count" :aria-label="`${ref.qualified_count} referidos calificados`">{{ ref.qualified_count }}</div>
          <div class="podium-count-label">QUALIFIED</div>
        </article>
      </div>

      <ul v-if="restList.length" class="refs-rest" aria-label="Posiciones 4 y 5">
        <li
          v-for="(ref, idx) in restList"
          :key="ref.referrer_id ?? idx + 3"
          class="ref-row"
        >
          <span class="ref-row-rank">#{{ idx + 4 }}</span>
          <span class="ref-row-name">{{ ref.name }}</span>
          <span class="ref-row-count" role="img" :aria-label="`${ref.qualified_count} qualified`">{{ ref.qualified_count }}</span>
        </li>
      </ul>
    </div>
  </section>
</template>

<style scoped>
.top-refs {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 18px;
}
.refs-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.refs-title {
    font-family: var(--font-display);
    font-size: 13px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text);
    margin: 0;
}
.refs-period {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-3);
}

/* ── Empty state ──────────────────────────────────────────────────────── */
.refs-empty { padding: 18px 8px 14px; text-align: center; }
.refs-empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--c-surface-2);
    letter-spacing: 0.8px;
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}
.refs-empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: var(--c-text-3);
    line-height: 1.55;
    margin: 0 0 16px;
    text-wrap: balance;
}
.refs-empty-cta {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    color: var(--c-text-2);
    text-decoration: none;
    text-transform: uppercase;
    border-bottom: 1px solid var(--c-border);
    padding-bottom: 4px;
    transition: color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
}
.refs-empty-cta:hover { color: var(--c-text); border-bottom-color: var(--c-accent); }

/* ── Content ──────────────────────────────────────────────────────────── */
.refs-content { display: flex; flex-direction: column; gap: 14px; }
.podium { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }
.podium-card {
    border-radius: 10px;
    padding: 12px 10px;
    text-align: center;
    border: 1px solid var(--c-border);
    background: rgba(24, 24, 24, 0.6);
}
.podium-card--gold   { border-color: rgba(212,160,76,.3);  background: linear-gradient(180deg,rgba(212,160,76,.1),rgba(24,24,24,.6)); }
.podium-card--silver { border-color: rgba(163,163,163,.25); background: linear-gradient(180deg,rgba(163,163,163,.08),rgba(24,24,24,.6)); }
.podium-card--bronze { border-color: rgba(180,83,9,.3);    background: linear-gradient(180deg,rgba(180,83,9,.08),rgba(24,24,24,.6)); }

.podium-rank {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 1.6px;
    color: var(--c-text-3);
    margin-bottom: 4px;
}
.podium-card--gold .podium-rank { color: #C8A769; }
.podium-name {
    font-family: var(--font-sans);
    font-size: 12px;
    font-weight: 700;
    color: var(--c-text);
    margin-bottom: 8px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.podium-count {
    font-family: var(--font-display);
    font-size: 24px;
    color: #60A5FA;
    line-height: 1;
}
.podium-count-label {
    font-family: var(--font-display);
    font-size: 7px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
    margin-top: 2px;
}

/* ── Rest list ────────────────────────────────────────────────────────── */
.refs-rest { list-style: none; margin: 0; padding: 0; border-top: 1px solid var(--c-border); }
.ref-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,.04);
}
.ref-row:last-child { border-bottom: none; }
.ref-row-rank {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    color: var(--c-text-3);
    flex-shrink: 0;
    width: 28px;
}
.ref-row-name {
    flex: 1;
    font-size: 12px;
    font-weight: 500;
    color: var(--c-text-2);
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.ref-row-count {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 700;
    color: #60A5FA;
}
</style>
