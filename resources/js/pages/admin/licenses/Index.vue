<script setup lang="ts">
import { ConfirmDialog } from '@/components/admin';
import { DataTable, type Column } from '@/components/data-table';
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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useCopyToClipboard } from '@/composables/useCopyToClipboard';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, formatDateTime, truncateId } from '@/lib/formatters';
import { getStatusVariant } from '@/lib/variants';
import { dashboard } from '@/routes';
import licenses from '@/routes/admin/licenses';
import type { BreadcrumbItem } from '@/types';
import type { License, PaginatedResponse } from '@/types/resources';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Check,
    Copy,
    Eye,
    MoreHorizontal,
    Plus,
    ShieldOff,
} from 'lucide-vue-next';
import { computed, h, ref } from 'vue';

interface Props {
    licenses: PaginatedResponse<License>;
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
        href: licenses.index().url,
    },
];

// Issue license modal state
const isIssueDialogOpen = ref(false);

// License details modal state
const isDetailsDialogOpen = ref(false);
const selectedLicense = ref<License | null>(null);

// Success modal state
const isSuccessDialogOpen = ref(!!props.license_key);
const generatedLicenseKey = ref(props.license_key || '');
const generatedLicenseEmail = ref(props.license_email || '');

// Revoke dialog state
const isRevokeDialogOpen = ref(false);
const licenseToRevoke = ref<License | null>(null);
const revokeForm = useForm({
    reason: '',
});

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

// Column definitions
const columns: Column<License>[] = [
    {
        key: 'id',
        label: 'License ID',
        headerClass: 'w-[120px]',
        render: (row: License) => {
            return h(
                'div',
                { class: 'font-mono text-sm text-muted-foreground' },
                truncateId(row.id),
            );
        },
    },
    {
        key: 'status',
        label: 'Status',
        headerClass: 'w-[100px]',
        render: (row: License) => {
            return h(
                Badge,
                { variant: getStatusVariant(row.status), class: 'capitalize' },
                { default: () => row.status },
            );
        },
    },
    {
        key: 'max_major_version',
        label: 'Max Version',
        headerClass: 'w-[120px]',
        render: (row: License) => {
            return h(
                'div',
                { class: 'text-sm text-muted-foreground' },
                row.max_major_version === 999
                    ? 'Lifetime'
                    : `v${row.max_major_version}.x`,
            );
        },
    },
    {
        key: 'issued_at',
        label: 'Issued',
        headerClass: 'w-[140px]',
        render: (row: License) => {
            if (!row.issued_at) {
                return h(
                    'div',
                    { class: 'text-sm text-muted-foreground' },
                    'Not issued',
                );
            }
            return h('div', { class: 'text-sm' }, formatDate(row.issued_at));
        },
    },
    {
        key: 'activation_count',
        label: 'Activations',
        headerClass: 'w-[110px]',
        render: (row: License) => {
            return h(
                Badge,
                { variant: 'outline', class: 'font-mono text-xs' },
                {
                    default: () =>
                        `${row.activation_count} device${row.activation_count === 1 ? '' : 's'}`,
                },
            );
        },
    },
    {
        key: 'device_id',
        label: 'Device ID',
        headerClass: 'w-[160px]',
        render: (row: License) => {
            if (!row.device_id) {
                return h(
                    'span',
                    { class: 'text-muted-foreground text-sm' },
                    'Not activated',
                );
            }

            return h(
                'div',
                { class: 'font-mono text-xs text-muted-foreground truncate' },
                truncateId(row.device_id),
            );
        },
    },
    {
        key: 'created_at',
        label: 'Created',
        headerClass: 'w-[140px]',
        render: (row: License) => {
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
        render: (row: License) => {
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
                                default: () =>
                                    [
                                        h(
                                            DropdownMenuLabel,
                                            {},
                                            { default: () => 'Actions' },
                                        ),
                                        h(
                                            DropdownMenuItem,
                                            {
                                                onClick: () => viewLicense(row),
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
                                        row.status === 'active'
                                            ? h(DropdownMenuSeparator)
                                            : null,
                                        row.status === 'active'
                                            ? h(
                                                DropdownMenuItem,
                                                {
                                                    onClick: () =>
                                                        revokeLicense(row),
                                                    class: 'text-destructive focus:text-destructive',
                                                },
                                                {
                                                    default: () => [
                                                        h(ShieldOff, {
                                                            class: 'mr-2 h-4 w-4',
                                                        }),
                                                        'Revoke License',
                                                    ],
                                                },
                                            )
                                            : null,
                                    ].filter(Boolean),
                            },
                        ),
                    ],
                },
            );
        },
    },
];

// Actions
const viewLicense = (license: License): void => {
    selectedLicense.value = license;
    isDetailsDialogOpen.value = true;
};

const revokeLicense = (license: License): void => {
    licenseToRevoke.value = license;
    revokeForm.reset();
    isRevokeDialogOpen.value = true;
};

const processRevoke = (): void => {
    if (!licenseToRevoke.value) {
        return;
    }

    revokeForm.post(`/admin/licenses/${licenseToRevoke.value.id}/revoke`, {
        preserveScroll: true,
        onSuccess: () => {
            isRevokeDialogOpen.value = false;
            licenseToRevoke.value = null;
            revokeForm.reset();
        },
    });
};

const handleIssueLicense = (): void => {
    issueForm.post(licenses.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            isIssueDialogOpen.value = false;
            issueForm.reset();
        },
    });
};

const handlePageChange = (page: number): void => {
    router.visit(licenses.index().url, {
        data: { page },
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Licenses" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6"
        >
            <div class="flex flex-col gap-6">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-2">
                        <h3 class="text-2xl font-semibold tracking-tight">
                            Licenses
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            Manage and issue product licenses for customers.
                        </p>
                    </div>

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
                                    Create a new license key for a customer. The
                                    license key will be generated automatically.
                                </DialogDescription>
                            </DialogHeader>

                            <form
                                @submit.prevent="handleIssueLicense"
                                class="space-y-4"
                            >
                                <div class="space-y-2">
                                    <Label for="email">Customer Email</Label>
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

                                <div class="space-y-2">
                                    <Label for="max_major_version"
                                        >Max Major Version</Label
                                    >
                                    <Select
                                        v-model="issueForm.max_major_version"
                                    >
                                        <SelectTrigger id="max_major_version">
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
                                            issueForm.errors.max_major_version
                                        "
                                        class="text-sm text-destructive"
                                    >
                                        {{ issueForm.errors.max_major_version }}
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
                                    >License Issued Successfully</DialogTitle
                                >
                                <DialogDescription>
                                    The license has been generated and is ready
                                    to use. Make sure to copy it now as it won't
                                    be shown again.
                                </DialogDescription>
                            </DialogHeader>

                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <Label>Customer Email</Label>
                                    <Input
                                        :value="generatedLicenseEmail"
                                        readonly
                                        class="font-mono text-sm"
                                    />
                                </div>

                                <div class="space-y-2">
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
                                    <p class="text-xs text-muted-foreground">
                                        This key will only be shown once. Make
                                        sure to copy and save it securely.
                                    </p>
                                </div>
                            </div>

                            <DialogFooter>
                                <Button @click="isSuccessDialogOpen = false">
                                    Close
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>

                <DataTable
                    :columns="columns"
                    :data="props.licenses.data"
                    :meta="props.licenses.meta"
                    empty-message="No licenses found. Issue your first license to get started."
                    @page-change="handlePageChange"
                />
            </div>
        </div>

        <!-- License Details Modal -->
        <Dialog v-model:open="isDetailsDialogOpen">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>License Details</DialogTitle>
                    <DialogDescription>
                        Detailed information about the selected license.
                    </DialogDescription>
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
                            <span v-else> Not issued yet </span>
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

        <!-- Revoke Confirmation Dialog -->
        <ConfirmDialog
            v-model:open="isRevokeDialogOpen"
            title="Revoke License"
            confirm-label="Revoke License"
            :loading="revokeForm.processing"
            :show-trigger="false"
            @confirm="processRevoke"
        >
            <p class="mb-4">
                Are you sure you want to revoke license
                <code class="rounded bg-muted px-1 py-0.5 font-mono text-sm">{{
                    licenseToRevoke ? truncateId(licenseToRevoke.id) : ''
                }}</code
                >?
            </p>
            <ul class="mb-4 list-disc space-y-1 pl-6 text-sm">
                <li>The license will no longer be valid for activation</li>
                <li>Already activated installations will continue to work</li>
            </ul>
            <div class="space-y-2">
                <Label for="revoke-reason">Reason (optional)</Label>
                <Input
                    id="revoke-reason"
                    v-model="revokeForm.reason"
                    placeholder="Enter revocation reason..."
                />
            </div>
            <p class="mt-4 text-sm font-medium text-destructive">
                This action cannot be undone.
            </p>
        </ConfirmDialog>
    </AppLayout>
</template>
