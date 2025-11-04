<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';
import Separator from '@/components/ui/separator/Separator.vue';
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-background to-muted/20">
        <!-- Header -->
        <header
            class="sticky top-0 z-50 border-b border-border/40 bg-background/80 backdrop-blur-sm"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <a href="/" class="flex items-center gap-3">
                        <AppLogoIcon
                            class="h-10 w-10 transition-transform hover:scale-110"
                        />
                        <span class="text-xl font-bold">Honeymelon</span>
                    </a>
                    <nav class="flex items-center gap-3">
                        <Button as-child variant="ghost">
                            <a href="/#features">Features</a>
                        </Button>
                        <Button as-child variant="ghost">
                            <a href="/pricing">Pricing</a>
                        </Button>
                        <Button as-child>
                            <a href="/download">Download</a>
                        </Button>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <slot />

        <!-- Footer -->
        <footer class="border-t border-border/40 bg-muted/50 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <div class="mb-6 flex items-center gap-3">
                            <AppLogoIcon class="h-8 w-8" />
                            <span class="text-lg font-bold">Honeymelon</span>
                        </div>
                        <p
                            class="text-sm leading-relaxed text-muted-foreground"
                        >
                            Smart media converter for macOS Apple Silicon.
                            Convert video, audio, and images with remux-first
                            intelligence.
                        </p>
                    </div>
                    <div>
                        <h3 class="mb-4 text-sm font-semibold">Product</h3>
                        <ul class="space-y-3 text-sm text-muted-foreground">
                            <li>
                                <a
                                    href="/#features"
                                    class="transition-colors hover:text-foreground"
                                    >Features</a
                                >
                            </li>
                            <li>
                                <a
                                    href="/pricing"
                                    class="transition-colors hover:text-foreground"
                                    >Pricing</a
                                >
                            </li>
                            <li>
                                <a
                                    href="https://docs.honeymelon.app"
                                    target="_blank"
                                    rel="noopener"
                                    class="transition-colors hover:text-foreground"
                                    >Documentation</a
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-4 text-sm font-semibold">Resources</h3>
                        <ul class="space-y-3 text-sm text-muted-foreground">
                            <li>
                                <a
                                    href="https://docs.honeymelon.app"
                                    target="_blank"
                                    rel="noopener"
                                    class="transition-colors hover:text-foreground"
                                    >Documentation</a
                                >
                            </li>
                            <li>
                                <a
                                    href="/download"
                                    class="transition-colors hover:text-foreground"
                                    >Download</a
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-4 text-sm font-semibold">Legal</h3>
                        <ul class="space-y-3 text-sm text-muted-foreground">
                            <li>
                                <a
                                    href="/privacy"
                                    class="transition-colors hover:text-foreground"
                                    >Privacy Policy</a
                                >
                            </li>
                            <li>
                                <a
                                    href="/terms"
                                    class="transition-colors hover:text-foreground"
                                    >Terms of Use</a
                                >
                            </li>
                            <li>
                                <a
                                    href="/terms"
                                    class="transition-colors hover:text-foreground"
                                    >License Agreement</a
                                >
                            </li>
                        </ul>
                    </div>
                </div>
                <Separator class="my-10" />
                <div
                    class="flex flex-col items-center justify-between gap-4 sm:flex-row"
                >
                    <p class="text-sm text-muted-foreground">
                        © 2025 Honeymelon. Privacy-first media conversion for
                        Mac.
                    </p>
                    <div class="flex gap-3">
                        <Badge variant="outline" class="px-3 py-1"
                            >macOS 13+</Badge
                        >
                        <Badge variant="outline" class="px-3 py-1"
                            >Apple Silicon</Badge
                        >
                        <Badge variant="outline" class="px-3 py-1"
                            >Privacy-First</Badge
                        >
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
