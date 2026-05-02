<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';

const props = defineProps({
  // Array de alerts del API: [{ type: 'error'|'warning'|'info', title, body, link?, ctaLabel? }, ...]
  alerts: { type: Array, default: () => [] },
});

// Pull-quote brutal: extrae las primeras 2 palabras del title como big text
function pullQuote(title) {
  if (!title) return '';
  const words = title.trim().split(/\s+/);
  return words.slice(0, 2).join(' ').toUpperCase();
}

// Variante visual segun el type
function alertClasses(type) {
  switch (type) {
    case 'warning': return 'alert-card alert-card--warning';
    case 'info': return 'alert-card alert-card--info';
    case 'error':
    default: return 'alert-card alert-card--error';
  }
}

// Texto corto para etiqueta superior (URGENT / WARNING / INFO)
function alertTag(type) {
  switch (type) {
    case 'warning': return 'WARNING';
    case 'info': return 'INFO';
    case 'error':
    default: return 'URGENT';
  }
}
</script>

<template>
  <div v-if="alerts.length" class="alerts-row">
    <div
      v-for="(alert, idx) in alerts"
      :key="`${alert.type}-${idx}`"
      :class="alertClasses(alert.type)"
    >
      <!-- Mobile: pull-quote brutal arriba, body, CTA -->
      <div class="alert-card-mobile lg:hidden">
        <div class="alert-pullquote">{{ pullQuote(alert.title) }}</div>
        <span class="alert-tag">{{ alertTag(alert.type) }}</span>
        <p class="alert-body">{{ alert.body }}</p>
        <RouterLink
          v-if="alert.link"
          :to="alert.link"
          class="alert-cta"
        >
          {{ alert.ctaLabel || 'Ver detalle' }} <span aria-hidden="true">→</span>
        </RouterLink>
      </div>

      <!-- Desktop: icono + pull-quote + body inline + CTA a la derecha -->
      <div class="alert-card-desktop hidden lg:flex">
        <div class="alert-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
          </svg>
        </div>
        <div class="alert-pullquote alert-pullquote--desktop">{{ pullQuote(alert.title) }}</div>
        <div class="alert-body-wrap">
          <span class="alert-tag">{{ alertTag(alert.type) }}</span>
          <p class="alert-body">{{ alert.body }}</p>
        </div>
        <RouterLink
          v-if="alert.link"
          :to="alert.link"
          class="alert-cta alert-cta--desktop"
        >
          {{ alert.ctaLabel || 'Ver detalle' }} <span aria-hidden="true">→</span>
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ============================================================================
   AdminAlertsRow — alert pull-quote brutal (rojo) con CTA editorial.
   3 variantes: error (rojo), warning (amber), info (azul).
   Mobile: stack vertical. Desktop: row con icono + pull-quote + body + CTA.
   ============================================================================ */

.alerts-row {
    display: flex; flex-direction: column; gap: 12px;
}

/* Variantes — borde-left + border full + bg gradient */
.alert-card {
    border-radius: 0 12px 12px 0;
    overflow: hidden;
}
.alert-card--error {
    border-left: 4px solid var(--c-accent, #DC2626);
    background: linear-gradient(to right, rgba(220, 38, 38, 0.09), rgba(220, 38, 38, 0.03));
    border-top: 1px solid rgba(220, 38, 38, 0.15);
    border-right: 1px solid rgba(220, 38, 38, 0.1);
    border-bottom: 1px solid rgba(220, 38, 38, 0.1);
}
.alert-card--warning {
    border-left: 4px solid #F59E0B;
    background: linear-gradient(to right, rgba(245, 158, 11, 0.09), rgba(245, 158, 11, 0.03));
    border-top: 1px solid rgba(245, 158, 11, 0.15);
    border-right: 1px solid rgba(245, 158, 11, 0.1);
    border-bottom: 1px solid rgba(245, 158, 11, 0.1);
}
.alert-card--info {
    border-left: 4px solid #3B82F6;
    background: linear-gradient(to right, rgba(59, 130, 246, 0.09), rgba(59, 130, 246, 0.03));
    border-top: 1px solid rgba(59, 130, 246, 0.15);
    border-right: 1px solid rgba(59, 130, 246, 0.1);
    border-bottom: 1px solid rgba(59, 130, 246, 0.1);
}

/* ── Mobile ──────────────────────────────────────────────────────────────── */
.alert-card-mobile { padding: 14px 14px 14px 16px; }
.alert-pullquote {
    font-family: var(--font-display);
    font-size: 26px; letter-spacing: 0.04em;
    line-height: 1; margin-bottom: 8px;
    text-transform: uppercase;
}
.alert-card--error .alert-pullquote { color: #F87171; }
.alert-card--warning .alert-pullquote { color: #FCD34D; }
.alert-card--info .alert-pullquote { color: #60A5FA; }

.alert-tag {
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600; letter-spacing: 1.4px; text-transform: uppercase;
    margin-bottom: 5px; display: block;
}
.alert-card--error .alert-tag { color: #F87171; }
.alert-card--warning .alert-tag { color: #FCD34D; }
.alert-card--info .alert-tag { color: #60A5FA; }

.alert-body {
    font-family: var(--font-sans);
    font-size: 14px; line-height: var(--lh-body, 1.65);
    color: var(--c-text-2);
    margin: 0 0 10px;
}
.alert-body :deep(strong) { color: var(--c-text); font-weight: 600; }

.alert-cta {
    display: inline-flex; align-items: center; gap: 5px;
    font-family: var(--font-display);
    font-size: 11px; font-weight: 600; letter-spacing: 1.6px; text-transform: uppercase;
    padding-bottom: 2px; min-height: 44px;
    border-bottom: 1px solid rgba(248,113,113,0.3);
    transition: opacity 0.15s var(--ease-out, ease);
    text-decoration: none;
}
.alert-card--error .alert-cta { color: #F87171; }
.alert-card--warning .alert-cta { color: #FCD34D; border-bottom-color: rgba(252,211,77,0.3); }
.alert-card--info .alert-cta { color: #60A5FA; border-bottom-color: rgba(96,165,250,0.3); }
.alert-cta:active, .alert-cta:hover { opacity: 0.7; }

/* ── Desktop ──────────────────────────────────────────────────────────────── */
.alert-card-desktop {
    padding: 14px 20px;
    align-items: center; gap: 20px;
}
.alert-icon-wrap {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.alert-card--error .alert-icon-wrap { background: rgba(220, 38, 38, 0.12); color: #F87171; }
.alert-card--warning .alert-icon-wrap { background: rgba(245, 158, 11, 0.12); color: #FCD34D; }
.alert-card--info .alert-icon-wrap { background: rgba(59, 130, 246, 0.12); color: #60A5FA; }

.alert-pullquote--desktop {
    margin-bottom: 0;
    font-size: 24px;
    flex-shrink: 0;
}
.alert-body-wrap { flex: 1; min-width: 0; }
.alert-body-wrap .alert-body { margin: 2px 0 0; font-size: 13px; }

.alert-cta--desktop {
    background: rgba(220, 38, 38, 0.1);
    border: 1px solid rgba(220, 38, 38, 0.3);
    border-bottom: 1px solid rgba(220, 38, 38, 0.3);
    border-radius: var(--r-sm, 12px);
    padding: 7px 14px;
    flex-shrink: 0; white-space: nowrap;
    transition: background 0.15s var(--ease-out, ease);
}
.alert-card--warning .alert-cta--desktop {
    background: rgba(245, 158, 11, 0.1);
    border-color: rgba(245, 158, 11, 0.3);
}
.alert-card--info .alert-cta--desktop {
    background: rgba(59, 130, 246, 0.1);
    border-color: rgba(59, 130, 246, 0.3);
}
.alert-cta--desktop:hover { background: rgba(220, 38, 38, 0.18); opacity: 1; }
</style>
