<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { PaginationMeta } from '@/types/api';
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    meta: PaginationMeta;
    isLoading?: boolean;
}>();

const emit = defineEmits<{
    'page-change': [page: number];
}>();

const canGoPrevious = computed(() => props.meta.current_page > 1);
const canGoNext = computed(
    () => props.meta.current_page < props.meta.last_page,
);

const goToFirstPage = () => {
    if (canGoPrevious.value && !props.isLoading) {
        emit('page-change', 1);
    }
};

const goToPreviousPage = () => {
    if (canGoPrevious.value && !props.isLoading) {
        emit('page-change', props.meta.current_page - 1);
    }
};

const goToNextPage = () => {
    if (canGoNext.value && !props.isLoading) {
        emit('page-change', props.meta.current_page + 1);
    }
};

const goToLastPage = () => {
    if (canGoNext.value && !props.isLoading) {
        emit('page-change', props.meta.last_page);
    }
};

const rangeText = computed(() => {
    if (!props.meta.from || !props.meta.to) {
        return 'No results';
    }
    return `${props.meta.from}-${props.meta.to} of ${props.meta.total}`;
});
</script>

<template>
    <div class="flex items-center justify-between">
        <div class="flex-1 text-sm text-muted-foreground">
            {{ rangeText }}
        </div>
        <div class="flex items-center space-x-6 lg:space-x-8">
            <div class="flex items-center space-x-2">
                <p class="text-sm font-medium">
                    Page {{ meta.current_page }} of {{ meta.last_page }}
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <Button
                    variant="outline"
                    size="icon"
                    class="hidden h-8 w-8 lg:flex"
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
                    class="hidden h-8 w-8 lg:flex"
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
