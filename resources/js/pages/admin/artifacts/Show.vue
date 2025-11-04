<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import artifacts from '@/routes/admin/artifacts';
import releases from '@/routes/admin/releases';
import type { BreadcrumbItem } from '@/types';
import type { Artifact } from '@/types/api';
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Download,
    FileArchive,
    Hash,
    ShieldCheck,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    artifact: Artifact;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Artifacts',
        href: artifacts.index().url,
    },
    {
        title: props.artifact.filename,
        href: artifacts.show(props.artifact.id).url,
    },
];

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
};

const formattedCreatedDate = computed(() => {
    const date = new Date(props.artifact.created_at);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
});

const sourceVariantMap: Record<string, 'default' | 'secondary' | 'outline'> = {
    github: 'default',
    r2: 'secondary',
    s3: 'outline',
};

const goBack = () => {
    router.visit(artifacts.index().url);
};

const downloadArtifact = () => {
    window.open(props.artifact.url, '_blank');
};

const viewRelease = () => {
    if (props.artifact.release_id) {
        router.visit(releases.show(props.artifact.release_id).url);
    }
};
</script>

<template>
    <Head :title="`Artifact ${artifact.filename}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex flex-col gap-6">
                <!-- Header -->
                <div class="flex items-start justify-between gap-4">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <Button
                                variant="ghost"
                                size="icon"
                                @click="goBack"
                                class="h-8 w-8"
                            >
                                <ArrowLeft class="h-4 w-4" />
                            </Button>
                            <h3 class="text-2xl font-semibold tracking-tight">
                                {{ artifact.filename }}
                            </h3>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            View artifact details and checksums.
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <Badge
                            :variant="sourceVariantMap[artifact.source]"
                            class="uppercase"
                        >
                            {{ artifact.source }}
                        </Badge>
                        <Badge
                            v-if="artifact.notarized"
                            variant="outline"
                            class="gap-1"
                        >
                            <ShieldCheck class="h-3 w-3" />
                            Notarized
                        </Badge>
                        <Button @click="downloadArtifact" size="sm">
                            <Download class="mr-2 h-4 w-4" />
                            Download
                        </Button>
                    </div>
                </div>

                <!-- Artifact Info -->
                <div class="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <FileArchive class="h-5 w-5" />
                                Artifact Information
                            </CardTitle>
                            <CardDescription
                                >File details and metadata</CardDescription
                            >
                        </CardHeader>
                        <CardContent class="flex flex-col gap-4">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium"
                                    >Filename</span
                                >
                                <span
                                    class="font-mono text-sm break-all text-muted-foreground"
                                    >{{ artifact.filename }}</span
                                >
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium"
                                    >Platform</span
                                >
                                <Badge
                                    variant="outline"
                                    class="w-fit font-mono text-xs"
                                    >{{ artifact.platform }}</Badge
                                >
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium">Size</span>
                                <span class="text-sm text-muted-foreground">{{
                                    formatFileSize(artifact.size)
                                }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium">Source</span>
                                <span
                                    class="text-sm text-muted-foreground uppercase"
                                    >{{ artifact.source }}</span
                                >
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium"
                                    >Notarized</span
                                >
                                <div class="flex items-center gap-2">
                                    <ShieldCheck
                                        v-if="artifact.notarized"
                                        class="h-4 w-4 text-green-600 dark:text-green-500"
                                    />
                                    <span class="text-sm text-muted-foreground">
                                        {{ artifact.notarized ? 'Yes' : 'No' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium"
                                    >Release ID</span
                                >
                                <Button
                                    v-if="artifact.release_id"
                                    variant="link"
                                    class="h-auto justify-start p-0 font-mono text-sm"
                                    @click="viewRelease"
                                >
                                    {{ artifact.release_id }}
                                </Button>
                                <span
                                    v-else
                                    class="font-mono text-sm text-muted-foreground"
                                    >—</span
                                >
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium">Created</span>
                                <span class="text-sm text-muted-foreground">{{
                                    formattedCreatedDate
                                }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Hash class="h-5 w-5" />
                                Checksums & Signatures
                            </CardTitle>
                            <CardDescription>Verification data</CardDescription>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-4">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium">SHA256</span>
                                <div
                                    v-if="artifact.sha256"
                                    class="overflow-x-auto rounded-md bg-muted p-3"
                                >
                                    <code class="font-mono text-xs break-all">{{
                                        artifact.sha256
                                    }}</code>
                                </div>
                                <span
                                    v-else
                                    class="text-sm text-muted-foreground italic"
                                    >Not available</span
                                >
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium"
                                    >Signature</span
                                >
                                <div
                                    v-if="artifact.signature"
                                    class="overflow-x-auto rounded-md bg-muted p-3"
                                >
                                    <code class="font-mono text-xs break-all">{{
                                        artifact.signature
                                    }}</code>
                                </div>
                                <span
                                    v-else
                                    class="text-sm text-muted-foreground italic"
                                    >Not available</span
                                >
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Download Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Download URL</CardTitle>
                        <CardDescription
                            >Direct download link for this
                            artifact</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-2">
                            <div
                                class="flex-1 overflow-x-auto rounded-md bg-muted p-3"
                            >
                                <code class="font-mono text-xs break-all">{{
                                    artifact.url
                                }}</code>
                            </div>
                            <Button
                                @click="downloadArtifact"
                                size="sm"
                                variant="outline"
                            >
                                <Download class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
