import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User | null;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface OrganisationSummary {
    id: string | null;
    slug: string | null;
    name: string | null;
}

export interface CerberusMeta {
    profileUrl: string | null;
    securityUrl: string | null;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    cerberus: CerberusMeta;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    cerberus_id: string | null;
    name: string;
    first_name?: string | null;
    last_name?: string | null;
    email: string;
    avatar?: string | null;
    organisation?: OrganisationSummary | null;
    email_verified_at: string | null;
    created_at?: string;
    updated_at?: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
