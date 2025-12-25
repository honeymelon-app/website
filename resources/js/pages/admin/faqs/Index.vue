<script setup lang="ts">
import {
    AdminLoadingState,
    AdminPage,
    AdminSection,
    AdminToolbar,
    ConfirmDialog,
} from '@/components/admin';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import {
    DataTableBulkActions,
    DataTablePagination,
    DataTableRoot,
    TableFilters,
    type FilterConfig,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { useDataTable } from '@/composables';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import faqsRoute from '@/routes/admin/faqs';
import type { BreadcrumbItem } from '@/types';
import type { FilterParams, PaginatedResponse } from '@/types/api';
import type { Faq } from '@/types/resources';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Save, Trash2 } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { columns } from './columns';

interface Filters {
    search?: string;
    is_active?: boolean;
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
    faqs: PaginatedResponse<Faq>;
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
        title: 'FAQs',
        href: faqsRoute.index().url,
    },
];

// Create/Edit FAQ modal state
const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const selectedFaq = ref<Faq | null>(null);

// Delete dialog state
const isDeleteDialogOpen = ref(false);
const faqToDelete = ref<Faq | null>(null);
const isDeleting = ref(false);

// Bulk delete dialog state
const isBulkDeleteDialogOpen = ref(false);
const isBulkDeleting = ref(false);

// Create FAQ form
const createForm = useForm({
    question: '',
    answer: '',
    order: 0,
    is_active: true,
});

// Edit FAQ form
const editForm = useForm({
    question: '',
    answer: '',
    order: 0,
    is_active: true,
});

// Reset edit form when dialog closes
watch(isEditDialogOpen, (isOpen) => {
    if (!isOpen) {
        selectedFaq.value = null;
        editForm.reset();
    }
});

// Filter configuration for TableFilters component
const filterConfig: FilterConfig[] = [
    {
        key: 'search',
        label: 'Search...',
        type: 'text',
        placeholder: 'Search questions or answers...',
    },
    {
        key: 'is_active',
        label: 'Status',
        type: 'select',
        options: [
            { label: 'Active', value: 'true' },
            { label: 'Inactive', value: 'false' },
        ],
    },
];

// Reactive filter state that syncs with props
const filterState = computed<FilterParams>(() => ({
    search: props.filters.search,
    is_active: props.filters.is_active?.toString(),
}));

// Actions for the columns
const editFaq = (faq: Faq): void => {
    selectedFaq.value = faq;
    // Populate the edit form immediately
    editForm.question = faq.question;
    editForm.answer = faq.answer;
    editForm.order = faq.order;
    editForm.is_active = Boolean(faq.is_active);
    // Clear any previous errors
    editForm.clearErrors();
    // Open the dialog
    isEditDialogOpen.value = true;
};

const deleteFaq = (faq: Faq): void => {
    faqToDelete.value = faq;
    isDeleteDialogOpen.value = true;
};

const confirmDeleteFaq = (): void => {
    if (!faqToDelete.value) return;

    isDeleting.value = true;
    router.delete(faqsRoute.destroy(faqToDelete.value.id).url, {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
            isDeleteDialogOpen.value = false;
            faqToDelete.value = null;
        },
    });
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
    data: computed(() => props.faqs),
    columns,
    sorting: computed(() => props.sorting),
    filters: computed(() => props.filters as Record<string, unknown>),
    pagination: computed(() => props.pagination),
    indexUrl: faqsRoute.index().url,
    getRowId: (row) => row.id.toString(),
    enableRowSelection: true,
    meta: {
        editFaq,
        deleteFaq,
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
    if (selectedCount.value === 0) return;
    isBulkDeleteDialogOpen.value = true;
};

const confirmBulkDelete = () => {
    const faqs = selectedRows.value;
    if (faqs.length === 0) return;

    isBulkDeleting.value = true;

    let completed = 0;
    faqs.forEach((faq) => {
        router.delete(faqsRoute.destroy(faq.id).url, {
            preserveScroll: true,
            onFinish: () => {
                completed++;
                if (completed === faqs.length) {
                    isBulkDeleting.value = false;
                    isBulkDeleteDialogOpen.value = false;
                    clearSelection();
                }
            },
        });
    });
};

// Handle create FAQ
const handleCreateFaq = (): void => {
    createForm.post(faqsRoute.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
        },
    });
};

// Handle edit FAQ
const handleEditFaq = (): void => {
    if (!selectedFaq.value) return;

    editForm.put(faqsRoute.update(selectedFaq.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            isEditDialogOpen.value = false;
            selectedFaq.value = null;
        },
    });
};

// Get next order number
const nextOrderNumber = computed(() => {
    if (props.faqs.data.length === 0) return 0;
    return Math.max(...props.faqs.data.map((f) => f.order)) + 1;
});

// Handle create dialog open
const openCreateDialog = (): void => {
    createForm.reset();
    createForm.order = nextOrderNumber.value;
    isCreateDialogOpen.value = true;
};
</script>

<template>
    <Head title="FAQs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AdminPage>
            <AdminSection>
                <!-- Header + Filters -->
                <AdminToolbar
                    title="FAQs"
                    subtitle="Manage frequently asked questions"
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
                        <Button @click="openCreateDialog">
                            <Plus class="mr-2 h-4 w-4" />
                            Create FAQ
                        </Button>
                    </template>
                </AdminToolbar>

                <!-- Bulk Actions Toolbar -->
                <DataTableBulkActions
                    :selected-count="selectedCount"
                    item-label="FAQ"
                    @clear="clearSelection"
                >
                    <Button
                        variant="destructive"
                        size="sm"
                        :disabled="selectedCount === 0"
                        @click="bulkDelete"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete Selected
                    </Button>
                </DataTableBulkActions>

                <!-- Table -->
                <div class="space-y-4">
                    <AdminLoadingState v-if="isInitialLoad" :rows="5" />
                    <DataTableRoot v-else :table="table" :columns="columns">
                        <template #empty>
                            <AdminEmptyState
                                icon="MessageCircleQuestion"
                                title="No FAQs yet"
                                description="Create your first FAQ to help your users."
                            >
                                <Button @click="isCreateDialogOpen = true">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Create FAQ
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

        <!-- Create FAQ Dialog -->
        <Dialog v-model:open="isCreateDialogOpen">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Create FAQ</DialogTitle>
                    <DialogDescription>
                        Add a new frequently asked question to your site.
                    </DialogDescription>
                </DialogHeader>

                <form class="space-y-4" @submit.prevent="handleCreateFaq">
                    <div class="grid gap-2">
                        <Label for="create-question">
                            Question
                            <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="create-question"
                            v-model="createForm.question"
                            type="text"
                            placeholder="What is your question?"
                            :disabled="createForm.processing"
                            required
                        />
                        <p
                            v-if="createForm.errors.question"
                            class="text-sm text-destructive"
                        >
                            {{ createForm.errors.question }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="create-answer">
                            Answer
                            <span class="text-destructive">*</span>
                        </Label>
                        <Textarea
                            id="create-answer"
                            v-model="createForm.answer"
                            placeholder="Provide a detailed answer..."
                            :disabled="createForm.processing"
                            rows="6"
                            required
                        />
                        <p
                            v-if="createForm.errors.answer"
                            class="text-sm text-destructive"
                        >
                            {{ createForm.errors.answer }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="create-order">
                                Order
                                <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="create-order"
                                v-model.number="createForm.order"
                                type="number"
                                min="0"
                                :disabled="createForm.processing"
                                required
                            />
                            <p class="text-sm text-muted-foreground">
                                Lower numbers appear first
                            </p>
                            <p
                                v-if="createForm.errors.order"
                                class="text-sm text-destructive"
                            >
                                {{ createForm.errors.order }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-is-active">Status</Label>
                            <div class="flex items-center space-x-2">
                                <Switch
                                    id="create-is-active"
                                    v-model="createForm.is_active"
                                    :disabled="createForm.processing"
                                />
                                <Label
                                    for="create-is-active"
                                    class="font-normal"
                                >
                                    {{
                                        createForm.is_active
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </Label>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                Show this FAQ on the website
                            </p>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="createForm.processing"
                            @click="isCreateDialogOpen = false"
                        >
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="createForm.processing">
                            <Save class="mr-2 h-4 w-4" />
                            Create FAQ
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Edit FAQ Dialog -->
        <Dialog v-model:open="isEditDialogOpen">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Edit FAQ</DialogTitle>
                    <DialogDescription>
                        Update the FAQ details below.
                    </DialogDescription>
                </DialogHeader>

                <form class="space-y-4" @submit.prevent="handleEditFaq">
                    <div class="grid gap-2">
                        <Label for="edit-question">
                            Question
                            <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="edit-question"
                            v-model="editForm.question"
                            type="text"
                            placeholder="What is your question?"
                            :disabled="editForm.processing"
                            required
                        />
                        <p
                            v-if="editForm.errors.question"
                            class="text-sm text-destructive"
                        >
                            {{ editForm.errors.question }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-answer">
                            Answer
                            <span class="text-destructive">*</span>
                        </Label>
                        <Textarea
                            id="edit-answer"
                            v-model="editForm.answer"
                            placeholder="Provide a detailed answer..."
                            :disabled="editForm.processing"
                            rows="6"
                            required
                        />
                        <p
                            v-if="editForm.errors.answer"
                            class="text-sm text-destructive"
                        >
                            {{ editForm.errors.answer }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="edit-order">
                                Order
                                <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="edit-order"
                                v-model.number="editForm.order"
                                type="number"
                                min="0"
                                :disabled="editForm.processing"
                                required
                            />
                            <p class="text-sm text-muted-foreground">
                                Lower numbers appear first
                            </p>
                            <p
                                v-if="editForm.errors.order"
                                class="text-sm text-destructive"
                            >
                                {{ editForm.errors.order }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-is-active">Status</Label>
                            <div class="flex items-center space-x-2">
                                <Switch
                                    :key="`edit-switch-${selectedFaq?.id}`"
                                    id="edit-is-active"
                                    v-model="editForm.is_active"
                                    :disabled="editForm.processing"
                                />
                                <Label for="edit-is-active" class="font-normal">
                                    {{
                                        editForm.is_active
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </Label>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                Show this FAQ on the website
                            </p>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="editForm.processing"
                            @click="isEditDialogOpen = false"
                        >
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="editForm.processing">
                            <Save class="mr-2 h-4 w-4" />
                            Update FAQ
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Single Delete Confirmation Dialog -->
        <ConfirmDialog
            v-model:open="isDeleteDialogOpen"
            title="Delete FAQ"
            confirm-label="Delete FAQ"
            :loading="isDeleting"
            :show-trigger="false"
            @confirm="confirmDeleteFaq"
        >
            <p class="mb-4">Are you sure you want to delete this FAQ?</p>
            <p
                v-if="faqToDelete"
                class="mb-4 rounded bg-muted p-3 text-sm font-medium"
            >
                {{ faqToDelete.question }}
            </p>
            <ul class="list-disc space-y-1 pl-6 text-sm text-muted-foreground">
                <li>This will remove the FAQ from your website</li>
                <li>This action cannot be undone</li>
            </ul>
        </ConfirmDialog>

        <!-- Bulk Delete Confirmation Dialog -->
        <ConfirmDialog
            v-model:open="isBulkDeleteDialogOpen"
            title="Delete Selected FAQs"
            confirm-label="Delete All"
            :loading="isBulkDeleting"
            :show-trigger="false"
            @confirm="confirmBulkDelete"
        >
            <p class="mb-4">
                Are you sure you want to delete
                <strong>{{ selectedCount }}</strong> FAQ(s)?
            </p>
            <ul class="list-disc space-y-1 pl-6 text-sm text-muted-foreground">
                <li>These FAQs will be removed from your website</li>
                <li>This action cannot be undone</li>
            </ul>
        </ConfirmDialog>
    </AppLayout>
</template>
