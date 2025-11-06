<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { show } from '@/routes/two-factor';
import { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Props {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
    message?: string;
}

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
    message: 'Two-factor authentication is managed through Cerberus IAM.',
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Two-Factor Authentication',
        href: show.url(),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Two-Factor Authentication" />
        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Two-Factor Authentication"
                    description="Two-factor authentication is managed through Cerberus IAM"
                />

                <div class="rounded-lg border bg-muted/50 p-6">
                    <p class="text-sm text-muted-foreground">
                        {{ message }}
                    </p>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Please configure two-factor authentication through the Cerberus IAM dashboard.
                    </p>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
