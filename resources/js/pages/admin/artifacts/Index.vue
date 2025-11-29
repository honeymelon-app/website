<script setup lang="ts">
import { DataTable, type Column } from '@/components/data-table';
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
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import artifactsRoute from '@/routes/admin/artifacts';
import type { BreadcrumbItem } from '@/types';
import type { Artifact, PaginatedResponse } from '@/types/resources';
import { Head, router } from '@inertiajs/vue3';
import {
    Download,
    Eye,
    FileArchive,
    MoreHorizontal,
    ShieldCheck,
} from 'lucide-vue-next';
import { h, ref } from 'vue';

interface Props {
    artifacts: PaginatedResponse<Artifact>;
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
const columns: Column<Artifact>[] = [
    {
        key: 'filename',
        label: 'Filename',
        headerClass: 'w-[200px]',
        render: (row: Artifact) => {
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
        render: (row: Artifact) => {
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
        render: (row: Artifact) => {
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
        key: 'size',
        label: 'Size',
        headerClass: 'w-[100px]',
        render: (row: Artifact) => {
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
        render: (row: Artifact) => {
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
        render: (row: Artifact) => {
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
        render: (row: Artifact) => {
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
        render: (row: Artifact) => {
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
                                ],
                            },
                        ),
                    ],
                },
            );
        },
    },
];

// Artifact details modal state
const isDetailsDialogOpen = ref(false);
const selectedArtifact = ref<Artifact | null>(null);

// Actions
const viewArtifact = (artifact: Artifact): void => {
    selectedArtifact.value = artifact;
    isDetailsDialogOpen.value = true;
};

const downloadArtifact = (artifact: Artifact): void => {
    if (artifact.url) {
        window.open(artifact.url, '_blank');
    }
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
                        Manage build artifacts for different platforms and
                        releases.
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
                            Path
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
    </AppLayout>
</template>
