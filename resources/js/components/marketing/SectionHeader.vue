<script setup lang="ts">
import Badge from '@/components/ui/badge/Badge.vue';

interface Props {
    badge?: string;
    title: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="mb-20 text-center">
        <Badge v-if="badge" variant="outline" class="mb-6 px-3 py-1">{{
            badge
        }}</Badge>
        <h2 class="mb-6 text-4xl font-bold tracking-tight sm:text-5xl">
            {{ title }}
        </h2>
        <p
            v-if="description"
            class="mx-auto max-w-2xl text-lg leading-relaxed text-muted-foreground"
        >
            {{ description }}
        </p>
    </div>
</template>
