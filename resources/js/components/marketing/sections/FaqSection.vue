<script setup lang="ts">
import AnimatedSection from '@/components/marketing/AnimatedSection.vue';
import Accordion from '@/components/ui/accordion/Accordion.vue';
import AccordionContent from '@/components/ui/accordion/AccordionContent.vue';
import AccordionItem from '@/components/ui/accordion/AccordionItem.vue';
import AccordionTrigger from '@/components/ui/accordion/AccordionTrigger.vue';

const faqs = [
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
        question: 'Can I use my license on multiple Macs?',
        answer: "Each license activates on one Mac device. The activation is one-time and cannot be transferred. If you need Honeymelon on multiple Macs, you'll need a separate license for each.",
    },
    {
        question: 'What happens when version 2.0 is released?',
        answer: 'Your license covers all v1.x updates. When v2.0 is released, existing customers will be offered a discounted upgrade. You can continue using v1.x indefinitely.',
    },
    {
        question: 'What if I want a refund?',
        answer: "We offer a 30-day money-back guarantee, no questions asked. If Honeymelon doesn't work for you, contact us for a full refund.",
    },
];
</script>

<template>
    <section
        id="faq"
        class="border-t border-border/50 bg-muted/30 py-24 sm:py-32"
    >
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <AnimatedSection>
                <div class="text-center">
                    <h2
                        class="text-3xl font-semibold tracking-tight text-foreground sm:text-4xl"
                    >
                        Frequently Asked Questions
                    </h2>
                </div>
            </AnimatedSection>

            <AnimatedSection :delay="100">
                <div class="mt-12">
                    <Accordion type="single" collapsible class="w-full">
                        <AccordionItem
                            v-for="(faq, index) in faqs"
                            :key="index"
                            :value="`item-${index}`"
                            class="border-b border-border/50"
                        >
                            <AccordionTrigger
                                class="py-5 text-left text-base font-medium text-foreground hover:no-underline"
                            >
                                {{ faq.question }}
                            </AccordionTrigger>
                            <AccordionContent
                                class="pb-5 text-muted-foreground"
                            >
                                {{ faq.answer }}
                            </AccordionContent>
                        </AccordionItem>
                    </Accordion>
                </div>
            </AnimatedSection>
        </div>
    </section>
</template>
