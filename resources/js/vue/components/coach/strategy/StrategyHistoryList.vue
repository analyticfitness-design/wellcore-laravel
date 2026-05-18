<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useCoachStrategyStore } from '../../../stores/coachStrategy';

const { t } = useI18n();
const store = useCoachStrategyStore();
const comingSoon = ref(false);

onMounted(async () => {
    if (!store.history.length) {
        await store.fetchHistory();
    }
});

const statusClasses = {
    pending: 'bg-yellow-500/10 text-yellow-500',
    generating: 'bg-blue-500/10 text-blue-400',
    in_review: 'bg-blue-500/10 text-blue-400',
    approved: 'bg-emerald-500/10 text-emerald-400',
    ready: 'bg-emerald-500/10 text-emerald-400',
    in_progress: 'bg-blue-500/10 text-blue-400',
    completed: 'bg-emerald-500/10 text-emerald-500',
    archived: 'bg-wc-bg-tertiary text-wc-text-tertiary',
};

const statusKeys = {
    pending: 'status_pending',
    generating: 'status_generating',
    in_review: 'status_in_review',
    approved: 'status_approved',
    ready: 'status_ready',
    in_progress: 'status_in_progress',
    completed: 'status_completed',
    archived: 'status_archived',
};

function getStatusConfig(status) {
    const cls = statusClasses[status] ?? 'bg-wc-bg-tertiary text-wc-text-tertiary';
    const key = statusKeys[status];
    const label = key ? t(`coach_growth.history.${key}`) : status;
    return { label, cls };
}
</script>

<template>
    <div class="space-y-4">
        <div v-if="store.isLoadingHistory" class="space-y-4">
            <div v-for="n in 4" :key="n" class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary h-28"></div>
        </div>

        <div v-else-if="!store.history.length" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
            <p class="text-sm text-wc-text-secondary">{{ t('coach_growth.history.empty') }}</p>
        </div>

        <div v-else class="space-y-4">
            <div
                v-for="drop in store.history"
                :key="drop.id"
                class="relative rounded-xl border border-wc-border bg-wc-bg-secondary py-6 pl-12 pr-6 transition-all duration-200 hover:-translate-y-0.5 hover:border-wc-accent/30 cursor-pointer"
                @click="comingSoon = true"
            >
                <!-- Week label -->
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-2 flex-1">
                        <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary">
                            {{ t('coach_growth.history.week_label', { year: drop.iso_year, week: String(drop.iso_week).padStart(2, '0') }) }}
                        </span>
                        <h3 class="font-display text-xl uppercase tracking-tight text-wc-text">
                            {{ drop.brief_title ?? t('coach_growth.history.no_title') }}
                        </h3>

                        <!-- Progress bar -->
                        <div class="pt-1">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-mono text-[9px] uppercase tracking-[0.2em] text-wc-text-tertiary">
                                    {{ t('coach_growth.history.progress_label') }}
                                </span>
                                <span class="font-data text-xs text-wc-text tabular-nums">
                                    {{ t('coach_growth.history.progress_count', { done: drop.pieces_completed, total: drop.pieces_total }) }}
                                </span>
                            </div>
                            <div class="h-px w-full bg-wc-border">
                                <div
                                    class="h-full bg-wc-accent transition-all duration-500"
                                    :style="{ width: drop.pieces_total ? `${Math.round((drop.pieces_completed / drop.pieces_total) * 100)}%` : '0%' }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Status badge -->
                    <span
                        class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold"
                        :class="getStatusConfig(drop.status).cls"
                    >
                        {{ getStatusConfig(drop.status).label }}
                    </span>
                </div>

                <!-- Arrow -->
                <div class="absolute right-6 top-1/2 -translate-y-1/2 text-wc-text-tertiary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Coming soon modal -->
        <Transition name="fade">
            <div v-if="comingSoon" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4" @click="comingSoon = false">
                <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-8 text-center max-w-sm w-full" @click.stop>
                    <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary">{{ t('coach_growth.history.coming_soon_label') }}</span>
                    <h3 class="mt-3 font-display text-2xl uppercase text-wc-text">{{ t('coach_growth.history.coming_soon_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">{{ t('coach_growth.history.coming_soon_body') }}</p>
                    <button
                        type="button"
                        @click="comingSoon = false"
                        class="mt-6 rounded-lg bg-wc-accent px-6 py-2 font-mono text-xs uppercase tracking-wider text-white hover:bg-wc-accent/90 transition-colors"
                    >
                        {{ t('coach_growth.history.coming_soon_close') }}
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
