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
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, truncateId } from '@/lib/formatters';
import { getProviderVariant } from '@/lib/variants';
import { dashboard } from '@/routes';
import ordersRoutes from '@/routes/admin/orders';
import type { BreadcrumbItem } from '@/types';
import type { Order, PaginatedResponse } from '@/types/resources';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Clock3,
    Eye,
    MoreHorizontal,
    RotateCcw,
} from 'lucide-vue-next';
import { h, ref } from 'vue';

interface Props {
    orders: PaginatedResponse<Order>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Orders',
        href: ordersRoutes.index().url,
    },
];

// Refund dialog state
const showRefundDialog = ref(false);
const orderToRefund = ref<Order | null>(null);
const refundForm = useForm({
    reason: '',
});

// Column definitions
const columns: Column<Order>[] = [
    {
        key: 'id',
        label: 'Order ID',
        headerClass: 'w-[120px]',
        render: (row: Order) => {
            return h(
                'div',
                { class: 'font-mono text-sm text-muted-foreground' },
                truncateId(row.id),
            );
        },
    },
    {
        key: 'email',
        label: 'Email',
        render: (row: Order) => {
            return h('div', { class: 'text-sm font-medium' }, row.email);
        },
    },
    {
        key: 'provider',
        label: 'Provider',
        headerClass: 'w-[100px]',
        render: (row: Order) => {
            return h(
                Badge,
                {
                    variant: getProviderVariant(row.provider),
                    class: 'capitalize',
                },
                { default: () => row.provider },
            );
        },
    },
    {
        key: 'formatted_amount',
        label: 'Amount',
        headerClass: 'w-[120px]',
        render: (row: Order) => {
            return h(
                'div',
                { class: 'text-sm font-medium' },
                row.formatted_amount,
            );
        },
    },
    {
        key: 'status',
        label: 'Status',
        headerClass: 'w-[110px]',
        render: (row: Order) => {
            const variant = row.is_refunded ? 'outline' : 'secondary';
            const label = row.is_refunded ? 'Refunded' : 'Paid';
            const Icon = row.is_refunded ? RotateCcw : BadgeCheck;

            return h(
                Badge,
                { variant, class: 'gap-1 text-xs' },
                {
                    default: () => [h(Icon, { class: 'h-3.5 w-3.5' }), label],
                },
            );
        },
    },
    {
        key: 'refund_window',
        label: 'Refund Window',
        headerClass: 'w-[140px] text-center',
        class: 'text-center',
        render: (row: Order) => {
            if (row.is_refunded) {
                return h(
                    'span',
                    { class: 'text-muted-foreground text-sm' },
                    'Processed',
                );
            }

            return row.is_within_refund_window
                ? h(
                    'span',
                    {
                        class: 'text-emerald-600 dark:text-emerald-500 text-sm flex items-center justify-center gap-1',
                    },
                    [h(Clock3, { class: 'h-4 w-4' }), 'Open'],
                )
                : h(
                    'span',
                    { class: 'text-muted-foreground text-sm' },
                    'Closed',
                );
        },
    },
    {
        key: 'license_id',
        label: 'License',
        headerClass: 'w-[100px]',
        render: (row: Order) => {
            if (!row.license_id) {
                return h(
                    'span',
                    { class: 'text-muted-foreground text-sm' },
                    '—',
                );
            }
            return h(
                Badge,
                { variant: 'outline', class: 'font-mono text-xs' },
                { default: () => truncateId(row.license_id) },
            );
        },
    },
    {
        key: 'created_at',
        label: 'Created',
        headerClass: 'w-[140px]',
        render: (row: Order) => {
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
        render: (row: Order) => {
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
                                            onClick: () => viewOrder(row),
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
                                    row.can_be_refunded
                                        ? h(
                                            DropdownMenuItem,
                                            {
                                                onClick: () =>
                                                    openRefundDialog(row),
                                            },
                                            {
                                                default: () => [
                                                    h(RotateCcw, {
                                                        class: 'mr-2 h-4 w-4',
                                                    }),
                                                    'Refund Order',
                                                ],
                                            },
                                        )
                                        : null,
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
const viewOrder = (order: Order): void => {
    router.visit(ordersRoutes.show(order.id).url);
};

const openRefundDialog = (order: Order): void => {
    orderToRefund.value = order;
    refundForm.reset();
    showRefundDialog.value = true;
};

const processRefund = (): void => {
    if (!orderToRefund.value) {
        return;
    }

    refundForm.post(ordersRoutes.refund(orderToRefund.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            showRefundDialog.value = false;
            orderToRefund.value = null;
            refundForm.reset();
        },
    });
};

const handlePageChange = (page: number): void => {
    router.visit(ordersRoutes.index().url, {
        data: { page },
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Orders" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6"
        >
            <div class="flex flex-col gap-6">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-2">
                        <h3 class="text-2xl font-semibold tracking-tight">
                            Orders
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            View and manage customer orders and payments.
                        </p>
                    </div>
                </div>

                <DataTable
                    :columns="columns"
                    :data="props.orders.data"
                    :meta="props.orders.meta"
                    empty-message="No orders found. Orders will appear here when customers make purchases."
                    @page-change="handlePageChange"
                />
            </div>
        </div>

        <!-- Refund Confirmation Dialog -->
        <ConfirmDialog
            v-model:open="showRefundDialog"
            title="Refund Order"
            confirm-label="Process Refund"
            :loading="refundForm.processing"
            :show-trigger="false"
            @confirm="processRefund"
        >
            <p class="mb-4">
                Are you sure you want to refund order
                <code class="rounded bg-muted px-1 py-0.5 font-mono text-sm">{{
                    orderToRefund ? truncateId(orderToRefund.id) : ''
                }}</code
                >?
            </p>
            <ul class="mb-4 list-disc space-y-1 pl-6 text-sm">
                <li>Process a full refund through the payment provider</li>
                <li>Revoke the associated license</li>
            </ul>
            <div class="space-y-2">
                <Label for="reason">Reason (optional)</Label>
                <Input
                    id="reason"
                    v-model="refundForm.reason"
                    placeholder="Enter refund reason..."
                />
            </div>
            <p class="mt-4 text-sm font-medium text-destructive">
                This action cannot be undone.
            </p>
        </ConfirmDialog>
    </AppLayout>
</template>
