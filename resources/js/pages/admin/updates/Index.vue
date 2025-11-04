<script setup lang="ts">
import {
    DataTable,
    TableFilters,
    type Column,
    type FilterConfig,
} from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useTableData } from '@/composables/useTableData';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import updates from '@/routes/admin/updates';
import type { BreadcrumbItem } from '@/types';
import type { FilterParams, Update } from '@/types/api';
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle, Download, Eye, MoreHorizontal } from 'lucide-vue-next';
import { h, onMounted, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Updates',
        href: updates.index().url,
    },
];

// Fetch data
const {
    data: updatesData,
    meta,
    isLoading,
    fetchData,
    updateFilters,
    goToPage,
    clearFilters,
} = useTableData<Update>('/api/updates');

// Filters
const filterParams = ref<FilterParams>({});

const filterConfigs: FilterConfig[] = [
    {
        key: 'search',
        type: 'text',
        label: 'Search',
        placeholder: 'Search versions...',
    },
    {
        key: 'channel',
        type: 'select',
        label: 'Channel',
        options: [
            { label: 'Stable', value: 'stable' },
            { label: 'Beta', value: 'beta' },
        ],
    },
    {
        key: 'is_latest',
        type: 'select',
        label: 'Latest Only',
        options: [{ label: 'Latest', value: 'true' }],
    },
];

// Column definitions
const columns: Column<Update>[] = [
    {
        key: 'version',
        label: 'Version',
        headerClass: 'w-[120px]',
        render: (row: Update) => {
            return h('div', { class: 'font-mono font-medium' }, row.version);
        },
    },
    {
        key: 'channel',
        label: 'Channel',
        headerClass: 'w-[100px]',
        render: (row: Update) => {
            const variant = row.channel === 'stable' ? 'default' : 'secondary';
            return h(
                Badge,
                { variant, class: 'capitalize' },
                { default: () => row.channel },
            );
        },
    },
    {
        key: 'is_latest',
        label: 'Latest',
        headerClass: 'w-[80px] text-center',
        class: 'text-center',
        render: (row: Update) => {
            return row.is_latest
                ? h(CheckCircle, {
                      class: 'h-4 w-4 text-green-600 dark:text-green-500 inline',
                  })
                : h('span', { class: 'text-muted-foreground' }, '—');
        },
    },
    {
        key: 'published_at',
        label: 'Published',
        headerClass: 'w-[140px]',
        render: (row: Update) => {
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
        key: 'release_id',
        label: 'Release',
        headerClass: 'w-[100px]',
        render: (row: Update) => {
            return h(
                'div',
                { class: 'font-mono text-xs text-muted-foreground truncate' },
                row.release_id.substring(0, 8),
            );
        },
    },
    {
        key: 'created_at',
        label: 'Created',
        headerClass: 'w-[140px]',
        render: (row: Update) => {
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
        render: (row: Update) => {
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
                                            onClick: () => viewUpdate(row),
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
                                                downloadManifest(row),
                                        },
                                        {
                                            default: () => [
                                                h(Download, {
                                                    class: 'mr-2 h-4 w-4',
                                                }),
                                                'Download Manifest',
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
const viewUpdate = (update: Update): void => {
    router.visit(`/admin/updates/${update.id}`);
};

const downloadManifest = (update: Update): void => {
    console.log('Download manifest for:', update.version);
    // Implement download logic
};

const handleFilterApply = (): void => {
    updateFilters(filterParams.value);
};

const handleFilterClear = (): void => {
    filterParams.value = {};
    clearFilters();
};

const handlePageChange = (page: number): void => {
    goToPage(page);
};

// Fetch data on mount
onMounted(() => {
    fetchData();
});
</script>

<template>
    <Head title="Updates" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex flex-col gap-6">
                <div class="flex flex-col gap-2">
                    <h3 class="text-2xl font-semibold tracking-tight">
                        Updates
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        Manage update manifests for different channels and
                        versions.
                    </p>
                </div>

                <div class="flex flex-col gap-4">
                    <TableFilters
                        v-model="filterParams"
                        :filters="filterConfigs"
                        @apply="handleFilterApply"
                        @clear="handleFilterClear"
                    />

                    <DataTable
                        :columns="columns"
                        :data="updatesData"
                        :meta="meta"
                        :is-loading="isLoading"
                        empty-message="No updates found."
                        @page-change="handlePageChange"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
