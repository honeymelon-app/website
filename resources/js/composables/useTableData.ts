import type { FilterParams } from '@/types/api';
import type { PaginatedResponse } from '@/types/resources';
import { computed, ref } from 'vue';

export function useTableData<T>(
    endpoint: string,
    initialFilters: FilterParams = {},
) {
    const isLoading = ref(false);
    const data = ref<PaginatedResponse<T> | null>(null);
    const filters = ref<FilterParams>({ ...initialFilters });
    const currentPage = ref(1);
    const perPage = ref(20);

    // Build query string from filters
    const buildQueryString = () => {
        const params = new URLSearchParams();

        // Add pagination
        params.append('page', currentPage.value.toString());
        params.append('per_page', perPage.value.toString());

        // Add filters
        Object.entries(filters.value).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                params.append(key, String(value));
            }
        });

        return params.toString();
    };

    // Fetch data from API
    const fetchData = async () => {
        isLoading.value = true;
        const query = buildQueryString();

        try {
            const response = await fetch(`${endpoint}?${query}`, {
                credentials: 'include',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            data.value = await response.json();
        } catch (error) {
            console.error('Failed to fetch data:', error);
        } finally {
            isLoading.value = false;
        }
    };

    // Update filters and refetch
    const updateFilters = (newFilters: Partial<FilterParams>) => {
        filters.value = { ...filters.value, ...newFilters };
        currentPage.value = 1; // Reset to page 1 when filters change
        fetchData();
    };

    // Clear filters
    const clearFilters = () => {
        filters.value = {};
        currentPage.value = 1;
        fetchData();
    };

    // Go to specific page
    const goToPage = (page: number) => {
        currentPage.value = page;
        fetchData();
    };

    // Change per page
    const changePerPage = (size: number) => {
        perPage.value = size;
        currentPage.value = 1;
        fetchData();
    };

    // Computed properties
    const hasData = computed(
        () => data.value?.data && data.value.data.length > 0,
    );
    const meta = computed(() => data.value?.meta);
    const links = computed(() => data.value?.links);

    return {
        // State
        isLoading,
        data: computed(() => data.value?.data ?? []),
        meta,
        links,
        hasData,
        filters,
        currentPage,
        perPage,

        // Methods
        fetchData,
        updateFilters,
        clearFilters,
        goToPage,
        changePerPage,
    };
}
