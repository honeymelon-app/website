<script setup lang="ts">
/**
 * AdminToolbar - Standardized toolbar for admin list pages
 *
 * Combines page header (title + subtitle) with filters and action buttons
 * in a consistent layout. Ensures proper responsive behavior and spacing.
 *
 * @example
 * <AdminToolbar
 *   title="Licenses"
 *   subtitle="Manage and issue product licenses for customers."
 * >
 *   <template #filters>
 *     <TableFilters ... />
 *   </template>
 *   <template #actions>
 *     <Button>
 *       <Plus class="mr-2 h-4 w-4" />
 *       Issue License
 *     </Button>
 *   </template>
 * </AdminToolbar>
 */

interface Props {
    title: string;
    subtitle?: string;
}

defineProps<Props>();
</script>

<template>
    <div
        class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
    >
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-semibold tracking-tight">
                <slot name="title">{{ title }}</slot>
            </h1>
            <p
                v-if="subtitle || $slots.subtitle"
                class="text-sm text-muted-foreground"
            >
                <slot name="subtitle">{{ subtitle }}</slot>
            </p>
        </div>
        <div
            v-if="$slots.filters || $slots.actions"
            class="flex items-center gap-3"
        >
            <slot name="filters" />
            <slot name="actions" />
        </div>
    </div>
</template>
