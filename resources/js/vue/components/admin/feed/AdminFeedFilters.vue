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
    height: 28px;
    padding: 0 10px;
    border-radius: 6px;
    border: 1px solid var(--color-wc-border);
    background: transparent;
    cursor: pointer;
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    transition: background 0.15s var(--ease-out), color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
    white-space: nowrap;
}
.filter-pill:hover {
    color: var(--color-wc-text-secondary);
    border-color: var(--color-wc-border-2);
}

.filter-pill--active.filter-pill--blue  { background: var(--color-wc-blue-soft);  color: var(--color-wc-blue-text);  border-color: var(--color-wc-blue-text); }
.filter-pill--active.filter-pill--green { background: var(--color-wc-green-soft); color: var(--color-wc-green-text); border-color: var(--color-wc-green-text); }
.filter-pill--active.filter-pill--amber { background: var(--color-wc-amber-soft); color: var(--color-wc-amber-text); border-color: var(--color-wc-amber-text); }
</style>
