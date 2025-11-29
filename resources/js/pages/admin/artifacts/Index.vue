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
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
    CheckCircle2,
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
        key: 'release_id',
        label: 'Release',
        headerClass: 'w-[100px]',
        render: (row: ArtifactWithSync) => {
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

// State
const isDetailsDialogOpen = ref(false);
const selectedArtifact = ref<ArtifactWithSync | null>(null);
const showDeleteDialog = ref(false);
const artifactToDelete = ref<ArtifactWithSync | null>(null);

// Actions
const viewArtifact = (artifact: ArtifactWithSync): void => {
    selectedArtifact.value = artifact;
    isDetailsDialogOpen.value = true;
};

const downloadArtifact = (artifact: ArtifactWithSync): void => {
    if (artifact.url) {
        window.open(artifact.url, '_blank');
    }
};

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
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
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

        <!-- Artifact Details Modal -->
        <Dialog v-model:open="isDetailsDialogOpen">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Artifact Details</DialogTitle>
                    <DialogDescription>
                        Detailed information about the selected artifact.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="selectedArtifact" class="grid gap-6 py-4">
                    <!-- Storage Status Banner -->
                    <div
                        v-if="selectedArtifact.storage_status"
                        :class="[
                            'flex items-center gap-3 rounded-lg border p-4',
                            selectedArtifact.storage_status.synced
                                ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950'
                                : 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950',
                        ]"
                    >
                        <CheckCircle2
                            v-if="selectedArtifact.storage_status.synced"
                            class="h-5 w-5 text-green-600 dark:text-green-400"
                        />
                        <XCircle
                            v-else
                            class="h-5 w-5 text-red-600 dark:text-red-400"
                        />
                        <div>
                            <p
                                :class="[
                                    'font-medium',
                                    selectedArtifact.storage_status.synced
                                        ? 'text-green-800 dark:text-green-200'
                                        : 'text-red-800 dark:text-red-200',
                                ]"
                            >
                                {{
                                    selectedArtifact.storage_status.synced
                                        ? 'Storage Synced'
                                        : 'Storage Issue'
                                }}
                            </p>
                            <p
                                :class="[
                                    'text-sm',
                                    selectedArtifact.storage_status.synced
                                        ? 'text-green-700 dark:text-green-300'
                                        : 'text-red-700 dark:text-red-300',
                                ]"
                            >
                                {{ selectedArtifact.storage_status.message }}
                            </p>
                        </div>
                    </div>

                    <!-- Filename -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Filename
                        </div>
                        <div class="col-span-2 flex items-center gap-2">
                            <FileArchive
                                class="h-4 w-4 flex-shrink-0 text-muted-foreground"
                            />
                            <span class="font-mono text-sm break-all">
                                {{ selectedArtifact.filename || 'N/A' }}
                            </span>
                        </div>
                    </div>

                    <!-- Platform -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Platform
                        </div>
                        <div class="col-span-2">
                            <Badge variant="outline" class="font-mono text-xs">
                                {{ selectedArtifact.platform }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Source -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Source
                        </div>
                        <div class="col-span-2">
                            <Badge
                                :variant="
                                    selectedArtifact.source === 'github'
                                        ? 'default'
                                        : selectedArtifact.source === 'r2'
                                          ? 'secondary'
                                          : 'outline'
                                "
                                class="uppercase"
                            >
                                {{ selectedArtifact.source }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Size -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Size
                        </div>
                        <div class="col-span-2 text-sm">
                            {{
                                selectedArtifact.size
                                    ? formatFileSize(selectedArtifact.size)
                                    : 'N/A'
                            }}
                        </div>
                    </div>

                    <!-- Notarized -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Notarized
                        </div>
                        <div class="col-span-2 flex items-center gap-2">
                            <ShieldCheck
                                v-if="selectedArtifact.notarized"
                                class="h-4 w-4 text-green-600 dark:text-green-500"
                            />
                            <span
                                :class="
                                    selectedArtifact.notarized
                                        ? 'text-green-600 dark:text-green-500'
                                        : 'text-muted-foreground'
                                "
                                class="text-sm"
                            >
                                {{ selectedArtifact.notarized ? 'Yes' : 'No' }}
                            </span>
                        </div>
                    </div>

                    <!-- SHA256 -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            SHA256
                        </div>
                        <div class="col-span-2">
                            <code
                                v-if="selectedArtifact.sha256"
                                class="block rounded bg-muted px-2 py-1 font-mono text-xs break-all"
                            >
                                {{ selectedArtifact.sha256 }}
                            </code>
                            <span v-else class="text-sm text-muted-foreground">
                                N/A
                            </span>
                        </div>
                    </div>

                    <!-- Signature -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Signature
                        </div>
                        <div class="col-span-2">
                            <code
                                v-if="selectedArtifact.signature"
                                class="block max-h-32 overflow-y-auto rounded bg-muted px-2 py-1 font-mono text-xs break-all"
                            >
                                {{ selectedArtifact.signature }}
                            </code>
                            <span v-else class="text-sm text-muted-foreground">
                                N/A
                            </span>
                        </div>
                    </div>

                    <!-- Release ID -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Release ID
                        </div>
                        <div
                            class="col-span-2 font-mono text-xs text-muted-foreground"
                        >
                            {{ selectedArtifact.release_id }}
                        </div>
                    </div>

                    <!-- Path -->
                    <div
                        v-if="selectedArtifact.path"
                        class="grid grid-cols-3 items-start gap-4"
                    >
                        <div class="text-sm font-medium text-muted-foreground">
                            Storage Path
                        </div>
                        <div class="col-span-2">
                            <code
                                class="block rounded bg-muted px-2 py-1 font-mono text-xs break-all"
                            >
                                {{ selectedArtifact.path }}
                            </code>
                        </div>
                    </div>

                    <!-- URL -->
                    <div
                        v-if="selectedArtifact.url"
                        class="grid grid-cols-3 items-start gap-4"
                    >
                        <div class="text-sm font-medium text-muted-foreground">
                            URL
                        </div>
                        <div class="col-span-2">
                            <a
                                :href="selectedArtifact.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="block font-mono text-xs break-all text-primary hover:underline"
                            >
                                {{ selectedArtifact.url }}
                            </a>
                        </div>
                    </div>

                    <!-- Created At -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Created At
                        </div>
                        <div class="col-span-2 text-sm text-muted-foreground">
                            {{
                                new Date(
                                    selectedArtifact.created_at,
                                ).toLocaleString('en-US', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit',
                                })
                            }}
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-2 pt-4">
                        <Button
                            variant="destructive"
                            @click="
                                isDetailsDialogOpen = false;
                                confirmDelete(selectedArtifact);
                            "
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete
                        </Button>
                        <Button
                            v-if="selectedArtifact.url"
                            variant="outline"
                            @click="downloadArtifact(selectedArtifact)"
                        >
                            <Download class="mr-2 h-4 w-4" />
                            Download
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

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
