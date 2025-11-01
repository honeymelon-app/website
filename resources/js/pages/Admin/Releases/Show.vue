<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Release } from '@/types/resources';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';

interface Props {
    release: Release & {
        artifacts?: Array<{
            id: string;
            platform: string;
            source: string;
            filename: string | null;
            size: number | null;
            notarized: boolean;
            created_at: string;
        }>;
        updates?: Array<{
            id: string;
            channel: string;
            version: string;
            is_latest: boolean;
            published_at: string | null;
        }>;
    };
}

const props = defineProps<Props>();

const formatDate = (dateString: string | null) => {
    if (!dateString) {
        return 'Not published';
    }
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatBytes = (bytes: number | null) => {
    if (!bytes) {
        return 'Unknown';
    }
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
};
</script>

<template>
    <Head :title="`Release ${release.version}`" />

    <AppLayout :breadcrumbs="[
        { title: 'Admin', href: '#' },
        { title: 'Releases', href: '/admin/releases' },
        { title: release.version, href: '#' }
    ]">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold tracking-tight">{{ release.version }}</h1>
                        <Badge>{{ release.channel }}</Badge>
                        <Badge v-if="release.major" variant="destructive">Major Release</Badge>
                    </div>
                    <p class="text-muted-foreground mt-1">
                        {{ release.tag }} - {{ release.commit_hash }}
                    </p>
                </div>
                <Button as-child variant="outline">
                    <Link href="/admin/releases">Back to Releases</Link>
                </Button>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Release Information</CardTitle>
                        <CardDescription>Basic details about this release</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <dl class="grid gap-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Version</dt>
                                <dd class="font-medium">{{ release.version }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Channel</dt>
                                <dd class="font-medium">{{ release.channel }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Tag</dt>
                                <dd class="font-mono text-xs">{{ release.tag }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Commit Hash</dt>
                                <dd class="font-mono text-xs">{{ release.commit_hash.substring(0, 12) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Published</dt>
                                <dd>{{ formatDate(release.published_at) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Created</dt>
                                <dd>{{ formatDate(release.created_at) }}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <Card v-if="release.notes">
                    <CardHeader>
                        <CardTitle>Release Notes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="whitespace-pre-wrap text-sm">{{ release.notes }}</p>
                    </CardContent>
                </Card>
            </div>

            <Card v-if="release.artifacts && release.artifacts.length > 0">
                <CardHeader>
                    <CardTitle>Artifacts ({{ release.artifacts.length }})</CardTitle>
                    <CardDescription>Build artifacts for this release</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-3">
                        <div
                            v-for="artifact in release.artifacts"
                            :key="artifact.id"
                            class="flex items-center justify-between rounded-lg border border-sidebar-border/70 p-3 dark:border-sidebar-border"
                        >
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ artifact.platform }}</span>
                                    <Badge variant="outline">{{ artifact.source }}</Badge>
                                    <Badge v-if="artifact.notarized" variant="secondary">Notarized</Badge>
                                </div>
                                <p class="text-muted-foreground text-sm">
                                    {{ artifact.filename || 'No filename' }} - {{ formatBytes(artifact.size) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="release.updates && release.updates.length > 0">
                <CardHeader>
                    <CardTitle>Update Manifests ({{ release.updates.length }})</CardTitle>
                    <CardDescription>Published update manifests for this release</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-3">
                        <div
                            v-for="update in release.updates"
                            :key="update.id"
                            class="flex items-center justify-between rounded-lg border border-sidebar-border/70 p-3 dark:border-sidebar-border"
                        >
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ update.channel }}</span>
                                    <Badge v-if="update.is_latest" variant="default">Latest</Badge>
                                </div>
                                <p class="text-muted-foreground text-sm">
                                    Version {{ update.version }} - {{ formatDate(update.published_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
