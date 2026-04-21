<script setup>
/**
 * LevelUpCelebration — adaptador de compatibilidad hacia BentoCelebration.
 *
 * Los layouts siguen usando:
 *   <LevelUpCelebration :event="levelUp" @close="clearLevelUp" />
 *
 * Este componente delega al sistema singleton useCelebration para que
 * BentoCelebration :global="true" (montado en ClientLayout/RiseLayout) lo renderice.
 *
 * Prop event: { from, to, totalXP, xpGained }
 */
import { watch } from 'vue';
import { useCelebration } from '../composables/useCelebration';
import { LEVEL_UP_QUOTES } from '../constants/coach-quotes';

const props = defineProps({
    event: { type: Object, default: null },
});
const emit = defineEmits(['close']);

const { celebrate } = useCelebration();

watch(() => props.event, (evt) => {
    if (!evt) return;
    const quote = LEVEL_UP_QUOTES[(evt.to - 1) % LEVEL_UP_QUOTES.length];
    celebrate('level-up', {
        title: 'Subiste',
        subtitle: 'de nivel',
        status: `Ahora eres nivel ${evt.to}`,
        metadata: `Lvl ${evt.from} → Lvl ${evt.to}`,
        hero: {
            label: 'Nivel',
            value: String(evt.to),
            description: '',
            icon: 'wc:level-ring',
        },
        stats: [
            { icon: 'wc:xp-bolt', label: 'XP Total', value: evt.totalXP.toLocaleString(), span: 2 },
            { icon: 'ph-fill ph-plus-circle', label: 'Ganados', value: `+${evt.xpGained.toLocaleString()}`, span: 2 },
        ],
        quote,
        share: { enabled: true, preset: 'level-up' },
        cta: 'Continuar',
    });
    // Notifica al layout que puede limpiar el evento
    setTimeout(() => emit('close'), 400);
}, { immediate: true });
</script>

<template>
  <!-- Renderizado delegado a <BentoCelebration :global="true"> en el layout -->
</template>
