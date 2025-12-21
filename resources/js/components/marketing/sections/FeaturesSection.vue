<script setup lang="ts">
import AnimatedSection from '@/components/marketing/AnimatedSection.vue';
import { Settings2, Sparkles, Zap } from 'lucide-vue-next';

const featureGroups = [
    {
        icon: Zap,
        title: 'Performance',
        features: [
            {
                name: 'Apple Silicon native',
                description: 'Hardware-accelerated encoding on M-series chips',
            },
            {
                name: 'Batch processing',
                description: 'Convert multiple files with concurrent jobs',
            },
            {
                name: 'Remux-first engine',
                description: "Copy streams when re-encoding isn't needed",
            },
        ],
    },
    {
        icon: Settings2,
        title: 'Control',
        features: [
            {
                name: 'Custom presets',
                description: 'Save your preferred settings for quick access',
            },
            {
                name: 'Format flexibility',
                description: 'MP4, MOV, MKV, WebM, audio, and more',
            },
            {
                name: 'Codec options',
                description: 'H.264, H.265/HEVC, ProRes, and VP9',
            },
        ],
    },
    {
        icon: Sparkles,
        title: 'Simplicity',
        features: [
            {
                name: 'Drag & drop',
                description: 'Add files from Finder or any app',
            },
            {
                name: 'Smart queue',
                description: 'Track progress across all conversions',
            },
            {
                name: 'Clean interface',
                description: 'Native macOS design that stays out of your way',
            },
        ],
    },
];
</script>

<template>
    <section id="features" class="bg-muted/30 py-24 sm:py-32">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <AnimatedSection>
                <div class="text-center">
                    <h2
                        class="text-3xl font-semibold tracking-tight text-foreground sm:text-4xl"
                    >
                        Everything You Need
                    </h2>
                    <p
                        class="mx-auto mt-4 max-w-xl text-lg text-muted-foreground"
                    >
                        Powerful features, thoughtfully organized.
                    </p>
                </div>
            </AnimatedSection>

            <div class="mt-16 grid gap-12 lg:grid-cols-3">
                <AnimatedSection
                    v-for="(group, index) in featureGroups"
                    :key="group.title"
                    :delay="index * 100"
                >
                    <div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10"
                            >
                                <component
                                    :is="group.icon"
                                    class="h-5 w-5 text-primary"
                                />
                            </div>
                            <h3 class="text-lg font-semibold text-foreground">
                                {{ group.title }}
                            </h3>
                        </div>

                        <ul class="mt-6 space-y-4">
                            <li
                                v-for="feature in group.features"
                                :key="feature.name"
                            >
                                <h4 class="font-medium text-foreground">
                                    {{ feature.name }}
                                </h4>
                                <p class="mt-0.5 text-sm text-muted-foreground">
                                    {{ feature.description }}
                                </p>
                            </li>
                        </ul>
                    </div>
                </AnimatedSection>
            </div>
        </div>
    </section>
</template>
