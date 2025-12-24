<script setup lang="ts">
import {
    DataTableBulkActions,
    DataTablePagination,
    DataTableRoot,
    TableFilters,
    type FilterConfig,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { useDataTable } from '@/composables';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import releasesRoute from '@/routes/admin/releases';
import type { BreadcrumbItem } from '@/types';
import type { FilterParams, PaginatedResponse } from '@/types/api';
import type { Release } from '@/types/resources';
import { Head, router } from '@inertiajs/vue3';
import { Download, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { columns } from './columns';

interface Filters {
    version?: string;
    tag?: string;
    channel?: string;
    major?: string;
    search?: string;
}

interface Sorting {
    column: string | null;
    direction: 'asc' | 'desc';
}

interface Pagination {
    pageSize: number;
    allowedPageSizes: number[];
}

interface Props {
    releases: PaginatedResponse<Release>;
    filters: Filters;
    sorting: Sorting;
    pagination: Pagination;
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

// Filter configuration for TableFilters component
const filterConfig: FilterConfig[] = [
    {
        key: 'search',
        label: 'Search version...',
        type: 'text',
        placeholder: 'Search version...',
    },
    {
        key: 'channel',
        label: 'Channel',
        type: 'select',
        options: [
            { label: 'Stable', value: 'stable' },
            { label: 'Beta', value: 'beta' },
        ],
    },
];

// Reactive filter state that syncs with props
const filterState = computed<FilterParams>(() => ({
    search: props.filters.search,
    channel: props.filters.channel,
}));

// Use the data table composable
const {
    table,
    selectedRows,
    selectedCount,
    clearSelection,
    handlePageChange,
    handlePageSizeChange,
    handleFilterUpdate,
    handleFilterClear,
    paginationMeta,
    allowedPageSizes,
} = useDataTable({
    data: computed(() => props.releases),
    columns,
    sorting: computed(() => props.sorting),
    filters: computed(() => props.filters as Record<string, unknown>),
    pagination: computed(() => props.pagination),
    indexUrl: releasesRoute.index().url,
    getRowId: (row) => row.id,
    enableRowSelection: true,
});

// Bulk delete action
const bulkDelete = () => {
    const releases = selectedRows.value;

    if (releases.length === 0) {
        return;
    }

    const confirmed = confirm(
        `Delete ${releases.length} release(s)? This will also delete their artifacts and cannot be undone.`,
    );

    if (!confirmed) {
        return;
    }

    releases.forEach((release) => {
        router.delete(releasesRoute.destroy(release.id).url, {
            preserveScroll: true,
        });
    });

    clearSelection();
};

// Export selected releases to CSV
const exportSelected = () => {
    const releases = selectedRows.value;

    const headers = [
        'Version',
        'Tag',
        'Channel',
        'Major',
        'Artifacts',
        'Published',
        'Created',
    ];
    const csvContent = [
        headers.join(','),
        ...releases.map((release) =>
            [
                release.version,
                release.tag,
                release.channel,
                release.major ? 'Yes' : 'No',
                release.artifacts_count ?? 0,
                release.published_at || 'Not published',
                release.created_at,
            ].join(','),
        ),
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `releases-export-${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
};
</script>

<template>
    <Head title="Releases" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6"
        >
            <div class="flex flex-col gap-6">
                <!-- Header + Filters -->
                <div
                    class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
                >
                    <div class="flex flex-col gap-1">
                        <h1 class="text-2xl font-semibold tracking-tight">
                            Releases
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Manage your application releases and versions.
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <TableFilters
                            :filters="filterConfig"
                            :model-value="filterState"
                            @update:model-value="handleFilterUpdate"
                            @clear="handleFilterClear"
                        />
                        <Button @click="router.visit('/admin/releases/create')">
                            Create Release
                        </Button>
                    </div>
                </div>

                <!-- Bulk Actions Toolbar -->
                <DataTableBulkActions
                    :selected-count="selectedCount"
                    item-label="release"
                    @clear="clearSelection"
                >
                    <Button
                        variant="outline"
                        size="sm"
                        class="h-8"
                        @click="exportSelected"
                    >
                        <Download class="mr-1.5 h-4 w-4" />
                        Export CSV
                    </Button>
                    <Button
                        variant="destructive"
                        size="sm"
                        class="h-8"
                        @click="bulkDelete"
                    >
                        <Trash2 class="mr-1.5 h-4 w-4" />
                        Delete Selected
                    </Button>
                </DataTableBulkActions>

                <!-- Table -->
                <div class="space-y-4">
                    <DataTableRoot
                        :table="table"
                        :columns="columns"
                        empty-message="No releases found. Create your first release to get started."
                    />

                    <!-- Pagination -->
                    <DataTablePagination
                        :meta="paginationMeta"
                        :allowed-page-sizes="allowedPageSizes"
                        @page-change="handlePageChange"
                        @page-size-change="handlePageSizeChange"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
