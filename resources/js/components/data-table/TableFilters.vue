<script setup lang="ts">
import { ref, watch } from 'vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { ChevronDown, X } from 'lucide-vue-next'
import type { FilterParams } from '@/types/api'

export interface FilterConfig {
  key: string
  label: string
  type: 'text' | 'select'
  options?: { label: string; value: string }[]
  placeholder?: string
}

const props = defineProps<{
  filters: FilterConfig[]
  modelValue: FilterParams
}>()

const emit = defineEmits<{
  'update:modelValue': [filters: FilterParams]
  'apply': []
  'clear': []
}>()

const localFilters = ref<FilterParams>({ ...props.modelValue })

// Watch for external changes
watch(
  () => props.modelValue,
  (newVal) => {
    localFilters.value = { ...newVal }
  },
  { deep: true }
)

const updateFilter = (key: string, value: any) => {
  localFilters.value[key] = value
  emit('update:modelValue', localFilters.value)
  emit('apply')
}

const clearFilters = () => {
  localFilters.value = {}
  emit('update:modelValue', {})
  emit('clear')
}

const hasActiveFilters = () => {
  return Object.values(localFilters.value).some(
    (value) => value !== undefined && value !== null && value !== ''
  )
}
</script>

<template>
  <div class="flex items-center gap-2">
    <slot name="prepend" />

    <template v-for="filter in filters" :key="filter.key">
      <!-- Text Input Filter -->
      <Input
        v-if="filter.type === 'text'"
        :placeholder="filter.placeholder || filter.label"
        :model-value="localFilters[filter.key] as string"
        class="max-w-sm"
        @update:model-value="updateFilter(filter.key, $event)"
      />

      <!-- Select Dropdown Filter -->
      <DropdownMenu v-else-if="filter.type === 'select'">
        <DropdownMenuTrigger as-child>
          <Button variant="outline" class="ml-auto">
            {{ filter.label }}
            <ChevronDown class="ml-2 h-4 w-4" />
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
          <DropdownMenuCheckboxItem
            v-for="option in filter.options"
            :key="option.value"
            :model-value="localFilters[filter.key] === option.value"
            @update:model-value="
              (checked) => updateFilter(filter.key, checked ? option.value : undefined)
            "
          >
            {{ option.label }}
          </DropdownMenuCheckboxItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </template>

    <Button
      v-if="hasActiveFilters()"
      variant="ghost"
      size="sm"
      class="h-8 px-2 lg:px-3"
      @click="clearFilters"
    >
      Clear
      <X class="ml-2 h-4 w-4" />
    </Button>

    <slot name="append" />
  </div>
</template>
