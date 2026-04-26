import { ref, computed, onMounted } from 'vue';

const isImpersonatingByAdmin = ref(false);
const isImpersonatingByCoach = ref(false);

function refreshFromStorage() {
    isImpersonatingByAdmin.value = localStorage.getItem('wc_impersonating') === 'true';
    isImpersonatingByCoach.value = localStorage.getItem('wc_impersonating_by_coach') === '1';
}

let listenerAttached = false;

export function useImpersonation() {
    const anyImpersonation = computed(() => isImpersonatingByAdmin.value || isImpersonatingByCoach.value);

    onMounted(() => {
        refreshFromStorage();
        if (!listenerAttached) {
            window.addEventListener('storage', refreshFromStorage);
            listenerAttached = true;
        }
    });

    return {
        isImpersonatingByAdmin,
        isImpersonatingByCoach,
        anyImpersonation,
        refresh: refreshFromStorage,
    };
}
