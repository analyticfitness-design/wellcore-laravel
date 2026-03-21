/**
 * WellCore Push Notification Subscription
 *
 * Handles:
 * - Service worker registration for push
 * - Push subscription creation via VAPID
 * - Sending subscription details to the server
 * - Permission state management
 * - Unsubscribe support
 */

let pushRegistration = null;

export function initPushSubscription() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        console.debug('[WellCore Push] Push not supported in this browser');
        return;
    }

    // Register push service worker
    navigator.serviceWorker.register('/sw-push.js').then((registration) => {
        pushRegistration = registration;
        console.debug('[WellCore Push] SW registered');

        // Check current subscription state
        registration.pushManager.getSubscription().then((subscription) => {
            if (subscription) {
                console.debug('[WellCore Push] Already subscribed');
                // Dispatch state event for UI components
                window.dispatchEvent(new CustomEvent('wellcore:push-state', {
                    detail: { subscribed: true, permission: Notification.permission },
                }));
                return;
            }

            // Not yet subscribed — dispatch state for UI
            window.dispatchEvent(new CustomEvent('wellcore:push-state', {
                detail: { subscribed: false, permission: Notification.permission },
            }));
        });

        // Listen for user-triggered subscription
        window.addEventListener('wellcore:subscribe-push', () => {
            subscribePush(registration);
        });

        // Listen for user-triggered unsubscription
        window.addEventListener('wellcore:unsubscribe-push', () => {
            unsubscribePush(registration);
        });

    }).catch((err) => {
        console.warn('[WellCore Push] SW registration failed:', err);
        window.dispatchEvent(new CustomEvent('wellcore:push-state', {
            detail: { subscribed: false, permission: 'unavailable', error: err.message },
        }));
    });
}

/**
 * Subscribe the browser to push notifications via VAPID.
 */
async function subscribePush(registration) {
    const vapidKey = document.querySelector('meta[name="vapid-public-key"]')?.content;
    if (!vapidKey) {
        console.warn('[WellCore Push] No VAPID key found in meta tag');
        window.dispatchEvent(new CustomEvent('wellcore:push-state', {
            detail: { subscribed: false, permission: Notification.permission, error: 'no_vapid_key' },
        }));
        return;
    }

    try {
        // Request notification permission if not already granted
        if (Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                console.debug('[WellCore Push] Permission denied');
                window.dispatchEvent(new CustomEvent('wellcore:push-state', {
                    detail: { subscribed: false, permission: 'denied' },
                }));
                return;
            }
        }

        if (Notification.permission === 'denied') {
            console.debug('[WellCore Push] Permission previously denied');
            window.dispatchEvent(new CustomEvent('wellcore:push-state', {
                detail: { subscribed: false, permission: 'denied' },
            }));
            return;
        }

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
            window.dispatchEvent(new CustomEvent('wellcore:push-state', {
                detail: { subscribed: true, permission: 'granted' },
            }));
        } else {
            console.error('[WellCore Push] Server rejected subscription:', response.status);
            window.dispatchEvent(new CustomEvent('wellcore:push-state', {
                detail: { subscribed: false, permission: 'granted', error: 'server_error' },
            }));
        }
    } catch (err) {
        console.error('[WellCore Push] Subscribe failed:', err);
        window.dispatchEvent(new CustomEvent('wellcore:push-state', {
            detail: { subscribed: false, permission: Notification.permission, error: err.message },
        }));
    }
}

/**
 * Unsubscribe the browser from push notifications.
 */
async function unsubscribePush(registration) {
    try {
        const subscription = await registration.pushManager.getSubscription();
        if (!subscription) {
            window.dispatchEvent(new CustomEvent('wellcore:push-state', {
                detail: { subscribed: false, permission: Notification.permission },
            }));
            return;
        }

        // Notify server to deactivate
        await fetch('/api/push/unsubscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ endpoint: subscription.endpoint }),
        });

        // Unsubscribe locally
        await subscription.unsubscribe();

        console.log('[WellCore Push] Unsubscribed successfully');
        window.dispatchEvent(new CustomEvent('wellcore:push-state', {
            detail: { subscribed: false, permission: Notification.permission },
        }));
    } catch (err) {
        console.error('[WellCore Push] Unsubscribe failed:', err);
    }
}

/**
 * Convert URL-safe base64 string to Uint8Array (for VAPID applicationServerKey).
 */
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = atob(base64);
    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
}
