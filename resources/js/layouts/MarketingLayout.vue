<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Button from '@/components/ui/button/Button.vue';
import Separator from '@/components/ui/separator/Separator.vue';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { Link } from '@inertiajs/vue3';
import { ExternalLink, Menu } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

const isScrolled = ref(false);

function handleScroll(): void {
    isScrolled.value = window.scrollY > 10;
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="min-h-screen bg-background">
        <!-- Header: Clean, minimal -->
        <header
            class="sticky top-0 z-50 transition-all duration-200"
            :class="[
                isScrolled
                    ? 'border-b border-border/50 bg-background/95 backdrop-blur-sm'
                    : 'border-b border-transparent bg-transparent',
            ]"
        >
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <Link href="/" class="flex items-center gap-2.5">
                        <AppLogoIcon class="h-8 w-8" />
                        <span class="text-lg font-semibold text-foreground"
                            >Honeymelon</span
                        >
                    </Link>

                    <!-- Desktop Navigation -->
                    <nav class="hidden items-center gap-1 md:flex">
                        <Button
                            as-child
                            variant="ghost"
                            size="sm"
                            class="text-muted-foreground hover:text-foreground"
                        >
                            <a href="/#features">Features</a>
                        </Button>
                        <Button
                            as-child
                            variant="ghost"
                            size="sm"
                            class="text-muted-foreground hover:text-foreground"
                        >
                            <a href="/#pricing">Pricing</a>
                        </Button>
                        <Button
                            as-child
                            variant="ghost"
                            size="sm"
                            class="text-muted-foreground hover:text-foreground"
                        >
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
                        <div class="ml-2">
                            <Button as-child size="sm">
                                <a href="/#download">Download</a>
                            </Button>
                        </div>
                    </nav>

                    <!-- Mobile Navigation -->
                    <div class="md:hidden">
                        <Sheet>
                            <SheetTrigger :as-child="true">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-9 w-9"
                                >
                                    <Menu class="h-5 w-5" />
                                    <span class="sr-only">Toggle menu</span>
                                </Button>
                            </SheetTrigger>
                            <SheetContent side="right" class="w-[280px]">
                                <SheetHeader>
                                    <SheetTitle class="text-left">
                                        <div class="flex items-center gap-2.5">
                                            <AppLogoIcon class="h-7 w-7" />
                                            <span
                                                class="text-base font-semibold"
                                                >Honeymelon</span
                                            >
                                        </div>
                                    </SheetTitle>
                                </SheetHeader>
                                <nav class="mt-8 flex flex-col gap-2">
                                    <Button
                                        as-child
                                        variant="ghost"
                                        class="justify-start"
                                    >
                                        <a href="/#features">Features</a>
                                    </Button>
                                    <Button
                                        as-child
                                        variant="ghost"
                                        class="justify-start"
                                    >
                                        <a href="/#pricing">Pricing</a>
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
                                            Docs
                                            <ExternalLink class="h-3 w-3" />
                                        </a>
                                    </Button>
                                    <Button as-child class="mt-2 justify-start">
                                        <a href="/#download">Download</a>
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

        <!-- Footer: Clean, editorial -->
        <footer class="border-t border-border/50 py-16">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Brand -->
                    <div class="sm:col-span-2 lg:col-span-1">
                        <div class="flex items-center gap-2.5">
                            <AppLogoIcon class="h-7 w-7" />
                            <span
                                class="text-base font-semibold text-foreground"
                                >Honeymelon</span
                            >
                        </div>
                        <p
                            class="mt-4 text-sm leading-relaxed text-muted-foreground"
                        >
                            Smart media conversion for macOS. Built for Apple
                            Silicon.
                        </p>
                    </div>

                    <!-- Product -->
                    <div>
                        <h3 class="text-sm font-medium text-foreground">
                            Product
                        </h3>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li>
                                <a
                                    href="/#features"
                                    class="text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    Features
                                </a>
                            </li>
                            <li>
                                <a
                                    href="/#download"
                                    class="text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    Download
                                </a>
                            </li>
                            <li>
                                <a
                                    href="/#pricing"
                                    class="text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    Pricing
                                </a>
                            </li>
                            <li>
                                <a
                                    href="https://docs.honeymelon.app"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-1 text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    Documentation
                                    <ExternalLink class="h-3 w-3" />
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Resources -->
                    <div>
                        <h3 class="text-sm font-medium text-foreground">
                            Resources
                        </h3>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li>
                                <a
                                    href="https://github.com/honeymelon-app/honeymelon"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-1 text-muted-foreground transition-colors hover:text-foreground"
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
                                    class="inline-flex items-center gap-1 text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    Support
                                    <ExternalLink class="h-3 w-3" />
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div>
                        <h3 class="text-sm font-medium text-foreground">
                            Legal
                        </h3>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li>
                                <Link
                                    href="/privacy"
                                    class="text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    Privacy Policy
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/terms"
                                    class="text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    Terms of Use
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <Separator class="my-10" />

                <div
                    class="flex flex-col items-center justify-between gap-4 sm:flex-row"
                >
                    <p class="text-sm text-muted-foreground">
                        © 2025 Honeymelon. All rights reserved.
                    </p>
                    <p class="text-sm text-muted-foreground/70">
                        macOS 13+ · Apple Silicon · Privacy-First
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
