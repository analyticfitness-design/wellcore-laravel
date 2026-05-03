<script setup>
/**
 * WcEmptyState — Estado vacio con SVG line-art + titulo + subtitulo + CTA opcional.
 *
 * Default icon usa currentColor + --color-wc-text-tertiary, asi se adapta a
 * light y dark sin overrides. El consumer puede sobreescribir con slot #icon.
 *
 * Props:
 *   title    string  — heading del estado
 *   subtitle string  — descripcion (opcional)
 *   ctaLabel string  — label del boton CTA (opcional)
 *   ctaTo    string  — ruta a navegar al click (opcional)
 *
 * Slots:
 *   icon   — override del SVG default
 *   actions — override del CTA (si necesitas algo distinto a un single button)
 *
 * Eventos:
 *   @cta — emitido al clickear el CTA, ademas del navigate via router-link
 *
 * Uso:
 *   <WcEmptyState
 *     title="Sin datos de peso aun"
 *     subtitle="Registra tu peso cada semana para ver tu progreso"
 *     cta-label="Registrar peso"
 *     cta-to="/client/metrics"
 *   />
 */
import { useSlots } from 'vue';

defineProps({
    title:    { type: String, required: true },
    subtitle: { type: String, default: '' },
    ctaLabel: { type: String, default: '' },
    ctaTo:    { type: String, default: '' },
});

defineEmits(['cta']);

const slots = useSlots();
</script>

<template>
  <div class="wc-card text-center" style="padding: var(--ds-s-8);">
    <div class="flex justify-center mb-4" style="color: var(--color-wc-text-tertiary);">
      <slot name="icon">
        <!-- SVG line-art generico (bascula). Override con slot #icon segun contexto. -->
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <rect x="6" y="14" width="36" height="28" rx="4" />
          <circle cx="24" cy="28" r="6" />
          <path d="M24 24v4M22 28h4" />
          <path d="M14 14V8h20v6" />
        </svg>
      </slot>
    </div>
    <h3 class="font-display text-lg mb-2" style="color: var(--color-wc-text);">
      {{ title }}
    </h3>
    <p v-if="subtitle" class="text-sm mb-5" style="color: var(--color-wc-text-secondary);">
      {{ subtitle }}
    </p>
    <slot name="actions">
      <router-link
        v-if="ctaLabel && ctaTo"
        :to="ctaTo"
        class="wc-btn-primary"
        @click="$emit('cta', $event)"
      >
        {{ ctaLabel }}
      </router-link>
      <button
        v-else-if="ctaLabel"
        type="button"
        class="wc-btn-primary"
        @click="$emit('cta', $event)"
      >
        {{ ctaLabel }}
      </button>
    </slot>
  </div>
</template>
