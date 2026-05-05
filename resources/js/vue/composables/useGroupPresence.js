import { reactive } from 'vue';

/**
 * useGroupPresence — composable que filtra el channel `online-users` por rol.
 * Singleton: una sola subscripción Echo PresenceChannel para toda la app.
 * Reset hook al logout/impersonation.
 */

const onlineMap = reactive({
    client: new Set(),
    coach: new Set(),
    admin: new Set(),
});
let presenceChannel = null;
let initialized = false;

function addUser(user) {
    const type = user.user_type || user.type;
    const id = user.id || user.user_id;
    if (!type || !id) return;
    if (onlineMap[type]) onlineMap[type].add(parseInt(id, 10));
}

function removeUser(user) {
    const type = user.user_type || user.type;
    const id = user.id || user.user_id;
    if (!type || !id) return;
    if (onlineMap[type]) onlineMap[type].delete(parseInt(id, 10));
}

export function useGroupPresence() {
    function init() {
        if (initialized || !window.Echo) return;
        initialized = true;
        try {
            presenceChannel = window.Echo.join('online-users')
                .here((users) => users.forEach(u => addUser(u)))
                .joining((user) => addUser(user))
                .leaving((user) => removeUser(user))
                .error((err) => {
                    // eslint-disable-next-line no-console
                    console.error('[useGroupPresence] presence error', err);
                });
        } catch (e) {
            // eslint-disable-next-line no-console
            console.error('[useGroupPresence] init failed', e);
            initialized = false;
        }
    }

    function isOnline(type, id) {
        return onlineMap[type]?.has(parseInt(id, 10)) ?? false;
    }

    function countByRole(type) {
        return onlineMap[type]?.size ?? 0;
    }

    return { onlineMap, init, isOnline, countByRole };
}

export function resetGroupPresence() {
    if (presenceChannel && window.Echo) {
        try { window.Echo.leave('online-users'); } catch (e) { /* swallow */ }
    }
    presenceChannel = null;
    initialized = false;
    onlineMap.client.clear();
    onlineMap.coach.clear();
    onlineMap.admin.clear();
}
