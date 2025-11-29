<script setup lang="ts">
import { useScrollAnimation } from '@/composables/useScrollAnimation';
import { computed } from 'vue';

interface Props {
    delay?: number;
    direction?: 'up' | 'down' | 'left' | 'right';
    duration?: number;
    distance?: string;
    once?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    delay: 0,
    direction: 'up',
    duration: 600,
    distance: '24px',
    once: true,
});

const { elementRef, isVisible } = useScrollAnimation({
    once: props.once,
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px',
});

const transitionStyle = computed(() => ({
    transitionDelay: `${props.delay}ms`,
    transitionDuration: `${props.duration}ms`,
}));
</script>

<template>
    <div
        ref="elementRef"
        :style="transitionStyle"
        class="transition-all ease-out"
        :class="[
            isVisible
                ? 'translate-x-0 translate-y-0 opacity-100'
                : `opacity-0 ${
                      direction === 'up'
                          ? 'translate-y-6'
                          : direction === 'down'
                            ? '-translate-y-6'
                            : direction === 'left'
                              ? 'translate-x-6'
                              : '-translate-x-6'
                  }`,
        ]"
    >
        <slot />
    </div>
</template>
