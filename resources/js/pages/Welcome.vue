<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import PageHero from '@/components/marketing/PageHero.vue';
import SectionHeader from '@/components/marketing/SectionHeader.vue';
import Alert from '@/components/ui/alert/Alert.vue';
import AlertDescription from '@/components/ui/alert/AlertDescription.vue';
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
import systemRequirementsData from '@/content/systemRequirements.json';

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
const systemRequirements = systemRequirementsData;
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
                        class="text-base shadow-lg shadow-primary/20"
                    >
                        <a
                            href="https://github.com"
                            target="_blank"
                            rel="noopener"
                        >
                            Download for macOS
                            <ArrowRight class="ml-2 h-4 w-4" />
                        </a>
                    </Button>
                    <Button
                        as-child
                        variant="outline"
                        size="lg"
                        class="text-base"
                    >
                        <a
                            href="https://github.com"
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
                    class="mt-20 flex flex-wrap items-center justify-center gap-3"
                >
                    <span class="text-sm font-medium text-muted-foreground"
                        >Requirements:</span
                    >
                    <Badge variant="secondary" class="px-3 py-1"
                        >macOS 13+</Badge
                    >
                    <Badge variant="secondary" class="px-3 py-1"
                        >Apple Silicon</Badge
                    >
                    <Badge variant="secondary" class="px-3 py-1"
                        >Privacy-First</Badge
                    >
                </div>
            </template>
        </PageHero>

        <!-- Features Section with hover effects -->
        <section id="features" class="border-b border-border/40 py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    badge="Features"
                    title="Everything You Need"
                    description="Built with modern technologies and best practices for production-ready deployments."
                />

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="feature in features"
                        :key="feature.title"
                        class="transition-shadow hover:shadow-lg"
                    >
                        <CardHeader class="pb-4">
                            <component
                                :is="feature.icon"
                                class="mb-4 h-12 w-12 text-primary"
                                :stroke-width="1.5"
                            />
                            <CardTitle class="text-xl">{{
                                feature.title
                            }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <CardDescription
                                class="text-base leading-relaxed"
                                >{{ feature.description }}</CardDescription
                            >
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- How It Works Pipeline -->
        <section class="border-b border-border/40 bg-muted/30 py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    badge="Process"
                    title="How It Works"
                    description="Three-stage intelligent conversion pipeline"
                />

                <div class="grid gap-8 md:grid-cols-3">
                    <div
                        v-for="stage in pipeline"
                        :key="stage.number"
                        class="relative"
                    >
                        <Card
                            class="h-full border-2 transition-shadow hover:shadow-lg"
                        >
                            <CardHeader class="pb-4">
                                <div
                                    class="mb-6 flex h-20 w-20 items-center justify-center rounded-2xl bg-primary text-3xl font-bold text-primary-foreground shadow-lg shadow-primary/20"
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
                    </div>
                </div>
            </div>
        </section>

        <!-- Supported Formats -->
        <section class="border-b border-border/40 py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    badge="Compatibility"
                    title="Supported Formats"
                    description="Comprehensive format support powered by FFmpeg"
                />

                <div class="grid gap-8 md:grid-cols-3">
                    <Card class="transition-shadow hover:shadow-lg">
                        <CardHeader class="pb-4">
                            <Video
                                class="mb-4 h-14 w-14 text-primary"
                                :stroke-width="1.5"
                            />
                            <CardTitle class="text-2xl">Video</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                <Badge
                                    v-for="format in formats.video"
                                    :key="format"
                                    variant="secondary"
                                    class="px-3 py-1"
                                    >{{ format }}</Badge
                                >
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="transition-shadow hover:shadow-lg">
                        <CardHeader class="pb-4">
                            <Music
                                class="mb-4 h-14 w-14 text-primary"
                                :stroke-width="1.5"
                            />
                            <CardTitle class="text-2xl">Audio</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                <Badge
                                    v-for="format in formats.audio"
                                    :key="format"
                                    variant="secondary"
                                    class="px-3 py-1"
                                    >{{ format }}</Badge
                                >
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="transition-shadow hover:shadow-lg">
                        <CardHeader class="pb-4">
                            <Image
                                class="mb-4 h-14 w-14 text-primary"
                                :stroke-width="1.5"
                            />
                            <CardTitle class="text-2xl">Images</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                <Badge
                                    v-for="format in formats.image"
                                    :key="format"
                                    variant="secondary"
                                    class="px-3 py-1"
                                    >{{ format }}</Badge
                                >
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- System Requirements -->
        <section class="border-b border-border/40 bg-muted/30 py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    badge="Requirements"
                    title="System Requirements"
                />

                <div class="grid gap-8 lg:grid-cols-2">
                    <Card class="transition-shadow hover:shadow-lg">
                        <CardHeader class="pb-4">
                            <CardTitle class="text-2xl">Minimum</CardTitle>
                            <CardDescription class="text-base"
                                >Basic requirements to run
                                Honeymelon</CardDescription
                            >
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div
                                    v-for="req in systemRequirements.minimum"
                                    :key="req.label"
                                    class="flex justify-between border-b border-border/50 pb-4"
                                >
                                    <span class="font-semibold">{{
                                        req.label
                                    }}</span>
                                    <span class="text-muted-foreground">{{
                                        req.value
                                    }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="transition-shadow hover:shadow-lg">
                        <CardHeader class="pb-4">
                            <CardTitle class="text-2xl">Recommended</CardTitle>
                            <CardDescription class="text-base"
                                >Optimal performance for heavy
                                workloads</CardDescription
                            >
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div
                                    v-for="req in systemRequirements.recommended"
                                    :key="req.label"
                                    class="flex justify-between border-b border-border/50 pb-4"
                                >
                                    <span class="font-semibold">{{
                                        req.label
                                    }}</span>
                                    <span class="text-muted-foreground">{{
                                        req.value
                                    }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Alert class="mt-10">
                    <AlertDescription class="text-base">
                        <strong>Important:</strong> Honeymelon is compiled
                        exclusively for Apple Silicon (ARM64 architecture).
                        Intel-based Macs are not supported.
                    </AlertDescription>
                </Alert>
            </div>
        </section>

        <!-- Key Features Checklist -->
        <section class="border-b border-border/40 py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-16 lg:grid-cols-2 lg:items-center">
                    <div>
                        <Badge variant="outline" class="mb-6 px-3 py-1"
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
                    </div>

                    <Card class="transition-shadow hover:shadow-lg">
                        <CardContent class="pt-8">
                            <ul class="space-y-5">
                                <li
                                    v-for="feature in keyFeatures"
                                    :key="feature"
                                    class="flex items-start gap-4"
                                >
                                    <Badge
                                        class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full p-0"
                                    >
                                        <Check
                                            class="h-3.5 w-3.5"
                                            :stroke-width="3"
                                        />
                                    </Badge>
                                    <span class="text-base leading-relaxed">{{
                                        feature
                                    }}</span>
                                </li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- CTA Section with gradient -->
        <section class="py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <Card
                    class="relative overflow-hidden border-2 border-primary/20 bg-gradient-to-br from-primary/10 via-primary/5 to-background shadow-xl"
                >
                    <div
                        class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,theme(colors.primary.DEFAULT/10%),transparent)]"
                    />
                    <CardContent
                        class="flex flex-col items-center gap-8 py-20 text-center"
                    >
                        <Badge variant="secondary" class="px-4 py-1.5"
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
                            intelligent, privacy-first media conversion on your
                            Mac.
                        </p>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <Button
                                as-child
                                size="lg"
                                class="text-base shadow-lg shadow-primary/20"
                            >
                                <a
                                    href="https://github.com"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    Download for macOS
                                    <ArrowRight class="ml-2 h-4 w-4" />
                                </a>
                            </Button>
                            <Button
                                as-child
                                variant="outline"
                                size="lg"
                                class="text-base"
                            >
                                <a
                                    href="https://github.com"
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
            </div>
        </section>
    </MarketingLayout>
</template>
