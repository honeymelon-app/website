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
import { marked } from 'marked';
import { computed, h, ref } from 'vue';

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

// Release details modal state
const isDetailsDialogOpen = ref(false);
const selectedRelease = ref<Release | null>(null);

// Computed property to parse markdown notes
const parsedNotes = computed(() => {
    if (!selectedRelease.value?.notes) return '';
    return marked(selectedRelease.value.notes);
});

// Column definitions
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
    selectedRelease.value = release;
    isDetailsDialogOpen.value = true;
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
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
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

        <!-- Release Details Modal -->
        <Dialog v-model:open="isDetailsDialogOpen">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Release Details</DialogTitle>
                    <DialogDescription>
                        Detailed information about the selected release.
                    </DialogDescription>
                </DialogHeader>

                <div
                    v-if="selectedRelease"
                    class="grid gap-6 py-4"
                >
                    <!-- Version -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Version
                        </div>
                        <div class="col-span-2">
                            <span class="font-mono font-medium text-lg">
                                {{ selectedRelease.version }}
                            </span>
                        </div>
                    </div>

                    <!-- Tag -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Git Tag
                        </div>
                        <div class="col-span-2">
                            <code
                                class="block break-all rounded bg-muted px-2 py-1 font-mono text-xs"
                            >
                                {{ selectedRelease.tag }}
                            </code>
                        </div>
                    </div>

                    <!-- Channel -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Channel
                        </div>
                        <div class="col-span-2">
                            <Badge
                                :variant="selectedRelease.channel === 'stable'
                                        ? 'default'
                                        : 'secondary'
                                    "
                                class="capitalize"
                            >
                                {{ selectedRelease.channel }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Major Release -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Major Release
                        </div>
                        <div class="col-span-2">
                            <Badge
                                v-if="selectedRelease.major"
                                variant="destructive"
                                class="text-xs"
                            >
                                Major
                            </Badge>
                            <span
                                v-else
                                class="text-sm text-muted-foreground"
                            >
                                Minor/Patch
                            </span>
                        </div>
                    </div>

                    <!-- Commit Hash -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Commit Hash
                        </div>
                        <div class="col-span-2">
                            <code
                                class="block break-all rounded bg-muted px-2 py-1 font-mono text-xs"
                            >
                                {{ selectedRelease.commit_hash }}
                            </code>
                        </div>
                    </div>

                    <!-- Published At -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Published At
                        </div>
                        <div class="col-span-2 text-sm text-muted-foreground">
                            <span v-if="selectedRelease.published_at">
                                {{
                                    new Date(
                                        selectedRelease.published_at,
                                    ).toLocaleString('en-US', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                    })
                                }}
                            </span>
                            <span v-else> Not published yet </span>
                        </div>
                    </div>

                    <!-- Created By -->
                    <div
                        v-if="selectedRelease.created_by"
                        class="grid grid-cols-3 items-start gap-4"
                    >
                        <div class="text-sm font-medium text-muted-foreground">
                            Created By
                        </div>
                        <div class="col-span-2 text-sm">
                            {{ selectedRelease.created_by }}
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
                                    selectedRelease.created_at,
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

                    <!-- Release Notes -->
                    <div
                        v-if="selectedRelease.notes"
                        class="grid grid-cols-3 items-start gap-4"
                    >
                        <div class="text-sm font-medium text-muted-foreground">
                            Release Notes
                        </div>
                        <div class="col-span-2">
                            <div
                                class="prose prose-sm dark:prose-invert max-w-none rounded bg-muted p-4"
                                v-html="parsedNotes"
                            />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-2 pt-4">
                        <Button
                            variant="outline"
                            @click="downloadArtifacts(selectedRelease)"
                        >
                            <Download class="mr-2 h-4 w-4" />
                            Download Artifacts
                        </Button>
                        <Button @click="publishRelease(selectedRelease)">
                            <Rocket class="mr-2 h-4 w-4" />
                            Publish to Channel
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
