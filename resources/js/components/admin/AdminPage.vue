<script setup lang="ts">
/**
 * AdminPage - Standard container wrapper for admin pages
 *
 * Provides consistent outer container styling with proper flex layout,
 * padding, gap, and overflow handling for admin interface pages.
 *
 * @example
 * <AdminPage>
 *   <AdminToolbar ... />
 *   <DataTableRoot ... />
 * </AdminPage>
 */
</script>

<template>
    <div
        class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6"
    >
        <slot />
    </div>
</template>
