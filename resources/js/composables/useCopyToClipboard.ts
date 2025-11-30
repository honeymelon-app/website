import { ref } from 'vue';

/**
 * Composable for copying text to clipboard with feedback state.
 *
 * @param resetDelay - Delay in ms before resetting `copied` state (default: 2000)
 * @returns Object with copy function and reactive copied state
 *
 * @example
 * const { copy, copied } = useCopyToClipboard();
 * await copy('Hello World');
 * // copied.value is true for 2 seconds
 */
export function useCopyToClipboard(resetDelay: number = 2000) {
    const copied = ref(false);
    let timeoutId: ReturnType<typeof setTimeout> | null = null;

    async function copy(text: string): Promise<boolean> {
        try {
            await navigator.clipboard.writeText(text);
            copied.value = true;

            // Clear any existing timeout
            if (timeoutId) {
                clearTimeout(timeoutId);
            }

            // Reset copied state after delay
            timeoutId = setTimeout(() => {
                copied.value = false;
                timeoutId = null;
            }, resetDelay);

            return true;
        } catch (error) {
            console.error('Failed to copy to clipboard:', error);
            copied.value = false;
            return false;
        }
    }

    return {
        copy,
        copied,
    };
}
