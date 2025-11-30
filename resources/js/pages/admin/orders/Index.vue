<script setup lang="ts">
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
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, truncateId } from '@/lib/formatters';
import { getProviderVariant } from '@/lib/variants';
import { dashboard } from '@/routes';
import ordersRoute from '@/routes/admin/orders';
import type { BreadcrumbItem } from '@/types';
import type { Order, PaginatedResponse } from '@/types/resources';
import { Head, router } from '@inertiajs/vue3';
import { Eye, MoreHorizontal } from 'lucide-vue-next';
import { h } from 'vue';

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
        href: ordersRoute.index().url,
    },
];

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
    router.visit(ordersRoute.show(order.id).url);
};

const handlePageChange = (page: number): void => {
    router.visit(ordersRoute.index().url, {
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
    </AppLayout>
</template>
