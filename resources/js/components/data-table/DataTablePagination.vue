<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { PaginationMeta } from '@/types/resources';
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        meta: PaginationMeta;
        isLoading?: boolean;
        allowedPageSizes?: number[];
        showPageSizeSelector?: boolean;
    }>(),
    {
        isLoading: false,
        allowedPageSizes: () => [10, 15, 25, 50, 100],
        showPageSizeSelector: true,
    },
);

const emit = defineEmits<{
    'page-change': [page: number];
    'page-size-change': [pageSize: number];
}>();

// Helper to ensure we have a number (handles arrays from URL params)
const toNumber = (
    val: number | number[] | undefined,
    fallback: number,
): number => {
    if (val === undefined) return fallback;
    const num = Array.isArray(val) ? val[0] : val;
    return typeof num === 'number' ? num : fallback;
};

// Computed values that safely handle potentially malformed data
const currentPage = computed(() =>
    toNumber(props.meta.current_page as number | number[], 1),
);
const lastPage = computed(() =>
    toNumber(props.meta.last_page as number | number[], 1),
);
const perPage = computed(() =>
    toNumber(props.meta.per_page as number | number[], 15),
);
const from = computed(() => toNumber(props.meta.from as number | number[], 0));
const to = computed(() => toNumber(props.meta.to as number | number[], 0));
const total = computed(() =>
    toNumber(props.meta.total as number | number[], 0),
);

const canGoPrevious = computed(() => currentPage.value > 1);
const canGoNext = computed(() => currentPage.value < lastPage.value);

const goToFirstPage = () => {
    if (canGoPrevious.value && !props.isLoading) {
        emit('page-change', 1);
    }
};

const goToPreviousPage = () => {
    if (canGoPrevious.value && !props.isLoading) {
        emit('page-change', currentPage.value - 1);
    }
};

const goToNextPage = () => {
    if (canGoNext.value && !props.isLoading) {
        emit('page-change', currentPage.value + 1);
    }
};

const goToLastPage = () => {
    if (canGoNext.value && !props.isLoading) {
        emit('page-change', lastPage.value);
    }
};

const handlePageSizeChange = (value: unknown) => {
    if (!props.isLoading && value !== null && typeof value === 'string') {
        emit('page-size-change', parseInt(value, 10));
    }
};

const rangeText = computed(() => {
    if (!from.value || !to.value) {
        return `0 of ${total.value}`;
    }
    return `${from.value}-${to.value} of ${total.value}`;
});
</script>

<template>
    <div
        class="flex flex-col items-center justify-between gap-4 px-2 sm:flex-row"
    >
        <!-- Results summary -->
        <p class="text-sm text-muted-foreground">
            {{ rangeText }}
        </p>

        <div class="flex items-center gap-4 sm:gap-6">
            <!-- Page Size Selector -->
            <div v-if="showPageSizeSelector" class="flex items-center gap-2">
                <span class="text-sm text-muted-foreground">Rows</span>
                <Select
                    :model-value="String(perPage)"
                    @update:model-value="handlePageSizeChange"
                >
                    <SelectTrigger class="h-8 w-16">
                        <SelectValue :placeholder="String(perPage)" />
                    </SelectTrigger>
                    <SelectContent side="top">
                        <SelectItem
                            v-for="pageSize in allowedPageSizes"
                            :key="pageSize"
                            :value="String(pageSize)"
                        >
                            {{ pageSize }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Page indicator -->
            <span class="text-sm text-muted-foreground tabular-nums">
                Page {{ currentPage }} of {{ lastPage }}
            </span>

            <!-- Navigation Buttons -->
            <div class="flex items-center gap-1">
                <Button
                    variant="outline"
                    size="icon"
                    class="hidden h-8 w-8 sm:inline-flex"
                    :disabled="!canGoPrevious || isLoading"
                    @click="goToFirstPage"
                >
                    <span class="sr-only">Go to first page</span>
                    <ChevronsLeft class="h-4 w-4" />
                </Button>
                <Button
                    variant="outline"
                    size="icon"
                    class="h-8 w-8"
                    :disabled="!canGoPrevious || isLoading"
                    @click="goToPreviousPage"
                >
                    <span class="sr-only">Go to previous page</span>
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <Button
                    variant="outline"
                    size="icon"
                    class="h-8 w-8"
                    :disabled="!canGoNext || isLoading"
                    @click="goToNextPage"
                >
                    <span class="sr-only">Go to next page</span>
                    <ChevronRight class="h-4 w-4" />
                </Button>
                <Button
                    variant="outline"
                    size="icon"
                    class="hidden h-8 w-8 sm:inline-flex"
                    :disabled="!canGoNext || isLoading"
                    @click="goToLastPage"
                >
                    <span class="sr-only">Go to last page</span>
                    <ChevronsRight class="h-4 w-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
