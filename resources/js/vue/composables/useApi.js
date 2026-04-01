import axios from 'axios';
import { useAuthStore } from '../stores/auth';

/**
 * Creates a pre-configured axios instance that automatically attaches
 * the Bearer token from the auth store to every request.
 *
 * Usage:
 *   import { useApi } from '../composables/useApi';
 *   const api = useApi();
 *   const { data } = await api.get('/api/v/client/dashboard');
 */
export function useApi() {
    const authStore = useAuthStore();

    const instance = axios.create({
        baseURL: '',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    });

    // Attach token on every request
    instance.interceptors.request.use((config) => {
        if (authStore.token) {
            config.headers.Authorization = `Bearer ${authStore.token}`;
        }
        return config;
    });

    // Handle 401 responses globally — clear auth and redirect to login
    instance.interceptors.response.use(
        (response) => response,
        (error) => {
            if (error.response && error.response.status === 401) {
                authStore.clearAuth();
                window.location.href = '/login';
            }
            return Promise.reject(error);
        }
    );

    return instance;
}
