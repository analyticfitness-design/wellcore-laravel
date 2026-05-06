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
    body: 'Luz natural de frente o lateral. Evita contraluz y lámparas directamente arriba — generan sombras que mienten.',
  },
  {
    n: '02',
    title: 'Vestimenta',
    body: 'Ropa ajustada — short y top, o ropa interior. Lo holgado oculta los cambios reales del cuerpo.',
  },
  {
    n: '03',
    title: 'Distancia',
    body: '1.5 a 2 metros de la cámara. Cuerpo entero en el encuadre, con espacio sobre la cabeza y bajo los pies.',
  },
  {
    n: '04',
    title: 'Consistencia',
    body: 'Misma hora, mismo lugar, mismo fondo cada quincena. Así la única variable que cambia es tu cuerpo.',
  },
];
</script>

<template>
  <div class="grid gap-6 border-t border-wc-border pt-8 sm:grid-cols-[auto_1fr] sm:gap-12">
    <div class="max-w-[14ch]">
      <small class="mb-2 block font-mono text-[10px] font-normal uppercase tracking-[0.16em] text-wc-text-tertiary">
        Cuatro reglas
      </small>
      <h3 class="font-display text-xl font-medium uppercase tracking-wider text-wc-text">
        Para que la comparación sea real
      </h3>
    </div>
    <ul class="grid gap-6 sm:grid-cols-2 sm:gap-x-10 sm:gap-y-6" aria-label="Reglas para tomar fotos precisas">
      <li
        v-for="tip in TIPS"
        :key="tip.n"
        class="grid grid-cols-[auto_1fr] items-start gap-3.5"
      >
        <span class="pt-0.5 font-mono text-[11px] uppercase tracking-widest text-wc-text-tertiary">
          {{ tip.n }}
        </span>
        <div class="min-w-0">
          <h4 class="mb-1 font-display text-sm font-semibold uppercase tracking-wider text-wc-text">
            {{ tip.title }}
          </h4>
          <p class="text-sm leading-relaxed text-wc-text-secondary">
            {{ tip.body }}
          </p>
        </div>
      </li>
    </ul>
  </div>
</template>
