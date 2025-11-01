<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { PaginatedResponse, Release } from '@/types/resources';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';

interface Props {
    releases: PaginatedResponse<Release>;
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
    });
};

const getChannelVariant = (channel: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    switch (channel) {
        case 'stable':
            return 'default';
        case 'beta':
            return 'secondary';
        case 'alpha':
            return 'outline';
        default:
            return 'outline';
    }
};
</script>

<template>
    <Head title="Releases" />

    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '#' }, { title: 'Releases', href: '#' }]">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Releases</h1>
                    <p class="text-muted-foreground mt-1">
                        Manage application releases and updates
                    </p>
                </div>
            </div>

            <div class="grid gap-4">
                <Card v-for="release in props.releases.data" :key="release.id">
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2">
                                    <CardTitle>{{ release.version }}</CardTitle>
                                    <Badge :variant="getChannelVariant(release.channel)">
                                        {{ release.channel }}
                                    </Badge>
                                    <Badge v-if="release.major" variant="destructive">
                                        Major
                                    </Badge>
                                </div>
                                <CardDescription>
                                    {{ release.tag }} - {{ release.commit_hash.substring(0, 7) }}
                                </CardDescription>
                            </div>
                            <Button as-child variant="outline" size="sm">
                                <Link :href="`/admin/releases/${release.id}`">
                                    View Details
                                </Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-2 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="text-muted-foreground">Published:</span>
                                <span>{{ formatDate(release.published_at) }}</span>
                            </div>
                            <div v-if="release.notes" class="border-t border-sidebar-border/70 pt-2 dark:border-sidebar-border">
                                <p class="line-clamp-2 text-muted-foreground">{{ release.notes }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="props.releases.meta.last_page > 1" class="flex items-center justify-between border-t border-sidebar-border/70 pt-4 dark:border-sidebar-border">
                <p class="text-muted-foreground text-sm">
                    Showing {{ props.releases.meta.from }} to {{ props.releases.meta.to }} of {{ props.releases.meta.total }} releases
                </p>
                <div class="flex gap-2">
                    <Button
                        v-if="props.releases.links.prev"
                        as-child
                        variant="outline"
                        size="sm"
                    >
                        <Link :href="props.releases.links.prev">Previous</Link>
                    </Button>
                    <Button
                        v-if="props.releases.links.next"
                        as-child
                        variant="outline"
                        size="sm"
                    >
                        <Link :href="props.releases.links.next">Next</Link>
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
