<script setup>
/**
 * TipsList — 4 numbered editorial rules (01-04) for taking precise photos.
 * Replaces the legacy "💡 Iluminación / 👕 Vestimenta..." emoji rows with
 * a Bebas/Oswald + monospaced number hierarchy.
 *
 * Tips are constants (no props) — voice & content fixed by the design system
 * for consistency across the app. If we ever localize, these become i18n keys.
 */

const TIPS = [
  {
    n: '01',
    title: 'Iluminación',
    body: 'Luz natural de frente o lateral. Evita contraluz y lámparas por encima.',
  },
  {
    n: '02',
    title: 'Encuadre',
    body: '1.5–2 metros de la cámara. Cuerpo entero en el cuadro, sin recortes.',
  },
  {
    n: '03',
    title: 'Postura',
    body: 'Brazos al lado, pies juntos, mirando al frente. Relaja los hombros.',
  },
  {
    n: '04',
    title: 'Consistencia',
    body: 'Misma hora, mismo lugar, mismo fondo. Así la comparación es real.',
  },
];
</script>

<template>
  <ul class="flex flex-col gap-3" aria-label="Reglas para tomar fotos precisas">
    <li
      v-for="tip in TIPS"
      :key="tip.n"
      class="grid grid-cols-[auto_1fr] items-start gap-3 rounded-xl border border-wc-border bg-wc-bg-tertiary p-4"
    >
      <span class="font-mono text-[11px] uppercase tracking-widest text-wc-text-tertiary">
        {{ tip.n }}
      </span>
      <div class="min-w-0">
        <h4 class="font-display text-sm font-semibold uppercase tracking-wider text-wc-text">
          {{ tip.title }}
        </h4>
        <p class="mt-1 text-xs leading-relaxed text-wc-text-secondary">
          {{ tip.body }}
        </p>
      </div>
    </li>
  </ul>
</template>
