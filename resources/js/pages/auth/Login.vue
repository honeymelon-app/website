<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Form, Head, router } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const handleSuccess = () => {
    // Password will be cleared automatically by form reset
};
</script>

<template>
    <AuthBase
        title="Log in to your account"
        description="Enter your email and password below"
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <Form
            action="/login"
            method="post"
            :data="{
                email: '',
                password: '',
                remember: false,
            }"
            @success="handleSuccess"
            #default="{ errors, processing, reset }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink
                            v-if="canResetPassword"
                            href="/forgot-password"
                            class="text-sm"
                            :tabindex="5"
                        >
                            Forgot password?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        name="password"
                        type="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <Label for="remember" class="text-sm font-normal">
                        Remember me
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    :tabindex="4"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    Log in
                </Button>
            </div>

            <div
                v-if="canRegister"
                class="text-center text-sm text-muted-foreground"
            >
                Don't have an account?
                <TextLink
                    href="/register"
                    class="underline underline-offset-4"
                    :tabindex="6"
                >
                    Sign up
                </TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
