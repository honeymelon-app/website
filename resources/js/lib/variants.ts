/**
 * Badge variant helpers for consistent styling across admin pages.
 * Centralizes the mapping of statuses/providers to badge variants.
 */

export type BadgeVariant = 'default' | 'secondary' | 'destructive' | 'outline';

/**
 * Get badge variant for license/order status.
 */
export function getStatusVariant(status: string): BadgeVariant {
    const variantMap: Record<string, BadgeVariant> = {
        active: 'default',
        revoked: 'destructive',
        expired: 'secondary',
        pending: 'secondary',
        completed: 'default',
        failed: 'destructive',
    };
    return variantMap[status] || 'secondary';
}

/**
 * Get badge variant for payment provider.
 */
export function getProviderVariant(provider: string): BadgeVariant {
    const variantMap: Record<string, BadgeVariant> = {
        stripe: 'default',
        manual: 'secondary',
        ls: 'outline',
    };
    return variantMap[provider] || 'outline';
}

/**
 * Get badge variant for release channel.
 */
export function getChannelVariant(channel: string): BadgeVariant {
    const variantMap: Record<string, BadgeVariant> = {
        stable: 'default',
        beta: 'secondary',
        alpha: 'outline',
        nightly: 'destructive',
    };
    return variantMap[channel] || 'secondary';
}

/**
 * Get badge variant for artifact source.
 */
export function getSourceVariant(source: string): BadgeVariant {
    const variantMap: Record<string, BadgeVariant> = {
        github: 'default',
        r2: 'secondary',
        s3: 'outline',
    };
    return variantMap[source] || 'outline';
}
