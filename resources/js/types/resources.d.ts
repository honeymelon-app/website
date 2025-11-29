/**
 * Laravel API Resource Type Definitions
 * Generated from app/Http/Resources
 */

export interface Release {
    id: string;
    version: string;
    tag: string;
    commit_hash: string;
    channel: string;
    notes: string | null;
    published_at: string | null;
    major: boolean;
    created_by: string | null;
    created_at: string;
}

export interface Artifact {
    id: string;
    release_id: string;
    platform: string;
    source: string;
    filename: string | null;
    size: number | null;
    sha256: string | null;
    signature: string | null;
    notarized: boolean;
    url: string | null;
    path: string | null;
    created_at: string;
    release?: Release;
    download_url?: string;
}

export interface Update {
    id: string;
    release_id: string;
    channel: string;
    version: string;
    is_latest: boolean;
    published_at: string | null;
    created_at: string;
}

export interface License {
    id: string;
    key: string;
    key_hash: string;
    status: string;
    max_major_version: number;
    issued_at: string | null;
    created_at: string;
}

export interface Order {
    id: string;
    provider: string;
    external_id: string;
    email: string;
    amount_cents: number | null;
    currency: string | null;
    license_id: string | null;
    created_at: string;
}

export interface WebhookEvent {
    id: string;
    provider: string;
    type: string;
    processed_at: string | null;
    created_at: string;
}

/**
 * Pagination metadata
 */
export interface PaginationMeta {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
}

/**
 * Pagination links
 */
export interface PaginationLinks {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
}

/**
 * Paginated response wrapper
 */
export interface PaginatedResponse<T> {
    data: T[];
    meta: PaginationMeta;
    links: PaginationLinks;
}

/**
 * Tauri Update Manifest (raw format - NO envelope)
 */
export interface UpdateManifest {
    version: string;
    notes: string;
    pub_date: string;
    platforms: {
        [platform: string]: {
            signature: string;
            url: string;
            sha256: string;
        };
    };
}
