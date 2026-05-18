<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import { useMentions } from '../../composables/useMentions';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    maxLength: { type: Number, default: 1000 },
    rows: { type: Number, default: 4 },
    scope: { type: String, default: 'coach-team' },
});
const emit = defineEmits(['update:modelValue', 'mention']);

const { t } = useI18n();
const { search } = useMentions();

const textareaRef = ref(null);
const dropdownRef = ref(null);
const dropdownOpen = ref(false);
const dropdownItems = ref([]);
const selectedIndex = ref(0);
const currentToken = ref('');
const tokenStartPos = ref(0);
const dropdownPos = ref({ top: 0, left: 0 });

const SPECIAL_TOKENS = computed(() => [
    { type: 'special', id: null, name: 'coach',    label: t('client_social.mention_coach_label')    },
    { type: 'special', id: null, name: 'wellcore', label: t('client_social.mention_wellcore_label') },
]);

let searchTimer = null;

function onInput(e) {
    const value = e.target.value;
    emit('update:modelValue', value);
    detectMention();
}

function detectMention() {
    if (!textareaRef.value) return;
    const cursor = textareaRef.value.selectionStart;
    const before = props.modelValue.slice(0, cursor);
    const match = before.match(/@([a-zA-Z0-9_]{0,50})$/);

    if (!match) {
        dropdownOpen.value = false;
        return;
    }

    currentToken.value = match[1];
    tokenStartPos.value = cursor - match[0].length;

    const partial = match[1].toLowerCase();
    const matchingSpecials = SPECIAL_TOKENS.value.filter(s => s.name.startsWith(partial));

    if (currentToken.value.length < 3) {
        dropdownItems.value = matchingSpecials.length ? matchingSpecials : [];
        dropdownOpen.value = matchingSpecials.length > 0;
        if (dropdownOpen.value) updateDropdownPos();
        return;
    }

    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(async () => {
        const results = await search(currentToken.value, { scope: props.scope });
        dropdownItems.value = [...matchingSpecials, ...results];
        dropdownOpen.value = dropdownItems.value.length > 0;
        selectedIndex.value = 0;
        if (dropdownOpen.value) updateDropdownPos();
    }, 200);
}

function updateDropdownPos() {
    if (!textareaRef.value) return;
    const rect = textareaRef.value.getBoundingClientRect();
    dropdownPos.value = {
        top: rect.bottom + window.scrollY + 4,
        left: rect.left + window.scrollX,
    };
}

function selectItem(item) {
    let token;
    if (item.type === 'special') {
        token = `@${item.name}`;
    } else if (item.id) {
        token = `@cliente_${item.id}`;
    } else {
        token = `@${item.name}`;
    }

    const before = props.modelValue.slice(0, tokenStartPos.value);
    const after = props.modelValue.slice(tokenStartPos.value + currentToken.value.length + 1);
    const newValue = before + token + ' ' + after;
    emit('update:modelValue', newValue);
    emit('mention', item);

    dropdownOpen.value = false;
    nextTick(() => {
        if (textareaRef.value) {
            const newPos = before.length + token.length + 1;
            textareaRef.value.focus();
            textareaRef.value.setSelectionRange(newPos, newPos);
        }
    });
}

function onKeydown(e) {
    if (!dropdownOpen.value) return;
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex.value = (selectedIndex.value + 1) % dropdownItems.value.length;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex.value = (selectedIndex.value - 1 + dropdownItems.value.length) % dropdownItems.value.length;
    } else if (e.key === 'Enter') {
        e.preventDefault();
        selectItem(dropdownItems.value[selectedIndex.value]);
    } else if (e.key === 'Escape') {
        dropdownOpen.value = false;
    }
}

function closeOnClickOutside(e) {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target) && e.target !== textareaRef.value) {
        dropdownOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', closeOnClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', closeOnClickOutside);
});
</script>

<template>
  <div class="relative">
    <textarea
      ref="textareaRef"
      :value="modelValue"
      @input="onInput"
      @keydown="onKeydown"
      :placeholder="placeholder"
      :maxlength="maxLength"
      :rows="rows"
      class="w-full rounded-lg border border-wc-border bg-wc-bg p-3 text-sm text-wc-text resize-none focus:border-wc-accent focus:outline-none"
    />
    <Teleport to="body">
      <div v-if="dropdownOpen" ref="dropdownRef"
        :style="{ top: dropdownPos.top + 'px', left: dropdownPos.left + 'px' }"
        class="fixed z-50 w-72 max-h-64 overflow-y-auto rounded-xl border border-wc-border bg-wc-bg-secondary shadow-2xl py-1"
      >
        <button
          v-for="(item, i) in dropdownItems" :key="`${item.type}-${item.id ?? item.name}`"
          @click="selectItem(item)"
          :class="i === selectedIndex ? 'bg-wc-bg-tertiary' : ''"
          class="w-full text-left px-3 py-2 text-sm hover:bg-wc-bg-tertiary"
        >
          <span v-if="item.type === 'special'" class="font-semibold text-wc-accent">{{ item.label }}</span>
          <span v-else>
            <span class="font-semibold text-wc-text">{{ item.name }}</span>
            <span class="text-wc-text-tertiary text-xs ml-1">{{ t('client_social.mention_client_suffix') }}</span>
          </span>
        </button>
      </div>
    </Teleport>
  </div>
</template>
