<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { show } from '@/routes/two-factor';
import type { AppPageProps, BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Props {
    qrCode?: string;
    setupKey?: string;
    recoveryCodes?: string[];
    requiresConfirmation?: boolean;
}

const props = defineProps<Props>();

const page = usePage<AppPageProps>();
const user = computed(() => page.props.auth.user);

const confirmationForm = useForm({
    code: '',
});

const confirming = ref(false);
const showingRecoveryCodes = ref(false);

const twoFactorEnabled = computed(
    () => user.value?.two_factor_confirmed_at != null,
);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Two-Factor Authentication',
        href: show.url(),
    },
];

const enableTwoFactorAuthentication = (): void => {
    router.post(
        '/user/two-factor-authentication',
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                confirming.value = true;
            },
        },
    );
};

const confirmTwoFactorAuthentication = (): void => {
    confirmationForm.post('/user/confirmed-two-factor-authentication', {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirming.value = false;
            showingRecoveryCodes.value = true;
        },
    });
};

const regenerateRecoveryCodes = (): void => {
    router.post(
        '/user/two-factor-recovery-codes',
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showingRecoveryCodes.value = true;
            },
        },
    );
};

const showRecoveryCodes = (): void => {
    router.get(
        show.url(),
        { showRecoveryCodes: true },
        {
            preserveScroll: true,
            onSuccess: () => {
                showingRecoveryCodes.value = true;
            },
        },
    );
};

const disableTwoFactorAuthentication = (): void => {
    router.delete('/user/two-factor-authentication', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Two-Factor Authentication" />
        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Two-Factor Authentication"
                    description="Add additional security to your account using two-factor authentication."
                />

                <!-- Enabled State -->
                <div v-if="twoFactorEnabled && !confirming" class="space-y-4">
                    <div class="rounded-lg border bg-green-50 p-4 dark:bg-green-950">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">
                            Two-factor authentication is enabled.
                        </p>
                        <p class="mt-1 text-sm text-green-600 dark:text-green-400">
                            Your account is now more secure with two-factor authentication.
                        </p>
                    </div>

                    <!-- Recovery Codes -->
                    <div
                        v-if="showingRecoveryCodes && props.recoveryCodes?.length"
                        class="space-y-3"
                    >
                        <p class="text-sm text-muted-foreground">
                            Store these recovery codes in a secure location.
                            They can be used to recover access to your account
                            if your two-factor authentication device is lost.
                        </p>
                        <div
                            class="grid gap-1 rounded-lg bg-muted p-4 font-mono text-sm"
                        >
                            <div
                                v-for="code in props.recoveryCodes"
                                :key="code"
                            >
                                {{ code }}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <Button
                            v-if="!showingRecoveryCodes"
                            variant="outline"
                            @click="showRecoveryCodes"
                        >
                            Show Recovery Codes
                        </Button>
                        <Button variant="outline" @click="regenerateRecoveryCodes">
                            Regenerate Recovery Codes
                        </Button>
                        <Button
                            variant="destructive"
                            @click="disableTwoFactorAuthentication"
                        >
                            Disable
                        </Button>
                    </div>
                </div>

                <!-- Setup State -->
                <div v-else-if="confirming" class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        To finish enabling two-factor authentication, scan the
                        following QR code using your authenticator application
                        or enter the setup key.
                    </p>

                    <div
                        v-if="props.qrCode"
                        class="inline-block rounded-lg bg-white p-4"
                        v-html="props.qrCode"
                    />

                    <div v-if="props.setupKey" class="space-y-2">
                        <p class="text-sm font-medium">Setup Key</p>
                        <code
                            class="block rounded bg-muted px-3 py-2 font-mono text-sm"
                        >
                            {{ props.setupKey }}
                        </code>
                    </div>

                    <form
                        @submit.prevent="confirmTwoFactorAuthentication"
                        class="max-w-xs space-y-4"
                    >
                        <div class="space-y-2">
                            <Label for="code">Verification Code</Label>
                            <Input
                                id="code"
                                v-model="confirmationForm.code"
                                type="text"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                placeholder="Enter 6-digit code"
                            />
                            <InputError :message="confirmationForm.errors.code" />
                        </div>
                        <div class="flex gap-3">
                            <Button
                                type="submit"
                                :disabled="confirmationForm.processing"
                            >
                                <Spinner v-if="confirmationForm.processing" />
                                Confirm
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                @click="disableTwoFactorAuthentication"
                            >
                                Cancel
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- Disabled State -->
                <div v-else class="space-y-4">
                    <div class="rounded-lg border bg-muted/50 p-4">
                        <p class="text-sm text-muted-foreground">
                            Two-factor authentication is not enabled. When
                            two-factor authentication is enabled, you will be
                            prompted for a secure, random token during
                            authentication.
                        </p>
                    </div>

                    <Button @click="enableTwoFactorAuthentication">
                        Enable Two-Factor Authentication
                    </Button>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
