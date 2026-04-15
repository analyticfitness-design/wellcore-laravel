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
            'Accept': 'application/json',
        },
    });

    // Attach token on every request and handle Content-Type
    instance.interceptors.request.use((config) => {
        if (authStore.token) {
            config.headers.Authorization = `Bearer ${authStore.token}`;
        }
        // Let axios set the Content-Type automatically for FormData (multipart/form-data with boundary).
        // Only set JSON content-type for non-FormData requests.
        if (!(config.data instanceof FormData)) {
            config.headers['Content-Type'] = 'application/json';
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
