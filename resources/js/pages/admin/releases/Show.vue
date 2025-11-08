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
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import releasesRoute from '@/routes/admin/releases';
import type { BreadcrumbItem } from '@/types';
import type { Release } from '@/types/resources';
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    Download,
    GitCommit,
    Rocket,
    Tag,
} from 'lucide-vue-next';
import { marked } from 'marked';
import { computed } from 'vue';

interface Props {
    release: Release;
}

const props = defineProps<Props>();

const releaseBreadcrumbHref = computed(() => {
    const releaseId = props.release?.id ?? '';

    if (!releaseId) {
        return releasesRoute.index().url;
    }

    return releasesRoute.show(releaseId).url;
});

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Releases',
        href: releasesRoute.index().url,
    },
    {
        title: props.release.version,
        href: releaseBreadcrumbHref.value,
    },
]);

// Parsed markdown notes
const parsedNotes = computed(() => {
    if (!props.release.notes) return '';
    return marked(props.release.notes);
});

const formattedPublishedDate = computed(() => {
    if (!props.release.published_at) return 'Not published yet';
    const date = new Date(props.release.published_at);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
});

const formattedCreatedDate = computed(() => {
    const date = new Date(props.release.created_at);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
});

const goBack = () => {
    router.visit(releasesRoute.index().url);
};

const downloadArtifacts = () => {
    console.log('Download artifacts for:', props.release.version);
    // Implement download logic
};

const publishRelease = () => {
    console.log('Publish release:', props.release.version);
    // Implement publish logic
};
</script>

<template>
    <Head :title="`Release ${release.version}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header -->
            <div class="flex items-start justify-between gap-4">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            @click="goBack"
                            class="h-8 w-8"
                        >
                            <ArrowLeft class="h-4 w-4" />
                        </Button>
                        <div class="flex flex-col gap-1">
                            <h3
                                class="text-2xl font-semibold tracking-tight font-mono"
                            >
                                {{ release.version }}
                            </h3>
                            <p class="text-sm text-muted-foreground">
                                {{ release.tag }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Badge
                        :variant="release.channel === 'stable'
                                ? 'default'
                                : 'secondary'
                            "
                        class="capitalize"
                    >
                        {{ release.channel }}
                    </Badge>
                    <Badge v-if="release.major" variant="destructive">
                        Major
                    </Badge>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="downloadArtifacts"
                    >
                        <Download class="mr-2 h-4 w-4" />
                        Download
                    </Button>
                    <Button size="sm" @click="publishRelease">
                        <Rocket class="mr-2 h-4 w-4" />
                        Publish
                    </Button>
                </div>
            </div>

            <Separator />

            <!-- Metadata Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="flex items-start gap-3 pt-6">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                        >
                            <Tag class="h-5 w-5 text-primary" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs text-muted-foreground">
                                Version
                            </span>
                            <span class="font-mono text-sm font-medium">
                                {{ release.version }}
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="flex items-start gap-3 pt-6">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                        >
                            <GitCommit class="h-5 w-5 text-primary" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs text-muted-foreground">
                                Commit
                            </span>
                            <span class="font-mono text-sm font-medium">
                                {{ release.commit_hash.substring(0, 8) }}
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="flex items-start gap-3 pt-6">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                        >
                            <Calendar class="h-5 w-5 text-primary" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs text-muted-foreground">
                                Published
                            </span>
                            <span class="text-sm font-medium">
                                {{
                                    release.published_at
                                        ? new Date(
                                            release.published_at,
                                        ).toLocaleDateString('en-US', {
                                            month: 'short',
                                            day: 'numeric',
                                            year: 'numeric',
                                        })
                                        : 'Not published'
                                }}
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="flex items-start gap-3 pt-6">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                        >
                            <Calendar class="h-5 w-5 text-primary" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs text-muted-foreground">
                                Created
                            </span>
                            <span class="text-sm font-medium">
                                {{
                                    new Date(
                                        release.created_at,
                                    ).toLocaleDateString('en-US', {
                                        month: 'short',
                                        day: 'numeric',
                                        year: 'numeric',
                                    })
                                }}
                            </span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Release Details -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content - Release Notes -->
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Release Notes</CardTitle>
                            <CardDescription>
                                What's new in this release
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="release.notes"
                                class="prose prose-sm dark:prose-invert max-w-none"
                                v-html="parsedNotes"
                            />
                            <p
                                v-else
                                class="text-sm text-muted-foreground italic"
                            >
                                No release notes available for this release.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar - Additional Info -->
                <div class="flex flex-col gap-6">
                    <!-- Technical Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Technical Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-1">
                                <span
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    Commit Hash
                                </span>
                                <code
                                    class="block rounded bg-muted px-2 py-1.5 text-xs font-mono break-all"
                                >
                                    {{ release.commit_hash }}
                                </code>
                            </div>

                            <div class="space-y-1">
                                <span
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    Release Channel
                                </span>
                                <div>
                                    <Badge
                                        :variant="release.channel === 'stable'
                                                ? 'default'
                                                : 'secondary'
                                            "
                                        class="capitalize"
                                    >
                                        {{ release.channel }}
                                    </Badge>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <span
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    Release Type
                                </span>
                                <div>
                                    <Badge
                                        v-if="release.major"
                                        variant="destructive"
                                    >
                                        Major Release
                                    </Badge>
                                    <span
                                        v-else
                                        class="text-sm text-muted-foreground"
                                    >
                                        Minor/Patch Release
                                    </span>
                                </div>
                            </div>

                            <Separator />

                            <div class="space-y-1">
                                <span
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    Published At
                                </span>
                                <p class="text-sm">
                                    {{ formattedPublishedDate }}
                                </p>
                            </div>

                            <div class="space-y-1">
                                <span
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    Created At
                                </span>
                                <p class="text-sm">
                                    {{ formattedCreatedDate }}
                                </p>
                            </div>

                            <div
                                v-if="release.created_by"
                                class="space-y-1"
                            >
                                <span
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    Created By
                                </span>
                                <p class="text-sm">
                                    {{ release.created_by }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
