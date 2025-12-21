<script setup lang="ts">
import AnimatedSection from '@/components/marketing/AnimatedSection.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';
import { Download } from 'lucide-vue-next';
import type { Artifact } from '@/types/api';

const props = defineProps<{
    artifact?: Artifact | null;
}>();

function triggerDownload(): void {
    if (props.artifact?.url) {
        window.location.href = props.artifact.url;
    }
}

function formatBytes(bytes: number): string {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <section id="download" class="border-t border-border/50 bg-muted/30 py-24 sm:py-32">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <AnimatedSection>
                <div class="text-center">
                    <h2
                        class="text-3xl font-semibold tracking-tight text-foreground sm:text-4xl"
                    >
                        Download for macOS
                    </h2>
                    <p class="mt-4 text-lg text-muted-foreground">
                        Free to download. Requires a license to activate.
                    </p>
                </div>
            </AnimatedSection>

            <AnimatedSection :delay="100">
                <div
                    class="mt-12 rounded-3xl border border-border bg-background p-8 sm:p-10"
                >
                    <div class="grid gap-8 sm:grid-cols-2">
                        <!-- Download Info -->
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xl font-semibold text-foreground">
                                    Honeymelon
                                </h3>
                                <Badge
                                    v-if="artifact?.release"
                                    variant="secondary"
                                >
                                    v{{ artifact.release.version }}
                                </Badge>
                            </div>

                            <p class="mt-2 text-muted-foreground">
                                Apple Silicon (M1, M2, M3, M4)
                            </p>

                            <dl v-if="artifact" class="mt-6 space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-muted-foreground">Released</dt>
                                    <dd class="font-medium text-foreground">
                                        {{
                                            artifact.release?.published_at
                                                ? formatDate(artifact.release.published_at)
                                                : 'N/A'
                                        }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-muted-foreground">Size</dt>
                                    <dd class="font-medium text-foreground">
                                        {{ formatBytes(artifact.size) }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-muted-foreground">Requires</dt>
                                    <dd class="font-medium text-foreground">
                                        macOS 13+
                                    </dd>
                                </div>
                            </dl>

                            <Button
                                :disabled="!artifact?.url"
                                size="lg"
                                class="mt-6 h-12 w-full text-base"
                                @click="triggerDownload"
                            >
                                <Download class="mr-2 h-4 w-4" />
                                Download for Apple Silicon
                            </Button>

                            <p class="mt-3 text-center text-xs text-muted-foreground/70">
                                Intel-based Macs are not supported
                            </p>
                        </div>

                        <!-- Installation Steps -->
                        <div class="border-t border-border/50 pt-8 sm:border-l sm:border-t-0 sm:pl-8 sm:pt-0">
                            <h4 class="font-semibold text-foreground">
                                Installation
                            </h4>
                            <ol class="mt-4 space-y-4 text-sm">
                                <li class="flex gap-3">
                                    <span
                                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary"
                                    >
                                        1
                                    </span>
                                    <span class="text-muted-foreground">
                                        Download and open the DMG file
                                    </span>
                                </li>
                                <li class="flex gap-3">
                                    <span
                                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary"
                                    >
                                        2
                                    </span>
                                    <span class="text-muted-foreground">
                                        Drag Honeymelon to your Applications folder
                                    </span>
                                </li>
                                <li class="flex gap-3">
                                    <span
                                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary"
                                    >
                                        3
                                    </span>
                                    <span class="text-muted-foreground">
                                        Launch and enter your license key to activate
                                    </span>
                                </li>
                            </ol>

                            <p class="mt-6 text-sm text-muted-foreground">
                                Don't have a license?
                                <a
                                    href="#pricing"
                                    class="font-medium text-primary hover:underline"
                                >
                                    Purchase one above
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </AnimatedSection>
        </div>
    </section>
</template>
