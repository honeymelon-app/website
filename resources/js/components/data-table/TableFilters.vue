<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import type { FilterParams } from '@/types/api';
import { Check, ChevronDown, Search, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

export interface FilterConfig {
    key: string;
    label: string;
    type: 'text' | 'select';
    options?: { label: string; value: string; }[];
    placeholder?: string;
}

const props = defineProps<{
    filters: FilterConfig[];
    modelValue: FilterParams;
}>();

const emit = defineEmits<{
    'update:modelValue': [filters: FilterParams];
    apply: [];
    clear: [];
}>();

const localFilters = ref<FilterParams>({ ...props.modelValue });

// Watch for external changes
watch(
    () => props.modelValue,
    (newVal) => {
        localFilters.value = { ...newVal };
    },
    { deep: true },
);

const updateFilter = (key: string, value: any) => {
    localFilters.value[key] = value;
    emit('update:modelValue', localFilters.value);
    emit('apply');
};

const clearFilters = () => {
    localFilters.value = {};
    emit('update:modelValue', {});
    emit('clear');
};

const hasActiveFilters = () => {
    return Object.values(localFilters.value).some(
        (value) => value !== undefined && value !== null && value !== '',
    );
};

const activeFilterCount = computed(() => {
    return Object.values(localFilters.value).filter(
        (value) => value !== undefined && value !== null && value !== '',
    ).length;
});

// Get the selected option label for a select filter
const getSelectedLabel = (filter: FilterConfig): string | undefined => {
    const value = localFilters.value[filter.key];
    if (!value || !filter.options) return undefined;
    return filter.options.find((o) => o.value === value)?.label;
};
</script>

<template>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="flex flex-1 flex-wrap items-center gap-2">
            <slot name="prepend" />

            <template v-for="filter in filters" :key="filter.key">
                <!-- Text Input Filter with Search Icon -->
                <div v-if="filter.type === 'text'" class="relative">
                    <Search
                        class="absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 text-muted-foreground/60"
                    />
                    <Input
                        :placeholder="filter.placeholder || filter.label"
                        :model-value="localFilters[filter.key] as string"
                        class="h-9 w-full pr-3 pl-9 sm:w-[200px] lg:w-[280px]"
                        @update:model-value="updateFilter(filter.key, $event)"
                    />
                </div>

                <!-- Select Dropdown Filter with improved styling -->
                <DropdownMenu v-else-if="filter.type === 'select'">
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="outline"
                            size="sm"
                            :class="[
                                'h-9 gap-1.5 border-dashed font-normal',
                                localFilters[filter.key]
                                    ? 'border-primary/50 bg-primary/5'
                                    : '',
                            ]"
                        >
                            <span class="text-muted-foreground">
                                {{ filter.label }}
                            </span>
                            <template v-if="getSelectedLabel(filter)">
                                <span
                                    class="h-4 w-px bg-border"
                                    aria-hidden="true"
                                />
                                <Badge
                                    variant="secondary"
                                    class="rounded-sm px-1.5 py-0 text-xs font-normal"
                                >
                                    {{ getSelectedLabel(filter) }}
                                </Badge>
                            </template>
                            <ChevronDown class="h-4 w-4 opacity-50" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="start" class="w-40">
                        <DropdownMenuItem
                            v-for="option in filter.options"
                            :key="option.value"
                            class="flex items-center justify-between gap-2"
                            @click="
                                updateFilter(
                                    filter.key,
                                    localFilters[filter.key] === option.value
                                        ? undefined
                                        : option.value,
                                )
                                "
                        >
                            {{ option.label }}
                            <Check
                                v-if="localFilters[filter.key] === option.value"
                                class="h-4 w-4 text-primary"
                            />
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </template>

            <!-- Clear filters button with count badge -->
            <Button
                v-if="hasActiveFilters()"
                variant="ghost"
                size="sm"
                class="h-9 gap-1.5 px-2.5 text-muted-foreground hover:text-foreground"
                @click="clearFilters"
            >
                Clear
                <Badge
                    v-if="activeFilterCount > 1"
                    variant="secondary"
                    class="rounded-full px-1.5 py-0 text-xs"
                >
                    {{ activeFilterCount }}
                </Badge>
                <X class="h-4 w-4" />
            </Button>
        </div>

        <div class="flex items-center gap-2">
            <slot name="append" />
        </div>
    </div>
</template>
