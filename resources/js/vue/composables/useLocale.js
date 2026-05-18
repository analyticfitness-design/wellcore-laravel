import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useLocaleStore } from '../stores/locale';
import { SUPPORTED_LOCALES } from '../i18n';

/**
 * useLocale — composable de fachada sobre el Pinia store + vue-i18n.
 *
 * Devuelve estado reactivo del idioma + acciones para cambiarlo.
 * Honra `locale_locked`: si está bloqueado por superadmin, `canChange` = false.
 */
export function useLocale() {
    const store = useLocaleStore();
    const { t, locale: i18nLocale } = useI18n();

    const locale = computed({
        get: () => store.locale,
        set: (value) => {
            store.changeLocale(value);
        },
    });

    const canChange = computed(() => !store.localeLocked);
    const isLocked = computed(() => store.localeLocked);
    const isSaving = computed(() => store.saving);
    const error = computed(() => store.lastError);

    const isEnglish = computed(() => store.locale === 'en');
    const isSpanish = computed(() => store.locale === 'es');

    async function setLocale(value) {
        return store.changeLocale(value);
    }

    function toggle() {
        const next = store.locale === 'en' ? 'es' : 'en';
        return store.changeLocale(next);
    }

    return {
        locale,
        i18nLocale,
        supportedLocales: SUPPORTED_LOCALES,
        canChange,
        isLocked,
        isSaving,
        isEnglish,
        isSpanish,
        error,
        setLocale,
        toggle,
        t,
    };
}
