<script setup>
import AdminLayout from '../../layouts/AdminLayout.vue';
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">Enviar Invitacion de Plan</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Envio rapido de invitaciones de plan a prospectos</p>
      </div>

      <!-- Coming Soon -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-wc-bg-secondary">
          <svg class="h-8 w-8 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
          </svg>
        </div>
        <h2 class="mt-4 font-display text-xl tracking-wide text-wc-text">Proximamente</h2>
        <p class="mt-2 text-sm text-wc-text-tertiary">El formulario de envio de invitaciones de plan esta en desarrollo.</p>
        <p class="mt-1 text-xs text-wc-text-tertiary/60">Mientras tanto, utiliza el gestor de invitaciones para crear y enviar invitaciones.</p>
        <RouterLink to="/admin/invitations" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
          Ir a Invitaciones
        </RouterLink>
      </div>

    </div>
  </AdminLayout>
</template>
