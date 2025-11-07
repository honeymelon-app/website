<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/user-password';
import type { AppPageProps, BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    managePasswordUrl?: string | null;
}

const props = defineProps<Props>();
const page = usePage<AppPageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Password settings',
        href: edit().url,
    },
];

const managePasswordUrl = computed(
    () => props.managePasswordUrl ?? page.props.cerberus.securityUrl,
);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Password settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Update password"
                    description="Password changes are managed in Cerberus IAM"
                />

                <div class="rounded-lg border bg-muted/50 p-6 text-sm">
                    <p class="text-muted-foreground">
                        Honeymelon relies on Cerberus IAM for authentication,
                        so we no longer store or verify passwords locally.
                    </p>
                    <p class="mt-2 text-muted-foreground">
                        Update your password (and any other security settings)
                        from the Cerberus security dashboard. Changes will apply
                        to every connected application instantly.
                    </p>

                    <Button
                        v-if="managePasswordUrl"
                        class="mt-6 w-full sm:w-auto"
                        as-child
                    >
                        <a
                            :href="managePasswordUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Open Cerberus security settings
                        </a>
                    </Button>
                    <Button
                        v-else
                        class="mt-6 w-full sm:w-auto"
                        variant="outline"
                        disabled
                    >
                        Cerberus security URL not configured
                    </Button>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
