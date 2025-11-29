<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post('/email/verification-notification');
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent'
);
</script>

<template>
    <AuthLayout
        title="Verify your email"
        description="Thanks for signing up! Please verify your email address"
    >
        <Head title="Email Verification" />

        <div class="space-y-6">
            <div class="text-sm text-muted-foreground">
                Before getting started, please verify your email address by clicking the
                link we just emailed to you. If you didn't receive the email, we'll
                gladly send you another.
            </div>

            <div
                v-if="verificationLinkSent"
                class="rounded-lg border bg-green-50 p-4 text-center text-sm font-medium text-green-600 dark:bg-green-900/20 dark:text-green-400"
            >
                A new verification link has been sent to your email address.
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <Button type="submit" class="w-full" :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    Resend verification email
                </Button>

                <div class="text-center">
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="text-sm text-muted-foreground underline underline-offset-4 hover:text-foreground"
                    >
                        Log out
                    </Link>
                </div>
            </form>
        </div>
    </AuthLayout>
</template>
