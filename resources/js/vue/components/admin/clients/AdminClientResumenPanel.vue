<script setup>
import { computed } from 'vue';
import AdminClientKPIs from './AdminClientKPIs.vue';

const props = defineProps({
    client: { type: Object, default: null },
});

const lastCheckin = computed(() => (props.client?.checkins || [])[0] || null);
const lastPayment = computed(() => (props.client?.payments || [])[0] || null);
</script>

<template>
  <div class="resumen-panel">
    <AdminClientKPIs :client="client" />

    <div class="resumen-grid">
      <article class="card">
        <header class="card-head">
          <span class="card-eyebrow">ULTIMO CHECK-IN</span>
        </header>
        <div v-if="lastCheckin" class="card-body">
          <div class="line">
            <span class="line-label">FECHA</span>
            <span class="line-mono">{{ lastCheckin.date || '—' }}</span>
          </div>
          <div class="line">
            <span class="line-label">REVISION COACH</span>
            <span class="pill" :class="lastCheckin.reviewed ? 'pill--success' : 'pill--amber'">
              {{ lastCheckin.reviewed ? 'REVISADO' : 'PENDIENTE' }}
            </span>
          </div>
          <p v-if="lastCheckin.note" class="line-quote">{{ lastCheckin.note }}</p>
        </div>
        <div v-else class="card-empty">
          <p class="empty-msg">"Aún no hay check-ins registrados."</p>
        </div>
      </article>

      <article class="card">
        <header class="card-head">
          <span class="card-eyebrow">ULTIMO PAGO</span>
        </header>
        <div v-if="lastPayment" class="card-body">
          <div class="line">
            <span class="line-label">FECHA</span>
            <span class="line-mono">{{ lastPayment.date || '—' }}</span>
          </div>
          <div class="line">
            <span class="line-label">MONTO</span>
            <span class="line-data">{{ lastPayment.amount }} {{ (lastPayment.currency || 'COP').toUpperCase() }}</span>
          </div>
          <div class="line">
            <span class="line-label">ESTADO</span>
            <span class="pill" :class="lastPayment.status === 'approved' ? 'pill--success' : 'pill--amber'">
              {{ (lastPayment.status || '').toUpperCase() || 'PENDIENTE' }}
            </span>
          </div>
        </div>
        <div v-else class="card-empty">
          <p class="empty-msg">"Sin pagos en el historial todavía."</p>
        </div>
      </article>

      <article class="card card--full">
        <header class="card-head">
          <span class="card-eyebrow">CONTEXTO RAPIDO</span>
        </header>
        <div class="card-body">
          <div v-if="client?.bio" class="line-quote">{{ client.bio }}</div>
          <div v-else class="card-empty">
            <p class="empty-msg">"Sin biografía. El admin puede pedirla en el próximo check-in."</p>
          </div>

          <div class="meta-grid">
            <div class="meta-item">
              <span class="line-label">CIUDAD</span>
              <span class="line-mono">{{ client?.city || '—' }}</span>
            </div>
            <div class="meta-item">
              <span class="line-label">NACIMIENTO</span>
              <span class="line-mono">{{ client?.birth_date || '—' }}</span>
            </div>
            <div class="meta-item">
              <span class="line-label">REFERIDO POR</span>
              <span class="line-mono">{{ client?.referred_by || '—' }}</span>
            </div>
            <div class="meta-item">
              <span class="line-label">CODIGO REFERIDO</span>
              <span class="line-mono">{{ client?.referral_code || '—' }}</span>
            </div>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>

<style scoped>
.resumen-panel { display: flex; flex-direction: column; gap: 14px; }

.resumen-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}
@media (min-width: 720px) {
    .resumen-grid { grid-template-columns: 1fr 1fr; }
    .card--full { grid-column: 1 / -1; }
}

.card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.65);
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.card-head { display: flex; align-items: center; justify-content: space-between; }
.card-eyebrow {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.card-body { display: flex; flex-direction: column; gap: 8px; }

.line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    padding-bottom: 8px;
}
.line:last-child { border-bottom: none; padding-bottom: 0; }

.line-label {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.line-mono {
    font-family: var(--font-display);
    font-size: 11px;
    letter-spacing: 1.0px;
    color: var(--c-text-2);
    text-transform: uppercase;
}
.line-data {
    font-family: var(--font-display);
    font-feature-settings: 'tnum' 1;
    font-size: 14px;
    font-weight: 600;
    color: var(--c-text);
}
.line-quote {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 13px;
    color: var(--c-text-2);
    line-height: 1.55;
    text-wrap: balance;
}

.pill { display: inline-block; font-family: var(--font-display); font-size: 8px; letter-spacing: 1.6px; text-transform: uppercase; padding: 3px 7px; border-radius: var(--r-pill, 999px); line-height: 1.4; }
.pill--success { background: rgba(16,185,129,0.1); color: #34D399; }
.pill--amber   { background: rgba(245,158,11,0.1); color: #FCD34D; }

.card-empty { padding: 8px 0; }
.empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: var(--c-text-3);
    margin: 0;
}

.meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 8px;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.04);
}
.meta-item { display: flex; flex-direction: column; gap: 2px; }
</style>
