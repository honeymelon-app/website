/**
 * Laravel API Resource Type Definitions
 * Generated from app/Http/Resources
 */

export interface ReleaseArtifact {
    id: string;
    platform: string;
    filename: string | null;
    size: number | null;
    source: string;
    download_url: string | null;
}

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
    artifacts_count?: number;
    artifacts?: ReleaseArtifact[];
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
    activated_at: string | null;
    activation_count: number;
    device_id: string | null;
    is_activated: boolean;
    can_be_revoked: boolean;
    created_at: string;
}

export interface Order {
    id: string;
    provider: string;
    external_id: string;
    email: string;
    amount_cents: number | null;
    formatted_amount: string;
    currency: string | null;
    license_id: string | null;
    license?: License | null;
    refund_id: string | null;
    refunded_at: string | null;
    is_refunded: boolean;
    can_be_refunded: boolean;
    is_within_refund_window: boolean;
    created_at: string;
}

export interface WebhookEvent {
    id: string;
    provider: string;
    type: string;
    processed_at: string | null;
    created_at: string;
}

export interface Faq {
    id: number;
    question: string;
    answer: string;
    order: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

/**
 * Pagination metadata
 */
export interface PaginationMeta {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
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
