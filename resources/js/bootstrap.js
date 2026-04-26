import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Suprimir toast cuando exercise_name llega vacío al backend (Bug A SP-1).
// El backend retorna 204 defensivo, pero si por alguna razón llega un 422,
// lo silenciamos para que no aparezca el pop-up "El campo ejercicio es obligatorio".
window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error?.response?.status;
        const url = error?.config?.url || '';
        const msg = error?.response?.data?.errors?.exercise_name?.[0];

        if (
            status === 422
            && msg?.includes('ejercicio')
            && /\/api\/v\/(training|social|rise)\//.test(url)
        ) {
            console.warn('[WC] Suppressed exercise_name 422 from', url);
            return Promise.resolve(error.response);
        }

        return Promise.reject(error);
    }
);
