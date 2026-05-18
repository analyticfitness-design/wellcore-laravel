<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useModeration } from '../../composables/useModeration';

const { t } = useI18n();

const props = defineProps({
    post: { type: Object, required: true },
});
const emit = defineEmits(['updated', 'deleted']);

const moderation = useModeration();
const open = ref(false);
const confirmingDelete = ref(false);

const isPinned = computed(() => !!props.post.pinned);
const isOfficial = computed(() => !!props.post.is_official);

async function togglePin() {
    try {
        if (isPinned.value) {
            await moderation.unpinPost(props.post.id);
        } else {
            await moderation.pinPost(props.post.id, 168, null);
        }
        emit('updated');
    } finally {
        open.value = false;
    }
}

async function makeOfficial() {
    if (isOfficial.value) {
        open.value = false;
        return;
    }
    try {
        await moderation.makeOfficial(props.post.id);
        emit('updated');
    } finally {
        open.value = false;
    }
}

function startDelete() {
    confirmingDelete.value = true;
}

async function confirmDelete() {
    try {
        await moderation.deletePost(props.post.id, 'coach_action');
        emit('deleted', props.post.id);
    } finally {
        confirmingDelete.value = false;
        open.value = false;
    }
}
</script>

<template>
  <div class="relative">
    <button @click="open = !open" class="rounded-lg p-1.5 text-wc-text-tertiary hover:text-wc-text hover:bg-wc-bg-tertiary" :aria-label="t('client_social.coach_actions_label')">
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zm0 6a.75.75 0 110-1.5.75.75 0 010 1.5zm0 6a.75.75 0 110-1.5.75.75 0 010 1.5z" />
      </svg>
    </button>
    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95"
    >
      <div v-if="open" class="absolute right-0 top-full mt-1 w-52 rounded-xl border border-wc-border bg-wc-bg-secondary shadow-xl z-20 py-1">
        <button @click="togglePin" class="w-full text-left px-3 py-2 text-sm hover:bg-wc-bg-tertiary flex items-center gap-2">
          <span>\u{1F4CC}</span>
          <span>{{ isPinned ? t('client_social.coach_actions_unpin') : t('client_social.coach_actions_pin') }}</span>
        </button>
        <button v-if="!isOfficial" @click="makeOfficial" class="w-full text-left px-3 py-2 text-sm hover:bg-wc-bg-tertiary flex items-center gap-2">
          <span>⭐</span>
          <span>{{ t('client_social.coach_actions_make_official') }}</span>
        </button>
        <div class="my-1 border-t border-wc-border"></div>
        <button v-if="!confirmingDelete" @click="startDelete" class="w-full text-left px-3 py-2 text-sm text-rose-500 hover:bg-rose-500/10 flex items-center gap-2">
          <span>\u{1F5D1}</span><span>{{ t('client_social.coach_actions_delete') }}</span>
        </button>
        <button v-else @click="confirmDelete" class="w-full text-left px-3 py-2 text-sm bg-rose-500/10 text-rose-600 font-semibold flex items-center gap-2">
          <span>⚠️</span><span>{{ t('client_social.coach_actions_confirm_delete') }}</span>
        </button>
      </div>
    </Transition>
  </div>
</template>
