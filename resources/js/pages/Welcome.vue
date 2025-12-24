<script setup lang="ts">
import ComparisonSection from '@/components/marketing/sections/ComparisonSection.vue';
import CtaSection from '@/components/marketing/sections/CtaSection.vue';
import DownloadSection from '@/components/marketing/sections/DownloadSection.vue';
import FaqSection from '@/components/marketing/sections/FaqSection.vue';
import FeaturesSection from '@/components/marketing/sections/FeaturesSection.vue';
import HeroSection from '@/components/marketing/sections/HeroSection.vue';
import HowItWorksSection from '@/components/marketing/sections/HowItWorksSection.vue';
import InterfaceSection from '@/components/marketing/sections/InterfaceSection.vue';
import PricingSection from '@/components/marketing/sections/PricingSection.vue';
import ProofSection from '@/components/marketing/sections/ProofSection.vue';
import {
    generateFaqSchema,
    generateOrganizationSchema,
    generateSoftwareApplicationSchema,
    useSeoMeta,
} from '@/composables';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import type { Artifact, Product } from '@/types/api';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    artifact?: Artifact | null;
    product?: Product | null;
}>();

const page = usePage();
const appUrl = computed(() => (page.props.appUrl as string) || '');

// FAQ data for both display and structured data
const faqs = [
    {
        question: 'Is Honeymelon free?',
        answer: 'Honeymelon is free to download. A paid license is required to use the app. The license is a one-time purchase with no subscriptions.',
    },
    {
        question: 'What are the system requirements?',
        answer: 'Honeymelon requires macOS 13 (Ventura) or later and an Apple Silicon chip (M1 or newer). Intel-based Macs are not supported.',
    },
    {
        question: 'Does Honeymelon work offline?',
        answer: 'Yes. Honeymelon requires a one-time internet connection to activate your license. After that, the app runs fully offline—no telemetry, no license checks, nothing.',
    },
    {
        question: 'What file formats are supported?',
        answer: 'Honeymelon supports MP4, MOV, MKV, WebM, and GIF for video; M4A, MP3, FLAC, WAV, and Opus for audio; and PNG, JPEG, and WebP for images. Powered by FFmpeg.',
    },
    {
        question: 'Do you collect my files or data?',
        answer: 'No. All conversions happen locally on your Mac. Your files never leave your device, and we collect zero telemetry or usage data.',
    },
    {
        question: 'Can I use my license on multiple Macs?',
        answer: "Each license activates on one Mac device. The activation is one-time and cannot be transferred. If you need Honeymelon on multiple Macs, you'll need a separate license for each.",
    },
];

// Generate price for structured data
const priceData = computed(() => {
    if (!props.product) return undefined;
    return {
        price: props.product.price_cents / 100,
        priceCurrency: props.product.currency?.toUpperCase() || 'USD',
    };
});

// Generate JSON-LD structured data
const jsonLdSchemas = computed(() => {
    const schemas = [];

    // SoftwareApplication schema
    schemas.push(
        generateSoftwareApplicationSchema({
            name: 'Honeymelon',
            description:
                'Native macOS media converter for Apple Silicon. Free download, paid license required to use. Convert video, audio, and images offline with remux-first intelligence.',
            operatingSystem: 'macOS',
            applicationCategory: 'MultimediaApplication',
            url: appUrl.value,
            image: `${appUrl.value}/images/og-image.png`,
            offers: priceData.value,
        }),
    );

    // Organization schema
    schemas.push(
        generateOrganizationSchema({
            name: 'Honeymelon',
            url: appUrl.value,
            logo: `${appUrl.value}/images/logo.png`,
        }),
    );

    // FAQ schema
    schemas.push(generateFaqSchema(faqs));

    return schemas;
});

// SEO meta configuration
const { headTags, jsonLdScript } = useSeoMeta({
    title: 'Honeymelon – Smart Media Converter for macOS',
    description:
        'Native macOS media converter for Apple Silicon. Free download, paid license required. Convert video, audio, and images with remux-first intelligence. No subscriptions, files stay local.',
    canonical: '/',
    ogImage: '/images/og-image.png',
    jsonLd: jsonLdSchemas,
});
</script>

<template>
    <Head>
        <title>{{ headTags.title }}</title>
        <meta name="description" :content="headTags.description" />
        <link rel="canonical" :href="headTags.canonical" />
        <meta name="robots" :content="headTags.robots" />

        <!-- OpenGraph -->
        <meta property="og:title" :content="headTags.ogTitle" />
        <meta property="og:description" :content="headTags.ogDescription" />
        <meta property="og:image" :content="headTags.ogImage" />
        <meta property="og:url" :content="headTags.ogUrl" />
        <meta property="og:type" :content="headTags.ogType" />
        <meta property="og:site_name" :content="headTags.ogSiteName" />

        <!-- Twitter Card -->
        <meta name="twitter:card" :content="headTags.twitterCard" />
        <meta name="twitter:title" :content="headTags.twitterTitle" />
        <meta
            name="twitter:description"
            :content="headTags.twitterDescription"
        />
        <meta name="twitter:image" :content="headTags.twitterImage" />

        <!-- JSON-LD Structured Data -->
        <script type="application/ld+json" v-html="jsonLdScript" />
    </Head>

    <MarketingLayout>
        <!-- 1. Hero -->
        <HeroSection />

        <!-- 2. Proof / Why it's different -->
        <ProofSection />

        <!-- 3. Features (grouped) -->
        <FeaturesSection />

        <!-- 4. How it works -->
        <HowItWorksSection />

        <!-- 5. Interface showcase -->
        <InterfaceSection />

        <!-- 6. Pricing -->
        <PricingSection :product="product" />

        <!-- 7. Download -->
        <DownloadSection :artifact="artifact" />

        <!-- 8. Comparison -->
        <ComparisonSection />

        <!-- 9. FAQ -->
        <FaqSection :faqs="faqs" />

        <!-- 10. Final CTA -->
        <CtaSection />
    </MarketingLayout>
</template>
