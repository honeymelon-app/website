<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { dashboard } from '@/routes';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <AuthLayout
        title="Password Confirmation"
        description="Password confirmation is managed through Cerberus IAM"
    >
        <Head title="Password Confirmation" />

        <div class="space-y-6">
            <div class="rounded-lg border bg-muted/50 p-6 text-center">
                <p class="text-sm text-muted-foreground">
                    Password confirmation is now managed through Cerberus IAM.
                    Please re-authenticate through the Cerberus IAM portal if needed.
                </p>
            </div>

            <div class="space-x-1 text-center text-sm text-muted-foreground">
                <span>Return to</span>
                <TextLink :href="dashboard()">dashboard</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
