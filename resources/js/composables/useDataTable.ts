import { valueUpdater } from '@/lib/utils';
import type {
    FilterParams,
    PaginatedResponse,
    PaginationMeta,
} from '@/types/api';
import { router } from '@inertiajs/vue3';
import {
    getCoreRowModel,
    useVueTable,
    type ColumnDef,
    type RowSelectionState,
    type SortingState,
    type Table,
} from '@tanstack/vue-table';
import { computed, ref, watch, type ComputedRef, type Ref } from 'vue';

export interface DataTableSorting {
    column: string | null;
    direction: 'asc' | 'desc';
}

export interface DataTablePagination {
    pageSize: number;
    allowedPageSizes: number[];
}

export interface UseDataTableOptions<TData> {
    /** The paginated data from the server */
    data: ComputedRef<PaginatedResponse<TData>> | Ref<PaginatedResponse<TData>>;
    /** Column definitions for the table */
    columns: ColumnDef<TData, unknown>[];
    /** Current sorting state from server */
    sorting: ComputedRef<DataTableSorting> | Ref<DataTableSorting>;
    /** Current filters state from server */
    filters:
        | ComputedRef<Record<string, unknown>>
        | Ref<Record<string, unknown>>;
    /** Pagination configuration */
    pagination: ComputedRef<DataTablePagination> | Ref<DataTablePagination>;
    /** The base URL for navigation */
    indexUrl: string;
    /** Function to get the row ID (defaults to row.id) */
    getRowId?: (row: TData) => string;
    /** Enable row selection (defaults to false) */
    enableRowSelection?: boolean;
    /** Optional meta data to pass to the table (for column actions, etc.) */
    meta?: Record<string, unknown>;
}

export interface UseDataTableReturn<TData> {
    /** The TanStack Table instance */
    table: Table<TData>;
    /** Current sorting state */
    sortingState: Ref<SortingState>;
    /** Current row selection state */
    rowSelection: Ref<RowSelectionState>;
    /** Selected rows data */
    selectedRows: ComputedRef<TData[]>;
    /** Count of selected rows */
    selectedCount: ComputedRef<number>;
    /** Whether any rows are selected */
    hasSelection: ComputedRef<boolean>;
    /** Clear all selections */
    clearSelection: () => void;
    /** Navigate with updated params */
    navigateWithParams: (params: Record<string, unknown>) => void;
    /** Handle page change */
    handlePageChange: (page: number) => void;
    /** Handle page size change */
    handlePageSizeChange: (pageSize: number) => void;
    /** Handle filter updates */
    handleFilterUpdate: (newFilters: FilterParams) => void;
    /** Handle clearing all filters */
    handleFilterClear: () => void;
    /** Whether navigation is in progress */
    isNavigating: Ref<boolean>;
    /** Pagination meta for DataTablePagination */
    paginationMeta: ComputedRef<PaginationMeta>;
    /** Allowed page sizes */
    allowedPageSizes: ComputedRef<number[]>;
}

/**
 * Composable for managing TanStack Table with server-side pagination, sorting, and filtering.
 *
 * @example
 * ```ts
 * const { table, handlePageChange, handleFilterUpdate } = useDataTable({
 *   data: computed(() => props.orders),
 *   columns,
 *   sorting: computed(() => props.sorting),
 *   filters: computed(() => props.filters),
 *   pagination: computed(() => props.pagination),
 *   indexUrl: ordersRoute.index().url,
 *   enableRowSelection: true,
 * });
 * ```
 */
export function useDataTable<TData>(
    options: UseDataTableOptions<TData>,
): UseDataTableReturn<TData> {
    const {
        data,
        columns,
        sorting,
        filters,
        pagination,
        indexUrl,
        getRowId = (row) => (row as Record<string, unknown>).id as string,
        enableRowSelection = false,
        meta,
    } = options;

    // Track navigation state
    const isNavigating = ref(false);

    // Convert backend sorting state to TanStack format
    const sortingState = ref<SortingState>(
        sorting.value.column
            ? [
                  {
                      id: sorting.value.column,
                      desc: sorting.value.direction === 'desc',
                  },
              ]
            : [],
    );

    // Row selection state
    const rowSelection = ref<RowSelectionState>({});

    // Create TanStack Table instance
    const table = useVueTable({
        get data() {
            return data.value.data;
        },
        columns,
        getCoreRowModel: getCoreRowModel(),
        manualPagination: true,
        manualSorting: true,
        get pageCount() {
            return data.value.meta.last_page;
        },
        getRowId,
        state: {
            get sorting() {
                return sortingState.value;
            },
            get rowSelection() {
                return rowSelection.value;
            },
        },
        onSortingChange: (updaterOrValue) =>
            valueUpdater(updaterOrValue, sortingState),
        onRowSelectionChange: (updaterOrValue) =>
            valueUpdater(updaterOrValue, rowSelection),
        enableRowSelection,
        meta,
    });

    // Computed selection helpers
    const selectedRows = computed(() =>
        table.getFilteredSelectedRowModel().rows.map((row) => row.original),
    );
    const selectedCount = computed(() => selectedRows.value.length);
    const hasSelection = computed(() => selectedCount.value > 0);

    // Clear selection
    const clearSelection = () => {
        rowSelection.value = {};
    };

    // Helper to extract single value (handles arrays from malformed URLs)
    const getSingleValue = <T>(val: T | T[]): T =>
        Array.isArray(val) ? val[0] : val;

    // Navigate with params
    const navigateWithParams = (params: Record<string, unknown>) => {
        if (isNavigating.value) {
            return;
        }

        isNavigating.value = true;

        // Build current params
        const currentParams: Record<
            string,
            string | number | boolean | null | undefined
        > = {
            page: getSingleValue(data.value.meta.current_page),
            per_page: getSingleValue(pagination.value.pageSize),
        };

        // Add filter params
        Object.entries(filters.value).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                currentParams[key] = value as string | number | boolean;
            }
        });

        // Add sorting params
        if (sorting.value.column) {
            currentParams.sort = sorting.value.column;
            currentParams.direction = sorting.value.direction;
        }

        const mergedParams = { ...currentParams, ...params };

        // Remove null/undefined values and ensure single values
        const cleanParams: Record<string, string | number | boolean> = {};
        Object.keys(mergedParams).forEach((key) => {
            let value = mergedParams[key];
            if (Array.isArray(value)) {
                value = value[0];
            }
            if (
                value !== null &&
                value !== undefined &&
                typeof value !== 'object'
            ) {
                cleanParams[key] = value as string | number | boolean;
            }
        });

        router.visit(indexUrl, {
            data: cleanParams,
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isNavigating.value = false;
            },
        });
    };

    // Watch for sorting changes and navigate
    watch(sortingState, (newSorting) => {
        if (newSorting.length > 0) {
            navigateWithParams({
                sort: newSorting[0].id,
                direction: newSorting[0].desc ? 'desc' : 'asc',
                page: 1,
            });
        } else {
            navigateWithParams({
                sort: null,
                direction: null,
                page: 1,
            });
        }
    });

    // Handle page change
    const handlePageChange = (page: number): void => {
        navigateWithParams({ page });
    };

    // Handle page size change
    const handlePageSizeChange = (pageSize: number): void => {
        navigateWithParams({
            per_page: pageSize,
            page: 1,
        });
    };

    // Handle filter updates
    const handleFilterUpdate = (newFilters: FilterParams): void => {
        const filterParams: Record<string, unknown> = { page: 1 };
        Object.entries(newFilters).forEach(([key, value]) => {
            filterParams[key] = value || null;
        });
        navigateWithParams(filterParams);
    };

    // Handle clearing all filters
    const handleFilterClear = (): void => {
        const filterParams: Record<string, unknown> = { page: 1 };
        Object.keys(filters.value).forEach((key) => {
            filterParams[key] = null;
        });
        navigateWithParams(filterParams);
    };

    // Pagination computed helpers
    const paginationMeta = computed(() => data.value.meta);
    const allowedPageSizes = computed(() => pagination.value.allowedPageSizes);

    return {
        table,
        sortingState,
        rowSelection,
        selectedRows,
        selectedCount,
        hasSelection,
        clearSelection,
        navigateWithParams,
        handlePageChange,
        handlePageSizeChange,
        handleFilterUpdate,
        handleFilterClear,
        isNavigating,
        paginationMeta,
        allowedPageSizes,
    };
}
