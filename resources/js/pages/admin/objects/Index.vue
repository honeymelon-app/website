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
import { Button } from '@/components/ui/button';
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
import objectsRoute from '@/routes/admin/objects';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
    Copy,
    Download,
    ExternalLink,
    MoreHorizontal,
    Trash2,
} from 'lucide-vue-next';
import { h, ref } from 'vue';

interface R2Object {
    path: string;
    name: string;
    size: number;
    last_modified: number;
    url: string;
}

interface Props {
    objects: R2Object[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Objects',
        href: objectsRoute.index().url,
    },
];

// Delete confirmation state
const showDeleteDialog = ref(false);
const objectToDelete = ref<R2Object | null>(null);

// Helper to format file size
const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
};

// Helper to format date
const formatDate = (timestamp: number): string => {
    const date = new Date(timestamp * 1000);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const columns: Column<R2Object>[] = [
    {
        key: 'name',
        label: 'Name',
        render: (row: R2Object) => {
            return h('div', { class: 'font-medium' }, row.name);
        },
    },
    {
        key: 'path',
        label: 'Path',
        class: 'text-xs text-muted-foreground font-mono',
        render: (row: R2Object) => {
            return h('div', { class: 'max-w-md truncate' }, row.path);
        },
    },
    {
        key: 'size',
        label: 'Size',
        headerClass: 'w-[120px]',
        render: (row: R2Object) => {
            return h(
                'div',
                { class: 'text-sm' },
                formatFileSize(row.size),
            );
        },
    },
    {
        key: 'last_modified',
        label: 'Last Modified',
        headerClass: 'w-[180px]',
        render: (row: R2Object) => {
            return h(
                'time',
                {
                    datetime: new Date(row.last_modified * 1000).toISOString(),
                    class: 'text-sm text-muted-foreground',
                },
                formatDate(row.last_modified),
            );
        },
    },
    {
        key: 'actions',
        label: '',
        headerClass: 'w-[50px]',
        render: (row: R2Object) => {
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
                                            onClick: () => copyUrl(row),
                                        },
                                        {
                                            default: () => [
                                                h(Copy, {
                                                    class: 'mr-2 h-4 w-4',
                                                }),
                                                'Copy URL',
                                            ],
                                        },
                                    ),
                                    h(
                                        DropdownMenuItem,
                                        {
                                            onClick: () => downloadObject(row),
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
                                            onClick: () => openUrl(row),
                                        },
                                        {
                                            default: () => [
                                                h(ExternalLink, {
                                                    class: 'mr-2 h-4 w-4',
                                                }),
                                                'Open in Browser',
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
const copyUrl = async (object: R2Object): Promise<void> => {
    try {
        await navigator.clipboard.writeText(object.url);
        console.log('URL copied to clipboard:', object.url);
        // You could add a toast notification here
    } catch (err) {
        console.error('Failed to copy URL:', err);
    }
};

const downloadObject = (object: R2Object): void => {
    window.open(object.url, '_blank');
};

const openUrl = (object: R2Object): void => {
    window.open(object.url, '_blank');
};

const confirmDelete = (object: R2Object): void => {
    objectToDelete.value = object;
    showDeleteDialog.value = true;
};

const handleDelete = (): void => {
    if (!objectToDelete.value) return;

    router.delete(objectsRoute.destroy(objectToDelete.value.path).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            objectToDelete.value = null;
        },
        onError: () => {
            console.error('Failed to delete object');
        },
    });
};

const cancelDelete = (): void => {
    showDeleteDialog.value = false;
    objectToDelete.value = null;
};
</script>

<template>
    <Head title="Objects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex flex-col gap-6">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-2">
                        <h3 class="text-2xl font-semibold tracking-tight">
                            Objects
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            View and manage all objects stored in your R2 bucket.
                        </p>
                    </div>
                </div>

                <DataTable
                    :columns="columns"
                    :data="props.objects"
                    empty-message="No objects found in the R2 bucket."
                />
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <AlertDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete Object</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to delete "{{ objectToDelete?.name }}"?
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
