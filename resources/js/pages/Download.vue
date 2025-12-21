<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onMounted } from 'vue';

onMounted(() => {
    router.visit('/#download', { preserveScroll: false });
});
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-background">
        <div class="text-center">
            <p class="text-muted-foreground">Redirecting to download...</p>
        </div>
    </div>
</template>
