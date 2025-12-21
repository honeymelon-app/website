<script setup lang="ts">
import AnimatedSection from '@/components/marketing/AnimatedSection.vue';
import Button from '@/components/ui/button/Button.vue';
import { ArrowRight } from 'lucide-vue-next';
</script>

<template>
    <section class="py-24 sm:py-32">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <AnimatedSection>
                <h2
                    class="text-3xl font-semibold tracking-tight text-foreground sm:text-4xl"
                >
                    Built for macOS.
                    <br />
                    Designed to stay out of your way.
                </h2>
                <p class="mt-6 text-lg text-muted-foreground">
                    Download Honeymelon and experience intelligent,
                    privacy-first media conversion on your Mac.
                </p>
                <div
                    class="mt-10 flex flex-col items-center gap-4 sm:flex-row sm:justify-center"
                >
                    <Button as-child size="lg" class="h-12 px-8 text-base">
                        <a href="#download">
                            Download for macOS
                            <ArrowRight class="ml-2 h-4 w-4" />
                        </a>
                    </Button>
                    <Button
                        as-child
                        variant="ghost"
                        size="lg"
                        class="h-12 text-base text-muted-foreground"
                    >
                        <a href="#pricing">View Pricing</a>
                    </Button>
                </div>
            </AnimatedSection>
        </div>
    </section>
</template>
