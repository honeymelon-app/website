<script setup lang="ts">
import AnimatedSection from '@/components/marketing/AnimatedSection.vue';

const steps = [
    {
        number: '01',
        title: 'Drop Your Files',
        description: 'Drag any video, audio, or image file into the queue. Honeymelon accepts most common formats.',
    },
    {
        number: '02',
        title: 'Choose Your Output',
        description: 'Select your target format and quality. Use a preset or customize every setting.',
    },
    {
        number: '03',
        title: 'Convert Intelligently',
        description: 'Honeymelon analyzes your files and picks the fastest path—remux when possible, transcode when needed.',
    },
];
</script>

<template>
    <section class="py-24 sm:py-32">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <AnimatedSection>
                <div class="text-center">
                    <h2
                        class="text-3xl font-semibold tracking-tight text-foreground sm:text-4xl"
                    >
                        How It Works
                    </h2>
                    <p class="mx-auto mt-4 max-w-xl text-lg text-muted-foreground">
                        Three steps. That's it.
                    </p>
                </div>
            </AnimatedSection>

            <div class="mt-16 grid gap-8 md:grid-cols-3">
                <AnimatedSection
                    v-for="(step, index) in steps"
                    :key="step.number"
                    :delay="index * 100"
                >
                    <div class="relative">
                        <div
                            class="mb-4 text-4xl font-semibold tabular-nums text-primary/20"
                        >
                            {{ step.number }}
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">
                            {{ step.title }}
                        </h3>
                        <p class="mt-2 text-muted-foreground">
                            {{ step.description }}
                        </p>
                    </div>
                </AnimatedSection>
            </div>
        </div>
    </section>
</template>
