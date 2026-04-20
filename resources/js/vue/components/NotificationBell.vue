<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const props = defineProps({
    endpoint: {
        type: String,
        default: '/api/v/client/notifications',
    },
    pollInterval: {
        type: Number,
        default: 90000,
    },
});

const authStore = useAuthStore();
const router = useRouter();

const notifications = ref([]);
const unreadCount = ref(0);
const showDropdown = ref(false);
let pollTimer = null;
// Unique id so multiple bells don't collide on click-outside handling
const wrapperId = `notif-dropdown-wrapper-${Math.random().toString(36).slice(2, 9)}`;

const hasNotifications = computed(() => notifications.value.length > 0);
const badgeText = computed(() => (unreadCount.value > 9 ? '9+' : unreadCount.value));

function authHeaders() {
    return { Authorization: `Bearer ${authStore.token}` };
}

async function fetchNotifications() {
    try {
        const res = await fetch(props.endpoint, { headers: authHeaders() });
        if (!res.ok) return;
        const data = await res.json();
        notifications.value = data.notifications ?? [];
        // Support both snake_case (coach/admin) and camelCase (client) responses
        unreadCount.value = data.unread_count ?? data.unreadCount ?? 0;
    } catch {
        // silently ignore network errors
    }
}

async function markAsRead(id) {
    await fetch(`${props.endpoint}/${id}/read`, {
        method: 'POST',
        headers: authHeaders(),
    });
    await fetchNotifications();
}

async function markAllAsRead() {
    await fetch(`${props.endpoint}/read-all`, {
        method: 'POST',
        headers: authHeaders(),
    });
    await fetchNotifications();
}

function handleNotificationClick(notification) {
    markAsRead(notification.id);
    showDropdown.value = false;
    if (notification.link) {
        // If link is a SPA path, use Vue Router; external → full nav
        const link = notification.link;
        const isExternal = /^https?:\/\//i.test(link);
        if (isExternal) {
            setTimeout(() => { window.location.href = link; }, 100);
        } else {
            setTimeout(() => { router.push(link).catch(() => { window.location.href = link; }); }, 100);
        }
    }
}

function handleClickOutside(e) {
    const el = document.getElementById(wrapperId);
    if (el && !el.contains(e.target)) {
        showDropdown.value = false;
    }
}

function startPolling() {
    pollTimer = setInterval(fetchNotifications, props.pollInterval);
}

onMounted(async () => {
    await fetchNotifications();
    startPolling();
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    clearInterval(pollTimer);
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
  <div :id="wrapperId" class="relative">

    <!-- Bell button -->
    <button
      @click="showDropdown = !showDropdown"
      type="button"
      class="relative flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
      title="Notificaciones"
      :aria-label="unreadCount > 0 ? `Ver notificaciones, ${unreadCount} sin leer` : 'Ver notificaciones'"
      :aria-expanded="showDropdown"
      aria-haspopup="true"
    >
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
      </svg>

      <!-- Unread badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-wc-accent px-1 text-[10px] font-bold text-white"
      >
        {{ badgeText }}
      </span>
    </button>

    <!-- Dropdown -->
    <Transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="opacity-0 scale-95 -translate-y-1"
      enter-to-class="opacity-100 scale-100 translate-y-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100 scale-100 translate-y-0"
      leave-to-class="opacity-0 scale-95 -translate-y-1"
    >
      <div
        v-if="showDropdown"
        class="absolute right-0 top-full mt-2 w-80 rounded-xl border border-wc-border bg-wc-bg-secondary shadow-xl z-50"
      >
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-wc-border px-4 py-3">
          <h3 class="text-sm font-semibold text-wc-text font-data tracking-wide">Notificaciones</h3>
          <button
            v-if="unreadCount > 0"
            @click="markAllAsRead"
            class="text-xs text-wc-accent hover:text-wc-accent/80 font-medium transition-colors"
          >
            Marcar todas como leidas
          </button>
        </div>

        <!-- List -->
        <div class="max-h-96 overflow-y-auto">
          <template v-if="hasNotifications">
            <div
              v-for="notification in notifications"
              :key="notification.id"
              @click="handleNotificationClick(notification)"
              class="flex items-start gap-3 border-b border-wc-border px-4 py-3 hover:bg-wc-bg-tertiary cursor-pointer transition-colors last:border-b-0"
            >
              <!-- Unread dot -->
              <div class="mt-1.5 shrink-0">
                <span
                  :class="[
                    'block h-2 w-2 rounded-full',
                    notification.read_at ? 'bg-wc-border' : 'bg-wc-accent',
                  ]"
                ></span>
              </div>

              <!-- Content -->
              <div class="min-w-0 flex-1">
                <p
                  :class="[
                    'text-sm font-medium text-wc-text leading-snug',
                    notification.read_at ? 'opacity-60' : '',
                  ]"
                >
                  {{ notification.title }}
                </p>
                <p v-if="notification.body" class="mt-0.5 text-xs text-wc-text-secondary line-clamp-2 leading-relaxed">
                  {{ notification.body }}
                </p>
                <p v-if="notification.created_at" class="mt-1 text-[10px] text-wc-text-tertiary font-data">
                  {{ notification.created_at }}
                </p>
              </div>
            </div>
          </template>

          <!-- Empty state -->
          <div v-else class="flex flex-col items-center justify-center py-10 px-4 text-center">
            <svg class="h-10 w-10 text-wc-border mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
            <p class="text-sm text-wc-text-secondary">Sin notificaciones</p>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
