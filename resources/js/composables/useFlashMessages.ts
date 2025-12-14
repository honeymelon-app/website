import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { watch } from 'vue';

/**
 * Composable that watches for Laravel flash messages and displays them as sonner toasts.
 * Call this in components/pages that need to react to flash messages.
 */
export function useFlashMessages(): void {
    const page = usePage();

    watch(
        () => page.props.flash,
        (flash) => {
            if (!flash) {
                return;
            }

            const flashData = flash as { success?: string; error?: string };

            if (flashData.success) {
                toast.success(flashData.success);
            }

            if (flashData.error) {
                toast.error(flashData.error);
            }
        },
        { immediate: true },
    );
}
