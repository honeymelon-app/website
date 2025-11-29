<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import AnimatedSection from '@/components/marketing/AnimatedSection.vue';
import FeatureCard from '@/components/marketing/FeatureCard.vue';
import PageHero from '@/components/marketing/PageHero.vue';
import SectionHeader from '@/components/marketing/SectionHeader.vue';
import Accordion from '@/components/ui/accordion/Accordion.vue';
import AccordionContent from '@/components/ui/accordion/AccordionContent.vue';
import AccordionItem from '@/components/ui/accordion/AccordionItem.vue';
import AccordionTrigger from '@/components/ui/accordion/AccordionTrigger.vue';
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
    Check,
    Heart,
    Loader2,
    Shield,
    Sparkles,
    X,
    Zap,
} from 'lucide-vue-next';
import { ref } from 'vue';

const isCheckingOut = ref(false);
const checkoutError = ref<string | null>(null);

async function startCheckout() {
    if (isCheckingOut.value) return;

    isCheckingOut.value = true;
    checkoutError.value = null;

    try {
        const response = await fetch('/api/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({
                provider: 'stripe',
                amount: 2900,
                currency: 'usd',
                success_url: `${window.location.origin}/download?success=true`,
                cancel_url: `${window.location.origin}/pricing?cancelled=true`,
            }),
        });

        if (!response.ok) {
            throw new Error('Failed to create checkout session');
        }

        const data = await response.json();

        if (data.checkout_url) {
            window.location.href = data.checkout_url;
        } else {
            throw new Error('No checkout URL returned');
        }
    } catch (error) {
        checkoutError.value =
            error instanceof Error ? error.message : 'Something went wrong';
        isCheckingOut.value = false;
    }
}

const whyBuilt = [
    {
        icon: Shield,
        title: 'Privacy Matters',
        description:
            "Your data is yours. No tracking, no analytics, no servers. Everything happens on your Mac, as it should be. We're tired of apps that treat users as products.",
    },
    {
        icon: Zap,
        title: 'Subscriptions Are Broken',
        description:
            "Pay monthly forever? No thanks. Software shouldn't hold your workflow hostage. Buy once, own forever. Use it as long as it works for you.",
    },
    {
        icon: Heart,
        title: 'Built for People',
        description:
            'This app exists to solve real problems for real people. No venture capital pressure, no growth-at-all-costs mentality. Just honest software that respects you.',
    },
];

const features = [
    'Lifetime license - no subscriptions',
    'All v1.x updates included free',
    'Privacy-first, no data collection',
    'Works 100% offline',
    'Native Apple Silicon performance',
    'Unlimited conversions',
    'All supported formats included',
    'Email support',
];

const pricingFaqs = [
    {
        question: 'What happens when version 2.0 is released?',
        answer: 'Your license covers all updates within the major version you purchased (v1.x). When v2.0 is released, existing customers will be offered a discounted upgrade price as a thank-you for their support. You can continue using v1.x indefinitely.',
    },
    {
        question: 'Can I use my license on multiple Macs?',
        answer: 'Yes! Your license is valid for up to 3 Mac devices that you personally own or control. Perfect for your desktop, laptop, and home office setup.',
    },
    {
        question: 'What if I want a refund?',
        answer: "We offer a 30-day money-back guarantee, no questions asked. If Honeymelon doesn't work for you, simply contact us for a full refund.",
    },
];

const comparisonFeatures = [
    { feature: 'Subscription Required', us: false, them: true },
    { feature: 'Data Collection', us: false, them: true },
    { feature: 'Cloud Processing', us: false, them: true },
    { feature: 'Internet Required', us: false, them: true },
    { feature: 'Buy Once, Own Forever', us: true, them: false },
    { feature: 'Privacy Guaranteed', us: true, them: false },
    { feature: 'Native Performance', us: true, them: false },
    { feature: 'Unlimited Usage', us: true, them: false },
];
</script>

<template>
    <Head title="Pricing - Honest, Simple, Forever" />

    <MarketingLayout>
        <PageHero
            badge="Honest Pricing"
            :badge-icon="AppLogoIcon"
            title="Buy Once, "
            highlighted-text="Own Forever"
            description="No subscriptions. No hidden fees. No data harvesting. Just honest software that respects you and your privacy."
        />

        <!-- Why We Built This -->
        <section class="border-b border-border/40 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    badge="Our Philosophy"
                    title="Why This App Exists"
                    description="We built Honeymelon because software should serve people, not exploit them."
                />

                <div class="grid gap-8 md:grid-cols-3">
                    <FeatureCard
                        v-for="(reason, index) in whyBuilt"
                        :key="reason.title"
                        :title="reason.title"
                        :description="reason.description"
                        :icon="reason.icon"
                        :delay="index * 75"
                    />
                </div>
            </div>
        </section>

        <!-- Pricing Card -->
        <section class="border-b border-border/40 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <AnimatedSection>
                        <Badge
                            variant="outline"
                            class="mb-6 px-4 py-1.5 transition-colors hover:bg-muted"
                        >
                            <Sparkles class="mr-2 h-4 w-4" />
                            Simple & Fair
                        </Badge>
                        <h2
                            class="mb-6 text-4xl font-bold tracking-tight sm:text-5xl"
                        >
                            One Price. Forever.
                        </h2>
                        <p
                            class="mb-12 text-lg leading-relaxed text-muted-foreground"
                        >
                            Your purchase supports ongoing development and keeps
                            Honeymelon independent, privacy-focused, and
                            subscription-free.
                        </p>
                    </AnimatedSection>
                </div>

                <div class="mx-auto max-w-2xl">
                    <AnimatedSection :delay="150">
                        <Card
                            class="group relative overflow-hidden border-2 border-primary/20 transition-all duration-500 hover:-translate-y-1 hover:border-primary/30"
                        >
                            <div
                                class="absolute inset-0 -z-10 bg-gradient-to-br from-primary/5 via-background to-background transition-opacity duration-500 group-hover:from-primary/10"
                            />
                            <CardHeader
                                class="border-b border-border/50 pb-8 text-center"
                            >
                                <CardTitle class="mb-4 text-3xl">
                                    Honeymelon Lifetime License
                                </CardTitle>
                                <div
                                    class="mb-4 flex items-baseline justify-center gap-2"
                                >
                                    <span class="text-6xl font-bold">$29</span>
                                    <span class="text-xl text-muted-foreground">
                                        USD
                                    </span>
                                </div>
                                <CardDescription class="text-base">
                                    Pay once, use forever. All v1.x updates
                                    included.
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="pt-8">
                                <ul class="mb-8 space-y-4">
                                    <li
                                        v-for="feature in features"
                                        :key="feature"
                                        class="flex items-start gap-3"
                                    >
                                        <Badge
                                            class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full p-0 transition-transform hover:scale-110"
                                        >
                                            <Check
                                                class="h-3.5 w-3.5"
                                                :stroke-width="3"
                                            />
                                        </Badge>
                                        <span class="text-base">{{
                                            feature
                                        }}</span>
                                    </li>
                                </ul>

                                <Button
                                    :disabled="isCheckingOut"
                                    size="lg"
                                    class="group w-full text-base transition-all duration-300 hover:scale-[1.01]"
                                    @click="startCheckout"
                                >
                                    <Loader2
                                        v-if="isCheckingOut"
                                        class="mr-2 h-4 w-4 animate-spin"
                                    />
                                    <template v-else>
                                        Buy Honeymelon
                                        <ArrowRight
                                            class="ml-2 h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                                        />
                                    </template>
                                </Button>

                                <p
                                    v-if="checkoutError"
                                    class="mt-2 text-center text-sm text-destructive"
                                >
                                    {{ checkoutError }}
                                </p>

                                <p
                                    class="mt-6 text-center text-sm text-muted-foreground"
                                >
                                    30-day money-back guarantee. No questions
                                    asked.
                                </p>
                            </CardContent>
                        </Card>
                    </AnimatedSection>
                </div>
            </div>
        </section>

        <!-- Comparison Table -->
        <section class="border-b border-border/40 bg-muted/30 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    badge="The Difference"
                    title="Honeymelon vs. Others"
                    description="See why choosing privacy-first, subscription-free software matters."
                />

                <div class="mx-auto max-w-4xl">
                    <AnimatedSection>
                        <Card
                            class="transition-all duration-300 hover:-translate-y-1 hover:border-primary/30"
                        >
                            <CardContent class="p-0">
                                <div class="overflow-x-auto">
                                    <table class="w-full min-w-[500px]">
                                        <thead>
                                            <tr
                                                class="border-b border-border/50"
                                            >
                                                <th
                                                    class="p-4 text-left text-sm font-semibold md:p-6"
                                                >
                                                    Feature
                                                </th>
                                                <th
                                                    class="p-4 text-center text-sm font-semibold md:p-6"
                                                >
                                                    Honeymelon
                                                </th>
                                                <th
                                                    class="p-4 text-center text-sm font-semibold text-muted-foreground md:p-6"
                                                >
                                                    Others
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(
item, index
                                                ) in comparisonFeatures"
                                                :key="item.feature"
                                                :class="[
                                                    index !==
                                                        comparisonFeatures.length -
                                                        1
                                                        ? 'border-b border-border/50'
                                                        : '',
                                                    'transition-colors hover:bg-muted/30',
                                                ]"
                                            >
                                                <td class="p-4 text-sm md:p-6">
                                                    {{ item.feature }}
                                                </td>
                                                <td
                                                    class="p-4 text-center md:p-6"
                                                >
                                                    <div
                                                        class="flex justify-center"
                                                    >
                                                        <div
                                                            v-if="item.us"
                                                            class="flex h-7 w-7 items-center justify-center rounded-full bg-primary/10 transition-transform hover:scale-110"
                                                        >
                                                            <Check
                                                                class="h-4 w-4 text-primary"
                                                                :stroke-width="3
                                                                    "
                                                            />
                                                        </div>
                                                        <X
                                                            v-else
                                                            class="h-5 w-5 text-muted-foreground md:h-6 md:w-6"
                                                            :stroke-width="2"
                                                        />
                                                    </div>
                                                </td>
                                                <td
                                                    class="p-4 text-center md:p-6"
                                                >
                                                    <div
                                                        class="flex justify-center"
                                                    >
                                                        <div
                                                            v-if="item.them"
                                                            class="flex h-7 w-7 items-center justify-center rounded-full bg-muted"
                                                        >
                                                            <Check
                                                                class="h-4 w-4 text-muted-foreground"
                                                                :stroke-width="2
                                                                    "
                                                            />
                                                        </div>
                                                        <X
                                                            v-else
                                                            class="h-5 w-5 text-muted-foreground md:h-6 md:w-6"
                                                            :stroke-width="2"
                                                        />
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </CardContent>
                        </Card>
                    </AnimatedSection>
                </div>
            </div>
        </section>

        <!-- Supporting Development -->
        <section class="border-b border-border/40 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <AnimatedSection>
                        <Badge
                            variant="outline"
                            class="mb-6 px-4 py-1.5 transition-colors hover:bg-muted"
                        >
                            <Heart class="mr-2 h-4 w-4" />
                            Supporting Development
                        </Badge>
                        <h2
                            class="mb-6 text-4xl font-bold tracking-tight sm:text-5xl"
                        >
                            Your Purchase Makes a Difference
                        </h2>
                        <p
                            class="mb-8 text-lg leading-relaxed text-muted-foreground"
                        >
                            When you buy Honeymelon, you're getting a license
                            for the current major version (v1.x) with all
                            updates within that version included. You're also
                            supporting:
                        </p>
                    </AnimatedSection>

                    <div class="space-y-6 text-left">
                        <AnimatedSection :delay="100">
                            <Card
                                class="group transition-all duration-300 hover:-translate-y-1 hover:border-primary/30"
                            >
                                <CardContent
                                    class="flex items-start gap-4 pt-6"
                                >
                                    <div
                                        class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                                    >
                                        <Sparkles
                                            class="h-6 w-6 text-primary"
                                            :stroke-width="2"
                                        />
                                    </div>
                                    <div>
                                        <h3 class="mb-2 text-xl font-semibold">
                                            Continuous Improvement
                                        </h3>
                                        <p
                                            class="leading-relaxed text-muted-foreground"
                                        >
                                            Your purchase funds ongoing
                                            development, new features, bug
                                            fixes, and compatibility updates.
                                            We're in this for the long haul.
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>
                        </AnimatedSection>

                        <AnimatedSection :delay="175">
                            <Card
                                class="group transition-all duration-300 hover:-translate-y-1 hover:border-primary/30"
                            >
                                <CardContent
                                    class="flex items-start gap-4 pt-6"
                                >
                                    <div
                                        class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                                    >
                                        <Shield
                                            class="h-6 w-6 text-primary"
                                            :stroke-width="2"
                                        />
                                    </div>
                                    <div>
                                        <h3 class="mb-2 text-xl font-semibold">
                                            Independence
                                        </h3>
                                        <p
                                            class="leading-relaxed text-muted-foreground"
                                        >
                                            No investors, no board meetings, no
                                            pressure to monetize your data. We
                                            answer to you, not shareholders.
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>
                        </AnimatedSection>

                        <AnimatedSection :delay="250">
                            <Card
                                class="group transition-all duration-300 hover:-translate-y-1 hover:border-primary/30"
                            >
                                <CardContent
                                    class="flex items-start gap-4 pt-6"
                                >
                                    <div
                                        class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                                    >
                                        <Heart
                                            class="h-6 w-6 text-primary"
                                            :stroke-width="2"
                                        />
                                    </div>
                                    <div>
                                        <h3 class="mb-2 text-xl font-semibold">
                                            Better Software
                                        </h3>
                                        <p
                                            class="leading-relaxed text-muted-foreground"
                                        >
                                            A movement toward ethical,
                                            user-focused software. Every
                                            purchase proves that people value
                                            privacy and fair pricing.
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>
                        </AnimatedSection>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing FAQ Section -->
        <section class="border-b border-border/40 bg-muted/30 py-16">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    badge="Questions"
                    title="Common Questions"
                    description="Everything you need to know about pricing and licenses"
                />

                <AnimatedSection>
                    <Card
                        class="transition-all duration-300 hover:-translate-y-1 hover:border-primary/30"
                    >
                        <CardContent class="p-6">
                            <Accordion type="single" collapsible class="w-full">
                                <AccordionItem
                                    v-for="(faq, index) in pricingFaqs"
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
                            <Badge
                                variant="secondary"
                                class="px-4 py-1.5 transition-colors hover:bg-secondary/80"
                            >
                                Join the Movement
                            </Badge>
                            <h2
                                class="max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl"
                            >
                                Ready to Own Your Software?
                            </h2>
                            <p
                                class="max-w-2xl text-lg leading-relaxed text-muted-foreground"
                            >
                                One purchase. Lifetime access. No regrets. Join
                                users who believe software should respect
                                people.
                            </p>
                            <Button
                                :disabled="isCheckingOut"
                                size="lg"
                                class="group text-base transition-all duration-300 hover:scale-[1.02]"
                                @click="startCheckout"
                            >
                                <Loader2
                                    v-if="isCheckingOut"
                                    class="mr-2 h-4 w-4 animate-spin"
                                />
                                <template v-else>
                                    Buy Honeymelon for $29
                                    <ArrowRight
                                        class="ml-2 h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                                    />
                                </template>
                            </Button>
                            <p class="text-sm text-muted-foreground">
                                30-day money-back guarantee · Secure payment ·
                                Instant delivery
                            </p>
                        </CardContent>
                    </Card>
                </AnimatedSection>
            </div>
        </section>
    </MarketingLayout>
</template>
