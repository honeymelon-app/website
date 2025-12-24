<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const passwordInput = ref<HTMLInputElement | null>(null);
const dialogOpen = ref(false);

const form = useForm({
    password: '',
});

const openDialog = () => {
    dialogOpen.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const closeDialog = () => {
    dialogOpen.value = false;
    form.clearErrors();
    form.reset();
};

const deleteUser = () => {
    form.delete('/settings/profile', {
        preserveScroll: true,
        onSuccess: () => closeDialog(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <div class="space-y-6">
        <HeadingSmall
            title="Delete account"
            description="Once your account is deleted, all of its resources and data will be permanently deleted."
        />

        <div
            class="space-y-3 rounded-lg border border-red-100 bg-red-50 p-4 text-sm leading-relaxed text-red-700 dark:border-red-200/10 dark:bg-red-700/10 dark:text-red-100"
        >
            <p>
                Before deleting your account, please download any data or
                information that you wish to retain.
            </p>
        </div>

        <Dialog v-model:open="dialogOpen">
            <DialogTrigger as-child>
                <Button variant="destructive" @click="openDialog">
                    Delete account
                </Button>
            </DialogTrigger>
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle
                        >Are you sure you want to delete your
                        account?</DialogTitle
                    >
                    <DialogDescription>
                        Once your account is deleted, all of its resources and
                        data will be permanently deleted. Please enter your
                        password to confirm you would like to permanently delete
                        your account.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="deleteUser" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="password" class="sr-only">Password</Label>
                        <Input
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            placeholder="Password"
                            autocomplete="current-password"
                        />
                        <InputError :message="form.errors.password" />
                    </div>
                    <DialogFooter class="gap-2 sm:gap-0">
                        <Button
                            type="button"
                            variant="secondary"
                            @click="closeDialog"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            variant="destructive"
                            :disabled="form.processing"
                        >
                            <Spinner v-if="form.processing" />
                            Delete account
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
