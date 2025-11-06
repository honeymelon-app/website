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
        title="Password Reset"
        description="Password reset is managed through Cerberus IAM"
    >
        <Head title="Password Reset" />

        <div class="space-y-6">
            <div class="rounded-lg border bg-muted/50 p-6 text-center">
                <p class="text-sm text-muted-foreground">
                    Password reset functionality is now managed through Cerberus IAM.
                    Please contact your administrator or use the Cerberus IAM portal to reset your password.
                </p>
            </div>

            <div class="space-x-1 text-center text-sm text-muted-foreground">
                <span>Return to</span>
                <TextLink :href="home()">log in</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
