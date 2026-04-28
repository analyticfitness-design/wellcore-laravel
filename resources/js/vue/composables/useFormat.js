/**
 * useFormat — helpers de formato para Colombia (es-CO).
 *
 * Usado por componentes admin v2. Mantiene la coherencia entre AdminTopBar,
 * AdminGreeting, AdminHeroMetrics, AdminPulseCharts, AdminActivityFeed.
 *
 * Patrón: funciones puras (no reactive). Se importan directo donde se necesitan.
 *  import { formatCOP, formatNumber } from '@/composables/useFormat';
 *  formatCOP(199800) === '$199.800'
 */

const COP = new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 });

export function formatCOP(value) {
    const n = Number(value || 0);
    return '$' + COP.format(n);
}

export function formatCOPShort(value) {
    const n = Number(value || 0);
    if (n === 0) return '$0';
    if (Math.abs(n) >= 1_000_000) return `$${(n / 1_000_000).toFixed(1)}M`;
    if (Math.abs(n) >= 1_000) return `$${(n / 1_000).toFixed(0)}k`;
    return `$${n}`;
}

export function formatNumber(value) {
    return COP.format(Number(value || 0));
}

export function formatPercent(value, digits = 1) {
    const n = Number(value || 0);
    return `${n.toFixed(digits)}%`;
}

/**
 * Diferencia delta con signo + decimal: 12.5 → "+12.5%"
 */
export function formatDelta(value, digits = 1) {
    const n = Number(value || 0);
    const sign = n >= 0 ? '+' : '';
    return `${sign}${n.toFixed(digits)}%`;
}

const DATE_FMT = new Intl.DateTimeFormat('es-CO', {
    weekday: 'long', day: 'numeric', month: 'short',
});
const TIME_FMT = new Intl.DateTimeFormat('es-CO', {
    hour: '2-digit', minute: '2-digit', hour12: false,
});

export function formatDateLong(d) {
    return DATE_FMT.format(d instanceof Date ? d : new Date(d));
}

export function formatTimeShort(d) {
    return TIME_FMT.format(d instanceof Date ? d : new Date(d));
}

/**
 * "hace 2 minutos" / "hace 1 hora" — receives Date or ISO string.
 * Para timestamps recientes (<1m, 1-59m, 1-23h, 1-30d, 1-12m, anos).
 */
const SECONDS = { min: 60, hr: 3600, day: 86400, mes: 2592000, ano: 31536000 };
export function formatRelativeTime(date) {
    if (!date) return '';
    const d = date instanceof Date ? date : new Date(date);
    const diff = (Date.now() - d.getTime()) / 1000;
    if (diff < 60) return 'hace unos segundos';
    if (diff < SECONDS.hr)  return `hace ${Math.floor(diff / SECONDS.min)} min`;
    if (diff < SECONDS.day) return `hace ${Math.floor(diff / SECONDS.hr)} h`;
    if (diff < SECONDS.mes) return `hace ${Math.floor(diff / SECONDS.day)} d`;
    if (diff < SECONDS.ano) return `hace ${Math.floor(diff / SECONDS.mes)} meses`;
    return `hace ${Math.floor(diff / SECONDS.ano)} anos`;
}

/**
 * Composable wrapper para uso con `const { formatCOP } = useFormat()`.
 * Útil cuando un consumidor prefiere desestructurar desde un objeto.
 */
export function useFormat() {
    return {
        formatCOP,
        formatCOPShort,
        formatNumber,
        formatPercent,
        formatDelta,
        formatDateLong,
        formatTimeShort,
        formatRelativeTime,
    };
}
