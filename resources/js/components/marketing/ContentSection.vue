<script setup lang="ts">
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
</script>

<template>
    <div :class="['mx-auto px-4 py-24 sm:px-6 lg:px-8', maxWidthClass]">
        <div class="mb-12 text-center">
            <h1 class="mb-4 text-4xl font-bold tracking-tight sm:text-5xl">
                {{ title }}
            </h1>
            <p v-if="subtitle" class="text-lg text-muted-foreground">
                {{ subtitle }}
            </p>
        </div>
        <slot />
    </div>
</template>
