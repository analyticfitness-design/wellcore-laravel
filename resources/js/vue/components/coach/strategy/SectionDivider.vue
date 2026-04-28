<script setup>
import { computed } from 'vue';

const props = defineProps({
    number: { type: String, required: true },
    title: { type: String, required: true },
    sub: { type: String, default: '' },
    icon: { type: String, default: null },
});

// Default icon mapping by section number when prop is not passed
const defaultIconByNumber = {
    '01': 'amber',
    '02': 'red',
    '03': 'sky',
    '04': 'emerald',
    '05': 'orange',
    '06': 'emerald',
};

const resolvedIcon = computed(() => props.icon ?? defaultIconByNumber[props.number] ?? null);

// SVG paths per icon color (sourced from strategy-redesigned.html)
const iconHasSvg = computed(() => ['amber', 'red', 'sky', 'emerald'].includes(resolvedIcon.value));
</script>

<template>
    <div class="sec-divider">
        <span class="sec-n">{{ number }}</span>
        <span class="sec-slash">/</span>

        <span v-if="resolvedIcon" :class="['ic', `ic-${resolvedIcon}`, 'ic-sm']">
            <svg v-if="iconHasSvg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path
                    v-if="resolvedIcon === 'amber'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"
                />
                <path
                    v-else-if="resolvedIcon === 'red'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"
                />
                <path
                    v-else-if="resolvedIcon === 'sky'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859"
                />
                <path
                    v-else-if="resolvedIcon === 'emerald'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
        </span>

        <h2 class="sec-h">{{ title }}</h2>
        <span v-if="sub" class="sec-s">{{ sub }}</span>
        <span class="sec-line"></span>
    </div>
</template>
