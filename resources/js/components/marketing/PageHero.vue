<script setup lang="ts">
import Badge from '@/components/ui/badge/Badge.vue';
import { onMounted, ref } from 'vue';

interface Props {
    badge?: string;
    badgeIcon?: any;
    title: string;
    highlightedText?: string;
    description: string;
}

defineProps<Props>();

const isVisible = ref(false);

onMounted(() => {
    // Check for reduced motion preference
    const prefersReducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    if (prefersReducedMotion) {
        isVisible.value = true;
    } else {
        // Small delay for smoother page load animation
        requestAnimationFrame(() => {
            isVisible.value = true;
        });
    }
});
</script>

<template>
    <section
        class="relative overflow-hidden border-b border-border/40 py-20 sm:py-28"
    >
        <!-- Subtle grid pattern overlay -->
        <div
            class="absolute inset-0 -z-10 bg-[linear-gradient(to_right,theme(colors.border/5%)_1px,transparent_1px),linear-gradient(to_bottom,theme(colors.border/5%)_1px,transparent_1px)] bg-[size:4rem_4rem]"
        />
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center text-center">
                <!-- Badge with fade-in -->
                <Badge
                    v-if="badge"
                    variant="outline"
                    class="mb-6 border-primary/50 px-4 py-1.5 text-sm transition-all duration-500 ease-out"
                    :class="isVisible
                            ? 'translate-y-0 opacity-100'
                            : '-translate-y-2 opacity-0'
                        "
                >
                    <component
                        v-if="badgeIcon"
                        :is="badgeIcon"
                        class="mr-0.5 size-5"
                    />
                    {{ badge }}
                </Badge>

                <!-- Title with staggered fade-in -->
                <h1
                    class="mb-6 max-w-4xl text-4xl leading-tight font-bold tracking-tight text-foreground transition-all delay-100 duration-600 ease-out sm:text-5xl md:text-6xl lg:text-7xl lg:leading-tight"
                    :class="isVisible
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-4 opacity-0'
                        "
                >
                    {{ title }}
                    <span
                        v-if="highlightedText"
                        class="text-primary"
                        >{{ highlightedText }}</span
                    >
                </h1>

                <!-- Description with fade-in -->
                <p
                    class="mb-10 max-w-2xl text-lg leading-relaxed text-muted-foreground transition-all delay-200 duration-600 ease-out sm:text-xl"
                    :class="isVisible
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-4 opacity-0'
                        "
                >
                    {{ description }}
                </p>

                <!-- Actions slot with fade-in -->
                <div
                    class="transition-all delay-300 duration-600 ease-out"
                    :class="isVisible
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-4 opacity-0'
                        "
                >
                    <slot name="actions" />
                </div>

                <!-- Footer slot with fade-in -->
                <div
                    class="w-full transition-all delay-400 duration-700 ease-out"
                    :class="isVisible
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-6 opacity-0'
                        "
                >
                    <slot name="footer" />
                </div>
            </div>
        </div>
    </section>
</template>
