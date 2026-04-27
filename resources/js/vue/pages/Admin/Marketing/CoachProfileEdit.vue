<script setup>
defineProps({ coachId: { type: [String, Number], required: true } });
import AdminLayout from '../../../layouts/AdminLayout.vue';
</script>
<template>
  <AdminLayout>
    <div class="mx-auto max-w-7xl px-6 py-12">
      <h1 class="font-display text-4xl uppercase">Editar Perfil Coach</h1>
      <p class="mt-3 text-wc-text-secondary">Implementacion pendiente (M10).</p>
    </div>
  </AdminLayout>
</template>
