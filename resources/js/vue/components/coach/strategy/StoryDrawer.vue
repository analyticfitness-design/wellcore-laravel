<script setup>
import { ref, computed } from 'vue';
import PieceMarkPublishedButton from './PieceMarkPublishedButton.vue';
import { coachStrategyApi } from '../../../api/coachStrategy';

const props = defineProps({
    story: { type: Object, required: true },
    dropId: { type: Number, required: true },
    pieceState: { type: Object, default: null },
    /** Top-level drop assets — we filter the ones linked to this story's day. */
    dropAssets: { type: Array, default: () => [] },
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

/**
 * Assets attached to this story's day (story or slide-level). Sorted by
 * `order` so launch sequences keep their authored order.
 */
const linkedAssets = computed(() => {
    const day = props.story.day;
    const list = (props.dropAssets ?? []).filter((a) => {
        const lt = a.linked_to;
        if (!lt) return false;
        if ((lt.type === 'story' || lt.type === 'slide') && lt.day === day) return true;
        return false;
    });
    list.sort((a, b) => (a.order ?? 999) - (b.order ?? 999));
    return list;
});

const hasAssets = computed(() => linkedAssets.value.length > 0);

async function copyText() {
    try {
        await navigator.clipboard.writeText(allSlidesText.value);
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    } catch (e) {
        copied.value = false;
    }
}

async function downloadAsset(asset) {
    downloading.value = true;
    try {
        await coachStrategyApi.downloadSingle(asset);
    } finally {
        setTimeout(() => (downloading.value = false), 600);
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

                <!-- Linked assets gallery (visible imágenes que el coach descarga) -->
                <div v-if="hasAssets" class="space-y-3">
                    <span class="block font-mono text-[10px] uppercase tracking-[0.2em] text-wc-accent">
                        Imágenes para publicar ({{ linkedAssets.length }})
                    </span>
                    <div class="grid grid-cols-2 gap-2">
                        <div v-for="asset in linkedAssets" :key="asset.id" class="group relative overflow-hidden rounded-lg border border-wc-border bg-wc-bg">
                            <div class="relative aspect-[9/16] bg-black">
                                <img v-if="asset.kind === 'image'" :src="asset.url" :alt="asset.filename" loading="lazy" class="absolute inset-0 h-full w-full object-cover" />
                                <div v-else class="absolute inset-0 flex items-center justify-center font-mono text-[10px] text-wc-text-tertiary">VIDEO</div>
                            </div>
                            <button type="button" @click="downloadAsset(asset)"
                                class="absolute inset-x-0 bottom-0 flex items-center justify-center gap-1.5 bg-black/80 py-2 font-mono text-[10px] uppercase tracking-[0.15em] text-white opacity-0 transition-opacity backdrop-blur group-hover:opacity-100">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                                </svg>
                                Descargar
                            </button>
                        </div>
                    </div>
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
                    v-if="hasAssets && linkedAssets.length === 1"
                    type="button"
                    @click="downloadAsset(linkedAssets[0])"
                    :disabled="downloading"
                    class="flex items-center gap-2 rounded-lg border border-wc-accent bg-wc-accent/10 px-4 py-2 font-mono text-xs uppercase tracking-[0.15em] text-wc-accent transition-colors hover:bg-wc-accent hover:text-white disabled:opacity-50"
                >
                    {{ downloading ? 'Descargando...' : 'Descargar imagen' }}
                </button>
                <div class="ml-auto">
                    <PieceMarkPublishedButton :drop-id="dropId" :piece-key="pieceKey" :state="stateValue" />
                </div>
            </footer>
        </aside>
    </div>
</template>
