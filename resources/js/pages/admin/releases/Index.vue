<script setup lang="ts">
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import {
    AdminLoadingState,
    AdminPage,
    AdminSection,
    AdminToolbar,
    ConfirmDialog,
} from '@/components/admin';
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
import { Download, Plus, Trash2 } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
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

// Bulk delete dialog state
const isBulkDeleteDialogOpen = ref(false);
const isBulkDeleting = ref(false);

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

// Loading state for initial render
const isInitialLoad = ref(true);

onMounted(() => {
    // Brief loading state for skeleton UI
    setTimeout(() => {
        isInitialLoad.value = false;
    }, 150);
});

// Bulk delete action
const bulkDelete = () => {
    if (selectedRows.value.length === 0) {
        return;
    }
    isBulkDeleteDialogOpen.value = true;
};

const confirmBulkDelete = () => {
    const releases = selectedRows.value;

    if (releases.length === 0) {
        return;
    }

    isBulkDeleting.value = true;

    let completed = 0;
    releases.forEach((release) => {
        router.delete(releasesRoute.destroy(release.id).url, {
            preserveScroll: true,
            onFinish: () => {
                completed++;
                if (completed === releases.length) {
                    isBulkDeleting.value = false;
                    isBulkDeleteDialogOpen.value = false;
                    clearSelection();
                }
            },
        });
    });
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
        <AdminPage>
            <AdminSection>
                <!-- Header + Filters -->
                <AdminToolbar
                    title="Releases"
                    subtitle="Manage your application releases and versions."
                >
                    <template #filters>
                        <TableFilters
                            :filters="filterConfig"
                            :model-value="filterState"
                            @update:model-value="handleFilterUpdate"
                            @clear="handleFilterClear"
                        />
                    </template>
                    <template #actions>
                        <Button @click="router.visit('/admin/releases/create')">
                            <Plus class="mr-2 h-4 w-4" />
                            Create Release
                        </Button>
                    </template>
                </AdminToolbar>

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
                        <Download class="mr-2 h-4 w-4" />
                        Export CSV
                    </Button>
                    <ConfirmDialog
                        v-model:open="isBulkDeleteDialogOpen"
                        title="Delete Releases?"
                        :description="`Delete ${selectedCount} release(s)? This will also delete their artifacts and cannot be undone.`"
                        confirm-label="Delete"
                        :loading="isBulkDeleting"
                        @confirm="confirmBulkDelete"
                    >
                        <template #trigger>
                            <Button
                                variant="destructive"
                                size="sm"
                                class="h-8"
                            >
                                <Trash2 class="mr-2 h-4 w-4" />
                                Delete Selected
                            </Button>
                        </template>
                    </ConfirmDialog>
                </DataTableBulkActions>

                <!-- Table -->
                <div class="space-y-4">
                    <AdminLoadingState v-if="isInitialLoad" :rows="5" />
                    <DataTableRoot
                        v-else
                        :table="table"
                        :columns="columns"
                    >
                        <template #empty>
                            <AdminEmptyState
                                icon="Package"
                                title="No releases yet"
                                description="Create your first release to get started."
                            >
                                <Button @click="router.visit('/admin/releases/create')">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Create Release
                                </Button>
                            </AdminEmptyState>
                        </template>
                    </DataTableRoot>

                    <!-- Pagination -->
                    <DataTablePagination
                        :meta="paginationMeta"
                        :allowed-page-sizes="allowedPageSizes"
                        @page-change="handlePageChange"
                        @page-size-change="handlePageSizeChange"
                    />
                </div>
            </AdminSection>
        </AdminPage>
    </AppLayout>
</template>
