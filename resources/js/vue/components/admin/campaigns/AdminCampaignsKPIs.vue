<script setup>
import { formatCOP, formatNumber } from '../../../composables/useFormat';

const props = defineProps({
    spendMes:        { type: Number, default: 0 },
    conversionesMes: { type: Number, default: 0 },
    roasPromedio:    { type: Number, default: 0 },
    cplPromedio:     { type: Number, default: 0 },
    loading:         { type: Boolean, default: false },
});

function roasColor(roas) {
    if (roas >= 3) return 'var(--color-wc-green-text)';
    if (roas >= 1) return 'var(--color-wc-amber-text)';
    return 'var(--color-wc-red-text)';
}
</script>

<template>
    <div class="campaigns-kpis">
        <!-- Spend mes -->
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">SPEND MES</span>
                <div class="kpi-icon">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                </div>
            </div>
            <div v-if="loading" class="kpi-value-skeleton"></div>
            <p v-else class="kpi-value">{{ formatCOP(spendMes) }}</p>
            <p class="kpi-sub">invertido en ads</p>
        </div>

        <!-- Conversiones -->
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">CONVERSIONES</span>
                <div class="kpi-icon" style="background: var(--color-wc-green-soft); color: var(--color-wc-green-text);">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <div v-if="loading" class="kpi-value-skeleton"></div>
            <p v-else class="kpi-value">{{ formatNumber(conversionesMes) }}</p>
            <p class="kpi-sub">ventas del mes</p>
        </div>

        <!-- ROAS -->
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">ROAS</span>
                <div class="kpi-icon" :style="{ background: 'rgba(16,185,129,0.08)', color: roasColor(roasPromedio) }">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </div>
            </div>
            <div v-if="loading" class="kpi-value-skeleton"></div>
            <p v-else class="kpi-value" :style="{ color: roasColor(roasPromedio) }">{{ roasPromedio.toFixed(2) }}x</p>
            <p class="kpi-sub">retorno sobre inversión</p>
        </div>

        <!-- CPL -->
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">CPL PROMEDIO</span>
                <div class="kpi-icon" style="background: var(--color-wc-amber-soft); color: var(--color-wc-amber-text);">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
            </div>
            <div v-if="loading" class="kpi-value-skeleton"></div>
            <p v-else class="kpi-value">{{ formatCOP(cplPromedio) }}</p>
            <p class="kpi-sub">costo por lead</p>
        </div>
    </div>
</template>

<style scoped>
.campaigns-kpis {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

@media (min-width: 768px) {
    .campaigns-kpis { grid-template-columns: repeat(4, 1fr); }
}

.kpi-card {
    background: rgba(17,17,17,0.7);
    border: 1px solid var(--color-wc-border);
    border-radius: 14px;
    padding: 16px;
}

.kpi-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.kpi-label {
    font-family: var(--font-mono);
    font-size: 8px;
    letter-spacing: 0.2em;
    color: var(--color-wc-text-tertiary);
    text-transform: uppercase;
}

.kpi-icon {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: var(--color-wc-red-soft);
    color: var(--color-wc-accent);
    flex-shrink: 0;
}

.kpi-value {
    font-family: var(--font-data);
    font-size: 22px;
    font-weight: 700;
    color: var(--color-wc-text);
    line-height: 1.1;
    margin: 0 0 4px;
    font-feature-settings: 'tnum' 1;
}

.kpi-value-skeleton {
    height: 28px;
    background: var(--color-wc-bg-tertiary);
    border-radius: 6px;
    animation: page-pulse 1.5s ease-in-out infinite;
    margin-bottom: 4px;
}

.kpi-sub {
    font-family: var(--font-sans);
    font-size: 11px;
    color: var(--color-wc-text-tertiary);
    margin: 0;
}

@keyframes page-pulse {
    0%, 100% { opacity: 0.6; }
    50%       { opacity: 0.9; }
}

@media (prefers-reduced-motion: reduce) {
    .kpi-value-skeleton { animation: none !important; }
}
</style>
