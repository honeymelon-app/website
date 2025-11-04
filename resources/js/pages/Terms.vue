<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Separator from '@/components/ui/separator/Separator.vue';
</script>

<template>
    <Head title="Terms of Use - Honeymelon" />

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
                    Terms of Use
                </h1>
                <p class="text-lg text-muted-foreground">
                    Last updated: January 2025
                </p>
            </div>

            <Card>
                <CardContent class="prose prose-slate max-w-none space-y-8 py-8 dark:prose-invert">
                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Agreement to Terms</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            By downloading, installing, or using Honeymelon, you agree to be bound by these Terms of Use. If you do not agree to these terms, do not use the software.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">License Grant</h2>
                        <p class="mb-4 text-base leading-relaxed text-muted-foreground">
                            Subject to your compliance with these terms, we grant you a limited, non-exclusive, non-transferable, revocable license to:
                        </p>
                        <ul class="space-y-2 text-base text-muted-foreground">
                            <li>• Download and install Honeymelon on Mac devices you own or control</li>
                            <li>• Use Honeymelon for personal or commercial media conversion purposes</li>
                            <li>• Receive updates and support according to your license type</li>
                        </ul>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">License Types</h2>

                        <div class="mb-6">
                            <h3 class="mb-2 text-xl font-semibold">Standard License</h3>
                            <ul class="space-y-2 text-base text-muted-foreground">
                                <li>• Valid for one (1) user</li>
                                <li>• Can be installed on up to three (3) Mac devices owned by the license holder</li>
                                <li>• Includes one (1) year of updates and support</li>
                            </ul>
                        </div>

                        <div class="mb-6">
                            <h3 class="mb-2 text-xl font-semibold">Pro License</h3>
                            <ul class="space-y-2 text-base text-muted-foreground">
                                <li>• Valid for one (1) user</li>
                                <li>• Can be installed on up to five (5) Mac devices owned by the license holder</li>
                                <li>• Includes lifetime updates and priority support</li>
                            </ul>
                        </div>

                        <div>
                            <h3 class="mb-2 text-xl font-semibold">Team License</h3>
                            <ul class="space-y-2 text-base text-muted-foreground">
                                <li>• Valid for up to ten (10) users within the same organization</li>
                                <li>• Can be installed on unlimited Mac devices owned by the organization</li>
                                <li>• Includes lifetime updates and dedicated support</li>
                            </ul>
                        </div>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Restrictions</h2>
                        <p class="mb-4 text-base leading-relaxed text-muted-foreground">
                            You may not:
                        </p>
                        <ul class="space-y-2 text-base text-muted-foreground">
                            <li>• Reverse engineer, decompile, or disassemble the software</li>
                            <li>• Modify, adapt, or create derivative works of the software</li>
                            <li>• Remove or alter any proprietary notices or labels</li>
                            <li>• Share, sell, rent, lease, or distribute your license key</li>
                            <li>• Use the software for illegal purposes or to process illegal content</li>
                            <li>• Exceed the device or user limits of your license type</li>
                            <li>• Use the software to infringe on third-party copyrights or intellectual property</li>
                        </ul>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">System Requirements</h2>
                        <p class="mb-4 text-base leading-relaxed text-muted-foreground">
                            Honeymelon requires:
                        </p>
                        <ul class="space-y-2 text-base text-muted-foreground">
                            <li>• macOS 13 (Ventura) or later</li>
                            <li>• Apple Silicon (M1, M2, M3, M4, or later)</li>
                            <li>• At least 50 MB of available disk space</li>
                        </ul>
                        <p class="mt-4 text-base leading-relaxed text-muted-foreground">
                            Intel-based Macs are not supported. We are not responsible if the software does not function on unsupported systems.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Open Source Components</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            Honeymelon uses FFmpeg, which is licensed under the LGPL (Lesser General Public License). The use of FFmpeg does not affect your rights to use Honeymelon as described in these terms. FFmpeg source code and license information can be found at ffmpeg.org.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Content Responsibility</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            You are solely responsible for the media files you convert using Honeymelon. You must ensure you have the legal right to convert, modify, and distribute any content you process. We do not monitor, review, or have access to the content you convert.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Refund Policy</h2>
                        <p class="mb-4 text-base leading-relaxed text-muted-foreground">
                            We offer a 30-day money-back guarantee. If you're not satisfied with Honeymelon, contact us within 30 days of purchase for a full refund. Refund requests must include:
                        </p>
                        <ul class="space-y-2 text-base text-muted-foreground">
                            <li>• Your purchase email and license key</li>
                            <li>• Brief explanation of why you're requesting a refund</li>
                        </ul>
                        <p class="mt-4 text-base leading-relaxed text-muted-foreground">
                            Refunds are processed within 5-7 business days. Your license will be deactivated upon refund.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Warranty Disclaimer</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            HONEYMELON IS PROVIDED "AS IS" WITHOUT WARRANTY OF ANY KIND. WE DISCLAIM ALL WARRANTIES, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT. WE DO NOT WARRANT THAT THE SOFTWARE WILL BE ERROR-FREE OR UNINTERRUPTED.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Limitation of Liability</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            TO THE MAXIMUM EXTENT PERMITTED BY LAW, WE SHALL NOT BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, OR ANY LOSS OF PROFITS OR REVENUES, WHETHER INCURRED DIRECTLY OR INDIRECTLY, OR ANY LOSS OF DATA, USE, GOODWILL, OR OTHER INTANGIBLE LOSSES RESULTING FROM YOUR USE OF HONEYMELON. OUR TOTAL LIABILITY SHALL NOT EXCEED THE AMOUNT YOU PAID FOR YOUR LICENSE.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Updates and Support</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            We may release updates, bug fixes, and new versions of Honeymelon. Updates are provided according to your license type. We reserve the right to discontinue support for older versions. Support is provided via email on a best-effort basis.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Termination</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            We may terminate your license if you violate these terms. Upon termination, you must cease using Honeymelon and delete all copies from your devices. Termination does not entitle you to a refund unless required by law.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Changes to Terms</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            We reserve the right to modify these terms at any time. Material changes will be communicated via email to license holders. Continued use of Honeymelon after changes constitutes acceptance of the new terms.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Governing Law</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            These terms are governed by the laws of the jurisdiction in which our company is registered, without regard to conflict of law principles. Any disputes shall be resolved in the courts of that jurisdiction.
                        </p>
                    </section>

                    <Separator />

                    <section>
                        <h2 class="mb-4 text-2xl font-bold">Contact Us</h2>
                        <p class="text-base leading-relaxed text-muted-foreground">
                            If you have questions about these terms, please contact us at legal@honeymelon.app.
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
