<script setup>
import { ref, computed } from 'vue';
import ReelTimecodeTable from './ReelTimecodeTable.vue';
import PieceMarkPublishedButton from './PieceMarkPublishedButton.vue';

const props = defineProps({
    reel: { type: Object, required: true },
    dropId: { type: Number, required: true },
    pieceState: { type: Object, default: null },
});

const notesOpen = ref(false);
const captionCopied = ref(false);

const reelNumber = computed(() => (props.reel.key === 'reel_1' ? '1' : '2'));

const headerStrip = computed(() => {
    const meta = props.reel.format_meta ?? {};
    const platforms = (meta.platforms ?? []).join(' · ').toUpperCase();
    const dur = meta.duration_sec_min && meta.duration_sec_max
        ? `${meta.duration_sec_min}-${meta.duration_sec_max}s`
        : '';
    return `REEL_${reelNumber.value} · ${(props.reel.type ?? '').toUpperCase()} · ${dur} · ${platforms}`;
});

const stateValue = computed(() => props.pieceState?.state ?? 'pending');

async function copyCaption() {
    try {
        await navigator.clipboard.writeText(props.reel.caption ?? '');
        captionCopied.value = true;
        setTimeout(() => (captionCopied.value = false), 1500);
    } catch (e) {
        captionCopied.value = false;
    }
}
</script>

<template>
    <article
        class="mb-6 space-y-6 rounded-xl border border-wc-border bg-wc-bg-secondary py-10 pl-12 pr-6 transition-all duration-[240ms] ease-[cubic-bezier(0.16,1,0.3,1)] hover:translate-y-[-2px] hover:shadow-[0_20px_40px_-15px_rgba(220,38,38,0.25)]"
    >
        <header class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary">
                    {{ headerStrip }}
                </span>
                <h3 class="mt-3 font-display text-3xl uppercase leading-tight tracking-tight text-wc-text">
                    {{ reel.title }}
                </h3>
            </div>
            <PieceMarkPublishedButton
                :drop-id="dropId"
                :piece-key="reel.key"
                :state="stateValue"
            />
        </header>

        <section class="border-l-2 border-wc-accent pl-5">
            <span class="mb-2 block font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary">
                Hook
            </span>
            <p class="font-display text-2xl uppercase leading-tight tracking-tight text-wc-text">
                {{ reel.hook?.text }}
            </p>
            <p
                v-if="reel.hook?.rationale"
                class="mt-3 font-editorial text-base italic leading-relaxed text-wc-text-secondary"
            >
                {{ reel.hook.rationale }}
            </p>
        </section>

        <section v-if="reel.timecode_table?.length">
            <span class="mb-3 block font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary">
                Guion por escenas
            </span>
            <ReelTimecodeTable :rows="reel.timecode_table" />
        </section>

        <section v-if="reel.caption">
            <div class="flex items-center justify-between">
                <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary">
                    Caption
                </span>
                <button
                    type="button"
                    @click="copyCaption"
                    class="font-mono text-[10px] uppercase tracking-[0.2em] text-wc-text-secondary transition-colors hover:text-wc-accent"
                >
                    {{ captionCopied ? 'Copiado ✓' : 'Copiar' }}
                </button>
            </div>
            <div class="mt-2 rounded-lg bg-wc-bg-tertiary p-4">
                <p class="select-text whitespace-pre-wrap text-sm leading-relaxed text-wc-text">
                    {{ reel.caption }}
                </p>
            </div>
        </section>

        <section v-if="reel.music_note" class="flex items-center gap-2">
            <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary">
                Música
            </span>
            <span class="font-editorial text-sm italic text-wc-text">{{ reel.music_note }}</span>
            <span
                v-if="reel.format_meta?.bpm_hint"
                class="ml-2 font-mono text-[10px] uppercase tracking-[0.2em] text-wc-text-tertiary"
            >
                · {{ reel.format_meta.bpm_hint }}
            </span>
        </section>

        <section v-if="reel.production_notes">
            <button
                type="button"
                @click="notesOpen = !notesOpen"
                class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary transition-colors hover:text-wc-text"
            >
                <svg
                    class="h-3 w-3 transition-transform"
                    :class="notesOpen ? 'rotate-90' : ''"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
                Notas de producción
            </button>
            <div v-show="notesOpen" class="mt-3 pl-5">
                <p class="font-editorial text-sm italic leading-relaxed text-wc-text-secondary">
                    {{ reel.production_notes }}
                </p>
            </div>
        </section>
    </article>
</template>
