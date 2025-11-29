<script setup lang="ts">
import Badge from '@/components/ui/badge/Badge.vue';
import { useScrollAnimation } from '@/composables/useScrollAnimation';

interface Props {
    badge?: string;
    title: string;
    description?: string;
}

defineProps<Props>();

const { elementRef, isVisible } = useScrollAnimation({
    threshold: 0.2,
    rootMargin: '0px 0px -100px 0px',
});
</script>

<template>
    <div ref="elementRef" class="mb-20 text-center">
        <Badge
            v-if="badge"
            variant="outline"
            class="mb-6 px-3 py-1 transition-all duration-500 ease-out"
            :class="
                isVisible
                    ? 'translate-y-0 opacity-100'
                    : '-translate-y-2 opacity-0'
            "
        >
            {{ badge }}
        </Badge>
        <h2
            class="mb-6 text-4xl font-bold tracking-tight transition-all delay-75 duration-500 ease-out sm:text-5xl"
            :class="
                isVisible
                    ? 'translate-y-0 opacity-100'
                    : 'translate-y-4 opacity-0'
            "
        >
            {{ title }}
        </h2>
        <p
            v-if="description"
            class="mx-auto max-w-2xl text-lg leading-relaxed text-muted-foreground transition-all delay-150 duration-500 ease-out"
            :class="
                isVisible
                    ? 'translate-y-0 opacity-100'
                    : 'translate-y-4 opacity-0'
            "
        >
            {{ description }}
        </p>
    </div>
</template>
