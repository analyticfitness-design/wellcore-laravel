<script setup>
import { ref, computed } from 'vue';
import PieceMarkPublishedButton from './PieceMarkPublishedButton.vue';

const props = defineProps({
    story: { type: Object, required: true },
    dropId: { type: Number, required: true },
    pieceState: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const copied = ref(false);
const downloading = ref(false);
const activeIdx = ref(0);

const pieceKey = computed(() => `story_${(props.story.day ?? '').toLowerCase()}`);
const stateValue = computed(() => props.pieceState?.state ?? 'pending');

const allSlidesText = computed(() =>
    (props.story.slides ?? [])
        .map((s) => s.text ?? '')
        .filter(Boolean)
        .join('\n\n'),
);

const currentSlideText = computed(() => props.story.slides?.[activeIdx.value]?.text ?? '');

async function copyText() {
    try {
        await navigator.clipboard.writeText(allSlidesText.value);
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    } catch (e) {
        copied.value = false;
    }
}

async function downloadPng() {
    downloading.value = true;
    try {
        const { toPng } = await import('html-to-image');
        const node = document.getElementById(`story-export-${props.story.day}`);
        if (!node) return;
        const dataUrl = await toPng(node, { width: 1080, height: 1920, pixelRatio: 1 });
        const link = document.createElement('a');
        link.download = `wellcore-story-${(props.story.day ?? '').toLowerCase()}.png`;
        link.href = dataUrl;
        link.click();
    } catch (e) {
        console.error('Error generating PNG', e);
    } finally {
        downloading.value = false;
    }
}
</script>

<template>
    <div class="fixed inset-0 z-50 bg-black/70" @click.self="emit('close')">
        <aside class="absolute right-0 top-0 flex h-full w-full max-w-[480px] flex-col overflow-hidden border-l border-wc-border bg-wc-bg-secondary shadow-2xl">
            <header class="flex items-center justify-between border-b border-wc-border px-6 py-4">
                <div>
                    <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary">Story</span>
                    <h3 class="font-display text-2xl uppercase tracking-tight text-wc-text">{{ story.day }}</h3>
                    <span v-if="story.pillar" class="font-mono text-[10px] uppercase tracking-[0.2em] text-wc-accent">{{ story.pillar }}</span>
                </div>
                <button
                    type="button"
                    @click="emit('close')"
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary transition-colors hover:text-wc-text"
                    aria-label="Cerrar"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </header>

            <div class="flex-1 space-y-5 overflow-y-auto px-6 py-6">
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="(_, idx) in (story.slides ?? [])"
                        :key="idx"
                        type="button"
                        @click="activeIdx = idx"
                        class="rounded-full border px-3 py-1 font-mono text-[10px] uppercase tracking-[0.2em] transition-colors"
                        :class="activeIdx === idx
                            ? 'border-wc-accent bg-wc-accent/15 text-wc-accent'
                            : 'border-wc-border text-wc-text-secondary hover:text-wc-text'"
                    >
                        Slide {{ idx + 1 }}
                    </button>
                </div>

                <article
                    v-for="(slide, idx) in (story.slides ?? [])"
                    :key="idx"
                    v-show="activeIdx === idx"
                    class="space-y-4 rounded-lg border border-wc-border bg-wc-bg p-5"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded border border-wc-border px-2 py-0.5 font-mono text-[10px] uppercase tracking-[0.2em] text-wc-text-secondary">
                            {{ slide.kind }}
                        </span>
                        <span
                            v-if="slide.sticker && slide.sticker !== 'none'"
                            class="rounded border border-wc-accent/40 px-2 py-0.5 font-mono text-[10px] uppercase tracking-[0.2em] text-wc-accent"
                        >
                            sticker · {{ slide.sticker }}
                        </span>
                    </div>
                    <p class="font-display text-2xl uppercase leading-tight tracking-tight text-wc-text">
                        {{ slide.text }}
                    </p>
                    <p
                        v-if="slide.visual_hint"
                        class="font-editorial text-base italic leading-relaxed text-wc-text-secondary"
                    >
                        Visual: {{ slide.visual_hint }}
                    </p>
                </article>

                <div v-if="story.dm_followup_hint" class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 p-4">
                    <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.2em] text-wc-accent">
                        DM follow-up
                    </span>
                    <p class="font-editorial text-sm italic leading-relaxed text-wc-text-secondary">
                        {{ story.dm_followup_hint }}
                    </p>
                </div>
            </div>

            <footer class="flex flex-wrap items-center gap-3 border-t border-wc-border px-6 py-4">
                <button
                    type="button"
                    @click="copyText"
                    class="flex items-center gap-2 rounded-lg border border-wc-border px-4 py-2 font-mono text-xs uppercase tracking-[0.15em] text-wc-text-secondary transition-colors hover:text-wc-text"
                >
                    {{ copied ? 'Copiado' : 'Copiar texto' }}
                </button>
                <button
                    type="button"
                    @click="downloadPng"
                    :disabled="downloading"
                    class="flex items-center gap-2 rounded-lg border border-wc-border px-4 py-2 font-mono text-xs uppercase tracking-[0.15em] text-wc-text-secondary transition-colors hover:text-wc-text disabled:opacity-50"
                >
                    {{ downloading ? 'Generando...' : 'Descargar PNG' }}
                </button>
                <div class="ml-auto">
                    <PieceMarkPublishedButton :drop-id="dropId" :piece-key="pieceKey" :state="stateValue" />
                </div>
            </footer>
        </aside>

        <div
            :id="`story-export-${story.day}`"
            style="position:fixed;left:-9999px;top:0;width:1080px;height:1920px;background:#09090B;color:#FAFAFA;font-family:'Oswald';padding:120px;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;"
        >
            <p style="font-size:32px;font-family:'JetBrains Mono';text-transform:uppercase;letter-spacing:0.2em;margin-bottom:60px;color:#DC2626;">{{ story.day }}</p>
            <h1 style="font-size:84px;line-height:1.05;font-weight:700;text-transform:uppercase;letter-spacing:0.02em;">{{ currentSlideText }}</h1>
        </div>
    </div>
</template>
