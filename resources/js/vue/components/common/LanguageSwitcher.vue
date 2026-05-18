<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useLocale } from '../../composables/useLocale';

const { locale, isLocked, isSaving, setLocale } = useLocale();

const open = ref(false);
const root = ref(null);

const label = computed(() => locale.value === 'en' ? 'EN' : 'ES');
const lockTooltip = computed(() =>
    locale.value === 'en'
        ? 'Language set by your coach team'
        : 'Idioma asignado por tu coach',
);

function handleClickOutside(event) {
    if (root.value && !root.value.contains(event.target)) {
        open.value = false;
    }
}

async function change(target) {
    if (isLocked.value || isSaving.value) {
        open.value = false;
        return;
    }
    if (target === locale.value) {
        open.value = false;
        return;
    }
    open.value = false;
    const ok = await setLocale(target);
    if (ok) {
        // Cookie ya quedó persistida por el backend; recargamos para que el
        // shell Blade vuelva a inyectar el bundle correcto sin FOUC.
        window.location.reload();
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div ref="root" class="relative">
        <button
            type="button"
            :disabled="isLocked"
            :title="isLocked ? lockTooltip : ''"
            :aria-label="isLocked ? lockTooltip : 'Cambiar idioma'"
            :class="[
                'flex items-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs font-medium text-wc-text-secondary hover:text-wc-text transition-colors',
                isLocked ? 'opacity-60 cursor-not-allowed' : ''
            ]"
            @click="!isLocked && (open = !open)"
        >
            <span>{{ label }}</span>
            <svg
                v-if="!isLocked"
                class="h-3 w-3"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
            <svg
                v-else
                class="h-3 w-3"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </button>

        <transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="open && !isLocked"
                class="absolute right-0 top-full mt-1 w-36 overflow-hidden rounded-lg border border-wc-border bg-wc-bg-secondary shadow-xl z-50"
                role="menu"
            >
                <button
                    type="button"
                    role="menuitem"
                    :disabled="isSaving"
                    class="flex w-full items-center gap-2 px-3 py-2 text-xs hover:bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text disabled:opacity-50"
                    @click="change('es')"
                >
                    <span aria-hidden="true">🇨🇴</span>
                    <span>Español</span>
                    <span v-if="locale === 'es'" class="ml-auto text-wc-accent" aria-label="actual">✓</span>
                </button>
                <button
                    type="button"
                    role="menuitem"
                    :disabled="isSaving"
                    class="flex w-full items-center gap-2 px-3 py-2 text-xs hover:bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text disabled:opacity-50"
                    @click="change('en')"
                >
                    <span aria-hidden="true">🇺🇸</span>
                    <span>English</span>
                    <span v-if="locale === 'en'" class="ml-auto text-wc-accent" aria-label="current">✓</span>
                </button>
            </div>
        </transition>
    </div>
</template>
