<script setup lang="ts">
import AnimatedSection from '@/components/marketing/AnimatedSection.vue';
import ScreenshotFrame from '@/components/marketing/ScreenshotFrame.vue';
import { Check } from 'lucide-vue-next';
</script>

<template>
    <section class="border-t border-border/50 bg-muted/30 py-24 sm:py-32">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <AnimatedSection>
                <div class="text-center">
                    <h2
                        class="text-3xl font-semibold tracking-tight text-foreground sm:text-4xl"
                    >
                        Designed for macOS
                    </h2>
                    <p
                        class="mx-auto mt-4 max-w-xl text-lg text-muted-foreground"
                    >
                        A native interface that feels right at home on your Mac.
                    </p>
                </div>
            </AnimatedSection>

            <!-- Queue Management Screenshot -->
            <div class="mt-20 grid items-center gap-16 lg:grid-cols-2">
                <AnimatedSection direction="left">
                    <div>
                        <h3
                            class="text-2xl font-semibold tracking-tight text-foreground"
                        >
                            Effortless Queue Management
                        </h3>
                        <p class="mt-4 text-lg text-muted-foreground">
                            Track pending, active, and completed conversions in
                            one clean interface. Batch process with confidence.
                        </p>
                        <ul class="mt-6 space-y-3">
                            <li
                                class="flex items-center gap-3 text-muted-foreground"
                            >
                                <Check class="h-5 w-5 shrink-0 text-primary" />
                                <span>Real-time progress tracking</span>
                            </li>
                            <li
                                class="flex items-center gap-3 text-muted-foreground"
                            >
                                <Check class="h-5 w-5 shrink-0 text-primary" />
                                <span>Concurrent job management</span>
                            </li>
                            <li
                                class="flex items-center gap-3 text-muted-foreground"
                            >
                                <Check class="h-5 w-5 shrink-0 text-primary" />
                                <span>Accurate ETA calculations</span>
                            </li>
                        </ul>
                    </div>
                </AnimatedSection>
                <div>
                    <ScreenshotFrame
                        src="/images/screens/Pending-Files-Queue-Light-Mode.png"
                        alt="Queue Management Interface"
                        direction="right"
                        :delay="100"
                    />
                </div>
            </div>

            <!-- Dark Mode Screenshot -->
            <div class="mt-32 grid items-center gap-16 lg:grid-cols-2">
                <div class="order-2 lg:order-1">
                    <ScreenshotFrame
                        src="/images/screens/Pending-Files-Active-Files-Completed-Files-Dark-Mode.png"
                        alt="Dark Mode Interface"
                        direction="left"
                        :delay="100"
                    />
                </div>
                <AnimatedSection direction="right" class="order-1 lg:order-2">
                    <div>
                        <h3
                            class="text-2xl font-semibold tracking-tight text-foreground"
                        >
                            Beautiful in Any Light
                        </h3>
                        <p class="mt-4 text-lg text-muted-foreground">
                            Full dark mode support that follows your system
                            preferences. Easy on the eyes during late-night
                            sessions.
                        </p>
                        <ul class="mt-6 space-y-3">
                            <li
                                class="flex items-center gap-3 text-muted-foreground"
                            >
                                <Check class="h-5 w-5 shrink-0 text-primary" />
                                <span>Automatic system sync</span>
                            </li>
                            <li
                                class="flex items-center gap-3 text-muted-foreground"
                            >
                                <Check class="h-5 w-5 shrink-0 text-primary" />
                                <span>Carefully balanced colors</span>
                            </li>
                            <li
                                class="flex items-center gap-3 text-muted-foreground"
                            >
                                <Check class="h-5 w-5 shrink-0 text-primary" />
                                <span>Reduced eye strain</span>
                            </li>
                        </ul>
                    </div>
                </AnimatedSection>
            </div>
        </div>
    </section>
</template>
