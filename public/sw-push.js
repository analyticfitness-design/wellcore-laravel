/**
 * WellCore Push Notification Service Worker
 *
 * Handles incoming push events with rich notification payloads:
 * - Structured data (title, body, icon, badge, tag, actions)
 * - Click routing to specific URLs
 * - Notification grouping via tags
 * - Dismiss action handling
 */

// ─── PUSH EVENT ────────────────────────────────────────────────────────
self.addEventListener('push', (event) => {
    let data = {
        title: 'WellCore Fitness',
        body: 'Tienes una nueva notificacion',
        icon: '/images/logo-dark.png',
        badge: '/icons/icon-192x192.png',
        tag: 'wellcore-general',
        data: { url: '/client/dashboard', type: 'general' },
        actions: [],
    };

    if (event.data) {
        try {
            const incoming = event.data.json();
            data = { ...data, ...incoming };
        } catch (e) {
            // Fallback: treat payload as plain text body
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: data.icon || '/images/logo-dark.png',
        badge: data.badge || '/icons/icon-192x192.png',
        tag: data.tag || 'wellcore-general',
        data: data.data || { url: '/client/dashboard', type: 'general' },
        actions: data.actions || [],
        vibrate: [200, 100, 200],
        renotify: true, // Re-alert even if same tag replaces existing
        requireInteraction: false, // Auto-dismiss after a while
        silent: false,
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'WellCore Fitness', options)
    );
});

// ─── NOTIFICATION CLICK ────────────────────────────────────────────────
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    // If the user clicked "dismiss" action, just close
    if (event.action === 'dismiss') {
        return;
    }

    // Determine the URL to navigate to
    const url = event.notification.data?.url || '/client/dashboard';
    const fullUrl = new URL(url, self.location.origin).href;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            // Try to find an existing WellCore window/tab
            for (const client of windowClients) {
                if (client.url.startsWith(self.location.origin) && 'focus' in client) {
                    // Navigate the existing tab to the target URL
                    client.navigate(fullUrl);
                    return client.focus();
                }
            }
            // No existing window — open a new one
            return clients.openWindow(fullUrl);
        })
    );
});

// ─── NOTIFICATION CLOSE (for analytics) ────────────────────────────────
self.addEventListener('notificationclose', (event) => {
    // Could be used to track dismiss rates in the future
    const type = event.notification.data?.type || 'unknown';
    // Silently log — no network call needed for now
    console.debug('[WellCore Push] Notification dismissed:', type);
});
