<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import StoryDrawer from './StoryDrawer.vue';

const { t } = useI18n();

const props = defineProps({
    story: { type: Object, required: true },
    dropId: { type: Number, required: true },
    pieceState: { type: Object, default: null },
    dropAssets: { type: Array, default: () => [] },
});

const VALID_DAYS = ['lun', 'mar', 'mie', 'jue', 'vie', 'sab', 'dom'];

const dayKey = computed(() => {
    const k = (props.story.day ?? '').toLowerCase();
    return VALID_DAYS.includes(k) ? k : 'lun';
});

const dayInitialMap = {
    LUN: 'L', MAR: 'M', MIE: 'X', JUE: 'J', VIE: 'V', SAB: 'S', DOM: 'D',
};

const dayInitial = computed(() => {
    const upper = (props.story.day ?? '').toUpperCase();
    return dayInitialMap[upper] ?? upper.charAt(0) ?? '';
});

const linkedCount = computed(() => {
    return (props.dropAssets ?? []).filter((a) => {
        const lt = a.linked_to;
        return lt && (lt.type === 'story' || lt.type === 'slide') && lt.day === props.story.day;
    }).length;
});

const previewText = computed(() => {
    const raw = (props.story.slides?.[0]?.text ?? '').trim();
    if (!raw) return '';
    const words = raw.split(/\s+/);
    if (words.length <= 14) return raw;
    return words.slice(0, 14).join(' ') + '…';
});

const dmHint = computed(() => {
    return props.story.dm_followup_hint ?? props.story.dm_followup ?? '';
});

const drawerOpen = ref(false);
</script>

<template>
    <div>
        <button
            type="button"
            class="story-card"
            :style="`border-top-color: var(--color-wc-day-${dayKey})`"
            @click="drawerOpen = true"
        >
            <div class="story-inner">
                <span class="story-initial" :style="`color: var(--color-wc-day-${dayKey})`">{{ dayInitial }}</span>
                <span
                    v-if="story.pillar"
                    class="story-pillar"
                    :style="`color: var(--color-wc-day-${dayKey}); border-color: var(--color-wc-day-${dayKey})`"
                >{{ story.pillar }}</span>
                <p class="story-preview">{{ previewText }}</p>
                <div class="story-footer">
                    <span class="story-slides">{{ (story.slides?.length ?? 0) === 1 ? t('coach_growth.strategy.story_slides_one', { n: story.slides?.length ?? 0 }) : t('coach_growth.strategy.story_slides_many', { n: story.slides?.length ?? 0 }) }}</span>
                    <span v-if="linkedCount > 0" class="story-img-badge">{{ t('coach_growth.strategy.story_img_badge', { n: linkedCount }) }}</span>
                    <span v-if="pieceState?.state === 'published'" class="story-state">{{ t('coach_growth.strategy.story_published_mark') }}</span>
                    <span v-else-if="pieceState?.state === 'skipped'" class="story-state-skip">{{ t('coach_growth.strategy.story_skipped_mark') }}</span>
                </div>
            </div>
            <div v-if="dmHint" class="story-dm-hint">
                <p class="story-dm-text">{{ dmHint }}</p>
            </div>
        </button>

        <Transition name="drawer">
            <StoryDrawer
                v-if="drawerOpen"
                :story="story"
                :drop-id="dropId"
                :piece-state="pieceState"
                :drop-assets="dropAssets"
                @close="drawerOpen = false"
            />
        </Transition>
    </div>
</template>
