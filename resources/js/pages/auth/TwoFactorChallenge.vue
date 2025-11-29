<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { home } from '@/routes';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <AuthLayout
        title="Two-Factor Authentication"
        description="Two-factor authentication is managed through Cerberus IAM"
    >
        <Head title="Two-Factor Authentication" />

        <div class="space-y-6">
            <div class="rounded-lg border bg-muted/50 p-6 text-center">
                <p class="text-sm text-muted-foreground">
                    Two-factor authentication is now managed through Cerberus
                    IAM. Please authenticate through the Cerberus IAM portal.
                </p>
            </div>

            <div class="space-x-1 text-center text-sm text-muted-foreground">
                <span>Return to</span>
                <TextLink :href="home()">log in</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
