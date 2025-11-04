<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import PageHero from '@/components/marketing/PageHero.vue';
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
    Check,
    Heart,
    Shield,
    Sparkles,
    X,
    Zap,
} from 'lucide-vue-next';

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
            "This app exists to solve real problems for real people. No venture capital pressure, no growth-at-all-costs mentality. Just honest software that respects you.",
    },
];

const features = [
    'Lifetime license - no subscriptions',
    'All future updates included',
    'Privacy-first, no data collection',
    'Works 100% offline',
    'Native Apple Silicon performance',
    'Unlimited conversions',
    'All supported formats included',
    'Email support',
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
        <section class="border-b border-border/40 py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    badge="Our Philosophy"
                    title="Why This App Exists"
                    description="We built Honeymelon because software should serve people, not exploit them."
                />

                <div class="grid gap-8 md:grid-cols-3">
                    <Card
                        v-for="reason in whyBuilt"
                        :key="reason.title"
                        class="transition-shadow hover:shadow-lg"
                    >
                        <CardHeader class="pb-4">
                            <component
                                :is="reason.icon"
                                class="mb-4 h-12 w-12 text-primary"
                                :stroke-width="1.5"
                            />
                            <CardTitle class="text-xl">{{
                                reason.title
                            }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <CardDescription class="text-base leading-relaxed">
                                {{ reason.description }}
                            </CardDescription>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- Pricing Card -->
        <section class="border-b border-border/40 py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <Badge variant="outline" class="mb-6 px-4 py-1.5">
                        <Sparkles class="mr-2 h-4 w-4" />
                        Simple & Fair
                    </Badge>
                    <h2 class="mb-6 text-4xl font-bold tracking-tight sm:text-5xl">
                        One Price. Forever.
                    </h2>
                    <p class="mb-12 text-lg leading-relaxed text-muted-foreground">
                        Your purchase supports ongoing development and keeps
                        Honeymelon independent, privacy-focused, and
                        subscription-free.
                    </p>
                </div>

                <div class="mx-auto max-w-2xl">
                    <Card
                        class="relative overflow-hidden border-2 border-primary/20 shadow-xl"
                    >
                        <div
                            class="absolute inset-0 -z-10 bg-gradient-to-br from-primary/5 via-background to-background"
                        />
                        <CardHeader class="border-b border-border/50 pb-8 text-center">
                            <CardTitle class="mb-4 text-3xl">
                                Honeymelon Lifetime License
                            </CardTitle>
                            <div class="mb-4 flex items-baseline justify-center gap-2">
                                <span class="text-6xl font-bold">$29</span>
                                <span class="text-xl text-muted-foreground">
                                    USD
                                </span>
                            </div>
                            <CardDescription class="text-base">
                                Pay once, use forever. All updates included.
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
                                        class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full p-0"
                                    >
                                        <Check class="h-3.5 w-3.5" :stroke-width="3" />
                                    </Badge>
                                    <span class="text-base">{{ feature }}</span>
                                </li>
                            </ul>

                            <Button
                                as-child
                                size="lg"
                                class="w-full text-base shadow-lg shadow-primary/20"
                            >
                                <a href="/download">
                                    Get Honeymelon
                                    <ArrowRight class="ml-2 h-4 w-4" />
                                </a>
                            </Button>

                            <p class="mt-6 text-center text-sm text-muted-foreground">
                                30-day money-back guarantee. No questions asked.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- Comparison Table -->
        <section class="border-b border-border/40 bg-muted/30 py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    badge="The Difference"
                    title="Honeymelon vs. Others"
                    description="See why choosing privacy-first, subscription-free software matters."
                />

                <div class="mx-auto max-w-4xl">
                    <Card>
                        <CardContent class="p-0">
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-border/50">
                                            <th
                                                class="p-6 text-left font-semibold"
                                            >
                                                Feature
                                            </th>
                                            <th
                                                class="p-6 text-center font-semibold"
                                            >
                                                Honeymelon
                                            </th>
                                            <th
                                                class="p-6 text-center font-semibold text-muted-foreground"
                                            >
                                                Others
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(item, index) in comparisonFeatures"
                                            :key="item.feature"
                                            :class="
                                                index !== comparisonFeatures.length - 1
                                                    ? 'border-b border-border/50'
                                                    : ''
                                            "
                                        >
                                            <td class="p-6">
                                                {{ item.feature }}
                                            </td>
                                            <td class="p-6 text-center">
                                                <div
                                                    class="flex justify-center"
                                                >
                                                    <Check
                                                        v-if="item.us"
                                                        class="h-6 w-6 text-primary"
                                                        :stroke-width="3"
                                                    />
                                                    <X
                                                        v-else
                                                        class="h-6 w-6 text-muted-foreground"
                                                        :stroke-width="2"
                                                    />
                                                </div>
                                            </td>
                                            <td class="p-6 text-center">
                                                <div
                                                    class="flex justify-center"
                                                >
                                                    <Check
                                                        v-if="item.them"
                                                        class="h-6 w-6 text-muted-foreground"
                                                        :stroke-width="2"
                                                    />
                                                    <X
                                                        v-else
                                                        class="h-6 w-6 text-muted-foreground"
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
                </div>
            </div>
        </section>

        <!-- Supporting Development -->
        <section class="border-b border-border/40 py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <Badge variant="outline" class="mb-6 px-4 py-1.5">
                        <Heart class="mr-2 h-4 w-4" />
                        Supporting Development
                    </Badge>
                    <h2 class="mb-6 text-4xl font-bold tracking-tight sm:text-5xl">
                        Your Purchase Makes a Difference
                    </h2>
                    <p class="mb-8 text-lg leading-relaxed text-muted-foreground">
                        When you buy Honeymelon, you're not just getting
                        software. You're supporting:
                    </p>
                    <div class="space-y-6 text-left">
                        <Card class="transition-shadow hover:shadow-lg">
                            <CardContent class="flex items-start gap-4 pt-6">
                                <div
                                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary/10"
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
                                    <p class="leading-relaxed text-muted-foreground">
                                        Your purchase funds ongoing development,
                                        new features, bug fixes, and
                                        compatibility updates. We're in this for
                                        the long haul.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card class="transition-shadow hover:shadow-lg">
                            <CardContent class="flex items-start gap-4 pt-6">
                                <div
                                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary/10"
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
                                    <p class="leading-relaxed text-muted-foreground">
                                        No investors, no board meetings, no
                                        pressure to monetize your data. We
                                        answer to you, not shareholders.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card class="transition-shadow hover:shadow-lg">
                            <CardContent class="flex items-start gap-4 pt-6">
                                <div
                                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary/10"
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
                                    <p class="leading-relaxed text-muted-foreground">
                                        A movement toward ethical, user-focused
                                        software. Every purchase proves that
                                        people value privacy and fair pricing.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
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
                        <Badge variant="secondary" class="px-4 py-1.5">
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
                            users who believe software should respect people.
                        </p>
                        <Button
                            as-child
                            size="lg"
                            class="text-base shadow-lg shadow-primary/20"
                        >
                            <a href="/download">
                                Get Honeymelon for $29
                                <ArrowRight class="ml-2 h-4 w-4" />
                            </a>
                        </Button>
                        <p class="text-sm text-muted-foreground">
                            30-day money-back guarantee · Secure payment ·
                            Instant delivery
                        </p>
                    </CardContent>
                </Card>
            </div>
        </section>
    </MarketingLayout>
</template>
