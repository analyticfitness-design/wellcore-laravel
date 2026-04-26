import { ref, computed } from 'vue';
import { useApi } from './useApi';

export interface PaymentProof {
    id: number;
    client_name: string;
    client_email: string;
    plan: string;
    amount: number | null;
    payment_method: string | null;
    status: 'pendiente' | 'aprobado' | 'rechazado' | 'expirado';
    coach_note: string | null;
    rejection_reason: string | null;
    file_url: string | null;
    created_at: string;
}

export interface ProofFilters {
    status?: string;
    from_date?: string;
    to_date?: string;
}

export function usePaymentProofs() {
    const api = useApi();

    const proofs = ref<PaymentProof[]>([]);
    const loading = ref(false);
    const submitting = ref(false);
    const error = ref<string | null>(null);

    const pendingCount = computed(
        () => proofs.value.filter((p) => p.status === 'pendiente').length
    );

    async function fetchProofs(filters?: ProofFilters) {
        loading.value = true;
        error.value = null;
        try {
            const params: Record<string, string> = {};
            if (filters?.status) params.status = filters.status;
            if (filters?.from_date) params.from_date = filters.from_date;
            if (filters?.to_date) params.to_date = filters.to_date;

            const response = await api.get('/api/v/coach/payment-proofs', { params });
            proofs.value = response.data.data ?? response.data ?? [];
        } catch (e: any) {
            error.value = 'No se pudieron cargar los comprobantes.';
            proofs.value = [];
        } finally {
            loading.value = false;
        }
    }

    async function submitProof(formData: FormData): Promise<void> {
        submitting.value = true;
        error.value = null;
        try {
            await api.post('/api/v/coach/payment-proofs', formData);
        } finally {
            submitting.value = false;
        }
    }

    return {
        proofs,
        loading,
        submitting,
        error,
        pendingCount,
        fetchProofs,
        submitProof,
    };
}
