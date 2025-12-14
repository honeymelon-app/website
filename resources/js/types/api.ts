/**
 * API utility types
 *
 * Note: Resource types (Release, Artifact, License, Order, etc.) are defined in resources.d.ts
 * This file only contains utility types for API operations.
 */

// Re-export pagination types from resources for backward compatibility
export type {
    PaginatedResponse,
    PaginationLinks,
    PaginationMeta,
} from './resources.d';

// Filter params for table filtering
export interface FilterParams {
    [key: string]: string | number | boolean | undefined;
}
