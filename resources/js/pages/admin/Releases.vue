<script setup lang="ts">
import { computed, h, onMounted, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { DataTable, TableFilters, type Column, type FilterConfig } from '@/components/data-table'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { MoreHorizontal, Download, Eye, Rocket } from 'lucide-vue-next'
import { useTableData } from '@/composables/useTableData'
import type { Release, FilterParams } from '@/types/api'

// Fetch data
const {
  data: releases,
  meta,
  isLoading,
  fetchData,
  updateFilters,
  goToPage,
  clearFilters,
} = useTableData<Release>('/api/releases')

// Filters
const filterParams = ref<FilterParams>({})

const filterConfigs: FilterConfig[] = [
  {
    key: 'search',
    type: 'text',
    label: 'Search',
    placeholder: 'Search versions, tags, or notes...',
  },
  {
    key: 'channel',
    type: 'select',
    label: 'Channel',
    options: [
      { label: 'Stable', value: 'stable' },
      { label: 'Beta', value: 'beta' },
    ],
  },
  {
    key: 'major',
    type: 'select',
    label: 'Major Releases',
    options: [{ label: 'Major Only', value: 'true' }],
  },
]

// Column definitions
const columns: Column<Release>[] = [
  {
    key: 'version',
    label: 'Version',
    headerClass: 'w-[120px]',
    render: (row: Release) => {
      return h('div', { class: 'font-mono font-medium' }, row.version)
    },
  },
  {
    key: 'tag',
    label: 'Tag',
    class: 'font-mono text-xs text-muted-foreground',
  },
  {
    key: 'channel',
    label: 'Channel',
    headerClass: 'w-[100px]',
    render: (row: Release) => {
      const variant = row.channel === 'stable' ? 'default' : 'secondary'
      return h(
        Badge,
        { variant, class: 'capitalize' },
        { default: () => row.channel }
      )
    },
  },
  {
    key: 'major',
    label: 'Major',
    headerClass: 'w-[80px] text-center',
    class: 'text-center',
    render: (row: Release) => {
      return row.major
        ? h(Badge, { variant: 'destructive', class: 'text-xs' }, { default: () => 'Major' })
        : h('span', { class: 'text-muted-foreground' }, '—')
    },
  },
  {
    key: 'published_at',
    label: 'Published',
    headerClass: 'w-[140px]',
    render: (row: Release) => {
      const date = new Date(row.published_at)
      return h(
        'div',
        { class: 'text-sm' },
        date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
        })
      )
    },
  },
  {
    key: 'created_at',
    label: 'Created',
    headerClass: 'w-[140px]',
    render: (row: Release) => {
      const date = new Date(row.created_at)
      return h(
        'time',
        {
          datetime: row.created_at,
          class: 'text-sm text-muted-foreground',
        },
        date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
        })
      )
    },
  },
  {
    key: 'actions',
    label: '',
    headerClass: 'w-[50px]',
    render: (row: Release) => {
      return h(
        DropdownMenu,
        {},
        {
          default: () => [
            h(
              DropdownMenuTrigger,
              { asChild: true },
              {
                default: () =>
                  h(
                    Button,
                    { variant: 'ghost', size: 'icon', class: 'h-8 w-8' },
                    {
                      default: () => [
                        h('span', { class: 'sr-only' }, 'Open menu'),
                        h(MoreHorizontal, { class: 'h-4 w-4' }),
                      ],
                    }
                  ),
              }
            ),
            h(
              DropdownMenuContent,
              { align: 'end' },
              {
                default: () => [
                  h(DropdownMenuLabel, {}, { default: () => 'Actions' }),
                  h(
                    DropdownMenuItem,
                    {
                      onClick: () => viewRelease(row),
                    },
                    {
                      default: () => [
                        h(Eye, { class: 'mr-2 h-4 w-4' }),
                        'View Details',
                      ],
                    }
                  ),
                  h(
                    DropdownMenuItem,
                    {
                      onClick: () => downloadArtifacts(row),
                    },
                    {
                      default: () => [
                        h(Download, { class: 'mr-2 h-4 w-4' }),
                        'Download Artifacts',
                      ],
                    }
                  ),
                  h(DropdownMenuSeparator),
                  h(
                    DropdownMenuItem,
                    {
                      onClick: () => publishRelease(row),
                    },
                    {
                      default: () => [
                        h(Rocket, { class: 'mr-2 h-4 w-4' }),
                        'Publish to Channel',
                      ],
                    }
                  ),
                ],
              }
            ),
          ],
        }
      )
    },
  },
]

// Actions
const viewRelease = (release: Release): void => {
  router.visit(`/admin/releases/${release.id}`)
}

const downloadArtifacts = (release: Release): void => {
  console.log('Download artifacts for:', release.version)
  // Implement download logic
}

const publishRelease = (release: Release): void => {
  console.log('Publish release:', release.version)
  // Implement publish logic
}

const handleFilterApply = (): void => {
  updateFilters(filterParams.value)
}

const handleFilterClear = (): void => {
  filterParams.value = {}
  clearFilters()
}

const handlePageChange = (page: number): void => {
  goToPage(page)
}

// Fetch data on mount
onMounted(() => {
  fetchData()
})
</script>

<template>
  <Head title="Releases" />

  <div class="space-y-6">
    <div>
      <h3 class="text-2xl font-semibold">Releases</h3>
      <p class="text-sm text-muted-foreground">
        Manage your application releases and versions.
      </p>
    </div>

    <div class="space-y-4">
      <TableFilters
        v-model="filterParams"
        :filters="filterConfigs"
        @apply="handleFilterApply"
        @clear="handleFilterClear"
      >
        <template #append>
          <Button @click="router.visit('/admin/releases/create')">
            Create Release
          </Button>
        </template>
      </TableFilters>

      <DataTable
        :columns="columns"
        :data="releases"
        :meta="meta"
        :is-loading="isLoading"
        empty-message="No releases found. Create your first release to get started."
        @page-change="handlePageChange"
      />
    </div>
  </div>
</template>
