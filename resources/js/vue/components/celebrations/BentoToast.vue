<script setup>
/**
 * BentoToast — toast enriquecido para notificaciones menores.
 *
 * Presets: success | info | warning | destructive | referral | ticket | measurements | profile | video
 *
 * Uso:
 *   <BentoToast v-model:open="show" preset="success" :data="{ title, message, badge, icon }" />
 */
import { watch, onBeforeUnmount } from 'vue';
import { useHaptics } from '../../composables/useHaptics';
import WcIcon from '../ui/WcIcon.vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    preset: { type: String, default: 'success' },
    data: { type: Object, default: () => ({}) },
    position: { type: String, default: 'bottom-right' },
    autoDismissMs: { type: Number, default: 4000 },
});
const emit = defineEmits(['update:open', 'close']);
const haptic = useHaptics();

const STYLES = {
    success:     { border: 'border-emerald-500/30', bg: 'from-emerald-900/30 to-[#0b0b10]', icon: 'wc-check',        iconColor: 'text-emerald-300' },
    info:        { border: 'border-blue-500/30',    bg: 'from-blue-900/30 to-[#0b0b10]',    icon: 'wc-sparkle-magic', iconColor: 'text-blue-300' },
    warning:     { border: 'border-amber-500/30',   bg: 'from-amber-900/30 to-[#0b0b10]',   icon: 'wc-bell',         iconColor: 'text-amber-300' },
    destructive: { border: 'border-wc-border',      bg: 'from-wc-bg-secondary to-[#0b0b10]', icon: 'wc-trash',       iconColor: 'text-wc-text-tertiary' },
    referral:    { border: 'border-emerald-500/30', bg: 'from-emerald-900/30 to-[#0b0b10]', icon: 'wc-share',        iconColor: 'text-emerald-300' },
    ticket:      { border: 'border-wc-accent/30',   bg: 'from-[#1a0b0b] to-[#0b0b10]',     icon: 'wc-ticket',       iconColor: 'text-wc-accent-soft' },
    measurements:{ border: 'border-emerald-500/30', bg: 'from-emerald-900/30 to-[#0b0b10]', icon: 'wc-check',        iconColor: 'text-emerald-300' },
    profile:     { border: 'border-wc-border',      bg: 'from-wc-bg-secondary to-[#0b0b10]', icon: 'wc-check',      iconColor: 'text-wc-accent-soft' },
    video:       { border: 'border-blue-500/30',    bg: 'from-blue-900/30 to-[#0b0b10]',   icon: 'wc-video',        iconColor: 'text-blue-300' },
};

function getStyle() { return STYLES[props.preset] || STYLES.success; }

const POSITION_CLASS = {
    'bottom-right':  'bottom-4 right-4',
    'top-center':    'top-4 left-1/2 -translate-x-1/2',
    'bottom-center': 'bottom-4 left-1/2 -translate-x-1/2',
};

let timer = null;
watch(() => props.open, (val) => {
    clearTimeout(timer);
    if (val) {
        haptic.light();
        if (props.autoDismissMs > 0) {
            timer = setTimeout(() => emit('update:open', false), props.autoDismissMs);
        }
    }
}, { immediate: true });

onBeforeUnmount(() => clearTimeout(timer));
</script>

<template>
  <Teleport to="body">
    <Transition name="wc-toast">
      <div
        v-if="open"
        :class="['fixed z-[90] w-[320px] max-w-[calc(100vw-32px)]', POSITION_CLASS[position] || POSITION_CLASS['bottom-right']]"
        role="status"
        aria-live="polite"
      >
        <div
          :class="[
            'rounded-2xl bg-gradient-to-br border p-3 flex items-start gap-3 animate-wc-rise shadow-lg',
            getStyle().bg,
            getStyle().border,
          ]"
        >
          <div
            :class="[
              'h-11 w-11 rounded-xl border flex items-center justify-center shrink-0',
              getStyle().border,
            ]"
          >
            <WcIcon :name="data.icon || getStyle().icon" :size="24" :class="getStyle().iconColor" />
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-2">
              <p class="text-sm font-bold truncate">{{ data.title }}</p>
              <span
                v-if="data.badge"
                class="rounded-full px-1.5 py-0.5 text-[8px] font-bold flex items-center gap-0.5 shrink-0 bg-wc-bg-tertiary border border-wc-border"
              >
                {{ data.badge }}
              </span>
            </div>
            <p v-if="data.message" class="text-[11px] text-wc-text-secondary mt-0.5">{{ data.message }}</p>
            <slot />
          </div>
          <button
            @click="emit('update:open', false)"
            type="button"
            class="text-wc-text-tertiary hover:text-wc-text shrink-0"
            aria-label="Cerrar"
          >
            <i class="ph ph-x" style="font-size:14px"></i>
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.wc-toast-enter-active,
.wc-toast-leave-active {
    transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.wc-toast-enter-from {
    opacity: 0;
    transform: translateY(20px);
}
.wc-toast-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>
