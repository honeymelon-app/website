<script setup lang="ts" generic="T extends Record<string, any>">
import { computed } from 'vue'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Skeleton } from '@/components/ui/skeleton'
import DataTablePagination from './DataTablePagination.vue'
import type { PaginationMeta } from '@/types/api'

export interface Column<T> {
  key: string
  label: string
  sortable?: boolean
  class?: string
  headerClass?: string
  render?: (row: T) => any
}

const props = defineProps<{
  columns: Column<T>[]
  data: T[]
  meta?: PaginationMeta
  isLoading?: boolean
  emptyMessage?: string
}>()

const emit = defineEmits<{
  'page-change': [page: number]
  'sort': [key: string, direction: 'asc' | 'desc']
}>()

const handlePageChange = (page: number) => {
  emit('page-change', page)
}

const hasData = computed(() => props.data && props.data.length > 0)
</script>

<template>
  <div class="space-y-4">
    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead
              v-for="column in columns"
              :key="column.key"
              :class="column.headerClass"
            >
              {{ column.label }}
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <!-- Loading State -->
          <template v-if="isLoading">
            <TableRow v-for="i in 5" :key="`skeleton-${i}`">
              <TableCell v-for="column in columns" :key="column.key">
                <Skeleton class="h-4 w-full" />
              </TableCell>
            </TableRow>
          </template>

          <!-- Data Rows -->
          <template v-else-if="hasData">
            <TableRow v-for="(row, index) in data" :key="row.id || index">
              <TableCell
                v-for="column in columns"
                :key="column.key"
                :class="column.class"
              >
                <component
                  :is="column.render ? column.render(row) : row[column.key]"
                  v-if="column.render"
                />
                <template v-else>
                  {{ row[column.key] }}
                </template>
              </TableCell>
            </TableRow>
          </template>

          <!-- Empty State -->
          <TableRow v-else>
            <TableCell :colspan="columns.length" class="h-24 text-center">
              <div class="text-muted-foreground">
                {{ emptyMessage || 'No results found.' }}
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Pagination -->
    <DataTablePagination
      v-if="meta"
      :meta="meta"
      :is-loading="isLoading"
      @page-change="handlePageChange"
    />
  </div>
</template>
