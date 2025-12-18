<script setup lang="ts">
import { onMounted, ref } from 'vue';

interface Props {
    title: string;
    highlightedText?: string;
    description: string;
}

defineProps<Props>();

const isVisible = ref(false);

onMounted(() => {
    const prefersReducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    if (prefersReducedMotion) {
        isVisible.value = true;
    } else {
        requestAnimationFrame(() => {
            isVisible.value = true;
        });
    }
});
</script>

<template>
    <section class="relative overflow-hidden pb-16 pt-20 sm:pb-24 sm:pt-32">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center text-center">
                <!-- Title -->
                <h1
                    class="max-w-4xl text-4xl font-medium tracking-tight text-foreground transition-all duration-500 ease-out sm:text-5xl lg:text-6xl"
                    :class="isVisible
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-4 opacity-0'
                        "
                >
                    {{ title
                    }}<span v-if="highlightedText" class="text-primary">{{
                        highlightedText
                    }}</span>
                </h1>

                <!-- Description -->
                <p
                    class="mt-6 max-w-2xl text-lg leading-relaxed text-muted-foreground transition-all delay-100 duration-500 ease-out"
                    :class="isVisible
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-4 opacity-0'
                        "
                >
                    {{ description }}
                </p>

                <!-- Actions slot -->
                <div
                    class="mt-10 transition-all delay-200 duration-500 ease-out"
                    :class="isVisible
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-4 opacity-0'
                        "
                >
                    <slot name="actions" />
                </div>

                <!-- Footer slot for additional content like screenshots -->
                <div
                    class="mt-16 w-full transition-all delay-300 duration-500 ease-out"
                    :class="isVisible
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-4 opacity-0'
                        "
                >
                    <slot name="footer" />
                </div>
            </div>
        </div>
    </section>
</template>
