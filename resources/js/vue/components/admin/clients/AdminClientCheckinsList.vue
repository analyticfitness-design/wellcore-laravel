<script setup>
import { computed } from 'vue';

const props = defineProps({
    client: { type: Object, default: null },
});

const checkins = computed(() => props.client?.checkins || []);
</script>

<template>
  <div class="checkins-panel">
    <article class="card">
      <header class="card-head">
        <span class="card-eyebrow">TIMELINE · ULTIMOS {{ checkins.length }}</span>
      </header>

      <div v-if="checkins.length" class="timeline">
        <div
          v-for="(c, i) in checkins"
          :key="i"
          class="timeline-row"
        >
          <div class="timeline-marker" :class="c.reviewed ? 'marker--reviewed' : 'marker--pending'">
            <span class="marker-glyph">{{ c.reviewed ? '✓' : '·' }}</span>
          </div>
          <div class="timeline-body">
            <header class="row-head">
              <span class="line-mono">{{ c.date || '—' }}</span>
              <span class="pill" :class="c.reviewed ? 'pill--success' : 'pill--amber'">
                {{ c.reviewed ? 'REVISADO' : 'PENDIENTE' }}
              </span>
            </header>
            <p v-if="c.note" class="row-note">{{ c.note }}</p>
            <p v-else class="row-empty">Sin comentario del cliente.</p>
          </div>
        </div>
      </div>

      <div v-else class="card-empty">
        <div class="empty-num">—</div>
        <p class="empty-msg">"Sin check-ins registrados todavía. Aparecerán aquí en cuanto el cliente envíe el primero."</p>
      </div>
    </article>
  </div>
</template>

<style scoped>
.checkins-panel { display: flex; flex-direction: column; gap: 12px; }

.card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.65);
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.card-head { display: flex; align-items: center; justify-content: space-between; }
.card-eyebrow {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
}

.timeline { display: flex; flex-direction: column; gap: 0; }
.timeline-row {
    display: grid;
    grid-template-columns: 28px 1fr;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    position: relative;
}
.timeline-row:last-child { border-bottom: none; }
.timeline-row::before {
    content: '';
    position: absolute;
    left: 13px;
    top: 24px;
    bottom: -12px;
    width: 1px;
    background: rgba(255, 255, 255, 0.06);
}
.timeline-row:last-child::before { display: none; }

.timeline-marker {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid;
    font-family: var(--font-display);
    font-size: 14px;
    flex-shrink: 0;
    z-index: 1;
    background: var(--c-surface);
}
.marker--reviewed {
    color: #34D399;
    border-color: rgba(16, 185, 129, 0.4);
    background: rgba(16,185,129,0.1);
}
.marker--pending {
    color: #FCD34D;
    border-color: rgba(245, 158, 11, 0.4);
    background: rgba(245,158,11,0.1);
}

.timeline-body { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.row-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; }

.line-mono {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.4px;
    color: var(--c-text-2);
    text-transform: uppercase;
}

.pill { display: inline-block; font-family: var(--font-display); font-size: 8px; letter-spacing: 1.6px; text-transform: uppercase; padding: 3px 7px; border-radius: var(--r-pill, 999px); line-height: 1.4; }
.pill--success { background: rgba(16,185,129,0.1); color: #34D399; }
.pill--amber   { background: rgba(245,158,11,0.1); color: #FCD34D; }

.row-note {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--c-text-2);
    line-height: 1.55;
    margin: 4px 0 0;
}
.row-empty {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 11px;
    color: var(--c-text-3);
    margin: 4px 0 0;
}

.card-empty {
    text-align: center;
    padding: 24px 8px 16px;
}
.empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--c-surface-2);
    letter-spacing: 0.8px;
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: var(--c-text-3);
    margin: 0;
    max-width: 380px;
    margin-inline: auto;
    text-wrap: balance;
}
</style>
