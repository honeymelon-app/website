<script setup lang="ts">
import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import type { AppPageProps, BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    mustVerifyEmail?: boolean;
    status?: string;
}

const props = defineProps<Props>();

const page = usePage<AppPageProps>();
const user = computed(() => page.props.auth.user);

const form = useForm({
    name: user.value?.name ?? '',
    email: user.value?.email ?? '',
});

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const submit = () => {
    form.put('/settings/profile', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Profile information"
                    description="Update your account's profile information and email address."
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            autocomplete="name"
                            v-model="form.name"
                            class="max-w-md"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            autocomplete="email"
                            v-model="form.email"
                            class="max-w-md"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div
                        v-if="props.mustVerifyEmail && !user?.email_verified_at"
                        class="text-sm text-muted-foreground"
                    >
                        Your email address is unverified.
                        <a
                            href="/email/verification-notification"
                            class="underline underline-offset-4 hover:text-foreground"
                        >
                            Click here to re-send the verification email.
                        </a>
                    </div>

                    <div
                        v-if="props.status === 'profile-updated'"
                        class="text-sm font-medium text-green-600"
                    >
                        Profile updated successfully.
                    </div>

                    <Button type="submit" :disabled="form.processing">
                        <Spinner v-if="form.processing" />
                        Save changes
                    </Button>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
