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
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import type { Artifact, Product } from '@/types/api';
import { Head } from '@inertiajs/vue3';

defineProps<{
    artifact?: Artifact | null;
    product?: Product | null;
}>();
</script>

<template>
    <Head title="Honeymelon – Smart Media Converter for macOS" />

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
        <FaqSection />

        <!-- 10. Final CTA -->
        <CtaSection />
    </MarketingLayout>
</template>
