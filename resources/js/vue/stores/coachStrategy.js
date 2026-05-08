import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { coachStrategyApi } from '../api/coachStrategy';

export const useCoachStrategyStore = defineStore('coachStrategy', () => {
    const profile = ref(null);
    const isLoadingProfile = ref(false);
    const profileError = ref(null);

    const currentDrop = ref(null);
    const isLoadingDrop = ref(false);
    const dropError = ref(null);

    const history = ref([]);
    const historyMeta = ref({ total: 0, current_page: 1 });
    const isLoadingHistory = ref(false);
    const historyError = ref(null);

    const isProfileComplete = computed(() => profile.value?.is_complete === true);
    const hasCurrentDrop = computed(() => currentDrop.value !== null);

    async function fetchProfile() {
        isLoadingProfile.value = true;
        profileError.value = null;
        try {
            profile.value = await coachStrategyApi.getProfile();
        } catch (e) {
            profileError.value = e?.response?.data?.message ?? 'Error cargando perfil';
            throw e;
        } finally {
            isLoadingProfile.value = false;
        }
    }

    async function submitProfile(payload) {
        profile.value = await coachStrategyApi.submitProfile(payload);
        return profile.value;
    }

    async function saveProfileDraft(patch) {
        profile.value = await coachStrategyApi.saveDraft(patch);
        return profile.value;
    }

    async function fetchCurrentDrop() {
        isLoadingDrop.value = true;
        dropError.value = null;
        try {
            currentDrop.value = await coachStrategyApi.getCurrentDrop();
        } catch (e) {
            // 403 PROFILE_INCOMPLETE no es un "error" de drop — es estado esperado
            if (e?.response?.status === 403 && e?.response?.data?.code === 'PROFILE_INCOMPLETE') {
                currentDrop.value = null;
            } else {
                dropError.value = e?.response?.data?.message ?? 'Error cargando drop';
                throw e;
            }
        } finally {
            isLoadingDrop.value = false;
        }
    }

    async function fetchHistory(page = 1) {
        isLoadingHistory.value = true;
        historyError.value = null;
        try {
            const res = await coachStrategyApi.getHistory(page);
            history.value = res.data;
            historyMeta.value = res.meta;
        } catch (e) {
            historyError.value = e?.response?.data?.message ?? 'Error cargando historial';
        } finally {
            isLoadingHistory.value = false;
        }
    }

    async function markPiecePublished(pieceKey, url, notes) {
        if (!currentDrop.value) return;

        // Optimistic update
        const idx = currentDrop.value.pieces.findIndex((p) => p.piece_key === pieceKey);
        const prev = idx >= 0 ? { ...currentDrop.value.pieces[idx] } : null;
        const optimistic = {
            piece_type: prev?.piece_type ?? detectType(pieceKey),
            piece_key: pieceKey,
            state: 'published',
            published_url: url ?? null,
            state_changed_at: new Date().toISOString(),
        };
        if (idx >= 0) currentDrop.value.pieces[idx] = optimistic;
        else currentDrop.value.pieces.push(optimistic);

        try {
            const fresh = await coachStrategyApi.publishPiece(currentDrop.value.id, pieceKey, { url, notes });
            const i = currentDrop.value.pieces.findIndex((p) => p.piece_key === pieceKey);
            if (i >= 0) currentDrop.value.pieces[i] = fresh;
            return fresh;
        } catch (e) {
            // Rollback
            if (prev) {
                const i = currentDrop.value.pieces.findIndex((p) => p.piece_key === pieceKey);
                if (i >= 0) currentDrop.value.pieces[i] = prev;
            } else {
                currentDrop.value.pieces = currentDrop.value.pieces.filter((p) => p.piece_key !== pieceKey);
            }
            throw e;
        }
    }

    async function markPieceSkipped(pieceKey) {
        if (!currentDrop.value) return false;
        try {
            const fresh = await coachStrategyApi.skipPiece(currentDrop.value.id, pieceKey);
            const idx = currentDrop.value.pieces.findIndex((p) => p.piece_key === pieceKey);
            if (idx >= 0) currentDrop.value.pieces[idx] = fresh;
            else currentDrop.value.pieces.push(fresh);
            return true;
        } catch (e) {
            return false;
        }
    }

    async function markPieceInProgress(pieceKey) {
        if (!currentDrop.value) return false;
        try {
            const fresh = await coachStrategyApi.inProgressPiece(currentDrop.value.id, pieceKey);
            const idx = currentDrop.value.pieces.findIndex((p) => p.piece_key === pieceKey);
            if (idx >= 0) currentDrop.value.pieces[idx] = fresh;
            else currentDrop.value.pieces.push(fresh);
            return true;
        } catch (e) {
            return false;
        }
    }

    function detectType(pieceKey) {
        if (pieceKey.startsWith('reel_')) return 'reel';
        if (pieceKey.startsWith('story_')) return 'story';
        if (pieceKey.startsWith('phase_')) return 'checklist_phase';
        return 'reel';
    }

    function reset() {
        profile.value = null;
        currentDrop.value = null;
        history.value = [];
        historyMeta.value = { total: 0, current_page: 1 };
    }

    return {
        // state
        profile, isLoadingProfile, profileError,
        currentDrop, isLoadingDrop, dropError,
        history, historyMeta, isLoadingHistory, historyError,
        // computed
        isProfileComplete, hasCurrentDrop,
        // actions
        fetchProfile, submitProfile, saveProfileDraft,
        fetchCurrentDrop, fetchHistory,
        markPiecePublished, markPieceSkipped, markPieceInProgress,
        reset,
    };
});
