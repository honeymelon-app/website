<script setup lang="ts">
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
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
        :class="
            isVisible ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'
        "
        :style="{ transitionDelay: `${delay}ms` }"
    >
        <Card
            class="group relative h-full overflow-hidden border-border/50 transition-all duration-300 hover:-translate-y-1 hover:border-primary/30"
        >
            <!-- Subtle gradient overlay on hover -->
            <div
                class="absolute inset-0 -z-10 bg-gradient-to-br from-primary/5 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"
            />

            <CardHeader class="pb-4">
                <div
                    v-if="icon"
                    class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-primary/10 transition-all duration-300 group-hover:scale-110 group-hover:bg-primary/15"
                >
                    <component
                        :is="icon"
                        class="h-7 w-7 text-primary"
                        :stroke-width="1.5"
                    />
                </div>
                <CardTitle class="text-xl">{{ title }}</CardTitle>
            </CardHeader>
            <CardContent>
                <CardDescription class="text-base leading-relaxed">
                    {{ description }}
                </CardDescription>
            </CardContent>
        </Card>
    </div>
</template>
