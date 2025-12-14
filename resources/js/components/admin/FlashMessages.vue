<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();

const successMessage = computed(
    () => page.props.flash?.success as string | undefined,
);

const errorMessage = computed(
    () => page.props.flash?.error as string | undefined,
);
</script>

<template>
    <div
        v-if="successMessage"
        class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-950"
    >
        <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
        <p class="text-sm font-medium text-green-800 dark:text-green-200">
            {{ successMessage }}
        </p>
    </div>
    <div
        v-if="errorMessage"
        class="flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950"
    >
        <AlertTriangle class="h-5 w-5 text-red-600 dark:text-red-400" />
        <p class="text-sm font-medium text-red-800 dark:text-red-200">
            {{ errorMessage }}
        </p>
    </div>
</template>
