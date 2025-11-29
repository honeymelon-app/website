<script setup lang="ts">
import ContentSection from '@/components/marketing/ContentSection.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Separator from '@/components/ui/separator/Separator.vue';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Privacy Policy - Honeymelon" />

    <MarketingLayout>
        <ContentSection
            title="Privacy Policy"
            subtitle="Last updated: January 2025"
        >
            <Card>
                <CardContent
                    class="prose max-w-none space-y-8 py-8 prose-slate dark:prose-invert"
                >
                    <section>
                        <h2 class="mb-4 text-2xl font-bold">
                            Our Commitment to Privacy
                        </h2>
                        <p
                            class="text-base leading-relaxed text-muted-foreground"
                        >
                            Honeymelon is built with privacy as a core
                            principle. We believe your media files and
                            conversion activities are your business, not ours.
                            This privacy policy explains how we handle (or
                            rather, don't handle) your data.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">
                            Data We Don't Collect
                        </h2>
                        <p
                            class="mb-4 text-base leading-relaxed text-muted-foreground"
                        >
                            Honeymelon operates entirely offline on your Mac. We
                            do not collect, transmit, or store:
                        </p>
                        <ul class="space-y-2 text-base text-muted-foreground">
                            <li>Your media files or their contents</li>
                            <li>File names, paths, or metadata</li>
                            <li>Conversion settings or preferences</li>
                            <li>Usage analytics or telemetry data</li>
                            <li>Crash reports or diagnostic information</li>
                            <li>Your IP address or device identifiers</li>
                            <li>Any personally identifiable information</li>
                        </ul>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">
                            How Honeymelon Works
                        </h2>
                        <p
                            class="text-base leading-relaxed text-muted-foreground"
                        >
                            All media conversion happens locally on your Mac
                            using your device's hardware. Honeymelon never
                            connects to the internet for conversion tasks and
                            does not require an internet connection to function.
                            Your files never leave your device.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">
                            Website Data Collection
                        </h2>
                        <p
                            class="mb-4 text-base leading-relaxed text-muted-foreground"
                        >
                            This website (honeymelon.app) collects minimal data:
                        </p>
                        <ul class="space-y-2 text-base text-muted-foreground">
                            <li>
                                <strong>Download Statistics:</strong> We track
                                the number of downloads to understand usage
                                patterns
                            </li>
                            <li>
                                <strong>Purchase Information:</strong> When
                                you purchase a license, payment processing is
                                handled by Stripe according to their privacy
                                policy
                            </li>
                            <li>
                                <strong>License Validation:</strong> When you
                                activate Honeymelon, your license key is
                                validated once. We store only the license key
                                and activation status, no personal information
                            </li>
                        </ul>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">
                            Third-Party Services
                        </h2>
                        <p
                            class="mb-4 text-base leading-relaxed text-muted-foreground"
                        >
                            We use the following third-party services:
                        </p>
                        <ul class="space-y-2 text-base text-muted-foreground">
                            <li>
                                <strong>Stripe:</strong> Payment processing
                                for license purchases (subject to Stripe's
                                Privacy Policy)
                            </li>
                        </ul>
                        <p
                            class="mt-4 text-base leading-relaxed text-muted-foreground"
                        >
                            Stripe may collect payment information necessary to
                            process transactions. We do not store credit card
                            information on our servers.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Cookies</h2>
                        <p
                            class="text-base leading-relaxed text-muted-foreground"
                        >
                            Our website uses minimal essential cookies for
                            authentication and session management if you log in
                            to manage your licenses. We do not use tracking
                            cookies or third-party advertising cookies.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Data Security</h2>
                        <p
                            class="text-base leading-relaxed text-muted-foreground"
                        >
                            Since Honeymelon processes all media locally on your
                            device and doesn't transmit your files, the security
                            of your media files is entirely under your control.
                            We recommend following standard macOS security best
                            practices to protect your data.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Your Rights</h2>
                        <p
                            class="mb-4 text-base leading-relaxed text-muted-foreground"
                        >
                            Since we collect minimal data, your rights are
                            straightforward:
                        </p>
                        <ul class="space-y-2 text-base text-muted-foreground">
                            <li>
                                <strong>Access:</strong> Contact us to request
                                any data we have associated with your license
                            </li>
                            <li>
                                <strong>Deletion:</strong> Request deletion of
                                your license and associated data at any time
                            </li>
                            <li>
                                <strong>Portability:</strong> Request export
                                of your license information
                            </li>
                        </ul>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">
                            Children's Privacy
                        </h2>
                        <p
                            class="text-base leading-relaxed text-muted-foreground"
                        >
                            Honeymelon is not directed at children under 13. We
                            do not knowingly collect personal information from
                            children. If you believe a child has provided us
                            with personal information, please contact us.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">
                            Changes to This Policy
                        </h2>
                        <p
                            class="text-base leading-relaxed text-muted-foreground"
                        >
                            We may update this privacy policy from time to time.
                            We will notify users of any material changes by
                            updating the "Last updated" date at the top of this
                            policy and, for active license holders, via email.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Contact Us</h2>
                        <p
                            class="text-base leading-relaxed text-muted-foreground"
                        >
                            If you have questions about this privacy policy or
                            our privacy practices, please contact us at
                            legal@honeymelon.app.
                        </p>
                    </section>
                </CardContent>
            </Card>
        </ContentSection>
    </MarketingLayout>
</template>
