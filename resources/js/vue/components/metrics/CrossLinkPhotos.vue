<script setup>
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
defineProps({
  photos: { type: Array, default: () => [] },
  // Array of { thumbnail_url } — first 3 shown
});
</script>

<template>
  <div class="crosslink">
    <div class="crosslink-inner">
      <!-- Thumb strip (max 3) -->
      <div v-if="photos.length" class="crosslink-thumbs" aria-hidden="true">
        <div
          v-for="(photo, i) in photos.slice(0, 3)"
          :key="i"
          class="crosslink-thumb"
          :style="photo.thumbnail_url ? `background-image:url(${photo.thumbnail_url})` : ''"
        ></div>
        <div v-if="photos.length > 3" class="crosslink-thumb crosslink-thumb--more">
          +{{ photos.length - 3 }}
        </div>
      </div>
      <div v-else class="crosslink-icon" aria-hidden="true">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
        </svg>
      </div>

      <div class="crosslink-text">
        <p class="crosslink-title">{{ t('client_progress.metrics_photos_title') }}</p>
        <p class="crosslink-sub">{{ photos.length ? (photos.length > 1 ? t('client_progress.metrics_photos_count_plural', { n: photos.length }) : t('client_progress.metrics_photos_count_singular', { n: photos.length })) : t('client_progress.metrics_photos_empty') }}</p>
      </div>

      <a href="/client/photos" class="crosslink-cta">
        {{ t('client_progress.metrics_photos_view') }}
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
        </svg>
      </a>
    </div>
  </div>
</template>

<style scoped>
.crosslink {
  border-radius: 16px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-secondary);
  padding: 16px 20px;
  margin-bottom: 24px;
}
.crosslink-inner {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
}
.crosslink-thumbs {
  display: flex;
  gap: 4px;
  flex-shrink: 0;
}
.crosslink-thumb {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: var(--color-wc-bg-tertiary);
  background-size: cover;
  background-position: center;
  border: 1px solid var(--color-wc-border);
}
.crosslink-thumb--more {
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-mono);
  font-size: 11px;
  font-weight: 600;
  color: var(--color-wc-text-secondary);
}
.crosslink-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: var(--color-wc-bg-tertiary);
  border: 1px solid var(--color-wc-border);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-wc-text-tertiary);
  flex-shrink: 0;
}
.crosslink-text { flex: 1; min-width: 0; }
.crosslink-title {
  font-size: 13px;
  font-weight: 600;
  color: var(--color-wc-text);
  margin: 0;
}
.crosslink-sub {
  font-size: 12px;
  color: var(--color-wc-text-tertiary);
  margin-top: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.crosslink-cta {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  min-height: 44px;
  border-radius: 10px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-tertiary);
  color: var(--color-wc-text);
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  white-space: nowrap;
  transition: background .12s;
  flex-shrink: 0;
}
.crosslink-cta:hover { background: var(--color-wc-bg); }
</style>
