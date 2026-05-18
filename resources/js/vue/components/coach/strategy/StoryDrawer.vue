<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import PieceMarkPublishedButton from './PieceMarkPublishedButton.vue';
import { coachStrategyApi } from '../../../api/coachStrategy';

const { t } = useI18n();

const props = defineProps({
    story: { type: Object, required: true },
    dropId: { type: Number, required: true },
    pieceState: { type: Object, default: null },
    dropAssets: { type: Array, default: () => [] },
});

const emit = defineEmits(['close']);

const copied = ref(false);
const downloading = ref(false);
const activeIdx = ref(0);

const dayColors = {
    LUN: 'lun', MAR: 'mar', MIE: 'mie', JUE: 'jue',
    VIE: 'vie', SAB: 'sab', DOM: 'dom',
};
const dayKey = computed(() => dayColors[props.story.day] ?? 'lun');

const dayKeyMap = {
    LUN: 'story_day_full_lun', MAR: 'story_day_full_mar', MIE: 'story_day_full_mie',
    JUE: 'story_day_full_jue', VIE: 'story_day_full_vie', SAB: 'story_day_full_sab',
    DOM: 'story_day_full_dom',
};
const dayFull = computed(() => {
    const key = dayKeyMap[props.story.day];
    return key ? t(`coach_growth.strategy.${key}`) : props.story.day;
});

const pieceKey = computed(() => `story_${(props.story.day ?? '').toLowerCase()}`);
const stateValue = computed(() => props.pieceState?.state ?? 'pending');

const slides = computed(() => props.story.slides ?? []);
const activeSlide = computed(() => slides.value[activeIdx.value] ?? null);

const allSlidesText = computed(() =>
    slides.value
        .map((s, i) => `[Slide ${i + 1}] ${s.text ?? ''}`)
        .filter(Boolean)
        .join('\n\n'),
);

const linkedAssets = computed(() => {
    const day = props.story.day;
    const list = (props.dropAssets ?? []).filter((a) => {
        const lt = a.linked_to;
        if (!lt) return false;
        return (lt.type === 'story' || lt.type === 'slide') && lt.day === day;
    });
    list.sort((a, b) => (a.order ?? 999) - (b.order ?? 999));
    return list;
});

const hasAssets = computed(() => linkedAssets.value.length > 0);

const dmHint = computed(() => props.story.dm_followup_hint ?? props.story.dm_followup ?? '');

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

function onKey(e) {
    if (e.key === 'Escape') emit('close');
    if (e.key === 'ArrowRight' && activeIdx.value < slides.value.length - 1) activeIdx.value += 1;
    if (e.key === 'ArrowLeft' && activeIdx.value > 0) activeIdx.value -= 1;
}

onMounted(() => window.addEventListener('keydown', onKey));
onBeforeUnmount(() => window.removeEventListener('keydown', onKey));
</script>

<template>
    <div class="story-drawer-backdrop" @click.self="emit('close')">
        <aside class="story-drawer" :data-day="dayKey">
            <header class="sd-header">
                <div class="sd-header-stripe"></div>
                <div class="sd-header-inner">
                    <div class="sd-header-left">
                        <span class="sd-day-mono">{{ t('coach_growth.strategy.story_day_label', { day: dayFull.toUpperCase() }) }}</span>
                        <h3 class="sd-day-big">{{ story.day }}</h3>
                        <span v-if="story.pillar" class="sd-pillar-pill">{{ story.pillar }}</span>
                    </div>
                    <button type="button" class="sd-close-btn" @click="emit('close')" :aria-label="t('coach_growth.strategy.story_close')">
                        ESC ·
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </header>

            <div class="sd-body">
                <nav v-if="slides.length > 1" class="sd-slide-tabs" role="tablist">
                    <button
                        v-for="(_, idx) in slides"
                        :key="idx"
                        type="button"
                        @click="activeIdx = idx"
                        :class="['sd-slide-tab', { active: activeIdx === idx }]"
                        :aria-selected="activeIdx === idx"
                    >
                        {{ t('coach_growth.strategy.story_slide_label', { num: String(idx + 1).padStart(2, '0') }) }}
                    </button>
                </nav>

                <article v-if="activeSlide" class="sd-slide-card">
                    <div class="sd-slide-meta">
                        <span class="sd-slide-kind">{{ activeSlide.kind || 'slide' }}</span>
                        <span
                            v-if="activeSlide.sticker && activeSlide.sticker !== 'none'"
                            class="sd-slide-sticker"
                        >
                            <span class="sd-dot"></span>
                            sticker · {{ activeSlide.sticker }}
                        </span>
                    </div>
                    <p class="sd-slide-text">{{ activeSlide.text }}</p>
                    <div v-if="activeSlide.visual_hint" class="sd-visual-hint">
                        <span class="sd-visual-label">{{ t('coach_growth.strategy.story_visual_label') }}</span>
                        <p>{{ activeSlide.visual_hint }}</p>
                    </div>
                </article>

                <section v-if="hasAssets" class="sd-assets">
                    <span class="sd-section-label">{{ t('coach_growth.strategy.story_assets_label', { count: linkedAssets.length }) }}</span>
                    <div class="sd-assets-grid">
                        <div
                            v-for="(asset, idx) in linkedAssets"
                            :key="asset.id || idx"
                            class="sd-asset-card"
                        >
                            <span class="sd-asset-order">{{ String(idx + 1).padStart(2, '0') }}</span>
                            <div class="sd-asset-thumb">
                                <img
                                    v-if="asset.kind === 'image' || /\.(jpg|jpeg|png|gif|webp)$/i.test(asset.filename || '')"
                                    :src="asset.url"
                                    :alt="asset.filename"
                                    loading="lazy"
                                />
                                <div v-else class="sd-asset-fallback">{{ t('coach_growth.strategy.story_video_fallback', { filename: asset.filename }) }}</div>
                            </div>
                            <button
                                type="button"
                                @click="downloadAsset(asset)"
                                :disabled="downloading"
                                class="sd-asset-dl"
                            >
                                {{ t('coach_growth.strategy.story_dl') }}
                            </button>
                        </div>
                    </div>
                </section>

                <aside v-if="dmHint" class="sd-dm-block">
                    <span class="sd-dm-label">{{ t('coach_growth.strategy.story_dm_label') }}</span>
                    <p class="sd-dm-text">{{ dmHint }}</p>
                </aside>
            </div>

            <footer class="sd-footer">
                <button type="button" class="sd-close-mobile" @click="emit('close')" :aria-label="t('coach_growth.strategy.story_close')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ t('coach_growth.strategy.story_close') }}
                </button>
                <button
                    type="button"
                    @click="copyText"
                    :class="['sd-copy-btn', { ok: copied }]"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    {{ copied ? t('coach_growth.strategy.story_copied') : t('coach_growth.strategy.story_copy') }}
                </button>
                <div class="sd-footer-spacer"></div>
                <PieceMarkPublishedButton :drop-id="dropId" :piece-key="pieceKey" :state="stateValue" />
            </footer>
        </aside>
    </div>
</template>
