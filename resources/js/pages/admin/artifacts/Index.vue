<script setup lang="ts">
import { ConfirmDialog } from '@/components/admin';
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
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, formatFileSize } from '@/lib/formatters';
import { getSourceVariant } from '@/lib/variants';
import { dashboard } from '@/routes';
import artifacts from '@/routes/admin/artifacts';
import releases from '@/routes/admin/releases';
import type { BreadcrumbItem } from '@/types';
import type { ArtifactWithSync, PaginatedResponse } from '@/types/resources';
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
        href: artifacts.index().url,
    },
];

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
            return h(
                Badge,
                {
                    variant: getSourceVariant(row.source),
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
                formatFileSize(row.size),
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
        headerClass: 'w-[120px]',
        render: (row: ArtifactWithSync) => {
            const version = row.release?.version ?? row.release_id;
            if (!version || !row.release_id) {
                return h(
                    'span',
                    { class: 'text-muted-foreground text-sm' },
                    'N/A',
                );
            }

            return h(
                Button,
                {
                    variant: 'link',
                    class: 'px-0 text-xs font-mono text-muted-foreground',
                    onClick: () => viewRelease(row.release_id!),
                },
                { default: () => version },
            );
        },
    },
    {
        key: 'sha256',
        label: 'SHA256',
        headerClass: 'w-[160px]',
        render: (row: ArtifactWithSync) => {
            if (!row.sha256) {
                return h(
                    'span',
                    { class: 'text-muted-foreground text-sm' },
                    '—',
                );
            }

            const shortHash = `${row.sha256.substring(0, 10)}…`;

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
                                                    'span',
                                                    {
                                                        class: 'font-mono text-xs text-muted-foreground',
                                                    },
                                                    shortHash,
                                                ),
                                        },
                                    ),
                                    h(
                                        TooltipContent,
                                        {},
                                        { default: () => row.sha256 },
                                    ),
                                ],
                            },
                        ),
                },
            );
        },
    },
    {
        key: 'created_at',
        label: 'Created',
        headerClass: 'w-[140px]',
        render: (row: ArtifactWithSync) => {
            return h(
                'time',
                {
                    datetime: row.created_at,
                    class: 'text-sm text-muted-foreground',
                },
                formatDate(row.created_at),
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
    router.visit(artifacts.show(artifact.id).url);
};

const viewRelease = (releaseId: string): void => {
    router.visit(releases.show(releaseId).url);
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

    router.delete(artifacts.destroy(artifactToDelete.value.id).url, {
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
    router.visit(artifacts.index().url, {
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
                    empty-message="No artifacts found. Artifacts are created when releases are synced from GitHub."
                    @page-change="handlePageChange"
                />
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            v-model:open="showDeleteDialog"
            title="Delete Artifact"
            confirm-label="Delete"
            :show-trigger="false"
            @confirm="handleDelete"
            @cancel="cancelDelete"
        >
            <p>
                Are you sure you want to delete "{{
                    artifactToDelete?.filename
                }}"?
            </p>
            <p
                v-if="
                    artifactToDelete?.source === 'r2' ||
                    artifactToDelete?.source === 's3'
                "
                class="mt-2 font-medium text-destructive"
            >
                This will also delete the file from R2 storage.
            </p>
            <p class="mt-2">This action cannot be undone.</p>
        </ConfirmDialog>
    </AppLayout>
</template>
