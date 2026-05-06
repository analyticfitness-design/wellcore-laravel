<template>
  <div class="wp-skeleton" data-testid="plan-skeleton">
    <div class="skel-card">
      <div class="skel-line" style="width:35%"></div>
      <div class="skel-line" style="width:75%;height:22px"></div>
      <div class="skel-line" style="width:50%"></div>
    </div>
    <div class="skel-card skel-card--row">
      <div class="skel-block"></div>
      <div class="skel-grow">
        <div class="skel-line" style="width:80%"></div>
        <div class="skel-line" style="width:55%;height:10px"></div>
      </div>
    </div>
    <div class="skel-card skel-card--row">
      <div class="skel-block"></div>
      <div class="skel-grow">
        <div class="skel-line" style="width:70%"></div>
        <div class="skel-line" style="width:45%;height:10px"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
// PlanSkeleton — loading state placeholder.
// CSS lines 875-894 del HTML V2.1 (.skel-*).
</script>

<style scoped>
.wp-skeleton {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.skel-card {
  border-radius: 16px;
  border: 1px solid var(--wc-border);
  background: var(--wc-bg-secondary);
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.skel-card--row {
  flex-direction: row;
  align-items: center;
}
.skel-grow {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.skel-line {
  height: 14px;
  border-radius: 4px;
  background: linear-gradient(90deg, var(--wc-bg-tertiary) 0%, rgba(255, 255, 255, 0.06) 50%, var(--wc-bg-tertiary) 100%);
  background-size: 200% 100%;
  animation: skel-pulse 1.6s ease-in-out infinite;
}
.skel-block {
  width: 64px;
  height: 64px;
  flex-shrink: 0;
  border-radius: 12px;
  background: linear-gradient(90deg, var(--wc-bg-tertiary) 0%, rgba(255, 255, 255, 0.06) 50%, var(--wc-bg-tertiary) 100%);
  background-size: 200% 100%;
  animation: skel-pulse 1.6s ease-in-out infinite;
}
@keyframes skel-pulse {
  0%, 100% { background-position: 200% 0; }
  50% { background-position: -200% 0; }
}
:global(.light) .skel-line,
:global(.light) .skel-block {
  background: linear-gradient(90deg, rgba(0, 0, 0, 0.04) 0%, rgba(0, 0, 0, 0.08) 50%, rgba(0, 0, 0, 0.04) 100%);
  background-size: 200% 100%;
}
</style>
