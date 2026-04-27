<script setup>
defineProps({
    status: { type: String, required: true },
});

const STATUS_STYLES = {
    pending:     { bg: 'bg-zinc-500/10',   border: 'border-zinc-500/30',   text: 'text-zinc-400',   dot: 'bg-zinc-500/60',   label: 'Pendiente' },
    generating:  { bg: 'bg-amber-500/10',  border: 'border-amber-500/30',  text: 'text-amber-400',  dot: 'bg-amber-500/60',  label: 'Generando' },
    in_review:   { bg: 'bg-blue-500/10',   border: 'border-blue-500/30',   text: 'text-blue-400',   dot: 'bg-blue-500/60',   label: 'En revisión' },
    approved:    { bg: 'bg-violet-500/10', border: 'border-violet-500/30', text: 'text-violet-400', dot: 'bg-violet-500/60', label: 'Aprobado' },
    ready:       { bg: 'bg-green-500/10',  border: 'border-green-500/30',  text: 'text-green-400',  dot: 'bg-green-500/60',  label: 'Listo' },
    in_progress: { bg: 'bg-teal-500/10',   border: 'border-teal-500/30',   text: 'text-teal-400',   dot: 'bg-teal-500/60',   label: 'En progreso' },
    completed:   { bg: 'bg-emerald-500/10',border: 'border-emerald-500/30',text: 'text-emerald-400',dot: 'bg-emerald-500/60',label: 'Completado' },
    archived:    { bg: 'bg-zinc-700/10',   border: 'border-zinc-700/30',   text: 'text-zinc-500',   dot: 'bg-zinc-700/60',   label: 'Archivado' },
};
</script>

<template>
  <span :class="[
    'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-mono uppercase tracking-wide',
    STATUS_STYLES[status]?.bg, STATUS_STYLES[status]?.border, STATUS_STYLES[status]?.text
  ]">
    <span class="h-1.5 w-1.5 rounded-full" :class="STATUS_STYLES[status]?.dot"></span>
    {{ STATUS_STYLES[status]?.label ?? status }}
  </span>
</template>
