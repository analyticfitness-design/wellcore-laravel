<script setup lang="ts">
interface Props {
  name: string;
  initials: string;
  ringColor: 'red' | 'gold' | 'green' | 'blue' | 'purple' | 'gray';
  hasNew: boolean;
  pulsoId?: number | null;
  size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  pulsoId: null,
});

const emit = defineEmits<{ click: [] }>();

const ringClasses: Record<string, string> = {
  red:    'bg-gradient-to-tr from-wc-accent via-orange-500 to-yellow-400',
  gold:   'bg-gradient-to-tr from-yellow-400 via-amber-500 to-yellow-300',
  green:  'bg-gradient-to-tr from-green-500 via-emerald-400 to-teal-400',
  blue:   'bg-gradient-to-tr from-blue-500 via-cyan-400 to-sky-400',
  purple: 'bg-gradient-to-tr from-purple-600 via-violet-500 to-purple-400',
  gray:   'bg-zinc-700',
};

const sizeClasses = {
  sm: { wrap: 'w-12 h-12', ring: 'p-[2px]', avatar: 'w-10 h-10 text-xs' },
  md: { wrap: 'w-16 h-16', ring: 'p-[2.5px]', avatar: 'w-[52px] h-[52px] text-sm' },
  lg: { wrap: 'w-20 h-20', ring: 'p-[3px]', avatar: 'w-[68px] h-[68px] text-base' },
};
</script>

<template>
  <button
    :class="['flex flex-col items-center gap-1.5 focus:outline-none', !hasNew && 'opacity-70']"
    @click="emit('click')"
  >
    <div :class="[sizeClasses[size].wrap, 'relative rounded-full']">
      <!-- Animated ring -->
      <div
        :class="[
          'absolute inset-0 rounded-full',
          ringClasses[ringColor] ?? ringClasses.gray,
          hasNew ? 'animate-spin-slow' : '',
        ]"
      ></div>
      <!-- Avatar inner -->
      <div :class="['absolute inset-0 rounded-full', sizeClasses[size].ring]">
        <div
          :class="[
            sizeClasses[size].avatar,
            'flex items-center justify-center rounded-full bg-wc-bg-secondary font-bold uppercase text-wc-text',
          ]"
        >
          {{ initials }}
        </div>
      </div>
    </div>
    <span class="max-w-[56px] truncate text-center text-[10px] text-wc-text-secondary">
      {{ name.split(' ')[0] }}
    </span>
  </button>
</template>
