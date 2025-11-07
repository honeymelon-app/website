<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { show } from '@/routes/two-factor';
import type { AppPageProps, BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

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

const page = usePage<AppPageProps>();
const securityUrl = computed(() => page.props.cerberus.securityUrl);

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

                <div class="rounded-lg border bg-muted/50 p-6 text-sm">
                    <p class="text-muted-foreground">
                        {{ message }}
                    </p>
                    <p class="mt-2 text-muted-foreground">
                        Configure two-factor authentication and other advanced
                        security policies from the Cerberus IAM dashboard.
                    </p>

                    <Button
                        v-if="securityUrl"
                        class="mt-6 w-full sm:w-auto"
                        as-child
                    >
                        <a
                            :href="securityUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Open Cerberus security settings
                        </a>
                    </Button>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
