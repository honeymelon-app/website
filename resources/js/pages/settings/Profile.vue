<script setup lang="ts">
import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/profile';
import type { AppPageProps, BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    manageProfileUrl?: string | null;
}

const props = defineProps<Props>();

const page = usePage<AppPageProps>();
const user = computed(() => page.props.auth.user);
const manageProfileUrl = computed(
    () => props.manageProfileUrl ?? page.props.cerberus.profileUrl,
);

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

const profileDetails = computed(() => {
    if (!user.value) {
        return [];
    }

    return [
        { label: 'Full name', value: user.value.name },
        { label: 'First name', value: user.value.first_name ?? '—' },
        { label: 'Last name', value: user.value.last_name ?? '—' },
        { label: 'Email', value: user.value.email },
        {
            label: 'Organisation',
            value: user.value.organisation?.name ?? '—',
        },
    ];
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Profile information"
                    description="Profile updates are managed in Cerberus IAM"
                />

                <Card>
                    <CardHeader class="space-y-2">
                        <CardTitle class="text-base font-semibold">
                            Synced details
                        </CardTitle>
                        <p class="text-sm text-muted-foreground">
                            We mirror a subset of your Cerberus profile so
                            relationships (releases, licenses, etc.) continue to
                            work inside Honeymelon.
                        </p>
                    </CardHeader>
                    <CardContent>
                        <dl class="divide-y divide-border/60 text-sm">
                            <template v-if="user">
                                <div
                                    v-for="item in profileDetails"
                                    :key="item.label"
                                    class="flex flex-col gap-1 py-3 first:pt-0 last:pb-0 md:flex-row md:items-center md:justify-between"
                                >
                                    <dt class="font-medium text-muted-foreground">
                                        {{ item.label }}
                                    </dt>
                                    <dd class="text-base">
                                        {{ item.value }}
                                    </dd>
                                </div>
                            </template>
                            <template v-else>
                                <div class="py-3 text-muted-foreground">
                                    Profile data will be available once you sign
                                    in through Cerberus.
                                </div>
                            </template>
                        </dl>

                        <div class="mt-6">
                            <Button
                                v-if="manageProfileUrl"
                                as-child
                                class="w-full sm:w-auto"
                            >
                                <a
                                    :href="manageProfileUrl"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    Manage profile in Cerberus
                                </a>
                            </Button>
                            <Button v-else disabled class="w-full sm:w-auto">
                                Cerberus portal URL not configured
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
