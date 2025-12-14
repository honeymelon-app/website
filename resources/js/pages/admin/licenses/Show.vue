<script setup lang="ts">
import { ConfirmDialog, PageHeader } from '@/components/admin';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useCopyToClipboard } from '@/composables/useCopyToClipboard';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDateTime, truncateId } from '@/lib/formatters';
import { getStatusVariant } from '@/lib/variants';
import { dashboard } from '@/routes';
import licenses from '@/routes/admin/licenses';
import type { BreadcrumbItem } from '@/types';
import type { License } from '@/types/resources';
import { Head, useForm } from '@inertiajs/vue3';
import {
    Check,
    Copy,
    ShieldOff,
    Smartphone,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { useFlashMessages } from '@/composables/useFlashMessages';

useFlashMessages();

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

// Revoke dialog state
const showRevokeDialog = ref(false);
const revokeForm = useForm({
    reason: '',
});

const processRevoke = (): void => {
    revokeForm.post(`/admin/licenses/${props.license.id}/revoke`, {
        preserveScroll: true,
        onSuccess: () => {
            showRevokeDialog.value = false;
            revokeForm.reset();
        },
    });
};
</script>

<template>
    <Head title="License Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6"
        >
            <div class="flex flex-col gap-6">
                <!-- Revoked/Refunded Banner -->
                <div
                    v-if="license.status === 'revoked'"
                    class="flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950"
                >
                    <ShieldOff class="h-5 w-5 text-red-600 dark:text-red-400" />
                    <div>
                        <p class="font-medium text-red-800 dark:text-red-200">
                            This license has been revoked
                        </p>
                        <p class="text-sm text-red-700 dark:text-red-300">
                            The license can no longer be used or activated.
                        </p>
                    </div>
                </div>
                <div
                    v-if="license.status === 'refunded'"
                    class="flex items-center gap-3 rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-950"
                >
                    <ShieldOff
                        class="h-5 w-5 text-yellow-600 dark:text-yellow-400"
                    />
                    <div>
                        <p
                            class="font-medium text-yellow-800 dark:text-yellow-200"
                        >
                            This license has been refunded
                        </p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            The associated order was refunded.
                        </p>
                    </div>
                </div>

                <PageHeader
                    title="License Details"
                    description="View license information and status."
                    :back-url="licenses.index().url"
                >
                    <template #badges>
                        <Badge :variant="getStatusVariant(license.status)">
                            {{ license.status }}
                        </Badge>
                        <Badge
                            v-if="license.is_activated"
                            variant="outline"
                            class="border-green-500 text-green-600"
                        >
                            Activated
                        </Badge>
                    </template>
                    <template #actions>
                        <ConfirmDialog
                            v-if="license.can_be_revoked"
                            v-model:open="showRevokeDialog"
                            title="Revoke License"
                            confirm-label="Revoke License"
                            trigger-label="Revoke License"
                            :loading="revokeForm.processing"
                            @confirm="processRevoke"
                        >
                            <template #trigger>
                                <Button variant="destructive" size="sm">
                                    <ShieldOff class="mr-2 h-4 w-4" />
                                    Revoke License
                                </Button>
                            </template>
                            <p class="mb-4">
                                Are you sure you want to revoke this license?
                            </p>
                            <ul class="mb-4 list-disc space-y-1 pl-6 text-sm">
                                <li>
                                    The license will no longer be valid for
                                    activation
                                </li>
                                <li>
                                    Already activated installations will
                                    continue to work
                                </li>
                                <li>This action cannot be undone</li>
                            </ul>
                            <div class="space-y-2">
                                <Label for="reason">Reason (optional)</Label>
                                <Input
                                    id="reason"
                                    v-model="revokeForm.reason"
                                    placeholder="Enter revocation reason..."
                                />
                            </div>
                        </ConfirmDialog>
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

                    <!-- Activation Status -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Smartphone class="h-5 w-5" />
                                Activation Status
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label>Activation Status</Label>
                                <div class="flex items-center gap-2">
                                    <Badge
                                        :variant="license.is_activated
                                            ? 'default'
                                            : 'secondary'
                                            "
                                    >
                                        {{
                                            license.is_activated
                                                ? 'Activated'
                                                : 'Not Activated'
                                        }}
                                    </Badge>
                                    <span
                                        v-if="license.activation_count > 0"
                                        class="text-xs text-muted-foreground"
                                    >
                                        ({{
                                            license.activation_count
                                        }}
                                        activation{{
                                            license.activation_count !== 1
                                                ? 's'
                                                : ''
                                        }})
                                    </span>
                                </div>
                            </div>

                            <div v-if="license.activated_at" class="space-y-2">
                                <Label>Activated At</Label>
                                <p class="text-sm">
                                    {{ formatDateTime(license.activated_at) }}
                                </p>
                            </div>

                            <div v-if="license.device_id" class="space-y-2">
                                <Label>Device ID</Label>
                                <code
                                    class="block rounded bg-muted px-3 py-2 font-mono text-xs break-all"
                                >
                                    {{ license.device_id }}
                                </code>
                            </div>

                            <div
                                v-if="!license.is_activated"
                                class="rounded-lg border border-dashed border-muted-foreground/30 p-4 text-center"
                            >
                                <p class="text-sm text-muted-foreground">
                                    This license has not been activated yet.
                                </p>
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
                                    {{
                                        formatDateTime(
                                            license.issued_at,
                                            'Not issued',
                                        )
                                    }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label>Created At</Label>
                                <p class="text-sm">
                                    {{ formatDateTime(license.created_at) }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
