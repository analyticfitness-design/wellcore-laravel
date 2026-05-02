<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';

const props = defineProps({
    client: { type: Object, default: null },
});

const router = useRouter();

const STATUS_VARIANT = {
    activo: 'success',
    pendiente: 'amber',
    inactivo: 'neutral',
    suspendido: 'danger',
    congelado: 'info',
};
const STATUS_LABEL = {
    activo: 'ACTIVO',
    pendiente: 'PENDIENTE',
    inactivo: 'INACTIVO',
    suspendido: 'SUSPENDIDO',
    congelado: 'CONGELADO',
};

const initial = computed(() => (props.client?.name || '?').trim().charAt(0).toUpperCase() || '?');
const statusVariant = computed(() => {
    const k = typeof props.client?.status === 'string' ? props.client.status : (props.client?.status?.value || '');
    return STATUS_VARIANT[k] || 'neutral';
});
const statusLabel = computed(() => {
    const k = typeof props.client?.status === 'string' ? props.client.status : (props.client?.status?.value || '');
    return STATUS_LABEL[k] || (k ? k.toUpperCase() : '—');
});

function goBack() {
    if (window.history.length > 1) router.back();
    else router.push('/admin/clients');
}
</script>

<template>
  <header class="client-header">
    <button type="button" class="back-link" @click="goBack" aria-label="Volver al listado">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
        <path d="M15.75 19.5 8.25 12l7.5-7.5" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      <span>VOLVER</span>
    </button>

    <div class="header-grid">
      <div class="head-identity">
        <span class="avatar-big" aria-hidden="true">
          <img v-if="client?.avatar_url" :src="client.avatar_url" :alt="`Avatar de ${client?.name}`" />
          <span v-else>{{ initial }}</span>
        </span>
        <div class="head-info">
          <span class="eyebrow">CLIENTE · {{ client?.client_code || 'SIN CODIGO' }}</span>
          <h1 class="name-display">{{ client?.name || 'Cargando...' }}</h1>
          <div class="meta-row">
            <span v-if="statusLabel" class="pill" :class="`pill--${statusVariant}`">{{ statusLabel }}</span>
            <span v-if="client?.plan_label" class="pill pill--info">{{ client.plan_label.toUpperCase() }}</span>
            <span v-if="client?.email" class="meta-mail">{{ client.email }}</span>
            <span v-if="client?.phone" class="meta-mono">{{ client.phone }}</span>
          </div>
        </div>
      </div>

      <div class="head-stats">
        <div class="stat-block">
          <span class="stat-label">REGISTRO</span>
          <span class="stat-mono">{{ client?.registeredAt || client?.created_at || '—' }}</span>
        </div>
        <div class="stat-block">
          <span class="stat-label">ULTIMO LOGIN</span>
          <span class="stat-mono">{{ client?.lastLogin || '—' }}</span>
        </div>
        <div v-if="client?.coachName" class="stat-block">
          <span class="stat-label">COACH</span>
          <span class="stat-mono">{{ client.coachName }}</span>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
.client-header {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 4px 0 8px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    align-self: flex-start;
    background: transparent;
    border: none;
    color: var(--c-text-3);
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    cursor: pointer;
    padding: 6px 4px;
    transition: color 0.15s var(--ease-out, ease);
}
.back-link:hover { color: var(--c-text); }
.back-link:focus-visible {
    outline: 1px solid var(--c-accent);
    outline-offset: 2px;
    border-radius: 4px;
}

.header-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 18px;
    align-items: stretch;
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.65);
    padding: 18px;
}
@media (min-width: 1024px) {
    .header-grid {
        grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr);
        gap: 24px;
    }
}

.head-identity {
    display: flex;
    align-items: center;
    gap: 16px;
    min-width: 0;
}

.avatar-big {
    width: 72px;
    height: 72px;
    flex-shrink: 0;
    border-radius: 50%;
    background: rgba(220, 38, 38, 0.12);
    border: 1px solid rgba(220, 38, 38, 0.25);
    color: #F87171;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-size: 32px;
    letter-spacing: 0.04em;
    overflow: hidden;
}
.avatar-big img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.head-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
    flex: 1;
}
.eyebrow {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.name-display {
    font-family: var(--font-display);
    font-size: clamp(28px, 4vw, 44px);
    letter-spacing: 0.04em;
    color: var(--c-text);
    margin: 0;
    line-height: 1;
}
.meta-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 4px;
}
.meta-mail {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--c-text-2);
}
.meta-mono {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 1.0px;
    color: var(--c-text-3);
    text-transform: uppercase;
}

.pill,
.pill--success,
.pill--neutral,
.pill--amber,
.pill--danger,
.pill--info {
    display: inline-block;
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: var(--r-pill, 999px);
    line-height: 1.4;
}
.pill--success { background: rgba(16,185,129,0.1); color: #34D399; }
.pill--neutral { background: rgba(255, 255, 255, 0.04); color: var(--c-text-3); }
.pill--amber   { background: rgba(245,158,11,0.1); color: #FCD34D; }
.pill--danger  { background: var(--c-accent-dim); color: #F87171; }
.pill--info    { background: rgba(59,130,246,0.1); color: #60A5FA; }

.head-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
@media (min-width: 1024px) {
    .head-stats { grid-template-columns: 1fr 1fr 1fr; }
}
.stat-block {
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    padding: 10px 12px;
    background: rgba(255, 255, 255, 0.02);
    min-width: 0;
}
.stat-label {
    display: block;
    font-family: var(--font-display);
    font-size: 7px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
    margin-bottom: 4px;
}
.stat-mono {
    font-family: var(--font-display);
    font-size: 11px;
    letter-spacing: 1.0px;
    color: var(--c-text-2);
    text-transform: uppercase;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
}
</style>
