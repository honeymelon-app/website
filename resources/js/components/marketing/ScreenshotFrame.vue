<script setup lang="ts">
import { useScrollAnimation } from '@/composables/useScrollAnimation';
import { computed } from 'vue';

interface Props {
    src: string;
    alt: string;
    delay?: number;
    direction?: 'left' | 'right';
}

const props = withDefaults(defineProps<Props>(), {
    delay: 0,
    direction: 'right',
});

const { elementRef, isVisible } = useScrollAnimation({
    threshold: 0.15,
    rootMargin: '0px 0px -50px 0px',
});

const transitionClass = computed(() => {
    if (isVisible.value) {
        return 'translate-x-0 translate-y-0 opacity-100 rotate-0';
    }
    if (props.direction === 'left') {
        return '-translate-x-8 opacity-0 rotate-1';
    }
    return 'translate-x-8 opacity-0 -rotate-1';
});
</script>

<template>
    <div
        ref="elementRef"
        class="flex justify-center transition-all duration-700 ease-out"
        :class="transitionClass"
        :style="{ transitionDelay: `${delay}ms` }"
    >
        <img :src="src" :alt="alt" class="h-auto max-w-full" loading="lazy" />
    </div>
</template>
