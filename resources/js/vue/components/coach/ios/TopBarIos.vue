<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  variant: {
    type: String,
    default: 'mobile',
    validator: v => ['mobile', 'desktop'].includes(v),
  },
  dateLabel: { type: String, default: '' },
  showUpdated: { type: Boolean, default: true },
  urgentCount: { type: Number, default: 0 },
  unreadNotifs: { type: Number, default: 0 },
  coachInitial: { type: String, default: 'C' },
  coachName: { type: String, default: '' },
});

const { t } = useI18n();

const emit = defineEmits([
  'menu-open',
  'bell-click',
  'theme-toggle',
  'avatar-click',
  'action-pill-click',
  'cmd-k-open',
  'actions-btn-click',
]);

const isDesktop = computed(() => props.variant === 'desktop');

const scrolled = ref(false);

function onScroll() {
  scrolled.value = window.scrollY > 10;
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));
</script>

<template>
  <header
    v-if="!isDesktop"
    :class="['topbar-ios', { scrolled }]"
    role="banner"
  >
    <button
      class="relative h-9 w-9 inline-flex flex-col items-center justify-center gap-[5px] rounded-[10px] border border-[var(--b1)] active:scale-[0.94] transition"
      style="background: var(--s1); transition-duration: var(--t-tap); transition-timing-function: var(--ease-spring-ios);"
      :aria-label="urgentCount > 0 ? t('coach_nav.menu_alerts', { n: urgentCount }) : t('coach_nav.open_menu')"
      @click="emit('menu-open')"
    >
      <span
        v-if="urgentCount > 0"
        class="absolute top-1.5 right-1.5 w-1.5 h-1.5 rounded-full bg-wc-accent pulse-dot"
        style="box-shadow: 0 0 0 1.5px var(--color-wc-bg);"
      />
      <span class="block w-3.5 h-px rounded" style="background: var(--color-wc-text-2);" />
      <span class="block w-3.5 h-px rounded" style="background: var(--color-wc-text-2);" />
      <span class="block w-3.5 h-px rounded" style="background: var(--color-wc-text-2);" />
    </button>

    <span
      v-if="dateLabel"
      class="flex-1 font-display text-[13px] font-medium tracking-wider uppercase text-[var(--color-wc-text-3)]"
    >
      {{ dateLabel }}
    </span>

    <div class="flex items-center gap-2">
      <div v-if="showUpdated" class="updated-indicator">
        <span class="dot pulse-green" />
        <span>{{ t('coach_nav.now') }}</span>
      </div>

      <button
        class="relative h-9 w-9 inline-flex items-center justify-center rounded-[10px] border border-[var(--b1)] active:scale-[0.92] transition"
        style="background: var(--s1); transition-duration: var(--t-tap); transition-timing-function: var(--ease-spring-ios);"
        :aria-label="unreadNotifs > 0 ? t('coach_nav.notifications_unread', { n: unreadNotifs }) : t('coach_nav.view_notifications')"
        @click="emit('bell-click')"
      >
        <span v-if="unreadNotifs > 0" class="notif-badge">{{ unreadNotifs > 9 ? '9+' : unreadNotifs }}</span>
        <slot name="bell-icon">
          <svg class="h-4 w-4" style="stroke: var(--color-wc-text-2);" fill="none" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
          </svg>
        </slot>
      </button>

      <slot name="language" />

      <button
        class="h-9 w-9 inline-flex items-center justify-center rounded-[10px] border border-[var(--b1)] active:scale-[0.92] transition"
        style="background: var(--s1); transition-duration: var(--t-tap); transition-timing-function: var(--ease-spring-ios);"
        :aria-label="t('coach_nav.change_mode')"
        @click="emit('theme-toggle')"
      >
        <svg class="h-4 w-4 dark:hidden" style="stroke: var(--color-wc-text-2);" fill="none" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
        </svg>
        <svg class="h-4 w-4 hidden dark:block" style="stroke: var(--color-wc-text-2);" fill="none" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
        </svg>
      </button>

      <button
        class="ring-conic-accent w-9 h-9 active:scale-[0.92] transition"
        style="transition-duration: var(--t-tap); transition-timing-function: var(--ease-spring-ios);"
        :aria-label="coachName ? t('coach_nav.profile_of', { name: coachName }) : t('coach_nav.profile')"
        @click="emit('avatar-click')"
      >
        <span class="absolute inset-[2px] rounded-full bg-wc-accent flex items-center justify-center font-display text-[13px] font-semibold tracking-wider text-white z-[1]">
          {{ coachInitial }}
        </span>
      </button>

      <button
        class="action-pill"
        :aria-label="t('coach_nav.quick_actions')"
        @click="emit('action-pill-click')"
      >
        <svg class="h-3 w-3 stroke-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        <span>{{ t('coach_nav.actions') }}</span>
      </button>
    </div>
  </header>

  <header
    v-else
    :class="['topbar-ios topbar-ios-desktop', { scrolled }]"
    role="banner"
  >
    <div class="flex items-center gap-3 flex-1">
      <span class="font-display text-[13px] font-medium tracking-wider uppercase text-[var(--color-wc-text-3)]">
        {{ dateLabel }}
      </span>
      <div v-if="showUpdated" class="updated-indicator">
        <span class="dot pulse-green" />
        <span>{{ t('coach_nav.updated_ago') }}</span>
      </div>
    </div>

    <button
      class="search-bar flex items-center gap-2 w-[280px] h-9 px-3 border border-[var(--b1)] rounded-[10px] hover:border-[var(--b2)] transition"
      style="background: var(--s1); transition-duration: var(--t-tap);"
      :aria-label="t('coach_nav.search_or_run_cmd')"
      @click="emit('cmd-k-open')"
    >
      <svg class="h-3.5 w-3.5" style="stroke: var(--color-wc-text-3);" fill="none" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
      </svg>
      <span class="flex-1 text-left text-[13px] text-[var(--color-wc-text-3)]">{{ t('coach_nav.search_or_cmd_k') }}</span>
      <kbd class="flex items-center gap-1 px-1.5 py-0.5 text-[10px] text-[var(--color-wc-text-3)] border border-[var(--b1)] rounded" style="background: var(--s2);">
        <span>⌘</span><span>K</span>
      </kbd>
    </button>

    <div class="flex items-center gap-2.5">
      <button
        class="relative h-9 w-9 inline-flex items-center justify-center rounded-[9px] border border-[var(--b1)] hover:border-[var(--b2)] active:scale-[0.93] transition"
        style="background: var(--s1); transition-duration: var(--t-tap); transition-timing-function: var(--ease-spring-ios);"
        :aria-label="unreadNotifs > 0 ? t('coach_nav.notifications_unread', { n: unreadNotifs }) : t('coach_nav.view_notifications')"
        @click="emit('bell-click')"
      >
        <span v-if="unreadNotifs > 0" class="notif-badge">{{ unreadNotifs > 9 ? '9+' : unreadNotifs }}</span>
        <slot name="bell-icon">
          <svg class="h-4 w-4" style="stroke: var(--color-wc-text-2);" fill="none" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
          </svg>
        </slot>
      </button>

      <slot name="language" />

      <button
        class="h-9 w-9 inline-flex items-center justify-center rounded-[9px] border border-[var(--b1)] hover:border-[var(--b2)] active:scale-[0.93] transition"
        style="background: var(--s1); transition-duration: var(--t-tap);"
        :aria-label="t('coach_nav.change_mode')"
        @click="emit('theme-toggle')"
      >
        <svg class="h-4 w-4 dark:hidden" style="stroke: var(--color-wc-text-2);" fill="none" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
        </svg>
        <svg class="h-4 w-4 hidden dark:block" style="stroke: var(--color-wc-text-2);" fill="none" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
        </svg>
      </button>

      <button
        class="ring-conic-accent w-9 h-9 hover:scale-[1.05] transition"
        style="transition-duration: var(--t-tap); transition-timing-function: var(--ease-spring-ios);"
        :aria-label="coachName ? t('coach_nav.profile_of', { name: coachName }) : t('coach_nav.profile')"
        @click="emit('avatar-click')"
      >
        <span class="absolute inset-[2px] rounded-full bg-wc-accent flex items-center justify-center font-display text-[13px] font-bold text-white z-[1]">
          {{ coachInitial }}
        </span>
      </button>
      <span v-if="coachName" class="text-[13px] font-semibold text-[var(--color-wc-text-2)] hidden xl:inline">{{ coachName }}</span>

      <button
        class="action-pill"
        :aria-label="t('coach_nav.quick_actions')"
        @click="emit('actions-btn-click')"
      >
        <svg class="h-3 w-3 stroke-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
        </svg>
        <span>{{ t('coach_nav.actions') }}</span>
        <svg class="h-2.5 w-2.5 stroke-white/70" fill="none" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
      </button>
    </div>
  </header>
</template>
