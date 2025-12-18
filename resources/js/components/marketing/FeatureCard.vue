<script setup lang="ts">
import { useScrollAnimation } from '@/composables/useScrollAnimation';
import type { Component } from 'vue';

interface Props {
    title: string;
    description: string;
    icon?: Component;
    delay?: number;
}

withDefaults(defineProps<Props>(), {
    delay: 0,
});

const { elementRef, isVisible } = useScrollAnimation({
    threshold: 0.1,
    rootMargin: '0px 0px -30px 0px',
});
</script>

<template>
    <div
        ref="elementRef"
        class="transition-all duration-500 ease-out"
        :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'
            "
        :style="{ transitionDelay: `${delay}ms` }"
    >
        <div class="group relative">
            <div
                v-if="icon"
                class="mb-4 flex size-10 items-center justify-center rounded-lg bg-muted"
            >
                <component
                    :is="icon"
                    class="size-5 text-foreground"
                    :stroke-width="1.5"
                />
            </div>
            <h3 class="text-base font-semibold text-foreground">
                {{ title }}
            </h3>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                {{ description }}
            </p>
        </div>
    </div>
</template>
