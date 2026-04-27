<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    hashtags: { type: Object, required: true },
});

const activeTab = ref(0);
const copied = ref(false);

const sets = computed(() => props.hashtags?.sets ?? []);
const activeSet = computed(() => sets.value[activeTab.value] ?? null);

async function copySet() {
    if (!activeSet.value) return;
    const text = activeSet.value.tags.map(t => t.startsWith('#') ? t : `#${t}`).join(' ');
    try {
        await navigator.clipboard.writeText(text);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    } catch {}
}
</script>

<template>
    <div class="rounded-xl border border-wc-border bg-wc-bg-secondary py-8 pl-12 pr-6 transition-transform duration-200 hover:-translate-y-0.5 space-y-6">
        <!-- Set tabs -->
        <div v-if="sets.length > 1" class="flex gap-2 flex-wrap">
            <button
                v-for="(set, idx) in sets"
                :key="idx"
                type="button"
                @click="activeTab = idx"
                class="rounded-full px-3 py-1 font-mono text-xs uppercase tracking-[0.15em] transition-colors"
                :class="activeTab === idx
                    ? 'bg-wc-accent text-white'
                    : 'border border-wc-border text-wc-text-tertiary hover:text-wc-text'"
            >
                {{ set.name }}
            </button>
        </div>

        <!-- Tags -->
        <div v-if="activeSet" class="space-y-4">
            <div v-if="sets.length === 1" class="font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary">
                {{ activeSet.name }}
            </div>
            <div class="flex flex-wrap gap-2">
                <span
                    v-for="(tag, idx) in activeSet.tags"
                    :key="idx"
                    class="font-mono text-xs bg-wc-bg-tertiary border border-wc-border rounded-full px-2 py-0.5 text-wc-text-secondary"
                >
                    {{ tag.startsWith('#') ? tag : `#${tag}` }}
                </span>
            </div>

            <div class="flex justify-end">
                <button
                    type="button"
                    @click="copySet"
                    class="flex items-center gap-2 rounded-lg border border-wc-border px-4 py-2 font-mono text-xs uppercase tracking-[0.15em] text-wc-text-secondary hover:text-wc-text transition-colors"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    {{ copied ? 'Copiado' : 'Copiar set completo' }}
                </button>
            </div>
        </div>

        <p v-else class="text-sm text-wc-text-tertiary">Sin sets de hashtags disponibles.</p>
    </div>
</template>
