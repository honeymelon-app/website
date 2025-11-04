<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import licenses from '@/routes/admin/licenses';
import type { BreadcrumbItem } from '@/types';
import type { License } from '@/types/resources';
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Check, Copy } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    license: License;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Licenses',
        href: licenses.index().url,
    },
    {
        title: 'License Details',
        href: licenses.show(props.license.id).url,
    },
];

const isCopied = ref(false);

const copyToClipboard = async (text: string): Promise<void> => {
    try {
        await navigator.clipboard.writeText(text);
        isCopied.value = true;
        setTimeout(() => {
            isCopied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
    }
};

const getStatusVariant = (
    status: string,
): 'default' | 'secondary' | 'destructive' => {
    const variantMap: Record<
        string,
        'default' | 'secondary' | 'destructive'
    > = {
        active: 'default',
        revoked: 'destructive',
        expired: 'secondary',
    };
    return variantMap[status] || 'secondary';
};

const formatDate = (dateString: string | null): string => {
    if (!dateString) return 'Not issued';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="License Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex flex-col gap-6">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-3">
                            <Button
                                variant="ghost"
                                size="icon"
                                @click="router.visit(licenses.index().url)"
                            >
                                <ArrowLeft class="h-4 w-4" />
                            </Button>
                            <h3 class="text-2xl font-semibold tracking-tight">
                                License Details
                            </h3>
                        </div>
                        <p class="text-sm text-muted-foreground ml-12">
                            View license information and status.
                        </p>
                    </div>

                    <Badge :variant="getStatusVariant(license.status)">
                        {{ license.status }}
                    </Badge>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <!-- License Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>License Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label>License ID</Label>
                                <div class="flex gap-2">
                                    <code
                                        class="flex-1 rounded bg-muted px-3 py-2 text-xs font-mono"
                                    >
                                        {{ license.id }}
                                    </code>
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        @click="copyToClipboard(license.id)"
                                    >
                                        <Check v-if="isCopied" class="h-4 w-4" />
                                        <Copy v-else class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label>License Key</Label>
                                <div class="flex gap-2">
                                    <code
                                        class="flex-1 rounded bg-muted px-3 py-2 text-xs font-mono break-all"
                                    >
                                        {{ license.key }}
                                    </code>
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        @click="copyToClipboard(license.key)"
                                    >
                                        <Check v-if="isCopied" class="h-4 w-4" />
                                        <Copy v-else class="h-4 w-4" />
                                    </Button>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    This is the publicly visible license key (hashed).
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label>Max Major Version</Label>
                                <p class="text-sm">
                                    {{
                                        license.max_major_version === 999
                                            ? 'Lifetime (All Versions)'
                                            : `Version ${license.max_major_version}.x`
                                    }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label>Status</Label>
                                <Badge :variant="getStatusVariant(license.status)">
                                    {{ license.status }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Dates -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Important Dates</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label>Issued At</Label>
                                <p class="text-sm">
                                    {{ formatDate(license.issued_at) }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label>Created At</Label>
                                <p class="text-sm">
                                    {{ formatDate(license.created_at) }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
