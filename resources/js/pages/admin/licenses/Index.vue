<script setup lang="ts">
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import { AdminLoadingState, AdminPage, AdminSection, AdminToolbar, ConfirmDialog } from '@/components/admin';
import {
    DataTableBulkActions,
    DataTablePagination,
    DataTableRoot,
    TableFilters,
    type FilterConfig,
} from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useDataTable } from '@/composables';
import { useCopyToClipboard } from '@/composables/useCopyToClipboard';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDateTime } from '@/lib/formatters';
import { getStatusVariant } from '@/lib/variants';
import { dashboard } from '@/routes';
import licensesRoute from '@/routes/admin/licenses';
import type { BreadcrumbItem } from '@/types';
import type { FilterParams, PaginatedResponse } from '@/types/api';
import type { License } from '@/types/resources';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Check, Copy, Download, Plus, ShieldOff } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { columns } from './columns';

interface Filters {
    search?: string;
    status?: string;
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
    licenses: PaginatedResponse<License>;
    filters: Filters;
    sorting: Sorting;
    pagination: Pagination;
    license_key?: string;
    license_email?: string;
    available_versions: number[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Licenses',
        href: licensesRoute.index().url,
    },
];

// Issue license modal state
const isIssueDialogOpen = ref(false);

// License details modal state
const isDetailsDialogOpen = ref(false);
const selectedLicense = ref<License | null>(null);

// Revoke dialog state
const isRevokeDialogOpen = ref(false);
const licenseToRevoke = ref<License | null>(null);
const isRevoking = ref(false);

// Bulk revoke dialog state
const isBulkRevokeDialogOpen = ref(false);
const isBulkRevoking = ref(false);

// Success modal state
const isSuccessDialogOpen = ref(!!props.license_key);
const generatedLicenseKey = ref(props.license_key || '');
const generatedLicenseEmail = ref(props.license_email || '');

// Use clipboard composable
const { copy: copyToClipboard, copied: isCopied } = useCopyToClipboard();

const sortedMajorVersions = computed(() =>
    [...(props.available_versions ?? [])].sort((a, b) => b - a),
);

const versionOptions = computed(() => [
    ...sortedMajorVersions.value.map((major) => ({
        value: major.toString(),
        label: `Version ${major}.x`,
    })),
    { value: '999', label: 'Lifetime (All Versions)' },
]);

const defaultMaxMajorVersion = versionOptions.value[0]?.value ?? '1';

// Issue license form
const issueForm = useForm({
    email: '',
    max_major_version: defaultMaxMajorVersion,
});

// Filter configuration for TableFilters component
const filterConfig: FilterConfig[] = [
    {
        key: 'search',
        label: 'Search...',
        type: 'text',
        placeholder: 'Search by ID...',
    },
    {
        key: 'status',
        label: 'Status',
        type: 'select',
        options: [
            { label: 'Active', value: 'active' },
            { label: 'Revoked', value: 'revoked' },
            { label: 'Pending', value: 'pending' },
        ],
    },
];

// Reactive filter state that syncs with props
const filterState = computed<FilterParams>(() => ({
    search: props.filters.search,
    status: props.filters.status,
}));

// Actions for the columns
const viewLicense = (license: License): void => {
    selectedLicense.value = license;
    isDetailsDialogOpen.value = true;
};

const revokeLicense = (license: License): void => {
    licenseToRevoke.value = license;
    isRevokeDialogOpen.value = true;
};

const confirmRevokeLicense = (): void => {
    if (!licenseToRevoke.value) return;

    isRevoking.value = true;
    router.post(
        '/api/admin/licenses/revoke',
        { license_id: licenseToRevoke.value.id },
        {
            preserveScroll: true,
            onFinish: () => {
                isRevoking.value = false;
                isRevokeDialogOpen.value = false;
                licenseToRevoke.value = null;
            },
        },
    );
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
    data: computed(() => props.licenses),
    columns,
    sorting: computed(() => props.sorting),
    filters: computed(() => props.filters as Record<string, unknown>),
    pagination: computed(() => props.pagination),
    indexUrl: licensesRoute.index().url,
    getRowId: (row) => row.id,
    enableRowSelection: true,
    meta: {
        viewLicense,
        revokeLicense,
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

// Bulk revoke action
const activeSelectedCount = computed(
    () => selectedRows.value.filter((l) => l.status === 'active').length,
);

const bulkRevoke = () => {
    if (activeSelectedCount.value === 0) {
        return;
    }
    isBulkRevokeDialogOpen.value = true;
};

const confirmBulkRevoke = () => {
    const licenses = selectedRows.value.filter((l) => l.status === 'active');

    if (licenses.length === 0) return;

    isBulkRevoking.value = true;

    let completed = 0;
    licenses.forEach((license) => {
        router.post(
            '/api/admin/licenses/revoke',
            { license_id: license.id },
            {
                preserveScroll: true,
                onFinish: () => {
                    completed++;
                    if (completed === licenses.length) {
                        isBulkRevoking.value = false;
                        isBulkRevokeDialogOpen.value = false;
                        clearSelection();
                    }
                },
            },
        );
    });
};

// Export selected licenses to CSV
const exportSelected = () => {
    const licenses = selectedRows.value;

    const headers = [
        'License ID',
        'Status',
        'Max Version',
        'Activations',
        'Device ID',
        'Issued',
        'Created',
    ];
    const csvContent = [
        headers.join(','),
        ...licenses.map((license) =>
            [
                license.id,
                license.status,
                license.max_major_version === 999
                    ? 'Lifetime'
                    : `v${license.max_major_version}.x`,
                license.activation_count ?? 0,
                license.device_id || '',
                license.issued_at || 'Not issued',
                license.created_at,
            ].join(','),
        ),
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `licenses-export-${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
};

const handleIssueLicense = (): void => {
    issueForm.post(licensesRoute.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            isIssueDialogOpen.value = false;
            issueForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Licenses" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AdminPage>
            <AdminSection>
                <!-- Header + Filters -->
                <AdminToolbar
                    title="Licenses"
                    subtitle="Manage and issue product licenses for customers."
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
                        <Dialog v-model:open="isIssueDialogOpen">
                            <DialogTrigger as-child>
                                <Button>
                                    <Plus class="mr-2 h-4 w-4" />
                                    Issue License
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Issue New License</DialogTitle>
                                    <DialogDescription>
                                        Create a new license key for a customer.
                                        The license key will be generated
                                        automatically.
                                    </DialogDescription>
                                </DialogHeader>

                                <form
                                    @submit.prevent="handleIssueLicense"
                                    class="space-y-4"
                                >
                                    <div class="grid gap-2">
                                        <Label for="email"
                                            >Customer Email</Label
                                        >
                                        <Input
                                            id="email"
                                            v-model="issueForm.email"
                                            type="email"
                                            placeholder="customer@example.com"
                                            required
                                        />
                                        <p
                                            v-if="issueForm.errors.email"
                                            class="text-sm text-destructive"
                                        >
                                            {{ issueForm.errors.email }}
                                        </p>
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="max_major_version"
                                            >Max Major Version</Label
                                        >
                                        <Select
                                            v-model="issueForm.max_major_version
                                                "
                                        >
                                            <SelectTrigger
                                                id="max_major_version"
                                            >
                                                <SelectValue
                                                    placeholder="Select version"
                                                />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="option in versionOptions"
                                                    :key="option.value"
                                                    :value="option.value"
                                                >
                                                    {{ option.label }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <p
                                            v-if="
                                                issueForm.errors
                                                    .max_major_version
                                            "
                                            class="text-sm text-destructive"
                                        >
                                            {{
                                                issueForm.errors
                                                    .max_major_version
                                            }}
                                        </p>
                                    </div>

                                    <DialogFooter>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            @click="isIssueDialogOpen = false"
                                        >
                                            Cancel
                                        </Button>
                                        <Button
                                            type="submit"
                                            :disabled="issueForm.processing"
                                        >
                                            {{
                                                issueForm.processing
                                                    ? 'Issuing...'
                                                    : 'Issue License'
                                            }}
                                        </Button>
                                    </DialogFooter>
                                </form>
                            </DialogContent>
                        </Dialog>

                        <!-- Success Modal -->
                        <Dialog v-model:open="isSuccessDialogOpen">
                            <DialogContent class="sm:max-w-2xl">
                                <DialogHeader>
                                    <DialogTitle
                                        >License Issued
                                        Successfully</DialogTitle
                                    >
                                    <DialogDescription>
                                        The license has been generated and is
                                        ready to use. Make sure to copy it now
                                        as it won't be shown again.
                                    </DialogDescription>
                                </DialogHeader>

                                <div class="space-y-4">
                                    <div class="grid gap-2">
                                        <Label>Customer Email</Label>
                                        <Input
                                            :value="generatedLicenseEmail"
                                            readonly
                                            class="font-mono text-sm"
                                        />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label>License Key</Label>
                                        <div class="flex gap-2">
                                            <Input
                                                :value="generatedLicenseKey"
                                                readonly
                                                class="flex-1 font-mono text-xs"
                                            />
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="icon"
                                                @click="copyToClipboard"
                                            >
                                                <Check
                                                    v-if="isCopied"
                                                    class="h-4 w-4"
                                                />
                                                <Copy v-else class="h-4 w-4" />
                                            </Button>
                                        </div>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            This key will only be shown once.
                                            Make sure to copy and save it
                                            securely.
                                        </p>
                                    </div>
                                </div>

                                <DialogFooter>
                                    <Button @click="isSuccessDialogOpen = false"
                                        >Close</Button
                                    >
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>
                    </template>
                </AdminToolbar>

                <!-- Bulk Actions Toolbar -->
                <DataTableBulkActions
                    :selected-count="selectedCount"
                    item-label="license"
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
                    <Button
                        variant="destructive"
                        size="sm"
                        class="h-8"
                        :disabled="activeSelectedCount === 0"
                        @click="bulkRevoke"
                    >
                        <ShieldOff class="mr-2 h-4 w-4" />
                        Revoke Selected ({{ activeSelectedCount }})
                    </Button>
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
                                icon="KeyRound"
                                title="No licenses yet"
                                description="Issue your first license to get started."
                            >
                                <Button @click="isIssueDialogOpen = true">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Issue License
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

        <!-- License Details Modal -->
        <Dialog v-model:open="isDetailsDialogOpen">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>License Details</DialogTitle>
                    <DialogDescription
                        >Detailed information about the selected
                        license.</DialogDescription
                    >
                </DialogHeader>

                <div v-if="selectedLicense" class="grid gap-6 py-4">
                    <!-- License ID -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            License ID
                        </div>
                        <div class="col-span-2">
                            <code
                                class="block rounded bg-muted px-2 py-1 font-mono text-xs break-all"
                            >
                                {{ selectedLicense.id }}
                            </code>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Status
                        </div>
                        <div class="col-span-2">
                            <Badge
                                :variant="getStatusVariant(selectedLicense.status)
                                    "
                                class="capitalize"
                            >
                                {{ selectedLicense.status }}
                            </Badge>
                        </div>
                    </div>

                    <!-- License Key -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            License Key
                        </div>
                        <div class="col-span-2">
                            <code
                                class="block rounded bg-muted px-2 py-1 font-mono text-xs break-all"
                            >
                                {{ selectedLicense.key }}
                            </code>
                        </div>
                    </div>

                    <!-- Key Hash -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Key Hash
                        </div>
                        <div class="col-span-2">
                            <code
                                class="block rounded bg-muted px-2 py-1 font-mono text-xs break-all"
                            >
                                {{ selectedLicense.key_hash }}
                            </code>
                        </div>
                    </div>

                    <!-- Max Major Version -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Max Major Version
                        </div>
                        <div class="col-span-2 text-sm">
                            {{
                                selectedLicense.max_major_version === 999
                                    ? 'Lifetime (All versions)'
                                    : `v${selectedLicense.max_major_version}.x`
                            }}
                        </div>
                    </div>

                    <!-- Issued At -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Issued At
                        </div>
                        <div class="col-span-2 text-sm text-muted-foreground">
                            <span v-if="selectedLicense.issued_at">
                                {{ formatDateTime(selectedLicense.issued_at) }}
                            </span>
                            <span v-else>Not issued yet</span>
                        </div>
                    </div>

                    <!-- Created At -->
                    <div class="grid grid-cols-3 items-start gap-4">
                        <div class="text-sm font-medium text-muted-foreground">
                            Created At
                        </div>
                        <div class="col-span-2 text-sm text-muted-foreground">
                            {{ formatDateTime(selectedLicense.created_at) }}
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        v-if="selectedLicense.status === 'active'"
                        class="flex justify-end gap-2 pt-4"
                    >
                        <Button
                            variant="destructive"
                            @click="
                                revokeLicense(selectedLicense);
                            isDetailsDialogOpen = false;
                            "
                        >
                            <ShieldOff class="mr-2 h-4 w-4" />
                            Revoke License
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Single Revoke Confirmation Dialog -->
        <ConfirmDialog
            v-model:open="isRevokeDialogOpen"
            title="Revoke License"
            confirm-label="Revoke License"
            :loading="isRevoking"
            :show-trigger="false"
            @confirm="confirmRevokeLicense"
        >
            <p class="mb-4">
                Are you sure you want to revoke license
                <code class="rounded bg-muted px-1.5 py-0.5 font-mono text-xs">
                    {{ licenseToRevoke?.id.substring(0, 8) }}... </code
                >?
            </p>
            <ul class="list-disc space-y-1 pl-6 text-sm text-muted-foreground">
                <li>The license will no longer be valid for activation</li>
                <li>Already activated installations will continue to work</li>
                <li>This action cannot be undone</li>
            </ul>
        </ConfirmDialog>

        <!-- Bulk Revoke Confirmation Dialog -->
        <ConfirmDialog
            v-model:open="isBulkRevokeDialogOpen"
            title="Revoke Selected Licenses"
            confirm-label="Revoke All"
            :loading="isBulkRevoking"
            :show-trigger="false"
            @confirm="confirmBulkRevoke"
        >
            <p class="mb-4">
                Are you sure you want to revoke
                <strong>{{ activeSelectedCount }}</strong> active license(s)?
            </p>
            <ul class="list-disc space-y-1 pl-6 text-sm text-muted-foreground">
                <li>These licenses will no longer be valid for activation</li>
                <li>Already activated installations will continue to work</li>
                <li>This action cannot be undone</li>
            </ul>
        </ConfirmDialog>
    </AppLayout>
</template>
