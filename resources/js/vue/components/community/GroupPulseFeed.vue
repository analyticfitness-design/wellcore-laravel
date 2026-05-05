<script setup>
import { ref, onMounted, computed } from 'vue';
import { useGroupPulse } from '../../composables/useGroupPulse';

const { fetchFeed, loading } = useGroupPulse();

const time = ref('today');
const events = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const page = ref(1);

const TIME_OPTIONS = [
    { key: 'today', label: 'Hoy' },
    { key: 'week', label: 'Esta semana' },
    { key: 'all', label: 'Todos' },
];

const hasMore = computed(() => page.value < pagination.value.last_page);

async function load(reset = false) {
    if (reset) {
        page.value = 1;
        events.value = [];
    }
    const data = await fetchFeed({ time: time.value, type: 'all', page: page.value, perPage: 10 });
    if (!data) return;
    if (reset) events.value = data.events;
    else events.value.push(...data.events);
    pagination.value = data.pagination;
}

async function loadMore() {
    if (loading.value || !hasMore.value) return;
    page.value++;
    await load(false);
}

function setTime(key) {
    if (time.value === key) return;
    time.value = key;
    load(true);
}

onMounted(() => load(true));
</script>

<template>
  <!-- .wc-shell wrapper hereda los tokens del design system del dashboard
       (CommunityFeed no está scopeado bajo .wc-shell--dashboard). -->
  <div class="wc-shell">
    <section class="gpf-root">
      <div class="gpf-filters">
        <button
          v-for="opt in TIME_OPTIONS"
          :key="opt.key"
          type="button"
          class="gpf-filter"
          :class="{ active: time === opt.key }"
          @click="setTime(opt.key)"
        >
          {{ opt.label }}
        </button>
      </div>

      <div v-if="loading && events.length === 0" class="gpf-empty">
        Cargando latido del grupo...
      </div>

      <div v-else-if="events.length === 0" class="gpf-empty">
        Sin actividad del grupo en este rango.
      </div>

      <div v-else class="gpf-list">
        <article
          v-for="(ev, idx) in events"
          :key="idx"
          class="gpf-card"
          :data-type="ev.type"
        >
          <div v-if="ev.client_initials" class="gpf-avatar">{{ ev.client_initials }}</div>
          <div v-else class="gpf-avatar gpf-avatar--aggregate">{{ ev.people_count }}</div>

          <div class="gpf-body">
            <div class="gpf-headline">
              <strong v-if="ev.client_name">{{ ev.client_name }}</strong>
              <span>{{ ev.headline }}</span>
            </div>
            <div v-if="ev.delta" class="gpf-meta">{{ ev.delta }}</div>
            <div v-if="ev.extra" class="gpf-meta">{{ ev.extra }}</div>
            <div v-if="ev.preview_initials" class="gpf-stack" aria-hidden="true">
              <span
                v-for="(init, i) in ev.preview_initials"
                :key="i"
                class="gpf-stack-item"
              >{{ init }}</span>
            </div>
            <div v-if="ev.minutes_ago !== undefined" class="gpf-time tnum">
              hace {{ ev.minutes_ago }}min
            </div>
          </div>
        </article>
      </div>

      <button
        v-if="hasMore"
        type="button"
        class="gpf-load-more"
        :disabled="loading"
        @click="loadMore"
      >
        {{ loading ? 'Cargando...' : 'Cargar más' }}
      </button>
    </section>
  </div>
</template>

<style scoped>
/* Component-specific. Tokens vienen del .wc-shell wrapper. */
.gpf-root {
    display: flex;
    flex-direction: column;
    gap: var(--s12);
    color: var(--wc-text);
    font-family: var(--fs);
}

/* Filter pills — match .chip pattern from wc-shell.css */
.gpf-filters {
    display: flex;
    gap: var(--s8);
    flex-wrap: wrap;
}
.gpf-filter {
    display: inline-flex;
    align-items: center;
    padding: 5px 12px;
    border-radius: var(--r-pill);
    background: transparent;
    color: var(--wc-text-2);
    border: 1px solid var(--wc-border);
    font: 600 11px/1 var(--fs);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    cursor: pointer;
    transition: color 180ms var(--ease-out), background 180ms var(--ease-out), border-color 180ms var(--ease-out);
}
.gpf-filter:hover {
    color: var(--wc-text);
    background: rgba(255, 255, 255, 0.04);
}
.gpf-filter.active {
    color: #FCA5A5;
    background: rgba(220, 38, 38, 0.12);
    border-color: rgba(220, 38, 38, 0.30);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

/* Empty state */
.gpf-empty {
    padding: var(--s24);
    text-align: center;
    color: var(--wc-text-3);
    font-size: 13px;
}

/* Event cards list */
.gpf-list {
    display: flex;
    flex-direction: column;
    gap: var(--s8);
}
.gpf-card {
    display: flex;
    gap: var(--s12);
    padding: var(--s12) var(--s16);
    background: var(--wc-bg3);
    border: 1px solid var(--wc-border);
    border-left: 3px solid var(--wc-accent);
    border-radius: var(--r-md);
    box-shadow: var(--sh-soft);
    transition: background 200ms var(--ease-out), transform 200ms var(--ease-spring), box-shadow 200ms var(--ease-out);
}
.gpf-card:hover {
    background: var(--wc-bg4);
    transform: translateY(-1px);
}
.gpf-card[data-type="aggregate"] { border-left-color: var(--wc-text-3); }
.gpf-card[data-type="streak_milestone"] { border-left-color: var(--wc-amber); }
.gpf-card[data-type="achievement"] { border-left-color: var(--wc-purple); }

/* Avatar — mismo conic ring pattern que .wc-shell .avatar (wc-shell.css:174).
   Los hex hardcoded reproducen el gradiente canónico del design system,
   no introducen colores nuevos. */
.gpf-avatar {
    width: 42px;
    height: 42px;
    border-radius: var(--r-pill);
    flex-shrink: 0;
    position: relative;
    background:
      linear-gradient(135deg, #DC2626 0%, #7F1D1D 100%) padding-box,
      conic-gradient(from 140deg, #DC2626, #71717A 50%, #DC2626 100%) border-box;
    border: 2px solid transparent;
    display: grid;
    place-items: center;
    font-family: var(--fd);
    font-weight: 700;
    font-size: 14px;
    color: #fff;
    letter-spacing: 0.02em;
}
.gpf-avatar--aggregate {
    background:
      linear-gradient(135deg, var(--wc-amber) 0%, #92400E 100%) padding-box,
      conic-gradient(from 140deg, var(--wc-amber), #71717A 50%, var(--wc-amber) 100%) border-box;
    color: #1A1A1A;
}

.gpf-body { flex: 1; min-width: 0; }
.gpf-headline {
    font-size: 14px;
    line-height: 1.4;
    color: var(--wc-text-2);
}
.gpf-headline strong {
    color: var(--wc-text);
    font-weight: 600;
    margin-right: 4px;
}
.gpf-meta {
    font-size: 12px;
    color: var(--wc-text-3);
    margin-top: 4px;
}

/* Mini avatar stack (preview de eventos agregados) */
.gpf-stack {
    display: flex;
    margin-top: var(--s8);
}
.gpf-stack-item {
    width: 24px;
    height: 24px;
    border-radius: var(--r-pill);
    background: var(--wc-bg4);
    border: 2px solid var(--wc-bg2);
    margin-left: -6px;
    font: 600 10px/1 var(--fs);
    display: grid;
    place-items: center;
    color: var(--wc-text-2);
}
.gpf-stack-item:first-child { margin-left: 0; }

.gpf-time {
    font-size: 11px;
    color: var(--wc-text-3);
    margin-top: 6px;
    text-align: right;
    text-transform: lowercase;
}

/* Load-more — secondary button style consistent with dashboard */
.gpf-load-more {
    padding: 10px var(--s16);
    background: var(--wc-bg3);
    border: 1px solid var(--wc-border);
    border-radius: var(--r-sm);
    color: var(--wc-text-2);
    font: 600 13px/1 var(--fs);
    letter-spacing: 0.02em;
    cursor: pointer;
    transition: background 200ms var(--ease-out), color 200ms var(--ease-out);
}
.gpf-load-more:hover:not(:disabled) {
    background: var(--wc-bg4);
    color: var(--wc-text);
}
.gpf-load-more:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
