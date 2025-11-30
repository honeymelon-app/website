<script setup lang="ts">
import { PageHeader } from '@/components/admin';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { useCopyToClipboard } from '@/composables/useCopyToClipboard';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDateTime, truncateId } from '@/lib/formatters';
import { getStatusVariant } from '@/lib/variants';
import { dashboard } from '@/routes';
import licenses from '@/routes/admin/licenses';
import type { BreadcrumbItem } from '@/types';
import type { License } from '@/types/resources';
import { Head } from '@inertiajs/vue3';
import { Check, Copy } from 'lucide-vue-next';

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
        title: truncateId(props.license.id),
        href: licenses.show(props.license.id).url,
    },
];

const { copied: isCopied, copy: copyToClipboard } = useCopyToClipboard();

const formatDate = (dateString: string | null): string => {
    if (!dateString) {
        return 'Not issued';
    }
    return formatDateTime(dateString);
};
</script>

<template>
    <Head title="License Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6"
        >
            <div class="flex flex-col gap-6">
                <PageHeader
                    title="License Details"
                    description="View license information and status."
                    :back-url="licenses.index().url"
                >
                    <template #badges>
                        <Badge :variant="getStatusVariant(license.status)">
                            {{ license.status }}
                        </Badge>
                    </template>
                </PageHeader>

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
                                        class="flex-1 rounded bg-muted px-3 py-2 font-mono text-xs"
                                    >
                                        {{ license.id }}
                                    </code>
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        @click="copyToClipboard(license.id)"
                                    >
                                        <Check
                                            v-if="isCopied"
                                            class="h-4 w-4"
                                        />
                                        <Copy v-else class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label>License Key</Label>
                                <div class="flex gap-2">
                                    <code
                                        class="flex-1 rounded bg-muted px-3 py-2 font-mono text-xs break-all"
                                    >
                                        {{ license.key }}
                                    </code>
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        @click="copyToClipboard(license.key)"
                                    >
                                        <Check
                                            v-if="isCopied"
                                            class="h-4 w-4"
                                        />
                                        <Copy v-else class="h-4 w-4" />
                                    </Button>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    This is the publicly visible license key
                                    (hashed).
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
                                <Badge
                                    :variant="getStatusVariant(license.status)"
                                >
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
