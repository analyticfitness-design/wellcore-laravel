<script setup>
import { RouterLink } from 'vue-router';
</script>

<template>
    <div class="flex flex-col items-center justify-center px-6 py-32 text-center">
        <span class="font-mono text-[11px] uppercase tracking-[0.3em] text-wc-text-tertiary">
            WC · ESTRATEGIA / SIN-DROP
        </span>
        <h2 class="mt-6 max-w-2xl font-display text-4xl uppercase tracking-tight text-wc-text">
            EL DROP DE ESTA SEMANA SE ESTÁ PREPARANDO
        </h2>
        <p class="mt-4 max-w-xl font-editorial text-lg italic text-wc-text-secondary">
            El equipo está construyendo tu próxima semana de contenido.
        </p>
        <RouterLink
            :to="{ name: 'coach-strategy-history' }"
            class="mt-10 inline-flex items-center gap-2 border-b border-wc-border pb-1 font-mono text-xs uppercase tracking-[0.2em] text-wc-text-secondary transition-colors hover:border-wc-accent hover:text-wc-text"
        >
            Ver drops anteriores
            <span aria-hidden="true">→</span>
        </RouterLink>
    </div>
</template>
