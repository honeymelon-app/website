<script setup lang="ts">
import { DataTable, type Column } from '@/components/data-table';
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
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import artifactsRoute from '@/routes/admin/artifacts';
import type { BreadcrumbItem } from '@/types';
import type { Artifact, PaginatedResponse } from '@/types/resources';
import { Head, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Cloud,
    Download,
    Eye,
    FileArchive,
    Github,
    MoreHorizontal,
    ShieldCheck,
    Trash2,
    XCircle,
} from 'lucide-vue-next';
import { h, ref } from 'vue';

interface StorageStatus {
    synced: boolean;
    type: 'github' | 'r2' | 'missing_path' | 'not_found' | 'error';
    message: string;
    storage_size?: number;
    size_match?: boolean;
}

interface ArtifactWithSync extends Artifact {
    storage_status: StorageStatus;
}

interface Props {
    artifacts: PaginatedResponse<ArtifactWithSync>;
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

// Helper to format file size
const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
};

// Column definitions
const columns: Column<ArtifactWithSync>[] = [
    {
        key: 'filename',
        label: 'Filename',
        headerClass: 'w-[200px]',
        render: (row: ArtifactWithSync) => {
            return h('div', { class: 'flex items-center gap-2' }, [
                h(FileArchive, {
                    class: 'h-4 w-4 text-muted-foreground flex-shrink-0',
                }),
                h(
                    'span',
                    { class: 'font-mono text-xs truncate' },
                    row.filename || 'N/A',
                ),
            ]);
        },
    },
    {
        key: 'platform',
        label: 'Platform',
        headerClass: 'w-[140px]',
        render: (row: ArtifactWithSync) => {
            return h(
                Badge,
                { variant: 'outline', class: 'font-mono text-xs' },
                { default: () => row.platform },
            );
        },
    },
    {
        key: 'source',
        label: 'Source',
        headerClass: 'w-[100px]',
        render: (row: ArtifactWithSync) => {
            const variantMap: Record<
                string,
                'default' | 'secondary' | 'outline'
            > = {
                github: 'default',
                r2: 'secondary',
                s3: 'outline',
            };
            return h(
                Badge,
                {
                    variant: variantMap[row.source] || 'outline',
                    class: 'uppercase',
                },
                { default: () => row.source },
            );
        },
    },
    {
        key: 'storage_status',
        label: 'Storage',
        headerClass: 'w-[100px]',
        render: (row: ArtifactWithSync) => {
            const status = row.storage_status;
            const iconClass = 'h-4 w-4';

            let icon;
            let colorClass;
            let tooltipText = status.message;

            if (status.synced) {
                if (status.type === 'github') {
                    icon = h(Github, { class: iconClass });
                    colorClass = 'text-foreground';
                } else {
                    icon = h(Cloud, { class: iconClass });
                    colorClass = 'text-green-600 dark:text-green-500';
                    if (status.size_match === false) {
                        icon = h(AlertTriangle, { class: iconClass });
                        colorClass = 'text-yellow-600 dark:text-yellow-500';
                        tooltipText = 'Size mismatch between DB and R2';
                    }
                }
            } else {
                icon = h(XCircle, { class: iconClass });
                colorClass = 'text-red-600 dark:text-red-500';
            }

            return h(
                TooltipProvider,
                {},
                {
                    default: () =>
                        h(
                            Tooltip,
                            {},
                            {
                                default: () => [
                                    h(
                                        TooltipTrigger,
                                        { asChild: true },
                                        {
                                            default: () =>
                                                h(
                                                    'div',
                                                    {
                                                        class: `flex items-center gap-1.5 ${colorClass}`,
                                                    },
                                                    [
                                                        icon,
                                                        h(
                                                            'span',
                                                            {
                                                                class: 'text-xs',
                                                            },
                                                            status.synced
                                                                ? 'Synced'
                                                                : 'Missing',
                                                        ),
                                                    ],
                                                ),
                                        },
                                    ),
                                    h(
                                        TooltipContent,
                                        {},
                                        { default: () => tooltipText },
                                    ),
                                ],
                            },
                        ),
                },
            );
        },
    },
    {
        key: 'size',
        label: 'Size',
        headerClass: 'w-[100px]',
        render: (row: ArtifactWithSync) => {
            return h(
                'div',
                { class: 'text-sm text-muted-foreground' },
                row.size ? formatFileSize(row.size) : 'N/A',
            );
        },
    },
    {
        key: 'notarized',
        label: 'Notarized',
        headerClass: 'w-[80px] text-center',
        class: 'text-center',
        render: (row: ArtifactWithSync) => {
            return row.notarized
                ? h(ShieldCheck, {
                    class: 'h-4 w-4 text-green-600 dark:text-green-500 inline',
                })
                : h('span', { class: 'text-muted-foreground' }, '—');
        },
    },
    {
        key: 'release',
        label: 'Release',
        headerClass: 'w-[100px]',
        render: (row: ArtifactWithSync) => {
            const version =
                row.release?.version ??
                row.release_id?.substring?.(0, 8) ??
                'N/A';
            return h(
                'div',
                { class: 'font-mono text-xs text-muted-foreground truncate' },
                version,
            );
        },
    },
    {
        key: 'created_at',
        label: 'Created',
        headerClass: 'w-[140px]',
        render: (row: ArtifactWithSync) => {
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
        render: (row: ArtifactWithSync) => {
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
                                            onClick: () => viewArtifact(row),
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
                                                downloadArtifact(row),
                                        },
                                        {
                                            default: () => [
                                                h(Download, {
                                                    class: 'mr-2 h-4 w-4',
                                                }),
                                                'Download',
                                            ],
                                        },
                                    ),
                                    h(DropdownMenuSeparator),
                                    h(
                                        DropdownMenuItem,
                                        {
                                            onClick: () => confirmDelete(row),
                                            class: 'text-destructive focus:text-destructive',
                                        },
                                        {
                                            default: () => [
                                                h(Trash2, {
                                                    class: 'mr-2 h-4 w-4',
                                                }),
                                                'Delete',
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
    if (!artifactToDelete.value) return;

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

const handlePageChange = (page: number): void => {
    router.visit(artifactsRoute.index().url, {
        data: { page },
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Artifacts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6"
        >
            <div class="flex flex-col gap-6">
                <div class="flex flex-col gap-2">
                    <h3 class="text-2xl font-semibold tracking-tight">
                        Artifacts
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        Manage build artifacts and their R2 storage sync status.
                    </p>
                </div>

                <DataTable
                    :columns="columns"
                    :data="props.artifacts.data"
                    :meta="props.artifacts.meta"
                    empty-message="No artifacts found."
                    @page-change="handlePageChange"
                />
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
                    <AlertDialogCancel @click="cancelDelete">
                        Cancel
                    </AlertDialogCancel>
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
