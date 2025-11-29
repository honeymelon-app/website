<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
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
import type { Artifact, Release } from '@/types/resources';
import { Head, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    Cloud,
    Download,
    FileArchive,
    Github,
    Hash,
    ShieldCheck,
    Trash2,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface StorageStatus {
    synced: boolean;
    type: 'github' | 'r2' | 'missing_path' | 'not_found' | 'error';
    message: string;
    storage_size?: number;
    size_match?: boolean;
}

interface ArtifactWithSync extends Artifact {
    storage_status: StorageStatus;
    download_url?: string;
    release?: Release;
}

interface Props {
    artifact: ArtifactWithSync;
}

const props = defineProps<Props>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Artifacts',
        href: artifacts.index().url,
    },
    {
        title:
            props.artifact.filename ??
            props.artifact.id?.substring(0, 8) ??
            'Artifact',
        href: artifacts.show(props.artifact.id).url,
    },
]);

const showDeleteDialog = ref(false);

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
    const url = props.artifact.download_url ?? props.artifact.url;
    if (url) {
        window.open(url, '_blank');
    }
};

const viewRelease = () => {
    if (props.artifact.release?.id) {
        router.visit(releases.show(props.artifact.release.id).url);
    } else if (props.artifact.release_id) {
        router.visit(releases.show(props.artifact.release_id).url);
    }
};

const confirmDelete = () => {
    showDeleteDialog.value = true;
};

const handleDelete = () => {
    router.delete(artifacts.destroy(props.artifact.id).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
        },
    });
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
};
</script>

<template>
    <Head :title="`Artifact ${artifact.filename ?? 'Details'}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6"
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
                                {{ artifact.filename ?? 'Artifact Details' }}
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
                        <Button
                            @click="downloadArtifact"
                            size="sm"
                            :disabled="!artifact.download_url && !artifact.url"
                        >
                            <Download class="mr-2 h-4 w-4" />
                            Download
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            @click="confirmDelete"
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete
                        </Button>
                    </div>
                </div>

                <!-- Storage Status Banner -->
                <div
                    v-if="artifact.storage_status"
                    :class="[
                        'flex items-center gap-3 rounded-lg border p-4',
                        artifact.storage_status.synced
                            ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950'
                            : 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950',
                    ]"
                >
                    <template v-if="artifact.storage_status.synced">
                        <Github
                            v-if="artifact.storage_status.type === 'github'"
                            class="h-5 w-5 text-green-600 dark:text-green-400"
                        />
                        <AlertTriangle
                            v-else-if="
                                artifact.storage_status.size_match === false
                            "
                            class="h-5 w-5 text-yellow-600 dark:text-yellow-400"
                        />
                        <Cloud
                            v-else
                            class="h-5 w-5 text-green-600 dark:text-green-400"
                        />
                    </template>
                    <XCircle
                        v-else
                        class="h-5 w-5 text-red-600 dark:text-red-400"
                    />
                    <div>
                        <p
                            :class="[
                                'font-medium',
                                artifact.storage_status.synced
                                    ? 'text-green-800 dark:text-green-200'
                                    : 'text-red-800 dark:text-red-200',
                            ]"
                        >
                            {{
                                artifact.storage_status.synced
                                    ? 'Storage Synced'
                                    : 'Storage Issue'
                            }}
                        </p>
                        <p
                            :class="[
                                'text-sm',
                                artifact.storage_status.synced
                                    ? 'text-green-700 dark:text-green-300'
                                    : 'text-red-700 dark:text-red-300',
                            ]"
                        >
                            {{ artifact.storage_status.message }}
                            <template
                                v-if="
                                    artifact.storage_status.size_match === false
                                "
                            >
                                (Size mismatch: DB={{
                                    artifact.size
                                        ? formatFileSize(artifact.size)
                                        : 'N/A'
                                }}, R2={{
                                    artifact.storage_status.storage_size
                                        ? formatFileSize(
                                              artifact.storage_status
                                                  .storage_size,
                                          )
                                        : 'N/A'
                                }})
                            </template>
                        </p>
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
                                    >{{ artifact.filename ?? 'N/A' }}</span
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
                                    artifact.size
                                        ? formatFileSize(artifact.size)
                                        : 'N/A'
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
                                <span class="text-sm font-medium">Release</span>
                                <Button
                                    v-if="
                                        artifact.release?.version ||
                                        artifact.release_id
                                    "
                                    variant="link"
                                    class="h-auto justify-start p-0 font-mono text-sm"
                                    @click="viewRelease"
                                >
                                    {{
                                        artifact.release?.version ??
                                        artifact.release_id
                                    }}
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
                            >Direct download link for this artifact (signed URL
                            valid for 1 hour)</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-2">
                            <div
                                class="flex-1 overflow-x-auto rounded-md bg-muted p-3"
                            >
                                <code class="font-mono text-xs break-all">{{
                                    artifact.download_url ??
                                    artifact.url ??
                                    'Not available'
                                }}</code>
                            </div>
                            <Button
                                @click="downloadArtifact"
                                size="sm"
                                variant="outline"
                                :disabled="
                                    !artifact.download_url && !artifact.url
                                "
                            >
                                <Download class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Storage Path -->
                <Card v-if="artifact.path">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Cloud class="h-5 w-5" />
                            Storage Path
                        </CardTitle>
                        <CardDescription
                            >R2/S3 storage location</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto rounded-md bg-muted p-3">
                            <code class="font-mono text-xs break-all">{{
                                artifact.path
                            }}</code>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <AlertDialog
            :open="showDeleteDialog"
            @update:open="showDeleteDialog = $event"
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete Artifact</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to delete "{{
                            artifact.filename
                        }}"?
                        <span
                            v-if="
                                artifact.source === 'r2' ||
                                artifact.source === 's3'
                            "
                            class="mt-2 block font-medium text-destructive"
                        >
                            This will also delete the file from R2 storage.
                        </span>
                        This action cannot be undone.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="cancelDelete">
                        Cancel
                    </AlertDialogCancel>
                    <AlertDialogAction
                        @click="handleDelete"
                        class="bg-destructive hover:bg-destructive/90"
                    >
                        Delete
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
