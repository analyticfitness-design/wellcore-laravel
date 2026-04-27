<script setup>
import { ref } from 'vue';
import StoryDrawer from './StoryDrawer.vue';

const props = defineProps({
    story: { type: Object, required: true },
    dropId: { type: Number, required: true },
    pieceState: { type: Object, default: null },
});

const dayColors = {
    LUN: '#DC2626',
    MAR: '#10B981',
    MIE: '#F59E0B',
    JUE: '#3B82F6',
    VIE: '#A78BFA',
    SAB: '#EC4899',
    DOM: '#14B8A6',
};

const dayColor = dayColors[props.story.day] ?? '#DC2626';

const drawerOpen = ref(false);

const previewText = (props.story.slides?.[0]?.text ?? '').slice(0, 80);
</script>

<template>
    <div>
        <button
            type="button"
            @click="drawerOpen = true"
            class="relative w-full text-left rounded-xl border border-wc-border bg-wc-bg-secondary hover:border-wc-accent/40 transition-all duration-200 hover:-translate-y-0.5 overflow-hidden"
            :style="{ borderTopColor: dayColor, borderTopWidth: '3px' }"
        >
            <div class="p-4 space-y-3">
                <!-- Day header -->
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-display text-lg uppercase" :style="{ color: dayColor }">{{ story.day }}</span>
                        <span v-if="story.pillar" class="ml-2 font-mono text-[9px] uppercase tracking-[0.15em] text-wc-text-tertiary">{{ story.pillar }}</span>
                    </div>

                    <!-- State badge -->
                    <span v-if="pieceState?.state === 'published'" class="text-emerald-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <span v-else-if="pieceState?.state === 'skipped'" class="text-wc-text-tertiary opacity-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </span>
                </div>

                <!-- Preview text -->
                <p v-if="previewText" class="text-xs text-wc-text-secondary leading-relaxed line-clamp-3">
                    {{ previewText }}{{ (story.slides?.[0]?.text ?? '').length > 80 ? '...' : '' }}
                </p>

                <!-- Slides count -->
                <div class="font-mono text-[9px] uppercase tracking-[0.2em] text-wc-text-tertiary">
                    {{ story.slides?.length ?? 0 }} slides
                </div>
            </div>
        </button>

        <!-- Drawer -->
        <Transition name="drawer">
            <StoryDrawer
                v-if="drawerOpen"
                :story="story"
                :drop-id="dropId"
                :piece-state="pieceState"
                @close="drawerOpen = false"
            />
        </Transition>
    </div>
</template>

<style scoped>
.drawer-enter-active,
.drawer-leave-active {
    transition: opacity 0.2s ease;
}
.drawer-enter-from,
.drawer-leave-to {
    opacity: 0;
}
</style>
