/**
 * Helpers de fechas timezone-safe para WellCore.
 * Siempre usa la hora LOCAL del navegador (NO UTC).
 *
 * Uso:
 *   import { useDate } from '../composables/useDate';
 *   const { localDateStr, isToday, timeAgo, formatDate } = useDate();
 *   const today = localDateStr(); // "2026-04-21"
 */

function pad(n) {
  return n < 10 ? `0${n}` : `${n}`;
}

/**
 * Devuelve 'YYYY-MM-DD' usando hora LOCAL del navegador.
 * NO usar `d.toISOString().split('T')[0]` porque eso devuelve UTC.
 */
export function localDateStr(d = new Date()) {
  if (!(d instanceof Date) || isNaN(d.getTime())) {
    d = new Date();
  }
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

export function isToday(dateStr) {
  if (!dateStr) return false;
  return dateStr === localDateStr();
}

const MESES_ES = [
  'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
  'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
];

/**
 * "21 abril 2026"
 */
export function formatDate(isoDate) {
  if (!isoDate) return '';
  const d = isoDate instanceof Date ? isoDate : new Date(isoDate);
  if (isNaN(d.getTime())) return '';
  return `${d.getDate()} ${MESES_ES[d.getMonth()]} ${d.getFullYear()}`;
}

/**
 * "hace 5m", "hace 2h", "hace 3d".
 */
export function timeAgo(isoDate) {
  if (!isoDate) return '';
  const d = isoDate instanceof Date ? isoDate : new Date(isoDate);
  if (isNaN(d.getTime())) return '';

  const diffMs = Date.now() - d.getTime();
  const diffSec = Math.floor(diffMs / 1000);

  if (diffSec < 10) return 'ahora';
  if (diffSec < 60) return `hace ${diffSec}s`;

  const diffMin = Math.floor(diffSec / 60);
  if (diffMin < 60) return `hace ${diffMin}m`;

  const diffHr = Math.floor(diffMin / 60);
  if (diffHr < 24) return `hace ${diffHr}h`;

  const diffDay = Math.floor(diffHr / 24);
  if (diffDay < 30) return `hace ${diffDay}d`;

  const diffMonth = Math.floor(diffDay / 30);
  if (diffMonth < 12) return `hace ${diffMonth}mes`;

  const diffYear = Math.floor(diffMonth / 12);
  return `hace ${diffYear}a`;
}

export function useDate() {
  return {
    localDateStr,
    isToday,
    formatDate,
    timeAgo,
  };
}
