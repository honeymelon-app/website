<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { home } from '@/routes';
import { Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Email Verification"
        description="Email verification is managed through Cerberus IAM"
    >
        <Head title="Email Verification" />

        <div class="space-y-6">
            <div class="rounded-lg border bg-muted/50 p-6 text-center">
                <p class="text-sm text-muted-foreground">
                    Email verification is now managed through Cerberus IAM.
                    Please check your email or contact your administrator.
                </p>
            </div>

            <div class="text-center">
                <TextLink
                    :href="home()"
                    as="button"
                    class="text-sm"
                >
                    Log out
                </TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
