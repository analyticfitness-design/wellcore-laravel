<script setup>
/**
 * IdentityHero.vue — bloque hero con avatar+ring+nombre+email+chips faltantes.
 *
 * Estructura:
 *   ┌──────────────┬───────────────────────────────────────┐
 *   │              │  Nombre (Oswald 24px)                  │
 *   │   <slot>     │  email (handle)                        │
 *   │  (ring +     │  ─────────────────────────────────     │
 *   │   avatar)    │  Completitud           XX% (tier color)│
 *   │              │  Mensaje contextual                    │
 *   │              │  [chip falta+15pts] [chip ...]         │
 *   └──────────────┴───────────────────────────────────────┘
 *
 * Slot por defecto: el padre coloca `<CompletionRing><AvatarUploader/></CompletionRing>`
 * para mantener la composición ring → disco → cámara.
 *
 * Click en chip → emit('chip-click', missingItem) — el padre puede usar esto
 * para hacer scroll al campo y disparar flash effect.
 */
import { computed } from 'vue';

const props = defineProps({
    name:           { type: String, default: '' },
    email:          { type: String, default: '' },
    plan:           { type: String, default: '' }, // p.ej. "S1 Activo" — se concatena al email
    completionScore:{ type: Number, default: 0 },
    completionTier: { type: String, default: 'low' }, // 'low' | 'mid' | 'high'
    missing:        { type: Array, default: () => [] }, // [{key, label, points}]
    completionMessage: { type: String, default: '' },
});
const emit = defineEmits(['chip-click']);

const tierColor = computed(() => {
    switch (props.completionTier) {
        case 'high': return '#10B981';
        case 'mid':  return '#3B82F6';
        default:     return '#F59E0B';
    }
});

const safeMessage = computed(() => {
    if (props.completionMessage) return props.completionMessage;
    const n = (props.missing ?? []).length;
    if (n === 0) return 'Tu perfil está completo y visible en la comunidad.';
    return `Faltan ${n} dato${n === 1 ? '' : 's'} para completar tu perfil.`;
});

const handleText = computed(() => {
    const parts = [];
    if (props.email) parts.push(props.email);
    if (props.plan) parts.push(props.plan);
    return parts.join(' · ');
});

function onChipClick(item) {
    emit('chip-click', item);
}
</script>

<template>
  <section class="identity" aria-labelledby="identity-name">
    <div class="identity__avatar">
      <slot />
    </div>

    <div class="identity__meta">
      <h2 id="identity-name" class="id-name font-display">
        {{ name || 'Sin nombre' }}
      </h2>
      <p v-if="handleText" class="id-handle">{{ handleText }}</p>

      <div class="id-progress-row">
        <span class="id-progress-label">Completitud del perfil</span>
        <span
          class="id-progress-value font-display tabular-nums"
          :style="{ color: tierColor }"
          aria-live="polite"
        >{{ completionScore }}%</span>
      </div>

      <p class="id-help">{{ safeMessage }}</p>

      <ul v-if="missing && missing.length" class="id-chips" aria-label="Campos pendientes">
        <li
          v-for="item in missing"
          :key="item.key || item.label"
          class="id-chips__item"
        >
          <button
            type="button"
            class="id-chip"
            @click="onChipClick(item)"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <line x1="12" y1="5" x2="12" y2="19" />
              <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            <span class="id-chip__label">{{ item.label }}</span>
            <span v-if="item.points" class="id-chip__pts tabular-nums">+{{ item.points }}</span>
          </button>
        </li>
      </ul>
    </div>
  </section>
</template>

<style scoped>
.identity {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 28px;
  align-items: center;
  padding: 28px;
  border-radius: 20px;
  border: 1px solid var(--color-wc-border);
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.02) 0%, transparent 100%),
              var(--color-wc-bg-secondary);
}
@media (max-width: 720px) {
  .identity {
    grid-template-columns: 1fr;
    gap: 20px;
    padding: 20px;
    text-align: left;
  }
}

.identity__avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
@media (max-width: 720px) {
  .identity__avatar { justify-content: flex-start; }
}

.identity__meta { min-width: 0; }

.id-name {
  font-size: 24px;
  font-weight: 600;
  letter-spacing: 0.02em;
  color: var(--color-wc-text);
  line-height: 1.15;
  margin: 0;
  word-break: break-word;
}

.id-handle {
  margin: 2px 0 0;
  font-size: 14px;
  color: var(--color-wc-text-tertiary);
  word-break: break-all;
}

.id-progress-row {
  margin-top: 14px;
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 12px;
}
.id-progress-label {
  font-size: 13px;
  color: var(--color-wc-text-secondary);
  font-weight: 500;
}
.id-progress-value {
  font-size: 16px;
  font-weight: 600;
  letter-spacing: 0.04em;
  transition: color 0.4s ease;
}

.id-help {
  margin: 8px 0 0;
  font-size: 13px;
  line-height: 1.5;
  color: var(--color-wc-text-tertiary);
}

.id-chips {
  margin: 14px 0 0;
  padding: 0;
  list-style: none;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.id-chips__item { list-style: none; padding: 0; margin: 0; display: contents; }

.id-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px 6px 8px;
  border-radius: 999px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-tertiary);
  font-size: 12px;
  font-weight: 500;
  color: var(--color-wc-text-secondary);
  cursor: pointer;
  transition: border-color 0.15s ease, color 0.15s ease, background 0.15s ease, transform 0.15s ease;
  user-select: none;
  min-height: 32px;
}
.id-chip:hover {
  border-color: var(--color-wc-border-strong, var(--color-wc-border));
  color: var(--color-wc-text);
  background: var(--color-wc-bg-prominent, var(--color-wc-bg-tertiary));
}
.id-chip:active { transform: translateY(1px); }
.id-chip:focus-visible {
  outline: 2px solid var(--color-wc-accent-glow, #EF4444);
  outline-offset: 2px;
}
.id-chip svg {
  width: 12px;
  height: 12px;
  opacity: 0.7;
  flex-shrink: 0;
}

.id-chip__label { white-space: nowrap; }

.id-chip__pts {
  font-size: 11px;
  font-weight: 600;
  color: var(--color-wc-text-tertiary);
}

@media (prefers-reduced-motion: reduce) {
  .id-chip,
  .id-progress-value { transition-duration: 0.01ms; }
}
</style>
