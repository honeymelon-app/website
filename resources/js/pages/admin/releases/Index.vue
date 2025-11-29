<script setup lang="ts">
import { DataTable, type Column } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import releasesRoute from '@/routes/admin/releases';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedResponse, Release } from '@/types/resources';
import { Head, router } from '@inertiajs/vue3';
import { Download, Eye, MoreHorizontal, Rocket } from 'lucide-vue-next';
import { h } from 'vue';

interface Props {
    releases: PaginatedResponse<Release>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Releases',
        href: releasesRoute.index().url,
    },
];

// Helper to format file size
const columns: Column<Release>[] = [
    {
        key: 'version',
        label: 'Version',
        headerClass: 'w-[120px]',
        render: (row: Release) => {
            return h('div', { class: 'font-mono font-medium' }, row.version);
        },
    },
    {
        key: 'tag',
        label: 'Tag',
        class: 'font-mono text-xs text-muted-foreground',
    },
    {
        key: 'channel',
        label: 'Channel',
        headerClass: 'w-[100px]',
        render: (row: Release) => {
            const variant = row.channel === 'stable' ? 'default' : 'secondary';
            return h(
                Badge,
                { variant, class: 'capitalize' },
                { default: () => row.channel },
            );
        },
    },
    {
        key: 'major',
        label: 'Major',
        headerClass: 'w-[80px] text-center',
        class: 'text-center',
        render: (row: Release) => {
            return row.major
                ? h(
                    Badge,
                    { variant: 'destructive', class: 'text-xs' },
                    { default: () => 'Major' },
                )
                : h('span', { class: 'text-muted-foreground' }, '—');
        },
    },
    {
        key: 'published_at',
        label: 'Published',
        headerClass: 'w-[140px]',
        render: (row: Release) => {
            if (!row.published_at) {
                return h(
                    'div',
                    { class: 'text-sm text-muted-foreground' },
                    'Not published',
                );
            }
            const date = new Date(row.published_at);
            return h(
                'div',
                { class: 'text-sm' },
                date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                }),
            );
        },
    },
    {
        key: 'created_at',
        label: 'Created',
        headerClass: 'w-[140px]',
        render: (row: Release) => {
            const date = new Date(row.created_at);
            return h(
                'time',
                {
                    datetime: row.created_at,
                    class: 'text-sm text-muted-foreground',
                },
                date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                }),
            );
        },
    },
    {
        key: 'actions',
        label: '',
        headerClass: 'w-[50px]',
        render: (row: Release) => {
            return h(
                DropdownMenu,
                {},
                {
                    default: () => [
                        h(
                            DropdownMenuTrigger,
                            { asChild: true },
                            {
                                default: () =>
                                    h(
                                        Button,
                                        {
                                            variant: 'ghost',
                                            size: 'icon',
                                            class: 'h-8 w-8',
                                        },
                                        {
                                            default: () => [
                                                h(
                                                    'span',
                                                    { class: 'sr-only' },
                                                    'Open menu',
                                                ),
                                                h(MoreHorizontal, {
                                                    class: 'h-4 w-4',
                                                }),
                                            ],
                                        },
                                    ),
                            },
                        ),
                        h(
                            DropdownMenuContent,
                            { align: 'end' },
                            {
                                default: () => [
                                    h(
                                        DropdownMenuLabel,
                                        {},
                                        { default: () => 'Actions' },
                                    ),
                                    h(
                                        DropdownMenuItem,
                                        {
                                            onClick: () => viewRelease(row),
                                        },
                                        {
                                            default: () => [
                                                h(Eye, {
                                                    class: 'mr-2 h-4 w-4',
                                                }),
                                                'View Details',
                                            ],
                                        },
                                    ),
                                    h(
                                        DropdownMenuItem,
                                        {
                                            onClick: () =>
                                                downloadArtifacts(row),
                                        },
                                        {
                                            default: () => [
                                                h(Download, {
                                                    class: 'mr-2 h-4 w-4',
                                                }),
                                                'Download Artifacts',
                                            ],
                                        },
                                    ),
                                    h(DropdownMenuSeparator),
                                    h(
                                        DropdownMenuItem,
                                        {
                                            onClick: () => publishRelease(row),
                                        },
                                        {
                                            default: () => [
                                                h(Rocket, {
                                                    class: 'mr-2 h-4 w-4',
                                                }),
                                                'Publish to Channel',
                                            ],
                                        },
                                    ),
                                ],
                            },
                        ),
                    ],
                },
            );
        },
    },
];

// Actions
const viewRelease = (release: Release): void => {
    router.visit(releasesRoute.show(release.id).url);
};

const downloadArtifacts = (release: Release): void => {
    console.log('Download artifacts for:', release.version);
    // Implement download logic
};

const publishRelease = (release: Release): void => {
    console.log('Publish release:', release.version);
    // Implement publish logic
};

const handlePageChange = (page: number): void => {
    router.visit(releasesRoute.index().url, {
        data: { page },
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Releases" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6"
        >
            <div class="flex flex-col gap-6">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-2">
                        <h3 class="text-2xl font-semibold tracking-tight">
                            Releases
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            Manage your application releases and versions.
                        </p>
                    </div>

                    <Button @click="router.visit('/admin/releases/create')">
                        Create Release
                    </Button>
                </div>

                <DataTable
                    :columns="columns"
                    :data="props.releases.data"
                    :meta="props.releases.meta"
                    empty-message="No releases found. Create your first release to get started."
                    @page-change="handlePageChange"
                />
            </div>
        </div>
    </AppLayout>
</template>
