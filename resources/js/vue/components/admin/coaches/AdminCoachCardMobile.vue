<script setup>
import { useAdminCoachDetailStore } from '../../../stores/adminCoachDetail';

const props = defineProps({
    coach: { type: Object, required: true },
});

const emit = defineEmits(['edit', 'reset', 'suspend', 'impersonate']);
const detail = useAdminCoachDetailStore();

function avatarInitial(name) {
    return (name || '?').trim().charAt(0).toUpperCase() || '?';
}

function formatLastLogin(iso) {
    if (!iso) return 'NUNCA';
    try {
        const d = new Date(iso);
        const days = Math.floor((Date.now() - d.getTime()) / 86400000);
        if (days <= 0) return 'HOY';
        if (days === 1) return 'AYER';
        if (days < 7) return `HACE ${days}D`;
        if (days < 30) return `HACE ${Math.floor(days / 7)}SEM`;
        return `HACE ${Math.floor(days / 30)}M`;
    } catch {
        return '—';
    }
}

function actionClick(action, ev) {
    ev?.stopPropagation();
    emit(action, props.coach);
}
</script>

<template>
  <article
    class="coach-card"
    role="button"
    tabindex="0"
    @click="detail.openCoach(coach)"
    @keydown.enter.prevent="detail.openCoach(coach)"
    @keydown.space.prevent="detail.openCoach(coach)"
  >
    <header class="card-head">
      <span class="avatar" aria-hidden="true">{{ avatarInitial(coach.name) }}</span>
      <div class="head-info">
        <h3 class="card-name">{{ coach.name || 'Sin nombre' }}</h3>
        <span class="card-handle">@{{ coach.username || '—' }}</span>
      </div>
      <span class="status-pill" :class="coach.active ? 'pill--success' : 'pill--neutral'">
        {{ coach.active ? 'ACTIVO' : 'INACTIVO' }}
      </span>
    </header>

    <div class="card-stats">
      <div class="stat">
        <span class="stat-label">CLIENTES</span>
        <span class="stat-value">{{ coach.client_count ?? 0 }}</span>
      </div>
      <div class="stat">
        <span class="stat-label">ULTIMO LOGIN</span>
        <span class="stat-value-mono">{{ formatLastLogin(coach.last_login_at) }}</span>
      </div>
    </div>

    <div v-if="coach.email || coach.whatsapp" class="card-contact">
      <span v-if="coach.email" class="contact-line">{{ coach.email }}</span>
      <span v-if="coach.whatsapp" class="contact-line contact-line--mono">{{ coach.whatsapp }}</span>
    </div>

    <div class="card-actions" @click.stop>
      <button class="action-btn" type="button" @click="actionClick('edit', $event)">EDITAR</button>
      <button class="action-btn action-btn--amber" type="button" @click="actionClick('reset', $event)">RESET</button>
      <button class="action-btn action-btn--accent" type="button" @click="actionClick('impersonate', $event)">IMPERSONAR</button>
      <button v-if="coach.active" class="action-btn action-btn--danger" type="button" @click="actionClick('suspend', $event)">DESACTIVAR</button>
    </div>
  </article>
</template>

<style scoped>
.coach-card {
    display: block;
    width: 100%;
    text-align: left;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 14px;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.coach-card:hover {
    background: rgba(17, 17, 17, 0.9);
    border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.12));
}
.coach-card:focus-visible {
    outline: 1px solid var(--color-wc-accent, #DC2626);
    outline-offset: -1px;
}

.card-head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}
.avatar {
    width: 38px;
    height: 38px;
    flex-shrink: 0;
    border-radius: 50%;
    background: rgba(220, 38, 38, 0.12);
    border: 1px solid rgba(220, 38, 38, 0.25);
    color: var(--color-wc-red-text, #F87171);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-size: 16px;
    letter-spacing: 0.04em;
}
.head-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 1px;
}
.card-name {
    font-family: var(--font-sans);
    font-size: 14px;
    font-weight: 700;
    color: var(--color-wc-text);
    margin: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.card-handle {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}

.status-pill,
.pill--success,
.pill--neutral {
    display: inline-block;
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: 4px;
    line-height: 1.4;
}
.pill--success { background: var(--color-wc-green-soft, rgba(16, 185, 129, 0.1)); color: var(--color-wc-green-text, #34D399); }
.pill--neutral { background: rgba(255, 255, 255, 0.04); color: var(--color-wc-text-tertiary); }

.card-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 12px;
}
.stat {
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    padding: 8px 10px;
    background: rgba(255, 255, 255, 0.02);
}
.stat-label {
    display: block;
    font-family: var(--font-mono, monospace);
    font-size: 7px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    margin-bottom: 4px;
}
.stat-value {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-feature-settings: 'tnum' 1;
    font-size: 18px;
    font-weight: 700;
    color: var(--color-wc-text);
}
.stat-value-mono {
    font-family: var(--font-mono, monospace);
    font-size: 11px;
    letter-spacing: 0.12em;
    color: var(--color-wc-text-secondary);
}

.card-contact {
    display: flex;
    flex-direction: column;
    gap: 2px;
    margin-bottom: 12px;
    padding: 8px 10px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--color-wc-border);
}
.contact-line {
    font-size: 11px;
    color: var(--color-wc-text-secondary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.contact-line--mono {
    font-family: var(--font-mono, monospace);
    font-size: 10px;
    letter-spacing: 0.12em;
    color: var(--color-wc-text-tertiary);
}

.card-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.action-btn {
    flex: 1 1 calc(50% - 3px);
    min-width: 80px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: rgba(255, 255, 255, 0.02);
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.action-btn:hover {
    background: rgba(255, 255, 255, 0.05);
    color: var(--color-wc-text);
    border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.12));
}
.action-btn--amber:hover  { color: var(--color-wc-amber-text, #FCD34D); border-color: rgba(245, 158, 11, 0.35); }
.action-btn--accent:hover { color: var(--color-wc-red-text, #F87171); border-color: rgba(220, 38, 38, 0.4); }
.action-btn--danger:hover { color: var(--color-wc-red-text, #F87171); border-color: rgba(220, 38, 38, 0.4); }
</style>
