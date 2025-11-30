<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    open?: boolean;
    title: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    loading?: boolean;
    variant?: 'destructive' | 'default';
    triggerLabel?: string;
    showTrigger?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    open: undefined,
    confirmLabel: 'Confirm',
    cancelLabel: 'Cancel',
    loading: false,
    variant: 'destructive',
    showTrigger: true,
});

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'confirm'): void;
    (e: 'cancel'): void;
}>();

const isOpen = computed({
    get: () => props.open,
    set: (value: boolean) => emit('update:open', value),
});

const handleConfirm = () => {
    emit('confirm');
};

const handleCancel = () => {
    emit('cancel');
    isOpen.value = false;
};

const actionClasses = computed(() => {
    if (props.variant === 'destructive') {
        return 'bg-destructive text-destructive-foreground hover:bg-destructive/90';
    }
    return '';
});
</script>

<template>
    <AlertDialog v-model:open="isOpen">
        <AlertDialogTrigger v-if="showTrigger" as-child>
            <slot name="trigger">
                <Button :variant="variant" size="sm">
                    <Trash2
                        v-if="variant === 'destructive'"
                        class="mr-2 h-4 w-4"
                    />
                    {{ triggerLabel ?? confirmLabel }}
                </Button>
            </slot>
        </AlertDialogTrigger>
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ title }}</AlertDialogTitle>
                <AlertDialogDescription
                    v-if="description || $slots.default"
                    as="div"
                >
                    <slot>{{ description }}</slot>
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel :disabled="loading" @click="handleCancel">
                    {{ cancelLabel }}
                </AlertDialogCancel>
                <AlertDialogAction
                    :disabled="loading"
                    :class="actionClasses"
                    @click="handleConfirm"
                >
                    {{ loading ? 'Processing...' : confirmLabel }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
