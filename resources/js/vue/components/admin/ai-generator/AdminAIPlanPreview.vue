<script setup>
import { computed, ref, watch } from 'vue';
import { renderMarkdown, splitMarkdownByH2 } from '../../../composables/useMarkdown';

const props = defineProps({
    text: { type: String, default: '' },
});

const sections = computed(() => splitMarkdownByH2(props.text));
const activeIndex = ref(0);

watch(sections, (s) => {
    if (activeIndex.value >= s.length) activeIndex.value = 0;
});

const activeBody = computed(() => {
    const s = sections.value[activeIndex.value];
    return s ? renderMarkdown(s.body) : '';
});
</script>

<template>
  <section v-if="sections.length > 1" class="preview-card">
    <header class="preview-head">
      <p class="preview-eyebrow">PREVIEW ESTRUCTURADO</p>
      <h2 class="preview-title">Plan por secciones</h2>
    </header>

    <nav class="preview-tabs" role="tablist">
      <button
        v-for="(s, i) in sections"
        :key="s.title + i"
        type="button"
        role="tab"
        class="preview-tab"
        :class="{ 'preview-tab--active': activeIndex === i }"
        :aria-selected="activeIndex === i"
        @click="activeIndex = i"
      >{{ s.title }}</button>
    </nav>

    <article class="preview-body stream-md" v-html="activeBody"></article>
  </section>
</template>

<style scoped>
.preview-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    position: relative;
    z-index: 1;
}
.preview-head { display: flex; flex-direction: column; gap: 3px; }
.preview-eyebrow {
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.22em;
    color: var(--color-wc-text-tertiary);
    text-transform: uppercase;
    margin: 0;
}
.preview-title {
    font-family: var(--font-display);
    font-size: 22px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--color-wc-text);
    margin: 0;
    line-height: 1.05;
}
.preview-tabs {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    border-bottom: 1px solid var(--color-wc-border);
    padding-bottom: 10px;
}
.preview-tab {
    height: 30px;
    padding: 0 14px;
    border-radius: 999px;
    border: 1px solid var(--color-wc-border);
    background: transparent;
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out), border-color 0.15s var(--ease-out), color 0.15s var(--ease-out);
}
.preview-tab:hover {
    border-color: var(--color-wc-border-2);
    color: var(--color-wc-text);
}
.preview-tab--active {
    background: var(--color-wc-red-soft);
    border-color: var(--color-wc-accent);
    color: var(--color-wc-text);
}

.preview-body {
    color: var(--color-wc-text-secondary);
    font-family: var(--font-sans);
    font-size: 13.5px;
    line-height: 1.65;
    max-height: 480px;
    overflow-y: auto;
}
/* Reuse the .stream-md styles in scope */
.preview-body :deep(h1),
.preview-body :deep(h2),
.preview-body :deep(h3),
.preview-body :deep(h4) {
    font-family: var(--font-display);
    color: var(--color-wc-text);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin: 14px 0 6px;
    line-height: 1.1;
}
.preview-body :deep(h3) { color: var(--color-wc-gold); font-size: 14px; }
.preview-body :deep(h4) { font-size: 12px; }
.preview-body :deep(p) { margin: 6px 0; }
.preview-body :deep(ul),
.preview-body :deep(ol) { margin: 6px 0 6px 20px; padding: 0; }
.preview-body :deep(li) { margin: 3px 0; }
.preview-body :deep(strong) { color: var(--color-wc-text); font-weight: 600; }
.preview-body :deep(.md-table-wrap) { overflow-x: auto; margin: 10px 0; border-radius: 8px; border: 1px solid var(--color-wc-border); }
.preview-body :deep(.md-table) { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.preview-body :deep(.md-table th) {
    background: rgba(255, 255, 255, 0.03);
    color: var(--color-wc-text);
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    text-align: left;
    padding: 7px 10px;
    border-bottom: 1px solid var(--color-wc-border);
}
.preview-body :deep(.md-table td) {
    padding: 6px 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    font-family: var(--font-data);
    font-feature-settings: 'tnum' 1;
}
.preview-body :deep(.md-table tr:last-child td) { border-bottom: none; }
</style>
