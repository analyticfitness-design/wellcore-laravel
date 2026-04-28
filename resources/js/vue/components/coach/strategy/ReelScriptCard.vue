<script setup>
import { ref, computed } from 'vue';
import ReelTimecodeTable from './ReelTimecodeTable.vue';
import PieceMarkPublishedButton from './PieceMarkPublishedButton.vue';
// import { coachStrategyApi } from '../../../api/coachStrategy';

const props = defineProps({
    reel: { type: Object, required: true },
    dropId: { type: Number, required: true },
    pieceState: { type: Object, default: null },
    dropAssets: { type: Array, default: () => [] },
    drop: { type: Object, default: null },
});

// Reel-asset linking kept for backwards compat — not rendered (assets live in AssetGallery).
// const reelAssets = computed(() => {
//     const list = (props.dropAssets ?? []).filter(
//         (a) => a.linked_to?.type === 'reel' && a.linked_to.reel_key === props.reel.key,
//     );
//     list.sort((a, b) => (a.order ?? 999) - (b.order ?? 999));
//     return list;
// });
// async function downloadAsset(asset) {
//     await coachStrategyApi.downloadSingle(asset);
// }

const captionCopied = ref(false);

const reelNumber = computed(() => (props.reel.key === 'reel_1' ? '1' : '2'));

const reelTypeLabel = computed(() => String(props.reel.type ?? '').toUpperCase());

const reelTypeBadgeLabel = computed(() => {
    const t = String(props.reel.type ?? '').toLowerCase();
    if (!t) return '';
    return t.charAt(0).toUpperCase() + t.slice(1);
});

const reelBadgeClass = computed(() => {
    const t = String(props.reel.type ?? '').toLowerCase();
    return t === 'educativo' ? 'badge-edu' : 'badge-conv';
});

const platformText = computed(() => {
    const meta = props.reel.format_meta ?? {};
    const platforms = (meta.platforms ?? []).join(' · ').toUpperCase();
    const dur = meta.duration_sec_min && meta.duration_sec_max
        ? `${meta.duration_sec_min}-${meta.duration_sec_max}s`
        : '';
    return [dur, platforms].filter(Boolean).join(' · ');
});

const stateValue = computed(() => props.pieceState?.state ?? 'pending');
const isPosted = computed(() => stateValue.value === 'published');

// Hook HTML — escape then wrap **word** / __word__ as <u>word</u>
function escapeHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

const hookHtml = computed(() => {
    const safe = escapeHtml(props.reel.hook?.text ?? '');
    return safe.replace(/\*\*(.+?)\*\*|__(.+?)__/g, (_, a, b) => {
        const word = a ?? b ?? '';
        return `<u>${word}</u>`;
    });
});

const igHandle = computed(() => props.drop?.coach?.handle ?? 'tu.coach');
const avatarInitial = computed(() => igHandle.value.charAt(0).toUpperCase());

const captionPreview = computed(() => {
    const cap = String(props.reel.caption ?? '').replace(/\s+/g, ' ').trim();
    if (cap.length <= 80) return cap;
    return cap.slice(0, 80) + '...';
});

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
    <article class="reel-card" :class="{ posted: isPosted }">
        <header class="reel-head">
            <div class="reel-num-col">
                <div class="reel-big">R{{ reelNumber }}</div>
                <div class="reel-type-label">{{ reelTypeLabel }}</div>
            </div>
            <div class="reel-head-center">
                <div class="reel-meta-row">
                    <span class="badge" :class="reelBadgeClass">{{ reelTypeBadgeLabel }}</span>
                    <span class="reel-platform">{{ platformText }}</span>
                </div>
                <div class="reel-title">{{ reel.title }}</div>
            </div>
            <div class="reel-head-right">
                <PieceMarkPublishedButton
                    :drop-id="dropId"
                    :piece-key="reel.key"
                    :state="stateValue"
                />
            </div>
        </header>

        <div class="reel-body">
            <div class="hook-block">
                <div class="hook-eyebrow">
                    <span class="ic ic-red ic-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
                        </svg>
                    </span>
                    <span class="eyebrow">Hook de apertura</span>
                </div>
                <p class="hook-text" v-html="hookHtml"></p>
                <p v-if="reel.hook?.rationale" class="hook-rationale">{{ reel.hook.rationale }}</p>
            </div>

            <div v-if="reel.timecode_table?.length">
                <div class="section-eyebrow">
                    <span class="ic ic-sky ic-sm"></span>
                    <span class="eyebrow">Guion por escenas</span>
                </div>
                <ReelTimecodeTable :rows="reel.timecode_table" />
            </div>

            <div v-if="reel.caption" class="caption-section">
                <div class="ig-mockup">
                    <div class="ig-head">
                        <div class="ig-avatar">{{ avatarInitial }}</div>
                        <span class="ig-handle">{{ igHandle }}</span>
                        <span class="ig-more">···</span>
                    </div>
                    <div class="ig-img-ph">reel · 9:16</div>
                    <div class="ig-actions">
                        <svg class="ig-act-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                        <svg class="ig-act-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12" />
                        </svg>
                        <svg class="ig-save-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                        </svg>
                    </div>
                    <div class="ig-body">
                        <p class="ig-cap-text">
                            <strong>{{ igHandle }}</strong> {{ captionPreview }}<span style="color:#888"> más</span>
                        </p>
                    </div>
                </div>
                <div class="caption-raw-wrap">
                    <div class="caption-raw-head">
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <span class="ic ic-sky ic-sm"></span>
                            <span class="eyebrow">Caption</span>
                        </div>
                        <button
                            type="button"
                            class="copy-btn"
                            :class="{ copied: captionCopied }"
                            @click="copyCaption"
                        >
                            {{ captionCopied ? 'Copiado ✓' : 'Copiar' }}
                        </button>
                    </div>
                    <pre class="caption-raw">{{ reel.caption }}</pre>
                </div>
            </div>

            <div v-if="reel.music_note" class="music-row">
                <span class="ic ic-violet ic-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303" />
                    </svg>
                </span>
                <span class="music-text">{{ reel.music_note }}</span>
                <span v-if="reel.format_meta?.bpm_hint" class="bpm-badge">● {{ reel.format_meta.bpm_hint }} BPM</span>
            </div>

            <details v-if="reel.production_notes" class="prod-notes">
                <summary>
                    <span class="prod-chev">▶</span>
                    <span class="ic ic-sky ic-sm" style="margin-left:.25rem"></span>
                    Notas de producción
                </summary>
                <div class="prod-body">{{ reel.production_notes }}</div>
            </details>
        </div>
    </article>
</template>
