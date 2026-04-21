<script setup>
/**
 * BentoCelebration — componente base para modales fullscreen de celebración.
 *
 * Modo global (desde ClientLayout/RiseLayout):
 *   <BentoCelebration :global="true" @cta-click="..." @share="..." />
 *   + celebrate('workout', {...}) desde cualquier composable
 *
 * Modo controlado:
 *   <BentoCelebration v-model:open="isOpen" preset="workout" :data="{...}" />
 *
 * Data shape:
 *   {
 *     title, subtitle, status, metadata,
 *     hero: { label, value, unit, description, badge, icon },
 *     stats: [{ icon, label, value, unit, sub, subColor, span }],
 *     quote,
 *     share: { enabled: true, preset: 'workout' },
 *     cta,
 *   }
 */
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useFocusTrap } from '@vueuse/integrations/useFocusTrap';
import { onClickOutside } from '@vueuse/core';
import { getPreset } from '../../presets/celebration-presets';
import { useCelebration } from '../../composables/useCelebration';
import { useConfetti } from '../../composables/useConfetti';
import { useHaptics } from '../../composables/useHaptics';
import { useSound } from '../../composables/useSound';
import { useCountUp } from '../../composables/useCountUp';
import { useReducedMotion } from '../../composables/useReducedMotion';
import WcIcon from '../ui/WcIcon.vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    preset: { type: String, default: 'workout' },
    data: { type: Object, default: () => ({}) },
    global: { type: Boolean, default: false },
    autoDismissMs: { type: Number, default: 0 },
});
const emit = defineEmits(['update:open', 'close', 'cta-click', 'share']);

const { current, dismiss: dismissGlobal } = useCelebration();
const { fire } = useConfetti();
const haptic = useHaptics();
const sound = useSound();
const reducedMotion = useReducedMotion();

const active = computed(() => props.global ? !!current.value : props.open);
const activeData = computed(() => props.global ? (current.value?.data || {}) : props.data);
const activePreset = computed(() => props.global ? (current.value?.preset || 'workout') : props.preset);
const presetConfig = computed(() => getPreset(activePreset.value));

const modalRef = ref(null);
const { activate, deactivate } = useFocusTrap(modalRef, { immediate: false });

function handleClose(method = 'cta') {
    if (props.global) {
        dismissGlobal();
    } else {
        emit('update:open', false);
    }
    emit('close', method);
}

function handleCtaClick() {
    emit('cta-click', activePreset.value);
    handleClose('cta');
}

function handleShare() {
    emit('share', { preset: activePreset.value, data: activeData.value });
}

onClickOutside(modalRef, () => { if (active.value) handleClose('backdrop'); });

function onKeydown(e) {
    if (e.key === 'Escape' && active.value) handleClose('escape');
}

let autoTimer = null;
function armAutoDismiss() {
    clearTimeout(autoTimer);
    if (props.autoDismissMs > 0) {
        autoTimer = setTimeout(() => handleClose('timeout'), props.autoDismissMs);
    }
}

watch(active, async (val) => {
    if (val) {
        await nextTick();
        try { activate(); } catch (_) {}
        if (!reducedMotion.value && presetConfig.value.confettiPreset) {
            fire(presetConfig.value.confettiPreset);
        }
        if (presetConfig.value.soundKey) sound.play(presetConfig.value.soundKey);
        if (presetConfig.value.hapticPattern) haptic.pattern(presetConfig.value.hapticPattern);
        armAutoDismiss();
    } else {
        try { deactivate(); } catch (_) {}
        clearTimeout(autoTimer);
    }
});

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown);
    clearTimeout(autoTimer);
});

const heroValueRef = ref(null);
useCountUp(heroValueRef, () => activeData.value?.hero?.value, { duration: 1200 });

const statsToRender = computed(() => (activeData.value.stats || []).slice(0, 6));
</script>

<template>
  <Teleport to="body">
    <Transition name="wc-celebration">
      <div
        v-if="active"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
        role="dialog"
        aria-modal="true"
        :aria-label="activeData.title || 'Celebración'"
      >
        <div
          ref="modalRef"
          :class="[
            'relative w-full max-w-[340px] rounded-[28px] overflow-hidden bg-[#0b0b10] p-5 animate-wc-pop',
            presetConfig.borderClass || 'border-wc-border',
            'border',
          ]"
          :style="activePreset === 'level-up' ? { boxShadow: '0 30px 80px -20px rgba(139,92,246,.4)' } : {}"
        >
          <!-- Header -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <WcIcon name="wc:logo" :size="24" />
              <span class="font-display text-sm tracking-widest font-bold">WELLCORE</span>
            </div>
            <span v-if="activeData.metadata" class="text-[10px] text-wc-text-tertiary">{{ activeData.metadata }}</span>
          </div>

          <!-- Title -->
          <h2 class="mt-4 font-display text-4xl leading-none tracking-tight font-bold">
            {{ activeData.title }}<br v-if="activeData.subtitle" />
            <span v-if="activeData.subtitle" class="text-wc-text-tertiary font-medium">{{ activeData.subtitle }}</span>
          </h2>

          <!-- Status subtitle -->
          <p
            v-if="activeData.status"
            :class="[
              'mt-2 text-xs tracking-[0.22em] uppercase flex items-center gap-1.5 font-semibold',
              presetConfig.subtitleColor,
            ]"
          >
            <WcIcon :name="presetConfig.subtitleIcon" :size="14" />
            {{ activeData.status }}
          </p>

          <!-- Bento grid -->
          <div class="mt-4 grid grid-cols-6 auto-rows-[70px] gap-2 text-sm">
            <slot name="hero" :data="activeData.hero" :preset-config="presetConfig">
              <div
                v-if="activeData.hero"
                :class="['col-span-4 row-span-2 rounded-2xl border p-3 relative overflow-hidden wc-noise', presetConfig.borderClass, presetConfig.heroClass]"
              >
                <div class="relative flex items-center justify-between">
                  <div class="flex items-center gap-1.5">
                    <WcIcon :name="activeData.hero.icon || presetConfig.titleIcon" :size="16" />
                    <p class="text-[10px] tracking-wider uppercase font-bold" :class="`text-${presetConfig.accentColor}-200`">
                      {{ activeData.hero.label }}
                    </p>
                  </div>
                  <span
                    v-if="activeData.hero.badge"
                    class="rounded-full px-2 py-0.5 text-[9px] font-bold flex items-center gap-1"
                    :class="`text-${presetConfig.accentColor}-300`"
                  >
                    <i class="ph-fill ph-trend-up" style="font-size:10px"></i>{{ activeData.hero.badge }}
                  </span>
                </div>
                <div class="relative mt-auto pt-4">
                  <p class="font-data text-[44px] leading-none font-black">
                    <span ref="heroValueRef">{{ activeData.hero.value }}</span>
                    <span v-if="activeData.hero.unit" class="text-lg text-wc-text-tertiary font-bold"> {{ activeData.hero.unit }}</span>
                  </p>
                  <p v-if="activeData.hero.description" class="text-[11px] text-wc-text-secondary mt-0.5">{{ activeData.hero.description }}</p>
                </div>
              </div>
            </slot>

            <!-- Stats cells -->
            <div
              v-for="(stat, i) in statsToRender"
              :key="i"
              :class="[`col-span-${stat.span || 2}`, 'rounded-2xl bg-wc-bg-tertiary border border-wc-border p-3']"
            >
              <div class="flex items-center gap-1">
                <WcIcon v-if="stat.icon" :name="stat.icon" :size="11" class="text-wc-text-tertiary" />
                <p class="text-[9px] tracking-wider uppercase text-wc-text-tertiary">{{ stat.label }}</p>
              </div>
              <p class="font-data text-xl font-bold mt-0.5">
                {{ stat.value }}<span v-if="stat.unit" class="text-xs text-wc-text-tertiary"> {{ stat.unit }}</span>
              </p>
              <p v-if="stat.sub" class="text-[10px]" :class="stat.subColor || 'text-wc-text-tertiary'">{{ stat.sub }}</p>
            </div>

            <!-- Quote -->
            <div
              v-if="activeData.quote"
              class="col-span-6 rounded-2xl bg-wc-bg-tertiary border p-3"
              :class="presetConfig.borderClass"
            >
              <div class="flex items-center gap-1">
                <i class="ph-fill ph-quotes" style="font-size:11px" :class="`text-${presetConfig.accentColor}-400`"></i>
                <p class="text-[9px] tracking-wider uppercase font-bold" :class="`text-${presetConfig.accentColor}-400`">Tu coach</p>
              </div>
              <p class="mt-0.5 text-sm italic text-wc-text-secondary leading-snug">"{{ activeData.quote }}"</p>
            </div>
          </div>

          <!-- CTA + share -->
          <div class="mt-4 flex gap-2">
            <button
              v-if="activeData.share?.enabled"
              @click="handleShare"
              type="button"
              class="rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm font-semibold hover:border-wc-accent/50 transition-colors flex items-center gap-2"
              aria-label="Compartir logro"
            >
              <i class="ph-bold ph-share-network" style="font-size:16px"></i>
            </button>
            <button
              @click="handleCtaClick"
              type="button"
              class="flex-1 rounded-xl bg-wc-accent py-3 text-sm font-semibold shadow-[0_10px_30px_-10px_rgba(220,38,38,.5)] hover:brightness-110 transition flex items-center justify-center gap-2"
            >
              <span>{{ activeData.cta || presetConfig.cta }}</span>
              <i :class="presetConfig.ctaIcon" style="font-size:14px"></i>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.wc-celebration-enter-active,
.wc-celebration-leave-active {
    transition: opacity 0.35s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.wc-celebration-enter-from,
.wc-celebration-leave-to {
    opacity: 0;
}
</style>
