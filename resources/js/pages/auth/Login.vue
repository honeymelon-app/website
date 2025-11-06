<script setup lang="ts">
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase
        title="Authentication"
        description="Authentication is managed through Cerberus IAM"
    >
        <Head title="Log in" />

        <div class="space-y-6">
            <div class="rounded-lg border bg-muted/50 p-6 text-center">
                <p class="text-sm text-muted-foreground">
                    Authentication is now managed through Cerberus IAM.
                    You will be automatically redirected to the Cerberus IAM portal to log in.
                </p>
            </div>
        </div>
    </AuthBase>
</template>
