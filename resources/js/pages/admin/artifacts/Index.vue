<script setup lang="ts">
import {
    DataTableBulkActions,
    DataTablePagination,
    DataTableRoot,
    TableFilters,
    type FilterConfig,
} from '@/components/data-table';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { useDataTable } from '@/composables';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import artifactsRoute from '@/routes/admin/artifacts';
import type { BreadcrumbItem } from '@/types';
import type { FilterParams, PaginatedResponse } from '@/types/api';
import { Head, router } from '@inertiajs/vue3';
import { Download, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
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

const showDeleteDialog = ref(false);
const artifactToDelete = ref<ArtifactWithSync | null>(null);

const confirmDelete = (artifact: ArtifactWithSync): void => {
    artifactToDelete.value = artifact;
    showDeleteDialog.value = true;
};

const handleDelete = (): void => {
    if (!artifactToDelete.value) {
        return;
    }

    router.delete(artifactsRoute.destroy(artifactToDelete.value.id).url, {
        onSuccess: () => {
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

// Bulk delete action
const bulkDelete = () => {
    const artifacts = selectedRows.value;

    if (artifacts.length === 0) {
        return;
    }

    const confirmed = confirm(
        `Delete ${artifacts.length} artifact(s)? This will also delete files from storage. This action cannot be undone.`,
    );

    if (!confirmed) {
        return;
    }

    artifacts.forEach((artifact) => {
        router.delete(artifactsRoute.destroy(artifact.id).url, {
            preserveScroll: true,
        });
    });

    clearSelection();
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
                            Artifacts
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Manage build artifacts and storage sync status.
                        </p>
                    </div>
                    <TableFilters
                        :filters="filterConfig"
                        :model-value="filterState"
                        @update:model-value="handleFilterUpdate"
                        @clear="handleFilterClear"
                    />
                </div>

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
                        empty-message="No artifacts found."
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

        <!-- Delete Confirmation Dialog -->
        <AlertDialog
            :open="showDeleteDialog"
            @update:open="showDeleteDialog = $event"
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete Artifact</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to delete "{{
                            artifactToDelete?.filename
                        }}"?
                        <span
                            v-if="
                                artifactToDelete?.source === 'r2' ||
                                artifactToDelete?.source === 's3'
                            "
                            class="mt-2 block font-medium text-destructive"
                        >
                            This will also delete the file from R2 storage.
                        </span>
                        This action cannot be undone.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="cancelDelete"
                        >Cancel</AlertDialogCancel
                    >
                    <AlertDialogAction
                        @click="handleDelete"
                        class="bg-destructive hover:bg-destructive/90"
                    >
                        Delete
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
