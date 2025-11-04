<script setup lang="ts">
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
import { index, show } from '@/routes/admin/licenses';
import type { BreadcrumbItem } from '@/types';
import type { License } from '@/types/api';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Eye, MoreHorizontal, Plus, ShieldOff } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    licenses: {
        data: License[];
        meta: {
            current_page: number;
            from: number | null;
            last_page: number;
            per_page: number;
            to: number | null;
            total: number;
        };
        links: {
            first: string | null;
            last: string | null;
            prev: string | null;
            next: string | null;
        };
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Licenses',
        href: index().url,
    },
];

// Issue license modal state
const isIssueDialogOpen = ref(false);

// Issue license form
const issueForm = useForm({
    email: '',
    max_major_version: '1',
});

// Helper to format status badge variant
const getStatusVariant = (
    status: string,
): 'default' | 'secondary' | 'destructive' => {
    const variantMap: Record<string, 'default' | 'secondary' | 'destructive'> =
        {
            active: 'default',
            revoked: 'destructive',
            expired: 'secondary',
        };
    return variantMap[status] || 'secondary';
};

// Helper to format date
const formatDate = (dateString: string | null): string => {
    if (!dateString) return 'Not issued';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Actions
const viewLicense = (license: License): void => {
    router.visit(show(license.id).url);
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
                onSuccess: () => {
                    alert('License revoked successfully');
                },
            },
        );
    }
};

const handleIssueLicense = (): void => {
    // TODO: Add API endpoint for issuing licenses
    issueForm.post('/api/admin/licenses', {
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
                </div>

                <div class="rounded-lg border">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-sm font-medium"
                                    >
                                        License ID
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-sm font-medium"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-sm font-medium"
                                    >
                                        Max Version
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-sm font-medium"
                                    >
                                        Order ID
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-sm font-medium"
                                    >
                                        Issued At
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-sm font-medium"
                                    >
                                        Created At
                                    </th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="license in props.licenses.data"
                                    :key="license.id"
                                    class="transition-colors hover:bg-muted/30"
                                >
                                    <td
                                        class="px-4 py-3 font-mono text-xs text-muted-foreground"
                                    >
                                        {{ license.id.substring(0, 8) }}...
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge
                                            :variant="
                                                getStatusVariant(license.status)
                                            "
                                            class="capitalize"
                                        >
                                            {{ license.status }}
                                        </Badge>
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm text-muted-foreground"
                                    >
                                        {{
                                            license.max_major_version === 999
                                                ? 'Lifetime'
                                                : `v${license.max_major_version}.x`
                                        }}
                                    </td>
                                    <td
                                        class="px-4 py-3 font-mono text-xs text-muted-foreground"
                                    >
                                        {{
                                            license.order_id
                                                ? license.order_id.substring(
                                                      0,
                                                      8,
                                                  )
                                                : '—'
                                        }}...
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm text-muted-foreground"
                                    >
                                        {{ formatDate(license.issued_at) }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm text-muted-foreground"
                                    >
                                        {{ formatDate(license.created_at) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-8 w-8"
                                                >
                                                    <span class="sr-only"
                                                        >Open menu</span
                                                    >
                                                    <MoreHorizontal
                                                        class="h-4 w-4"
                                                    />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuLabel
                                                    >Actions</DropdownMenuLabel
                                                >
                                                <DropdownMenuItem
                                                    @click="
                                                        viewLicense(license)
                                                    "
                                                >
                                                    <Eye class="mr-2 h-4 w-4" />
                                                    View Details
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    v-if="
                                                        license.status ===
                                                        'active'
                                                    "
                                                    @click="
                                                        revokeLicense(license)
                                                    "
                                                    class="text-destructive focus:text-destructive"
                                                >
                                                    <ShieldOff
                                                        class="mr-2 h-4 w-4"
                                                    />
                                                    Revoke License
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>

                                <tr
                                    v-if="
                                        !props.licenses.data ||
                                        props.licenses.data.length === 0
                                    "
                                >
                                    <td
                                        colspan="7"
                                        class="px-4 py-8 text-center text-sm text-muted-foreground"
                                    >
                                        No licenses found. Issue your first
                                        license to get started.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="
                            props.licenses.data &&
                            props.licenses.data.length > 0 &&
                            props.licenses.meta.last_page > 1
                        "
                        class="flex items-center justify-between border-t px-4 py-3"
                    >
                        <div class="text-sm text-muted-foreground">
                            Showing {{ props.licenses.meta.from }} to
                            {{ props.licenses.meta.to }} of
                            {{ props.licenses.meta.total }} licenses
                        </div>
                        <div class="flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!props.licenses.links.prev"
                                @click="
                                    router.visit(props.licenses.links.prev!)
                                "
                            >
                                Previous
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!props.licenses.links.next"
                                @click="
                                    router.visit(props.licenses.links.next!)
                                "
                            >
                                Next
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
