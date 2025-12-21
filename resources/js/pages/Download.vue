<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import AnimatedSection from '@/components/marketing/AnimatedSection.vue';
import PageHero from '@/components/marketing/PageHero.vue';
import SectionHeader from '@/components/marketing/SectionHeader.vue';
import Accordion from '@/components/ui/accordion/Accordion.vue';
import AccordionContent from '@/components/ui/accordion/AccordionContent.vue';
import AccordionItem from '@/components/ui/accordion/AccordionItem.vue';
import AccordionTrigger from '@/components/ui/accordion/AccordionTrigger.vue';
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
    BookOpen,
    Download as DownloadIcon,
    FileText,
    Key,
} from 'lucide-vue-next';

import faqsData from '@/content/faqs.json';
import { Artifact } from '@/types/api';

const faqs = faqsData;

const props = defineProps<{
    artifact: Artifact | null;
}>();

function triggerDownload() {
    if (props.artifact?.url) {
        window.location.href = props.artifact.url;
    }
}

function formatBytes(bytes: number): string {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <Head title="Download Honeymelon - Smart Media Converter for macOS" />

    <MarketingLayout>
        <PageHero
            badge="Download for macOS"
            :badge-icon="AppLogoIcon"
            title="Get Started with "
            highlighted-text="Honeymelon"
            description="Download the latest version of Honeymelon and start converting your media with intelligent, privacy-first technology."
        />

        <!-- License Required Notice -->
        <section class="border-b border-border/40 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <AnimatedSection>
                    <Alert
                        class="border-2 border-primary/30 bg-primary/5 transition-all duration-300 hover:border-primary/40"
                    >
                        <Key class="h-5 w-5 text-primary" />
                        <AlertDescription class="ml-2 text-base">
                            <strong class="font-semibold"
                                >License Required:</strong
                            >
                            Honeymelon is free to download, but requires a valid
                            license key to use. After downloading, you'll need
                            to
                            <a
                                href="/pricing"
                                class="font-semibold text-primary underline underline-offset-4 transition-colors hover:text-primary/80 hover:no-underline"
                            >
                                purchase a license
                            </a>
                            to activate the app. One-time payment of $29, no
                            subscription required.
                        </AlertDescription>
                    </Alert>
                </AnimatedSection>
            </div>
        </section>

        <!-- Download Cards -->
        <section class="border-b border-border/40 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-10 lg:grid-cols-2">
                    <!-- Latest Release -->
                    <AnimatedSection direction="left">
                        <Card
                            class="h-full border-2 border-primary/20 transition-all duration-300 hover:-translate-y-1 hover:border-primary/30"
                        >
                            <CardHeader class="pb-4">
                                <div class="mb-4 flex items-center gap-2">
                                    <Badge class="px-3 py-1"
                                        >Latest Release</Badge
                                    >
                                    <Badge
                                        v-if="artifact?.release"
                                        variant="outline"
                                        class="px-3 py-1"
                                    >
                                        v{{ artifact.release.version }}
                                    </Badge>
                                </div>
                                <CardTitle class="text-2xl">
                                    Honeymelon for macOS
                                </CardTitle>
                                <CardDescription class="text-base">
                                    Apple Silicon (M1, M2, M3, M4)
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <div v-if="artifact" class="space-y-3">
                                    <div
                                        class="flex items-center justify-between border-b border-border/50 pb-3 text-sm transition-colors hover:border-primary/30"
                                    >
                                        <span class="text-muted-foreground">
                                            Released
                                        </span>
                                        <span class="font-medium">
                                            {{
                                                artifact.release?.published_at
                                                    ? formatDate(
                                                          artifact.release
                                                              .published_at,
                                                      )
                                                    : 'N/A'
                                            }}
                                        </span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between border-b border-border/50 pb-3 text-sm transition-colors hover:border-primary/30"
                                    >
                                        <span class="text-muted-foreground">
                                            Size
                                        </span>
                                        <span class="font-medium">
                                            {{ formatBytes(artifact.size) }}
                                        </span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between border-b border-border/50 pb-3 text-sm transition-colors hover:border-primary/30"
                                    >
                                        <span class="text-muted-foreground">
                                            Requires
                                        </span>
                                        <span class="font-medium"
                                            >macOS 13+</span
                                        >
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <Button
                                        :disabled="!artifact?.url"
                                        size="lg"
                                        class="group w-full cursor-pointer text-base transition-all duration-300 hover:scale-[1.01]"
                                        @click="triggerDownload"
                                    >
                                        <DownloadIcon
                                            class="mr-2 h-4 w-4 transition-transform duration-300 group-hover:-translate-y-0.5"
                                        />
                                        Download for Apple Silicon
                                    </Button>
                                    <Button
                                        v-if="artifact?.release?.notes"
                                        variant="outline"
                                        size="lg"
                                        class="w-full text-base transition-all duration-300 hover:bg-muted/80"
                                    >
                                        <FileText class="mr-2 h-4 w-4" />
                                        View Release Notes
                                    </Button>
                                </div>

                                <Alert class="border-primary/20 bg-primary/5">
                                    <AlertDescription
                                        class="text-sm leading-relaxed"
                                    >
                                        <strong>Note:</strong> Intel-based Macs
                                        are not supported. Honeymelon is built
                                        exclusively for Apple Silicon.
                                    </AlertDescription>
                                </Alert>
                            </CardContent>
                        </Card>
                    </AnimatedSection>

                    <!-- Installation Guide -->
                    <AnimatedSection :delay="100" direction="right">
                        <Card
                            class="h-full transition-all duration-300 hover:-translate-y-1 hover:border-primary/30"
                        >
                            <CardHeader class="pb-4">
                                <CardTitle class="text-2xl"
                                    >Installation Guide</CardTitle
                                >
                                <CardDescription class="text-base">
                                    Get up and running in minutes
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <ol class="space-y-6">
                                    <li class="group flex gap-4">
                                        <div
                                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-primary text-base font-bold text-primary-foreground transition-transform duration-300 group-hover:scale-110"
                                        >
                                            1
                                        </div>
                                        <div class="pt-1">
                                            <h3 class="mb-2 font-semibold">
                                                Download the App
                                            </h3>
                                            <p
                                                class="text-sm leading-relaxed text-muted-foreground"
                                            >
                                                Click the download button above
                                                to get the latest version of
                                                Honeymelon.
                                            </p>
                                        </div>
                                    </li>
                                    <li class="group flex gap-4">
                                        <div
                                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-primary text-base font-bold text-primary-foreground transition-transform duration-300 group-hover:scale-110"
                                        >
                                            2
                                        </div>
                                        <div class="pt-1">
                                            <h3 class="mb-2 font-semibold">
                                                Open the DMG File
                                            </h3>
                                            <p
                                                class="text-sm leading-relaxed text-muted-foreground"
                                            >
                                                Double-click the downloaded file
                                                to mount the disk image.
                                            </p>
                                        </div>
                                    </li>
                                    <li class="group flex gap-4">
                                        <div
                                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-primary text-base font-bold text-primary-foreground transition-transform duration-300 group-hover:scale-110"
                                        >
                                            3
                                        </div>
                                        <div class="pt-1">
                                            <h3 class="mb-2 font-semibold">
                                                Drag to Applications
                                            </h3>
                                            <p
                                                class="text-sm leading-relaxed text-muted-foreground"
                                            >
                                                Drag the Honeymelon app to your
                                                Applications folder.
                                            </p>
                                        </div>
                                    </li>
                                    <li class="group flex gap-4">
                                        <div
                                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-primary text-base font-bold text-primary-foreground transition-transform duration-300 group-hover:scale-110"
                                        >
                                            4
                                        </div>
                                        <div class="pt-1">
                                            <h3 class="mb-2 font-semibold">
                                                Activate with License Key
                                            </h3>
                                            <p
                                                class="text-sm leading-relaxed text-muted-foreground"
                                            >
                                                Open Honeymelon and enter your
                                                license key when prompted. Don't
                                                have one?
                                                <a
                                                    href="/pricing"
                                                    class="font-medium text-primary underline underline-offset-2 transition-colors hover:text-primary/80"
                                                >
                                                    Purchase a license
                                                </a>
                                                to activate.
                                            </p>
                                        </div>
                                    </li>
                                </ol>
                            </CardContent>
                        </Card>
                    </AnimatedSection>
                </div>
            </div>
        </section>

        <!-- System Requirements -->
        <section class="border-b border-border/40 bg-muted/30 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    badge="Requirements"
                    title="System Requirements"
                    description="Make sure your Mac meets these requirements before downloading"
                />

                <div class="grid gap-8 md:grid-cols-2">
                    <AnimatedSection direction="left">
                        <Card
                            class="h-full transition-all duration-300 hover:-translate-y-1 hover:border-primary/30"
                        >
                            <CardHeader class="pb-4">
                                <CardTitle class="text-xl"
                                    >Minimum Requirements</CardTitle
                                >
                                <CardDescription class="text-base"
                                    >Basic requirements to run
                                    Honeymelon</CardDescription
                                >
                            </CardHeader>
                            <CardContent>
                                <dl class="space-y-4">
                                    <div
                                        class="flex justify-between border-b border-border/50 pb-3 transition-colors hover:border-primary/30"
                                    >
                                        <dt class="font-medium">
                                            Operating System
                                        </dt>
                                        <dd class="text-muted-foreground">
                                            macOS 13 (Ventura)
                                        </dd>
                                    </div>
                                    <div
                                        class="flex justify-between border-b border-border/50 pb-3 transition-colors hover:border-primary/30"
                                    >
                                        <dt class="font-medium">Processor</dt>
                                        <dd class="text-muted-foreground">
                                            Apple Silicon (M1+)
                                        </dd>
                                    </div>
                                    <div
                                        class="flex justify-between border-b border-border/50 pb-3 transition-colors hover:border-primary/30"
                                    >
                                        <dt class="font-medium">Memory</dt>
                                        <dd class="text-muted-foreground">
                                            4 GB RAM
                                        </dd>
                                    </div>
                                    <div
                                        class="flex justify-between transition-colors hover:border-primary/30"
                                    >
                                        <dt class="font-medium">Storage</dt>
                                        <dd class="text-muted-foreground">
                                            50 MB + output space
                                        </dd>
                                    </div>
                                </dl>
                            </CardContent>
                        </Card>
                    </AnimatedSection>

                    <AnimatedSection :delay="100" direction="right">
                        <Card
                            class="h-full transition-all duration-300 hover:-translate-y-1 hover:border-primary/30"
                        >
                            <CardHeader class="pb-4">
                                <CardTitle class="text-xl"
                                    >Recommended</CardTitle
                                >
                                <CardDescription class="text-base"
                                    >For optimal performance</CardDescription
                                >
                            </CardHeader>
                            <CardContent>
                                <dl class="space-y-4">
                                    <div
                                        class="flex justify-between border-b border-border/50 pb-3 transition-colors hover:border-primary/30"
                                    >
                                        <dt class="font-medium">
                                            Operating System
                                        </dt>
                                        <dd class="text-muted-foreground">
                                            macOS 14 (Sonoma)+
                                        </dd>
                                    </div>
                                    <div
                                        class="flex justify-between border-b border-border/50 pb-3 transition-colors hover:border-primary/30"
                                    >
                                        <dt class="font-medium">Processor</dt>
                                        <dd class="text-muted-foreground">
                                            M2 Pro/Max or M3+
                                        </dd>
                                    </div>
                                    <div
                                        class="flex justify-between border-b border-border/50 pb-3 transition-colors hover:border-primary/30"
                                    >
                                        <dt class="font-medium">Memory</dt>
                                        <dd class="text-muted-foreground">
                                            16 GB RAM
                                        </dd>
                                    </div>
                                    <div
                                        class="flex justify-between transition-colors hover:border-primary/30"
                                    >
                                        <dt class="font-medium">Storage</dt>
                                        <dd class="text-muted-foreground">
                                            ≥ 500 MB free
                                        </dd>
                                    </div>
                                </dl>
                            </CardContent>
                        </Card>
                    </AnimatedSection>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="border-b border-border/40 py-16">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    badge="FAQ"
                    title="Frequently Asked Questions"
                    description="Everything you need to know about Honeymelon"
                />

                <AnimatedSection>
                    <Card
                        class="transition-all duration-300 hover:-translate-y-1 hover:border-primary/30"
                    >
                        <CardContent class="p-6">
                            <Accordion type="single" collapsible class="w-full">
                                <AccordionItem
                                    v-for="(faq, index) in faqs"
                                    :key="index"
                                    :value="`item-${index}`"
                                    class="transition-colors hover:bg-muted/30"
                                >
                                    <AccordionTrigger
                                        class="text-left text-base font-semibold"
                                    >
                                        {{ faq.question }}
                                    </AccordionTrigger>
                                    <AccordionContent
                                        class="text-base leading-relaxed text-muted-foreground"
                                    >
                                        {{ faq.answer }}
                                    </AccordionContent>
                                </AccordionItem>
                            </Accordion>
                        </CardContent>
                    </Card>
                </AnimatedSection>
            </div>
        </section>

        <!-- CTA Section -->
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
                            <h2
                                class="max-w-2xl text-3xl font-bold tracking-tight sm:text-4xl"
                            >
                                Ready to Start Converting?
                            </h2>
                            <p
                                class="max-w-xl text-lg leading-relaxed text-muted-foreground"
                            >
                                Join thousands of Mac users who trust Honeymelon
                                for their media conversion needs.
                            </p>
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <Button
                                    size="lg"
                                    class="group cursor-pointer text-base transition-all duration-300 hover:scale-[1.02]"
                                    @click="triggerDownload"
                                >
                                    <DownloadIcon
                                        class="mr-2 h-4 w-4 transition-transform duration-300 group-hover:-translate-y-0.5"
                                    />
                                    Download Now
                                </Button>
                                <Button
                                    as-child
                                    variant="outline"
                                    size="lg"
                                    class="cursor-pointer text-base transition-all duration-300 hover:bg-muted/80"
                                >
                                    <a
                                        href="https://docs.honeymelon.app"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        <BookOpen class="mr-2 h-4 w-4" />
                                        Read Documentation
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
