<script setup>
import AdminLayout from '../../layouts/AdminLayout.vue';
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">Programa de Referidos</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Gestion de recompensas por referidos</p>
      </div>

      <!-- Coming Soon -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-wc-bg-secondary">
          <svg class="h-8 w-8 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
          </svg>
        </div>
        <h2 class="mt-4 font-display text-xl tracking-wide text-wc-text">Proximamente</h2>
        <p class="mt-2 text-sm text-wc-text-tertiary">El modulo de gestion de referidos esta en desarrollo.</p>
        <p class="mt-1 text-xs text-wc-text-tertiary/60">Aqui podras configurar recompensas, ver referidos activos y gestionar el programa de referidos.</p>
      </div>

    </div>
  </AdminLayout>
</template>
