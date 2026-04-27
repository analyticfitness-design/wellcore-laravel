<script setup lang="ts">
defineProps<{ id: string }>();
</script>
<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold">Drop Review #{{ id }}</h1>
    <p class="text-gray-500 mt-2">Revision de drop.</p>
  </div>
</template>
