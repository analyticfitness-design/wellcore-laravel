import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const key = import.meta.env.VITE_REVERB_APP_KEY;

// Guard: si no hay key inyectada en el build (ej. dev local sin .env, o build de
// alguien sin acceso a credenciales), no instanciamos Echo. Pusher tira un
// "Uncaught You must pass your app key when you instantiate Pusher" cuando se
// llama new Pusher(undefined) y eso ensucia la consola en producción.
if (key) {
    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/api/v/broadcasting/auth',
        auth: {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Authorization': 'Bearer ' + (localStorage.getItem('wc_token') ?? ''),
            },
        },
    });
} else {
    console.warn('[Echo] VITE_REVERB_APP_KEY missing — broadcasting disabled');
}
