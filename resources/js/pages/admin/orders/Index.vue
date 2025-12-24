<script setup lang="ts">
import {
    DataTableBulkActions,
    DataTablePagination,
    DataTableRoot,
    TableFilters,
    type FilterConfig,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { useDataTable } from '@/composables';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import ordersRoute from '@/routes/admin/orders';
import type { BreadcrumbItem } from '@/types';
import type { FilterParams, PaginatedResponse } from '@/types/api';
import type { Order } from '@/types/resources';
import { Head, router } from '@inertiajs/vue3';
import { Download, RotateCcw } from 'lucide-vue-next';
import { computed } from 'vue';
import { columns } from './columns';

interface Filters {
    provider?: string;
    email?: string;
    email_search?: string;
    min_amount?: number;
    max_amount?: number;
    currency?: string;
    has_license?: string;
    license_status?: string;
    created_after?: string;
    created_before?: string;
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
    orders: PaginatedResponse<Order>;
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
        title: 'Orders',
        href: ordersRoute.index().url,
    },
];

// Filter configuration for TableFilters component
const filterConfig: FilterConfig[] = [
    {
        key: 'email_search',
        label: 'Search email...',
        type: 'text',
        placeholder: 'Search email...',
    },
    {
        key: 'provider',
        label: 'Provider',
        type: 'select',
        options: [
            { label: 'Lemon Squeezy', value: 'ls' },
            { label: 'Stripe', value: 'stripe' },
        ],
    },
    {
        key: 'license_status',
        label: 'License',
        type: 'select',
        options: [
            { label: 'With License', value: 'with' },
            { label: 'Without License', value: 'without' },
        ],
    },
];

// Reactive filter state that syncs with props
const filterState = computed<FilterParams>(() => ({
    email_search: props.filters.email_search,
    provider: props.filters.provider,
    license_status: props.filters.license_status,
}));

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
    data: computed(() => props.orders),
    columns,
    sorting: computed(() => props.sorting),
    filters: computed(() => props.filters as Record<string, unknown>),
    pagination: computed(() => props.pagination),
    indexUrl: ordersRoute.index().url,
    getRowId: (row) => row.id,
    enableRowSelection: true,
});

// Bulk refund action
const bulkRefund = () => {
    const orders = selectedRows.value.filter((order) => order.can_be_refunded);

    if (orders.length === 0) {
        alert('No refundable orders selected.');
        return;
    }

    const confirmed = confirm(
        `Refund ${orders.length} order(s)? This will revoke their associated licenses.`,
    );

    if (!confirmed) {
        return;
    }

    // Process refunds sequentially
    orders.forEach((order) => {
        router.post(
            ordersRoute.refund(order.id).url,
            { reason: 'Bulk refund' },
            { preserveScroll: true },
        );
    });

    clearSelection();
};

// Export selected orders to CSV
const exportSelected = () => {
    const orders = selectedRows.value;

    const headers = [
        'Order ID',
        'Email',
        'Provider',
        'Amount',
        'Status',
        'License ID',
        'Created',
    ];
    const csvContent = [
        headers.join(','),
        ...orders.map((order) =>
            [
                order.id,
                order.email,
                order.provider,
                order.formatted_amount,
                order.is_refunded ? 'Refunded' : 'Paid',
                order.license_id || '',
                order.created_at,
            ].join(','),
        ),
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `orders-export-${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
};
</script>

<template>
    <Head title="Orders" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6"
        >
            <div class="flex flex-col gap-6">
                <!-- Header + Filters -->
                <div
                    class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
                >
                    <div class="flex flex-col gap-1">
                        <h1 class="text-2xl font-semibold tracking-tight">
                            Orders
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            View and manage customer orders and payments.
                        </p>
                    </div>
                    <TableFilters
                        :filters="filterConfig"
                        :model-value="filterState"
                        @update:model-value="handleFilterUpdate"
                        @clear="handleFilterClear"
                    />
                </div>

                <!-- Bulk Actions Toolbar -->
                <DataTableBulkActions
                    :selected-count="selectedCount"
                    item-label="order"
                    @clear="clearSelection"
                >
                    <Button
                        variant="outline"
                        size="sm"
                        class="h-8"
                        @click="exportSelected"
                    >
                        <Download class="mr-1.5 h-4 w-4" />
                        Export CSV
                    </Button>
                    <Button
                        variant="destructive"
                        size="sm"
                        class="h-8"
                        @click="bulkRefund"
                    >
                        <RotateCcw class="mr-1.5 h-4 w-4" />
                        Refund Selected
                    </Button>
                </DataTableBulkActions>

                <!-- Table -->
                <div class="space-y-4">
                    <DataTableRoot
                        :table="table"
                        :columns="columns"
                        empty-message="No orders found. Orders will appear here when customers make purchases."
                    />

                    <!-- Pagination -->
                    <DataTablePagination
                        :meta="paginationMeta"
                        :allowed-page-sizes="allowedPageSizes"
                        @page-change="handlePageChange"
                        @page-size-change="handlePageSizeChange"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
