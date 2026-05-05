<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useMentions } from '../../composables/useMentions';

const props = defineProps({
    content: { type: String, default: '' },
    scopeCoachId: { type: [Number, null], default: null },
});

const router = useRouter();
const { extract } = useMentions();

const segments = computed(() => {
    if (!props.content) return [];

    const tokens = extract(props.content);
    if (!tokens.length) return [{ type: 'text', value: props.content }];

    const result = [];
    let cursor = 0;

    for (const token of tokens) {
        const idx = props.content.indexOf(token.raw, cursor);
        if (idx === -1) continue;
        if (idx > cursor) {
            result.push({ type: 'text', value: props.content.slice(cursor, idx) });
        }
        result.push({ type: 'mention', mentionType: token.type, id: token.id, raw: token.raw });
        cursor = idx + token.raw.length;
    }
    if (cursor < props.content.length) {
        result.push({ type: 'text', value: props.content.slice(cursor) });
    }
    return result;
});

function onMentionClick(seg) {
    if (seg.mentionType === 'client' && seg.id) {
        router.push(`/client/profile/${seg.id}`);
    }
}

function chipClass(type) {
    return {
        client: 'text-blue-500 bg-blue-500/15',
        coach: 'text-amber-500 bg-amber-500/15',
        admin: 'text-wc-accent bg-wc-accent/15',
    }[type] || '';
}
</script>

<template>
  <p class="whitespace-pre-wrap text-sm text-wc-text leading-relaxed">
    <template v-for="(seg, i) in segments" :key="i">
      <span v-if="seg.type === 'text'">{{ seg.value }}</span>
      <button v-else
        @click="onMentionClick(seg)"
        :class="['inline-flex items-center rounded-md px-1.5 py-0 text-sm font-medium', chipClass(seg.mentionType)]"
      >{{ seg.raw }}</button>
    </template>
  </p>
</template>
