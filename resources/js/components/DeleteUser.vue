<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import type { AppPageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage<AppPageProps>();
const securityUrl = computed(() => page.props.cerberus.securityUrl);
</script>

<template>
    <div class="space-y-6">
        <HeadingSmall
            title="Account management"
            description="Account deletion is handled exclusively by Cerberus IAM"
        />

        <div
            class="space-y-3 rounded-lg border border-red-100 bg-red-50 p-4 text-sm leading-relaxed text-red-700 dark:border-red-200/10 dark:bg-red-700/10 dark:text-red-100"
        >
            <p>
                Honeymelon delegates account lifecycle tasks (account deletion,
                team membership, SSO, etc.) to Cerberus IAM. This keeps access
                control consistent across every product connected to your
                organisation.
            </p>

            <p>
                Use the Cerberus dashboard to deactivate or delete your account.
                Once removed there, this application will automatically sync the
                change and revoke your sessions.
            </p>

            <Button
                v-if="securityUrl"
                variant="destructive"
                class="mt-4 w-full sm:w-auto"
                as-child
            >
                <a
                    :href="securityUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Open Cerberus security settings
                </a>
            </Button>

            <Button
                v-else
                variant="outline"
                class="mt-4 w-full sm:w-auto"
                disabled
            >
                Cerberus security URL not configured
            </Button>
        </div>
    </div>
</template>
