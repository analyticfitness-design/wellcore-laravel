<script setup>
import { computed, onMounted } from 'vue';
import { useGroupPresence } from '../../composables/useGroupPresence';

const props = defineProps({
    userId: { type: [Number, null], required: true },
    userType: { type: String, required: true },
});

const presence = useGroupPresence();
onMounted(() => presence.init());

const isOnline = computed(() => presence.isOnline(props.userType, props.userId));
</script>

<template>
  <span :title="isOnline ? 'Activo ahora' : 'Inactivo'" class="relative inline-flex items-center">
    <span :class="isOnline ? 'bg-emerald-500' : 'bg-wc-text-tertiary/40'" class="h-2 w-2 rounded-full"></span>
    <span v-if="isOnline" class="absolute inline-flex h-2 w-2 rounded-full bg-emerald-500 opacity-75 animate-ping"></span>
  </span>
</template>
