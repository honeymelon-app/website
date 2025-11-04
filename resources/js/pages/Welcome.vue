<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Separator from '@/components/ui/separator/Separator.vue';
import Alert from '@/components/ui/alert/Alert.vue';
import AlertDescription from '@/components/ui/alert/AlertDescription.vue';

// No props needed for this page

const features = [
    {
        title: 'Remux-First Intelligence',
        description: 'Automatically detects when to copy streams vs. transcode, preserving quality while maximizing speed.',
        icon: '⚡',
    },
    {
        title: 'Privacy by Design',
        description: 'All processing happens locally on your Mac. No telemetry, no cloud, no internet required.',
        icon: '🔒',
    },
    {
        title: 'Professional Workflow',
        description: 'Batch processing, concurrent jobs, real-time progress tracking with accurate ETA calculations.',
        icon: '⚙️',
    },
    {
        title: 'Multi-Format Support',
        description: 'Convert video, audio, and images with dynamic presets for every container pair.',
        icon: '🎬',
    },
    {
        title: 'Apple Silicon Optimized',
        description: 'Native ARM64 binary with hardware acceleration support for maximum performance.',
        icon: '🚀',
    },
    {
        title: 'FFmpeg Powered',
        description: 'Industry-standard codec support with comprehensive format compatibility and LGPL compliance.',
        icon: '🛠️',
    },
];

const pipeline = [
    {
        number: '1',
        title: 'Probe',
        description: 'Analyzes media files to extract metadata, codecs, resolution, and color information.',
    },
    {
        number: '2',
        title: 'Plan',
        description: 'Intelligently chooses the best conversion strategy: copy, transcode, or hybrid approach.',
    },
    {
        number: '3',
        title: 'Execute',
        description: 'Converts with real-time progress tracking, concurrent job management, and accurate ETAs.',
    },
];

const formats = {
    video: ['MP4', 'MOV', 'MKV', 'WebM', 'GIF'],
    audio: ['M4A', 'MP3', 'FLAC', 'WAV', 'Opus'],
    image: ['PNG', 'JPEG', 'WebP'],
};

const systemRequirements = {
    minimum: [
        { label: 'OS', value: 'macOS 13 (Ventura)' },
        { label: 'Chip', value: 'Apple Silicon (M1+)' },
        { label: 'RAM', value: '4 GB' },
        { label: 'Disk', value: '50 MB + output space' },
    ],
    recommended: [
        { label: 'OS', value: 'macOS 14 (Sonoma) or later' },
        { label: 'Chip', value: 'M2 Pro/Max or M3+' },
        { label: 'RAM', value: '16 GB' },
        { label: 'Disk', value: '≥ 500 MB free' },
    ],
};

const keyFeatures = [
    'Remux-first intelligence - copies streams when possible',
    'Hardware acceleration support for M-series chips',
    'Batch processing with concurrent job management',
    'Real-time progress tracking with accurate ETAs',
    'No cloud uploads - all processing happens locally',
    'FFmpeg-powered with comprehensive codec support',
];
</script>

<template>
    <Head title="Honeymelon - Smart Media Converter for macOS" />

    <div class="min-h-screen bg-gradient-to-b from-background to-muted/20">
        <!-- Header with blur effect -->
        <header class="sticky top-0 z-50 border-b border-border/40 bg-background/80 backdrop-blur-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-3">
                        <AppLogoIcon class="h-10 w-10 transition-transform hover:scale-110" />
                        <span class="text-xl font-bold">Honeymelon</span>
                    </div>
                    <nav class="flex items-center gap-3">
                        <Button as-child variant="ghost">
                            <a href="#features">Features</a>
                        </Button>
                        <Button as-child variant="ghost">
                            <a href="#pricing">Pricing</a>
                        </Button>
                        <Button as-child>
                            <a href="/download">Download</a>
                        </Button>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Hero Section with gradient -->
        <section class="relative overflow-hidden border-b border-border/40 py-24 sm:py-32">
            <div class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,theme(colors.primary.DEFAULT/20%),transparent)]" />
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center text-center">
                    <Badge variant="outline" class="mb-6 animate-fade-in border-primary/50 text-base">
                        <span class="mr-2">🍈</span>
                        Native macOS Media Converter
                    </Badge>
                    <h1 class="mb-6 max-w-4xl bg-gradient-to-br from-foreground to-foreground/70 bg-clip-text text-5xl font-bold tracking-tight text-transparent sm:text-6xl md:text-7xl">
                        Smart Media Conversion<br />for
                        <span class="bg-gradient-to-r from-primary via-primary/80 to-primary bg-clip-text">Apple Silicon Macs</span>
                    </h1>
                    <p class="mb-10 max-w-2xl text-xl leading-relaxed text-muted-foreground sm:text-2xl">
                        Remux-first, privacy-minded media converter that intelligently preserves quality while maximizing speed. Built exclusively for macOS Apple Silicon.
                    </p>
                    <div class="flex flex-col gap-4 sm:flex-row">
                        <Button as-child size="lg" class="text-base">
                            <a href="https://github.com" target="_blank" rel="noopener">
                                Download for macOS
                                <span class="ml-2">→</span>
                            </a>
                        </Button>
                        <Button as-child variant="outline" size="lg" class="text-base">
                            <a href="https://github.com" target="_blank" rel="noopener">
                                <span class="mr-2">📖</span>
                                Documentation
                            </a>
                        </Button>
                    </div>

                    <!-- System Requirements Badge -->
                    <div class="mt-16 flex flex-wrap items-center justify-center gap-3">
                        <span class="text-sm font-medium text-muted-foreground">Requirements:</span>
                        <Badge variant="secondary" class="text-sm font-semibold">macOS 13+</Badge>
                        <Badge variant="secondary" class="text-sm font-semibold">Apple Silicon (M1+)</Badge>
                        <Badge variant="secondary" class="text-sm font-semibold">Privacy-First</Badge>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section with hover effects -->
        <section id="features" class="border-b border-border/40 py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-16 text-center">
                    <Badge variant="outline" class="mb-4">Features</Badge>
                    <h2 class="mb-4 text-4xl font-bold tracking-tight sm:text-5xl">
                        Everything You Need
                    </h2>
                    <p class="mx-auto max-w-2xl text-xl text-muted-foreground">
                        Built with modern technologies and best practices for production-ready deployments.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="feature in features" :key="feature.title">
                        <CardHeader>
                            <div class="mb-3 text-4xl">{{ feature.icon }}</div>
                            <CardTitle class="text-xl">{{ feature.title }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <CardDescription class="text-base leading-relaxed">{{ feature.description }}</CardDescription>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- How It Works Pipeline -->
        <section class="border-b border-border/40 bg-muted/30 py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-16 text-center">
                    <Badge variant="outline" class="mb-4">Process</Badge>
                    <h2 class="mb-4 text-4xl font-bold tracking-tight sm:text-5xl">
                        How It Works
                    </h2>
                    <p class="mx-auto max-w-2xl text-xl text-muted-foreground">
                        Three-stage intelligent conversion pipeline
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-3">
                    <div v-for="stage in pipeline" :key="stage.number" class="relative">
                        <Card class="h-full border-2">
                            <CardHeader>
                                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-2xl font-bold text-primary-foreground">
                                    {{ stage.number }}
                                </div>
                                <CardTitle class="text-2xl">{{ stage.title }}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <CardDescription class="text-base leading-relaxed">
                                    {{ stage.description }}
                                </CardDescription>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </section>

        <!-- Supported Formats -->
        <section class="border-b border-border/40 py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-16 text-center">
                    <Badge variant="outline" class="mb-4">Compatibility</Badge>
                    <h2 class="mb-4 text-4xl font-bold tracking-tight sm:text-5xl">
                        Supported Formats
                    </h2>
                    <p class="mx-auto max-w-2xl text-xl text-muted-foreground">
                        Comprehensive format support powered by FFmpeg
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <div class="mb-3 text-4xl">🎬</div>
                            <CardTitle class="text-2xl">Video</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                <Badge v-for="format in formats.video" :key="format">{{ format }}</Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <div class="mb-3 text-4xl">🎵</div>
                            <CardTitle class="text-2xl">Audio</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                <Badge v-for="format in formats.audio" :key="format">{{ format }}</Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <div class="mb-3 text-4xl">🖼️</div>
                            <CardTitle class="text-2xl">Images</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                <Badge v-for="format in formats.image" :key="format">{{ format }}</Badge>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- System Requirements -->
        <section class="border-b border-border/40 bg-muted/30 py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-16 text-center">
                    <Badge variant="outline" class="mb-4">Requirements</Badge>
                    <h2 class="mb-4 text-4xl font-bold tracking-tight sm:text-5xl">
                        System Requirements
                    </h2>
                </div>

                <div class="grid gap-8 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-2xl">Minimum</CardTitle>
                            <CardDescription>Basic requirements to run Honeymelon</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="req in systemRequirements.minimum" :key="req.label" class="flex justify-between border-b border-border/50 pb-3">
                                    <span class="font-semibold">{{ req.label }}</span>
                                    <span class="text-muted-foreground">{{ req.value }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle class="text-2xl">Recommended</CardTitle>
                            <CardDescription>Optimal performance for heavy workloads</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="req in systemRequirements.recommended" :key="req.label" class="flex justify-between border-b border-border/50 pb-3">
                                    <span class="font-semibold">{{ req.label }}</span>
                                    <span class="text-muted-foreground">{{ req.value }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Alert class="mt-8">
                    <AlertDescription class="text-base">
                        <strong>Important:</strong> Honeymelon is compiled exclusively for Apple Silicon (ARM64 architecture). Intel-based Macs are not supported.
                    </AlertDescription>
                </Alert>
            </div>
        </section>

        <!-- Key Features Checklist -->
        <section class="border-b border-border/40 py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                    <div>
                        <Badge variant="outline" class="mb-4">Technical Excellence</Badge>
                        <h2 class="mb-6 text-4xl font-bold tracking-tight sm:text-5xl">
                            Powerful Features
                        </h2>
                        <p class="text-xl leading-relaxed text-muted-foreground">
                            Built for professionals who need speed, quality, and privacy. Optimized for Apple Silicon with intelligent conversion strategies.
                        </p>
                    </div>

                    <Card>
                        <CardContent class="pt-6">
                            <ul class="space-y-4">
                                <li v-for="feature in keyFeatures" :key="feature" class="flex items-start gap-3">
                                    <Badge class="mt-0.5 h-6 w-6 flex-shrink-0 rounded-full p-0">
                                        <span class="flex h-full w-full items-center justify-center text-xs">✓</span>
                                    </Badge>
                                    <span class="text-base leading-relaxed">{{ feature }}</span>
                                </li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- CTA Section with gradient -->
        <section class="py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <Card class="relative overflow-hidden border-2 border-primary/20 bg-gradient-to-br from-primary/10 via-primary/5 to-background">
                    <div class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,theme(colors.primary.DEFAULT/10%),transparent)]" />
                    <CardContent class="flex flex-col items-center gap-8 py-16 text-center">
                        <Badge variant="secondary" class="text-base">Get Started Today</Badge>
                        <h2 class="max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl">
                            Ready to Transform Your Media Workflow?
                        </h2>
                        <p class="max-w-2xl text-xl leading-relaxed text-muted-foreground">
                            Download Honeymelon today and experience intelligent, privacy-first media conversion on your Mac.
                        </p>
                        <div class="flex flex-col gap-4 sm:flex-row">
                            <Button as-child size="lg" class="text-base">
                                <a href="https://github.com" target="_blank" rel="noopener">
                                    Download for macOS
                                    <span class="ml-2">→</span>
                                </a>
                            </Button>
                            <Button as-child variant="outline" size="lg" class="text-base">
                                <a href="https://github.com" target="_blank" rel="noopener">
                                    <span class="mr-2">📚</span>
                                    View Documentation
                                </a>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-border/40 bg-muted/50 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <AppLogoIcon class="h-8 w-8" />
                            <span class="text-lg font-bold">Honeymelon</span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Smart media converter for macOS Apple Silicon. Convert video, audio, and images with remux-first intelligence.
                        </p>
                    </div>
                    <div>
                        <h3 class="mb-4 text-sm font-semibold">Product</h3>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li><a href="#" class="hover:text-foreground">Features</a></li>
                            <li><a href="#" class="hover:text-foreground">Pricing</a></li>
                            <li><a href="#" class="hover:text-foreground">Documentation</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-4 text-sm font-semibold">Resources</h3>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li><a href="https://github.com" target="_blank" rel="noopener" class="hover:text-foreground">GitHub</a></li>
                            <li><a href="#" class="hover:text-foreground">API Reference</a></li>
                            <li><a href="#" class="hover:text-foreground">Support</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-4 text-sm font-semibold">Legal</h3>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li><a href="#" class="hover:text-foreground">Privacy Policy</a></li>
                            <li><a href="#" class="hover:text-foreground">Terms of Service</a></li>
                            <li><a href="#" class="hover:text-foreground">License</a></li>
                        </ul>
                    </div>
                </div>
                <Separator class="my-8" />
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <p class="text-sm text-muted-foreground">
                        © 2025 Honeymelon. Privacy-first media conversion for Mac.
                    </p>
                    <div class="flex gap-4">
                        <Badge variant="outline">macOS 13+</Badge>
                        <Badge variant="outline">Apple Silicon</Badge>
                        <Badge variant="outline">Privacy-First</Badge>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
