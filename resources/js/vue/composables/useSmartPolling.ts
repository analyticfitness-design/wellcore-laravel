import { onMounted, onBeforeUnmount } from 'vue';

interface UseSmartPollingOptions {
    min?: number;
    max?: number;
    start?: number;
}

export function useSmartPolling(
    callback: () => Promise<{ messageCount: number }>,
    { min = 10_000, max = 120_000, start = 30_000 }: UseSmartPollingOptions = {}
) {
    let interval = start;
    let timer: ReturnType<typeof setTimeout> | null = null;
    let visible = !document.hidden;
    let prevMessageCount = 0;

    function tick() {
        if (!visible) {
            timer = setTimeout(tick, interval);
            return;
        }
        callback().then(({ messageCount }) => {
            const gotNew = messageCount > prevMessageCount;
            interval = gotNew ? Math.max(min, interval / 2) : Math.min(max, interval * 1.5);
            prevMessageCount = messageCount;
        }).finally(() => {
            timer = setTimeout(tick, interval);
        });
    }

    function onVisibility() { visible = !document.hidden; }

    onMounted(() => {
        document.addEventListener('visibilitychange', onVisibility);
        timer = setTimeout(tick, interval);
    });

    onBeforeUnmount(() => {
        if (timer) clearTimeout(timer);
        document.removeEventListener('visibilitychange', onVisibility);
    });

    return {
        stop() {
            if (timer) clearTimeout(timer);
        },
    };
}
