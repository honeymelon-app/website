<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { home } from '@/routes';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const toggleRecovery = (): void => {
    recovery.value = !recovery.value;
    form.code = '';
    form.recovery_code = '';
};

const submit = (): void => {
    form.post('/two-factor-challenge');
};
</script>

<template>
    <AuthLayout
        title="Two-Factor Authentication"
        description="Please confirm access to your account by entering the authentication code provided by your authenticator application."
    >
        <Head title="Two-Factor Authentication" />

        <form @submit.prevent="submit" class="space-y-6">
            <div v-if="!recovery" class="space-y-2">
                <Label for="code">Authentication Code</Label>
                <Input
                    id="code"
                    v-model="form.code"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    autofocus
                    placeholder="Enter 6-digit code"
                />
                <InputError :message="form.errors.code" />
            </div>

            <div v-else class="space-y-2">
                <Label for="recovery_code">Recovery Code</Label>
                <Input
                    id="recovery_code"
                    v-model="form.recovery_code"
                    type="text"
                    autocomplete="one-time-code"
                    autofocus
                    placeholder="Enter recovery code"
                />
                <InputError :message="form.errors.recovery_code" />
            </div>

            <div class="flex items-center justify-between">
                <button
                    type="button"
                    class="text-sm text-muted-foreground underline hover:text-foreground"
                    @click="toggleRecovery"
                >
                    {{
                        recovery
                            ? 'Use authentication code'
                            : 'Use recovery code'
                    }}
                </button>
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">
                <Spinner v-if="form.processing" />
                {{ recovery ? 'Recover Account' : 'Log in' }}
            </Button>

            <div class="text-center text-sm text-muted-foreground">
                <span>Return to </span>
                <TextLink :href="home()">log in</TextLink>
            </div>
        </form>
    </AuthLayout>
</template>
