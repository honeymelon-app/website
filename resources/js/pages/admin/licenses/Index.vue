<script setup lang="ts">
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
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import licenses from '@/routes/admin/licenses';
import type { BreadcrumbItem } from '@/types';
import type { License, PaginatedResponse } from '@/types/resources';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Check, Copy, Eye, MoreHorizontal, Plus, ShieldOff } from 'lucide-vue-next';
import { h, ref } from 'vue';

interface Props {
    licenses: PaginatedResponse<License>;
    license_key?: string;
    license_email?: string;
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

// Success modal state
const isSuccessDialogOpen = ref(!!props.license_key);
const generatedLicenseKey = ref(props.license_key || '');
const generatedLicenseEmail = ref(props.license_email || '');
const isCopied = ref(false);

// Copy to clipboard function
const copyToClipboard = async (): Promise<void> => {
    try {
        await navigator.clipboard.writeText(generatedLicenseKey.value);
        isCopied.value = true;
        setTimeout(() => {
            isCopied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
    }
};

// Issue license form
const issueForm = useForm({
    email: '',
    max_major_version: '1',
});

// Helper to format status badge variant
const getStatusVariant = (
    status: string,
): 'default' | 'secondary' | 'destructive' => {
    const variantMap: Record<
        string,
        'default' | 'secondary' | 'destructive'
    > = {
        active: 'default',
        revoked: 'destructive',
        expired: 'secondary',
    };
    return variantMap[status] || 'secondary';
};

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
                row.id ? row.id.substring(0, 8) + '...' : 'N/A',
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
            const date = new Date(row.issued_at);
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
        render: (row: License) => {
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
                                default: () => [
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
    router.visit(licenses.show(license.id).url);
};

const revokeLicense = (license: License): void => {
    if (
        confirm(
            `Are you sure you want to revoke license ${license.id.substring(0, 8)}?`,
        )
    ) {
        router.post(
            '/api/admin/licenses/revoke',
            { license_id: license.id },
            {
                preserveScroll: true,
            },
        );
    }
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
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
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
                                            <SelectItem value="1"
                                                >Version 1.x</SelectItem
                                            >
                                            <SelectItem value="2"
                                                >Version 2.x</SelectItem
                                            >
                                            <SelectItem value="3"
                                                >Version 3.x</SelectItem
                                            >
                                            <SelectItem value="999"
                                                >Lifetime (All
                                                Versions)</SelectItem
                                            >
                                        </SelectContent>
                                    </Select>
                                    <p
                                        v-if="
                                            issueForm.errors.max_major_version
                                        "
                                        class="text-sm text-destructive"
                                    >
                                        {{
                                            issueForm.errors.max_major_version
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
                                <DialogTitle>License Issued Successfully</DialogTitle>
                                <DialogDescription>
                                    The license has been generated and is ready to use.
                                    Make sure to copy it now as it won't be shown
                                    again.
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
                                            class="font-mono text-xs flex-1"
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
                                        This key will only be shown once. Make sure to
                                        copy and save it securely.
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
    </AppLayout>
</template>
