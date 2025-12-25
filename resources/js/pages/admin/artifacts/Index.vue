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
import artifactsRoute from '@/routes/admin/artifacts';
import type { BreadcrumbItem } from '@/types';
import type { FilterParams, PaginatedResponse } from '@/types/api';
import { Head, router } from '@inertiajs/vue3';
import { Download, Trash2 } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { columns, type ArtifactWithSync } from './columns';

interface Filters {
    search?: string;
    platform?: string;
    source?: string;
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
    artifacts: PaginatedResponse<ArtifactWithSync>;
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
        title: 'Artifacts',
        href: artifactsRoute.index().url,
    },
];

// Delete dialog state
const showDeleteDialog = ref(false);
const artifactToDelete = ref<ArtifactWithSync | null>(null);
const isDeleting = ref(false);

// Bulk delete dialog state
const isBulkDeleteDialogOpen = ref(false);
const isBulkDeleting = ref(false);

// Filter configuration for TableFilters component
const filterConfig: FilterConfig[] = [
    {
        key: 'search',
        label: 'Search...',
        type: 'text',
        placeholder: 'Search by filename...',
    },
    {
        key: 'platform',
        label: 'Platform',
        type: 'select',
        options: [
            { label: 'macOS Intel', value: 'darwin-x86_64' },
            { label: 'macOS ARM', value: 'darwin-aarch64' },
            { label: 'Windows', value: 'windows-x86_64' },
            { label: 'Linux', value: 'linux-x86_64' },
        ],
    },
    {
        key: 'source',
        label: 'Source',
        type: 'select',
        options: [
            { label: 'GitHub', value: 'github' },
            { label: 'R2', value: 'r2' },
            { label: 'S3', value: 's3' },
        ],
    },
];

// Reactive filter state that syncs with props
const filterState = computed<FilterParams>(() => ({
    search: props.filters.search,
    platform: props.filters.platform,
    source: props.filters.source,
}));

// Actions for the columns
const viewArtifact = (artifact: ArtifactWithSync): void => {
    router.visit(artifactsRoute.show(artifact.id).url);
};

const downloadArtifact = (artifact: ArtifactWithSync): void => {
    if (artifact.download_url) {
        window.open(artifact.download_url, '_blank');
    } else if (artifact.url) {
        window.open(artifact.url, '_blank');
    }
};

const confirmDelete = (artifact: ArtifactWithSync): void => {
    artifactToDelete.value = artifact;
    showDeleteDialog.value = true;
};

const handleDelete = (): void => {
    if (!artifactToDelete.value) {
        return;
    }

    isDeleting.value = true;
    router.delete(artifactsRoute.destroy(artifactToDelete.value.id).url, {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
            showDeleteDialog.value = false;
            artifactToDelete.value = null;
        },
    });
};

const cancelDelete = (): void => {
    showDeleteDialog.value = false;
    artifactToDelete.value = null;
};

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
    data: computed(() => props.artifacts),
    columns,
    sorting: computed(() => props.sorting),
    filters: computed(() => props.filters as Record<string, unknown>),
    pagination: computed(() => props.pagination),
    indexUrl: artifactsRoute.index().url,
    getRowId: (row) => row.id,
    enableRowSelection: true,
    meta: {
        viewArtifact,
        downloadArtifact,
        confirmDelete,
    },
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
    const artifacts = selectedRows.value;

    if (artifacts.length === 0) {
        return;
    }

    isBulkDeleting.value = true;

    let completed = 0;
    artifacts.forEach((artifact) => {
        router.delete(artifactsRoute.destroy(artifact.id).url, {
            preserveScroll: true,
            onFinish: () => {
                completed++;
                if (completed === artifacts.length) {
                    isBulkDeleting.value = false;
                    isBulkDeleteDialogOpen.value = false;
                    clearSelection();
                }
            },
        });
    });
};

// Export selected artifacts to CSV
const exportSelected = () => {
    const artifacts = selectedRows.value;

    const headers = [
        'Filename',
        'Platform',
        'Source',
        'Size',
        'Notarized',
        'Release',
        'SHA256',
        'Created',
    ];
    const csvContent = [
        headers.join(','),
        ...artifacts.map((artifact) =>
            [
                artifact.filename,
                artifact.platform,
                artifact.source,
                artifact.size,
                artifact.notarized ? 'Yes' : 'No',
                artifact.release?.version || artifact.release_id || '',
                artifact.sha256 || '',
                artifact.created_at,
            ].join(','),
        ),
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `artifacts-export-${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
};
</script>

<template>
    <Head title="Artifacts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AdminPage>
            <AdminSection>
                <!-- Header + Filters -->
                <AdminToolbar
                    title="Artifacts"
                    subtitle="Manage build artifacts and storage sync status."
                >
                    <template #filters>
                        <TableFilters
                            :filters="filterConfig"
                            :model-value="filterState"
                            @update:model-value="handleFilterUpdate"
                            @clear="handleFilterClear"
                        />
                    </template>
                </AdminToolbar>

                <!-- Bulk Actions Toolbar -->
                <DataTableBulkActions
                    :selected-count="selectedCount"
                    item-label="artifact"
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
                        title="Delete Artifacts?"
                        :description="`Delete ${selectedCount} artifact(s)? This will also delete files from storage. This action cannot be undone.`"
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
                                title="No artifacts yet"
                                description="Artifacts will be created automatically when you publish releases."
                            />
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

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            v-model:open="showDeleteDialog"
            title="Delete Artifact"
            :description="`Are you sure you want to delete &quot;${artifactToDelete?.filename}&quot;? ${artifactToDelete?.source === 'r2' || artifactToDelete?.source === 's3' ? 'This will also delete the file from storage.' : ''} This action cannot be undone.`"
            confirm-label="Delete"
            :show-trigger="false"
            :loading="isDeleting"
            @confirm="handleDelete"
            @cancel="cancelDelete"
        />
    </AppLayout>
</template>
