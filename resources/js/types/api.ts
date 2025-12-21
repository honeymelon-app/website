// Pagination metadata from Laravel
export interface PaginationMeta {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
}

export interface PaginationLinks {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
}

// Generic paginated response from Laravel API Resources
export interface PaginatedResponse<T> {
    data: T[];
    meta: PaginationMeta;
    links: PaginationLinks;
}

// Release types
export interface Release {
    id: string;
    version: string;
    tag: string;
    channel: 'stable' | 'beta';
    notes: string | null;
    published_at: string;
    major: boolean;
    commit_hash: string;
    user_id: string | null;
    created_at: string;
    updated_at: string;
    user?: User;
    artifacts?: Artifact[];
    updates?: Update[];
}

// Artifact types
export interface Artifact {
    id: string;
    platform: string;
    source: 'github' | 'r2' | 's3';
    filename: string;
    size: number;
    sha256: string | null;
    signature: string | null;
    notarized: boolean;
    url: string;
    path: string | null;
    release_id: string;
    created_at: string;
    updated_at: string;
    release?: Release;
}

// Product types
export interface Product {
    id: string;
    name: string;
    slug: string;
    description: string | null;
    stripe_product_id: string | null;
    stripe_price_id: string | null;
    price_cents: number;
    currency: string;
    formatted_price: string;
    is_active: boolean;
}

// Update types
export interface Update {
    id: string;
    channel: 'stable' | 'beta';
    version: string;
    manifest: Record<string, any>;
    is_latest: boolean;
    published_at: string;
    release_id: string;
    created_at: string;
    updated_at: string;
    release?: Release;
}

// License types
export interface License {
    id: string;
    key: string;
    status: 'active' | 'revoked' | 'expired';
    max_major_version: number;
    issued_at: string | null;
    order_id: string;
    created_at: string;
    updated_at: string;
    order?: Order;
}

// Order types
export interface Order {
    id: string;
    provider: 'ls' | 'stripe';
    external_id: string;
    email: string;
    amount_cents: number;
    formatted_amount: string;
    currency: string;
    meta: Record<string, any>;
    created_at: string;
    updated_at: string;
    license?: License;
}

// Webhook Event types
export interface WebhookEvent {
    id: string;
    provider: 'ls' | 'stripe' | 'github';
    type: string;
    payload: Record<string, any>;
    processed_at: string | null;
    created_at: string;
    updated_at: string;
}

// User type (from index.d.ts)
export interface User {
    id: number;
    cerberus_id: string | null;
    name: string;
    first_name?: string | null;
    last_name?: string | null;
    email: string;
    avatar?: string | null;
    organisation?: {
        id: string | null;
        slug: string | null;
        name: string | null;
    } | null;
    email_verified_at: string | null;
    created_at?: string;
    updated_at?: string;
}

// Filter params
export interface FilterParams {
    [key: string]: string | number | boolean | undefined;
}
