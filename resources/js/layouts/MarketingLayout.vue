<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Button from '@/components/ui/button/Button.vue';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { Menu } from 'lucide-vue-next';
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

const navLinks = [
    { href: '/#features', label: 'Features' },
    { href: '/pricing', label: 'Pricing' },
    { href: 'https://docs.honeymelon.app', label: 'Docs', external: true },
];
</script>

<template>
    <div class="min-h-screen bg-background">
        <!-- Header -->
        <header
            class="sticky top-0 z-50 transition-all duration-200"
            :class="[
                isScrolled
                    ? 'border-b border-border bg-background/95 backdrop-blur-sm'
                    : 'bg-transparent',
            ]"
        >
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-14 items-center justify-between">
                    <a href="/" class="flex items-center gap-2.5">
                        <AppLogoIcon class="size-8" />
                        <span class="text-lg font-semibold tracking-tight"
                            >Honeymelon</span
                        >
                    </a>

                    <!-- Desktop Navigation -->
                    <nav class="hidden items-center gap-8 md:flex">
                        <template v-for="link in navLinks" :key="link.href">
                            <a
                                :href="link.href"
                                :target="link.external ? '_blank' : undefined"
                                :rel="link.external
                                        ? 'noopener noreferrer'
                                        : undefined
                                    "
                                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                            >
                                {{ link.label }}
                            </a>
                        </template>
                    </nav>

                    <div class="hidden md:block">
                        <Button as-child size="sm">
                            <a href="/download">Download</a>
                        </Button>
                    </div>

                    <!-- Mobile Navigation -->
                    <div class="md:hidden">
                        <Sheet>
                            <SheetTrigger :as-child="true">
                                <Button variant="ghost" size="icon">
                                    <Menu class="size-5" />
                                    <span class="sr-only">Toggle menu</span>
                                </Button>
                            </SheetTrigger>
                            <SheetContent side="right" class="w-72">
                                <SheetHeader>
                                    <SheetTitle class="text-left">
                                        <div class="flex items-center gap-2.5">
                                            <AppLogoIcon class="size-7" />
                                            <span class="font-semibold"
                                                >Honeymelon</span
                                            >
                                        </div>
                                    </SheetTitle>
                                </SheetHeader>
                                <nav class="mt-8 flex flex-col gap-1">
                                    <template
                                        v-for="link in navLinks"
                                        :key="link.href"
                                    >
                                        <a
                                            :href="link.href"
                                            :target="link.external
                                                    ? '_blank'
                                                    : undefined
                                                "
                                            :rel="link.external
                                                    ? 'noopener noreferrer'
                                                    : undefined
                                                "
                                            class="rounded-md px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                        >
                                            {{ link.label }}
                                        </a>
                                    </template>
                                    <div class="mt-4 px-3">
                                        <Button as-child class="w-full">
                                            <a href="/download">Download</a>
                                        </Button>
                                    </div>
                                </nav>
                            </SheetContent>
                        </Sheet>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t border-border bg-muted/30">
            <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Brand -->
                    <div class="sm:col-span-2 lg:col-span-1">
                        <a href="/" class="flex items-center gap-2.5">
                            <AppLogoIcon class="size-7" />
                            <span class="font-semibold">Honeymelon</span>
                        </a>
                        <p
                            class="mt-4 max-w-xs text-sm leading-relaxed text-muted-foreground"
                        >
                            Smart media converter for macOS. Hardware
                            accelerated, privacy-first.
                        </p>
                    </div>

                    <!-- Product -->
                    <div>
                        <h3 class="text-sm font-semibold">Product</h3>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li>
                                <a
                                    href="/#features"
                                    class="text-muted-foreground transition-colors hover:text-foreground"
                                    >Features</a
                                >
                            </li>
                            <li>
                                <a
                                    href="/pricing"
                                    class="text-muted-foreground transition-colors hover:text-foreground"
                                    >Pricing</a
                                >
                            </li>
                            <li>
                                <a
                                    href="/download"
                                    class="text-muted-foreground transition-colors hover:text-foreground"
                                    >Download</a
                                >
                            </li>
                        </ul>
                    </div>

                    <!-- Resources -->
                    <div>
                        <h3 class="text-sm font-semibold">Resources</h3>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li>
                                <a
                                    href="https://docs.honeymelon.app"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-muted-foreground transition-colors hover:text-foreground"
                                    >Documentation</a
                                >
                            </li>
                            <li>
                                <a
                                    href="https://github.com/honeymelon-app"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-muted-foreground transition-colors hover:text-foreground"
                                    >GitHub</a
                                >
                            </li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div>
                        <h3 class="text-sm font-semibold">Legal</h3>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li>
                                <a
                                    href="/privacy"
                                    class="text-muted-foreground transition-colors hover:text-foreground"
                                    >Privacy</a
                                >
                            </li>
                            <li>
                                <a
                                    href="/terms"
                                    class="text-muted-foreground transition-colors hover:text-foreground"
                                    >Terms</a
                                >
                            </li>
                        </ul>
                    </div>
                </div>

                <div
                    class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-border pt-8 sm:flex-row"
                >
                    <p class="text-sm text-muted-foreground">
                        © {{ new Date().getFullYear() }} Honeymelon. All rights
                        reserved.
                    </p>
                    <p class="text-sm text-muted-foreground">
                        Requires macOS 13+ and Apple Silicon
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
