<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
    User,
} from 'lucide-vue-next';
import { marked } from 'marked';
import { computed } from 'vue';

interface Props {
    release: Release;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
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
        href: '#',
    },
];

// Parsed markdown notes
const parsedNotes = computed(() => {
    if (!props.release.notes) return '';
    return marked(props.release.notes);
});

const formattedPublishedDate = computed(() => {
    if (!props.release.published_at) return 'Not published';
    const date = new Date(props.release.published_at);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
});

const formattedCreatedDate = computed(() => {
    const date = new Date(props.release.created_at);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
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
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="goBack"
                        class="h-9 w-9"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <h1
                                class="font-mono text-3xl font-bold tracking-tight"
                            >
                                {{ release.version }}
                            </h1>
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
                            <Badge
                                v-if="!release.published_at"
                                variant="outline"
                            >
                                Unpublished
                            </Badge>
                        </div>
                        <!-- Metadata badges row -->
                        <div
                            class="flex flex-wrap items-center gap-3 text-sm text-muted-foreground"
                        >
                            <div class="flex items-center gap-1.5">
                                <Tag class="h-3.5 w-3.5" />
                                <span class="font-mono">{{ release.tag }}</span>
                            </div>
                            <Separator orientation="vertical" class="h-4" />
                            <div class="flex items-center gap-1.5">
                                <GitCommit class="h-3.5 w-3.5" />
                                <span class="font-mono">{{
                                    release.commit_hash?.substring(0, 8) ?? 'N/A'
                                }}</span>
                            </div>
                            <Separator orientation="vertical" class="h-4" />
                            <div class="flex items-center gap-1.5">
                                <Calendar class="h-3.5 w-3.5" />
                                <span>{{ formattedPublishedDate }}</span>
                            </div>
                            <template v-if="release.created_by">
                                <Separator orientation="vertical" class="h-4" />
                                <div class="flex items-center gap-1.5">
                                    <User class="h-3.5 w-3.5" />
                                    <span>{{ release.created_by }}</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
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

            <!-- Release Notes -->
            <Card>
                <CardHeader>
                    <CardTitle>Release Notes</CardTitle>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="release.notes"
                        class="prose prose-sm max-w-none dark:prose-invert"
                        v-html="parsedNotes"
                    />
                    <p v-else class="text-sm text-muted-foreground italic">
                        No release notes available for this release.
                    </p>
                </CardContent>
            </Card>

            <!-- Technical Details (minimal) -->
            <Card>
                <CardHeader>
                    <CardTitle>Technical Details</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="space-y-1">
                            <span
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Full Commit Hash
                            </span>
                            <code
                                class="block rounded bg-muted px-3 py-2 font-mono text-xs break-all"
                            >
                                {{ release.commit_hash ?? 'N/A' }}
                            </code>
                        </div>
                        <div class="space-y-1">
                            <span
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Dates
                            </span>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground"
                                        >Created:</span
                                    >
                                    <span class="text-xs">{{
                                        formattedCreatedDate
                                    }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground"
                                        >Published:</span
                                    >
                                    <span class="text-xs">{{
                                        formattedPublishedDate
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
