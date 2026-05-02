<script setup>
const TYPE_OPTIONS = [
    { value: 'signup',   label: 'Inscripciones', color: 'blue' },
    { value: 'payment',  label: 'Pagos',         color: 'green' },
    { value: 'checkin',  label: 'Check-ins',     color: 'amber' },
    { value: 'message',  label: 'Mensajes',      color: 'blue' },
    { value: 'training', label: 'Entrenamientos', color: 'amber' },
];

const props = defineProps({
    activeTypes: { type: Array, required: true },
});

const emit = defineEmits(['update:activeTypes']);

function toggleType(type) {
    const current = [...props.activeTypes];
    const idx = current.indexOf(type);
    if (idx === -1) {
        current.push(type);
    } else if (current.length > 1) {
        // Mantener al menos 1 tipo activo
        current.splice(idx, 1);
    }
    emit('update:activeTypes', current);
}
</script>

<template>
    <div class="feed-filters" role="group" aria-label="Filtros por tipo de evento">
        <button
            v-for="opt in TYPE_OPTIONS"
            :key="opt.value"
            type="button"
            :aria-pressed="activeTypes.includes(opt.value)"
            :class="['filter-pill', `filter-pill--${opt.color}`, { 'filter-pill--active': activeTypes.includes(opt.value) }]"
            @click="toggleType(opt.value)"
        >
            {{ opt.label }}
        </button>
    </div>
</template>

<style scoped>
.feed-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.filter-pill {
    height: var(--tap-comfort, 48px);
    padding: 0 12px;
    border-radius: var(--r-pill, 999px);
    border: 1px solid var(--c-border);
    background: transparent;
    cursor: pointer;
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-3);
    transition: background 0.15s var(--ease-out), color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
    white-space: nowrap;
}
.filter-pill:hover {
    color: var(--c-text-2);
    border-color: rgba(255,255,255,0.16);
}

.filter-pill--active.filter-pill--blue  { background: rgba(59,130,246,0.10);  color: #60A5FA; border-color: #60A5FA; }
.filter-pill--active.filter-pill--green { background: rgba(16,185,129,0.10); color: #34D399; border-color: #34D399; }
.filter-pill--active.filter-pill--amber { background: rgba(245,158,11,0.10);  color: #FCD34D; border-color: #FCD34D; }
</style>
