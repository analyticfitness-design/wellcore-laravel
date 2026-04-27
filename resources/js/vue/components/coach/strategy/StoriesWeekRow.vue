<script setup>
import StoryDayCard from './StoryDayCard.vue';

const props = defineProps({
    stories: { type: Array, required: true },
    dropId: { type: Number, required: true },
    pieces: { type: Array, default: () => [] },
});

function pieceKeyFor(story) {
    return `story_${(story.day ?? '').toLowerCase()}`;
}

function getPieceState(story) {
    const key = pieceKeyFor(story);
    return props.pieces.find((p) => p.piece_key === key) ?? null;
}
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7">
        <StoryDayCard
            v-for="story in stories"
            :key="story.day"
            :story="story"
            :drop-id="dropId"
            :piece-state="getPieceState(story)"
        />
    </div>
</template>
