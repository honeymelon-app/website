<script setup lang="ts">
/**
 * AdminSection - Standard section wrapper for grouping content
 *
 * Provides consistent vertical spacing between grouped content areas
 * within admin pages.
 *
 * @example
 * <AdminSection>
 *   <AdminToolbar ... />
 *   <DataTableBulkActions ... />
 *   <DataTableRoot ... />
 * </AdminSection>
 */
</script>

<template>
    <div class="flex flex-col gap-6">
        <slot />
    </div>
</template>
