<script setup lang="ts">
import { Card } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';

/**
 * AdminLoadingState - Skeleton loader for admin list pages
 *
 * Displays a skeleton UI matching the structure of data tables
 * while content is loading.
 *
 * @example
 * <AdminLoadingState v-if="isLoading" />
 * <DataTableRoot v-else ... />
 */

interface Props {
    rows?: number;
}

withDefaults(defineProps<Props>(), {
    rows: 5,
});
</script>

<template>
    <Card>
        <div class="p-6">
            <!-- Header skeleton -->
            <div class="mb-6 flex items-center justify-between">
                <Skeleton class="h-8 w-48" />
                <div class="flex gap-2">
                    <Skeleton class="h-8 w-24" />
                    <Skeleton class="h-8 w-24" />
                </div>
            </div>

            <!-- Table skeleton -->
            <div class="space-y-3">
                <!-- Header row -->
                <div class="flex gap-4 border-b pb-3">
                    <Skeleton class="h-4 w-8" />
                    <Skeleton class="h-4 flex-1" />
                    <Skeleton class="h-4 w-32" />
                    <Skeleton class="h-4 w-24" />
                    <Skeleton class="h-4 w-16" />
                </div>

                <!-- Data rows -->
                <div
                    v-for="i in rows"
                    :key="i"
                    class="flex gap-4 border-b py-3"
                >
                    <Skeleton class="h-4 w-8" />
                    <Skeleton class="h-4 flex-1" />
                    <Skeleton class="h-4 w-32" />
                    <Skeleton class="h-4 w-24" />
                    <Skeleton class="h-4 w-16" />
                </div>
            </div>

            <!-- Pagination skeleton -->
            <div class="mt-6 flex items-center justify-between">
                <Skeleton class="h-8 w-48" />
                <div class="flex gap-2">
                    <Skeleton class="h-8 w-8" />
                    <Skeleton class="h-8 w-8" />
                    <Skeleton class="h-8 w-8" />
                    <Skeleton class="h-8 w-8" />
                </div>
            </div>
        </div>
    </Card>
</template>
