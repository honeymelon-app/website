<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Download as DownloadIcon, FileText, BookOpen } from 'lucide-vue-next';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Accordion from '@/components/ui/accordion/Accordion.vue';
import AccordionContent from '@/components/ui/accordion/AccordionContent.vue';
import AccordionItem from '@/components/ui/accordion/AccordionItem.vue';
import AccordionTrigger from '@/components/ui/accordion/AccordionTrigger.vue';
import Alert from '@/components/ui/alert/Alert.vue';
import AlertDescription from '@/components/ui/alert/AlertDescription.vue';

const faqs = [
    {
        question: 'What are the system requirements?',
        answer: 'Honeymelon requires macOS 13 (Ventura) or later and an Apple Silicon chip (M1 or newer). Intel-based Macs are not supported. For optimal performance, we recommend macOS 14 (Sonoma), 16GB RAM, and at least 500MB of free disk space.',
    },
    {
        question: 'Is Honeymelon free?',
        answer: 'Honeymelon offers a free trial with limited conversions. For unlimited conversions and advanced features, a one-time license purchase is required. Visit our pricing page for more details.',
    },
    {
        question: 'Does Honeymelon work offline?',
        answer: 'Yes! Honeymelon is designed for offline use. All media processing happens locally on your Mac with no cloud uploads or internet connection required. Your privacy is our priority.',
    },
    {
        question: 'What file formats are supported?',
        answer: 'Honeymelon supports a wide range of formats including MP4, MOV, MKV, WebM, and GIF for video; M4A, MP3, FLAC, WAV, and Opus for audio; and PNG, JPEG, and WebP for images. Powered by FFmpeg, we offer comprehensive codec compatibility.',
    },
    {
        question: 'How does remux-first intelligence work?',
        answer: 'Our smart conversion engine analyzes your media files and automatically determines the best approach. When possible, it copies streams directly without re-encoding (remuxing), which preserves quality and maximizes speed. Only when necessary does it transcode, ensuring optimal results every time.',
    },
    {
        question: 'Can I batch process multiple files?',
        answer: 'Absolutely! Honeymelon supports batch processing with concurrent job management. Add multiple files, configure your conversion settings, and let Honeymelon handle them all with real-time progress tracking and accurate ETAs.',
    },
    {
        question: 'Does Honeymelon use hardware acceleration?',
        answer: 'Yes! Honeymelon is optimized for Apple Silicon with native ARM64 support and leverages hardware acceleration on M-series chips for maximum performance.',
    },
    {
        question: 'How do I get support?',
        answer: 'Visit our documentation for guides and tutorials. For technical support, check our GitHub repository or contact our support team through the help center.',
    },
];
</script>

<template>
    <Head title="Download Honeymelon - Smart Media Converter for macOS" />

    <div class="min-h-screen bg-gradient-to-b from-background to-muted/20">
        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-border/40 bg-background/80 backdrop-blur-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <a href="/" class="flex items-center gap-3">
                        <AppLogoIcon class="h-10 w-10 transition-transform hover:scale-110" />
                        <span class="text-xl font-bold">Honeymelon</span>
                    </a>
                    <nav class="flex items-center gap-3">
                        <Button as-child variant="ghost">
                            <a href="/#features">Features</a>
                        </Button>
                        <Button as-child variant="ghost">
                            <a href="/#pricing">Pricing</a>
                        </Button>
                        <Button as-child>
                            <a href="/download">Download</a>
                        </Button>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Download Hero Section -->
        <section class="relative overflow-hidden border-b border-border/40 py-32 sm:py-40">
            <div class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,theme(colors.primary.DEFAULT/20%),transparent)]" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center text-center">
                    <Badge variant="outline" class="mb-8 border-primary/50 px-4 py-1.5 text-sm">
                        <AppLogoIcon class="mr-2 h-3.5 w-3.5" />
                        Download for macOS
                    </Badge>
                    <h1 class="mb-8 max-w-3xl bg-gradient-to-br from-foreground to-foreground/70 bg-clip-text text-4xl font-bold leading-tight tracking-tight text-transparent sm:text-5xl md:text-6xl">
                        Get Started with
                        <span class="bg-gradient-to-r from-primary via-primary/80 to-primary bg-clip-text">Honeymelon</span>
                    </h1>
                    <p class="mb-10 max-w-2xl text-lg leading-relaxed text-muted-foreground">
                        Download the latest version of Honeymelon and start converting your media with intelligent, privacy-first technology.
                    </p>
                </div>
            </div>
        </section>

        <!-- Download Cards -->
        <section class="border-b border-border/40 py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-10 lg:grid-cols-2">
                    <!-- Latest Release -->
                    <Card class="border-2 border-primary/20 shadow-lg transition-shadow hover:shadow-xl">
                        <CardHeader class="pb-4">
                            <div class="mb-4 flex items-center gap-2">
                                <Badge class="px-3 py-1">Latest Release</Badge>
                                <Badge variant="outline" class="px-3 py-1">v1.0.0</Badge>
                            </div>
                            <CardTitle class="text-2xl">Honeymelon for macOS</CardTitle>
                            <CardDescription class="text-base">
                                Apple Silicon (M1, M2, M3, M4)
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between border-b border-border/50 pb-3 text-sm">
                                    <span class="text-muted-foreground">Released</span>
                                    <span class="font-medium">January 15, 2025</span>
                                </div>
                                <div class="flex items-center justify-between border-b border-border/50 pb-3 text-sm">
                                    <span class="text-muted-foreground">Size</span>
                                    <span class="font-medium">42.3 MB</span>
                                </div>
                                <div class="flex items-center justify-between border-b border-border/50 pb-3 text-sm">
                                    <span class="text-muted-foreground">Requires</span>
                                    <span class="font-medium">macOS 13+</span>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <Button size="lg" class="w-full text-base shadow-lg shadow-primary/20">
                                    <DownloadIcon class="mr-2 h-4 w-4" />
                                    Download for Apple Silicon
                                </Button>
                                <Button variant="outline" size="lg" class="w-full text-base">
                                    <FileText class="mr-2 h-4 w-4" />
                                    View Release Notes
                                </Button>
                            </div>

                            <Alert>
                                <AlertDescription class="text-sm leading-relaxed">
                                    <strong>Note:</strong> Intel-based Macs are not supported. Honeymelon is built exclusively for Apple Silicon.
                                </AlertDescription>
                            </Alert>
                        </CardContent>
                    </Card>

                    <!-- Installation Guide -->
                    <Card class="transition-shadow hover:shadow-lg">
                        <CardHeader class="pb-4">
                            <CardTitle class="text-2xl">Installation Guide</CardTitle>
                            <CardDescription class="text-base">
                                Get up and running in minutes
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ol class="space-y-6">
                                <li class="flex gap-4">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-primary text-base font-bold text-primary-foreground shadow-lg shadow-primary/20">
                                        1
                                    </div>
                                    <div class="pt-1">
                                        <h3 class="mb-2 font-semibold">Download the App</h3>
                                        <p class="text-sm leading-relaxed text-muted-foreground">
                                            Click the download button above to get the latest version of Honeymelon.
                                        </p>
                                    </div>
                                </li>
                                <li class="flex gap-4">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-primary text-base font-bold text-primary-foreground shadow-lg shadow-primary/20">
                                        2
                                    </div>
                                    <div class="pt-1">
                                        <h3 class="mb-2 font-semibold">Open the DMG File</h3>
                                        <p class="text-sm leading-relaxed text-muted-foreground">
                                            Double-click the downloaded file to mount the disk image.
                                        </p>
                                    </div>
                                </li>
                                <li class="flex gap-4">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-primary text-base font-bold text-primary-foreground shadow-lg shadow-primary/20">
                                        3
                                    </div>
                                    <div class="pt-1">
                                        <h3 class="mb-2 font-semibold">Drag to Applications</h3>
                                        <p class="text-sm leading-relaxed text-muted-foreground">
                                            Drag the Honeymelon app to your Applications folder.
                                        </p>
                                    </div>
                                </li>
                                <li class="flex gap-4">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-primary text-base font-bold text-primary-foreground shadow-lg shadow-primary/20">
                                        4
                                    </div>
                                    <div class="pt-1">
                                        <h3 class="mb-2 font-semibold">Launch & Enjoy</h3>
                                        <p class="text-sm leading-relaxed text-muted-foreground">
                                            Open Honeymelon from your Applications folder and start converting!
                                        </p>
                                    </div>
                                </li>
                            </ol>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- System Requirements -->
        <section class="border-b border-border/40 bg-muted/30 py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-20 text-center">
                    <Badge variant="outline" class="mb-6 px-3 py-1">Requirements</Badge>
                    <h2 class="mb-6 text-3xl font-bold tracking-tight sm:text-4xl">
                        System Requirements
                    </h2>
                    <p class="mx-auto max-w-2xl text-lg leading-relaxed text-muted-foreground">
                        Make sure your Mac meets these requirements before downloading
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-2">
                    <Card class="transition-shadow hover:shadow-lg">
                        <CardHeader class="pb-4">
                            <CardTitle class="text-xl">Minimum Requirements</CardTitle>
                            <CardDescription class="text-base">Basic requirements to run Honeymelon</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <dl class="space-y-4">
                                <div class="flex justify-between border-b border-border/50 pb-3">
                                    <dt class="font-medium">Operating System</dt>
                                    <dd class="text-muted-foreground">macOS 13 (Ventura)</dd>
                                </div>
                                <div class="flex justify-between border-b border-border/50 pb-3">
                                    <dt class="font-medium">Processor</dt>
                                    <dd class="text-muted-foreground">Apple Silicon (M1+)</dd>
                                </div>
                                <div class="flex justify-between border-b border-border/50 pb-3">
                                    <dt class="font-medium">Memory</dt>
                                    <dd class="text-muted-foreground">4 GB RAM</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-medium">Storage</dt>
                                    <dd class="text-muted-foreground">50 MB + output space</dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>

                    <Card class="transition-shadow hover:shadow-lg">
                        <CardHeader class="pb-4">
                            <CardTitle class="text-xl">Recommended</CardTitle>
                            <CardDescription class="text-base">For optimal performance</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <dl class="space-y-4">
                                <div class="flex justify-between border-b border-border/50 pb-3">
                                    <dt class="font-medium">Operating System</dt>
                                    <dd class="text-muted-foreground">macOS 14 (Sonoma)+</dd>
                                </div>
                                <div class="flex justify-between border-b border-border/50 pb-3">
                                    <dt class="font-medium">Processor</dt>
                                    <dd class="text-muted-foreground">M2 Pro/Max or M3+</dd>
                                </div>
                                <div class="flex justify-between border-b border-border/50 pb-3">
                                    <dt class="font-medium">Memory</dt>
                                    <dd class="text-muted-foreground">16 GB RAM</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-medium">Storage</dt>
                                    <dd class="text-muted-foreground">≥ 500 MB free</dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="border-b border-border/40 py-28">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-20 text-center">
                    <Badge variant="outline" class="mb-6 px-3 py-1">FAQ</Badge>
                    <h2 class="mb-6 text-3xl font-bold tracking-tight sm:text-4xl">
                        Frequently Asked Questions
                    </h2>
                    <p class="mx-auto max-w-2xl text-lg leading-relaxed text-muted-foreground">
                        Everything you need to know about Honeymelon
                    </p>
                </div>

                <Card class="transition-shadow hover:shadow-lg">
                    <CardContent class="p-6">
                        <Accordion type="single" collapsible class="w-full">
                            <AccordionItem v-for="(faq, index) in faqs" :key="index" :value="`item-${index}`">
                                <AccordionTrigger class="text-left text-base font-semibold">
                                    {{ faq.question }}
                                </AccordionTrigger>
                                <AccordionContent class="text-base leading-relaxed text-muted-foreground">
                                    {{ faq.answer }}
                                </AccordionContent>
                            </AccordionItem>
                        </Accordion>
                    </CardContent>
                </Card>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <Card class="relative overflow-hidden border-2 border-primary/20 bg-gradient-to-br from-primary/10 via-primary/5 to-background shadow-xl">
                    <div class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,theme(colors.primary.DEFAULT/10%),transparent)]" />
                    <CardContent class="flex flex-col items-center gap-8 py-20 text-center">
                        <h2 class="max-w-2xl text-3xl font-bold tracking-tight sm:text-4xl">
                            Ready to Start Converting?
                        </h2>
                        <p class="max-w-xl text-lg leading-relaxed text-muted-foreground">
                            Join thousands of Mac users who trust Honeymelon for their media conversion needs.
                        </p>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <Button size="lg" class="text-base shadow-lg shadow-primary/20">
                                <DownloadIcon class="mr-2 h-4 w-4" />
                                Download Now
                            </Button>
                            <Button variant="outline" size="lg" class="text-base">
                                <BookOpen class="mr-2 h-4 w-4" />
                                Read Documentation
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-border/40 bg-muted/50 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-center gap-6 text-center">
                    <div class="flex items-center gap-3">
                        <AppLogoIcon class="h-7 w-7" />
                        <span class="text-lg font-semibold">Honeymelon</span>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        © 2025 Honeymelon. Privacy-first media conversion for Mac.
                    </p>
                    <div class="flex gap-4 text-sm">
                        <a href="/privacy" class="text-muted-foreground transition-colors hover:text-foreground">Privacy Policy</a>
                        <span class="text-muted-foreground">•</span>
                        <a href="/terms" class="text-muted-foreground transition-colors hover:text-foreground">Terms of Use</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
