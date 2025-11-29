<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';
import Separator from '@/components/ui/separator/Separator.vue';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { ExternalLink, Menu } from 'lucide-vue-next';
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

                    <!-- Desktop Navigation -->
                    <nav class="hidden items-center gap-3 md:flex">
                        <Button as-child variant="ghost">
                            <a href="/pricing">Pricing</a>
                        </Button>
                        <Button as-child variant="ghost">
                            <a
                                href="https://docs.honeymelon.app"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5"
                            >
                                Docs
                                <ExternalLink class="h-3 w-3" />
                            </a>
                        </Button>
                        <Button as-child>
                            <a href="/download">Download</a>
                        </Button>
                    </nav>

                    <!-- Mobile Navigation -->
                    <div class="md:hidden">
                        <Sheet>
                            <SheetTrigger :as-child="true">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-10 w-10"
                                >
                                    <Menu class="h-5 w-5" />
                                    <span class="sr-only">Toggle menu</span>
                                </Button>
                            </SheetTrigger>
                            <SheetContent side="right" class="w-[300px]">
                                <SheetHeader>
                                    <SheetTitle class="text-left">
                                        <div class="flex items-center gap-3">
                                            <AppLogoIcon class="h-8 w-8" />
                                            <span class="text-lg font-bold"
                                                >Honeymelon</span
                                            >
                                        </div>
                                    </SheetTitle>
                                </SheetHeader>
                                <nav class="mt-8 flex flex-col gap-4">
                                    <Button
                                        as-child
                                        variant="ghost"
                                        class="justify-start"
                                    >
                                        <a href="/pricing">Pricing</a>
                                    </Button>
                                    <Button
                                        as-child
                                        variant="ghost"
                                        class="justify-start"
                                    >
                                        <a
                                            href="https://docs.honeymelon.app"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5"
                                        >
                                            Documentation
                                            <ExternalLink class="h-3 w-3" />
                                        </a>
                                    </Button>
                                    <Button as-child class="justify-start">
                                        <a href="/download">Download</a>
                                    </Button>
                                </nav>
                            </SheetContent>
                        </Sheet>
                    </div>
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
                                    href="/download"
                                    class="transition-colors hover:text-foreground"
                                    >Download</a
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
                                    class="inline-flex items-center gap-1 transition-colors hover:text-foreground"
                                >
                                    Documentation
                                    <ExternalLink class="h-3 w-3" />
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-4 text-sm font-semibold">Resources</h3>
                        <ul class="space-y-3 text-sm text-muted-foreground">
                            <li>
                                <a
                                    href="https://github.com/honeymelon-app/honeymelon"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-1 transition-colors hover:text-foreground"
                                >
                                    GitHub
                                    <ExternalLink class="h-3 w-3" />
                                </a>
                            </li>
                            <li>
                                <a
                                    href="https://docs.honeymelon.app"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-1 transition-colors hover:text-foreground"
                                >
                                    Support
                                    <ExternalLink class="h-3 w-3" />
                                </a>
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
                    <p
                        class="text-center text-sm text-muted-foreground sm:text-left"
                    >
                        © 2025 Honeymelon. Privacy-first media conversion for
                        Mac.
                    </p>
                    <div class="flex flex-wrap justify-center gap-3">
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
