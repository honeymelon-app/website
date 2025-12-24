<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { X } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    selectedCount: number;
    itemLabel?: string;
    itemLabelPlural?: string;
}>();

const emit = defineEmits<{
    clear: [];
}>();

const label = computed(() => {
    const singular = props.itemLabel || 'item';
    const plural = props.itemLabelPlural || `${singular}s`;
    return props.selectedCount === 1 ? singular : plural;
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
    >
        <div
            v-if="selectedCount > 0"
            class="flex items-center justify-between gap-4 rounded-lg border border-primary/20 bg-primary/5 px-4 py-3"
        >
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium">
                    {{ selectedCount }} {{ label }} selected
                </span>
                <Button
                    variant="ghost"
                    size="sm"
                    class="h-7 px-2 text-muted-foreground"
                    @click="emit('clear')"
                >
                    <X class="mr-1 h-3.5 w-3.5" />
                    Clear
                </Button>
            </div>
            <div class="flex items-center gap-2">
                <slot />
            </div>
        </div>
    </Transition>
</template>
