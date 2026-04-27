<script setup>
defineProps({
    number: { type: String, required: true },
    title: { type: String, required: true },
    sub: { type: String, default: '' },
});
</script>

<template>
    <div class="my-12 flex items-center gap-4">
        <span class="font-mono text-xs uppercase tracking-[0.2em] text-wc-text-tertiary">{{ number }}</span>
        <span class="font-display text-2xl text-wc-accent">/</span>
        <h2 class="font-display text-3xl uppercase tracking-tight text-wc-text">{{ title }}</h2>
        <span v-if="sub" class="font-editorial text-lg italic text-wc-text-secondary">{{ sub }}</span>
        <span class="h-px flex-1 bg-wc-border"></span>
    </div>
</template>
