<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { useRoute, RouterLink } from 'vue-router';

const props = defineProps({
  tabs: { type: Array, required: true },
});

const emit = defineEmits(['tab-change']);

const route = useRoute();
const navRef = ref(null);
const indicatorStyle = ref({ left: '0px', width: '0px', opacity: 0 });

const activeIdx = computed(() =>
  props.tabs.findIndex(t => t.routeName === route.name)
);

async function positionIndicator() {
  await nextTick();
  if (activeIdx.value < 0 || !navRef.value) {
    indicatorStyle.value = { left: '0px', width: '0px', opacity: 0 };
    return;
  }
  const tabEls = navRef.value.querySelectorAll('[data-tab-item]');
  const tab = tabEls[activeIdx.value];
  if (!tab) return;
  const navRect = navRef.value.getBoundingClientRect();
  const tabRect = tab.getBoundingClientRect();
  const capsuleW = 56;
  const left = tabRect.left - navRect.left + (tabRect.width - capsuleW) / 2;
  indicatorStyle.value = { left: left + 'px', width: capsuleW + 'px', opacity: 1 };
}

onMounted(() => {
  positionIndicator();
  window.addEventListener('resize', positionIndicator, { passive: true });
});
onBeforeUnmount(() => window.removeEventListener('resize', positionIndicator));

watch(() => route.name, () => {
  positionIndicator();
});
</script>

<template>
  <nav class="bottom-nav-ios" role="navigation" aria-label="Navegación principal">
    <div ref="navRef" class="bottom-nav-ios-inner">
      <span class="tab-indicator" :style="indicatorStyle" aria-hidden="true" />
      <RouterLink
        v-for="(tab, i) in tabs"
        :key="tab.routeName"
        :to="tab.to"
        data-tab-item
        :class="[
          'flex-1 flex flex-col items-center justify-center gap-0.5 py-1.5 cursor-pointer relative z-[1] transition active:scale-[0.88]',
          activeIdx === i ? 'text-wc-accent' : 'text-[var(--color-wc-text-3)]',
        ]"
        style="transition-duration: var(--t-tap); transition-timing-function: var(--ease-spring-ios);"
        :aria-current="activeIdx === i ? 'page' : undefined"
        :aria-label="tab.label + (tab.badge ? ', ' + tab.badge + ' pendientes' : '')"
        @click="emit('tab-change', tab)"
      >
        <span
          v-if="tab.badge && tab.badge > 0"
          class="absolute top-1 right-[calc(50%-18px)] min-w-[14px] h-3.5 px-1 rounded-full bg-wc-accent font-display text-[8px] font-bold text-white flex items-center justify-center"
          style="box-shadow: 0 0 0 1.5px var(--color-wc-bg);"
        >
          {{ tab.badge > 9 ? '9+' : tab.badge }}
        </span>
        <svg
          class="h-[22px] w-[22px]"
          :stroke-width="activeIdx === i ? 2 : 1.5"
          stroke="currentColor"
          fill="none"
          viewBox="0 0 24 24"
          aria-hidden="true"
          v-html="tab.iconSvgPath"
        />
        <span class="text-[9px] font-semibold tracking-wider uppercase">{{ tab.label }}</span>
      </RouterLink>
    </div>
  </nav>
</template>
