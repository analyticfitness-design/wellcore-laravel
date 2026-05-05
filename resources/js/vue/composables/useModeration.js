import { useApi } from './useApi';
import { useHaptics } from './useHaptics';
import { useToast } from './useToast';

/**
 * useModeration — actions de moderación para el coach (pin/unpin/delete/make-official).
 * No cachea: cada acción dispara request directo al backend.
 * Optimistic UI lo maneja el component caller (revert si falla).
 */
export function useModeration() {
    const api = useApi();
    const haptics = useHaptics();
    const toast = useToast();

    async function pinPost(postId, hours = 168, note = null) {
        try {
            const res = await api.post(`/api/v/coach/posts/${postId}/pin`, { hours, note });
            haptics.success();
            toast.success('Post fijado.');
            return res.data;
        } catch (err) {
            haptics.error();
            toast.apiError(err, 'No pudimos fijar el post.');
            throw err;
        }
    }

    async function unpinPost(postId) {
        try {
            const res = await api.post(`/api/v/coach/posts/${postId}/unpin`);
            haptics.light();
            return res.data;
        } catch (err) {
            haptics.error();
            toast.apiError(err, 'No pudimos desfijar.');
            throw err;
        }
    }

    async function deletePost(postId, reason) {
        try {
            const res = await api.delete(`/api/v/coach/posts/${postId}`, { data: { reason } });
            haptics.medium();
            toast.success('Post eliminado.');
            return res.data;
        } catch (err) {
            haptics.error();
            toast.apiError(err, 'No pudimos eliminar.');
            throw err;
        }
    }

    async function makeOfficial(postId) {
        try {
            const res = await api.post(`/api/v/coach/posts/${postId}/make-official`);
            haptics.success();
            toast.success('Marcado como Coach Pick.');
            return res.data;
        } catch (err) {
            haptics.error();
            toast.apiError(err, 'No pudimos marcar como oficial.');
            throw err;
        }
    }

    async function reportPost(postId, reason, detail = null) {
        try {
            const res = await api.post(`/api/v/community/posts/${postId}/report`, {
                reason, reason_detail: detail,
            });
            return res.data;
        } catch (err) {
            toast.apiError(err, 'No pudimos enviar el reporte.');
            throw err;
        }
    }

    return { pinPost, unpinPost, deletePost, makeOfficial, reportPost };
}
