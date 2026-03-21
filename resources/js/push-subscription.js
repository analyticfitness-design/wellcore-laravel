/**
 * WellCore Push Notification Subscription
 */
export function initPushSubscription() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        return;
    }

    // Register push service worker
    navigator.serviceWorker.register('/sw-push.js').then((registration) => {
        console.log('[WellCore Push] SW registered');

        // Check if already subscribed
        registration.pushManager.getSubscription().then((subscription) => {
            if (subscription) {
                console.log('[WellCore Push] Already subscribed');
                return;
            }

            // Listen for user-triggered subscription
            window.addEventListener('wellcore:subscribe-push', () => {
                subscribePush(registration);
            });
        });
    }).catch((err) => {
        console.warn('[WellCore Push] SW registration failed:', err);
    });
}

async function subscribePush(registration) {
    const vapidKey = document.querySelector('meta[name="vapid-public-key"]')?.content;
    if (!vapidKey) {
        console.warn('[WellCore Push] No VAPID key found');
        return;
    }

    try {
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidKey),
        });

        // Send subscription to server
        const response = await fetch('/api/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({
                endpoint: subscription.endpoint,
                p256dh: btoa(String.fromCharCode(...new Uint8Array(subscription.getKey('p256dh')))),
                auth: btoa(String.fromCharCode(...new Uint8Array(subscription.getKey('auth')))),
            }),
        });

        if (response.ok) {
            console.log('[WellCore Push] Subscribed successfully');
        }
    } catch (err) {
        console.error('[WellCore Push] Subscribe failed:', err);
    }
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = atob(base64);
    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
}
