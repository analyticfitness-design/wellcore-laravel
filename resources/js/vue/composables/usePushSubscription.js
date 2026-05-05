import { ref } from 'vue';
import { useApi } from './useApi';

/**
 * usePushSubscription — browser permission + service worker push subscription para coach.
 * No singleton: cada componente tiene su propio state local de permission/subscription.
 * Backend dedup por (coach_id, endpoint) — re-suscribirse es seguro.
 */

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = atob(base64);
    const arr = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; i++) arr[i] = rawData.charCodeAt(i);
    return arr;
}

export function usePushSubscription() {
    const api = useApi();
    const permission = ref(
        typeof Notification !== 'undefined' ? Notification.permission : 'default'
    );
    const subscription = ref(null);

    async function request() {
        if (typeof Notification === 'undefined') {
            throw new Error('Notifications no soportadas en este navegador.');
        }
        const result = await Notification.requestPermission();
        permission.value = result;
        if (result === 'granted') {
            await subscribe();
        }
        return result;
    }

    async function subscribe() {
        if (!('serviceWorker' in navigator)) {
            throw new Error('Service Worker no soportado.');
        }
        const reg = await navigator.serviceWorker.ready;
        const vapidKey = window.__WC_VAPID_PUBLIC_KEY;
        if (!vapidKey) {
            throw new Error('VAPID public key no configurada.');
        }
        const sub = await reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidKey),
        });
        subscription.value = sub;
        const json = sub.toJSON();
        const res = await api.post('/api/v/coach/push/subscribe', {
            endpoint: json.endpoint,
            keys: { p256dh: json.keys.p256dh, auth: json.keys.auth },
            user_agent: navigator.userAgent.slice(0, 255),
        });
        return res.data;
    }

    async function unsubscribe() {
        if (!subscription.value) {
            const reg = await navigator.serviceWorker.ready;
            subscription.value = await reg.pushManager.getSubscription();
        }
        if (subscription.value) {
            await subscription.value.unsubscribe();
        }
    }

    return { permission, subscription, request, subscribe, unsubscribe };
}
