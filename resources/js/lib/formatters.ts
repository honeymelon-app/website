/**
 * Shared formatting utilities for the frontend.
 * Centralizes common formatting logic to ensure consistency across the application.
 */

/**
 * Format bytes into a human-readable file size string.
 * @param bytes - The number of bytes to format
 * @returns Formatted string like "1.5 MB" or "0 B" for null/zero
 */
export function formatFileSize(bytes: number | null | undefined): string {
    if (bytes === null || bytes === undefined || bytes === 0) {
        return '0 B';
    }

    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    const size = Math.round((bytes / Math.pow(k, i)) * 100) / 100;

    return `${size} ${sizes[i]}`;
}

/**
 * Format an ISO date string into a localized date string.
 * @param dateString - ISO date string to format
 * @param options - Intl.DateTimeFormat options (defaults to short date)
 * @returns Formatted date string or "N/A" if invalid
 */
export function formatDate(
    dateString: string | null | undefined,
    options: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    },
): string {
    if (!dateString) {
        return 'N/A';
    }

    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', options);
    } catch {
        return 'N/A';
    }
}

/**
 * Format an ISO date string into a localized date and time string.
 * @param dateString - ISO date string to format
 * @returns Formatted datetime string or "N/A" if invalid
 */
export function formatDateTime(dateString: string | null | undefined): string {
    return formatDate(dateString, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

/**
 * Format a date string as a relative time (e.g., "2 hours ago").
 * @param dateString - ISO date string to format
 * @returns Relative time string or "N/A" if invalid
 */
export function formatRelativeTime(
    dateString: string | null | undefined,
): string {
    if (!dateString) {
        return 'N/A';
    }

    try {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor(
            (now.getTime() - date.getTime()) / 1000,
        );

        const rtf = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });

        if (diffInSeconds < 60) {
            return rtf.format(-diffInSeconds, 'second');
        }
        if (diffInSeconds < 3600) {
            return rtf.format(-Math.floor(diffInSeconds / 60), 'minute');
        }
        if (diffInSeconds < 86400) {
            return rtf.format(-Math.floor(diffInSeconds / 3600), 'hour');
        }
        if (diffInSeconds < 2592000) {
            return rtf.format(-Math.floor(diffInSeconds / 86400), 'day');
        }
        if (diffInSeconds < 31536000) {
            return rtf.format(-Math.floor(diffInSeconds / 2592000), 'month');
        }
        return rtf.format(-Math.floor(diffInSeconds / 31536000), 'year');
    } catch {
        return 'N/A';
    }
}

/**
 * Format cents into a currency string.
 * @param cents - Amount in cents (e.g., 1999 for $19.99)
 * @param currency - ISO currency code (defaults to 'USD')
 * @returns Formatted currency string like "$19.99"
 */
export function formatCurrency(
    cents: number | null | undefined,
    currency: string = 'USD',
): string {
    if (cents === null || cents === undefined) {
        return '$0.00';
    }

    const amount = cents / 100;
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency.toUpperCase(),
    }).format(amount);
}

/**
 * Truncate a UUID or long string with ellipsis.
 * @param str - The string to truncate
 * @param length - Number of characters to show (default 8)
 * @returns Truncated string with "..." suffix
 */
export function truncateId(
    str: string | null | undefined,
    length: number = 8,
): string {
    if (!str) {
        return 'N/A';
    }
    if (str.length <= length) {
        return str;
    }
    return `${str.substring(0, length)}...`;
}

/**
 * Generate a UI Avatars URL for a given email.
 * Creates consistent avatar URLs with initials.
 * @param email - Email address to generate avatar for
 * @param size - Size in pixels (default 40)
 * @returns Avatar URL
 */
export function getAvatarUrl(
    email: string | null | undefined,
    size: number = 40,
): string {
    if (!email) {
        return `https://ui-avatars.com/api/?name=?&size=${size}&background=f5d78e&color=4a3728&bold=true`;
    }

    // Extract initials from email (first letter of local part)
    const localPart = email.split('@')[0] || '?';
    const initials = localPart
        .split(/[._-]/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');

    return `https://ui-avatars.com/api/?name=${encodeURIComponent(initials || '?')}&size=${size}&background=f5d78e&color=4a3728&bold=true&format=svg`;
}
