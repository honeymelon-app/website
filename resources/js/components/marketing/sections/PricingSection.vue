<script setup lang="ts">
import AnimatedSection from '@/components/marketing/AnimatedSection.vue';
import Button from '@/components/ui/button/Button.vue';
import { ArrowRight, Check, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';

const isCheckingOut = ref(false);
const checkoutError = ref<string | null>(null);

async function startCheckout(): Promise<void> {
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
                cancel_url: `${window.location.origin}/#pricing?cancelled=true`,
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

const features = [
    'Lifetime license — no subscriptions',
    'All v1.x updates included free',
    'Privacy-first, zero data collection',
    'Works offline after one-time activation',
    'Native Apple Silicon performance',
    'Unlimited conversions forever',
];
</script>

<template>
    <section id="pricing" class="py-24 sm:py-32">
        <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <AnimatedSection>
                <div class="text-center">
                    <h2
                        class="text-3xl font-semibold tracking-tight text-foreground sm:text-4xl"
                    >
                        One Price. Forever.
                    </h2>
                    <p class="mt-4 text-lg text-muted-foreground">
                        No subscriptions. No hidden fees. Pay once, own it
                        forever.
                    </p>
                </div>
            </AnimatedSection>

            <AnimatedSection :delay="100">
                <div
                    class="mt-12 rounded-3xl border border-border bg-background p-8 sm:p-10"
                >
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-foreground">
                            Honeymelon License
                        </h3>
                        <div
                            class="mt-6 flex items-baseline justify-center gap-2"
                        >
                            <span
                                class="text-6xl font-semibold tracking-tight text-foreground"
                            >
                                $29
                            </span>
                            <span class="text-lg text-muted-foreground"
                                >USD</span
                            >
                        </div>
                        <p class="mt-2 text-sm text-muted-foreground">
                            One-time payment · Lifetime access
                        </p>
                    </div>

                    <ul class="mt-10 space-y-4">
                        <li
                            v-for="feature in features"
                            :key="feature"
                            class="flex items-start gap-3"
                        >
                            <Check
                                class="mt-0.5 h-5 w-5 shrink-0 text-primary"
                            />
                            <span class="text-muted-foreground">{{
                                feature
                            }}</span>
                        </li>
                    </ul>

                    <div class="mt-10">
                        <Button
                            :disabled="isCheckingOut"
                            size="lg"
                            class="h-12 w-full text-base"
                            @click="startCheckout"
                        >
                            <Loader2
                                v-if="isCheckingOut"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            <template v-else>
                                Buy Honeymelon
                                <ArrowRight class="ml-2 h-4 w-4" />
                            </template>
                        </Button>

                        <p
                            v-if="checkoutError"
                            class="mt-3 text-center text-sm text-destructive"
                        >
                            {{ checkoutError }}
                        </p>

                        <p
                            class="mt-4 text-center text-sm text-muted-foreground/70"
                        >
                            30-day money-back guarantee · Secure payment via
                            Stripe
                        </p>
                    </div>
                </div>
            </AnimatedSection>
        </div>
    </section>
</template>
