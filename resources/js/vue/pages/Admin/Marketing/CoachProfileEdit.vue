<script setup lang="ts">
defineProps<{ coachId: string }>();
</script>
<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold">Editar Perfil Coach #{{ coachId }}</h1>
    <p class="text-gray-500 mt-2">Edicion de perfil de marca del coach.</p>
  </div>
</template>
