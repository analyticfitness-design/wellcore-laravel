import { onUnmounted } from 'vue';

export function useCancellableFetch() {
    let aborter = null;

    function cancel() {
        aborter?.abort();
        aborter = null;
    }

    function getSignal() {
        cancel();
        aborter = new AbortController();
        return aborter.signal;
    }

    onUnmounted(cancel);

    return { cancel, getSignal };
}
