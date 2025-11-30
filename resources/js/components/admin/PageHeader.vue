<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

interface Props {
    title: string;
    description?: string;
    backUrl?: string;
}

const props = defineProps<Props>();

const goBack = () => {
    if (props.backUrl) {
        router.visit(props.backUrl);
    }
};
</script>

<template>
    <div class="flex items-start justify-between gap-4">
        <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2">
                <Button
                    v-if="backUrl"
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8"
                    @click="goBack"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <h3 class="text-2xl font-semibold tracking-tight">
                    <slot name="title">{{ title }}</slot>
                </h3>
                <slot name="badges" />
            </div>
            <p v-if="description || $slots.description" class="text-sm text-muted-foreground">
                <slot name="description">{{ description }}</slot>
            </p>
        </div>
        <div v-if="$slots.actions" class="flex gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
