<script setup lang="ts">
import { ConfirmDialog, PageHeader } from '@/components/admin';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDateTime, formatFileSize } from '@/lib/formatters';
import { dashboard } from '@/routes';
import releasesRoute from '@/routes/admin/releases';
import type { BreadcrumbItem } from '@/types';
import type { Release, ReleaseArtifact } from '@/types/resources';
import { Head, useForm } from '@inertiajs/vue3';
import {
    Calendar,
    ChevronDown,
    Download,
    FileArchive,
    GitCommit,
    Rocket,
    Tag,
    User,
} from 'lucide-vue-next';
import { marked } from 'marked';
import { computed, ref } from 'vue';

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
    return formatDateTime(props.release.published_at);
});

const formattedCreatedDate = computed(() => {
    if (!props.release.created_at) return 'N/A';
    const date = new Date(props.release.created_at);
    if (isNaN(date.getTime())) return 'N/A';
    return formatDateTime(props.release.created_at);
});

const downloadArtifact = (artifact: ReleaseArtifact) => {
    if (artifact.download_url) {
        window.open(artifact.download_url, '_blank');
    }
};

const hasArtifacts = computed(() => {
    return props.release.artifacts && props.release.artifacts.length > 0;
});

const publishRelease = () => {
    console.log('Publish release:', props.release.version);
    // Implement publish logic
};

// Delete release functionality
const deleteDialogOpen = ref(false);
const isDeleting = ref(false);

const deleteForm = useForm({});

const deleteRelease = () => {
    isDeleting.value = true;
    deleteForm.delete(releasesRoute.destroy(props.release.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            deleteDialogOpen.value = false;
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`Release ${release.version}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <PageHeader
                :title="release.version"
                :back-url="releasesRoute.index().url"
            >
                <template #title>
                    <span class="font-mono text-3xl font-bold tracking-tight">
                        {{ release.version }}
                    </span>
                </template>
                <template #badges>
                    <Badge
                        :variant="
                            release.channel === 'stable'
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
                    <Badge v-if="!release.published_at" variant="outline">
                        Unpublished
                    </Badge>
                </template>
                <template #description>
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
                </template>
                <template #actions>
                    <DropdownMenu v-if="hasArtifacts">
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm">
                                <Download class="mr-2 h-4 w-4" />
                                Download
                                <ChevronDown class="ml-2 h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-64">
                            <DropdownMenuLabel>Artifacts</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                v-for="artifact in release.artifacts"
                                :key="artifact.id"
                                :disabled="!artifact.download_url"
                                class="flex items-center justify-between"
                                @click="downloadArtifact(artifact)"
                            >
                                <div class="flex items-center gap-2">
                                    <FileArchive class="h-4 w-4" />
                                    <div class="flex flex-col">
                                        <span class="text-sm">{{
                                            artifact.platform
                                        }}</span>
                                        <span
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ formatFileSize(artifact.size) }}
                                        </span>
                                    </div>
                                </div>
                                <Badge
                                    variant="outline"
                                    class="text-xs uppercase"
                                >
                                    {{ artifact.source }}
                                </Badge>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Button v-else variant="outline" size="sm" disabled>
                        <Download class="mr-2 h-4 w-4" />
                        No Artifacts
                    </Button>
                    <Button size="sm" @click="publishRelease">
                        <Rocket class="mr-2 h-4 w-4" />
                        Publish
                    </Button>
                    <ConfirmDialog
                        v-model:open="deleteDialogOpen"
                        :title="`Delete Release ${release.version}?`"
                        confirm-label="Delete"
                        trigger-label="Delete"
                        :loading="isDeleting"
                        @confirm="deleteRelease"
                    >
                        <p class="mb-3">
                            This will permanently delete this release and all
                            associated artifacts.
                        </p>
                        <p class="font-medium text-destructive">
                            The GitHub release and tag will also be deleted.
                            This action cannot be undone.
                        </p>
                    </ConfirmDialog>
                </template>
            </PageHeader>

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
