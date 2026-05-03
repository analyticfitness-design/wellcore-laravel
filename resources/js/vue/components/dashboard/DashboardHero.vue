<script setup>
defineProps({
    data: { type: Object, required: true },
    greeting: { type: String, required: true },
    motivationalQuote: { type: String, default: '' },
});
</script>

<template>
  <section class="hero grain section" :style="{ animationDelay: '40ms' }">
    <div class="hero-greeting tight">
      <span class="name">{{ greeting }},</span> {{ data.clientName }}
    </div>
    <div v-if="data.planLabel" class="hero-row">
      <span class="chip chip-accent">Plan {{ data.planLabel }}</span>
    </div>
    <p v-if="motivationalQuote" class="hero-sub">{{ motivationalQuote }}</p>
  </section>
</template>
