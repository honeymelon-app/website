import { InertiaLinkProps } from '@inertiajs/vue3';
import type { Updater } from '@tanstack/vue-table';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import type { Ref } from 'vue';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
) {
    return toUrl(urlToCheck) === currentUrl;
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

/**
 * Handle TanStack Table state updates.
 * Supports both direct values and functional updates.
 */
export function valueUpdater<T>(updaterOrValue: Updater<T>, ref: Ref<T>): void {
    ref.value =
        typeof updaterOrValue === 'function'
            ? (updaterOrValue as (old: T) => T)(ref.value)
            : updaterOrValue;
}
