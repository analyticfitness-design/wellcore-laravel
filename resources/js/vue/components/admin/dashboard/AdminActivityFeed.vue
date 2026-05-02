<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';

const props = defineProps({
  // [{ buyerName, plan, amount, method, timeAgo, ts? }, ...]
  payments: { type: Array, default: () => [] },
  // [{ nombre, email, plan, status, timeAgo, ts? }, ...]
  inscriptions: { type: Array, default: () => [] },
  // Limite de items mostrados (default 8)
  limit: { type: Number, default: 8 },
});

// Mergear payments + inscriptions, ordenar desc por ts si existe, sino preservar el
// orden recibido (asumiendo que el API ya los devuelve sorted desc).
const feed = computed(() => {
  const taggedPayments = (props.payments || []).map(p => ({
    type: 'payment',
    title: p.buyerName || 'Cliente',
    subtitle: p.plan || '',
    amount: p.amount,
    method: p.method,
    timeAgo: p.timeAgo,
    ts: p.ts || 0,
  }));
  const taggedInscriptions = (props.inscriptions || []).map(i => ({
    type: 'inscription',
    title: i.nombre || i.email || 'Lead',
    subtitle: i.plan || '',
    email: i.email,
    status: i.status,
    timeAgo: i.timeAgo,
    ts: i.ts || 0,
  }));

  const merged = [...taggedPayments, ...taggedInscriptions];

  // Si los items tienen ts, ordenar desc; sino dejar como vienen (intercalados)
  const hasTs = merged.some(m => m.ts > 0);
  if (hasTs) merged.sort((a, b) => (b.ts || 0) - (a.ts || 0));

  return merged.slice(0, props.limit);
});

const empty = computed(() => feed.value.length === 0);
</script>

<template>
  <section class="activity-feed">
    <header class="feed-header">
      <h2 class="feed-title">ACTIVIDAD RECIENTE</h2>
      <RouterLink to="/admin/feed" class="feed-link-all">VER TODO</RouterLink>
    </header>

    <p v-if="empty" class="feed-empty">
      Sin movimientos recientes. La actividad aparece aqui cuando entran pagos o inscripciones nuevas.
    </p>

    <ul v-else class="feed-list">
      <li
        v-for="(item, idx) in feed"
        :key="`${item.type}-${idx}`"
        class="feed-item"
      >
        <div class="feed-timeline">
          <span
            class="feed-dot"
            :class="`feed-dot--${item.type}`"
          ></span>
          <span v-if="idx < feed.length - 1" class="feed-tail"></span>
        </div>
        <div class="feed-body">
          <span
            class="feed-tag"
            :class="`feed-tag--${item.type}`"
          >{{ item.type === 'payment' ? 'PAGO' : 'INSCRIPCION' }}</span>

          <p class="feed-name">{{ item.title }}</p>

          <div v-if="item.type === 'payment'" class="feed-meta">
            <span class="feed-amount">${{ item.amount }}</span>
            <span class="feed-meta-sep" aria-hidden="true">·</span>
            <span class="feed-method">{{ item.method }}</span>
            <span class="feed-meta-sep" aria-hidden="true">·</span>
            <span class="feed-plan">{{ item.subtitle }}</span>
            <span class="feed-meta-sep" aria-hidden="true">·</span>
            <span class="feed-time">{{ item.timeAgo }}</span>
          </div>

          <div v-else class="feed-meta">
            <span class="feed-plan">{{ item.subtitle }}</span>
            <span v-if="item.status" class="feed-status">{{ formatStatus(item.status) }}</span>
            <span class="feed-meta-sep" aria-hidden="true">·</span>
            <span class="feed-time">{{ item.timeAgo }}</span>
          </div>

          <RouterLink
            v-if="item.type === 'inscription'"
            to="/admin/inscriptions"
            class="feed-cta"
          >
            Contactar <span aria-hidden="true">→</span>
          </RouterLink>
        </div>
      </li>
    </ul>
  </section>
</template>

<script>
function formatStatus(status) {
  if (!status) return '';
  return status.replaceAll('_', ' ').toUpperCase();
}
</script>

<style scoped>
/* ============================================================================
   AdminActivityFeed — timeline vertical con dots + pills color-coded.
   v2: Oswald tags/titles, Raleway names/meta, tokens v2.
   Mobile: stack normal. Desktop: card padding mas grande.
   ============================================================================ */

.activity-feed {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: var(--c-surface);
    padding: 18px;
}
.feed-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.feed-title {
    font-family: var(--font-display);
    font-size: 16px; font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--c-text);
    margin: 0;
}
.feed-link-all {
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-3);
    text-decoration: none;
    transition: color 0.15s var(--ease-out, ease);
}
.feed-link-all:hover { color: var(--c-text); }

.feed-empty {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 13px;
    color: var(--c-text-3);
    text-align: center;
    padding: 24px 12px;
    margin: 0;
    line-height: var(--lh-body, 1.65);
}

.feed-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
}
.feed-item {
    display: flex;
    gap: 12px;
    padding: 11px 0;
    border-bottom: 1px solid var(--c-border);
}
.feed-item:last-child { border-bottom: none; }

.feed-timeline {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
    padding-top: 6px;
}
.feed-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    flex-shrink: 0;
}
.feed-dot--payment     { background: #34D399; box-shadow: 0 0 6px rgba(52,211,153,0.4); }
.feed-dot--inscription { background: #60A5FA; box-shadow: 0 0 6px rgba(96,165,250,0.4); }

.feed-tail {
    flex: 1;
    width: 1px;
    background: rgba(255,255,255,0.06);
    margin-top: 5px;
    min-height: 16px;
}

.feed-body {
    flex: 1;
    min-width: 0;
}
.feed-tag {
    display: inline-block;
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: var(--r-pill, 999px);
    margin-bottom: 5px;
}
.feed-tag--payment     { background: var(--c-success-dim, rgba(22,163,74,0.12)); color: #34D399; }
.feed-tag--inscription { background: rgba(59,130,246,0.12); color: #60A5FA; }

.feed-name {
    font-family: var(--font-sans);
    font-size: 15px; font-weight: 600;
    color: var(--c-text);
    margin: 0 0 4px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.feed-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.feed-meta-sep {
    color: var(--c-text-3);
    opacity: 0.5;
    font-size: 10px;
}
.feed-amount {
    font-family: var(--font-display);
    font-size: 14px; font-weight: 700;
    color: #34D399;
    letter-spacing: var(--ls-display, -0.02em);
    font-variant-numeric: tabular-nums; font-feature-settings: "tnum";
}
.feed-method,
.feed-plan,
.feed-time {
    font-family: var(--font-sans);
    font-size: 12px; font-weight: 400;
    color: var(--c-text-3);
}
.feed-status {
    display: inline-block;
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600;
    letter-spacing: 1.2px;
    padding: 2px 7px;
    border-radius: var(--r-pill, 999px);
    background: var(--c-amber-dim);
    color: var(--c-amber, #D4A80E);
    text-transform: uppercase;
}
.feed-cta {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-family: var(--font-display);
    font-size: 11px; font-weight: 600;
    letter-spacing: 1.6px;
    color: #60A5FA;
    text-transform: uppercase;
    margin-top: 6px;
    min-height: 44px;
    text-decoration: none;
    transition: opacity 0.15s var(--ease-out, ease);
}
.feed-cta:hover { opacity: 0.7; }
</style>
