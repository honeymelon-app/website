<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Separator from '@/components/ui/separator/Separator.vue';
</script>

<template>
    <Head title="Privacy Policy - Honeymelon" />

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
                            <a href="/">Home</a>
                        </Button>
                        <Button as-child>
                            <a href="/download">Download</a>
                        </Button>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="mx-auto max-w-4xl px-4 py-24 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h1 class="mb-4 text-4xl font-bold tracking-tight sm:text-5xl">
                    Privacy Policy
                </h1>
                <p class="text-lg text-muted-foreground">
                    Last updated: January 2025
                </p>
            </div>

            <Card>
                <CardContent class="prose prose-slate max-w-none space-y-8 py-8 dark:prose-invert">
                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Our Commitment to Privacy</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            Honeymelon is built with privacy as a core principle. We believe your media files and conversion activities are your business, not ours. This privacy policy explains how we handle (or rather, don't handle) your data.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Data We Don't Collect</h2>
                        <p class="mb-4 text-base leading-relaxed text-muted-foreground">
                            Honeymelon operates entirely offline on your Mac. We do not collect, transmit, or store:
                        </p>
                        <ul class="space-y-2 text-base text-muted-foreground">
                            <li>• Your media files or their contents</li>
                            <li>• File names, paths, or metadata</li>
                            <li>• Conversion settings or preferences</li>
                            <li>• Usage analytics or telemetry data</li>
                            <li>• Crash reports or diagnostic information</li>
                            <li>• Your IP address or device identifiers</li>
                            <li>• Any personally identifiable information</li>
                        </ul>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">How Honeymelon Works</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            All media conversion happens locally on your Mac using your device's hardware. Honeymelon never connects to the internet for conversion tasks and does not require an internet connection to function. Your files never leave your device.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Website Data Collection</h2>
                        <p class="mb-4 text-base leading-relaxed text-muted-foreground">
                            This website (honeymelon.app) collects minimal data:
                        </p>
                        <ul class="space-y-2 text-base text-muted-foreground">
                            <li>• <strong>Download Statistics:</strong> We track the number of downloads to understand usage patterns</li>
                            <li>• <strong>Purchase Information:</strong> When you purchase a license, payment processing is handled by our payment providers (Stripe and LemonSqueezy) according to their privacy policies</li>
                            <li>• <strong>License Validation:</strong> When you activate Honeymelon, your license key is validated once. We store only the license key and activation status, no personal information</li>
                        </ul>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Third-Party Services</h2>
                        <p class="mb-4 text-base leading-relaxed text-muted-foreground">
                            We use the following third-party services:
                        </p>
                        <ul class="space-y-2 text-base text-muted-foreground">
                            <li>• <strong>Stripe:</strong> Payment processing for license purchases (subject to Stripe's Privacy Policy)</li>
                            <li>• <strong>LemonSqueezy:</strong> Alternative payment processing and license delivery (subject to LemonSqueezy's Privacy Policy)</li>
                        </ul>
                        <p class="mt-4 text-base leading-relaxed text-muted-foreground">
                            These services may collect payment information necessary to process transactions. We do not store credit card information on our servers.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Cookies</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            Our website uses minimal essential cookies for authentication and session management if you log in to manage your licenses. We do not use tracking cookies or third-party advertising cookies.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Data Security</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            Since Honeymelon processes all media locally on your device and doesn't transmit your files, the security of your media files is entirely under your control. We recommend following standard macOS security best practices to protect your data.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Your Rights</h2>
                        <p class="mb-4 text-base leading-relaxed text-muted-foreground">
                            Since we collect minimal data, your rights are straightforward:
                        </p>
                        <ul class="space-y-2 text-base text-muted-foreground">
                            <li>• <strong>Access:</strong> Contact us to request any data we have associated with your license</li>
                            <li>• <strong>Deletion:</strong> Request deletion of your license and associated data at any time</li>
                            <li>• <strong>Portability:</strong> Request export of your license information</li>
                        </ul>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Children's Privacy</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            Honeymelon is not directed at children under 13. We do not knowingly collect personal information from children. If you believe a child has provided us with personal information, please contact us.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Changes to This Policy</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            We may update this privacy policy from time to time. We will notify users of any material changes by updating the "Last updated" date at the top of this policy and, for active license holders, via email.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Contact Us</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            If you have questions about this privacy policy or our privacy practices, please contact us at privacy@honeymelon.app.
                        </p>
                    </section>
                </CardContent>
            </Card>
        </div>

        <!-- Footer -->
        <footer class="border-t border-border/40 bg-muted/50 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-center gap-4 text-center">
                    <div class="flex items-center gap-3">
                        <AppLogoIcon class="h-8 w-8" />
                        <span class="text-lg font-bold">Honeymelon</span>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        © 2025 Honeymelon. Privacy-first media conversion for Mac.
                    </p>
                    <div class="flex gap-4 text-sm">
                        <a href="/privacy" class="text-muted-foreground hover:text-foreground">Privacy Policy</a>
                        <span class="text-muted-foreground">•</span>
                        <a href="/terms" class="text-muted-foreground hover:text-foreground">Terms of Use</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
