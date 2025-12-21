<script setup lang="ts">
import AnimatedSection from '@/components/marketing/AnimatedSection.vue';
import { Check, X } from 'lucide-vue-next';

const comparisonFeatures = [
    { feature: 'One-time purchase', us: true, them: false },
    { feature: 'Works fully offline', us: true, them: false },
    { feature: 'No data collection', us: true, them: false },
    { feature: 'Native Apple Silicon', us: true, them: false },
];
</script>

<template>
    <section class="py-24 sm:py-32">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <AnimatedSection>
                <div class="text-center">
                    <h2
                        class="text-3xl font-semibold tracking-tight text-foreground sm:text-4xl"
                    >
                        The Honeymelon Difference
                    </h2>
                    <p class="mt-4 text-lg text-muted-foreground">
                        How we compare to subscription-based alternatives.
                    </p>
                </div>
            </AnimatedSection>

            <AnimatedSection :delay="100">
                <div
                    class="mt-12 overflow-hidden rounded-2xl border border-border/50 bg-background"
                >
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-border/50">
                                <th
                                    class="px-6 py-4 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Feature
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-sm font-semibold text-foreground"
                                >
                                    Honeymelon
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-sm font-medium text-muted-foreground"
                                >
                                    Others
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(item, index) in comparisonFeatures"
                                :key="item.feature"
                                :class="index !== comparisonFeatures.length - 1
                                        ? 'border-b border-border/50'
                                        : ''
                                    "
                            >
                                <td class="px-6 py-4 text-sm text-foreground">
                                    {{ item.feature }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center">
                                        <div
                                            v-if="item.us"
                                            class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/10"
                                        >
                                            <Check
                                                class="h-4 w-4 text-primary"
                                                :stroke-width="2.5"
                                            />
                                        </div>
                                        <X
                                            v-else
                                            class="h-5 w-5 text-muted-foreground/50"
                                        />
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center">
                                        <div
                                            v-if="item.them"
                                            class="flex h-6 w-6 items-center justify-center rounded-full bg-muted"
                                        >
                                            <Check
                                                class="h-4 w-4 text-muted-foreground"
                                            />
                                        </div>
                                        <X
                                            v-else
                                            class="h-5 w-5 text-muted-foreground/50"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </AnimatedSection>
        </div>
    </section>
</template>
