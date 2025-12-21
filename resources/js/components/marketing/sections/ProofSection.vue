<script setup lang="ts">
import AnimatedSection from '@/components/marketing/AnimatedSection.vue';
import { Check } from 'lucide-vue-next';

const proofPoints = [
    {
        title: 'Built exclusively for macOS',
        description: 'Native ARM64 binary with hardware acceleration. No Rosetta, no Electron, no compromises.',
    },
    {
        title: 'Privacy-first architecture',
        description: 'Your files never leave your machine. One-time activation, then fully offline forever.',
    },
    {
        title: 'Remux-first intelligence',
        description: 'Automatically copies streams when possible, transcodes only when necessary.',
    },
];
</script>

<template>
    <section class="border-t border-border/50 py-20 sm:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <AnimatedSection>
                <div class="text-center">
                    <h2
                        class="text-2xl font-semibold tracking-tight text-foreground sm:text-3xl"
                    >
                        Why Honeymelon?
                    </h2>
                </div>

                <ul class="mt-12 space-y-6">
                    <li
                        v-for="(point, index) in proofPoints"
                        :key="index"
                        class="flex gap-4"
                    >
                        <div
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10"
                        >
                            <Check class="h-4 w-4 text-primary" />
                        </div>
                        <div>
                            <h3 class="font-medium text-foreground">
                                {{ point.title }}
                            </h3>
                            <p class="mt-1 text-muted-foreground">
                                {{ point.description }}
                            </p>
                        </div>
                    </li>
                </ul>
            </AnimatedSection>
        </div>
    </section>
</template>
