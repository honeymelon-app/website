<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import AnimatedSection from '@/components/marketing/AnimatedSection.vue';
import FeatureCard from '@/components/marketing/FeatureCard.vue';
import PageHero from '@/components/marketing/PageHero.vue';
import ScreenshotFrame from '@/components/marketing/ScreenshotFrame.vue';
import SectionHeader from '@/components/marketing/SectionHeader.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpen,
    Check,
    Film,
    Image,
    Lock,
    Music,
    Rocket,
    Settings,
    Video,
    Wrench,
    Zap,
} from 'lucide-vue-next';

import featuresData from '@/content/features.json';
import formatsData from '@/content/formats.json';
import keyFeaturesData from '@/content/keyFeatures.json';
import pipelineData from '@/content/pipeline.json';

const iconMap: Record<string, any> = {
    Zap,
    Lock,
    Settings,
    Film,
    Rocket,
    Wrench,
};

const features = featuresData.map((feature) => ({
    ...feature,
    icon: iconMap[feature.icon],
}));

const pipeline = pipelineData;
const formats = formatsData;
const keyFeatures = keyFeaturesData;
</script>

<template>
    <Head title="Honeymelon - Smart Media Converter for macOS" />

    <MarketingLayout>
        <PageHero
            badge="Native macOS Media Converter"
            :badge-icon="AppLogoIcon"
            title="Smart Media Conversion for "
            highlighted-text="Apple Silicon Macs"
            description="Remux-first, privacy-minded media converter that intelligently preserves quality while maximizing speed. Built exclusively for macOS Apple Silicon."
        >
            <template #actions>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <Button
                        as-child
                        size="lg"
                        class="group text-base transition-all duration-300 hover:scale-[1.02]"
                    >
                        <a href="/download">
                            Download for macOS
                            <ArrowRight
                                class="ml-2 h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                            />
                        </a>
                    </Button>
                    <Button
                        as-child
                        variant="outline"
                        size="lg"
                        class="text-base transition-all duration-300 hover:bg-muted/80"
                    >
                        <a
                            href="https://docs.honeymelon.app"
                            target="_blank"
                            rel="noopener"
                        >
                            <BookOpen class="mr-2 h-4 w-4" />
                            Documentation
                        </a>
                    </Button>
                </div>
            </template>
            <template #footer>
                <div
                    class="mt-12 flex flex-wrap items-center justify-center gap-3"
                >
                    <span class="text-sm font-medium text-muted-foreground"
                        >Requirements:</span
                    >
                    <Badge
                        variant="secondary"
                        class="px-3 py-1 transition-colors duration-300 hover:bg-secondary/80"
                        >macOS 13+</Badge
                    >
                    <Badge
                        variant="secondary"
                        class="px-3 py-1 transition-colors duration-300 hover:bg-secondary/80"
                        >Apple Silicon</Badge
                    >
                    <Badge
                        variant="secondary"
                        class="px-3 py-1 transition-colors duration-300 hover:bg-secondary/80"
                        >Privacy-First</Badge
                    >
                </div>

                <!-- Hero App Screenshot with enhanced frame -->
                <div class="mt-10">
                    <ScreenshotFrame
                        src="/images/screens/Pending-Files-Active-Files-Completed-Files-Light-Mode.png"
                        alt="Honeymelon App Interface showing pending, active, and completed files"
                        :delay="500"
                    />
                </div>
            </template>
        </PageHero>

        <!-- Features Section with hover effects -->
        <section id="features" class="border-b border-border/40 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    badge="Features"
                    title="Everything You Need"
                    description="Built with modern technologies and best practices for production-ready deployments."
                />

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <FeatureCard
                        v-for="(feature, index) in features"
                        :key="feature.title"
                        :title="feature.title"
                        :description="feature.description"
                        :icon="feature.icon"
                        :delay="index * 75"
                    />
                </div>
            </div>
        </section>

        <!-- App Screenshots Showcase -->
        <section class="border-b border-border/40 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    badge="Interface"
                    title="Beautiful & Intuitive"
                    description="Designed for macOS with native performance and elegant UI"
                />

                <div class="space-y-20">
                    <!-- Queue Management -->
                    <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                        <AnimatedSection
                            class="order-2 lg:order-1"
                            direction="left"
                        >
                            <Badge
                                variant="outline"
                                class="mb-4 px-3 py-1 transition-colors hover:bg-muted"
                                >Queue Management</Badge
                            >
                            <h3 class="mb-4 text-3xl font-bold tracking-tight">
                                Effortless File Processing
                            </h3>
                            <p
                                class="mb-6 text-lg leading-relaxed text-muted-foreground"
                            >
                                Drop your files and watch them process
                                automatically. Track pending, active, and
                                completed conversions all in one clean
                                interface.
                            </p>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3">
                                    <div
                                        class="mt-1 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <Check
                                            class="h-3 w-3 text-primary"
                                            :stroke-width="3"
                                        />
                                    </div>
                                    <span class="text-base"
                                        >Real-time progress tracking</span
                                    >
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="mt-1 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <Check
                                            class="h-3 w-3 text-primary"
                                            :stroke-width="3"
                                        />
                                    </div>
                                    <span class="text-base"
                                        >Batch processing support</span
                                    >
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="mt-1 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <Check
                                            class="h-3 w-3 text-primary"
                                            :stroke-width="3"
                                        />
                                    </div>
                                    <span class="text-base"
                                        >Automatic error handling</span
                                    >
                                </li>
                            </ul>
                        </AnimatedSection>
                        <div class="order-1 lg:order-2">
                            <ScreenshotFrame
                                src="/images/screens/Pending-Files-Queue-Light-Mode.png"
                                alt="Queue Management"
                                direction="right"
                                :delay="100"
                            />
                        </div>
                    </div>

                    <!-- Dark Mode Support -->
                    <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                        <div>
                            <ScreenshotFrame
                                src="/images/screens/Pending-Files-Active-Files-Completed-Files-Dark-Mode.png"
                                alt="Dark Mode Interface"
                                direction="left"
                                :delay="100"
                            />
                        </div>
                        <AnimatedSection direction="right">
                            <Badge
                                variant="outline"
                                class="mb-4 px-3 py-1 transition-colors hover:bg-muted"
                                >Dark Mode</Badge
                            >
                            <h3 class="mb-4 text-3xl font-bold tracking-tight">
                                Easy on the Eyes
                            </h3>
                            <p
                                class="mb-6 text-lg leading-relaxed text-muted-foreground"
                            >
                                Beautiful dark mode that automatically adapts to
                                your system preferences. Perfect for long
                                conversion sessions.
                            </p>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3">
                                    <div
                                        class="mt-1 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <Check
                                            class="h-3 w-3 text-primary"
                                            :stroke-width="3"
                                        />
                                    </div>
                                    <span class="text-base"
                                        >System preference sync</span
                                    >
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="mt-1 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <Check
                                            class="h-3 w-3 text-primary"
                                            :stroke-width="3"
                                        />
                                    </div>
                                    <span class="text-base"
                                        >Carefully crafted color palette</span
                                    >
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="mt-1 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <Check
                                            class="h-3 w-3 text-primary"
                                            :stroke-width="3"
                                        />
                                    </div>
                                    <span class="text-base"
                                        >Reduced eye strain</span
                                    >
                                </li>
                            </ul>
                        </AnimatedSection>
                    </div>

                    <!-- Destination Selection -->
                    <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                        <AnimatedSection
                            class="order-2 lg:order-1"
                            direction="left"
                        >
                            <Badge
                                variant="outline"
                                class="mb-4 px-3 py-1 transition-colors hover:bg-muted"
                                >Smart Output</Badge
                            >
                            <h3 class="mb-4 text-3xl font-bold tracking-tight">
                                Flexible Output Options
                            </h3>
                            <p
                                class="mb-6 text-lg leading-relaxed text-muted-foreground"
                            >
                                Choose where your converted files go. Save to
                                custom directories or let Honeymelon organize
                                them for you.
                            </p>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3">
                                    <div
                                        class="mt-1 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <Check
                                            class="h-3 w-3 text-primary"
                                            :stroke-width="3"
                                        />
                                    </div>
                                    <span class="text-base"
                                        >Custom output directories</span
                                    >
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="mt-1 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <Check
                                            class="h-3 w-3 text-primary"
                                            :stroke-width="3"
                                        />
                                    </div>
                                    <span class="text-base"
                                        >Automatic file organization</span
                                    >
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="mt-1 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <Check
                                            class="h-3 w-3 text-primary"
                                            :stroke-width="3"
                                        />
                                    </div>
                                    <span class="text-base"
                                        >Original file preservation</span
                                    >
                                </li>
                            </ul>
                        </AnimatedSection>
                        <div class="order-1 lg:order-2">
                            <ScreenshotFrame
                                src="/images/screens/Destination-Directory-Chosen-Light-Mode.png"
                                alt="Destination Selection"
                                direction="right"
                                :delay="100"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Pipeline -->
        <section class="border-b border-border/40 bg-muted/30 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    badge="Process"
                    title="How It Works"
                    description="Three-stage intelligent conversion pipeline"
                />

                <div class="grid gap-8 md:grid-cols-3">
                    <AnimatedSection
                        v-for="(stage, index) in pipeline"
                        :key="stage.number"
                        :delay="index * 100"
                        class="relative"
                    >
                        <Card
                            class="group h-full border-2 transition-all duration-300 hover:-translate-y-1 hover:border-primary/30"
                        >
                            <CardHeader class="pb-4">
                                <div
                                    class="mb-6 flex h-20 w-20 items-center justify-center rounded-2xl bg-primary text-3xl font-bold text-primary-foreground transition-transform duration-300 group-hover:scale-110"
                                >
                                    {{ stage.number }}
                                </div>
                                <CardTitle class="text-2xl">{{
                                    stage.title
                                }}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <CardDescription
                                    class="text-base leading-relaxed"
                                >
                                    {{ stage.description }}
                                </CardDescription>
                            </CardContent>
                        </Card>
                    </AnimatedSection>
                </div>
            </div>
        </section>

        <!-- Supported Formats -->
        <section class="border-b border-border/40 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    badge="Compatibility"
                    title="Supported Formats"
                    description="Comprehensive format support powered by FFmpeg"
                />

                <div class="grid gap-8 md:grid-cols-3">
                    <AnimatedSection :delay="0">
                        <Card
                            class="group h-full transition-all duration-300 hover:-translate-y-1 hover:border-primary/30"
                        >
                            <CardHeader class="pb-4">
                                <div
                                    class="mb-4 flex h-16 w-16 items-center justify-center rounded-xl bg-primary/10 transition-all duration-300 group-hover:scale-110 group-hover:bg-primary/15"
                                >
                                    <Video
                                        class="h-8 w-8 text-primary"
                                        :stroke-width="1.5"
                                    />
                                </div>
                                <CardTitle class="text-2xl">Video</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="flex flex-wrap gap-2">
                                    <Badge
                                        v-for="format in formats.video"
                                        :key="format"
                                        variant="secondary"
                                        class="px-3 py-1 transition-colors hover:bg-secondary/80"
                                        >{{ format }}</Badge
                                    >
                                </div>
                            </CardContent>
                        </Card>
                    </AnimatedSection>

                    <AnimatedSection :delay="75">
                        <Card
                            class="group h-full transition-all duration-300 hover:-translate-y-1 hover:border-primary/30"
                        >
                            <CardHeader class="pb-4">
                                <div
                                    class="mb-4 flex h-16 w-16 items-center justify-center rounded-xl bg-primary/10 transition-all duration-300 group-hover:scale-110 group-hover:bg-primary/15"
                                >
                                    <Music
                                        class="h-8 w-8 text-primary"
                                        :stroke-width="1.5"
                                    />
                                </div>
                                <CardTitle class="text-2xl">Audio</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="flex flex-wrap gap-2">
                                    <Badge
                                        v-for="format in formats.audio"
                                        :key="format"
                                        variant="secondary"
                                        class="px-3 py-1 transition-colors hover:bg-secondary/80"
                                        >{{ format }}</Badge
                                    >
                                </div>
                            </CardContent>
                        </Card>
                    </AnimatedSection>

                    <AnimatedSection :delay="150">
                        <Card
                            class="group h-full transition-all duration-300 hover:-translate-y-1 hover:border-primary/30"
                        >
                            <CardHeader class="pb-4">
                                <div
                                    class="mb-4 flex h-16 w-16 items-center justify-center rounded-xl bg-primary/10 transition-all duration-300 group-hover:scale-110 group-hover:bg-primary/15"
                                >
                                    <Image
                                        class="h-8 w-8 text-primary"
                                        :stroke-width="1.5"
                                    />
                                </div>
                                <CardTitle class="text-2xl">Images</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="flex flex-wrap gap-2">
                                    <Badge
                                        v-for="format in formats.image"
                                        :key="format"
                                        variant="secondary"
                                        class="px-3 py-1 transition-colors hover:bg-secondary/80"
                                        >{{ format }}</Badge
                                    >
                                </div>
                            </CardContent>
                        </Card>
                    </AnimatedSection>
                </div>
            </div>
        </section>

        <!-- Key Features Checklist -->
        <section class="border-b border-border/40 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-16 lg:grid-cols-2 lg:items-center">
                    <AnimatedSection direction="left">
                        <Badge
                            variant="outline"
                            class="mb-6 px-3 py-1 transition-colors hover:bg-muted"
                            >Technical Excellence</Badge
                        >
                        <h2
                            class="mb-6 text-4xl font-bold tracking-tight sm:text-5xl"
                        >
                            Powerful Features
                        </h2>
                        <p
                            class="text-lg leading-relaxed text-muted-foreground"
                        >
                            Built for professionals who need speed, quality, and
                            privacy. Optimized for Apple Silicon with
                            intelligent conversion strategies.
                        </p>
                    </AnimatedSection>

                    <AnimatedSection :delay="150" direction="right">
                        <Card
                            class="transition-all duration-300 hover:-translate-y-1 hover:border-primary/30"
                        >
                            <CardContent class="pt-8">
                                <ul class="space-y-5">
                                    <li
                                        v-for="feature in keyFeatures"
                                        :key="feature"
                                        class="flex items-start gap-4 transition-colors hover:text-foreground"
                                    >
                                        <Badge
                                            class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full p-0 transition-transform hover:scale-110"
                                        >
                                            <Check
                                                class="h-3.5 w-3.5"
                                                :stroke-width="3"
                                            />
                                        </Badge>
                                        <span class="text-base leading-relaxed">
                                            {{ feature }}
                                        </span>
                                    </li>
                                </ul>
                            </CardContent>
                        </Card>
                    </AnimatedSection>
                </div>
            </div>
        </section>

        <!-- CTA Section with gradient -->
        <section class="py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <AnimatedSection>
                    <Card
                        class="relative overflow-hidden border-2 border-primary/20 bg-gradient-to-br from-primary/10 via-primary/5 to-background transition-all duration-500 hover:border-primary/30"
                    >
                        <div
                            class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,theme(colors.primary.DEFAULT/10%),transparent)]"
                        />
                        <CardContent
                            class="flex flex-col items-center gap-6 py-14 text-center"
                        >
                            <Badge
                                variant="secondary"
                                class="px-4 py-1.5 transition-colors hover:bg-secondary/80"
                                >Get Started Today</Badge
                            >
                            <h2
                                class="max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl"
                            >
                                Ready to Transform Your Media Workflow?
                            </h2>
                            <p
                                class="max-w-2xl text-lg leading-relaxed text-muted-foreground"
                            >
                                Download Honeymelon today and experience
                                intelligent, privacy-first media conversion on
                                your Mac.
                            </p>
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <Button
                                    as-child
                                    size="lg"
                                    class="group text-base transition-all duration-300 hover:scale-[1.02]"
                                >
                                    <a href="/download">
                                        Download for macOS
                                        <ArrowRight
                                            class="ml-2 h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                                        />
                                    </a>
                                </Button>
                                <Button
                                    as-child
                                    variant="outline"
                                    size="lg"
                                    class="text-base transition-all duration-300 hover:bg-muted/80"
                                >
                                    <a
                                        href="https://docs.honeymelon.app"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        <BookOpen class="mr-2 h-4 w-4" />
                                        View Documentation
                                    </a>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </AnimatedSection>
            </div>
        </section>
    </MarketingLayout>
</template>
