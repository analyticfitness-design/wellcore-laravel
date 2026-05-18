import { createI18n } from 'vue-i18n';

/**
 * vue-i18n setup para WellCore.
 *
 * Bootstrap:
 *   - El layout Blade inyecta `window.__wcInitialLocale` y opcionalmente
 *     `window.__wcMessages` (un objeto con namespaces pre-cargados) para evitar
 *     FOUC. Si no están presentes, se inicia en 'es' con messages vacíos y se
 *     carga via fetch().
 *
 * Locales soportados: 'es' (default, fallback) y 'en' (en-US native voice).
 */

const SUPPORTED = ['es', 'en'];
const DEFAULT_LOCALE = 'es';

function readInitialLocale() {
    if (typeof window === 'undefined') return DEFAULT_LOCALE;

    const fromWindow = window.__wcInitialLocale;
    if (SUPPORTED.includes(fromWindow)) return fromWindow;

    const fromMeta = document.querySelector('meta[name="wc-locale"]')?.getAttribute('content');
    if (SUPPORTED.includes(fromMeta)) return fromMeta;

    return DEFAULT_LOCALE;
}

function readInitialMessages() {
    if (typeof window === 'undefined') return {};
    const bootstrap = window.__wcMessages;
    return bootstrap && typeof bootstrap === 'object' ? bootstrap : {};
}

const initialLocale = readInitialLocale();
const bootstrapMessages = readInitialMessages();

export const i18n = createI18n({
    legacy: false,
    locale: initialLocale,
    fallbackLocale: DEFAULT_LOCALE,
    globalInjection: true,
    silentTranslationWarn: true,
    silentFallbackWarn: true,
    missingWarn: false,
    fallbackWarn: false,
    messages: {
        [initialLocale]: bootstrapMessages,
    },
});

const loadedLocales = new Set([initialLocale]);

/**
 * Lazy-load del bundle de traducciones desde /api/v/translations/{locale}.
 * Idempotente: si ya está cargado, retorna inmediatamente.
 */
export async function loadLocaleMessages(locale) {
    if (!SUPPORTED.includes(locale)) {
        console.warn(`[i18n] locale not supported: ${locale}`);
        return false;
    }

    if (loadedLocales.has(locale)) return true;

    try {
        const response = await fetch(`/api/v/translations/${locale}`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            console.warn(`[i18n] failed to load ${locale}: ${response.status}`);
            return false;
        }

        const payload = await response.json();
        i18n.global.setLocaleMessage(locale, payload.messages ?? {});
        loadedLocales.add(locale);
        return true;
    } catch (err) {
        console.warn('[i18n] fetch error', err);
        return false;
    }
}

/**
 * Cambia el locale global. Si el bundle no estaba pre-cargado, lo descarga.
 * Devuelve el locale final efectivo.
 */
export async function setI18nLocale(locale) {
    if (!SUPPORTED.includes(locale)) return i18n.global.locale.value;

    await loadLocaleMessages(locale);
    i18n.global.locale.value = locale;

    if (typeof document !== 'undefined') {
        document.documentElement.setAttribute('lang', locale);
    }

    return locale;
}

export const SUPPORTED_LOCALES = SUPPORTED;
