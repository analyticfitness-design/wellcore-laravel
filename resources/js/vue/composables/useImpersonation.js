import { ref, computed, onMounted } from 'vue';

const isImpersonatingByAdmin = ref(false);
const isImpersonatingByCoach = ref(false);
const isImpersonatingCoach = ref(false);
const chain = ref([]);

function refreshFromStorage() {
    isImpersonatingByAdmin.value = localStorage.getItem('wc_impersonating') === 'true';
    isImpersonatingByCoach.value = localStorage.getItem('wc_impersonating_by_coach') === '1';

    let parsed = [];
    try {
        parsed = JSON.parse(localStorage.getItem('wc_impersonation_chain') || '[]');
    } catch {
        parsed = [];
    }
    chain.value = Array.isArray(parsed) ? parsed : [];

    // Active when any chain entry is targeting an admin (i.e. superadmin -> coach).
    isImpersonatingCoach.value = chain.value.some(e => e.target_type === 'admin');
}

let listenerAttached = false;

export function useImpersonation() {
    const anyImpersonation = computed(() =>
        isImpersonatingByAdmin.value
        || isImpersonatingByCoach.value
        || isImpersonatingCoach.value
        || chain.value.length > 0
    );

    const topOfChain = computed(() =>
        chain.value.length ? chain.value[chain.value.length - 1] : null
    );
    const rootUserName = computed(() => localStorage.getItem('wc_root_user_name') || '');

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
        isImpersonatingCoach,
        anyImpersonation,
        chain,
        topOfChain,
        rootUserName,
        refresh: refreshFromStorage,
    };
}
