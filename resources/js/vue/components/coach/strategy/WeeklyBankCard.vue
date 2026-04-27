<script setup>
const props = defineProps({
    bank: { type: Object, required: true },
});

async function copyText(text) {
    try {
        await navigator.clipboard.writeText(text);
    } catch {}
}
</script>

<template>
    <div class="rounded-xl border border-wc-border bg-wc-bg-secondary py-8 pl-12 pr-6 transition-transform duration-200 hover:-translate-y-0.5">
        <div class="grid gap-8 md:grid-cols-3">
            <!-- Alt hooks -->
            <div>
                <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary block mb-4">
                    Hooks alternativos
                </span>
                <ul class="space-y-3">
                    <li
                        v-for="(hook, idx) in (bank.alt_hooks ?? [])"
                        :key="idx"
                        class="flex items-start justify-between gap-2 rounded-lg border border-wc-border/50 bg-wc-bg-tertiary p-3"
                    >
                        <p class="text-sm leading-relaxed text-wc-text flex-1">{{ hook }}</p>
                        <button
                            type="button"
                            @click="copyText(hook)"
                            class="shrink-0 rounded p-1 text-wc-text-tertiary hover:text-wc-text transition-colors"
                            title="Copiar"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Alt CTAs -->
            <div>
                <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary block mb-4">
                    CTAs alternativos
                </span>
                <ul class="space-y-3">
                    <li
                        v-for="(cta, idx) in (bank.alt_ctas ?? [])"
                        :key="idx"
                        class="flex items-start justify-between gap-2 rounded-lg border border-wc-border/50 bg-wc-bg-tertiary p-3"
                    >
                        <p class="text-sm leading-relaxed text-wc-text flex-1">{{ cta }}</p>
                        <button
                            type="button"
                            @click="copyText(cta)"
                            class="shrink-0 rounded p-1 text-wc-text-tertiary hover:text-wc-text transition-colors"
                            title="Copiar"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Alt captions -->
            <div>
                <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary block mb-4">
                    Captions alternativos
                </span>
                <ul class="space-y-3">
                    <li
                        v-for="(caption, idx) in (bank.alt_captions ?? [])"
                        :key="idx"
                        class="flex items-start justify-between gap-2 rounded-lg border border-wc-border/50 bg-wc-bg-tertiary p-3"
                    >
                        <p class="text-sm leading-relaxed text-wc-text flex-1">{{ caption }}</p>
                        <button
                            type="button"
                            @click="copyText(caption)"
                            class="shrink-0 rounded p-1 text-wc-text-tertiary hover:text-wc-text transition-colors"
                            title="Copiar"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
