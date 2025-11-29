<script setup lang="ts">
import { useScrollAnimation } from '@/composables/useScrollAnimation';

interface Props {
    title: string;
    subtitle?: string;
    maxWidth?:
    | 'sm'
    | 'md'
    | 'lg'
    | 'xl'
    | '2xl'
    | '3xl'
    | '4xl'
    | '5xl'
    | '6xl'
    | '7xl';
}

const props = withDefaults(defineProps<Props>(), {
    maxWidth: '4xl',
});

const maxWidthClass = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
    '3xl': 'max-w-3xl',
    '4xl': 'max-w-4xl',
    '5xl': 'max-w-5xl',
    '6xl': 'max-w-6xl',
    '7xl': 'max-w-7xl',
}[props.maxWidth];

const { elementRef, isVisible } = useScrollAnimation({
    threshold: 0.2,
    rootMargin: '0px 0px -100px 0px',
});
</script>

<template>
    <div :class="['mx-auto px-4 py-24 sm:px-6 lg:px-8', maxWidthClass]">
        <div ref="elementRef" class="mb-16 text-center">
            <h1
                class="mb-4 text-4xl font-bold tracking-tight transition-all duration-500 ease-out sm:text-5xl"
                :class="isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-4 opacity-0'
                    "
            >
                {{ title }}
            </h1>
            <p
                v-if="subtitle"
                class="text-lg text-muted-foreground transition-all delay-75 duration-500 ease-out"
                :class="isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-4 opacity-0'
                    "
            >
                {{ subtitle }}
            </p>
        </div>
        <slot />
    </div>
</template>
