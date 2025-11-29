<script setup lang="ts">
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Artifact, License } from '@/types/api';
import { Head } from '@inertiajs/vue3';
import {
    ArrowRight,
    Check,
    Copy,
    Download as DownloadIcon,
    Key,
    Package,
} from 'lucide-vue-next';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

const props = defineProps<{
    licenses: { data: License[] };
    latestArtifact: Artifact | null;
    hasActiveLicense: boolean;
}>();

const copiedKey = ref<string | null>(null);

async function copyLicenseKey(key: string) {
    await navigator.clipboard.writeText(key);
    copiedKey.value = key;
    setTimeout(() => {
        copiedKey.value = null;
    }, 2000);
}

function triggerDownload() {
    if (props.latestArtifact?.url) {
        window.location.href = props.latestArtifact.url;
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
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
                <p class="text-muted-foreground">
                    Manage your licenses and download Honeymelon
                </p>
            </div>

            <!-- License Section -->
            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <Key class="h-5 w-5 text-primary" />
                    <h2 class="text-xl font-semibold">Your Licenses</h2>
                </div>

                <template v-if="licenses.data.length > 0">
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Card
                            v-for="license in licenses.data"
                            :key="license.id"
                            class="transition-shadow hover:shadow-lg"
                        >
                            <CardHeader class="pb-3">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <CardTitle class="text-lg">
                                            Honeymelon License
                                        </CardTitle>
                                        <CardDescription>
                                            Version {{ license.max_major_version }}.x
                                        </CardDescription>
                                    </div>
                                    <Badge
                                        :variant="license.status === 'active' ? 'default' : 'secondary'"
                                    >
                                        {{ license.status }}
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-muted-foreground">
                                        License Key
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <code
                                            class="flex-1 rounded-md bg-muted px-3 py-2 text-sm font-mono break-all"
                                        >
                                            {{ license.key }}
                                        </code>
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            @click="copyLicenseKey(license.key)"
                                        >
                                            <Check
                                                v-if="copiedKey === license.key"
                                                class="h-4 w-4 text-green-600"
                                            />
                                            <Copy v-else class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    Issued {{ formatDate(license.created_at) }}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </template>

                <Card v-else class="border-dashed">
                    <CardContent class="flex flex-col items-center gap-4 py-12 text-center">
                        <Key class="h-12 w-12 text-muted-foreground/50" />
                        <div>
                            <h3 class="font-semibold">No licenses yet</h3>
                            <p class="text-sm text-muted-foreground">
                                Purchase a license to unlock Honeymelon
                            </p>
                        </div>
                        <Button as-child>
                            <a href="/pricing">
                                Get Honeymelon
                                <ArrowRight class="ml-2 h-4 w-4" />
                            </a>
                        </Button>
                    </CardContent>
                </Card>
            </section>

            <!-- Download Section -->
            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <Package class="h-5 w-5 text-primary" />
                    <h2 class="text-xl font-semibold">Downloads</h2>
                </div>

                <Card
                    v-if="latestArtifact"
                    class="transition-shadow hover:shadow-lg"
                >
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <CardTitle class="text-lg">
                                    Honeymelon for macOS
                                </CardTitle>
                                <CardDescription>
                                    Apple Silicon (M1, M2, M3, M4)
                                </CardDescription>
                            </div>
                            <Badge v-if="latestArtifact.release" variant="outline">
                                v{{ latestArtifact.release.version }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex flex-wrap gap-4 text-sm text-muted-foreground">
                            <span v-if="latestArtifact.release?.published_at">
                                Released {{ formatDate(latestArtifact.release.published_at) }}
                            </span>
                            <span>{{ formatBytes(latestArtifact.size) }}</span>
                            <span>macOS 13+</span>
                        </div>

                        <Button
                            :disabled="!hasActiveLicense"
                            class="w-full sm:w-auto"
                            @click="triggerDownload"
                        >
                            <DownloadIcon class="mr-2 h-4 w-4" />
                            Download Latest
                        </Button>

                        <p
                            v-if="!hasActiveLicense"
                            class="text-sm text-muted-foreground"
                        >
                            <a
                                href="/pricing"
                                class="font-medium text-primary underline underline-offset-2"
                            >
                                Purchase a license
                            </a>
                            to download Honeymelon
                        </p>
                    </CardContent>
                </Card>

                <Card v-else class="border-dashed">
                    <CardContent class="flex flex-col items-center gap-4 py-12 text-center">
                        <Package class="h-12 w-12 text-muted-foreground/50" />
                        <div>
                            <h3 class="font-semibold">No releases available</h3>
                            <p class="text-sm text-muted-foreground">
                                Check back soon for the latest release
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </section>
        </div>
    </AppLayout>
</template>
