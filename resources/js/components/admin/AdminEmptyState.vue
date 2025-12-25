<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import Icon from '../Icon.vue';

/**
 * AdminEmptyState - Consistent empty state component for admin pages
 *
 * Displays a centered empty state with icon, title, description, and
 * optional action slot for creating first items or clearing filters.
 *
 * @example
 * <AdminEmptyState
 *   icon="FileQuestion"
 *   title="No FAQs yet"
 *   description="Get started by creating your first FAQ"
 * >
 *   <Button @click="openCreateDialog">
 *     <Plus class="mr-2 h-4 w-4" />
 *     Create FAQ
 *   </Button>
 * </AdminEmptyState>
 */

interface Props {
    icon: string;
    title: string;
    description: string;
}

defineProps<Props>();
</script>

<template>
    <Card class="border-dashed">
        <CardContent class="flex flex-col items-center justify-center py-12">
            <div class="flex flex-col items-center gap-4 text-center">
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-full bg-muted"
                >
                    <Icon :name="icon" class="h-8 w-8 text-muted-foreground" />
                </div>
                <div class="flex flex-col gap-1">
                    <h3 class="text-lg font-semibold">{{ title }}</h3>
                    <p class="text-sm text-muted-foreground">
                        {{ description }}
                    </p>
                </div>
                <div v-if="$slots.default" class="mt-2">
                    <slot />
                </div>
            </div>
        </CardContent>
    </Card>
</template>
